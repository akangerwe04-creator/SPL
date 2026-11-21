@extends('layouts.app')

@section('title', 'Manajemen Periode & Buka Periode Baru (Admin)')
@section('page_title', 'Manajemen Periode & Buka Musim Baru')

@section('header_badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-purple-100 text-purple-900 border border-purple-300">
        <i class="fa-solid fa-calendar-plus mr-1 text-purple-600"></i> Otoritas Khusus Administrator
    </span>
@endsection

@section('content')
@php
    $isClosed = $isClosed ?? ($isBookClosed ?? ($appSetting->is_book_closed ?? false));
@endphp
<div class="space-y-6 max-w-6xl">

    <!-- 1. HERO BANNER MANAJEMEN PERIODE -->
    <div class="bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-white/10">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-purple-500/20 text-purple-300 border border-purple-400/30 mb-2">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Fitur Otoritas Administrator Sistem</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-heading font-black tracking-tight text-white">
                    Pusat Pembukaan Periode &amp; Musim Baru
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mt-1.5 leading-relaxed">
                    Ketika musim parsel yang berjalan telah selesai, Administrator dapat membuka periode tahun berikutnya. 
                    Seluruh data musim sebelumnya akan <strong>diarsipkan secara otomatis</strong>, dan sistem akan disiapkan dalam keadaan bersih 
                    sehingga seluruh data agen, barang, kemasan, dan menu paket dapat diinput kembali untuk musim baru.
                </p>
            </div>

            <!-- STATUS BADGE BUKU -->
            <div class="shrink-0">
                @if($isClosed)
                    <div class="px-5 py-3.5 rounded-2xl bg-emerald-500/20 border-2 border-emerald-400 text-emerald-300 text-center shadow-lg backdrop-blur">
                        <div class="flex items-center justify-center space-x-2 text-xs font-black uppercase tracking-wider mb-0.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>SUDAH TUTUP BUKU</span>
                        </div>
                        <div class="text-[11px] text-emerald-200/90 font-medium">Siap membuka periode baru</div>
                    </div>
                @else
                    <div class="px-5 py-3.5 rounded-2xl bg-rose-500/20 border-2 border-rose-400 text-rose-300 text-center shadow-lg backdrop-blur">
                        <div class="flex items-center justify-center space-x-2 text-xs font-black uppercase tracking-wider mb-0.5">
                            <i class="fa-solid fa-lock-open text-rose-400"></i>
                            <span>BELUM TUTUP BUKU</span>
                        </div>
                        <div class="text-[11px] text-rose-200/90 font-medium">Musim saat ini masih berjalan</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. KARTU STATUS SYARAT MUTLAK: WAJIB TUTUP BUKU DULU -->
    @if(!$isClosed)
        <div class="bg-gradient-to-r from-rose-50 to-orange-50 border-2 border-rose-400 rounded-3xl p-6 sm:p-7 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-md">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center space-x-1.5 text-xs font-black uppercase tracking-wider text-rose-800">
                            <span>Syarat Wajib Belum Terpenuhi</span>
                            <span class="px-2 py-0.2 rounded-full bg-rose-200 text-rose-950 text-[10px]">Tutup Buku Dulu</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-heading font-extrabold text-slate-900 mt-0.5">
                            Periode Saat Ini ({{ $currentPeriodName }}) Masih Aktif!
                        </h3>
                        <p class="text-xs text-slate-600 mt-1 max-w-2xl leading-relaxed">
                            Sesuai aturan operasional SPL, <strong>periode sebelumnya wajib melakukan Tutup Buku terlebih dahulu</strong> sebelum Anda dapat membuka periode musim baru. Hal ini untuk memastikan seluruh keputusan pemenuhan barang, setoran kas agen, dan pembagian parsel musim ini telah dipertanggungjawabkan seutuhnya.
                        </p>
                    </div>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('owner.book-closing.index') }}" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-lock"></i>
                        <span>Buka Menu Tutup Buku</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-400 rounded-3xl p-6 sm:p-7 shadow-sm">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-md">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="inline-flex items-center space-x-1.5 text-xs font-black uppercase tracking-wider text-emerald-800">
                        <span>Syarat Terpenuhi &bull; Siap Buka Musim</span>
                    </div>
                    <h3 class="text-base sm:text-lg font-heading font-extrabold text-emerald-950 mt-0.5">
                        Periode {{ $currentPeriodName }} Telah Resmi Tutup Buku
                    </h3>
                    <p class="text-xs text-emerald-800 mt-1 max-w-3xl leading-relaxed">
                        Ditutup pada: <strong>{{ $appSetting->book_closed_at ? $appSetting->book_closed_at->format('d F Y, H:i') . ' WIB' : '-' }}</strong> oleh <strong>{{ $appSetting->bookClosedByUser?->name ?: 'Administrator' }}</strong>.<br>
                        Anda sekarang diizinkan untuk mengarsipkan data musim ini dan membuka buku periode baru untuk tahun selanjutnya.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. RINGKASAN DATA MUSIM SAAT INI (YANG AKAN DIARSIPKAN) -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-box-archive text-purple-600"></i>
                    <span>Ringkasan Data Periode Saat Ini ({{ $currentPeriodName }})</span>
                </h3>
                <p class="text-xs text-slate-500">Data berikut akan disimpan secara permanen ke dalam file arsip sebelum sistem direset untuk musim baru</p>
            </div>
            <span class="text-xs font-extrabold px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                Snapshot Musim
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-100">
                <div class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Agen Lapangan</div>
                <div class="text-2xl font-black text-purple-950 mt-1">{{ $currentStats['total_agents'] }}</div>
                <div class="text-[10px] text-purple-600 mt-0.5">Akun binaan diarsipkan</div>
            </div>

            <div class="p-4 rounded-2xl bg-pink-50/60 border border-pink-100">
                <div class="text-[11px] font-bold text-pink-700 uppercase tracking-wider">Total Konsumen</div>
                <div class="text-2xl font-black text-pink-950 mt-1">{{ $currentStats['total_customers'] }}</div>
                <div class="text-[10px] text-pink-600 mt-0.5">Nasabah terdaftar</div>
            </div>

            <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100">
                <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Master Barang &amp; Kemasan</div>
                <div class="text-2xl font-black text-blue-950 mt-1">{{ $currentStats['total_items'] + $currentStats['total_packagings'] }}</div>
                <div class="text-[10px] text-blue-600 mt-0.5">{{ $currentStats['total_items'] }} Barang &bull; {{ $currentStats['total_packagings'] }} Kemasan</div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Setoran Terkumpul</div>
                <div class="text-lg sm:text-xl font-black text-emerald-950 mt-1 font-mono">Rp {{ number_format($currentStats['total_paid_nominal'], 0, ',', '.') }}</div>
                <div class="text-[10px] text-emerald-600 mt-0.5">Dari Target Rp {{ number_format($currentStats['total_target_nominal'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- 4. FORMULIR BUKA PERIODE BARU -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm relative overflow-hidden">
        @if(!$isClosed)
            <!-- OVERLAY JIKA BELUM TUTUP BUKU -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs z-20 flex items-center justify-center p-6 text-center">
                <div class="max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border-2 border-rose-400 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold mx-auto">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h4 class="font-heading font-extrabold text-slate-900 text-base">Formulir Terkunci</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Formulir pembuatan periode baru dinonaktifkan sementara karena periode saat ini belum melakukan <strong>Tutup Buku</strong>.
                    </p>
                    <a href="{{ route('owner.book-closing.index') }}" class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition">
                        <span>Menuju Menu Tutup Buku</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @endif

        <div class="border-b border-slate-100 pb-4 mb-6">
            <h3 class="text-lg font-heading font-extrabold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-calendar-plus text-emerald-600"></i>
                <span>Formulir Pembukaan Periode Tahun Selanjutnya</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Isi data periode baru di bawah ini. Saat disubmit, data lama diarsipkan dan sistem dibersihkan untuk input data baru.
            </p>
        </div>

        <form action="{{ route('admin.period.open-new') }}" method="POST" onsubmit="return confirm('PERINGATAN TERAKHIR!\n\nApakah Anda yakin ingin membuka periode baru?\n1. Seluruh data musim saat ini akan diarsipkan secara aman.\n2. Data agen, barang, kemasan, paket, dan konsumen akan dibersihkan agar dapat diinput ulang.\n3. Akun Owner & Administrator tetap aman.\n\nKlik OK untuk melanjutkan.')" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Nama Periode Musim Baru <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="new_period_name" 
                        value="{{ old('new_period_name', $suggestedNextPeriod) }}" 
                        required 
                        placeholder="Contoh: Musim 1448 H / 2027"
                        class="w-full text-xs font-bold p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-slate-900"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Nama musim yang akan tampil di katalog dan kop laporan</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Tanggal Mulai Program Baru <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="new_start_date" 
                        value="{{ old('new_start_date', $suggestedStartDate) }}" 
                        required 
                        class="w-full text-xs font-bold p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-slate-900"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Tanggal dimulainya pendaftaran konsumen baru</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Tanggal Selesai (Target Pembagian) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="new_end_date" 
                        value="{{ old('new_end_date', $suggestedEndDate) }}" 
                        required 
                        class="w-full text-xs font-bold p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-slate-900"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Batas akhir setoran musim baru</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1.5">
                    Catatan Arsip Musim Sebelumnya (Opsional)
                </label>
                <textarea 
                    name="notes" 
                    rows="2" 
                    placeholder="Contoh: Seluruh parsel musim ini telah terdistribusi 100% dan diserahterimakan ke agen..."
                    class="w-full text-xs font-medium p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-slate-800 leading-relaxed"
                >{{ old('notes') }}</textarea>
            </div>

            <!-- KOTAK KONFIRMASI KEAMANAN -->
            <div class="p-5 rounded-2xl bg-amber-50/70 border-2 border-amber-300 text-xs text-amber-950 space-y-3">
                <div class="font-extrabold flex items-center space-x-2 text-amber-900">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    <span>Konfirmasi Keamanan Pembukaan Periode Baru</span>
                </div>
                <ul class="list-disc pl-5 text-[11px] space-y-1 text-amber-900">
                    <li>Data musim saat ini (<strong>{{ $currentPeriodName }}</strong>) akan otomatis dibuatkan snapshot arsip dan dapat diunduh berkasnya kapan saja.</li>
                    <li>Sesuai instruksi, saat periode baru dimulai maka seluruh data <strong>agen lapangan, master barang, kemasan, paket, konsumen, serta transaksi</strong> akan dibersihkan agar dapat diinput ulang.</li>
                    <li><strong>Akun Administrator dan Akun Owner dijamin tetap utuh dan aman</strong>.</li>
                </ul>

                <div class="pt-2">
                    <label class="block text-xs font-bold text-amber-950 mb-1">
                        Ketik teks konfirmasi <span class="font-mono bg-amber-200/80 px-1.5 py-0.2 rounded text-amber-950">BUKA PERIODE BARU</span> atau masukkan kata sandi Administrator Anda:
                    </label>
                    <input 
                        type="text" 
                        name="confirm_text" 
                        required 
                        placeholder="Ketik BUKA PERIODE BARU di sini..."
                        class="w-full text-xs font-bold p-3 bg-white border border-amber-400 rounded-xl focus:outline-none focus:border-amber-600 focus:ring-2 focus:ring-amber-200 text-slate-900"
                    >
                </div>
            </div>

            <button 
                type="submit" 
                class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-xl shadow-emerald-600/30 transition flex items-center justify-center space-x-2"
                {{ !$isClosed ? 'disabled' : '' }}
            >
                <i class="fa-solid fa-calendar-check text-base"></i>
                <span>Mulai Periode Baru &amp; Simpan Arsip Musim Sebelumnya</span>
            </button>
        </form>
    </div>

    <!-- 5. RIWAYAT ARSIP PERIODE SEBELUMNYA -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                    <span>Riwayat Arsip Periode Sebelumnya</span>
                </h3>
                <p class="text-xs text-slate-500">Daftar arsip musim yang pernah ditutup dan disimpan ke dalam sistem</p>
            </div>
            <span class="text-xs font-bold text-slate-400">{{ $archives->total() }} Arsip Tersimpan</span>
        </div>

        @if($archives->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400">
                <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2"></i>
                <div>Belum ada riwayat arsip periode sebelumnya. Ini adalah periode pertama sistem SPL.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                            <th class="py-3 px-3.5">Nama Periode</th>
                            <th class="py-3 px-3.5">Waktu Program</th>
                            <th class="py-3 px-3.5">Diarsipkan Pada</th>
                            <th class="py-3 px-3.5 text-center">Agen</th>
                            <th class="py-3 px-3.5 text-center">Konsumen</th>
                            <th class="py-3 px-3.5 text-right">Total Setoran</th>
                            <th class="py-3 px-3.5 text-center">Ukuran File</th>
                            <th class="py-3 px-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($archives as $arc)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3.5 font-extrabold text-slate-900">
                                    {{ $arc->period_name }}
                                    @if($arc->notes)
                                        <div class="text-[10px] text-slate-400 font-normal line-clamp-1">{{ $arc->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-slate-600 text-[11px]">
                                    {{ $arc->program_start_date ? $arc->program_start_date->format('d/m/Y') : '-' }} s/d {{ $arc->program_end_date ? $arc->program_end_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-3.5 text-slate-600 text-[11px]">
                                    <div>{{ $arc->created_at->format('d M Y, H:i') }} WIB</div>
                                    <div class="text-[10px] text-slate-400">Oleh: {{ $arc->archived_by_name ?: '-' }}</div>
                                </td>
                                <td class="py-3 px-3.5 text-center font-bold text-slate-700">
                                    {{ $arc->total_agents }} Agen
                                </td>
                                <td class="py-3 px-3.5 text-center font-bold text-slate-700">
                                    {{ $arc->total_customers }} Orang
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold text-emerald-700">
                                    {{ $arc->formatted_paid_nominal }}
                                </td>
                                <td class="py-3 px-3.5 text-center font-mono text-slate-500 text-[11px]">
                                    {{ $arc->formatted_file_size }}
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <a 
                                        href="{{ route('admin.period.download-archive', $arc) }}" 
                                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition shadow-2xs"
                                        title="Unduh Berkas Arsip (.json)"
                                    >
                                        <i class="fa-solid fa-download text-[10px]"></i>
                                        <span>Unduh JSON</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-3">
                {{ $archives->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
