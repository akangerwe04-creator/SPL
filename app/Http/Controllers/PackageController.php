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
        if ($request->input('type') === 'tabungan' && $request->filled('tabungan_tiers') && is_array($request->input('tabungan_tiers'))) {
            $tiers = $request->input('tabungan_tiers');
            $periodType = $request->input('period_type', 'mingguan');
            $installmentCount = max(1, (int) ($request->input('installment_count') ?: 30));
            $baseName = trim($request->input('name') ?: 'Tabungan SPL');
            $startDate = $request->filled('start_date') ? $request->input('start_date') : null;
            $endDate = $request->filled('end_date') ? $request->input('end_date') : null;
            $safeThreshold = $request->filled('safe_threshold') ? (float) $request->input('safe_threshold') : null;
            $description = $request->input('description') ?: 'Paket Tabungan SPL dengan pencairan uang tunai dan hadiah bonus gratis.';
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
                        $tierName = count($tiers) > 1 
                            ? ($baseName . ' (Setor Rp ' . number_format($installment, 0, ',', '.') . ' - Cair Rp ' . number_format($targetDidapat, 0, ',', '.') . ')')
                            : $baseName;
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
        if ($package->customerPackages()->exists()) {
            return back()->with('error', 'Paket ini tidak dapat dihapus karena sudah ada konsumen yang mendaftar paket ini.');
        }

        if ($package->image_path && file_exists(public_path($package->image_path))) {
            @unlink(public_path($package->image_path));
        }

        $package->packageItems()->delete();
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dihapus.');
    }

    /**
     * Download format / template import data paket (.xlsx atau .csv)
     */
    public function downloadImportTemplate(Request $request, \App\Services\SimpleExcelHelper $excelHelper)
    {
        $format = strtolower($request->query('format', 'xlsx'));

        $headers = [
            'Kode Paket (Opsional)',
            'Nama Paket*',
            'Jenis Paket* (parsel/tabungan/ecer)',
            'Skema Periode* (mingguan/harian)',
            'Jumlah Angsuran*',
            'Nominal Angsuran (Rp)*',
            'Total Target Paket (Rp)',
            'Biaya Admin (Rp)',
            'Titik Aman Full Parsel (Rp)',
            'Keterangan / Deskripsi',
            'Status (Aktif/Nonaktif)',
        ];

        $sampleRows = [
            [
                'PAR-01',
                'Paket Parsel Silver Lebaran',
                'parsel',
                'mingguan',
                '30',
                '25000',
                '750000',
                '0',
                '600000',
                'Paket parsel sembako dan biskuit kaleng premium pilihan.',
                'Aktif',
            ],
            [
                'PAR-02',
                'Paket Parsel Gold Sultan',
                'parsel',
                'mingguan',
                '30',
                '50000',
                '1500000',
                '0',
                '1200000',
                'Paket parsel super mewah kaleng biskuit impor + sirup + kurma tunis.',
                'Aktif',
            ],
            [
                'TAB-01',
                'Paket Tabungan Uang Tunai 1 Juta',
                'tabungan',
                'mingguan',
                '30',
                '35000',
                '1000000',
                '0',
                '',
                'Nabung Rp 35rb/minggu, saat lebaran cair uang tunai Rp 1.000.000 + hadiah sembako.',
                'Aktif',
            ],
            [
                'ECR-01',
                'Paket Eceran Mandiri Bebas Rakit',
                'ecer',
                'mingguan',
                '30',
                '0',
                '0',
                '0',
                '',
                'Paket rakitan pilihan eceran bebas sesuai keinginan konsumen.',
                'Aktif',
            ],
        ];

        $colWidths = [
            1 => 18,
            2 => 34,
            3 => 24,
            4 => 20,
            5 => 18,
            6 => 24,
            7 => 24,
            8 => 18,
            9 => 24,
            10 => 40,
            11 => 16,
        ];

        if ($format === 'csv') {
            $csv = $excelHelper->createCsv($headers, $sampleRows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="Template_Import_Paket_SPL.csv"',
            ]);
        }

        $xlsx = $excelHelper->createXlsx(
            'TEMPLATE IMPORT PAKET',
            $headers,
            $sampleRows,
            'TEMPLATE IMPORT MASTER DATA PAKET SPL (PARSEL, TABUNGAN, ECER)',
            'Petunjuk: Kolom bertanda (*) wajib diisi. Jenis Paket isi: "parsel", "tabungan", atau "ecer". Skema: "mingguan" atau "harian".',
            $colWidths
        );

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Template_Import_Paket_SPL.xlsx"',
        ]);
    }

    /**
     * Proses import data paket dari file Excel (.xlsx) atau CSV
     */
    public function import(Request $request, \App\Services\SimpleExcelHelper $excelHelper)
    {
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

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $dataRows = array_slice($rows, $headerRowIdx + 1);

        foreach ($dataRows as $lineNo => $row) {
            $realLineNumber = $headerRowIdx + $lineNo + 2;

            $code = trim($row[0] ?? '');
            $name = trim($row[1] ?? '');
            $typeStr = strtolower(trim($row[2] ?? 'parsel'));
            $periodTypeStr = strtolower(trim($row[3] ?? 'mingguan'));
            $installmentCount = (int) preg_replace('/[^0-9]/', '', (string)($row[4] ?? 30));
            $installmentAmount = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($row[5] ?? 0)));
            $targetNominal = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($row[6] ?? 0)));
            $adminFee = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($row[7] ?? 0)));
            
            $safeThreshold = null;
            if (count($row) >= 11) {
                $safeVal = trim($row[8] ?? '');
                $safeThreshold = ($safeVal !== '' && is_numeric(str_replace(['.', ','], '', $safeVal)))
                    ? (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $safeVal))
                    : null;
                $description = trim($row[9] ?? '');
                $statusStr = strtolower(trim($row[10] ?? 'aktif'));
            } else {
                $description = trim($row[8] ?? '');
                $statusStr = strtolower(trim($row[9] ?? 'aktif'));
            }

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

            if ($code === '') {
                $prefix = strtoupper(substr($type, 0, 3));
                $count = Package::where('type', $type)->count() + $created + 1;
                $code = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            $existing = Package::where('code', $code)->first();

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

                $updated++;
            } else {
                Package::create([
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

