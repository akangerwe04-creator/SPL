<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerCustomItem;
use App\Models\Package;
use App\Models\Item;
use App\Models\Payment;
use App\Models\User;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Customer::with(['agent', 'activePackage.package']);

        // Jika agen lapangan, hanya tampilkan konsumen binaan sendiri
        if ($user->isAgent()) {
            $query->where('agent_id', $user->id);
        } elseif ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%");
            });
        }

        if ($request->filled('package_type')) {
            $type = $request->package_type;
            $query->whereHas('activePackage.package', fn($q) => $q->where('type', $type));
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $agents = $user->isOwnerOrAdmin() ? User::where('role', 'agent')->select('id', 'name', 'phone')->get() : collect();

        return view('customers.index', compact('customers', 'agents'));
    }

    public function create()
    {
        $user = Auth::user();
        $packages = Package::where('is_active', true)->with(['packageItems.item', 'packagings'])->get();
        $items = Item::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $agents = $user->isOwnerOrAdmin() ? User::where('role', 'agent')->select('id', 'name', 'phone')->get() : collect();

        return view('customers.create', compact('packages', 'items', 'agents', 'user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'package_id' => 'required|exists:packages,id',
            'packaging_id' => 'nullable|exists:packagings,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ];

        if ($user->isOwner()) {
            $rules['agent_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);
        $agentId = $user->isOwner() ? $validated['agent_id'] : $user->id;

        $package = Package::with('packagings')->findOrFail($validated['package_id']);

        DB::beginTransaction();
        try {
            // 1. Buat data Pelanggan
            $customer = Customer::create([
                'agent_id' => $agentId,
                'code' => Customer::generateNextCode(),
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Hitung harga kemasan (bila dipilih)
            $packagingId = !empty($validated['packaging_id']) ? $validated['packaging_id'] : null;
            $packagingPrice = 0;
            if ($packagingId) {
                $pkgOption = $package->packagings->where('id', $packagingId)->first();
                if ($pkgOption) {
                    $packagingPrice = $pkgOption->pivot->custom_price !== null ? (float)$pkgOption->pivot->custom_price : (float)$pkgOption->price;
                } else {
                    $masterKms = \App\Models\Packaging::find($packagingId);
                    if ($masterKms) {
                        $packagingPrice = (float) $masterKms->price;
                    }
                }
            }

            // 2. Tentukan Total Target & Cicilan
            $installmentCount = $package->installment_count ?: 30;
            if ((float) $package->target_nominal > 0) {
                $targetNominal = (float) $package->target_nominal + $packagingPrice;
                $rawInstallment = $installmentCount > 0 ? ($targetNominal / $installmentCount) : 0;
            } else {
                $baseInstallment = (float) ($package->installment_amount ?: 0);
                $rawInstallment = $baseInstallment + ($installmentCount > 0 ? ($packagingPrice / $installmentCount) : 0);
                $targetNominal = ($baseInstallment * $installmentCount) + $packagingPrice;
            }
            $installmentAmount = Package::roundInstallment($rawInstallment);

            // Jika Paket Ecer:
            $customItemsData = [];
            if ($package->isEcer()) {
                $selectedItems = $request->input('custom_items', []);
                $pkgItemsLookup = $package->packageItems->keyBy('item_id');
                $totalWeeklyInstallment = 0;
                $calculatedTotal = 0;

                // 1. Jika ada item yang dipilih oleh konsumen
                if (!empty($selectedItems) && is_array($selectedItems)) {
                    foreach ($selectedItems as $itemId => $qty) {
                        $qty = (int) $qty;
                        if ($qty > 0) {
                            $pkgItem = $package->packageItems->firstWhere('id', $itemId) ?? $pkgItemsLookup->get($itemId);
                            $item = $pkgItem?->item ?? Item::find($itemId);
                            if ($item) {
                                $itemWeekly = ($pkgItem && (float)$pkgItem->installment_amount > 0)
                                    ? (float) $pkgItem->installment_amount
                                    : 0;
                                $itemBaseQty = ($pkgItem && $pkgItem->quantity > 0) ? $pkgItem->quantity : 1;
                                $finalQty = $itemBaseQty * $qty;
                                $unit = $pkgItem?->unit ?: ($item->base_unit ?: 'pcs');

                                if ($itemWeekly > 0) {
                                    $itemSubtotalWeekly = $itemWeekly * $qty;
                                    $totalWeeklyInstallment += $itemSubtotalWeekly;
                                    $itemTarget = $itemSubtotalWeekly * $installmentCount;
                                    $calculatedTotal += $itemTarget;

                                    $customItemsData[] = [
                                        'item_id' => $item->id,
                                        'quantity' => $finalQty,
                                        'unit' => $unit,
                                        'installment_amount' => $itemWeekly,
                                        'unit_price' => $itemWeekly,
                                        'subtotal' => $itemTarget,
                                    ];
                                } else {
                                    $subtotal = $item->unit_price * $qty;
                                    $calculatedTotal += $subtotal;
                                    $customItemsData[] = [
                                        'item_id' => $item->id,
                                        'quantity' => $qty,
                                        'unit' => $unit,
                                        'installment_amount' => 0,
                                        'unit_price' => $item->unit_price,
                                        'subtotal' => $subtotal,
                                    ];
                                }
                            }
                        }
                    }
                }

                // 2. Jika tidak ada item yang dikirimkan, gunakan seluruh daftar barang yang telah ditentukan Owner
                if (empty($customItemsData) && $package->packageItems()->count() > 0) {
                    $hasItemInstallment = $package->packageItems->contains(fn($pi) => (float)$pi->installment_amount > 0);

                    if ($hasItemInstallment) {
                        foreach ($package->packageItems as $pi) {
                            $itemWeekly = (float) $pi->installment_amount;
                            $totalWeeklyInstallment += $itemWeekly;
                            $itemTarget = $itemWeekly * $installmentCount;
                            $calculatedTotal += $itemTarget;

                            $customItemsData[] = [
                                'item_id' => $pi->item_id,
                                'quantity' => $pi->quantity,
                                'unit' => $pi->unit ?: ($pi->item?->base_unit ?: 'pcs'),
                                'installment_amount' => $itemWeekly,
                                'unit_price' => $itemWeekly,
                                'subtotal' => $itemTarget,
                            ];
                        }
                    } else {
                        $basePkgWeekly = (float) ($package->installment_amount ?: 0);
                        $totalWeeklyInstallment = $basePkgWeekly;
                        $countItems = max(1, $package->packageItems()->count());
                        $itemWeeklyPart = $basePkgWeekly > 0 ? ($basePkgWeekly / $countItems) : 0;

                        foreach ($package->packageItems as $pi) {
                            $itemTarget = $itemWeeklyPart * $installmentCount;
                            $calculatedTotal += $itemTarget;

                            $customItemsData[] = [
                                'item_id' => $pi->item_id,
                                'quantity' => $pi->quantity,
                                'unit' => $pi->unit ?: ($pi->item?->base_unit ?: 'pcs'),
                                'installment_amount' => $itemWeeklyPart,
                                'unit_price' => $itemWeeklyPart > 0 ? $itemWeeklyPart : ($pi->item?->unit_price ?: 0),
                                'subtotal' => $itemTarget,
                            ];
                        }
                    }
                }

                if (empty($customItemsData)) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Silakan pilih minimal 1 barang untuk paket eceran.');
                }

                // 3. Kalkulasi Setoran per Minggu & Total Target
                if ($totalWeeklyInstallment > 0) {
                    $packagingWeekly = $installmentCount > 0 ? ($packagingPrice / $installmentCount) : 0;
                    if ($packagingWeekly > 0) {
                        $installmentAmount = Package::roundInstallment($totalWeeklyInstallment + $packagingWeekly);
                    } else {
                        // Nilai nominal storan paket eceran bebas tanpa paksaan pembulatan (misal 2.500 atau 1.100)
                        $installmentAmount = $totalWeeklyInstallment;
                    }
                    $targetNominal = $installmentAmount * $installmentCount;
                } else {
                    $adminFee = (float) ($package->admin_fee ?: 0);
                    $targetNominal = $calculatedTotal + $adminFee + $packagingPrice;
                    $rawInstallment = $installmentCount > 0 ? ($targetNominal / $installmentCount) : 0;
                    $installmentAmount = Package::roundInstallment($rawInstallment);
                }
            }

            // 3. Buat Customer Package
            $customerPackage = CustomerPackage::create([
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'agent_id' => $agentId,
                'packaging_id' => $packagingId,
                'packaging_price' => $packagingPrice,
                'start_date' => $validated['start_date'],
                'total_target' => $targetNominal,
                'total_paid' => 0,
                'status' => 'active',
                'fulfillment_status' => 'pending',
            ]);

            // Simpan rincian rakitan jika paket ecer
            if ($package->isEcer() && !empty($customItemsData)) {
                foreach ($customItemsData as $ci) {
                    $ci['customer_package_id'] = $customerPackage->id;
                    CustomerCustomItem::create($ci);
                }
            }

            // 4. Generate Ceklis Tagihan Pembayaran (Smart Checklist)
            for ($week = 1; $week <= $installmentCount; $week++) {
                Payment::create([
                    'customer_package_id' => $customerPackage->id,
                    'agent_id' => $agentId,
                    'period_number' => $week,
                    'amount' => $installmentAmount,
                    'is_paid' => false,
                    'is_locked' => false,
                    'status' => 'unpaid',
                ]);
            }

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Konsumen berhasil didaftarkan beserta ' . $installmentCount . ' jadwal ceklis tagihan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mendaftarkan konsumen: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $user = Auth::user();

        // Security check for agent
        if ($user->isAgent() && $customer->agent_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat data konsumen binaan Anda.');
        }

        $customer->load([
            'agent',
            'customerPackages.package.packageItems.item',
            'customerPackages.packaging',
            'customerPackages.customItems.item',
            'customerPackages.payments' => fn($q) => $q->orderBy('period_number', 'asc'),
            'customerPackages.partialFulfillment.items.item',
        ]);

        $activePackage = $customer->customerPackages->where('status', '!=', 'canceled')->first();

        return view('customers.show', compact('customer', 'activePackage'));
    }

    public function edit(Customer $customer)
    {
        $user = Auth::user();
        if ($user->isAgent() && $customer->agent_id !== $user->id) {
            abort(403);
        }

        $agents = User::where('role', 'agent')->get();
        return view('customers.edit', compact('customer', 'agents'));
    }

    public function update(Request $request, Customer $customer)
    {
        $user = Auth::user();
        if ($user->isAgent() && $customer->agent_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
        ]);

        if ($user->isOwner() && !empty($validated['agent_id'])) {
            $customer->agent_id = $validated['agent_id'];
            // Perbarui juga paket aktif
            foreach ($customer->customerPackages as $cp) {
                $cp->update(['agent_id' => $validated['agent_id']]);
            }
        }

        $customer->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('customers.show', $customer)->with('success', 'Data konsumen berhasil diperbarui.');
    }

    /**
     * Pembatalan / Mundur Konsumen (Potongan 10% sesuai aturan SPL)
     */
    public function cancelPackage(Request $request, CustomerPackage $customerPackage)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        if ($customerPackage->status === 'canceled') {
            return back()->with('error', 'Paket ini sudah berstatus batal.');
        }

        $customerPackage->cancelMembership($request->reason ?: 'Pengajuan pembatalan konsumen');

        return back()->with('success', 'Paket konsumen telah dibatalkan dengan potongan biaya administrasi 10%. Rincian pengembalian dana telah tercatat.');
    }
}

