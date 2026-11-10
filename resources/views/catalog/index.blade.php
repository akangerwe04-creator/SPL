@extends('layouts.app')

@section('title', 'Generator Cetak Katalog')
@section('page_title', 'Generator Cetak Katalog Brosur (PDF / Print)')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800 border border-teal-200">
        Brosur Siap Cetak
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- INTRO HEADER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-extrabold text-slate-900">Generator Cetak Brosur & Promosi Agen</h2>
            <p class="text-xs text-slate-500 max-w-2xl mt-1">
                Tarik data master langsung dari database ke format brosur siap cetak atau simpan ke PDF. Dilengkapi pengaturan dinamis Syarat & Ketentuan (T&C) sebelum dicetak.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 font-bold text-xs border border-teal-200">
                <i class="fa-solid fa-file-pdf mr-1"></i> Format A4 Standar
            </span>
        </div>
    </div>

    <!-- 3 CATALOG PRINT CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- 1. KATALOG PARSEL (4 KOLOM) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between hover:border-emerald-500 transition group">
            <div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold mb-3">
                    <i class="fa-solid fa-table-cells"></i>
                </div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base">Katalog Parsel (4 Kolom)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Layout CSS Grid 4 Kolom otomatis menampilkan seluruh paket parsel, rincian isi barang, harga total, dan cicilan mingguan.
                </p>
                <div class="mt-4 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg inline-block">
                    {{ $parselCount }} Paket Parsel Aktif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('catalog.print.parsel') }}" target="_blank" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center">
                    <i class="fa-solid fa-print mr-1.5"></i> Buka Brosur Parsel
                </a>
            </div>
        </div>

        <!-- 2. KATALOG TABUNGAN (NABUNG DAPET BONUS) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between hover:border-amber-500 transition group">
            <div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold mb-3">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base">Katalog Tabungan Uang</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Layout Tabel promosi khusus Tabungan: "Nabung Mingguan, Dapet Uang Tunai Utuh, Ditambah Hadiah Bonus Barang Gratis".
                </p>
                <div class="mt-4 text-[11px] font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg inline-block">
                    {{ $tabunganCount }} Paket Tabungan Aktif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('catalog.print.tabungan') }}" target="_blank" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center">
                    <i class="fa-solid fa-print mr-1.5"></i> Buka Brosur Tabungan
                </a>
            </div>
        </div>

        <!-- 3. KATALOG ECERAN DINAMIS (50 BARANG & BUNDLING) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between hover:border-blue-500 transition group">
            <div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold mb-3">
                    <i class="fa-solid fa-cubes"></i>
                </div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base">Katalog Eceran Bebas Rakit</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Layout campuran: Tabel 50 barang eceran di kiri untuk dicentang konsumen, dan paket hemat bundling di sebelah kanan.
                </p>
                <div class="mt-4 text-[11px] font-bold text-blue-800 bg-blue-50 px-2.5 py-1 rounded-lg inline-block">
                    {{ $ecerItemCount }} Barang Siap Rakit
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('catalog.print.ecer') }}" target="_blank" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center">
                    <i class="fa-solid fa-print mr-1.5"></i> Buka Brosur Eceran
                </a>
            </div>
        </div>

    </div>

    <!-- DYNAMIC SETTINGS FORM (T&C & CONTACTS) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="pb-4 border-b border-slate-100">
            <h3 class="font-heading font-bold text-slate-900 text-base">Pengaturan Dinamis Isi Brosur</h3>
            <p class="text-xs text-slate-400">Sesuaikan judul, periode program, dan syarat ketentuan (T&C) sebelum dicetak.</p>
        </div>

        <form action="{{ route('catalog.settings.update') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Utama Katalog</label>
                    <input type="text" name="title" value="{{ old('title', $setting->title) }}" required class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Periode Program</label>
                    <input type="text" name="program_period" value="{{ old('program_period', $setting->program_period) }}" required class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Kontak Pemesanan / CS</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $setting->contact_person) }}" required class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rekening Transfer Resmi (Opsional)</label>
                    <input type="text" name="bank_account_info" value="{{ old('bank_account_info', $setting->bank_account_info) }}" class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-teal-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Syarat & Ketentuan (T&C Dinamis)</label>
                <textarea name="terms_and_conditions" rows="5" required class="w-full text-xs p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 font-mono">{{ old('terms_and_conditions', $setting->terms_and_conditions) }}</textarea>
                <div class="text-[10px] text-slate-400 mt-1">Gunakan enter untuk memisahkan butir-butir ketentuan aturan SPL.</div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Pengaturan Brosur
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

