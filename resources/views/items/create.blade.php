@extends('layouts.app')

@section('title', 'Tambah Barang / Uang Baru')
@section('page_title', 'Tambah Master Barang / Uang')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    itemType: '{{ old('_item_type', $initialType ?? 'barang') }}',
    name: '{{ old('name', '') }}',
    unitPrice: '{{ old('unit_price', '') }}',
    costPrice: '{{ old('cost_price', '') }}',
    baseUnit: '{{ old('base_unit', 'pcs') }}',
    wholesaleUnit: '{{ old('wholesale_unit', 'dus') }}',
    conversionRate: '{{ old('conversion_rate', 24) }}',
    uangBaseUnit: '{{ old('base_unit', 'amplop') }}',
    formatRupiah(val) {
        if (!val || isNaN(val)) return 'Rp 0';
        return 'Rp ' + parseInt(val).toLocaleString('id-ID');
    },
    setPresetUang(presetName, nominal) {
        this.name = presetName;
        this.unitPrice = nominal;
        this.costPrice = nominal;
    }
}">

    <a href="{{ route('items.index') }}" class="inline-flex items-center text-xs font-bold text-pink-700 hover:text-pink-800">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Master Data
    </a>

    <!-- TYPE SWITCHER SELECTOR -->
    <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-5">
        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5">
            Pilih Jenis Master yang Ingin Ditambahkan:
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Pilihan 1: Barang Fisik -->
            <button type="button" @click="itemType = 'barang'; if(baseUnit === 'amplop') baseUnit = 'pcs';"
                :class="itemType === 'barang' ? 'border-pink-500 bg-pink-50/80 ring-2 ring-pink-400/20 text-slate-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-600'"
                class="flex items-start p-3.5 rounded-xl border-2 transition text-left">
                <div class="w-10 h-10 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center mr-3 text-lg flex-shrink-0">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div>
                    <div class="font-bold text-xs text-slate-900">📦 Barang Fisik (Sembako/Kue/Sirup)</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Memiliki satuan ecer, satuan grosir (dus), dan isi konversi untuk kulakan grosir.</div>
                </div>
            </button>

            <!-- Pilihan 2: Kategori Uang -->
            <button type="button" @click="itemType = 'uang'; baseUnit = uangBaseUnit; wholesaleUnit = uangBaseUnit; conversionRate = 1;"
                :class="itemType === 'uang' ? 'border-emerald-500 bg-emerald-50/80 ring-2 ring-emerald-400/20 text-slate-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-600'"
                class="flex items-start p-3.5 rounded-xl border-2 transition text-left">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mr-3 text-lg flex-shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="font-bold text-xs text-slate-900">💵 Kategori Uang (Tunai / Tabungan)</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Formulir khusus uang tunai untuk tabungan / THR. Tidak memerlukan perhitungan dus/karton.</div>
                </div>
            </button>
        </div>
    </div>

    <!-- ================= FORMULIR 1: MASTER BARANG FISIK ================= -->
    <div x-show="itemType === 'barang'" x-transition class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="pb-4 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500 inline-block"></span>
                <h3 class="font-heading font-bold text-slate-900 text-base">Formulir Tambah Master Barang Fisik</h3>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pastikan satuan dasar, satuan grosir, dan nilai konversi terisi akurat untuk kalkulator belanja.</p>
        </div>

        <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_item_type" value="barang">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Barang (Opsional)</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Auto: BRG-XXX" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-mono text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Barang</label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="Contoh: Biskuit & Kue / Sirup / Sembako" list="categories-list" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                    <datalist id="categories-list">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Barang Lengkap *</label>
                <input type="text" name="name" x-model="name" required placeholder="Contoh: Khong Guan Classic Red 1600g" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-sm text-slate-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Harga Ecer / Nilai Kasir Mini (Rp) *</label>
                    <input type="number" name="unit_price" x-model.number="unitPrice" required min="0" placeholder="Contoh: 115000" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-bold text-pink-900 text-sm">
                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-500">
                        <span>Pemotongan kasir Mini-POS & ecer</span>
                        <span class="font-bold text-pink-700" x-text="formatRupiah(unitPrice)"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Estimasi Harga Beli Grosir / Modal (Rp)</label>
                    <input type="number" name="cost_price" x-model.number="costPrice" min="0" placeholder="Contoh: 102000" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-500">
                        <span>Modal beli per satuan dasar untuk grosir</span>
                        <span class="font-bold text-slate-700" x-text="formatRupiah(costPrice)"></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Satuan Dasar *</label>
                    <input type="text" name="base_unit" x-model="baseUnit" required placeholder="pcs / botol / kaleng" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Satuan Grosir *</label>
                    <input type="text" name="wholesale_unit" x-model="wholesaleUnit" required placeholder="dus / karton / bal" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Isi per Satuan Grosir *</label>
                    <input type="number" name="conversion_rate" x-model.number="conversionRate" required min="1" placeholder="24" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-bold text-slate-900">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Foto / Gambar Barang (Opsional)</label>
                <input type="file" name="image" accept="image/*" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-pink-100 file:text-pink-800 hover:file:bg-pink-200">
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <a href="{{ route('items.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Barang Fisik
                </button>
            </div>
        </form>
    </div>

    <!-- ================= FORMULIR 2: MASTER UANG TUNAI / TABUNGAN ================= -->
    <div x-show="itemType === 'uang'" x-transition class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-6">
        <div class="pb-4 border-b border-emerald-100 flex items-start justify-between">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    <h3 class="font-heading font-bold text-slate-900 text-base">Formulir Master Uang (Tunai / Tabungan Uang)</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kategori khusus penyiapan uang tunai pencairan tabungan atau uang THR. Sistem akan menampilkan rincian uang ini di <strong>Menu Belanja Barang</strong> lengkap dengan nama konsumen dan agennya.
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex-shrink-0">
                <i class="fa-solid fa-money-bill-wave mr-1"></i> Mode Uang
            </span>
        </div>

        <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_item_type" value="uang">
            <input type="hidden" name="category" value="Uang">
            <input type="hidden" name="wholesale_unit" value="amplop">
            <input type="hidden" name="conversion_rate" value="1">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Master Uang (Opsional)</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Auto: UANG-XXX" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 font-mono text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Terpilih</label>
                    <div class="w-full p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 font-bold flex items-center">
                        <i class="fa-solid fa-money-bill-wave mr-2 text-emerald-600"></i> Uang (Tunai / Tabungan)
                    </div>
                </div>
            </div>

            <!-- PRESET QUICK BUTTONS -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Pilih Preset Nominal Uang Cepat (Opsional):</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="setPresetUang('Uang Tunai Tabungan Rp 1.500.000', 1500000)" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-semibold transition">
                        + Tabungan 1.5 Juta
                    </button>
                    <button type="button" @click="setPresetUang('Uang Tunai Tabungan Rp 1.000.000', 1000000)" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-semibold transition">
                        + Tabungan 1 Juta
                    </button>
                    <button type="button" @click="setPresetUang('Uang Tunai Tabungan Rp 900.000', 900000)" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-semibold transition">
                        + Tabungan 900 Ribu
                    </button>
                    <button type="button" @click="setPresetUang('Uang Tunai THR Rp 500.000', 500000)" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-semibold transition">
                        + THR 500 Ribu
                    </button>
                    <button type="button" @click="setPresetUang('Uang Pecahan Rp 100.000', 100000)" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-semibold transition">
                        + Pecahan 100 Ribu
                    </button>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Item / Paket Uang Lengkap *</label>
                <input type="text" name="name" x-model="name" required placeholder="Contoh: Uang Tunai Tabungan Lebaran Rp 1.500.000" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm text-slate-900 font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nominal Uang Tunai (Rp) *</label>
                    <input type="number" name="unit_price" x-model.number="unitPrice" @input="costPrice = unitPrice" required min="0" placeholder="Contoh: 1500000" class="w-full p-2.5 bg-emerald-50 border border-emerald-300 rounded-lg focus:outline-none focus:border-emerald-600 font-extrabold text-emerald-900 text-base">
                    <div class="flex items-center justify-between mt-1 text-[11px]">
                        <span class="text-slate-500">Nominal yang diserahkan:</span>
                        <span class="font-extrabold text-emerald-700" x-text="formatRupiah(unitPrice)"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Dana / Modal Uang yang Disiapkan (Rp)</label>
                    <input type="number" name="cost_price" x-model.number="costPrice" min="0" placeholder="Sama dengan nominal uang" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 font-bold text-slate-800 text-sm">
                    <span class="text-[10px] text-slate-400 mt-1 block">Otomatis setara nominal uang tunai yang harus disiapkan owner.</span>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Satuan Penyerahan Uang *</label>
                    <select name="base_unit" x-model="uangBaseUnit" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-slate-900 font-medium">
                        <option value="amplop">amplop (Standar Amplop SPL)</option>
                        <option value="paket">paket (Paket Tunai)</option>
                        <option value="lembar">lembar (Pecahan Lembar Uang)</option>
                        <option value="rupiah">rupiah (Nominal Saldo Tunai)</option>
                    </select>
                    <span class="text-[10px] text-slate-500 mt-1 block">Contoh: 1 amplop berisi uang Rp 1.500.000</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Perhitungan Satuan Grosir Dus/Karton</label>
                    <div class="p-2.5 bg-white border border-slate-200 rounded-lg text-slate-600 text-[11px] font-medium flex items-center">
                        <i class="fa-solid fa-circle-check text-emerald-600 mr-2"></i> Bebas konversi dus (Nominal Tunai Langsung)
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Di Menu Belanja Barang, uang tunai langsung dirinci per konsumen & agen.</span>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Foto / Ilustrasi Amplop Uang (Opsional)</label>
                <input type="file" name="image" accept="image/*" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <a href="{{ route('items.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                    <i class="fa-solid fa-money-bill-wave mr-1.5"></i> Simpan Master Uang
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
