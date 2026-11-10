@extends('layouts.app')

@section('title', 'Dasbor Finansial Owner')
@section('page_title', 'Dasbor Eksekutif & Rekap Finansial')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        <i class="fa-solid fa-crown mr-1 text-amber-500"></i> Ibu Susi (Owner)
    </span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- HERO / WELCOME BANNER -->
    <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold bg-white/20 text-white backdrop-blur mb-2">
                    Musim Lebaran 1447 H / 2026
                </span>
                <h2 class="text-xl md:text-2xl font-heading font-extrabold tracking-tight">Selamat Datang, {{ auth()->user()->name }}</h2>
                <p class="text-xs md:text-sm text-emerald-100 max-w-xl mt-1">
                    Operasional SPL berjalan aktif. Pantau arus kas masuk dari lapangan, validasi setoran agen, dan sesuaikan paket konsumen menunggak secara akurat.
                </p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('owner.agents.index') }}" class="px-3.5 py-2 rounded-xl bg-white text-emerald-800 font-bold text-xs hover:bg-emerald-50 transition shadow-sm flex items-center">
                    <i class="fa-solid fa-users-gear mr-1.5 text-emerald-600"></i> Menu Agen
                </a>
                <a href="{{ route('owner.deposits.index') }}" class="px-3.5 py-2 rounded-xl bg-white text-emerald-800 font-bold text-xs hover:bg-emerald-50 transition shadow-sm flex items-center">
                    <i class="fa-solid fa-money-bill-transfer mr-1.5 text-emerald-600"></i> Rekonsiliasi Agen
                    @if($pendingDeposits->where('status', 'submitted')->count() > 0)
                        <span class="ml-1.5 bg-amber-500 text-white text-[10px] px-1.5 py-0.2 rounded-full font-extrabold animate-pulse">
                            {{ $pendingDeposits->where('status', 'submitted')->count() }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('inventory.planner') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600/80 hover:bg-emerald-600 border border-white/20 text-white font-bold text-xs transition flex items-center">
                    <i class="fa-solid fa-cart-flatbed mr-1.5 text-amber-300"></i> Belanja Barang
                </a>
                <a href="{{ route('owner.book-closing.index') }}" class="px-3.5 py-2 rounded-xl {{ $appSetting->is_book_closed ? 'bg-rose-700 hover:bg-rose-800' : 'bg-pink-700/80 hover:bg-pink-700' }} border border-white/20 text-white font-bold text-xs transition flex items-center">
                    <i class="fa-solid fa-lock mr-1.5 {{ $appSetting->is_book_closed ? 'animate-pulse' : '' }}"></i> Tutup Buku
                </a>
                <a href="{{ route('owner.settings.index') }}" class="px-3.5 py-2 rounded-xl bg-emerald-900/60 hover:bg-emerald-900 border border-white/20 text-white font-bold text-xs transition flex items-center" title="Pengaturan Aplikasi & Reset">
                    <i class="fa-solid fa-sliders mr-1.5 text-emerald-200"></i> Pengaturan
                </a>
            </div>
        </div>
        <!-- Decorative Islamic geometric pattern simulation -->
        <div class="absolute -right-8 -bottom-12 opacity-10 text-white text-9xl pointer-events-none">
            <i class="fa-solid fa-mosque"></i>
        </div>
    </div>

    <!-- 4 FINANCIAL KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Target Nominal -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Target Piutang</div>
                <div class="text-xl font-heading font-extrabold text-slate-900 mt-1">
                    Rp {{ number_format($totalTarget, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    <span class="font-semibold text-slate-700">{{ $totalActivePackages }}</span> paket aktif terdaftar
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-bullseye"></i>
            </div>
        </div>

        <!-- 2. Kas Masuk Sah (Verified) -->
        <div class="bg-white rounded-xl p-5 border border-emerald-200 shadow-xs flex items-center justify-between bg-emerald-50/20">
            <div>
                <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Kas Masuk Terverifikasi</div>
                <div class="text-xl font-heading font-extrabold text-emerald-700 mt-1">
                    Rp {{ number_format($totalVerifiedPaid, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-emerald-800 mt-1 font-medium">
                    <i class="fa-solid fa-shield-check mr-1 text-emerald-600"></i> Sudah disahkan Ibu Susi
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <!-- 3. Kas di Lapangan (Pending/Submitted) -->
        <div class="bg-white rounded-xl p-5 border border-amber-200 shadow-xs flex items-center justify-between bg-amber-50/20">
            <div>
                <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Kas Menunggu Sah</div>
                <div class="text-xl font-heading font-extrabold text-amber-600 mt-1">
                    Rp {{ number_format($totalPendingDeposit, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-amber-700 mt-1 font-medium">
                    <i class="fa-solid fa-clock mr-1"></i> Sedang di tangan agen lapangan
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <!-- 4. Sisa Piutang Lapangan -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sisa Belum Tertagih</div>
                <div class="text-xl font-heading font-extrabold text-slate-700 mt-1">
                    Rp {{ number_format($remainingReceivable, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Akumulasi sisa cicilan minggu depan
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-lg">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT COLUMN (2 COLS): SETORAN AGEN & KONSUMEN MENUNGGAK -->
        <div class="lg:col-span-2 space-y-6">

            <!-- SETORAN AGEN MENUNGGU VALIDASI -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 md:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i>
                        <h3 class="font-heading font-bold text-slate-800 text-sm md:text-base">Setoran Agen Masuk (Batching)</h3>
                    </div>
                    <a href="{{ route('owner.deposits.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                        Lihat Semua <i class="fa-solid fa-chevron-right ml-1"></i>
                    </a>
                </div>

                @if($pendingDeposits->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-500"></i>
                        <div class="text-sm font-medium">Semua setoran agen sudah divalidasi dan rapi.</div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                                    <th class="py-3 px-4">No. Setoran</th>
                                    <th class="py-3 px-4">Nama Agen</th>
                                    <th class="py-3 px-4">Total Setoran</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($pendingDeposits as $dep)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4 font-mono font-medium text-slate-700">
                                            {{ $dep->deposit_number }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-800">{{ $dep->agent->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $dep->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-emerald-700">
                                            {{ $dep->formatted_total }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($dep->status === 'submitted')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    <i class="fa-solid fa-bell mr-1 animate-pulse"></i> Diajukan Agen
                                                </span>
                                            @elseif($dep->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                                    <i class="fa-solid fa-basket-shopping mr-1"></i> Keranjang Berjalan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right space-x-1">
                                            <a href="{{ route('owner.deposits.show', $dep) }}" class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                                Periksa & Sahkan
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- KONSUMEN MENUNGGAK & RAWAN (FITUR ATURAN TABUNGAN & MINI-POS) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 md:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                            <h3 class="font-heading font-bold text-slate-800 text-sm md:text-base">Konsumen Menunggak & Rawan</h3>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Monitoring aturan 3 minggu bolong (Bonus Hangus) & persiapan Kasir Mini-POS</div>
                    </div>
                    <a href="{{ route('mini-pos.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                        Buka Mini-POS <i class="fa-solid fa-cash-register ml-1"></i>
                    </a>
                </div>

                @if($laggingCustomers->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-check text-2xl text-emerald-500 mb-1"></i>
                        <div class="text-sm">Tidak ada tunggakan kritis saat ini.</div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                                    <th class="py-3 px-4">Konsumen</th>
                                    <th class="py-3 px-4">Paket & Agen</th>
                                    <th class="py-3 px-4">Saldo Masuk</th>
                                    <th class="py-3 px-4">Status Aturan</th>
                                    <th class="py-3 px-4 text-right">Tindakan Mini-POS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($laggingCustomers as $cp)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900">{{ $cp->customer->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $cp->customer->phone ?: 'No Telp -' }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-slate-700">{{ $cp->package->name }}</div>
                                            <div class="text-[10px] text-slate-500">Agen: {{ $cp->agent->name }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-800">{{ $cp->formatted_paid }}</div>
                                            <div class="text-[10px] text-slate-400">dari {{ $cp->formatted_target }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($cp->is_bonus_forfeited)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200" title="{{ $cp->forfeit_reason }}">
                                                    <i class="fa-solid fa-ban mr-1"></i> Bonus Hangus (≥3 Mgg)
                                                </span>
                                            @elseif($cp->consecutive_unpaid_weeks > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    {{ $cp->consecutive_unpaid_weeks }} Mgg Bolong
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                                    Belum Lunas
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('mini-pos.pos', $cp) }}" class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-500 text-white hover:bg-amber-600 transition shadow-xs">
                                                <i class="fa-solid fa-sliders mr-1"></i> Sesuaikan
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        <!-- RIGHT COLUMN (1 COL): PERFORMA AGEN & SHORTCUTS -->
        <div class="space-y-6">

            <!-- FITUR CEPAT ERP OWNER -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                <h3 class="font-heading font-bold text-slate-800 text-sm">Aksi Cepat Owner</h3>
                
                <a href="{{ route('inventory.planner') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/30 transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-boxes-packing"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition">Kalkulator Grosir</div>
                            <div class="text-[10px] text-slate-500">Hitung kebutuhan kulakan dus/karton</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:translate-x-1 group-hover:text-emerald-600 transition"></i>
                </a>

                <a href="{{ route('owner.agents.report') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/30 transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition">Laporan Agen & Paket</div>
                            <div class="text-[10px] text-slate-500">Persebaran paket & sub-data konsumen</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:translate-x-1 group-hover:text-emerald-600 transition"></i>
                </a>

                <a href="{{ route('catalog.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/30 transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-print"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition">Cetak Katalog Brosur</div>
                            <div class="text-[10px] text-slate-500">Brosur Parsel 4 Kolom & Tabungan</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:translate-x-1 group-hover:text-emerald-600 transition"></i>
                </a>

                <a href="{{ route('packages.create') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/30 transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition">Buat Paket Baru</div>
                            <div class="text-[10px] text-slate-500">Tambah paket parsel / tabungan uang</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:translate-x-1 group-hover:text-emerald-600 transition"></i>
                </a>

                <form action="{{ route('social.cleanup') }}" method="POST" onsubmit="return confirm('Jalankan pembersihan foto postingan lama (>60 hari)?');">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-red-400 hover:bg-red-50/30 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 group-hover:bg-red-100 group-hover:text-red-700 transition flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-broom"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 group-hover:text-red-700 transition">Auto-Cleanup Foto</div>
                                <div class="text-[10px] text-slate-500">Hapus arsip feed > 60 hari</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-trash-can text-xs text-slate-400 group-hover:text-red-600 transition"></i>
                    </button>
                </form>
            </div>

            <!-- PERFORMA SETORAN AGEN LAPANGAN -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                <h3 class="font-heading font-bold text-slate-800 text-sm mb-3 flex items-center justify-between">
                    <span>Performa Agen Lapangan</span>
                    <span class="text-[11px] font-normal text-slate-400">{{ count($agents) }} Agen Aktif</span>
                </h3>

                <div class="space-y-3">
                    @foreach($agents as $agent)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    </div>
                                    <div class="text-xs font-bold text-slate-800">{{ $agent->name }}</div>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-500">
                                    {{ $agent->customers_count }} Konsumen
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-slate-200/60">
                                <div>
                                    <div class="text-slate-400 text-[10px]">Tersahkan:</div>
                                    <div class="font-bold text-emerald-700">Rp {{ number_format($agent->total_verified, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-400 text-[10px]">Di Tangan (Belum Sah):</div>
                                    <div class="font-bold text-amber-600">Rp {{ number_format($agent->total_in_cart, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- DISTRIBUSI JENIS PAKET -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                <h3 class="font-heading font-bold text-slate-800 text-sm mb-3">Pilihan Konsumen</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50">
                        <span class="font-medium text-emerald-950"><i class="fa-solid fa-gift mr-1 text-emerald-600"></i> Paket Parsel Barang</span>
                        <span class="font-bold text-emerald-700">{{ $packageStats['parsel'] }} Pesanan</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-amber-50/50">
                        <span class="font-medium text-amber-950"><i class="fa-solid fa-coins mr-1 text-amber-600"></i> Tabungan Uang (+Bonus)</span>
                        <span class="font-bold text-amber-700">{{ $packageStats['tabungan'] }} Pesanan</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-blue-50/50">
                        <span class="font-medium text-blue-950"><i class="fa-solid fa-cubes mr-1 text-blue-600"></i> Paket Eceran Dinamis</span>
                        <span class="font-bold text-blue-700">{{ $packageStats['ecer'] }} Pesanan</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

