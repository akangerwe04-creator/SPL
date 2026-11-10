<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Services\ImageOptimizer;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Item::distinct()->whereNotNull('category')->pluck('category');

        return view('items.index', compact('items', 'categories'));
    }

    public function create(Request $request)
    {
        $categories = Item::distinct()
            ->whereNotNull('category')
            ->where('category', '!=', 'Uang')
            ->pluck('category');
        $initialType = $request->query('type') === 'uang' ? 'uang' : 'barang';
        return view('items.create', compact('categories', 'initialType'));
    }

    public function store(Request $request)
    {
        $isUang = $request->input('_item_type') === 'uang' || strcasecmp($request->input('category', ''), 'Uang') === 0;

        if ($isUang) {
            $request->merge([
                'category' => 'Uang',
                'base_unit' => $request->input('base_unit') ?: 'amplop',
                'wholesale_unit' => $request->input('wholesale_unit') ?: 'amplop',
                'conversion_rate' => $request->input('conversion_rate') ?: 1,
                'cost_price' => $request->filled('cost_price') ? $request->input('cost_price') : $request->input('unit_price'),
            ]);

            if (empty($request->code)) {
                $request->merge(['code' => Item::generateNextCode('Uang')]);
            }
        }

        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:items,code',
            'name' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'base_unit' => 'required|string|max:50',
            'wholesale_unit' => 'required|string|max:50',
            'conversion_rate' => 'required|numeric|min:1',
            'cost_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:8192',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Item::generateNextCode($validated['category'] ?? null);
        }

        if ($request->hasFile('image')) {
            $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'items');
            $validated['image_path'] = $result['path'];
        }

        Item::create($validated);

        $msg = $isUang ? 'Master Uang Tunai berhasil ditambahkan.' : 'Barang berhasil ditambahkan ke Master Data.';
        return redirect()->route('items.index')->with('success', $msg);
    }

    public function edit(Item $item)
    {
        $categories = Item::distinct()->whereNotNull('category')->pluck('category');
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $isUang = $request->input('_item_type') === 'uang' || strcasecmp($request->input('category', ''), 'Uang') === 0;

        if ($isUang) {
            $request->merge([
                'category' => 'Uang',
                'base_unit' => $request->input('base_unit') ?: 'amplop',
                'wholesale_unit' => $request->input('wholesale_unit') ?: 'amplop',
                'conversion_rate' => $request->input('conversion_rate') ?: 1,
                'cost_price' => $request->filled('cost_price') ? $request->input('cost_price') : $request->input('unit_price'),
            ]);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:items,code,' . $item->id,
            'name' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'base_unit' => 'required|string|max:50',
            'wholesale_unit' => 'required|string|max:50',
            'conversion_rate' => 'required|numeric|min:1',
            'cost_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:8192',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'items');
            $validated['image_path'] = $result['path'];
        }

        $item->update($validated);

        $msg = $isUang ? 'Data Master Uang berhasil diperbarui.' : 'Data barang berhasil diperbarui.';
        return redirect()->route('items.index')->with('success', $msg);
    }

    public function destroy(Item $item)
    {
        // Cegah hapus jika sudah ada relasi transaksi
        if ($item->packageItems()->exists() || $item->customItems()->exists()) {
            return back()->with('error', 'Barang ini tidak dapat dihapus karena sudah digunakan dalam paket atau pesanan aktif.');
        }

        if ($item->image_path && file_exists(public_path($item->image_path))) {
            @unlink(public_path($item->image_path));
        }

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * AJAX endpoint to quickly create or retrieve a Master Uang item on-the-fly
     * when an Owner determines a package with money.
     */
    public function quickStoreUang(Request $request)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1000',
            'name' => 'nullable|string|max:255',
            'base_unit' => 'nullable|string|max:50',
        ]);

        $nominal = (float) $validated['nominal'];
        $baseUnit = !empty($validated['base_unit']) ? $validated['base_unit'] : 'amplop';
        $name = !empty($validated['name']) ? $validated['name'] : ('Uang Tunai Rp ' . number_format($nominal, 0, ',', '.'));

        // Cari apakah sudah ada master uang dengan nama dan nominal yang sama
        $item = Item::where('category', 'Uang')
            ->where('unit_price', $nominal)
            ->where('name', $name)
            ->first();

        if (!$item) {
            $code = Item::generateNextCode('Uang');

            $item = Item::create([
                'code' => $code,
                'name' => $name,
                'category' => 'Uang',
                'unit_price' => $nominal,
                'wholesale_price' => $nominal,
                'cost_price' => $nominal,
                'base_unit' => $baseUnit,
                'wholesale_unit' => $baseUnit,
                'conversion_rate' => 1,
                'stock_units' => 1000,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'unit_price' => (float) $item->unit_price,
                'formatted_price' => 'Rp ' . number_format($item->unit_price, 0, ',', '.'),
                'category' => 'Uang',
                'base_unit' => $item->base_unit,
                'is_uang' => true,
            ],
        ]);
    }
}

