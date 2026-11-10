<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Packaging;
use App\Services\ImageOptimizer;

class PackagingController extends Controller
{
    public function index(Request $request)
    {
        $query = Packaging::withCount(['packages', 'customerPackages']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $packagings = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Packaging::count(),
            'active' => Packaging::where('is_active', true)->count(),
            'used_in_packages' => Packaging::has('packages')->count(),
            'ordered_by_customers' => Packaging::has('customerPackages')->count(),
        ];

        return view('owner.packagings.index', compact('packagings', 'stats'));
    }

    public function create()
    {
        return view('owner.packagings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:packagings,code',
            'cost_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:8192',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Packaging::generateNextCode();
        }

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        if ($request->hasFile('image')) {
            $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'packagings');
            $validated['image_path'] = $result['path'];
        }

        Packaging::create($validated);

        return redirect()->route('owner.packagings.index')
            ->with('success', "Kemasan '{$validated['name']}' berhasil ditambahkan.");
    }

    public function edit(Packaging $packaging)
    {
        return view('owner.packagings.edit', compact('packaging'));
    }

    public function update(Request $request, Packaging $packaging)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:packagings,code,' . $packaging->id,
            'cost_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:8192',
            'remove_image' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_image')) {
            if ($packaging->image_path && file_exists(public_path($packaging->image_path))) {
                @unlink(public_path($packaging->image_path));
            }
            $validated['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            if ($packaging->image_path && file_exists(public_path($packaging->image_path))) {
                @unlink(public_path($packaging->image_path));
            }
            $result = ImageOptimizer::optimizeAndSave($request->file('image'), 'packagings');
            $validated['image_path'] = $result['path'];
        }

        $packaging->update($validated);

        return redirect()->route('owner.packagings.index')
            ->with('success', "Data kemasan '{$packaging->name}' berhasil diperbarui.");
    }

    public function destroy(Packaging $packaging)
    {
        if ($packaging->customerPackages()->exists()) {
            return back()->with('error', "Kemasan '{$packaging->name}' tidak dapat dihapus karena sudah dipilih oleh konsumen binaan. Anda dapat menonaktifkannya.");
        }

        if ($packaging->image_path && file_exists(public_path($packaging->image_path))) {
            @unlink(public_path($packaging->image_path));
        }

        $packaging->packages()->detach();
        $packaging->delete();

        return redirect()->route('owner.packagings.index')
            ->with('success', "Kemasan berhasil dihapus.");
    }

    /**
     * API untuk mengambil daftar kemasan dan fotonya (untuk katalog & modal pemilihan)
     */
    public function apiList()
    {
        $packagings = Packaging::where('is_active', true)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'cost_price' => (float) $p->cost_price,
                'price' => (float) $p->price,
                'formatted_price' => $p->formatted_price,
                'image_url' => $p->image_url,
                'description' => $p->description,
            ];
        });

        return response()->json($packagings);
    }
}

