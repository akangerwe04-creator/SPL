@extends('layouts.app')

@section('title', 'Tutup Buku & Pemenuhan Parsel')
@section('page_title', 'Tutup Buku & Keputusan Pemenuhan Musim Parsel')

@section('header_badge')
    @if($appSetting->is_book_closed)
        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white shadow-xs animate-pulse flex items-center">
            <i class="fa-solid fa-lock mr-1"></i> Periode Tutup Buku Aktif
        </span>
    @else
        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center">
            <i class="fa-solid fa-lock-open mr-1"></i> Periode Berjalan (Belum Tutup Buku)
        </span>
    @endif
@endsection

@section('content')
<div class="space-y-6" x-data="bookClosingManager()">

    <!-- BANNER STATUS TUTUP BUKU -->
    <div class="p-5 md:p-6 rounded-2xl border shadow-sm transition {{ $appSetting->is_book_closed ? 'bg-gradient-to-r from-rose-900 via-pink-900 to-rose-950 text-white border-rose-800' : 'bg-gradient-to-r from-pink-500 via-rose-500 to-pink-600 text-white border-pink-400' }}">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full {{ $appSetting->is_book_closed ? 'bg-amber-400 animate-ping' : 'bg-emerald-300' }}"></span>
                    <h2 class="text-lg md:text-xl font-heading font-extrabold uppercase tracking-wide">
                        {{ $appSetting->is_book_closed ? 'Periode Tutup Buku Aktif' : 'Status: Periode Setoran Sedang Berjalan' }}
                    </h2>
                </div>
                <p class="text-xs md:text-sm text-pink-100 max-w-2xl leading-relaxed">
                    @if($appSetting->is_book_closed)
                        Musim parsel telah resmi ditutup pada <strong>{{ $appSetting->book_closed_at?->format('d M Y, H:i') }} WIB</strong>. 
                        Owner dan Admin dapat menetapkan keputusan barang dan uang bagi seluruh konsumen serta memfinalisasi persiapan belanja barang kulakan.
                    @else
                        Saat Tutup Buku diaktifkan, periode parsel berakhir. Konsumen yang mencapai <strong>Titik Aman Full Parsel</strong> otomatis berhak atas seluruh barang paket, sedangkan konsumen yang belum mencapai titik aman atau paket tanpa titik aman dapat disesuaikan barang dan uangnya langsung oleh Owner.
                    @endif
                </p>
                @if($appSetting->is_book_closed && $appSetting->book_closing_notes)
                    <div class="mt-2 text-xs bg-white/10 backdrop-blur p-2.5 rounded-xl border border-white/15 text-pink-50">
                        <i class="fa-solid fa-quote-left mr-1.5 text-pink-200"></i> {{ $appSetting->book_closing_notes }}
                    </div>
                @endif
            </div>

            <!-- ACTION BUTTONS UTAMA -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @if(!$appSetting->is_book_closed)
                    <button type="button" @click="showCloseModal = true" class="px-4 py-2.5 rounded-xl bg-white text-rose-700 hover:bg-rose-50 font-extrabold text-xs shadow-md transition flex items-center space-x-2">
                        <i class="fa-solid fa-lock text-sm"></i>
                        <span>Tutup Buku Sekarang</span>
                    </button>
                @else
                    <button type="button" @click="showReopenModal = true" class="px-3.5 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs border border-white/30 transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-lock-open text-xs"></i>
                        <span>Buka Kembali Buku</span>
                    </button>
                @endif

                <button type="button" @click="showBulkApproveModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-2">
                    <i class="fa-solid fa-shield-check text-sm"></i>
                    <span>Setujui Otomatis Lolos Titik Aman ({{ $safeReachedCount }})</span>
                </button>

                <a href="{{ route('inventory.planner') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-2">
                    <i class="fa-solid fa-cart-flatbed text-sm"></i>
                    <span>Rekap Belanja Barang</span>
                </a>
            </div>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- 1. Total Konsumen Aktif -->
        <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Pesanan</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalOrders) }}</div>
            <div class="text-[10px] text-slate-400 font-medium mt-0.5">Konsumen Aktif</div>
        </div>

        <!-- 2. Kas Terkumpul -->
        <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-200 shadow-2xs">
            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Kas Masuk</div>
            <div class="text-base font-black text-emerald-800 mt-1 truncate">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                {{ $totalTarget > 0 ? round(($totalPaid / $totalTarget) * 100, 1) : 0 }}% dari Target
            </div>
        </div>

        <!-- 3. Lolos Titik Aman / Lunas -->
        <a href="{{ route('owner.book-closing.index', ['tab' => 'safe']) }}" class="p-3.5 rounded-2xl bg-emerald-50 border-2 border-emerald-300 hover:border-emerald-500 shadow-2xs transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-emerald-900 uppercase">Lolos Titik Aman</span>
                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
            </div>
            <div class="text-xl font-black text-emerald-900 mt-1">{{ $safeReachedCount }}</div>
            <div class="text-[10px] text-emerald-700 font-bold mt-0.5">Full Barang Dijamin</div>
        </a>

        <!-- 4. Belum Capai Titik Aman / Macet -->
        <a href="{{ route('owner.book-closing.index', ['tab' => 'unreached']) }}" class="p-3.5 rounded-2xl bg-amber-50 border-2 border-amber-300 hover:border-amber-500 shadow-2xs transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-amber-900 uppercase">Belum Capai Aman</span>
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xs"></i>
            </div>
            <div class="text-xl font-black text-amber-900 mt-1">{{ $unreachedCount }}</div>
            <div class="text-[10px] text-amber-700 font-bold mt-0.5">Penyesuaian Owner</div>
        </a>

        <!-- 5. Sudah Selesai Diputuskan -->
        <a href="{{ route('owner.book-closing.index', ['tab' => 'decided']) }}" class="p-3.5 rounded-2xl bg-blue-50 border-2 border-blue-300 hover:border-blue-500 shadow-2xs transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-blue-900 uppercase">Sudah Diputuskan</span>
                <i class="fa-solid fa-stamp text-blue-600 text-xs"></i>
            </div>
            <div class="text-xl font-black text-blue-900 mt-1">{{ $decidedCount }}</div>
            <div class="text-[10px] text-blue-700 font-bold mt-0.5">Alokasi Belanja Siap</div>
        </a>

        <!-- 6. Perlu Keputusan -->
        <a href="{{ route('owner.book-closing.index', ['tab' => 'pending']) }}" class="p-3.5 rounded-2xl bg-rose-50 border-2 border-rose-300 hover:border-rose-500 shadow-2xs transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-rose-900 uppercase">Perlu Keputusan</span>
                <i class="fa-solid fa-clock text-rose-600 text-xs"></i>
            </div>
            <div class="text-xl font-black text-rose-900 mt-1">{{ $pendingCount }}</div>
            <div class="text-[10px] text-rose-700 font-bold mt-0.5">Menunggu Tindakan</div>
        </a>
    </div>

    <!-- TABS NAVIGASI UTAMA -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-2 sm:p-3 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('owner.book-closing.index', ['tab' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentTab === 'all' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/70' }}">
                    <i class="fa-solid fa-list-check text-xs"></i>
                    <span>Semua Konsumen ({{ $totalOrders }})</span>
                </a>

                <a href="{{ route('owner.book-closing.index', ['tab' => 'safe']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentTab === 'safe' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-800 bg-emerald-50 hover:bg-emerald-100' }}">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>Lolos Titik Aman ({{ $safeReachedCount }})</span>
                </a>

                <a href="{{ route('owner.book-closing.index', ['tab' => 'unreached']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentTab === 'unreached' ? 'bg-amber-600 text-white shadow-xs' : 'text-amber-900 bg-amber-50 hover:bg-amber-100' }}">
                    <i class="fa-solid fa-user-pen text-xs"></i>
                    <span>Belum Titik Aman / Tanpa Titik ({{ $unreachedCount }})</span>
                </a>

                <a href="{{ route('owner.book-closing.index', ['tab' => 'decided']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentTab === 'decided' ? 'bg-blue-600 text-white shadow-xs' : 'text-blue-900 bg-blue-50 hover:bg-blue-100' }}">
                    <i class="fa-solid fa-stamp text-xs"></i>
                    <span>Sudah Ditetapkan ({{ $decidedCount }})</span>
                </a>

                <a href="{{ route('owner.book-closing.index', ['tab' => 'pending']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentTab === 'pending' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-900 bg-rose-50 hover:bg-rose-100' }}">
                    <i class="fa-solid fa-hourglass-half text-xs"></i>
                    <span>Perlu Diputuskan ({{ $pendingCount }})</span>
                </a>

                <a href="{{ route('owner.book-closing.index', ['tab' => 'packages']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentTab === 'packages' ? 'bg-purple-600 text-white shadow-xs' : 'text-purple-900 bg-purple-50 hover:bg-purple-100' }}">
                    <i class="fa-solid fa-sliders text-xs"></i>
                    <span>Atur Titik Aman Master Paket</span>
                </a>
            </div>

            <!-- SEARCH & FILTER -->
            @if($currentTab !== 'packages')
                <form action="{{ route('owner.book-closing.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, hp, agen..." class="pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:outline-none focus:border-pink-500 w-48 sm:w-64">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('owner.book-closing.index', ['tab' => $currentTab]) }}" class="text-xs text-rose-600 hover:underline font-bold">Reset</a>
                    @endif
                </form>
            @endif
        </div>

        @if($currentTab === 'packages')
            <!-- ========================================== -->
            <!-- TAB: PENGATURAN TITIK AMAN MASTER PAKET   -->
            <!-- ========================================== -->
            <div class="p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-heading font-extrabold text-slate-900 text-sm flex items-center">
                            <i class="fa-solid fa-shield-halved text-purple-600 mr-2"></i>
                            Konfigurasi Titik Aman Full Parsel Masing-Masing Paket
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tentukan batas nominal setoran agar konsumen dijamin mendapatkan 100% seluruh barang isi parsel saat Tutup Buku. Kosongkan jika paket tidak memiliki titik aman.
                        </p>
                    </div>
                    <span class="text-[11px] font-bold text-purple-900 bg-purple-50 px-3 py-1 rounded-lg border border-purple-200">
                        {{ $packages->count() }} Paket Terdaftar
                    </span>
                </div>

                <form action="{{ route('owner.book-closing.update-thresholds') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/80 font-bold uppercase text-[10px] text-slate-600 border-b border-slate-200">
                                    <th class="py-3 px-3 w-12 text-center">No</th>
                                    <th class="py-3 px-3">Kode & Nama Paket</th>
                                    <th class="py-3 px-3">Jenis</th>
                                    <th class="py-3 px-3 text-right">Target Tagihan</th>
                                    <th class="py-3 px-3 text-right">Cicilan</th>
                                    <th class="py-3 px-3 text-center w-64">Titik Aman (Jumlah Setoran)</th>
                                    <th class="py-3 px-3 text-center w-28">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($packages as $idx => $pkg)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-3 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-3">
                                            <div class="font-mono text-[10px] font-extrabold text-pink-700 bg-pink-50 inline-block px-1.5 py-0.2 rounded">{{ $pkg->code }}</div>
                                            <div class="font-extrabold text-slate-900 mt-0.5">{{ $pkg->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $pkg->packageItems->count() }} jenis barang</div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $pkg->isParsel() ? 'bg-emerald-100 text-emerald-800' : ($pkg->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($pkg->type) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right font-black text-slate-900 font-mono">
                                            {{ $pkg->formatted_target }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold text-slate-600 font-mono">
                                            {{ $pkg->formatted_installment }} / {{ $pkg->period_label }}
                                        </td>
                                        <td class="py-3 px-3">
                                            <input type="hidden" name="packages[{{ $idx }}][id]" value="{{ $pkg->id }}">
                                            <div class="relative">
                                                <input type="number" 
                                                       name="packages[{{ $idx }}][safe_threshold]" 
                                                       value="{{ old("packages.{$idx}.safe_threshold", $pkg->safe_period_count ?: '') }}" 
                                                       placeholder="0 (Tanpa Titik)" 
                                                       step="1" 
                                                       min="0"
                                                       class="w-full pl-3 pr-14 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono font-bold text-slate-900 text-center focus:bg-white focus:outline-none focus:border-purple-600">
                                                <span class="absolute inset-y-0 right-0 pr-2.5 flex items-center font-bold text-purple-700 text-xs pointer-events-none">{{ $pkg->safe_period_unit }}</span>
                                            </div>
                                            @if($pkg->safe_period_count > 0)
                                                <div class="text-[10px] text-purple-800 font-semibold mt-0.5 text-right font-mono">
                                                    Setara {{ $pkg->formatted_safe_threshold }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($pkg->hasSafeThreshold())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    Ada Titik Aman
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 italic">
                                                    Tanpa Titik Aman
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-400">Belum ada paket terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-2">
                            <i class="fa-solid fa-floppy-disk text-sm"></i>
                            <span>Simpan Perubahan Titik Aman Seluruh Paket</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- ========================================== -->
            <!-- TAB: DAFTAR KONSUMEN & KEPUTUSAN TUTUP BUKU -->
            <!-- ========================================== -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 font-bold uppercase text-[10px] text-slate-600 border-b border-slate-200">
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-4">Konsumen & Agen</th>
                            <th class="py-3 px-3">Paket & Kemasan</th>
                            <th class="py-3 px-3 text-right">Setoran Masuk</th>
                            <th class="py-3 px-3 text-center">Evaluasi Titik Aman</th>
                            <th class="py-3 px-3">Keputusan Pemenuhan</th>
                            <th class="py-3 px-3 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customerPackages as $idx => $cp)
                            @php
                                $pkg = $cp->package;
                                $isSafe = $cp->isSafeThresholdReached();
                                $hasDecision = $cp->isDecisionMade();
                                $pf = $cp->partialFulfillment;
                            @endphp
                            <tr class="hover:bg-pink-50/20 transition {{ $isSafe ? 'bg-emerald-50/10' : '' }}">
                                <td class="py-3 px-3 text-center font-bold text-slate-400">
                                    {{ $customerPackages->firstItem() + $idx }}
                                </td>
                                
                                <!-- Konsumen & Agen -->
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-slate-900 text-xs">{{ $cp->customer?->name ?? 'Konsumen Umum' }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                        {{ $cp->customer?->code ?? '-' }} • {{ $cp->customer?->phone ?? '-' }}
                                    </div>
                                    <div class="text-[10px] font-bold text-pink-700 mt-1 flex items-center">
                                        <i class="fa-solid fa-user-tie text-[9px] mr-1"></i>
                                        <span>Agen: {{ $cp->agent?->name ?? 'Tanpa Agen' }}</span>
                                    </div>
                                </td>

                                <!-- Paket & Kemasan -->
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">{{ $pkg?->name ?? '-' }}</div>
                                    <div class="flex items-center space-x-1.5 mt-0.5">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase {{ $pkg?->isParsel() ? 'bg-emerald-100 text-emerald-800' : ($pkg?->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ ucfirst($pkg?->type ?? 'parsel') }}
                                        </span>
                                        @if($cp->packaging)
                                            <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.2 rounded border border-pink-200">
                                                <i class="fa-solid fa-box-archive mr-0.5"></i> {{ $cp->packaging->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Setoran Masuk & Progress -->
                                <td class="py-3 px-3 text-right">
                                    <div class="font-black text-slate-900 font-mono text-xs">
                                        Rp {{ number_format($cp->total_paid, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        dari target Rp {{ number_format($cp->total_target, 0, ',', '.') }}
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-slate-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $cp->progress_percent >= 100 ? 'bg-emerald-600' : ($isSafe ? 'bg-emerald-500' : 'bg-amber-500') }}" style="width: {{ min(100, $cp->progress_percent) }}%"></div>
                                    </div>
                                    <div class="text-[9px] font-extrabold {{ $isSafe ? 'text-emerald-700' : 'text-slate-500' }} mt-0.5 text-right">
                                        {{ $cp->progress_percent }}% Lunas
                                    </div>
                                </td>

                                <!-- Evaluasi Titik Aman -->
                                <td class="py-3 px-3 text-center">
                                    @if($cp->progress_percent >= 100)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white shadow-2xs">
                                            <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> Lunas 100%
                                        </span>
                                    @elseif($isSafe)
                                        <div class="space-y-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                <i class="fa-solid fa-shield-check mr-1 text-[9px]"></i> Lolos Titik Aman
                                            </span>
                                            <div class="text-[9px] text-emerald-700 font-mono">
                                                Min: {{ $pkg?->formatted_safe_threshold }}
                                            </div>
                                        </div>
                                    @elseif($pkg && $pkg->hasSafeThreshold())
                                        <div class="space-y-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                                <i class="fa-solid fa-triangle-exclamation mr-1 text-[9px]"></i> Belum Aman
                                            </span>
                                            <div class="text-[9px] text-amber-800 font-mono">
                                                Kurang Rp {{ number_format(max(0, ($pkg->safe_threshold_nominal ?: 0) - $cp->total_paid), 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 italic">
                                            Tanpa Titik Aman
                                        </span>
                                    @endif
                                </td>

                                <!-- Keputusan Pemenuhan -->
                                <td class="py-3 px-3">
                                    @if($hasDecision)
                                        @if($cp->fulfillment_decision === 'safe_full')
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-600 text-white">
                                                    <i class="fa-solid fa-gift mr-1"></i> Full Parsel Dijamin Utuh
                                                </span>
                                                @if($pf && $pf->cash_payout > 0)
                                                    <div class="text-[10px] font-bold text-emerald-800 font-mono">
                                                        + Uang: Rp {{ number_format($pf->cash_payout, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                                <div class="text-[9px] text-slate-500">Diputuskan oleh {{ $cp->decidedBy?->name ?? 'Owner' }}</div>
                                            </div>
                                        @elseif($cp->fulfillment_decision === 'custom_partial' || $cp->fulfillment_status === 'partial')
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-600 text-white">
                                                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Disesuaikan Owner
                                                </span>
                                                @if($pf)
                                                    <div class="text-[10px] text-slate-700">
                                                        {{ $pf->items->count() }} barang (Rp {{ number_format($pf->total_items_value, 0, ',', '.') }})
                                                        @if($pf->cash_payout > 0)
                                                            • Uang Rp {{ number_format($pf->cash_payout, 0, ',', '.') }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($cp->fulfillment_decision === 'cash_only')
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-600 text-white">
                                                    <i class="fa-solid fa-money-bill-wave mr-1"></i> Cair Uang Tunai
                                                </span>
                                                <div class="text-[10px] font-bold text-amber-900 font-mono">
                                                    Rp {{ number_format($pf?->cash_payout ?? 0, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        @if($isSafe)
                                            <div class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 p-1.5 rounded-lg border border-emerald-200">
                                                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Siap disetujui full parsel
                                            </div>
                                        @else
                                            <div class="text-[10px] font-bold text-amber-800 bg-amber-50 p-1.5 rounded-lg border border-amber-200">
                                                <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Menunggu penyesuaian barang
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button" 
                                                @click="openDecisionModal(@js($cp))" 
                                                class="px-2.5 py-1.5 rounded-lg font-extrabold text-[11px] transition shadow-2xs flex items-center space-x-1 {{ $hasDecision ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : ($isSafe ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white') }}"
                                                title="Tentukan Barang & Uang untuk Konsumen Ini">
                                            <i class="fa-solid {{ $hasDecision ? 'fa-pen-to-square' : 'fa-hand-holding-hand' }}"></i>
                                            <span>{{ $hasDecision ? 'Ubah' : 'Tentukan' }}</span>
                                        </button>

                                        @if($hasDecision)
                                            <a href="{{ route('owner.book-closing.receipt', $cp) }}" 
                                               target="_blank" 
                                               class="p-1.5 rounded-lg bg-white border border-slate-300 hover:border-pink-500 text-slate-700 hover:text-pink-600 transition" 
                                               title="Cetak Berita Acara / Surat Keputusan Tutup Buku">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                                    Tidak ada data konsumen yang sesuai dengan filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="p-4 border-t border-slate-200">
                {{ $customerPackages->links() }}
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: KONFIRMASI TUTUP BUKU SEKARANG                  -->
    <!-- ======================================================== -->
    <div x-show="showCloseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-rose-200 space-y-4" @click.away="showCloseModal = false">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl shrink-0 font-black">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-slate-900 text-base">Aktifkan Periode Tutup Buku?</h3>
                    <p class="text-xs text-slate-500">Musim parsel akan dinyatakan berakhir dan memasuki tahap pemenuhan barang.</p>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-950 space-y-1.5">
                <div class="font-bold flex items-center text-amber-900">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600"></i> Ketentuan Saat Tutup Buku:
                </div>
                <ul class="list-disc list-inside space-y-1 text-[11px] text-amber-900/90 font-medium pl-1">
                    <li>Konsumen yang mencapai <strong>Titik Aman Full Parsel</strong> dijamin menerima seluruh barang parsel secara utuh.</li>
                    <li>Konsumen yang belum mencapai titik aman atau paket tanpa titik aman dapat disesuaikan barang dan uangnya oleh Owner.</li>
                    <li>Seluruh keputusan yang Anda simpan otomatis dialokasikan ke <strong>Rekap Belanja Grosir Barang</strong>.</li>
                </ul>
            </div>

            <form action="{{ route('owner.book-closing.close') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pengesahan Tutup Buku (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Tutup Buku Musim Lebaran 1447 H resmi disahkan. Penyerahan parsel mulai H-7." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:border-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCloseModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Ya, Sahkan Tutup Buku Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: KONFIRMASI BUKA KEMBALI TUTUP BUKU              -->
    <!-- ======================================================== -->
    <div x-show="showReopenModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4" @click.away="showReopenModal = false">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0 font-black">
                    <i class="fa-solid fa-lock-open"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-slate-900 text-base">Buka Kembali Tutup Buku?</h3>
                    <p class="text-xs text-slate-500">Periode setoran akan kembali aktif dan status tutup buku dicabut.</p>
                </div>
            </div>

            <form action="{{ route('owner.book-closing.reopen') }}" method="POST">
                @csrf
                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showReopenModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs shadow-md transition">
                        Buka Kembali Buku
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: BULK APPROVE LOLOS TITIK AMAN                  -->
    <!-- ======================================================== -->
    <div x-show="showBulkApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-emerald-200 space-y-4" @click.away="showBulkApproveModal = false">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0 font-black">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-slate-900 text-base">Setujui Otomatis Lolos Titik Aman</h3>
                    <p class="text-xs text-slate-500">Sebanyak <strong>{{ $safeReachedCount }} konsumen</strong> telah mencapai Titik Aman Full Parsel atau Lunas.</p>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 font-medium">
                Sistem akan otomatis menetapkan status pemenuhan <strong>FULL PARSEL</strong> untuk seluruh konsumen yang lolos titik aman, mengalokasikan seluruh isi paket ke persiapan belanja kulakan, dan memberikan hak parsel 100%.
            </div>

            <form action="{{ route('owner.book-closing.bulk-approve-safe') }}" method="POST">
                @csrf
                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showBulkApproveModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-check-double mr-1"></i>
                        <span>Setujui Otomatis Sekarang ({{ $safeReachedCount }} Konsumen)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 4: KEPUTUSAN PEMENUHAN KONSUMEN (INTERAKTIF)       -->
    <!-- ======================================================== -->
    <div x-show="showDecisionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-5 sm:p-6 shadow-2xl border border-pink-200 space-y-4 max-h-[92vh] flex flex-col" @click.away="showDecisionModal = false">
            
            <!-- HEADER MODAL -->
            <div class="flex items-start justify-between border-b border-slate-200 pb-3">
                <div class="space-y-0.5">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-pink-600"></span>
                        <h3 class="font-heading font-extrabold text-slate-900 text-base">
                            Keputusan Pemenuhan Tutup Buku
                        </h3>
                    </div>
                    <p class="text-xs text-slate-500" x-show="activePackage">
                        Konsumen: <strong class="text-slate-900" x-text="activePackage?.customer?.name"></strong> 
                        (<span class="font-mono" x-text="activePackage?.customer?.code"></span>) • 
                        Paket: <strong class="text-pink-700" x-text="activePackage?.package?.name"></strong>
                    </p>
                </div>
                <button type="button" @click="showDecisionModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- BODY MODAL (SCROLLABLE) -->
            <div class="flex-1 overflow-y-auto space-y-4 pr-1 text-xs">
                
                <!-- SUMMARY INFORMASI KONSUMEN & TITIK AMAN -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Total Setoran Masuk</div>
                        <div class="text-sm font-black text-emerald-800 font-mono mt-0.5" x-text="formatRp(activePackage?.total_paid)"></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Target Tagihan</div>
                        <div class="text-sm font-black text-slate-900 font-mono mt-0.5" x-text="formatRp(activePackage?.total_target)"></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Harga Semua Barang</div>
                        <div class="text-xs font-black text-pink-800 font-mono mt-0.5" x-text="formatRp(activePackage?.goods_value || 0)"></div>
                        <div class="text-[9px] text-slate-400 font-medium">(Target - Uang Paket)</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Titik Aman & Status</div>
                        <div class="text-xs font-bold mt-0.5 font-mono" 
                             :class="activePackage?.package?.has_safe_threshold || activePackage?.package?.safe_period_count > 0 ? 'text-purple-700' : 'text-slate-400 italic'"
                             x-text="activePackage?.package?.formatted_safe_threshold || 'Tanpa Titik Aman'"></div>
                        <div class="mt-0.5">
                            <template x-if="isPackageSafe(activePackage)">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <i class="fa-solid fa-check text-[9px] mr-0.5"></i> Lolos Aman
                                </span>
                            </template>
                            <template x-if="!isPackageSafe(activePackage)">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                    <i class="fa-solid fa-triangle-exclamation text-[9px] mr-0.5"></i> Belum Aman
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- BANNER REKOMENDASI UANG TUNAI SPL -->
                <div class="p-3 bg-emerald-50/80 rounded-xl border border-emerald-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="space-y-0.5">
                        <div class="font-extrabold text-xs text-emerald-950 flex items-center">
                            <i class="fa-solid fa-calculator text-emerald-600 mr-1.5"></i>
                            <span>Rekomendasi Uang Tunai Konsumen: <strong class="text-emerald-800 font-mono text-sm ml-1" x-text="formatRp(activePackage?.recommended_cash || 0)"></strong></span>
                        </div>
                        <p class="text-[11px] text-emerald-900/90 font-medium">
                            Hitungan: Dari setoran masuk <strong class="font-mono" x-text="formatRp(activePackage?.total_paid)"></strong> dikurangi harga barang paket <strong class="font-mono" x-text="formatRp(activePackage?.goods_value)"></strong> = Sisa <strong class="font-mono text-emerald-800" x-text="formatRp(activePackage?.recommended_cash || 0)"></strong>.
                        </p>
                    </div>
                    <button type="button" @click="cashPayout = Number(activePackage?.recommended_cash || 0)" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shrink-0 shadow-2xs transition flex items-center self-start sm:self-auto space-x-1">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>
                        <span>Gunakan Rekomendasi (<span x-text="formatRp(activePackage?.recommended_cash || 0)"></span>)</span>
                    </button>
                </div>

                <form :action="`/owner/book-closing/${activePackage?.id}/decision`" method="POST" id="decisionForm" class="space-y-4">
                    @csrf

                    <!-- 1. PILIHAN MODE KEPUTUSAN -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1.5">1. Pilihan Mode Pemenuhan Tutup Buku *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-2"
                                   :class="decisionType === 'safe_full' ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 font-bold' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="decision_type" value="safe_full" x-model="decisionType" class="mt-0.5 text-emerald-600">
                                <div>
                                    <div class="text-xs font-bold">Full Parsel Dijamin</div>
                                    <div class="text-[10px] font-normal text-slate-500">Berikan 100% seluruh barang isi parsel utuh</div>
                                </div>
                            </label>

                            <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-2"
                                   :class="decisionType === 'custom_goods' ? 'border-blue-600 bg-blue-50/70 text-blue-950 font-bold' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="decision_type" value="custom_goods" x-model="decisionType" class="mt-0.5 text-blue-600">
                                <div>
                                    <div class="text-xs font-bold">Kustomisasi Barang</div>
                                    <div class="text-[10px] font-normal text-slate-500">Owner pilihkan jenis & jumlah barang pengganti</div>
                                </div>
                            </label>

                            <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-2"
                                   :class="decisionType === 'cash_only' ? 'border-amber-600 bg-amber-50/70 text-amber-950 font-bold' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="decision_type" value="cash_only" x-model="decisionType" class="mt-0.5 text-amber-600">
                                <div>
                                    <div class="text-xs font-bold">Pencairan Dana Uang</div>
                                    <div class="text-[10px] font-normal text-slate-500">Khusus tabungan / pengembalian dana</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. PEMILIHAN BARANG (JIKA KUSTOMISASI BARANG ATAU EDIT ITEM) -->
                    <div x-show="decisionType === 'custom_goods'" class="space-y-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-extrabold text-slate-800 text-xs">Daftar Barang yang Diberikan (Owner yang Memilih)</label>
                                <p class="text-[10px] text-slate-500">Pilih barang dari master item SPL dan tentukan kuantitasnya.</p>
                            </div>
                            <button type="button" @click="addItemRow()" class="px-2.5 py-1 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-2xs">
                                <i class="fa-solid fa-plus mr-1"></i> Tambah Barang
                            </button>
                        </div>

                        <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                            <template x-for="(row, idx) in selectedItems" :key="idx">
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-2xs">
                                    <div class="flex-1 min-w-0">
                                        <select :name="`items[${idx}][item_id]`" x-model="row.item_id" @change="onItemSelect(row)" class="w-full p-1.5 bg-slate-50 border border-slate-300 rounded text-xs font-medium focus:outline-none">
                                            <option value="">-- Pilih Barang Master --</option>
                                            <template x-for="item in masterItems" :key="item.id">
                                                <option :value="item.id" x-text="`${item.name} (${item.category || 'Barang'}) - Rp ${Number(item.unit_price).toLocaleString('id-ID')}`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="w-20">
                                        <input type="number" :name="`items[${idx}][quantity]`" x-model.number="row.quantity" min="1" placeholder="Qty" class="w-full p-1.5 bg-slate-50 border border-slate-300 rounded text-xs text-center font-bold">
                                    </div>
                                    <div class="w-28 text-right font-mono font-bold text-slate-800" x-text="formatRp(row.unit_price * row.quantity)"></div>
                                    <button type="button" @click="removeItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-1">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-200 font-bold text-xs">
                            <span class="text-slate-600">Total Nilai Barang Dipilih:</span>
                            <span class="font-black text-pink-700 font-mono text-sm" x-text="formatRp(calculateItemsTotal())"></span>
                        </div>
                    </div>

                    <!-- 3. KOMPONEN UANG TUNAI & BIAYA ADMIN -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-200">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-bold text-amber-950 text-xs">
                                    Nominal Uang Tunai yang Diberikan (Rp)
                                </label>
                                <button type="button" @click="cashPayout = Number(activePackage?.recommended_cash || 0)" class="text-[10px] font-extrabold text-emerald-700 hover:text-emerald-900 underline">
                                    Pakai Rekomendasi
                                </button>
                            </div>
                            <input type="number" name="cash_payout" x-model.number="cashPayout" min="0" step="any" placeholder="0" class="w-full p-2 bg-white border border-amber-300 rounded-lg text-xs font-mono font-bold text-amber-950 focus:outline-none focus:border-amber-600">
                            <span class="text-[10px] text-slate-500 block mt-1">Diisi nominal uang tunai yang disepakati Owner untuk konsumen ini.</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="block font-bold text-slate-800 text-xs mb-1">
                                Potongan Biaya Admin SPL (Rp)
                            </label>
                            <input type="number" name="admin_fee" x-model.number="adminFee" min="0" step="1000" placeholder="0" class="w-full p-2 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-500">
                            <span class="text-[10px] text-slate-500 block mt-1">Potongan administrasi/penalti jika ada.</span>
                        </div>
                    </div>

                    <!-- 4. HADIAH BONUS & CATATAN -->
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 cursor-pointer p-2.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-100/50">
                            <input type="checkbox" name="grant_bonus" value="1" x-model="grantBonus" class="rounded text-pink-600 focus:ring-pink-500">
                            <div>
                                <span class="font-bold text-slate-800 text-xs">Berikan Hadiah Bonus Paket</span>
                                <span class="text-[10px] text-slate-500 block">Centang jika konsumen tetap berhak menerima barang hadiah bonus paket.</span>
                            </div>
                        </label>

                        <div>
                            <label class="block font-bold text-slate-800 text-xs mb-1">Catatan Keputusan Tutup Buku</label>
                            <textarea name="notes" x-model="decisionNotes" rows="2" placeholder="Catatan atau alasan serah terima dari Owner..." class="w-full p-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:border-pink-500"></textarea>
                        </div>
                    </div>

                    <!-- FOOTER ACTIONS -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="showDecisionModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-floppy-disk mr-1"></i>
                            <span>Simpan Keputusan Tutup Buku</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function bookClosingManager() {
    return {
        showCloseModal: false,
        showReopenModal: false,
        showBulkApproveModal: false,
        showDecisionModal: false,
        activePackage: null,
        decisionType: 'safe_full',
        cashPayout: 0,
        adminFee: 0,
        grantBonus: true,
        decisionNotes: '',
        selectedItems: [],
        masterItems: @json($masterItems),

        isPackageSafe(cp) {
            if (!cp) return false;
            if (cp.is_safe_reached !== undefined) return Boolean(cp.is_safe_reached);
            const paid = Number(cp.total_paid || 0);
            const target = Number(cp.total_target || 0);
            if (target > 0 && paid >= target) return true;
            const safeNominal = Number(cp.package?.safe_threshold_nominal || 0);
            if (safeNominal > 0 && paid >= safeNominal) return true;
            const safe = Number(cp.package?.safe_threshold || 0);
            if (safe > 0 && paid >= safe) return true;
            return false;
        },

        openDecisionModal(cp) {
            this.activePackage = cp;
            const isSafe = this.isPackageSafe(cp);
            this.decisionType = cp.fulfillment_decision || (isSafe ? 'safe_full' : 'custom_goods');
            
            if (cp.partial_fulfillment && cp.partial_fulfillment.cash_payout !== null && cp.partial_fulfillment.cash_payout !== undefined) {
                this.cashPayout = Number(cp.partial_fulfillment.cash_payout || 0);
            } else if (cp.recommended_cash !== undefined && Number(cp.recommended_cash) > 0) {
                this.cashPayout = Number(cp.recommended_cash);
            } else {
                this.cashPayout = 0;
            }

            this.adminFee = cp.partial_fulfillment ? Number(cp.partial_fulfillment.admin_fee || 0) : 0;
            this.grantBonus = !cp.is_bonus_forfeited;
            this.decisionNotes = cp.partial_fulfillment ? (cp.partial_fulfillment.notes || '') : '';
            
            this.selectedItems = [];
            if (cp.partial_fulfillment && cp.partial_fulfillment.items && cp.partial_fulfillment.items.length > 0) {
                this.selectedItems = cp.partial_fulfillment.items.map(it => ({
                    item_id: it.item_id,
                    item_name: it.item?.name || '',
                    unit_price: Number(it.unit_price || 0),
                    quantity: Number(it.quantity || 1),
                    is_bonus: Boolean(it.is_bonus)
                }));
            } else if (cp.package && cp.package.package_items) {
                this.selectedItems = cp.package.package_items.map(pi => ({
                    item_id: pi.item_id,
                    item_name: pi.item?.name || '',
                    unit_price: Number(pi.item?.unit_price || 0),
                    quantity: Number(pi.quantity || 1),
                    is_bonus: Boolean(pi.is_bonus)
                }));
            }
            
            this.showDecisionModal = true;
        },

        addItemRow() {
            this.selectedItems.push({
                item_id: '',
                item_name: '',
                unit_price: 0,
                quantity: 1,
                is_bonus: false
            });
        },

        removeItemRow(idx) {
            this.selectedItems.splice(idx, 1);
        },

        onItemSelect(row) {
            const found = this.masterItems.find(m => m.id == row.item_id);
            if (found) {
                row.item_name = found.name;
                row.unit_price = Number(found.unit_price || 0);
            }
        },

        calculateItemsTotal() {
            return this.selectedItems.reduce((acc, r) => acc + (Number(r.unit_price || 0) * Number(r.quantity || 1)), 0);
        },

        formatRp(num) {
            return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endpush

