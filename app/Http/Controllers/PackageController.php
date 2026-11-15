<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Item;
use App\Models\Packaging;
use App\Services\ImageOptimizer;

use App\Models\CatalogSetting;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::with(['packageItems.item', 'packagings'])
            ->withCount(['customerPackages' => fn($q) => $q->where('status', '!=', 'canceled')]);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('packageItems.item', function ($iq) use ($search) {
                      $iq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $packages = $query->orderBy('type')->orderBy('name')->get();

        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        $items = Item::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $packagings = Packaging::where('is_active', true)->orderBy('name')->get();
        $setting = CatalogSetting::current();
        $defaultStartDate = $setting->program_start_date ? $setting->program_start_date->format('Y-m-d') : date('Y-m-d');
        $defaultEndDate = $setting->program_end_date ? $setting->program_end_date->format('Y-m-d') : date('Y-m-d', strtotime('+210 days'));

        return view('packages.create', compact('items', 'packagings', 'setting', 'defaultStartDate', 'defaultEndDate'));
    }

    public function store(Request $request)
    {
        if ($request->input('type') === 'ecer') {
            $request->merge([
                'period_type' => 'mingguan',
            ]);
        }

        if ($request->input('type') === 'tabungan' && !$request->filled('name')) {
            $request->merge([
                'name' => 'Tabungan SPL',
            ]);
        }

        // Pendaftaran multi-opsi jenis tabungan dengan nominal stor, nominal didapat, dan bonus masing-masing
        $tabunganTiers = $request->input('tabungan_tiers');
        if ($request->input('type') === 'tabungan' && is_array($tabunganTiers) && !empty($tabunganTiers)) {
            $tiers = $tabunganTiers;
            $periodType = $request->input('period_type', 'mingguan');
            $installmentCount = max(1, (int) ($request->input('installment_count') ?: 30));
            $baseName = trim($request->input('name') ?: 'Tabungan SPL');
            $startDate = $request->filled('start_date') ? $request->input('start_date') : null;
            $endDate = $request->filled('end_date') ? $request->input('end_date') : null;
            $safeThreshold = $request->filled('safe_threshold') ? (float) $request->input('safe_threshold') : null;
            $baseDescription = $request->input('description') ?: 'Paket Tabungan SPL dengan pencairan uang tunai dan hadiah bonus gratis.';
            $imagePath = null;

            if ($request->hasFile('image')) {
                $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'packages');
                $imagePath = $result['path'] ?? null;
            }

            DB::beginTransaction();
            try {
                $createdPackages = [];

                foreach ($tiers as $tIdx => $tier) {
                    $installment = (float) ($tier['installment_amount'] ?? 0);
                    $targetDidapat = (float) ($tier['target_nominal'] ?? 0);
                    if ($installment <= 0 && $targetDidapat <= 0) {
                        continue;
                    }

                    if ($installment <= 0 && $targetDidapat > 0 && $installmentCount > 0) {
                        $installment = Package::roundInstallment($targetDidapat / $installmentCount);
                    } elseif ($targetDidapat <= 0 && $installment > 0) {
                        $targetDidapat = $installment * $installmentCount;
                    }

                    $code = Package::generateNextCode('tabungan');

                    $customTitle = trim($tier['title'] ?? ($tier['name'] ?? ''));
                    if (!empty($customTitle)) {
                        $tierName = $customTitle;
                    } else {
                        $unitLabel = ($periodType === 'harian') ? '/hari' : '/mgg';
                        $tierName = $baseName . ' (Setor Rp ' . number_format($installment, 0, ',', '.') . $unitLabel . ' - Cair Rp ' . number_format($targetDidapat, 0, ',', '.') . ')';
                    }

                    // Kumpulkan nama bonus untuk dicantumkan pada keterangan paket
                    $bonusNames = [];
                    if (!empty($tier['bonuses']) && is_array($tier['bonuses'])) {
                        foreach ($tier['bonuses'] as $b) {
                            $bName = trim($b['item_name'] ?? ($b['name'] ?? ''));
                            $bId = $b['item_id'] ?? null;
                            if (empty($bName) && !empty($bId)) {
                                $existItm = Item::find($bId);
                                if ($existItm) $bName = $existItm->name;
                            }
                            if (!empty($bName)) {
                                $bQty = max(1, (int) ($b['quantity'] ?? ($b['qty'] ?? 1)));
                                $bUnit = trim($b['unit'] ?? 'pcs') ?: 'pcs';
                                $bonusNames[] = $bName . ($bQty > 1 ? " ({$bQty} {$bUnit})" : '');
                            }
                        }
                    }

                    $description = $baseDescription;
                    if (!empty($bonusNames)) {
                        $description .= ' | Hadiah Bonus: ' . implode(', ', $bonusNames);
                    }

                    $pkg = Package::create([
                        'code' => $code,
                        'name' => $tierName,
                        'type' => 'tabungan',
                        'base_price' => $targetDidapat,
                        'target_nominal' => $targetDidapat,
                        'installment_count' => $installmentCount,
                        'installment_amount' => $installment,
                        'admin_fee' => 0,
                        'period_type' => $periodType,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'safe_threshold' => $safeThreshold,
                        'image_path' => $imagePath,
                        'description' => $description,
                        'is_active' => true,
                    ]);

                    // Simpan hadiah bonus untuk jenis tabungan ini
                    if (!empty($tier['bonuses']) && is_array($tier['bonuses'])) {
                        foreach ($tier['bonuses'] as $b) {
                            $itemId = $b['item_id'] ?? null;
                            $itemName = trim($b['item_name'] ?? ($b['name'] ?? ''));
                            $qty = max(1, (int) ($b['quantity'] ?? ($b['qty'] ?? 1)));
                            $unit = trim($b['unit'] ?? 'pcs') ?: 'pcs';

                            if (empty($itemId) && !empty($itemName)) {
                                $existingItem = Item::whereRaw('LOWER(name) = ?', [strtolower($itemName)])->first();
                                if ($existingItem) {
                                    $itemId = $existingItem->id;
                                    if (empty($unit)) $unit = $existingItem->base_unit;
                                } else {
                                    $codeItem = Item::generateNextCode('Hadiah Bonus');
                                    $newItem = Item::create([
                                        'code' => $codeItem,
                                        'name' => $itemName,
                                        'category' => 'Hadiah Bonus',
                                        'base_unit' => $unit,
                                        'wholesale_unit' => 'dus',
                                        'conversion_rate' => 1,
                                        'unit_price' => 0,
                                        'cost_price' => 0,
                                        'wholesale_price' => 0,
                                        'stock_units' => 0,
                                        'is_active' => true,
                                    ]);
                                    $itemId = $newItem->id;
                                }
                            }

                            if (!empty($itemId)) {
                                PackageItem::create([
                                    'package_id' => $pkg->id,
                                    'item_id' => $itemId,
                                    'quantity' => $qty,
                                    'unit' => $unit,
                                    'is_bonus' => true,
                                ]);
                            }
                        }
                    }

                    $createdPackages[] = $pkg;
                }

                if (!empty($createdPackages)) {
                    DB::commit();
                    return redirect()->route('packages.index')
                        ->with('success', 'Berhasil mendaftarkan ' . count($createdPackages) . ' pilihan jenis Tabungan SPL dengan nominal storan, uang tunai cair, dan hadiah bonus masing-masing.');
                }

                DB::rollBack();
            } catch (\Throwable $e) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal menyimpan pilihan jenis tabungan: ' . $e->getMessage());
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:packages,code',
            'type' => 'required|in:parsel,tabungan,ecer',
            'base_price' => 'nullable|numeric|min:0',
            'target_nominal' => 'nullable|numeric|min:0',
            'safe_threshold' => 'nullable|numeric|min:0',
            'installment_count' => 'required|integer|min:1',
            'installment_amount' => 'nullable|numeric|min:0',
            'admin_fee' => 'nullable|numeric|min:0',
            'period_type' => 'required|in:mingguan,harian',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:8192',
            'items' => 'nullable|array',
            'items.*.item_id' => 'nullable',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.qty' => 'nullable|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.installment_amount' => 'nullable|numeric|min:0',
            'items.*.is_bonus' => 'nullable|boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Package::generateNextCode($validated['type']);
        }

        if ($request->hasFile('image')) {
            $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'packages');
            $validated['image_path'] = $result['path'];
        }

        if ($validated['type'] === 'ecer') {
            $validated['period_type'] = 'mingguan';
            $count = max(1, (int) $validated['installment_count']);
            $installmentAmount = (float) ($request->input('installment_amount', 0) ?: 0);
            $targetNominal = (float) ($request->input('target_nominal', 0) ?: 0);

            // Jika installment_amount diisi 0/kosong tetapi ada items dengan installment_amount
            if ($installmentAmount <= 0 && !empty($request->items)) {
                $sumInstallments = 0;
                foreach ($request->items as $it) {
                    $sumInstallments += (float) ($it['installment_amount'] ?? 0);
                }
                if ($sumInstallments > 0) {
                    $installmentAmount = $sumInstallments;
                }
            }

            if ($installmentAmount > 0) {
                $targetNominal = $installmentAmount * $count;
                $basePrice = $targetNominal;
                $adminFee = 0;
                $validated['installment_amount'] = $installmentAmount;
            } elseif ($targetNominal > 0) {
                $validated['installment_amount'] = ($count > 0) ? ($targetNominal / $count) : 0;
                $basePrice = $targetNominal;
                $adminFee = 0;
            } else {
                $basePrice = 0;
                $targetNominal = 0;
                $validated['installment_amount'] = 0;
                $adminFee = (float) ($request->input('admin_fee', 0) ?: 0);
            }
        } else {
            $adminFee = (float) ($request->input('admin_fee', 0) ?: 0);
            $basePrice = $request->filled('base_price') ? (float) $request->input('base_price') : null;
            $targetNominal = (float) ($validated['target_nominal'] ?? 0);

            if ($validated['type'] === 'tabungan' && $targetNominal > 0 && ($basePrice === null || $basePrice <= 0)) {
                $basePrice = $targetNominal;
            } elseif ($basePrice === null && $targetNominal > 0) {
                $basePrice = max(0, $targetNominal - $adminFee);
            } elseif ($basePrice !== null && $targetNominal <= 0) {
                $targetNominal = $basePrice + $adminFee;
            }

            // Jika installment_amount belum diisi atau 0, hitung otomatis dengan pembulatan SPL
            if (empty($validated['installment_amount']) || (float) $validated['installment_amount'] <= 0) {
                $raw = $targetNominal / max(1, (int) $validated['installment_count']);
                $validated['installment_amount'] = Package::roundInstallment($raw);
            }

            if ($validated['type'] === 'tabungan') {
                if ($validated['installment_amount'] <= 0 && $targetNominal <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Nominal setoran dan nominal uang cair untuk paket Tabungan wajib diisi lebih dari 0.');
                }
                if ($validated['name'] === 'Tabungan SPL' || empty($validated['name'])) {
                    $unitLabel = ($validated['period_type'] === 'harian') ? '/hari' : '/mgg';
                    $validated['name'] = 'Tabungan SPL (Setor Rp ' . number_format($validated['installment_amount'], 0, ',', '.') . $unitLabel . ' - Cair Rp ' . number_format($targetNominal, 0, ',', '.') . ')';
                }
            }
        }

        $validated['base_price'] = $basePrice;
        $validated['target_nominal'] = $targetNominal;
        $validated['admin_fee'] = $adminFee;
        $validated['safe_threshold'] = $request->filled('safe_threshold') ? (float)$request->input('safe_threshold') : null;

        DB::beginTransaction();
        try {
            $package = Package::create($validated);

            if (!empty($request->items)) {
                foreach ($request->items as $itemData) {
                    $itemId = $itemData['item_id'] ?? null;
                    $itemName = trim($itemData['item_name'] ?? ($itemData['name'] ?? ''));
                    $qty = (int) ($itemData['quantity'] ?? ($itemData['qty'] ?? 1));

                    // Jika item_id kosong tetapi nama barang diisi (kemudahan input parsel & eceran bagi Owner)
                    if (empty($itemId) && !empty($itemName)) {
                        $existingItem = Item::whereRaw('LOWER(name) = ?', [strtolower($itemName)])->first();
                        if ($existingItem) {
                            $itemId = $existingItem->id;
                        } else {
                            if (preg_match('/(?:uang\s*tunai|uang|cash)[^\d]*([\d\.,]+)/i', $itemName, $matchNom)) {
                                $parsedNominal = (float) preg_replace('/[^\d]/', '', $matchNom[1]);
                                $code = Item::generateNextCode('Uang');
                                $newItem = Item::create([
                                    'code' => $code,
                                    'name' => $itemName,
                                    'category' => 'Uang',
                                    'base_unit' => 'amplop',
                                    'wholesale_unit' => 'amplop',
                                    'conversion_rate' => 1,
                                    'unit_price' => $parsedNominal,
                                    'cost_price' => $parsedNominal,
                                    'wholesale_price' => $parsedNominal,
                                    'stock_units' => 1000,
                                    'is_active' => true,
                                ]);
                            } else {
                                $category = $validated['type'] === 'ecer' ? 'Barang Eceran' : ($validated['type'] === 'tabungan' ? 'Hadiah Bonus' : 'Barang Parsel');
                                $code = Item::generateNextCode($category);
                                $newItem = Item::create([
                                    'code' => $code,
                                    'name' => $itemName,
                                    'category' => $category,
                                    'base_unit' => $itemData['unit'] ?? 'pcs',
                                    'wholesale_unit' => 'dus',
                                    'conversion_rate' => 1,
                                    'unit_price' => 0,
                                    'cost_price' => 0,
                                    'wholesale_price' => 0,
                                    'stock_units' => 0,
                                    'is_active' => true,
                                ]);
                            }
                            $itemId = $newItem->id;
                        }
                    }

                    if (empty($itemId) && !empty($itemData['custom_uang_nominal'])) {
                        $nominal = (float) $itemData['custom_uang_nominal'];
                        $name = !empty($itemData['custom_uang_name']) ? $itemData['custom_uang_name'] : ('Uang Tunai Rp ' . number_format($nominal, 0, ',', '.'));
                        $uangItem = Item::firstOrCreate(
                            ['category' => 'Uang', 'unit_price' => $nominal, 'name' => $name],
                            [
                                'code' => Item::generateNextCode('Uang'),
                                'wholesale_price' => $nominal,
                                'cost_price' => $nominal,
                                'base_unit' => $itemData['custom_uang_unit'] ?? 'amplop',
                                'wholesale_unit' => $itemData['custom_uang_unit'] ?? 'amplop',
                                'conversion_rate' => 1,
                                'stock_units' => 1000,
                                'is_active' => true,
                            ]
                        );
                        $itemId = $uangItem->id;
                    }

                    if (!empty($itemId) && $qty > 0) {
                        $existing = Item::find($itemId);
                        $unit = !empty($itemData['unit']) ? trim($itemData['unit']) : ($existing?->base_unit ?: 'pcs');
                        $itemInstallment = isset($itemData['installment_amount']) ? (float)$itemData['installment_amount'] : 0;

                        PackageItem::create([
                            'package_id' => $package->id,
                            'item_id' => $itemId,
                            'quantity' => $qty,
                            'unit' => $unit,
                            'installment_amount' => $itemInstallment,
                            'is_bonus' => ($validated['type'] === 'tabungan') ? true : !empty($itemData['is_bonus']),
                        ]);
                    }
                }
            }

            // Simpan opsi kemasan yang ditawarkan
            if ($request->has('packagings') && is_array($request->packagings)) {
                $syncData = [];
                foreach ($request->packagings as $pkgId => $pkgData) {
                    if (!empty($pkgData['selected'])) {
                        $syncData[$pkgId] = [
                            'custom_price' => (isset($pkgData['custom_price']) && $pkgData['custom_price'] !== '') ? (float) $pkgData['custom_price'] : null,
                            'is_default' => !empty($pkgData['is_default']),
                        ];
                    }
                }
                $package->packagings()->sync($syncData);
            }

            DB::commit();
            return redirect()->route('packages.index')->with('success', 'Paket baru berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat paket: ' . $e->getMessage()]);
        }
    }

    public function show(Package $package)
    {
        $package->load(['packageItems.item', 'customerPackages.customer', 'packagings']);
        return view('packages.show', compact('package'));
    }

    public function edit(Package $package)
    {
        $package->load(['packageItems.item', 'packagings']);
        $items = Item::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $packagings = Packaging::where('is_active', true)->orderBy('name')->get();
        $setting = CatalogSetting::current();
        $defaultStartDate = $package->start_date ? $package->start_date->format('Y-m-d') : ($setting->program_start_date ? $setting->program_start_date->format('Y-m-d') : date('Y-m-d'));
        $defaultEndDate = $package->end_date ? $package->end_date->format('Y-m-d') : ($setting->program_end_date ? $setting->program_end_date->format('Y-m-d') : date('Y-m-d', strtotime('+210 days')));

        return view('packages.edit', compact('package', 'items', 'packagings', 'setting', 'defaultStartDate', 'defaultEndDate'));
    }

    public function update(Request $request, Package $package)
    {
        if ($request->input('type') === 'ecer' || $package->isEcer()) {
            $request->merge([
                'period_type' => 'mingguan',
            ]);
        }

        if (($request->input('type') === 'tabungan' || $package->isTabungan()) && !$request->filled('name')) {
            $request->merge([
                'name' => 'Tabungan SPL',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:packages,code,' . $package->id,
            'type' => 'required|in:parsel,tabungan,ecer',
            'base_price' => 'nullable|numeric|min:0',
            'target_nominal' => 'nullable|numeric|min:0',
            'safe_threshold' => 'nullable|numeric|min:0',
            'installment_count' => 'required|integer|min:1',
            'installment_amount' => 'nullable|numeric|min:0',
            'admin_fee' => 'nullable|numeric|min:0',
            'period_type' => 'required|in:mingguan,harian',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:8192',
            'is_active' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.item_id' => 'nullable',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.qty' => 'nullable|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.installment_amount' => 'nullable|numeric|min:0',
            'items.*.is_bonus' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_image')) {
            if ($package->image_path && file_exists(public_path($package->image_path))) {
                @unlink(public_path($package->image_path));
            }
            $validated['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            if ($package->image_path && file_exists(public_path($package->image_path))) {
                @unlink(public_path($package->image_path));
            }
            $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'packages');
            $validated['image_path'] = $result['path'];
        }

        if ($validated['type'] === 'ecer') {
            $validated['period_type'] = 'mingguan';
            $count = max(1, (int) $validated['installment_count']);
            $installmentAmount = (float) ($request->input('installment_amount', 0) ?: 0);
            $targetNominal = (float) ($request->input('target_nominal', 0) ?: 0);

            // Jika installment_amount diisi 0/kosong tetapi ada items dengan installment_amount
            if ($installmentAmount <= 0 && !empty($request->items)) {
                $sumInstallments = 0;
                foreach ($request->items as $it) {
                    $sumInstallments += (float) ($it['installment_amount'] ?? 0);
                }
                if ($sumInstallments > 0) {
                    $installmentAmount = $sumInstallments;
                }
            }

            if ($installmentAmount > 0) {
                $targetNominal = $installmentAmount * $count;
                $basePrice = $targetNominal;
                $adminFee = 0;
                $validated['installment_amount'] = $installmentAmount;
            } elseif ($targetNominal > 0) {
                $validated['installment_amount'] = ($count > 0) ? ($targetNominal / $count) : 0;
                $basePrice = $targetNominal;
                $adminFee = 0;
            } else {
                $basePrice = 0;
                $targetNominal = 0;
                $validated['installment_amount'] = 0;
                $adminFee = (float) ($request->input('admin_fee', 0) ?: 0);
            }
        } else {
            $adminFee = (float) ($request->input('admin_fee', 0) ?: 0);
            $basePrice = $request->filled('base_price') ? (float) $request->input('base_price') : null;
            $targetNominal = (float) ($validated['target_nominal'] ?? 0);

            if ($validated['type'] === 'tabungan' && $targetNominal > 0 && ($basePrice === null || $basePrice <= 0)) {
                $basePrice = $targetNominal;
            } elseif ($basePrice === null && $targetNominal > 0) {
                $basePrice = max(0, $targetNominal - $adminFee);
            } elseif ($basePrice !== null && $targetNominal <= 0) {
                $targetNominal = $basePrice + $adminFee;
            }

            // Jika installment_amount belum diisi atau 0, hitung otomatis dengan pembulatan SPL
            if (empty($validated['installment_amount']) || (float) $validated['installment_amount'] <= 0) {
                $raw = $targetNominal / max(1, (int) $validated['installment_count']);
                $validated['installment_amount'] = Package::roundInstallment($raw);
            }

            if ($validated['type'] === 'tabungan') {
                if ($validated['installment_amount'] <= 0 && $targetNominal <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Nominal setoran dan nominal uang cair untuk paket Tabungan wajib diisi lebih dari 0.');
                }
                if ($validated['name'] === 'Tabungan SPL' || empty($validated['name'])) {
                    $unitLabel = ($validated['period_type'] === 'harian') ? '/hari' : '/mgg';
                    $validated['name'] = 'Tabungan SPL (Setor Rp ' . number_format($validated['installment_amount'], 0, ',', '.') . $unitLabel . ' - Cair Rp ' . number_format($targetNominal, 0, ',', '.') . ')';
                }
            }
        }

        $validated['base_price'] = $basePrice;
        $validated['target_nominal'] = $targetNominal;
        $validated['admin_fee'] = $adminFee;
        $validated['safe_threshold'] = $request->filled('safe_threshold') ? (float)$request->input('safe_threshold') : null;

        DB::beginTransaction();
        try {
            $package->update($validated);

            // Perbarui item paket
            $package->packageItems()->delete();
            if (!empty($request->items)) {
                foreach ($request->items as $itemData) {
                    $itemId = $itemData['item_id'] ?? null;
                    $itemName = trim($itemData['item_name'] ?? ($itemData['name'] ?? ''));
                    $qty = (int) ($itemData['quantity'] ?? ($itemData['qty'] ?? 1));

                    // Jika item_id kosong tapi nama barang diisi
                    if (empty($itemId) && !empty($itemName)) {
                        $existingItem = Item::whereRaw('LOWER(name) = ?', [strtolower($itemName)])->first();
                        if ($existingItem) {
                            $itemId = $existingItem->id;
                        } else {
                            if (preg_match('/(?:uang\s*tunai|uang|cash)[^\d]*([\d\.,]+)/i', $itemName, $matchNom)) {
                                $parsedNominal = (float) preg_replace('/[^\d]/', '', $matchNom[1]);
                                $code = Item::generateNextCode('Uang');
                                $newItem = Item::create([
                                    'code' => $code,
                                    'name' => $itemName,
                                    'category' => 'Uang',
                                    'base_unit' => 'amplop',
                                    'wholesale_unit' => 'amplop',
                                    'conversion_rate' => 1,
                                    'unit_price' => $parsedNominal,
                                    'cost_price' => $parsedNominal,
                                    'wholesale_price' => $parsedNominal,
                                    'stock_units' => 1000,
                                    'is_active' => true,
                                ]);
                            } else {
                                $category = $validated['type'] === 'ecer' ? 'Barang Eceran' : ($validated['type'] === 'tabungan' ? 'Hadiah Bonus' : 'Barang Parsel');
                                $code = Item::generateNextCode($category);
                                $newItem = Item::create([
                                    'code' => $code,
                                    'name' => $itemName,
                                    'category' => $category,
                                    'base_unit' => $itemData['unit'] ?? 'pcs',
                                    'wholesale_unit' => 'dus',
                                    'conversion_rate' => 1,
                                    'unit_price' => 0,
                                    'cost_price' => 0,
                                    'wholesale_price' => 0,
                                    'stock_units' => 0,
                                    'is_active' => true,
                                ]);
                            }
                            $itemId = $newItem->id;
                        }
                    }

                    if (empty($itemId) && !empty($itemData['custom_uang_nominal'])) {
                        $nominal = (float) $itemData['custom_uang_nominal'];
                        $name = !empty($itemData['custom_uang_name']) ? $itemData['custom_uang_name'] : ('Uang Tunai Rp ' . number_format($nominal, 0, ',', '.'));
                        $uangItem = Item::firstOrCreate(
                            ['category' => 'Uang', 'unit_price' => $nominal, 'name' => $name],
                            [
                                'code' => Item::generateNextCode('Uang'),
                                'wholesale_price' => $nominal,
                                'cost_price' => $nominal,
                                'base_unit' => $itemData['custom_uang_unit'] ?? 'amplop',
                                'wholesale_unit' => $itemData['custom_uang_unit'] ?? 'amplop',
                                'conversion_rate' => 1,
                                'stock_units' => 1000,
                                'is_active' => true,
                            ]
                        );
                        $itemId = $uangItem->id;
                    }

                    if (!empty($itemId) && $qty > 0) {
                        $existing = Item::find($itemId);
                        $unit = !empty($itemData['unit']) ? trim($itemData['unit']) : ($existing?->base_unit ?: 'pcs');
                        $itemInstallment = isset($itemData['installment_amount']) ? (float)$itemData['installment_amount'] : 0;

                        PackageItem::create([
                            'package_id' => $package->id,
                            'item_id' => $itemId,
                            'quantity' => $qty,
                            'unit' => $unit,
                            'installment_amount' => $itemInstallment,
                            'is_bonus' => ($validated['type'] === 'tabungan') ? true : !empty($itemData['is_bonus']),
                        ]);
                    }
                }
            }

            // Perbarui opsi kemasan paket
            if ($request->has('packagings') && is_array($request->packagings)) {
                $syncData = [];
                foreach ($request->packagings as $pkgId => $pkgData) {
                    if (!empty($pkgData['selected'])) {
                        $syncData[$pkgId] = [
                            'custom_price' => (isset($pkgData['custom_price']) && $pkgData['custom_price'] !== '') ? (float) $pkgData['custom_price'] : null,
                            'is_default' => !empty($pkgData['is_default']),
                        ];
                    }
                }
                $package->packagings()->sync($syncData);
            } else {
                $package->packagings()->detach();
            }

            DB::commit();
            return redirect()->route('packages.index')->with('success', 'Paket berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui paket: ' . $e->getMessage()]);
        }
    }

    public function destroy(Package $package)
    {
        $packageName = $package->name;
        $isTabungan = $package->isTabungan();
        $affectedCount = $package->customerPackages()->count();

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($affectedCount > 0) {
                // Proses konsumen yang terdaftar di paket ini
                foreach ($package->customerPackages as $cp) {
                    $paidPayments = $cp->payments()->where('is_paid', true)->get();
                    $hasPaid = $paidPayments->isNotEmpty();

                    if ($hasPaid) {
                        // Jika sudah ada cicilan yang disetor:
                        // 1. Hapus kotak tagihan yang belum terbayar
                        $cp->payments()->where('is_paid', false)->delete();
                        // 2. Kosongkan package_id dan packaging_id, beri catatan resmi
                        $cp->update([
                            'package_id' => null,
                            'packaging_id' => null,
                            'status' => 'active',
                            'fulfillment_notes' => 'Pilihan ' . ($isTabungan ? 'tabungan' : 'paket') . ' sebelumnya (' . $packageName . ') dibatalkan karena paket dihapus oleh Owner. Dana setoran Rp ' . number_format($cp->total_paid, 0, ',', '.') . ' tersimpan aman. Konsumen wajib memilih paket pengganti.',
                        ]);
                    } else {
                        // Jika belum ada setoran yang masuk:
                        // Hapus seluruh tagihan kosong & hapus relasi customerPackage
                        $cp->payments()->delete();
                        $cp->customItems()->delete();
                        $cp->delete();
                    }
                }
            }

            if ($package->image_path && file_exists(public_path($package->image_path))) {
                @unlink(public_path($package->image_path));
            }

            $package->packagings()->detach();
            $package->packageItems()->delete();
            $package->delete();

            \Illuminate\Support\Facades\DB::commit();

            if ($affectedCount > 0) {
                $message = "Paket \"{$packageName}\" berhasil dihapus. Sebanyak {$affectedCount} konsumen yang sebelumnya memilih " . ($isTabungan ? 'tabungan' : 'paket') . " ini otomatis dibatalkan pilihannya dan wajib memilih paket kembali.";
            } else {
                $message = "Paket \"{$packageName}\" berhasil dihapus.";
            }

            return redirect()->route('packages.index')->with('success', $message);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }

    /**
     * Hapus sekaligus banyak paket yang dipilih oleh Admin / Owner
     */
    public function bulkDestroy(Request $request)
    {
        if (!auth()->user()->isOwnerOrAdmin()) {
            abort(403, 'Akses ditolak. Fitur hapus sekaligus hanya untuk Admin dan Owner.');
        }

        $packageIds = $request->input('package_ids', []);
        if (is_string($packageIds)) {
            $packageIds = explode(',', $packageIds);
        }
        $packageIds = array_filter(array_map('intval', (array) $packageIds));

        if (empty($packageIds)) {
            return redirect()->route('packages.index')->with('error', 'Tidak ada paket yang dipilih untuk dihapus.');
        }

        $packages = Package::whereIn('id', $packageIds)->get();
        if ($packages->isEmpty()) {
            return redirect()->route('packages.index')->with('error', 'Paket yang dipilih tidak ditemukan.');
        }

        $deletedCount = 0;
        $totalAffectedCustomers = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($packages as $package) {
                $packageName = $package->name;
                $isTabungan = $package->isTabungan();
                $affectedCount = $package->customerPackages()->count();

                if ($affectedCount > 0) {
                    $totalAffectedCustomers += $affectedCount;
                    foreach ($package->customerPackages as $cp) {
                        $paidPayments = $cp->payments()->where('is_paid', true)->get();
                        $hasPaid = $paidPayments->isNotEmpty();

                        if ($hasPaid) {
                            $cp->payments()->where('is_paid', false)->delete();
                            $cp->update([
                                'package_id' => null,
                                'packaging_id' => null,
                                'status' => 'active',
                                'fulfillment_notes' => 'Pilihan ' . ($isTabungan ? 'tabungan' : 'paket') . ' sebelumnya (' . $packageName . ') dibatalkan karena paket dihapus massal oleh Admin/Owner. Dana setoran Rp ' . number_format($cp->total_paid, 0, ',', '.') . ' tersimpan aman. Konsumen wajib memilih paket pengganti.',
                            ]);
                        } else {
                            $cp->payments()->delete();
                            $cp->customItems()->delete();
                            $cp->delete();
                        }
                    }
                }

                if ($package->image_path && file_exists(public_path($package->image_path))) {
                    @unlink(public_path($package->image_path));
                }

                $package->packagings()->detach();
                $package->packageItems()->delete();
                $package->delete();

                $deletedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            $message = "Berhasil menghapus {$deletedCount} paket sekaligus.";
            if ($totalAffectedCustomers > 0) {
                $message .= " Sebanyak {$totalAffectedCustomers} konsumen terkait otomatis dibatalkan pilihannya dan diarahkan untuk memilih paket pengganti (saldo tersimpan aman).";
            }

            return redirect()->route('packages.index')->with('success', $message);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('packages.index')->with('error', 'Gagal menghapus paket sekaligus: ' . $e->getMessage());
        }
    }

    /**
     * Download format / template import data paket (.xlsx atau .csv)
     * Menyediakan contoh lengkap untuk 5 varian:
     * 1. Parsel Mingguan
     * 2. Parsel Harian
     * 3. Tabungan Mingguan
     * 4. Tabungan Harian
     * 5. Eceran (Bebas Rakit)
     */
    public function downloadImportTemplate(Request $request, \App\Services\SimpleExcelHelper $excelHelper)
    {
        $type = strtolower($request->query('type', 'all'));
        $format = strtolower($request->query('format', 'xlsx'));

        $headers = [
            'Kode Paket (Opsional)',
            'Nama Paket*',
            'Jenis Paket* (parsel/tabungan/ecer)',
            'Skema Periode* (mingguan/harian)',
            'Jumlah Angsuran*',
            'Nominal Angsuran (Rp)*',
            'Total Target / Uang Cair (Rp)*',
            'Biaya Admin (Rp)',
            'Titik Aman Full Parsel (Rp)',
            'Daftar Barang / Hadiah Bonus (Opsional)',
            'Keterangan / Deskripsi',
            'Status (Aktif/Nonaktif)',
        ];

        $allSamples = [
            'parsel_mingguan' => [
                'PAR-M01',
                'Paket Parsel Silver Lebaran (Mingguan)',
                'parsel',
                'mingguan',
                '30',
                '25000',
                '750000',
                '0',
                '600000',
                'Beras 2.5 Kg (1), Minyak Goreng 2L (1), Gula Pasir 1 Kg (1), Sirup Marjan (2), Biskuit Khong Guan (1)',
                'Paket parsel sembako & biskuit mingguan (30x tagih @ Rp 25.000).',
                'Aktif',
            ],
            'parsel_harian' => [
                'PAR-H01',
                'Paket Parsel Sultan Berkah (Harian)',
                'parsel',
                'harian',
                '100',
                '7500',
                '750000',
                '0',
                '600000',
                'Beras 2.5 Kg (1), Minyak Goreng 2L (1), Sirup Marjan (2), Biskuit Kaleng (1)',
                'Paket parsel harian praktis (100 hari @ Rp 7.500).',
                'Aktif',
            ],
            'tabungan_mingguan' => [
                'TAB-M01',
                'Tabungan SPL Lebaran (Mingguan)',
                'tabungan',
                'mingguan',
                '30',
                '10000',
                '300000',
                '0',
                '',
                'Minyak Goreng 2L (1), Sirup Marjan (1)',
                'Nabung Rp 10.000/minggu x 30 minggu. Lebaran cair tunai Rp 300.000 + hadiah sembako gratis!',
                'Aktif',
            ],
            'tabungan_harian' => [
                'TAB-H01',
                'Tabungan SPL Berkah (Harian)',
                'tabungan',
                'harian',
                '100',
                '5000',
                '500000',
                '0',
                '',
                'Minyak Goreng 2L (1), Gula Pasir 1 Kg (1)',
                'Nabung Rp 5.000/hari x 100 hari. Lebaran cair tunai Rp 500.000 + hadiah sembako gratis!',
                'Aktif',
            ],
            'ecer_mingguan' => [
                'ECR-01',
                'Paket Eceran Mandiri Bebas Rakit (Mingguan)',
                'ecer',
                'mingguan',
                '30',
                '10000',
                '300000',
                '0',
                '',
                '',
                'Paket eceran mingguan rakitan bebas pilihan konsumen.',
                'Aktif',
            ],
            'ecer_harian' => [
                'ECR-H01',
                'Paket Eceran Harian Bebas Rakit (Harian)',
                'ecer',
                'harian',
                '100',
                '5000',
                '500000',
                '0',
                '',
                '',
                'Paket eceran harian rakitan bebas pilihan konsumen.',
                'Aktif',
            ],
        ];

        if ($type === 'parsel') {
            $sampleRows = [$allSamples['parsel_mingguan'], $allSamples['parsel_harian']];
            $title = 'TEMPLATE IMPORT PAKET PARSEL (HARIAN & MINGGUAN)';
            $filename = 'Template_Import_Paket_Parsel_SPL';
        } elseif ($type === 'tabungan') {
            $sampleRows = [$allSamples['tabungan_mingguan'], $allSamples['tabungan_harian']];
            $title = 'TEMPLATE IMPORT PAKET TABUNGAN (HARIAN & MINGGUAN)';
            $filename = 'Template_Import_Paket_Tabungan_SPL';
        } elseif ($type === 'ecer') {
            $sampleRows = [$allSamples['ecer_mingguan'], $allSamples['ecer_harian']];
            $title = 'TEMPLATE IMPORT PAKET ECERAN (BEBAS RAKIT)';
            $filename = 'Template_Import_Paket_Eceran_SPL';
        } else {
            $sampleRows = array_values($allSamples);
            $title = 'TEMPLATE IMPORT MASTER DATA PAKET SPL (LENGKAP SEMUA VARIAN)';
            $filename = 'Template_Import_Paket_Lengkap_SPL';
        }

        $colWidths = [
            1 => 18,
            2 => 38,
            3 => 22,
            4 => 20,
            5 => 18,
            6 => 24,
            7 => 28,
            8 => 18,
            9 => 24,
            10 => 45,
            11 => 45,
            12 => 16,
        ];

        if ($format === 'csv') {
            $csv = $excelHelper->createCsv($headers, $sampleRows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            ]);
        }

        $xlsx = $excelHelper->createXlsx(
            'TEMPLATE IMPORT PAKET',
            $headers,
            $sampleRows,
            $title,
            'Petunjuk: Kolom bertanda (*) wajib diisi. Jenis Paket: "parsel", "tabungan", atau "ecer". Skema: "mingguan" atau "harian". Barang/Bonus dapat diisi koma-separated: misal Minyak Goreng 2L (1), Sirup (2).',
            $colWidths
        );

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
        ]);
    }

    /**
     * Proses import data paket dari file Excel (.xlsx) atau CSV
     */
    public function import(Request $request, \App\Services\SimpleExcelHelper $excelHelper)
    {
        if (!auth()->user()->isOwnerOrAdmin()) {
            abort(403, 'Akses ditolak. Fitur import paket hanya dapat dilakukan oleh Admin dan Owner.');
        }

        $request->validate([
            'file' => 'required|file|max:10240',
            'duplicate_action' => 'nullable|string|in:update,skip',
        ]);

        $file = $request->file('file');
        $duplicateAction = $request->input('duplicate_action', 'update');

        try {
            $rows = $excelHelper->parseFile($file->getPathname(), $file->getClientOriginalName());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File yang diunggah kosong atau tidak memiliki data.');
        }

        $headerRowIdx = -1;
        foreach ($rows as $idx => $row) {
            $lineStr = strtolower(implode(' ', $row));
            if (str_contains($lineStr, 'nama') && (str_contains($lineStr, 'jenis') || str_contains($lineStr, 'tipe') || str_contains($lineStr, 'angsuran') || str_contains($lineStr, 'kode'))) {
                $headerRowIdx = $idx;
                break;
            }
        }

        if ($headerRowIdx === -1) {
            $headerRowIdx = 0;
        }

        // Petakan kolom secara dinamis berdasarkan nama header
        $headerMap = [
            'code' => 0,
            'name' => 1,
            'type' => 2,
            'period_type' => 3,
            'installment_count' => 4,
            'installment_amount' => 5,
            'target_nominal' => 6,
            'admin_fee' => 7,
            'safe_threshold' => 8,
            'items' => 9,
            'description' => 10,
            'status' => 11,
        ];

        if (isset($rows[$headerRowIdx])) {
            foreach ($rows[$headerRowIdx] as $colIdx => $colName) {
                $c = strtolower(trim((string)$colName));
                if (str_contains($c, 'kode')) {
                    $headerMap['code'] = $colIdx;
                } elseif (str_contains($c, 'nama')) {
                    $headerMap['name'] = $colIdx;
                } elseif (str_contains($c, 'jenis') || str_contains($c, 'tipe')) {
                    $headerMap['type'] = $colIdx;
                } elseif (str_contains($c, 'skema') || str_contains($c, 'periode')) {
                    $headerMap['period_type'] = $colIdx;
                } elseif (str_contains($c, 'jumlah') && (str_contains($c, 'angsuran') || str_contains($c, 'cicilan') || str_contains($c, 'kali') || str_contains($c, 'minggu') || str_contains($c, 'hari'))) {
                    $headerMap['installment_count'] = $colIdx;
                } elseif (str_contains($c, 'nominal') || str_contains($c, 'setoran') || (str_contains($c, 'angsuran') && !isset($headerMap['installment_amount']))) {
                    $headerMap['installment_amount'] = $colIdx;
                } elseif (str_contains($c, 'target') || str_contains($c, 'cair') || str_contains($c, 'pokok')) {
                    $headerMap['target_nominal'] = $colIdx;
                } elseif (str_contains($c, 'admin')) {
                    $headerMap['admin_fee'] = $colIdx;
                } elseif (str_contains($c, 'aman') || str_contains($c, 'threshold')) {
                    $headerMap['safe_threshold'] = $colIdx;
                } elseif (str_contains($c, 'barang') || str_contains($c, 'bonus') || str_contains($c, 'isi') || str_contains($c, 'hadiah')) {
                    $headerMap['items'] = $colIdx;
                } elseif (str_contains($c, 'keterangan') || str_contains($c, 'deskripsi') || str_contains($c, 'catatan')) {
                    $headerMap['description'] = $colIdx;
                } elseif (str_contains($c, 'status') || str_contains($c, 'aktif')) {
                    $headerMap['status'] = $colIdx;
                }
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $dataRows = array_slice($rows, $headerRowIdx + 1);

        foreach ($dataRows as $lineNo => $row) {
            $realLineNumber = $headerRowIdx + $lineNo + 2;

            $code = trim($row[$headerMap['code']] ?? '');
            $name = trim($row[$headerMap['name']] ?? '');
            $typeStr = strtolower(trim($row[$headerMap['type']] ?? 'parsel'));
            $periodTypeStr = strtolower(trim($row[$headerMap['period_type']] ?? 'mingguan'));
            $installmentCount = (int) preg_replace('/[^0-9]/', '', (string)($row[$headerMap['installment_count']] ?? 30));
            $installmentAmount = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($row[$headerMap['installment_amount']] ?? 0)));
            $targetNominal = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($row[$headerMap['target_nominal']] ?? 0)));
            $adminFee = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($row[$headerMap['admin_fee']] ?? 0)));
            
            $safeVal = trim($row[$headerMap['safe_threshold']] ?? '');
            $itemsStr = trim($row[$headerMap['items']] ?? '');
            $description = trim($row[$headerMap['description']] ?? '');
            $statusStr = strtolower(trim($row[$headerMap['status']] ?? 'aktif'));

            if ($name === '' && $code === '') {
                continue;
            }

            if ($name === '') {
                $errors[] = "Baris {$realLineNumber}: Nama paket tidak boleh kosong.";
                $skipped++;
                continue;
            }

            $type = 'parsel';
            if (str_contains($typeStr, 'tabung')) {
                $type = 'tabungan';
            } elseif (str_contains($typeStr, 'ecer')) {
                $type = 'ecer';
            }

            $periodType = str_contains($periodTypeStr, 'hari') ? 'harian' : 'mingguan';

            if ($installmentCount <= 0) {
                $installmentCount = ($periodType === 'harian') ? 100 : 30;
            }

            if ($targetNominal <= 0 && $installmentAmount > 0) {
                $targetNominal = $installmentCount * $installmentAmount;
            }

            if ($installmentAmount <= 0 && $targetNominal > 0 && $installmentCount > 0) {
                $installmentAmount = $targetNominal / $installmentCount;
            }

            $basePrice = max(0, $targetNominal - $adminFee);
            $isActive = !in_array($statusStr, ['nonaktif', 'tidak aktif', '0', 'false', 'inactive']);

            $safeThreshold = null;
            if ($type === 'parsel' && $safeVal !== '') {
                $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $safeVal));
                if (is_numeric($cleaned) && (float)$cleaned > 0) {
                    $safeThreshold = (float)$cleaned;
                }
            }

            if ($code === '') {
                $code = Package::generateNextCode($type);
            }

            $existing = Package::where('code', $code)->first();
            $package = null;

            if ($existing) {
                if ($duplicateAction === 'skip') {
                    $skipped++;
                    continue;
                }

                $existing->update([
                    'name' => $name,
                    'type' => $type,
                    'period_type' => $periodType,
                    'installment_count' => $installmentCount,
                    'installment_amount' => $installmentAmount,
                    'target_nominal' => $targetNominal,
                    'safe_threshold' => $safeThreshold !== null ? $safeThreshold : $existing->safe_threshold,
                    'base_price' => $basePrice,
                    'admin_fee' => $adminFee,
                    'description' => $description ?: $existing->description,
                    'is_active' => $isActive,
                ]);

                $package = $existing;
                $updated++;
            } else {
                $package = Package::create([
                    'code' => $code,
                    'name' => $name,
                    'type' => $type,
                    'period_type' => $periodType,
                    'installment_count' => $installmentCount,
                    'installment_amount' => $installmentAmount,
                    'target_nominal' => $targetNominal,
                    'safe_threshold' => $safeThreshold,
                    'base_price' => $basePrice,
                    'admin_fee' => $adminFee,
                    'description' => $description ?: null,
                    'is_active' => $isActive,
                ]);

                $created++;
            }

            // Parse daftar barang atau hadiah bonus jika diisi di file import
            if ($package && !empty($itemsStr)) {
                $entries = array_filter(array_map('trim', explode(',', $itemsStr)));
                foreach ($entries as $entry) {
                    if ($entry === '') continue;
                    $qty = 1;
                    $unit = 'pcs';
                    $itemName = $entry;

                    if (preg_match('/^(\d+)\s*(?:x|buah|bks|pcs|kg|liter|dus|kaleng)?\s+(.+)$/i', $entry, $m)) {
                        $qty = max(1, (int)$m[1]);
                        $itemName = trim($m[2]);
                    } elseif (preg_match('/^(.*?)\s*\(\s*(\d+)(?:\s*([a-zA-Z]+))?\s*\)$/', $entry, $m)) {
                        $itemName = trim($m[1]);
                        $qty = max(1, (int)$m[2]);
                        if (!empty($m[3])) $unit = trim($m[3]);
                    }

                    if ($itemName === '') continue;

                    $item = Item::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($itemName)])->first();
                    if (!$item) {
                        $category = ($type === 'tabungan') ? 'Hadiah Bonus' : ($type === 'ecer' ? 'Barang Eceran' : 'Barang Parsel');
                        $itemCode = Item::generateNextCode($category);
                        $item = Item::create([
                            'code' => $itemCode,
                            'name' => $itemName,
                            'category' => $category,
                            'base_unit' => $unit ?: 'pcs',
                            'wholesale_unit' => 'dus',
                            'conversion_rate' => 1,
                            'unit_price' => 0,
                            'cost_price' => 0,
                            'wholesale_price' => 0,
                            'stock_units' => 0,
                            'is_active' => true,
                        ]);
                    }

                    // Hubungkan ke PackageItem jika belum ada di paket ini
                    PackageItem::firstOrCreate([
                        'package_id' => $package->id,
                        'item_id' => $item->id,
                    ], [
                        'quantity' => $qty,
                        'unit' => $item->base_unit ?: $unit,
                        'installment_amount' => 0,
                        'is_bonus' => ($type === 'tabungan'),
                    ]);
                }
            }
        }

        $msg = "Import data paket selesai! Berhasil menambahkan {$created} paket baru";
        if ($updated > 0) {
            $msg .= ", memperbarui {$updated} paket";
        }
        if ($skipped > 0) {
            $msg .= ", melewati {$skipped} paket";
        }
        $msg .= ".";

        if (!empty($errors)) {
            return redirect()->route('packages.index')
                ->with('success', $msg)
                ->with('warning', 'Beberapa baris dilewati: ' . implode(' | ', array_slice($errors, 0, 3)));
        }

        return redirect()->route('packages.index')->with('success', $msg);
    }
}

