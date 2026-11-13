<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Item;
use App\Models\CatalogSetting;
use App\Models\PartialFulfillment;
use App\Models\PartialFulfillmentItem;

class BookClosingController extends Controller
{
    /**
     * Dasbor Utama Tutup Buku (Owner & Admin)
     */
    public function index(Request $request)
    {
        $appSetting = CatalogSetting::current();

        $query = CustomerPackage::with([
            'customer',
            'package.packageItems.item',
            'agent',
            'packaging',
            'partialFulfillment.items.item',
            'decidedBy',
        ])->where('status', '!=', 'canceled');

        // Pencarian konsumen / agen / paket
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereHas('customer', function ($cq) use ($s) {
                    $cq->where('name', 'like', "%{$s}%")
                       ->orWhere('code', 'like', "%{$s}%")
                       ->orWhere('phone', 'like', "%{$s}%");
                })->orWhereHas('agent', function ($aq) use ($s) {
                    $aq->where('name', 'like', "%{$s}%");
                })->orWhereHas('package', function ($pq) use ($s) {
                    $pq->where('name', 'like', "%{$s}%")
                       ->orWhere('code', 'like', "%{$s}%");
                });
            });
        }

        // Filter Agen
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $allPackagesForStats = (clone $query)->get();

        // Hitung statistik
        $totalOrders = $allPackagesForStats->count();
        $totalTarget = (float) $allPackagesForStats->sum('total_target');
        $totalPaid = (float) $allPackagesForStats->sum('total_paid');

        $safeReachedList = [];
        $unreachedList = [];
        $decidedList = [];

        foreach ($allPackagesForStats as $cp) {
            $isDecided = $cp->isDecisionMade();
            if ($isDecided) {
                $decidedList[] = $cp->id;
            }

            if ($cp->isSafeThresholdReached()) {
                $safeReachedList[] = $cp->id;
            } else {
                $unreachedList[] = $cp->id;
            }
        }

        $safeReachedCount = count($safeReachedList);
        $unreachedCount = count($unreachedList);
        $decidedCount = count($decidedList);
        $pendingCount = $totalOrders - $decidedCount;

        // Filter Tab
        $currentTab = $request->query('tab', 'all');
        if ($currentTab === 'safe') {
            $query->whereIn('id', $safeReachedList);
        } elseif ($currentTab === 'unreached') {
            $query->whereIn('id', $unreachedList);
        } elseif ($currentTab === 'decided') {
            $query->whereIn('id', $decidedList);
        } elseif ($currentTab === 'pending') {
            $pendingIds = array_diff($allPackagesForStats->pluck('id')->toArray(), $decidedList);
            $query->whereIn('id', $pendingIds);
        }

        $customerPackages = $query->orderBy('consecutive_unpaid_weeks', 'desc')
                                  ->orderBy('id', 'desc')
                                  ->paginate(20)
                                  ->withQueryString();

        // Master Paket untuk tab setting titik aman
        $packages = Package::with(['packageItems.item'])
            ->orderBy('type')
            ->orderBy('id')
            ->get();

        // Master Barang untuk modal pemilihan barang pengganti
        $masterItems = Item::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = Item::distinct()->whereNotNull('category')->pluck('category');

        $agents = \App\Models\User::where('role', 'agent')->orderBy('name')->get();

        // Pengelompokan Konsumen Titik Aman per Agen (Siapa yang lolos vs belum lolos titik aman)
        $agentsGrouped = \App\Models\User::where('role', 'agent')
            ->with([
                'customers' => function ($cq) {
                    $cq->with([
                        'customerPackages' => function ($pq) {
                            $pq->with(['package.packageItems.item', 'packaging', 'partialFulfillment.items.item', 'decidedBy'])
                              ->where('status', '!=', 'canceled')
                              ->orderBy('consecutive_unpaid_weeks', 'desc');
                        }
                    ])->orderBy('name');
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($agent) {
                $safeList = collect();
                $unreachedList = collect();

                foreach ($agent->customers as $cust) {
                    foreach ($cust->customerPackages as $cp) {
                        $cp->customer_name = $cust->name;
                        $cp->customer_phone = $cust->phone;
                        $cp->customer_address = $cust->address;

                        if ($cp->isSafeThresholdReached()) {
                            $safeList->push($cp);
                        } else {
                            $unreachedList->push($cp);
                        }
                    }
                }

                $agent->safe_packages = $safeList;
                $agent->unreached_packages = $unreachedList;
                $agent->safe_count = $safeList->count();
                $agent->unreached_count = $unreachedList->count();
                $agent->total_packages_count = $safeList->count() + $unreachedList->count();
                $agent->safe_percentage = $agent->total_packages_count > 0 
                    ? round(($agent->safe_count / $agent->total_packages_count) * 100, 1) 
                    : 0;

                return $agent;
            });

        return view('owner.book_closing.index', compact(
            'appSetting',
            'customerPackages',
            'packages',
            'masterItems',
            'categories',
            'agents',
            'agentsGrouped',
            'totalOrders',
            'totalTarget',
            'totalPaid',
            'safeReachedCount',
            'unreachedCount',
            'decidedCount',
            'pendingCount',
            'currentTab'
        ));
    }

    /**
     * Tutup Buku Sekarang (Akhiri Periode Parsel)
     */
    public function closeBook(Request $request)
    {
        $setting = CatalogSetting::current();
        $user = Auth::user();

        $setting->is_book_closed = true;
        $setting->book_closed_at = now();
        $setting->book_closed_by = $user->id;
        $setting->book_closing_notes = $request->input('notes') ?: 'Periode Tutup Buku Musim Parsel Lebaran telah resmi diaktifkan oleh ' . $user->name . '.';
        $setting->save();

        return redirect()->route('owner.book-closing.index')
            ->with('success', 'PERIODE TUTUP BUKU BERHASIL DIAKTIFKAN! Musim parsel resmi berakhir. Silakan tentukan keputusan pemenuhan dan siapkan belanja barang.');
    }

    /**
     * Buka Kembali Buku (Re-open)
     */
    public function reopenBook(Request $request)
    {
        $setting = CatalogSetting::current();

        $setting->is_book_closed = false;
        $setting->book_closed_at = null;
        $setting->book_closed_by = null;
        $setting->save();

        return redirect()->route('owner.book-closing.index')
            ->with('warning', 'Tutup Buku telah dibuka kembali. Periode setoran aktif kembali.');
    }

    /**
     * Form / Halaman Detail Keputusan Pemenuhan Konsumen
     */
    public function decision(CustomerPackage $customerPackage)
    {
        $customerPackage->load([
            'customer',
            'package.packageItems.item',
            'agent',
            'packaging',
            'partialFulfillment.items.item',
        ]);

        $masterItems = Item::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = Item::distinct()->whereNotNull('category')->pluck('category');

        return view('owner.book_closing.decision', compact('customerPackage', 'masterItems', 'categories'));
    }

    /**
     * Simpan Keputusan Pemenuhan Tutup Buku Konsumen
     */
    public function storeDecision(Request $request, CustomerPackage $customerPackage)
    {
        $user = Auth::user();

        $request->validate([
            'decision_type' => 'required|in:safe_full,custom_goods,cash_only',
            'cash_payout' => 'nullable|numeric|min:0',
            'admin_fee' => 'nullable|numeric|min:0',
            'grant_bonus' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.is_bonus' => 'nullable|boolean',
        ]);

        $decisionType = $request->input('decision_type');
        $cashPayout = max(0, (float) $request->input('cash_payout', 0));
        $adminFee = max(0, (float) $request->input('admin_fee', 0));
        $totalPaid = (float) $customerPackage->total_paid;
        $grantBonus = $request->boolean('grant_bonus', true);
        $notes = $request->input('notes');

        DB::beginTransaction();
        try {
            // Hapus keputusan lama jika ada
            if ($customerPackage->partialFulfillment) {
                $customerPackage->partialFulfillment->items()->delete();
                $customerPackage->partialFulfillment->delete();
            }

            if ($decisionType === 'safe_full') {
                // KONDISI A: Konsumen Lolos Titik Aman / Diputuskan Full Barang Utuh!
                // Semua barang dalam paket parsel yang dipilih konsumen dijamin diterima secara utuh.
                // Uang tunai ditentukan oleh Owner (default $cashPayout)
                $pkg = $customerPackage->package;
                $totalItemsValue = 0;
                $itemsToCreate = [];

                if ($pkg) {
                    foreach ($pkg->packageItems as $pi) {
                        if (!$pi->item) continue;
                        if ($pi->item->isUang()) continue; // Lewati item uang tunai karena dicover via cash_payout
                        $isBonus = (bool) $pi->is_bonus;
                        // Jika bonus dihanguskan, lewati bonus
                        if ($isBonus && !$grantBonus) continue;

                        $qty = (int) $pi->quantity;
                        $price = (float) $pi->item->unit_price;
                        $sub = $qty * $price;
                        $totalItemsValue += $sub;

                        $itemsToCreate[] = [
                            'item_id' => $pi->item_id,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'subtotal' => $sub,
                            'is_bonus' => $isBonus,
                        ];
                    }
                }

                $fulfillment = PartialFulfillment::create([
                    'customer_package_id' => $customerPackage->id,
                    'total_paid' => $totalPaid,
                    'admin_fee' => $adminFee,
                    'settlement_type' => 'goods',
                    'cash_payout' => $cashPayout,
                    'total_items_value' => $totalItemsValue,
                    'remaining_refund' => 0,
                    'adjusted_by' => $user->id,
                    'notes' => $notes ?: 'Persetujuan Titik Aman Full Parsel SPL: Seluruh barang paket parsel diberikan 100% secara utuh.',
                ]);

                foreach ($itemsToCreate as $it) {
                    $it['partial_fulfillment_id'] = $fulfillment->id;
                    PartialFulfillmentItem::create($it);
                }

                $customerPackage->fulfillment_status = 'full';
                $customerPackage->fulfillment_decision = 'safe_full';
                $customerPackage->decided_at = now();
                $customerPackage->decided_by = $user->id;
                $customerPackage->is_bonus_forfeited = !$grantBonus;
                $customerPackage->fulfillment_notes = 'Tutup Buku: Lolos Titik Aman / Jaminan Full Barang Parsel SPL disetujui' . 
                    ($cashPayout > 0 ? ' + Pencairan Uang Tunai: Rp ' . number_format($cashPayout, 0, ',', '.') : '');
                $customerPackage->save();

            } elseif ($decisionType === 'custom_goods') {
                // KONDISI B: Konsumen Tidak Selesai Stor / Belum Titik Aman / Tanpa Titik Aman
                // Owner menentukan barang apa saja yang diberikan dan berapa uang tunainya.
                $totalItemsValue = 0;
                $itemsToCreate = [];

                if (!empty($request->items)) {
                    foreach ($request->items as $itemInput) {
                        if (empty($itemInput['item_id'])) continue;
                        $item = Item::find($itemInput['item_id']);
                        if (!$item) continue;

                        $qty = max(1, (int) ($itemInput['quantity'] ?? 1));
                        $isBonus = !empty($itemInput['is_bonus']);
                        $price = (float) $item->unit_price;
                        $sub = $qty * $price;
                        $totalItemsValue += $sub;

                        $itemsToCreate[] = [
                            'item_id' => $item->id,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'subtotal' => $sub,
                            'is_bonus' => $isBonus,
                        ];
                    }
                }

                $remainingRefund = max(0, $totalPaid - $totalItemsValue - $cashPayout - $adminFee);

                $fulfillment = PartialFulfillment::create([
                    'customer_package_id' => $customerPackage->id,
                    'total_paid' => $totalPaid,
                    'admin_fee' => $adminFee,
                    'settlement_type' => 'goods',
                    'cash_payout' => $cashPayout,
                    'total_items_value' => $totalItemsValue,
                    'remaining_refund' => $remainingRefund,
                    'adjusted_by' => $user->id,
                    'notes' => $notes ?: 'Penyesuaian Tutup Buku: Keputusan pemilihan barang & dana ditentukan oleh Owner.',
                ]);

                foreach ($itemsToCreate as $it) {
                    $it['partial_fulfillment_id'] = $fulfillment->id;
                    PartialFulfillmentItem::create($it);
                }

                $customerPackage->fulfillment_status = 'partial';
                $customerPackage->fulfillment_decision = 'custom_partial';
                $customerPackage->decided_at = now();
                $customerPackage->decided_by = $user->id;
                $customerPackage->is_bonus_forfeited = !$grantBonus;
                $customerPackage->fulfillment_notes = 'Tutup Buku: Diserahkan ' . count($itemsToCreate) . ' jenis barang senilai Rp ' . number_format($totalItemsValue, 0, ',', '.') . 
                    ($cashPayout > 0 ? ', Uang Tunai: Rp ' . number_format($cashPayout, 0, ',', '.') : '') .
                    ($adminFee > 0 ? ', Potongan Admin: Rp ' . number_format($adminFee, 0, ',', '.') : '');
                $customerPackage->save();

            } elseif ($decisionType === 'cash_only') {
                // KONDISI C: Pencairan Tabungan / Dana Tunai Murni
                $remainingRefund = max(0, $totalPaid - $cashPayout - $adminFee);

                $fulfillment = PartialFulfillment::create([
                    'customer_package_id' => $customerPackage->id,
                    'total_paid' => $totalPaid,
                    'admin_fee' => $adminFee,
                    'settlement_type' => 'cash',
                    'cash_payout' => $cashPayout,
                    'total_items_value' => 0,
                    'remaining_refund' => $remainingRefund,
                    'adjusted_by' => $user->id,
                    'notes' => $notes ?: 'Pencairan dana uang tunai penuh saat Tutup Buku.',
                ]);

                // Jika ada hadiah bonus yang diberikan
                if ($grantBonus && $customerPackage->package) {
                    foreach ($customerPackage->package->bonusItems as $bi) {
                        if ($bi->item) {
                            PartialFulfillmentItem::create([
                                'partial_fulfillment_id' => $fulfillment->id,
                                'item_id' => $bi->item_id,
                                'quantity' => $bi->quantity,
                                'unit_price' => $bi->item->unit_price,
                                'subtotal' => $bi->quantity * $bi->item->unit_price,
                                'is_bonus' => true,
                            ]);
                        }
                    }
                }

                $customerPackage->fulfillment_status = 'partial';
                $customerPackage->fulfillment_decision = 'cash_only';
                $customerPackage->decided_at = now();
                $customerPackage->decided_by = $user->id;
                $customerPackage->is_bonus_forfeited = !$grantBonus;
                $customerPackage->fulfillment_notes = 'Tutup Buku: Pencairan Uang Tunai Rp ' . number_format($cashPayout, 0, ',', '.') . 
                    ($adminFee > 0 ? ' (Potongan Admin: Rp ' . number_format($adminFee, 0, ',', '.') . ')' : '');
                $customerPackage->save();
            }

            DB::commit();

            return redirect()->route('owner.book-closing.index', ['tab' => 'decided'])
                ->with('success', 'Keputusan pemenuhan konsumen ' . ($customerPackage->customer?->name ?? '') . ' berhasil disimpan dan otomatis terintegrasi ke Rekap Belanja Barang!');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan keputusan: ' . $e->getMessage());
        }
    }

    /**
     * 1-Click Setujui Otomatis Seluruh Konsumen yang Lolos Titik Aman
     */
    public function bulkApproveSafe(Request $request)
    {
        $user = Auth::user();

        $candidates = CustomerPackage::with(['package.packageItems.item'])
            ->where('status', '!=', 'canceled')
            ->get();

        $approvedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($candidates as $cp) {
                // Cek apakah mencapai titik aman atau lunas
                if (!$cp->isSafeThresholdReached()) {
                    continue;
                }

                $pkg = $cp->package;
                if (!$pkg) continue;

                // Hapus penyesuaian lama jika ada
                if ($cp->partialFulfillment) {
                    $cp->partialFulfillment->items()->delete();
                    $cp->partialFulfillment->delete();
                }

                $totalItemsValue = 0;
                $itemsToCreate = [];

                foreach ($pkg->packageItems as $pi) {
                    if (!$pi->item) continue;

                    // Lewati item uang tunai karena uang tunai dihitung tersendiri sesuai rekomendasi / setoran
                    if ($pi->item->isUang()) {
                        continue;
                    }

                    $sub = $pi->quantity * $pi->item->unit_price;
                    $totalItemsValue += $sub;

                    $itemsToCreate[] = [
                        'item_id' => $pi->item_id,
                        'quantity' => $pi->quantity,
                        'unit_price' => $pi->item->unit_price,
                        'subtotal' => $sub,
                        'is_bonus' => (bool) $pi->is_bonus,
                    ];
                }

                // Hitung rekomendasi uang tunai sesuai rumus SPL:
                // Sisa dari setoran konsumen setelah menutup total harga semua barang paket
                $cashPayout = $pkg->getRecommendedCashForCustomer((float) $cp->total_paid);

                $fulfillment = PartialFulfillment::create([
                    'customer_package_id' => $cp->id,
                    'total_paid' => $cp->total_paid,
                    'admin_fee' => 0,
                    'settlement_type' => 'goods',
                    'cash_payout' => $cashPayout,
                    'total_items_value' => $totalItemsValue,
                    'remaining_refund' => 0,
                    'adjusted_by' => $user->id,
                    'notes' => 'Persetujuan Otomatis Titik Aman Full Parsel SPL saat Tutup Buku.',
                ]);

                foreach ($itemsToCreate as $it) {
                    $it['partial_fulfillment_id'] = $fulfillment->id;
                    PartialFulfillmentItem::create($it);
                }

                $cp->fulfillment_status = 'full';
                $cp->fulfillment_decision = 'safe_full';
                $cp->decided_at = now();
                $cp->decided_by = $user->id;
                $cp->fulfillment_notes = 'Lolos Titik Aman: 100% Seluruh Barang Paket Parsel SPL Dijamin Diterima Utuh.';
                $cp->save();

                $approvedCount++;
            }

            DB::commit();

            return redirect()->route('owner.book-closing.index', ['tab' => 'decided'])
                ->with('success', "Berhasil menyetujui secara otomatis {$approvedCount} konsumen yang lolos Titik Aman / Lunas! Seluruh barang paket telah dialokasikan ke Belanja Barang.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses persetujuan massal: ' . $e->getMessage());
        }
    }

    /**
     * Update Cepat Titik Aman Masing-Masing Master Paket
     */
    public function updatePackageThresholds(Request $request)
    {
        $request->validate([
            'packages' => 'required|array',
            'packages.*.id' => 'required|exists:packages,id',
            'packages.*.safe_threshold' => 'nullable|numeric|min:0',
        ]);

        $updated = 0;
        foreach ($request->packages as $pkgData) {
            $val = isset($pkgData['safe_threshold']) && $pkgData['safe_threshold'] !== ''
                ? (float) $pkgData['safe_threshold']
                : null;

            Package::where('id', $pkgData['id'])->update([
                'safe_threshold' => $val,
            ]);
            $updated++;
        }

        return redirect()->route('owner.book-closing.index', ['tab' => 'packages'])
            ->with('success', "Berhasil memperbarui Titik Aman Full Parsel untuk {$updated} paket!");
    }

    /**
     * Cetak Berita Acara / Surat Keputusan Penyerahan Tutup Buku
     */
    public function printDecisionReceipt(CustomerPackage $customerPackage)
    {
        $customerPackage->load([
            'customer',
            'package.packageItems.item',
            'agent',
            'packaging',
            'partialFulfillment.items.item',
            'decidedBy',
        ]);

        $appSetting = CatalogSetting::current();

        return view('owner.book_closing.receipt_print', compact('customerPackage', 'appSetting'));
    }
}

