@extends('layouts.app')

@section('title', 'Menu Belanja Barang, Bonus & Kemasan - Kalkulator Grosir')
@section('page_title', 'Menu Belanja Barang, Bonus & Kemasan Parsel')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-900 border border-pink-200">
        Menu Belanja Owner
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'packages', previewPackagingPhoto: null, previewPackagingTitle: '', openedPackage: null }">

    <!-- INTRO HEADER -->
    <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-5 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-pink-600 inline-block"></span>
                <h2 class="text-lg font-heading font-extrabold text-slate-900">Rekapan Jumlah Paket & Kebutuhan Belanja Owner</h2>
            </div>
            <p class="text-xs text-slate-600 max-w-3xl mt-1 leading-relaxed">
                Kalkulator lengkap pemenuhan pesanan konsumen: rekapitulasi <strong>Jumlah Paket Masing-Masing Paket yang Harus Disiapkan</strong>, rincian <strong>Barang Pokok</strong>, <strong>Hadiah Bonus</strong>, <strong>Kemasan/Wadah</strong>, serta <strong>Uang Tunai</strong> yang harus dibelanjakan dan disiapkan owner.
            </p>
        </div>
        <div class="flex items-center space-x-2 flex-shrink-0">
            <a href="{{ route('inventory.print') }}" target="_blank" class="px-4 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Lembar Rekap & Belanja (Print)
            </a>
        </div>
    </div>

    <!-- INTEGRASI STATUS TUTUP BUKU -->
    <div class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs {{ $appSetting->is_book_closed ? 'bg-gradient-to-r from-rose-50 via-pink-50 to-rose-100 border-rose-300 text-rose-950' : 'bg-emerald-50/60 border-emerald-200 text-emerald-950' }}">
        <div class="flex items-start sm:items-center space-x-3">
            <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $appSetting->is_book_closed ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white' }}">
                <i class="fa-solid {{ $appSetting->is_book_closed ? 'fa-lock' : 'fa-shield-halved' }}"></i>
            </span>
            <div>
                <div class="text-xs font-black uppercase tracking-wider flex items-center gap-1.5">
                    <span>Integrasi Keputusan Tutup Buku SPL</span>
                    @if($appSetting->is_book_closed)
                        <span class="px-2 py-0.2 rounded-full text-[9px] font-black bg-rose-600 text-white uppercase">Tutup Buku Aktif</span>
                    @else
                        <span class="px-2 py-0.2 rounded-full text-[9px] font-extrabold bg-emerald-200 text-emerald-900 uppercase">Periode Berjalan</span>
                    @endif
                </div>
                <div class="text-[11px] font-medium text-slate-600 mt-0.5">
                    Seluruh keputusan pemenuhan barang pengganti, uang tunai, dan bonus yang disahkan oleh Owner/Admin pada menu Tutup Buku secara otomatis dan presisi direkapitulasi ke dalam daftar belanja kulakan di bawah ini.
                </div>
            </div>
        </div>
        <a href="{{ route('owner.book-closing.index') }}" class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-300 hover:border-pink-500 text-slate-800 font-extrabold text-xs shrink-0 shadow-2xs transition flex items-center space-x-1.5">
            <i class="fa-solid fa-sliders text-pink-600"></i>
            <span>Menu Tutup Buku</span>
        </a>
    </div>

    <!-- BANNER PERINGATAN KONSUMEN TIDAK SELESAI / BELUM TITIK AMAN -->
    @if(count($excludedIncompleteCustomers) > 0)
        <div class="p-4 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 flex flex-col md:flex-row md:items-center md:justify-between gap-3 shadow-2xs">
            <div class="flex items-start space-x-3">
                <span class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-base shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <div class="text-xs font-black uppercase tracking-wider text-amber-950 flex items-center gap-2">
                        <span>Penyaringan Titik Aman & Ketetapan Barang Owner</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900 uppercase">
                            {{ count($excludedIncompleteCustomers) }} Konsumen Belum Masuk Belanja
                        </span>
                    </div>
                    <p class="text-xs text-amber-900 mt-1 leading-relaxed">
                        Sesuai SOP SPL, yang masuk daftar belanja grosir hanyalah konsumen yang <strong>sudah melebihi/mencapai titik aman</strong> atau <strong>konsumen tidak selesai yang sudah ditentukan alokasi barangnya oleh Owner</strong>. Sebanyak <strong>{{ count($excludedIncompleteCustomers) }} konsumen</strong> yang belum aman dan belum ditentukan barangnya <em>dikecualikan dari daftar belanja</em> agar tidak terjadi pemborosan modal belanja kulakan.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" @click="activeTab = 'excluded'" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs shadow-xs transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-list-ul"></i>
                    <span>Lihat {{ count($excludedIncompleteCustomers) }} Konsumen</span>
                </button>
                <a href="{{ route('owner.book-closing.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-amber-300 hover:bg-amber-100 text-amber-950 font-extrabold text-xs shadow-2xs transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Tentukan Barang di Tutup Buku</span>
                </a>
            </div>
        </div>
    @endif

    <!-- 6 EXECUTIVE STAT CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <!-- Card 0: Paket Harus Disiapkan -->
        <div class="bg-white rounded-xl p-3.5 border-2 border-pink-400 shadow-xs flex items-center justify-between bg-gradient-to-br from-pink-50/40 to-white">
            <div>
                <div class="text-[10px] font-bold text-pink-900 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-boxes-packing mr-1 text-pink-600"></i> Paket Harus Disiapkan
                </div>
                <div class="text-xl font-heading font-black text-pink-900 mt-0.5">
                    {{ number_format($summary['total_packages_count'] ?? 0) }} <span class="text-xs font-bold text-slate-500">Paket</span>
                </div>
                <div class="text-[10px] text-slate-600 mt-0.5">
                    <strong>{{ $summary['total_packages_pending'] ?? 0 }}</strong> Pending &bull; {{ $summary['total_packages_fulfilled'] ?? 0 }} Selesai
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-pink-600 text-white flex items-center justify-center text-base shrink-0 shadow-xs">
                <i class="fa-solid fa-box-check"></i>
            </div>
        </div>

        <!-- Card 1: Barang Pokok -->
        <div class="bg-white rounded-xl p-3.5 border border-pink-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-cart-flatbed mr-1 text-pink-600"></i> Barang Pokok
                </div>
                <div class="text-base font-heading font-extrabold text-pink-900 mt-0.5">
                    Rp {{ number_format($summary['total_estimated_cost'], 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    <strong>{{ number_format($summary['total_base_pieces']) }}</strong> Pcs ({{ $summary['total_unique_items'] }} Macam)
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <!-- Card 2: Bonus Paket -->
        <div class="bg-white rounded-xl p-3.5 border border-amber-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-gift mr-1 text-amber-600"></i> Bonus Paket
                </div>
                <div class="text-base font-heading font-extrabold text-amber-800 mt-0.5">
                    Rp {{ number_format($summary['total_bonus_cost'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    <strong>{{ number_format($summary['total_bonus_pieces'] ?? 0) }}</strong> Pcs ({{ $summary['total_unique_bonus'] ?? 0 }} Macam)
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-award"></i>
            </div>
        </div>

        <!-- Card 3: Kemasan Parsel -->
        <div class="bg-white rounded-xl p-3.5 border border-purple-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-purple-800 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-box-archive mr-1 text-purple-600"></i> Kemasan / Wadah
                </div>
                <div class="text-base font-heading font-extrabold text-purple-900 mt-0.5">
                    Rp {{ number_format($summary['total_packaging_cost'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    <strong>{{ number_format($summary['total_packaging_units'] ?? 0) }}</strong> Unit ({{ $summary['total_packaging_types'] ?? 0 }} Model)
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-suitcase"></i>
            </div>
        </div>

        <!-- Card 4: Uang Tunai Disiapkan -->
        <div class="bg-white rounded-xl p-3.5 border border-emerald-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-money-bill-wave mr-1 text-emerald-600"></i> Uang Tunai Harus Disiapkan
                </div>
                <div class="text-base font-heading font-extrabold text-emerald-700 mt-0.5">
                    Rp {{ number_format($summary['total_cash_amount'], 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    <strong>{{ $summary['total_cash_recipients'] }}</strong> Konsumen ({{ $summary['total_cash_agents'] }} Agen)
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <!-- Card 5: Grand Total Dana Modal Owner -->
        <div class="bg-white rounded-xl p-3.5 border border-pink-300 shadow-xs flex items-center justify-between bg-gradient-to-br from-white to-pink-50/60">
            <div>
                <div class="text-[10px] font-bold text-pink-900 uppercase tracking-wider">
                    Total Dana Modal
                </div>
                <div class="text-base font-heading font-black text-pink-800 mt-0.5">
                    Rp {{ number_format($summary['grand_total_fund'], 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    Belanja + Uang Tunai
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-pink-200/70 text-pink-800 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>
    </div>

    <!-- TAB FILTER SELECTOR -->
    <div class="flex items-center space-x-2 border-b border-pink-200 pb-3 text-xs font-bold overflow-x-auto">
        <button type="button" @click="activeTab = 'packages'" :class="activeTab === 'packages' ? 'bg-pink-700 text-white shadow-xs' : 'bg-white text-pink-900 hover:bg-pink-50 border border-pink-200'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
            <i class="fa-solid fa-boxes-packing mr-1.5"></i> 1. Rekapan Paket yang Harus Disiapkan ({{ count($packagesToPrepare) }})
        </button>
        <button type="button" @click="activeTab = 'goods'" :class="activeTab === 'goods' ? 'bg-pink-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-pink-50 border border-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
            <i class="fa-solid fa-box-open mr-1.5"></i> 2. Belanja Barang Pokok ({{ count($items) }})
        </button>
        <button type="button" @click="activeTab = 'bonus'" :class="activeTab === 'bonus' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-amber-800 hover:bg-amber-50 border border-amber-200'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
            <i class="fa-solid fa-award mr-1.5"></i> 3. Belanja Bonus Paket ({{ count($bonusItems) }})
        </button>
        <button type="button" @click="activeTab = 'packaging'" :class="activeTab === 'packaging' ? 'bg-purple-700 text-white shadow-xs' : 'bg-white text-purple-800 hover:bg-purple-50 border border-purple-200'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
            <i class="fa-solid fa-box-archive mr-1.5"></i> 4. Belanja Kemasan Parsel ({{ count($packagingItems) }})
        </button>
        <button type="button" @click="activeTab = 'cash'" :class="activeTab === 'cash' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-emerald-800 hover:bg-emerald-50 border border-emerald-200'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
            <i class="fa-solid fa-money-bill-wave mr-1.5"></i> 5. Penyiapan Uang Tunai ({{ count($cashItems) }})
        </button>
        @if(count($excludedIncompleteCustomers) > 0)
            <button type="button" @click="activeTab = 'excluded'" :class="activeTab === 'excluded' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-amber-900 hover:bg-amber-50 border border-amber-300'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
                <i class="fa-solid fa-user-xmark mr-1.5"></i> 6. Belum Ditetapkan Owner ({{ count($excludedIncompleteCustomers) }})
            </button>
        @endif
        <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center shrink-0">
            <i class="fa-solid fa-layer-group mr-1.5"></i> Tampilkan Semua
        </button>
    </div>

    <!-- ================= TABEL 1: REKAPAN JUMLAH PAKET YANG HARUS DISIAPKAN (PERMINTAAN KONSUMEN) ================= -->
    <div x-show="activeTab === 'all' || activeTab === 'packages'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-pink-600 inline-block"></span>
                    <h3 class="font-heading font-extrabold text-slate-900 text-base">Rekapan Jumlah Masing-Masing Paket yang Harus Disiapkan</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Jumlah masing-masing paket yang harus disiapkan owner untuk memenuhi permintaan seluruh konsumen aktif, rincian komposisi barang & uang, serta sebaran agennya.
                </p>
            </div>

            <!-- SEARCH & FILTER FORM -->
            <form action="{{ route('inventory.planner') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari paket / konsumen / agen..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-pink-500">
                <select name="package_type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-medium">
                    <option value="">Semua Jenis Paket</option>
                    <option value="parsel" {{ request('package_type') === 'parsel' ? 'selected' : '' }}>Paket Parsel (Barang)</option>
                    <option value="tabungan" {{ request('package_type') === 'tabungan' ? 'selected' : '' }}>Tabungan SPL (Uang + Bonus)</option>
                    <option value="ecer" {{ request('package_type') === 'ecer' ? 'selected' : '' }}>Paket Eceran (Bebas)</option>
                </select>
                <select name="only_with_orders" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-medium">
                    <option value="1" {{ request('only_with_orders', '1') === '1' ? 'selected' : '' }}>Hanya Paket dengan Pesanan Aktif</option>
                    <option value="0" {{ request('only_with_orders') === '0' ? 'selected' : '' }}>Semua Master Paket (Termasuk 0 Pesanan)</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-pink-700 hover:bg-pink-800 text-white rounded-lg font-bold">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                @if(request()->hasAny(['search', 'package_type', 'only_with_orders', 'category']))
                    <a href="{{ route('inventory.planner') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 font-medium">
                        <i class="fa-solid fa-xmark mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-pink-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-pink-100 text-slate-700 font-extrabold bg-pink-50/50">
                            <th class="py-3 px-3 text-center w-12">No</th>
                            <th class="py-3 px-4 min-w-[200px]">Kode & Nama Paket</th>
                            <th class="py-3 px-3">Jenis & Skema Tagihan</th>
                            <th class="py-3 px-4 text-center min-w-[170px] bg-pink-100/50 text-pink-950 font-black">
                                Jumlah Paket yang Harus Disiapkan
                            </th>
                            <th class="py-3 px-4 min-w-[280px]">Komposisi Barang & Uang Total (Kebutuhan Pemenuhan)</th>
                            <th class="py-3 px-4 min-w-[170px]">Sebaran Agen Pemesan</th>
                            <th class="py-3 px-3 text-center">Aksi / Konsumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($packagesToPrepare as $idx => $p)
                            <tr class="hover:bg-pink-50/25 transition {{ $p['total_orders'] > 0 ? '' : 'opacity-60 bg-slate-50/30' }}">
                                <td class="py-3.5 px-3 font-mono font-medium text-slate-400 text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="font-bold text-slate-900 text-sm">
                                            {{ $p['package_name'] }}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <span class="font-mono text-[10px] font-bold text-pink-700 bg-pink-50 border border-pink-200 px-1.5 py-0.5 rounded">
                                            {{ $p['package_code'] }}
                                        </span>
                                        @if($p['description'])
                                            <span class="text-[10px] text-slate-400 truncate max-w-xs" title="{{ $p['description'] }}">
                                                {{ $p['description'] }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3.5 px-3">
                                    <div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $p['package_type'] === 'parsel' ? 'bg-pink-100 text-pink-800 border border-pink-200' : ($p['package_type'] === 'tabungan' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200') }}">
                                            {{ $p['package_type_label'] }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-semibold mt-1">
                                        <i class="fa-solid fa-clock-rotate-left text-slate-400 mr-0.5"></i>
                                        {{ $p['installment_label'] }} &bull; {{ $p['installment_count'] }}x Setor
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Rp {{ number_format($p['installment_amount'], 0, ',', '.') }} / kali
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-center bg-pink-50/30">
                                    @if($p['total_orders'] > 0)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-sm font-black bg-pink-700 text-white shadow-xs">
                                                <i class="fa-solid fa-box mr-1.5 text-xs"></i>
                                                {{ $p['total_orders'] }} Paket
                                            </span>
                                            <div class="flex items-center space-x-2 mt-1.5 text-[10px] font-bold">
                                                <span class="text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                                    {{ $p['pending_orders'] }} Menunggu
                                                </span>
                                                <span class="text-emerald-800 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">
                                                    {{ $p['fulfilled_orders'] }} Diserahkan
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-500">
                                            0 Paket (Belum Ada)
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="space-y-1.5">
                                        {{-- 1. Uang Tunai yang harus disiapkan --}}
                                        @if($p['total_cash_needed'] > 0)
                                            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs">
                                                <span class="font-bold text-emerald-900 flex items-center">
                                                    <i class="fa-solid fa-money-bill-wave text-emerald-600 mr-1.5"></i> Uang Tunai Disiapkan:
                                                </span>
                                                <span class="font-extrabold text-emerald-800">
                                                    Rp {{ number_format($p['total_cash_needed'], 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endif

                                        {{-- 2. Barang Pokok yang harus dibelanjakan untuk paket ini --}}
                                        @if(!empty($p['total_goods_needed']))
                                            <div>
                                                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center">
                                                    <i class="fa-solid fa-cart-shopping mr-1 text-pink-600"></i> Barang Pokok Disiapkan ({{ count($p['total_goods_needed']) }} Macam):
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px]">
                                                    @foreach($p['total_goods_needed'] as $g)
                                                        <div class="px-2 py-0.5 rounded bg-slate-50 border border-slate-200 text-slate-800 flex items-center justify-between">
                                                            <span class="truncate mr-1 text-slate-700">{{ $g['item_name'] }}</span>
                                                            <span class="font-extrabold text-pink-900 shrink-0">{{ number_format($g['total_qty']) }} {{ $g['unit'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 3. Hadiah Bonus yang harus dibelanjakan untuk paket ini --}}
                                        @if(!empty($p['bonuses_needed']))
                                            <div>
                                                <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1 flex items-center">
                                                    <i class="fa-solid fa-gift mr-1 text-amber-600"></i> Hadiah Bonus Disiapkan:
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px]">
                                                    @foreach($p['bonuses_needed'] as $b)
                                                        <div class="px-2 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between">
                                                            <span class="truncate mr-1">{{ $b['item_name'] }}</span>
                                                            <span class="font-extrabold shrink-0">{{ number_format($b['total_qty']) }} {{ $b['unit'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 4. Kemasan / Wadah yang dipilih konsumen paket ini --}}
                                        @if(!empty($p['packagings_needed']))
                                            <div>
                                                <div class="text-[10px] font-bold text-purple-800 uppercase tracking-wider mb-0.5 flex items-center">
                                                    <i class="fa-solid fa-suitcase mr-1 text-purple-600"></i> Kemasan Wadah Terpilih:
                                                </div>
                                                <div class="flex flex-wrap gap-1 text-[10px]">
                                                    @foreach($p['packagings_needed'] as $kms)
                                                        <span class="px-2 py-0.5 rounded bg-purple-50 border border-purple-200 font-bold text-purple-900">
                                                            {{ $kms['name'] }}: {{ $kms['qty'] }} Unit
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($p['total_cash_needed'] <= 0 && empty($p['total_goods_needed']) && empty($p['bonuses_needed']))
                                            <span class="text-[11px] text-slate-400 italic">Belum ada barang/uang yang harus disiapkan.</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if(!empty($p['agents']))
                                        <div class="space-y-1">
                                            @foreach($p['agents'] as $ag)
                                                <div class="px-2 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] flex items-center justify-between">
                                                    <span class="font-bold text-slate-800 flex items-center truncate mr-1">
                                                        <i class="fa-solid fa-user-tie text-pink-600 mr-1 text-[10px]"></i> {{ $ag['name'] }}
                                                    </span>
                                                    <span class="font-black text-pink-800 shrink-0">{{ $ag['count'] }} pkt</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    @if($p['total_orders'] > 0)
                                        <button type="button" 
                                            @click="openedPackage = (openedPackage === {{ $p['package_id'] }} ? null : {{ $p['package_id'] }})"
                                            class="px-2.5 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold rounded-lg text-[10px] border border-pink-200 transition inline-flex items-center space-x-1">
                                            <i class="fa-solid fa-users"></i>
                                            <span>{{ count($p['customers']) }} Konsumen</span>
                                            <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-200" :class="{ 'rotate-180': openedPackage === {{ $p['package_id'] }} }"></i>
                                        </button>
                                    @else
                                        <span class="text-[10px] text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- DRAWER RINCIAN KONSUMEN PER PAKET INI --}}
                            @if($p['total_orders'] > 0)
                                <tr x-show="openedPackage === {{ $p['package_id'] }}" x-transition style="display: none;" class="bg-pink-50/40">
                                    <td colspan="7" class="p-4 border-t border-b border-pink-200">
                                        <div class="bg-white rounded-xl border border-pink-200 p-4 shadow-xs">
                                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-pink-100">
                                                <h4 class="font-heading font-extrabold text-slate-900 text-xs flex items-center">
                                                    <i class="fa-solid fa-address-book text-pink-600 mr-1.5"></i>
                                                    Daftar Seluruh Konsumen Pemesan Paket: <span class="text-pink-800 ml-1">{{ $p['package_name'] }}</span>
                                                    <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-800">
                                                        Total {{ count($p['customers']) }} Konsumen
                                                    </span>
                                                </h4>
                                                <button type="button" @click="openedPackage = null" class="text-[10px] font-bold text-slate-400 hover:text-slate-700">
                                                    ✕ Tutup Rincian
                                                </button>
                                            </div>

                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-[11px]">
                                                    <thead>
                                                        <tr class="text-slate-500 font-bold border-b border-slate-100">
                                                            <th class="py-1.5 px-2">No</th>
                                                            <th class="py-1.5 px-2">Nama Konsumen</th>
                                                            <th class="py-1.5 px-2">Agen</th>
                                                            <th class="py-1.5 px-2">Wadah / Kemasan</th>
                                                            <th class="py-1.5 px-2 text-right">Target Paket</th>
                                                            <th class="py-1.5 px-2 text-center">Progress Setoran</th>
                                                            <th class="py-1.5 px-2 text-center">Status Pemenuhan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100">
                                                        @foreach($p['customers'] as $cIdx => $c)
                                                            <tr class="hover:bg-slate-50">
                                                                <td class="py-1.5 px-2 font-mono text-slate-400">{{ $cIdx + 1 }}</td>
                                                                <td class="py-1.5 px-2 font-bold text-slate-900">
                                                                    {{ $c['customer_name'] }}
                                                                    <span class="text-[9px] font-normal text-slate-400 font-mono ml-1">({{ $c['customer_code'] }})</span>
                                                                    <div class="mt-0.5">
                                                                        <span class="px-1.5 py-0.2 rounded text-[8px] font-bold {{ !empty($c['has_owner_decision']) ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                                                            {{ $c['eligibility_label'] ?? 'Lolos Titik Aman' }}
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                                <td class="py-1.5 px-2 font-semibold text-slate-700">
                                                                    {{ $c['agent_name'] }}
                                                                </td>
                                                                <td class="py-1.5 px-2 text-slate-600">
                                                                    {{ $c['packaging_name'] }}
                                                                </td>
                                                                <td class="py-1.5 px-2 text-right font-mono font-bold text-slate-800">
                                                                    Rp {{ number_format($c['total_target'], 0, ',', '.') }}
                                                                </td>
                                                                <td class="py-1.5 px-2 text-center">
                                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $c['progress_percent'] >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                                                        {{ $c['progress_percent'] }}% (Rp {{ number_format($c['total_paid'], 0, ',', '.') }})
                                                                    </span>
                                                                </td>
                                                                <td class="py-1.5 px-2 text-center">
                                                                    @if($c['fulfillment_status'] === 'fulfilled')
                                                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">
                                                                            <i class="fa-solid fa-check mr-0.5"></i> Sudah Diserahkan
                                                                        </span>
                                                                    @else
                                                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">
                                                                            <i class="fa-solid fa-clock mr-0.5"></i> Harus Disiapkan
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-2 block"></i>
                                    Tidak ada data paket yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABEL 2: BELANJA GROSIR BARANG POKOK ================= -->
    <div x-show="activeTab === 'all' || activeTab === 'goods'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-pink-600 inline-block"></span>
                    <h3 class="font-heading font-bold text-slate-900 text-base">Tabel 1: Rekap Belanja Barang Pokok (Parsel & Eceran)</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hasil konversi total pesanan satuan dasar (pcs/botol/kaleng) ke satuan kulakan yang lebih besar (Dus/Karton/Bal).
                </p>
            </div>

            <!-- SEARCH & CATEGORY FILTER -->
            <form action="{{ route('inventory.planner') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang / kode..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-pink-500">
                <select name="category" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-medium">
                    <option value="">Semua Kategori</option>
                    @foreach($summary['categories'] as $cat)
                        <option value="{{ $cat['category'] }}" {{ request('category') === $cat['category'] ? 'selected' : '' }}>
                            {{ $cat['category'] }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-pink-700 hover:bg-pink-800 text-white rounded-lg font-bold">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('inventory.planner') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800">
                        <i class="fa-solid fa-xmark mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-600 font-bold bg-slate-50/50">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Barang Pokok</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-center">Isi per Dus</th>
                            <th class="py-3 px-4 text-center">Total Kebutuhan (Pcs)</th>
                            <th class="py-3 px-4">Sumber Pesanan</th>
                            <th class="py-3 px-4 font-bold text-pink-900">Kebutuhan Grosir</th>
                            <th class="py-3 px-4 text-right">Estimasi Modal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $row)
                            <tr class="hover:bg-pink-50/20 transition">
                                <td class="py-3 px-4 font-mono font-medium text-slate-400">
                                    {{ $row['item_code'] }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $row['item_name'] }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $row['category'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono">
                                    {{ (int)$row['conversion_rate'] }} {{ $row['base_unit'] }}/{{ $row['wholesale_unit'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-900">
                                    {{ number_format($row['total_base_qty']) }} {{ $row['base_unit'] }}
                                </td>
                                <td class="py-3 px-4 text-[10px] text-slate-500 space-x-1">
                                    @if($row['from_parsel'] > 0)
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-pink-50 text-pink-700 font-medium">Parsel: {{ $row['from_parsel'] }}</span>
                                    @endif
                                    @if($row['from_ecer'] > 0)
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-medium">Ecer: {{ $row['from_ecer'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-pink-900 text-xs">
                                        {{ $row['wholesale_text'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        (atau genap <strong>{{ $row['wholesale_ceil'] }} {{ $row['wholesale_unit'] }}</strong>)
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    Rp {{ number_format($row['estimated_cost'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400">
                                    Belum ada pesanan barang pokok yang memerlukan kalkulasi kulakan grosir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABEL 2: BELANJA BARANG BONUS PAKET (TABEL TERPISAH SESUAI ARAHAN OWNER) ================= -->
    <div x-show="activeTab === 'all' || activeTab === 'bonus'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border border-amber-200 shadow-xs p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                    <h3 class="font-heading font-bold text-slate-900 text-base">Tabel 2: Rekap Belanja Bonus Paket (Hadiah Gratis Tabungan & Parsel)</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Daftar seluruh barang bonus/hadiah yang harus dibeli owner. Dipisahkan agar tidak tertukar dengan barang pokok paket.
                </p>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-xs font-bold text-amber-900 flex items-center shrink-0">
                <i class="fa-solid fa-gift mr-1.5 text-amber-600"></i> Total Modal Bonus: Rp {{ number_format($summary['total_bonus_cost'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-amber-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-amber-100 text-slate-600 font-bold bg-amber-50/40">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Barang Bonus</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-center">Isi per Dus</th>
                            <th class="py-3 px-4 text-center">Jumlah Bonus (Pcs)</th>
                            <th class="py-3 px-4">Paket yang Memberikan Bonus</th>
                            <th class="py-3 px-4 font-bold text-amber-900">Kebutuhan Grosir</th>
                            <th class="py-3 px-4 text-right">Estimasi Modal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bonusItems as $b)
                            <tr class="hover:bg-amber-50/20 transition">
                                <td class="py-3 px-4 font-mono font-medium text-slate-400">
                                    {{ $b['item_code'] }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 flex items-center">
                                        <i class="fa-solid fa-gift text-amber-500 mr-1.5 text-xs"></i>
                                        <span>{{ $b['item_name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        {{ $b['category'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono">
                                    {{ (int)$b['conversion_rate'] }} {{ $b['base_unit'] }}/{{ $b['wholesale_unit'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-amber-900">
                                    {{ number_format($b['total_base_qty']) }} {{ $b['base_unit'] }}
                                </td>
                                <td class="py-3 px-4 text-[10px] text-slate-600">
                                    @foreach($b['packages_list'] as $pName)
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-medium mr-1 mb-0.5">{{ $pName }}</span>
                                    @endforeach
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-amber-900 text-xs">
                                        {{ $b['wholesale_text'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        (atau genap <strong>{{ $b['wholesale_ceil'] }} {{ $b['wholesale_unit'] }}</strong>)
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    Rp {{ number_format($b['estimated_cost'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-gift text-2xl text-slate-300 mb-2 block"></i>
                                    Belum ada pesanan aktif dengan item bonus/hadiah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABEL 3: BELANJA KEMASAN PARSEL / WADAH (TABEL TERPISAH DENGAN FOTO) ================= -->
    <div x-show="activeTab === 'all' || activeTab === 'packaging'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border border-purple-200 shadow-xs p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-purple-600 inline-block"></span>
                    <h3 class="font-heading font-bold text-slate-900 text-base">Tabel 3: Rekap Belanja Kemasan Parsel (Koper, Keranjang, Box Hampers)</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data kemasan yang wajib dibeli owner sesuai pilihan masing-masing konsumen. Lengkap dengan foto tampak wadah dan breakdown penerimanya.
                </p>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-purple-50 border border-purple-200 text-xs font-bold text-purple-900 flex items-center shrink-0">
                <i class="fa-solid fa-box-archive mr-1.5 text-purple-600"></i> Total Modal Kemasan: Rp {{ number_format($summary['total_packaging_cost'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-purple-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-purple-100 text-slate-600 font-bold bg-purple-50/40">
                            <th class="py-3 px-4 text-center">Foto</th>
                            <th class="py-3 px-4">Kode & Nama Kemasan</th>
                            <th class="py-3 px-4 text-right">Harga Modal Kulak</th>
                            <th class="py-3 px-4 text-center font-bold text-purple-900">Jumlah Unit Dibeli</th>
                            <th class="py-3 px-4 text-right font-bold text-purple-900">Total Biaya Kulak</th>
                            <th class="py-3 px-4">Konsumen & Agen Pemesan</th>
                            <th class="py-3 px-4 text-center">Ceklis Beli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($packagingItems as $pkg)
                            <tr class="hover:bg-purple-50/20 transition">
                                <!-- Foto Kemasan Thumbnail -->
                                <td class="py-3 px-4 text-center">
                                    <div class="w-14 h-14 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden mx-auto flex items-center justify-center cursor-pointer relative group shadow-2xs"
                                         @click="previewPackagingPhoto = '{{ $pkg['image_url'] ?: '' }}'; previewPackagingTitle = '{{ addslashes($pkg['name']) }} ({{ $pkg['code'] }})'">
                                        @if(!empty($pkg['image_path']) && file_exists(public_path($pkg['image_path'])))
                                            <img src="{{ asset($pkg['image_path']) }}" alt="{{ $pkg['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition text-xs">
                                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                                            </div>
                                        @else
                                            <i class="fa-solid fa-box-archive text-slate-300 text-xl"></i>
                                        @endif
                                    </div>
                                    <button type="button" @click="previewPackagingPhoto = '{{ $pkg['image_url'] ?: '' }}'; previewPackagingTitle = '{{ addslashes($pkg['name']) }} ({{ $pkg['code'] }})'" class="text-[9px] text-purple-700 font-bold hover:underline mt-1 block mx-auto">
                                        Lihat Foto
                                    </button>
                                </td>

                                <!-- Nama & Kode Kemasan -->
                                <td class="py-3 px-4">
                                    <span class="font-mono text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.2 rounded">{{ $pkg['code'] }}</span>
                                    <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $pkg['name'] }}</div>
                                    @if(!empty($pkg['description']))
                                        <div class="text-[10px] text-slate-400 mt-0.5 max-w-xs">{{ $pkg['description'] }}</div>
                                    @endif
                                </td>

                                <!-- Harga Kulak / Modal Beli Owner -->
                                <td class="py-3 px-4 text-right font-mono text-slate-700">
                                    Rp {{ number_format($pkg['cost_price'], 0, ',', '.') }}
                                </td>

                                <!-- Jumlah Unit Dibeli -->
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-purple-100 text-purple-900 border border-purple-200">
                                        {{ $pkg['total_qty'] }} Unit
                                    </span>
                                </td>

                                <!-- Total Biaya Kulak -->
                                <td class="py-3 px-4 text-right font-black text-purple-900 text-sm font-mono">
                                    Rp {{ number_format($pkg['total_cost'], 0, ',', '.') }}
                                </td>

                                <!-- Daftar Konsumen & Agen -->
                                <td class="py-3 px-4">
                                    <div class="space-y-1 max-h-24 overflow-y-auto pr-1">
                                        @foreach($pkg['recipients'] as $rcp)
                                            <div class="text-[10px] text-slate-600 bg-slate-50 p-1.5 rounded border border-slate-100 flex items-center justify-between">
                                                <div>
                                                    <span class="font-bold text-slate-800">{{ $rcp['customer_name'] }}</span>
                                                    <span class="text-slate-400">({{ $rcp['customer_code'] }})</span>
                                                </div>
                                                <span class="text-purple-700 font-semibold">{{ $rcp['agent_name'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Checkbox Siap Dibeli -->
                                <td class="py-3 px-4 text-center">
                                    <label class="inline-flex items-center space-x-1.5 cursor-pointer">
                                        <input type="checkbox" class="w-4 h-4 rounded text-purple-600 border-slate-300 focus:ring-purple-500">
                                        <span class="text-[10px] font-semibold text-slate-600">Beli</span>
                                    </label>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-box-archive text-2xl text-slate-300 mb-2 block"></i>
                                    Belum ada konsumen yang memesan paket dengan kemasan khusus (koper, keranjang, dll).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABEL 4: PERSIAPAN UANG TUNAI ================= -->
    <div x-show="activeTab === 'all' || activeTab === 'cash'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <h3 class="font-heading font-bold text-slate-900 text-base">Tabel 4: Daftar Uang Tunai yang Harus Disiapkan Owner</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Informasi pasti jumlah uang tunai yang harus disiapkan Owner, <strong>lengkap untuk konsumen siapa dan melalui agen siapa</strong> (Pencairan Tabungan Lebaran & Item Uang).
                </p>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center shrink-0">
                <i class="fa-solid fa-wallet mr-1.5 text-emerald-600"></i> Total Uang: Rp {{ number_format($summary['total_cash_amount'], 0, ',', '.') }}
            </div>
        </div>

        @if(count($cashByAgent) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($cashByAgent as $ag)
                    <div class="bg-white rounded-xl p-3.5 border border-emerald-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Rekap Uang Agen:</div>
                            <div class="font-bold text-slate-900 text-sm flex items-center mt-0.5">
                                <i class="fa-solid fa-user-tie text-emerald-600 mr-1.5 text-xs"></i> {{ $ag['agent_name'] }}
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                {{ $ag['recipient_count'] }} Konsumen Binaan • Telp: {{ $ag['agent_phone'] ?: '-' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-slate-400">Total Uang:</div>
                            <div class="text-base font-extrabold text-emerald-700">
                                Rp {{ number_format($ag['total_cash'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-emerald-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-emerald-100 text-slate-600 font-bold bg-emerald-50/50">
                            <th class="py-3 px-4">No</th>
                            <th class="py-3 px-4">Konsumen Penerima</th>
                            <th class="py-3 px-4">Agen Penanggung Jawab</th>
                            <th class="py-3 px-4">Paket / Rincian Uang</th>
                            <th class="py-3 px-4 text-right font-extrabold text-emerald-900">Uang Disiapkan (Rp)</th>
                            <th class="py-3 px-4 text-center">Status Setoran</th>
                            <th class="py-3 px-4 text-center">Ceklis Amplop</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($cashItems as $idx => $row)
                            <tr class="hover:bg-emerald-50/30 transition">
                                <td class="py-3 px-4 font-mono text-slate-400 text-center">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 text-sm flex items-center">
                                        <i class="fa-solid fa-user text-slate-400 mr-1.5 text-xs"></i> {{ $row['customer_name'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Kode: <span class="font-mono font-semibold">{{ $row['customer_code'] }}</span> • HP: {{ $row['customer_phone'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Alamat: {{ $row['customer_address'] }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-800">
                                        <i class="fa-solid fa-user-tie text-emerald-600 mr-1 text-[10px]"></i> {{ $row['agent_name'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $row['cash_description'] }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Paket: <strong>{{ $row['package_name'] }}</strong> ({{ $row['period_text'] }})
                                    </div>
                                    @if($row['is_bonus_forfeited'])
                                        <div class="text-[10px] text-red-600 font-semibold mt-0.5">
                                            <i class="fa-solid fa-triangle-exclamation mr-0.5"></i> Bonus barang hangus (Uang tetap dicairkan utuh)
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="text-base font-extrabold text-emerald-700">
                                        Rp {{ number_format($row['cash_amount'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        Uang Tunai Utuh
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="font-bold text-slate-800 text-[11px]">
                                        Rp {{ number_format($row['total_paid'], 0, ',', '.') }}
                                    </div>
                                    <div class="w-20 bg-slate-100 rounded-full h-1.5 mx-auto mt-1 overflow-hidden">
                                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $row['progress_percent'] }}%"></div>
                                    </div>
                                    <span class="text-[9px] text-slate-500">{{ $row['progress_percent'] }}% Terkumpul</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <label class="inline-flex items-center space-x-1.5 cursor-pointer">
                                        <input type="checkbox" class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                        <span class="text-[10px] font-semibold text-slate-600">Siap</span>
                                    </label>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-money-bill-wave text-2xl text-slate-300 mb-2 block"></i>
                                    Belum ada pesanan aktif yang memerlukan penyiapan uang tunai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABEL 6: KONSUMEN TIDAK SELESAI / BELUM TITIK AMAN (BELUM DITETAPKAN OWNER) ================= -->
    <div x-show="activeTab === 'excluded'" x-transition class="space-y-4">
        <div class="bg-white rounded-2xl border-2 border-amber-300 shadow-xs p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-amber-50/60 to-white">
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                    <h3 class="font-heading font-extrabold text-amber-950 text-base">
                        Daftar Konsumen Belum Masuk Belanja (Menunggu Ketetapan Barang Owner)
                    </h3>
                </div>
                <p class="text-xs text-amber-900/90 leading-relaxed max-w-3xl">
                    Konsumen berikut <strong>tidak dimasukkan ke daftar belanja</strong> karena belum mencapai titik aman atau berhenti di tengah jalan dan Owner belum menetapkan alokasi barang penggantinya. Silakan klik tombol <strong>"Tetapkan Barang"</strong> untuk memutuskan apa yang akan diterima konsumen agar otomatis masuk ke daftar belanja kulakan.
                </p>
            </div>
            <a href="{{ route('owner.book-closing.index') }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 shrink-0">
                <i class="fa-solid fa-sliders"></i>
                <span>Buka Menu Tutup Buku</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-amber-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-amber-200 text-amber-950 font-extrabold bg-amber-50/70">
                            <th class="py-3 px-3 text-center w-12">No</th>
                            <th class="py-3 px-4">Konsumen</th>
                            <th class="py-3 px-4">Agen Penanggung Jawab</th>
                            <th class="py-3 px-4">Paket Pilihan</th>
                            <th class="py-3 px-4 text-right">Setoran Masuk</th>
                            <th class="py-3 px-4">Kekurangan / Status</th>
                            <th class="py-3 px-4 text-center">Tindakan Owner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($excludedIncompleteCustomers as $eIdx => $ex)
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="py-3 px-3 text-center font-mono text-slate-400">
                                    {{ $eIdx + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">
                                        {{ $ex['customer_name'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $ex['customer_code'] }} &bull; {{ $ex['customer_phone'] }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 flex items-center space-x-1">
                                        <i class="fa-solid fa-user-tie text-slate-400 mr-1 text-[10px]"></i>
                                        <span>{{ $ex['agent_name'] }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $ex['agent_phone'] }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $ex['package_name'] }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">
                                        {{ $ex['package_type'] }} &bull; Target: Rp {{ number_format($ex['total_target'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-mono font-bold text-emerald-800 text-xs">
                                        Rp {{ number_format($ex['total_paid'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        {{ $ex['progress_percent'] }}% Lunas
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $ex['stop_status'] === 'pending' ? 'bg-orange-100 text-orange-900 border border-orange-200' : 'bg-amber-100 text-amber-900 border border-amber-200' }}">
                                        {{ $ex['status_label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($ex['stop_status'] === 'pending')
                                        <a href="{{ route('owner.customer-stops.review', $ex['customer_package_id']) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-[11px] shadow-xs transition">
                                            <i class="fa-solid fa-boxes-packing mr-1"></i> Tentukan Barang Stop
                                        </a>
                                    @else
                                        <a href="{{ route('owner.book-closing.decision', $ex['customer_package_id']) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-[11px] shadow-xs transition">
                                            <i class="fa-solid fa-sliders mr-1"></i> Tetapkan Barang Tutup Buku
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-circle-check text-2xl text-emerald-400 mb-2 block"></i>
                                    Hebat! Seluruh konsumen telah mencapai titik aman atau telah ditetapkan alokasi barangnya oleh Owner.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL ZOOM FOTO KEMASAN -->
    <div x-show="previewPackagingPhoto" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4" style="display: none;" @click="previewPackagingPhoto = null" @keydown.escape.window="previewPackagingPhoto = null">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-pink-100" @click.stop>
            <div class="p-4 bg-gradient-to-r from-pink-600 to-rose-600 text-white flex items-center justify-between">
                <span class="font-bold text-sm" x-text="previewPackagingTitle"></span>
                <button type="button" @click="previewPackagingPhoto = null" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">✕</button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center min-h-[300px]">
                <img :src="previewPackagingPhoto" alt="Foto Kemasan" class="max-h-[450px] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="previewPackagingPhoto = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold text-xs">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection
