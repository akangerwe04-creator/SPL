@extends('layouts.app')

@section('title', 'Edit ' . ($item->isUang() ? 'Master Uang' : 'Master Barang') . ' - ' . $item->name)
@section('page_title', 'Edit ' . ($item->isUang() ? 'Master Uang' : 'Master Barang'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    itemType: '{{ old('_item_type', $item->isUang() ? 'uang' : 'barang') }}',
    name: '{{ old('name', $item->name) }}',
    unitPrice: '{{ old('unit_price', (int)$item->unit_price) }}',
    costPrice: '{{ old('cost_price', (int)$item->cost_price) }}',
    baseUnit: '{{ old('base_unit', $item->base_unit) }}',
    wholesaleUnit: '{{ old('wholesale_unit', $item->wholesale_unit) }}',
    conversionRate: '{{ old('conversion_rate', (int)$item->conversion_rate) }}',
    uangBaseUnit: '{{ old('base_unit', $item->base_unit ?: 'amplop') }}',
    formatRupiah(val) {
        if (!val || isNaN(val)) return 'Rp 0';
        return 'Rp ' + parseInt(val).toLocaleString('id-ID');
    }
}">

    <a href="{{ route('items.index') }}" class="inline-flex items-center text-xs font-bold text-pink-700 hover:text-pink-800">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Master Data
    </a>

    <!-- TYPE INDICATOR / SWITCHER -->
    <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-5">
        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5">
            Tipe Data Master:
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button" @click="itemType = 'barang'"
                :class="itemType === 'barang' ? 'border-pink-500 bg-pink-50/80 ring-2 ring-pink-400/20 text-slate-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-600'"
                class="flex items-start p-3.5 rounded-xl border-2 transition text-left">
                <div class="w-10 h-10 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center mr-3 text-lg flex-shrink-0">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div>
                    <div class="font-bold text-xs text-slate-900">📦 Barang Fisik</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Sembako / Kue / Sirup dengan satuan dus grosir.</div>
                </div>
            </button>

            <button type="button" @click="itemType = 'uang'"
                :class="itemType === 'uang' ? 'border-emerald-500 bg-emerald-50/80 ring-2 ring-emerald-400/20 text-slate-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-600'"
                class="flex items-start p-3.5 rounded-xl border-2 transition text-left">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mr-3 text-lg flex-shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="font-bold text-xs text-slate-900">💵 Kategori Uang</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Uang tunai / tabungan tanpa konversi dus.</div>
                </div>
            </button>
        </div>
    </div>

    <!-- ================= EDIT FORMULIR 1: BARANG FISIK ================= -->
    <div x-show="itemType === 'barang'" x-transition class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-bold text-slate-900 text-base">Edit Master Barang: {{ $item->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui rincian satuan konversi dus untuk belanja grosir.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-pink-100 text-pink-800">
                📦 Barang Fisik
            </span>
        </div>

        <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="_item_type" value="barang">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Barang</label>
                    <input type="text" name="code" value="{{ old('code', $item->code) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-mono text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Barang</label>
                    <input type="text" name="category" value="{{ old('category', $item->category === 'Uang' ? '' : $item->category) }}" list="categories-list" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                    <datalist id="categories-list">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Barang Lengkap *</label>
                <input type="text" name="name" x-model="name" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-sm text-slate-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Harga Ecer / Nilai Kasir Mini (Rp) *</label>
                    <input type="number" name="unit_price" x-model.number="unitPrice" required min="0" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-bold text-pink-900 text-sm">
                    <div class="mt-1 text-[10px] text-slate-500">
                        Terformat: <strong class="text-pink-700" x-text="formatRupiah(unitPrice)"></strong>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Estimasi Harga Beli Grosir / Modal (Rp)</label>
                    <input type="number" name="cost_price" x-model.number="costPrice" min="0" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Satuan Dasar *</label>
                    <input type="text" name="base_unit" x-model="baseUnit" required class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Satuan Grosir *</label>
                    <input type="text" name="wholesale_unit" x-model="wholesaleUnit" required class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Isi per Satuan Grosir *</label>
                    <input type="number" name="conversion_rate" x-model.number="conversionRate" required min="1" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-bold text-slate-900">
                </div>
            </div>

            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                    <span class="font-semibold text-slate-700">Status Aktif (Ditampilkan dalam katalog & POS)</span>
                </label>
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <a href="{{ route('items.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Perubahan Barang
                </button>
            </div>
        </form>
    </div>

    <!-- ================= EDIT FORMULIR 2: MASTER UANG ================= -->
    <div x-show="itemType === 'uang'" x-transition class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-6">
        <div class="pb-4 border-b border-emerald-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-bold text-slate-900 text-base">Edit Master Uang: {{ $item->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kategori khusus uang tunai yang akan ditampilkan di Menu Belanja Barang.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                <i class="fa-solid fa-money-bill-wave mr-1"></i> Mode Uang
            </span>
        </div>

        <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="_item_type" value="uang">
            <input type="hidden" name="category" value="Uang">
            <input type="hidden" name="wholesale_unit" value="amplop">
            <input type="hidden" name="conversion_rate" value="1">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Master Uang</label>
                    <input type="text" name="code" value="{{ old('code', $item->code) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 font-mono text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                    <div class="w-full p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 font-bold flex items-center">
                        <i class="fa-solid fa-money-bill-wave mr-2 text-emerald-600"></i> Uang (Tunai / Tabungan)
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Item / Paket Uang Lengkap *</label>
                <input type="text" name="name" x-model="name" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm text-slate-900 font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nominal Uang Tunai (Rp) *</label>
                    <input type="number" name="unit_price" x-model.number="unitPrice" @input="costPrice = unitPrice" required min="0" class="w-full p-2.5 bg-emerald-50 border border-emerald-300 rounded-lg focus:outline-none focus:border-emerald-600 font-extrabold text-emerald-900 text-base">
                    <div class="mt-1 text-[11px] font-bold text-emerald-700" x-text="formatRupiah(unitPrice)"></div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Dana / Modal Uang yang Disiapkan (Rp)</label>
                    <input type="number" name="cost_price" x-model.number="costPrice" min="0" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 font-bold text-slate-800 text-sm">
                </div>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200">
                <label class="block font-semibold text-slate-700 mb-1">Satuan Penyerahan Uang *</label>
                <select name="base_unit" x-model="uangBaseUnit" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-slate-900 font-medium">
                    <option value="amplop">amplop (Standar Amplop SPL)</option>
                    <option value="paket">paket (Paket Tunai)</option>
                    <option value="lembar">lembar (Pecahan Lembar Uang)</option>
                    <option value="rupiah">rupiah (Nominal Saldo Tunai)</option>
                </select>
            </div>

            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="font-semibold text-slate-700">Status Aktif (Ditampilkan dalam katalog & POS)</span>
                </label>
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <a href="{{ route('items.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                    <i class="fa-solid fa-money-bill-wave mr-1.5"></i> Simpan Perubahan Uang
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
