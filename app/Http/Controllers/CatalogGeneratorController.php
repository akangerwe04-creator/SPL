<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Item;
use App\Models\CatalogSetting;

class CatalogGeneratorController extends Controller
{
    public function index()
    {
        $setting = CatalogSetting::current();
        $parselCount = Package::where('type', 'parsel')->where('is_active', true)->count();
        $tabunganCount = Package::where('type', 'tabungan')->where('is_active', true)->count();
        $ecerItemCount = Item::where('is_active', true)->count();

        return view('catalog.index', compact('setting', 'parselCount', 'tabunganCount', 'ecerItemCount'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'program_period' => 'required|string|max:255',
            'terms_and_conditions' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'bank_account_info' => 'nullable|string|max:255',
        ]);

        $setting = CatalogSetting::current();
        $setting->update($validated);

        return back()->with('success', 'Pengaturan katalog berhasil disimpan.');
    }

    /**
     * Katalog Parsel: Layout CSS Grid 4 Kolom
     */
    public function printParsel()
    {
        $setting = CatalogSetting::current();
        $packages = Package::where('type', 'parsel')
            ->where('is_active', true)
            ->with(['packageItems.item', 'packagings'])
            ->orderBy('target_nominal', 'asc')
            ->get();
        $packagings = \App\Models\Packaging::where('is_active', true)->get();

        return view('catalog.print_parsel', compact('setting', 'packages', 'packagings'));
    }

    /**
     * Katalog Tabungan: Layout Tabel "Nabung, Dapet, Bonus"
     */
    public function printTabungan()
    {
        $setting = CatalogSetting::current();
        $packages = Package::where('type', 'tabungan')
            ->where('is_active', true)
            ->with(['packageItems' => fn($q) => $q->where('is_bonus', true)->with('item')])
            ->orderBy('target_nominal', 'asc')
            ->get();

        return view('catalog.print_tabungan', compact('setting', 'packages'));
    }

    /**
     * Katalog Eceran: Layout Campuran (50 Barang di kiri, Bundling di kanan)
     */
    public function printEcer()
    {
        $setting = CatalogSetting::current();
        $items = Item::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $bundlings = Package::where('type', 'ecer')->where('is_active', true)->with('packageItems.item')->get();

        return view('catalog.print_ecer', compact('setting', 'items', 'bundlings'));
    }
}

