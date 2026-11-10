@extends('layouts.app')

@section('title', 'Laporan Keseluruhan Agen & Paket')
@section('page_title', 'Laporan Keseluruhan Agen & Paket Konsumen')

@section('header_badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        {{ $totalCustomersCount }} Konsumen &bull; {{ $totalPackagesCount }} Paket Dipesan
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'all', 
    activeAgentFilter: '{{ request('agent_id', '') }}',
    searchHarian: '',
    searchMingguan: '',
    searchEcer: '',
    searchTabungan: '',
    searchDetail: '',
    matchesAgent(name, phone, address, term) {
        if (!term || term.trim() === '') return true;
        const q = term.toLowerCase().trim();
        return (name && name.toLowerCase().includes(q)) ||
               (phone && phone.toLowerCase().includes(q)) ||
               (address && address.toLowerCase().includes(q));
    }
}">

    <!-- TOP BREADCRUMB & ACTION BUTTONS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
        <div class="flex items-center space-x-2 text-slate-500">
            <a href="{{ route('owner.agents.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700 flex items-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Menu Agen
            </a>
            <span>&bull;</span>
            <span class="text-slate-400">Rekapitulasi Distribusi Paket</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- AGENT FILTER DROPDOWN -->
            <form action="{{ route('owner.agents.report') }}" method="GET" class="flex items-center space-x-2">
                <select name="agent_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200 rounded-xl font-medium text-slate-700 text-xs shadow-xs focus:border-emerald-500">
                    <option value="">-- Tampilkan Semua Agen ({{ $allAgentsList->count() }}) --</option>
                    @foreach($allAgentsList as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                            Agen: {{ $ag->name }}
                        </option>
                    @endforeach
                </select>
                @if(request('agent_id'))
                    <a href="{{ route('owner.agents.report') }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs" title="Reset Filter">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </form>

            <!-- EXPORT EXCEL BUTTON -->
            <a href="{{ route('owner.agents.report.export', ['agent_id' => request('agent_id')]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-file-excel mr-1.5 text-emerald-100"></i> Export Excel (.xlsx) Siap Cetak
            </a>

            <!-- PRINT BUTTON -->
            <a href="{{ route('owner.agents.report.print', ['agent_id' => request('agent_id')]) }}" target="_blank" class="px-4 py-2 bg-pink-700 hover:bg-pink-800 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Laporan (Print / PDF)
            </a>
        </div>
    </div>

    <!-- 4 KPI SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. TOTAL AGEN -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Agen Aktif</div>
                <div class="text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $agents->count() }} Agen</div>
                <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Penanggung Jawab Wilayah</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-tie"></i>
            </div>
        </div>

        <!-- 2. TOTAL KONSUMEN -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Konsumen Binaan</div>
                <div class="text-2xl font-heading font-extrabold text-blue-800 mt-1">{{ $totalCustomersCount }} Konsumen</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                    Harian: <strong>{{ $totalHarianCustomersCount }}</strong> &bull; Mingguan: <strong>{{ $totalMingguanCustomersCount }}</strong> &bull; Eceran: <strong>{{ $totalEcerCustomersCount }}</strong>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- 3. TOTAL PAKET DIPESAN -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Paket Dipesan</div>
                <div class="text-2xl font-heading font-extrabold text-amber-700 mt-1">{{ $totalPackagesCount }} Paket</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                    Harian: <strong>{{ $totalHarianOrdersCount }}</strong> &bull; Mingguan: <strong>{{ $totalMingguanOrdersCount }}</strong> &bull; Eceran: <strong>{{ $totalEcerOrdersCount }}</strong>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-gift"></i>
            </div>
        </div>

        <!-- 4. TOTAL TARGET AKUMULASI -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Nilai Pesanan</div>
                <div class="text-xl font-heading font-extrabold text-emerald-800 mt-1">Rp {{ number_format($totalTargetNominalSum, 0, ',', '.') }}</div>
                <div class="text-[10px] text-emerald-700 font-bold mt-0.5">
                    Kas Masuk: Rp {{ number_format($totalPaidSum, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
    </div>

    <!-- TAB SWITCHER NAVIGASI REKAPAN -->
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100 rounded-2xl border border-slate-200 text-xs font-bold">
        <button 
            type="button" 
            @click="activeTab = 'all'" 
            class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5"
            :class="activeTab === 'all' ? 'bg-white text-slate-900 shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
        >
            <i class="fa-solid fa-layer-group"></i>
            <span>Semua Rekapitulasi</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'harian'" 
            class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5"
            :class="activeTab === 'harian' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
        >
            <i class="fa-solid fa-calendar-day"></i>
            <span>I. Rekapan Parsel Harian</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'harian' ? 'bg-emerald-800 text-emerald-100' : 'bg-slate-200 text-slate-700'">
                {{ $totalHarianOrdersCount }} Paket
            </span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'mingguan'" 
            class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5"
            :class="activeTab === 'mingguan' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
        >
            <i class="fa-solid fa-calendar-week"></i>
            <span>II. Rekapan Paket Mingguan Lainnya</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'mingguan' ? 'bg-purple-800 text-purple-100' : 'bg-slate-200 text-slate-700'">
                {{ $totalMingguanOrdersCount }} Paket
            </span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'ecer'" 
            class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5"
            :class="activeTab === 'ecer' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
        >
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>III. Rekapan Parsel Eceran (Mingguan)</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'ecer' ? 'bg-blue-800 text-blue-100' : 'bg-slate-200 text-slate-700'">
                {{ count($ecerItemColumns) }} Komoditas &bull; {{ $totalEcerCustomersCount }} Org
            </span>
        </button>

        @if($tabunganPackages->isNotEmpty())
            <button 
                type="button" 
                @click="activeTab = 'tabungan'" 
                class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5"
                :class="activeTab === 'tabungan' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
            >
                <i class="fa-solid fa-vault"></i>
                <span>IV. Rekapan Tabungan Lebaran</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'tabungan' ? 'bg-amber-800 text-amber-100' : 'bg-slate-200 text-slate-700'">
                    {{ $totalTabunganOrdersCount }} Paket
                </span>
            </button>
        @endif

        <button 
            type="button" 
            @click="activeTab = 'detail'" 
            class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5"
            :class="activeTab === 'detail' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
        >
            <i class="fa-solid fa-sitemap"></i>
            <span>Rincian Konsumen per Agen</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TABEL 1: REKAPITULASI PAKET PARSEL HARIAN PER AGEN -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'harian'" class="bg-white rounded-2xl border-2 border-emerald-200 shadow-xs overflow-hidden">
        <div class="p-5 bg-gradient-to-r from-emerald-50 via-white to-emerald-50/30 border-b border-emerald-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 inline-block"></span>
                    <h3 class="font-heading font-extrabold text-slate-900 text-base flex items-center">
                        <i class="fa-solid fa-calendar-day text-emerald-600 mr-2"></i>
                        I. Matriks Rekapitulasi Agen &amp; Paket Parsel Harian
                    </h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rekapitulasi resmi paket parsel harian: menampilkan <strong>No, Nama Agen, Alamat Agen, Jumlah Konsumen Harian</strong>, dan <strong>pesanan tiap paket parsel harian</strong>.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- FITUR SEARCH KHUSUS HARIAN -->
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-emerald-600 text-xs"></i>
                    <input 
                        type="text" 
                        x-model="searchHarian" 
                        placeholder="Cari agen di tabel harian..." 
                        class="w-full pl-8 pr-7 py-1.5 bg-white border border-emerald-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-2xs"
                    >
                    <button 
                        type="button" 
                        x-show="searchHarian" 
                        @click="searchHarian = ''" 
                        class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <span class="text-xs font-bold text-emerald-900 bg-emerald-100/70 px-3 py-1.5 rounded-xl border border-emerald-200">
                    {{ $harianPackages->count() }} Jenis Paket Harian
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-emerald-50/70 text-emerald-950 font-bold border-b border-emerald-200 text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-3.5 w-10 text-center">No</th>
                        <th class="py-3 px-3.5 min-w-[180px]">Nama Agen Lapangan</th>
                        <th class="py-3 px-3.5 min-w-[200px]">Alamat Agen</th>
                        <th class="py-3 px-3.5 text-center w-36 bg-emerald-100/60 text-emerald-900 border-x border-emerald-200">
                            Jumlah Konsumen (Harian)
                        </th>
                        <!-- DYNAMIC COLUMNS FOR EACH HARIAN PACKAGE -->
                        @foreach($harianPackages as $pkg)
                            <th class="py-3 px-2 text-center min-w-[95px] border-r border-emerald-200" title="{{ $pkg->name }} ({{ $pkg->formatted_target }})">
                                <div class="font-mono text-[10px] text-emerald-700 font-extrabold">
                                    {{ $pkg->code }}
                                </div>
                                <div class="text-[11px] font-bold truncate max-w-[110px] mx-auto text-slate-900">
                                    {{ Str::limit($pkg->name, 16) }}
                                </div>
                                <div class="text-[9px] text-emerald-600 font-mono font-medium mt-0.5">
                                    {{ $pkg->installment_count }} Hari
                                </div>
                            </th>
                        @endforeach
                        <th class="py-3 px-3.5 text-center w-28 bg-emerald-600 text-white font-extrabold">
                            Total Parsel Harian
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agents as $idx => $agent)
                        <tr 
                            class="hover:bg-emerald-50/30 transition"
                            x-show="matchesAgent('{{ addslashes($agent->name) }}', '{{ addslashes($agent->phone) }}', '{{ addslashes($agent->address) }}', searchHarian)"
                        >
                            <td class="py-3 px-3.5 text-center font-mono text-slate-400 font-bold">
                                {{ $idx + 1 }}
                            </td>
                            <td class="py-3 px-3.5 font-bold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($agent->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-900">{{ $agent->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal">
                                            <i class="fa-brands fa-whatsapp text-emerald-500 mr-0.5"></i> {{ $agent->phone }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3.5 text-slate-600">
                                @if($agent->address)
                                    <div class="line-clamp-2" title="{{ $agent->address }}">
                                        <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i>
                                        <span>{{ $agent->address }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 italic">Belum dicatat</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center font-black text-emerald-900 bg-emerald-50/40 border-x border-emerald-100 text-sm">
                                <div>{{ $agent->harian_customers_count }} Orang</div>
                                <div class="text-[10px] font-normal text-slate-400">Total Binaan: {{ $agent->total_customers }} Org</div>
                            </td>

                            <!-- PER HARIAN PACKAGE COUNT FOR THIS AGENT -->
                            @foreach($harianPackages as $pkg)
                                @php
                                    $cnt = $agent->harian_package_counts[$pkg->id] ?? 0;
                                @endphp
                                <td class="py-3 px-2 text-center border-r border-slate-100 {{ $cnt > 0 ? 'bg-emerald-50/60 font-black text-emerald-800' : 'text-slate-300' }}">
                                    @if($cnt > 0)
                                        <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-xs">
                                            {{ $cnt }} Paket
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach

                            <td class="py-3 px-3.5 text-center font-black text-emerald-950 bg-emerald-100/50 text-sm">
                                {{ $agent->total_harian_orders }} Paket
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $harianPackages->count() }}" class="py-8 text-center text-slate-400">
                                Tidak ada data agen lapangan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-950 text-white font-bold border-t-2 border-emerald-800 text-xs">
                        <td colspan="3" class="py-3.5 px-4 text-right uppercase tracking-wider font-extrabold">
                            TOTAL SE-SPL (PARSEL HARIAN):
                        </td>
                        <td class="py-3.5 px-3.5 text-center text-emerald-200 font-extrabold text-sm bg-emerald-900 border-x border-emerald-800">
                            {{ $totalHarianCustomersCount }} Konsumen
                        </td>
                        @foreach($harianPackages as $pkg)
                            @php
                                $pkgSum = $harianPackageTotals[$pkg->id] ?? 0;
                            @endphp
                            <td class="py-3.5 px-2 text-center border-r border-emerald-800 {{ $pkgSum > 0 ? 'text-emerald-300 font-extrabold' : 'text-emerald-600/60' }}">
                                {{ $pkgSum > 0 ? $pkgSum . ' Paket' : '-' }}
                            </td>
                        @endforeach
                        <td class="py-3.5 px-3.5 text-center text-emerald-300 font-extrabold text-base bg-emerald-900">
                            {{ $totalHarianOrdersCount }} Paket
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TABEL 2: REKAPITULASI PAKET MINGGUAN LAINNYA / STANDAR PER AGEN -->
    <!-- ========================================== -->
    @if($mingguanPackages->isNotEmpty())
        <div x-show="activeTab === 'all' || activeTab === 'mingguan'" class="bg-white rounded-2xl border-2 border-purple-200 shadow-xs overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-purple-50 via-white to-purple-50/30 border-b border-purple-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600 inline-block"></span>
                        <h3 class="font-heading font-extrabold text-slate-900 text-base flex items-center">
                            <i class="fa-solid fa-calendar-week text-purple-600 mr-2"></i>
                            II. Matriks Rekapitulasi Agen &amp; Paket Mingguan Lainnya / Standar
                        </h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rekapitulasi resmi paket parsel mingguan terstruktur (non-eceran seperti Mingguan Hemat, Rokok, Ekonomis Ibu, dsb.): menampilkan <strong>No, Nama Agen, Alamat Agen, Jumlah Konsumen Mingguan</strong>, dan <strong>pesanan tiap paket mingguan</strong>.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <!-- FITUR SEARCH KHUSUS MINGGUAN LAINNYA -->
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-purple-600 text-xs"></i>
                        <input 
                            type="text" 
                            x-model="searchMingguan" 
                            placeholder="Cari agen di tabel mingguan..." 
                            class="w-full pl-8 pr-7 py-1.5 bg-white border border-purple-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 shadow-2xs"
                        >
                        <button 
                            type="button" 
                            x-show="searchMingguan" 
                            @click="searchMingguan = ''" 
                            class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <span class="text-xs font-bold text-purple-900 bg-purple-100/70 px-3 py-1.5 rounded-xl border border-purple-200">
                        {{ $mingguanPackages->count() }} Jenis Paket Mingguan Reguler
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-purple-50/70 text-purple-950 font-bold border-b border-purple-200 text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-10 text-center">No</th>
                            <th class="py-3 px-3.5 min-w-[180px]">Nama Agen Lapangan</th>
                            <th class="py-3 px-3.5 min-w-[200px]">Alamat Agen</th>
                            <th class="py-3 px-3.5 text-center w-36 bg-purple-100/60 text-purple-900 border-x border-purple-200">
                                Jumlah Konsumen (Mingguan)
                            </th>
                            <!-- DYNAMIC COLUMNS FOR EACH MINGGUAN PACKAGE -->
                            @foreach($mingguanPackages as $pkg)
                                <th class="py-3 px-2 text-center min-w-[100px] border-r border-purple-200" title="{{ $pkg->name }} ({{ $pkg->formatted_target }})">
                                    <div class="font-mono text-[10px] text-purple-700 font-extrabold">
                                        {{ $pkg->code }}
                                    </div>
                                    <div class="text-[11px] font-bold truncate max-w-[120px] mx-auto text-slate-900">
                                        {{ Str::limit($pkg->name, 18) }}
                                    </div>
                                    <div class="text-[9px] text-purple-600 font-mono font-medium mt-0.5">
                                        {{ $pkg->installment_count }} Minggu &bull; {{ $pkg->formatted_installment }}
                                    </div>
                                </th>
                            @endforeach
                            <th class="py-3 px-3.5 text-center w-28 bg-purple-600 text-white font-extrabold">
                                Total Paket Mingguan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($agents as $idx => $agent)
                            <tr 
                                class="hover:bg-purple-50/30 transition"
                                x-show="matchesAgent('{{ addslashes($agent->name) }}', '{{ addslashes($agent->phone) }}', '{{ addslashes($agent->address) }}', searchMingguan)"
                            >
                                <td class="py-3 px-3.5 text-center font-mono text-slate-400 font-bold">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-3.5 font-bold text-slate-900">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-800 font-extrabold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900">{{ $agent->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-normal">
                                                <i class="fa-brands fa-whatsapp text-emerald-500 mr-0.5"></i> {{ $agent->phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-slate-600">
                                    @if($agent->address)
                                        <div class="line-clamp-2" title="{{ $agent->address }}">
                                            <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i>
                                            <span>{{ $agent->address }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-300 italic">Belum dicatat</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center font-black text-purple-900 bg-purple-50/40 border-x border-purple-100 text-sm">
                                    <div>{{ $agent->mingguan_customers_count }} Orang</div>
                                    <div class="text-[10px] font-normal text-slate-400">Total Binaan: {{ $agent->total_customers }} Org</div>
                                </td>

                                <!-- PER MINGGUAN PACKAGE COUNT FOR THIS AGENT -->
                                @foreach($mingguanPackages as $pkg)
                                    @php
                                        $cnt = $agent->mingguan_package_counts[$pkg->id] ?? 0;
                                    @endphp
                                    <td class="py-3 px-2 text-center border-r border-slate-100 {{ $cnt > 0 ? 'bg-purple-50/60 font-black text-purple-800' : 'text-slate-300' }}">
                                        @if($cnt > 0)
                                            <span class="inline-block px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 font-extrabold text-xs">
                                                {{ $cnt }} Paket
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach

                                <td class="py-3 px-3.5 text-center font-black text-purple-950 bg-purple-100/50 text-sm">
                                    {{ $agent->total_mingguan_orders }} Paket
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $mingguanPackages->count() }}" class="py-8 text-center text-slate-400">
                                    Tidak ada data agen lapangan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-purple-950 text-white font-bold border-t-2 border-purple-800 text-xs">
                            <td colspan="3" class="py-3.5 px-4 text-right uppercase tracking-wider font-extrabold">
                                TOTAL SE-SPL (MINGGUAN LAINNYA):
                            </td>
                            <td class="py-3.5 px-3.5 text-center text-purple-200 font-extrabold text-sm bg-purple-900 border-x border-purple-800">
                                {{ $totalMingguanCustomersCount }} Konsumen
                            </td>
                            @foreach($mingguanPackages as $pkg)
                                @php
                                    $pkgSum = $mingguanPackageTotals[$pkg->id] ?? 0;
                                @endphp
                                <td class="py-3.5 px-2 text-center border-r border-purple-800 {{ $pkgSum > 0 ? 'text-purple-300 font-extrabold' : 'text-purple-600/60' }}">
                                    {{ $pkgSum > 0 ? $pkgSum . ' Paket' : '-' }}
                                </td>
                            @endforeach
                            <td class="py-3.5 px-3.5 text-center text-purple-300 font-extrabold text-base bg-purple-900">
                                {{ $totalMingguanOrdersCount }} Paket
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TABEL 3: REKAPITULASI PARSEL ECERAN (MINGGUAN) & PERSEBARAN NAMA BARANG ECERAN -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'ecer'" class="bg-white rounded-2xl border-2 border-blue-200 shadow-xs overflow-hidden">
        <div class="p-5 bg-gradient-to-r from-blue-50 via-white to-blue-50/30 border-b border-blue-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                    <h3 class="font-heading font-extrabold text-slate-900 text-base flex items-center">
                        <i class="fa-solid fa-boxes-stacked text-blue-600 mr-2"></i>
                        III. Rekapitulasi Parsel Eceran (Mingguan) &amp; Persebaran Barang Eceran
                    </h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Format khusus parsel eceran mingguan: menampilkan <strong>No, Nama Agen, Alamat Agen, Jumlah Konsumen Eceran</strong>, dan <strong>kolom nama-nama barang eceran</strong> (misal: Daging Sapi, Daging Ayam, Beras, Minyak, dsb).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- FITUR SEARCH KHUSUS ECERAN -->
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-blue-600 text-xs"></i>
                    <input 
                        type="text" 
                        x-model="searchEcer" 
                        placeholder="Cari agen / komoditas barang..." 
                        class="w-full pl-8 pr-7 py-1.5 bg-white border border-blue-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-2xs"
                    >
                    <button 
                        type="button" 
                        x-show="searchEcer" 
                        @click="searchEcer = ''" 
                        class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <span class="text-xs font-bold text-blue-900 bg-blue-100/70 px-3 py-1.5 rounded-xl border border-blue-200">
                    {{ count($ecerItemColumns) }} Komoditas Barang Eceran
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-blue-50/70 text-blue-950 font-bold border-b border-blue-200 text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-3.5 w-10 text-center">No</th>
                        <th class="py-3 px-3.5 min-w-[180px]">Nama Agen Lapangan</th>
                        <th class="py-3 px-3.5 min-w-[180px]">Alamat Agen</th>
                        <th class="py-3 px-3.5 text-center w-36 bg-blue-100/60 text-blue-900 border-x border-blue-200">
                            Jumlah Konsumen (Eceran)
                        </th>
                        <!-- DYNAMIC COLUMNS FOR EACH ECERAN GOOD / ITEM (NAMA-NAMA BARANG ECERAN) -->
                        @foreach($ecerItemColumns as $colName)
                            <th class="py-3 px-2.5 text-center min-w-[110px] border-r border-blue-200" title="Barang Eceran: {{ $colName }}">
                                <div class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 text-[9px] font-extrabold uppercase mb-0.5">
                                    <i class="fa-solid fa-cube mr-1 text-[8px]"></i> Eceran
                                </div>
                                <div class="text-[11px] font-extrabold truncate max-w-[130px] mx-auto text-slate-900" title="{{ $colName }}">
                                    {{ $colName }}
                                </div>
                            </th>
                        @endforeach
                        <th class="py-3 px-3.5 text-center w-28 bg-blue-600 text-white font-extrabold">
                            Total Item Eceran
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agents as $idx => $agent)
                        <tr 
                            class="hover:bg-blue-50/30 transition"
                            x-show="!searchEcer || matchesAgent('{{ addslashes($agent->name) }}', '{{ addslashes($agent->phone) }}', '{{ addslashes($agent->address) }}', searchEcer) || '{{ strtolower(implode(' ', array_keys(array_filter($agent->ecer_item_counts ?? [])))) }}'.includes(searchEcer.toLowerCase().trim())"
                        >
                            <td class="py-3 px-3.5 text-center font-mono text-slate-400 font-bold">
                                {{ $idx + 1 }}
                            </td>
                            <td class="py-3 px-3.5 font-bold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 font-extrabold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($agent->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-900">{{ $agent->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal">
                                            <i class="fa-brands fa-whatsapp text-emerald-500 mr-0.5"></i> {{ $agent->phone }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3.5 text-slate-600">
                                @if($agent->address)
                                    <div class="line-clamp-2" title="{{ $agent->address }}">
                                        <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i>
                                        <span>{{ $agent->address }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 italic">Belum dicatat</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center font-black text-blue-900 bg-blue-50/40 border-x border-blue-100 text-sm">
                                <div>{{ $agent->ecer_customers_count }} Orang</div>
                                <div class="text-[10px] font-normal text-slate-400">Total Binaan: {{ $agent->total_customers }} Org</div>
                            </td>

                            <!-- PER ECERAN GOOD FOR THIS AGENT -->
                            @foreach($ecerItemColumns as $colName)
                                @php
                                    $custCount = $agent->ecer_item_counts[$colName] ?? 0;
                                    $qtyCount = $agent->ecer_item_qty[$colName] ?? 0;
                                @endphp
                                <td class="py-3 px-2 text-center border-r border-slate-100 {{ $custCount > 0 ? 'bg-blue-50/60 font-black text-blue-900' : 'text-slate-300' }}">
                                    @if($custCount > 0)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-900 font-black text-xs shadow-2xs">
                                                {{ $custCount }} Orang
                                            </span>
                                            @if($qtyCount > $custCount)
                                                <span class="text-[9px] text-slate-500 font-mono mt-0.5">({{ $qtyCount }} Pcs)</span>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach

                            <td class="py-3 px-3.5 text-center font-black text-blue-950 bg-blue-100/50 text-sm">
                                {{ $agent->total_ecer_orders }} Item
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + count($ecerItemColumns) }}" class="py-8 text-center text-slate-400">
                                Tidak ada data agen lapangan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-blue-950 text-white font-bold border-t-2 border-blue-800 text-xs">
                        <td colspan="3" class="py-3.5 px-4 text-right uppercase tracking-wider font-extrabold">
                            TOTAL SE-SPL (PARSEL ECERAN):
                        </td>
                        <td class="py-3.5 px-3.5 text-center text-blue-200 font-extrabold text-sm bg-blue-900 border-x border-blue-800">
                            {{ $totalEcerCustomersCount }} Konsumen
                        </td>
                        @foreach($ecerItemColumns as $colName)
                            @php
                                $totCust = $ecerItemTotals[$colName]['customers'] ?? 0;
                                $totQty = $ecerItemTotals[$colName]['qty'] ?? 0;
                            @endphp
                            <td class="py-3.5 px-2 text-center border-r border-blue-800 {{ $totCust > 0 ? 'text-blue-200 font-extrabold' : 'text-blue-600/60' }}">
                                @if($totCust > 0)
                                    <div>{{ $totCust }} Orang</div>
                                    @if($totQty > $totCust)
                                        <div class="text-[9px] text-blue-300 font-mono">({{ $totQty }} Pcs)</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                        <td class="py-3.5 px-3.5 text-center text-blue-200 font-extrabold text-base bg-blue-900">
                            {{ $totalEcerOrdersCount }} Item
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TABEL 4: REKAPITULASI TABUNGAN LEBARAN PER AGEN -->
    <!-- ========================================== -->
    @if($tabunganPackages->isNotEmpty())
        <div x-show="activeTab === 'all' || activeTab === 'tabungan'" class="bg-white rounded-2xl border-2 border-amber-200 shadow-xs overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-amber-50 via-white to-amber-50/30 border-b border-amber-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-600 inline-block"></span>
                        <h3 class="font-heading font-extrabold text-slate-900 text-base flex items-center">
                            <i class="fa-solid fa-vault text-amber-600 mr-2"></i>
                            IV. Matriks Rekapitulasi Agen &amp; Paket Tabungan Lebaran
                        </h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rekapitulasi resmi paket tabungan uang dan bonus per agen lapangan.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <!-- FITUR SEARCH KHUSUS TABUNGAN -->
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-amber-600 text-xs"></i>
                        <input 
                            type="text" 
                            x-model="searchTabungan" 
                            placeholder="Cari agen di tabel tabungan..." 
                            class="w-full pl-8 pr-7 py-1.5 bg-white border border-amber-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-2xs"
                        >
                        <button 
                            type="button" 
                            x-show="searchTabungan" 
                            @click="searchTabungan = ''" 
                            class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <span class="text-xs font-bold text-amber-900 bg-amber-100/70 px-3 py-1.5 rounded-xl border border-amber-200">
                        {{ $tabunganPackages->count() }} Jenis Paket Tabungan
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-amber-50/70 text-amber-950 font-bold border-b border-amber-200 text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-10 text-center">No</th>
                            <th class="py-3 px-3.5 min-w-[180px]">Nama Agen Lapangan</th>
                            <th class="py-3 px-3.5 min-w-[200px]">Alamat Agen</th>
                            <th class="py-3 px-3.5 text-center w-36 bg-amber-100/60 text-amber-900 border-x border-amber-200">
                                Jumlah Konsumen (Tabungan)
                            </th>
                            <!-- DYNAMIC COLUMNS FOR EACH TABUNGAN PACKAGE -->
                            @foreach($tabunganPackages as $pkg)
                                <th class="py-3 px-2 text-center min-w-[95px] border-r border-amber-200" title="{{ $pkg->name }} ({{ $pkg->formatted_target }})">
                                    <div class="font-mono text-[10px] text-amber-700 font-extrabold">
                                        {{ $pkg->code }}
                                    </div>
                                    <div class="text-[11px] font-bold truncate max-w-[110px] mx-auto text-slate-900">
                                        {{ Str::limit($pkg->name, 16) }}
                                    </div>
                                    <div class="text-[9px] text-amber-700 font-mono font-medium mt-0.5">
                                        {{ $pkg->formatted_target }}
                                    </div>
                                </th>
                            @endforeach
                            <th class="py-3 px-3.5 text-center w-28 bg-amber-600 text-white font-extrabold">
                                Total Tabungan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($agents as $idx => $agent)
                            <tr 
                                class="hover:bg-amber-50/30 transition"
                                x-show="matchesAgent('{{ addslashes($agent->name) }}', '{{ addslashes($agent->phone) }}', '{{ addslashes($agent->address) }}', searchTabungan)"
                            >
                                <td class="py-3 px-3.5 text-center font-mono text-slate-400 font-bold">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-3.5 font-bold text-slate-900">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 font-extrabold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900">{{ $agent->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-normal">
                                                <i class="fa-brands fa-whatsapp text-emerald-500 mr-0.5"></i> {{ $agent->phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-slate-600">
                                    @if($agent->address)
                                        <div class="line-clamp-2" title="{{ $agent->address }}">
                                            <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i>
                                            <span>{{ $agent->address }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-300 italic">Belum dicatat</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center font-black text-amber-900 bg-amber-50/40 border-x border-amber-100 text-sm">
                                    <div>{{ $agent->tabungan_customers_count }} Orang</div>
                                    <div class="text-[10px] font-normal text-slate-400">Total Binaan: {{ $agent->total_customers }} Org</div>
                                </td>

                                <!-- PER TABUNGAN PACKAGE COUNT FOR THIS AGENT -->
                                @foreach($tabunganPackages as $pkg)
                                    @php
                                        $cnt = $agent->tabungan_package_counts[$pkg->id] ?? 0;
                                    @endphp
                                    <td class="py-3 px-2 text-center border-r border-slate-100 {{ $cnt > 0 ? 'bg-amber-50/60 font-black text-amber-800' : 'text-slate-300' }}">
                                        @if($cnt > 0)
                                            <span class="inline-block px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs">
                                                {{ $cnt }} Paket
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach

                                <td class="py-3 px-3.5 text-center font-black text-amber-950 bg-amber-100/50 text-sm">
                                    {{ $agent->total_tabungan_orders }} Paket
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $tabunganPackages->count() }}" class="py-8 text-center text-slate-400">
                                    Tidak ada data agen lapangan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-950 text-white font-bold border-t-2 border-amber-800 text-xs">
                            <td colspan="3" class="py-3.5 px-4 text-right uppercase tracking-wider font-extrabold">
                                TOTAL SE-SPL (TABUNGAN):
                            </td>
                            <td class="py-3.5 px-3.5 text-center text-amber-200 font-extrabold text-sm bg-amber-900 border-x border-amber-800">
                                {{ $totalTabunganCustomersCount }} Konsumen
                            </td>
                            @foreach($tabunganPackages as $pkg)
                                @php
                                    $pkgSum = $tabunganPackageTotals[$pkg->id] ?? 0;
                                @endphp
                                <td class="py-3.5 px-2 text-center border-r border-amber-800 {{ $pkgSum > 0 ? 'text-amber-300 font-extrabold' : 'text-amber-600/60' }}">
                                    {{ $pkgSum > 0 ? $pkgSum . ' Paket' : '-' }}
                                </td>
                            @endforeach
                            <td class="py-3.5 px-3.5 text-center text-amber-300 font-extrabold text-base bg-amber-900">
                                {{ $totalTabunganOrdersCount }} Paket
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- SECTION V: RINCIAN SUB-DATA KONSUMEN & PAKET PER AGEN -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'all' || activeTab === 'detail'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base flex items-center">
                    <i class="fa-solid fa-sitemap text-emerald-600 mr-2"></i> Rincian Sub-Data Konsumen &amp; Paket per Agen
                </h3>
                <p class="text-xs text-slate-500">
                    Daftar nama konsumen binaan dari masing-masing agen beserta paket apa saja yang dipilih, rincian barang eceran, target, dan progres setoran.
                </p>
            </div>

            <!-- SEARCH FILTER KHUSUS SUB-DATA KONSUMEN -->
            <div class="w-full sm:w-80">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input 
                        type="text" 
                        x-model="searchDetail" 
                        placeholder="Cari agen, konsumen, no HP, atau paket..." 
                        class="w-full pl-8 pr-7 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-emerald-500 shadow-xs"
                    >
                    <button 
                        type="button" 
                        x-show="searchDetail" 
                        @click="searchDetail = ''" 
                        class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        @foreach($agents as $agent)
            @php
                $agentSearchPayload = strtolower(
                    $agent->name . ' ' . 
                    $agent->address . ' ' . 
                    $agent->phone . ' ' . 
                    $agent->customers->pluck('name')->implode(' ') . ' ' . 
                    $agent->customers->pluck('phone')->implode(' ') . ' ' . 
                    $agent->customers->flatMap->customerPackages->pluck('package.name')->implode(' ')
                );
            @endphp
            <div 
                class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden transition"
                x-show="!searchDetail || '{{ addslashes($agentSearchPayload) }}'.includes(searchDetail.toLowerCase().trim())"
            >
                <!-- AGENT HEADER BANNER -->
                <div class="bg-gradient-to-r from-slate-50 via-white to-emerald-50/30 p-4 sm:p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-start space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white font-extrabold flex items-center justify-center text-lg shadow-sm shrink-0">
                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-heading font-extrabold text-slate-900 text-base">{{ $agent->name }}</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 font-mono">
                                    @<span>{{ $agent->username }}</span>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $agent->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $agent->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                <span><i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i> {{ $agent->phone }}</span>
                                <span><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> <strong>Alamat:</strong> {{ $agent->address ?: 'Belum diisi' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- AGENT TOTALS PILLS -->
                    <div class="flex items-center space-x-2.5 shrink-0 flex-wrap">
                        <div class="text-right px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200">
                            <div class="text-[9px] font-bold uppercase text-slate-600">Total Binaan</div>
                            <div class="text-sm font-heading font-extrabold text-slate-900">{{ $agent->customers->count() }} Orang</div>
                        </div>
                        <div class="text-right px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <div class="text-[9px] font-bold uppercase text-emerald-700">Parsel Harian</div>
                            <div class="text-sm font-heading font-extrabold text-emerald-950">{{ $agent->total_harian_orders }} Paket</div>
                        </div>
                        <div class="text-right px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-200">
                            <div class="text-[9px] font-bold uppercase text-purple-700">Mingguan Lainnya</div>
                            <div class="text-sm font-heading font-extrabold text-purple-950">{{ $agent->total_mingguan_orders }} Paket</div>
                        </div>
                        <div class="text-right px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200">
                            <div class="text-[9px] font-bold uppercase text-blue-700">Parsel Eceran</div>
                            <div class="text-sm font-heading font-extrabold text-blue-950">{{ $agent->total_ecer_orders }} Item</div>
                        </div>
                    </div>
                </div>

                <!-- SUB-DATA TABLE: KONSUMEN & PAKET YANG DIPILIH -->
                @if($agent->customers->isEmpty())
                    <div class="p-8 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-users-slash text-2xl text-slate-300 mb-2"></i>
                        <div>Agen ini belum memiliki konsumen binaan terdaftar.</div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-500 font-semibold border-b border-slate-100 text-[11px]">
                                    <th class="py-2.5 px-4 w-10 text-center">#</th>
                                    <th class="py-2.5 px-4 min-w-[160px]">Nama Konsumen</th>
                                    <th class="py-2.5 px-4 min-w-[140px]">Kontak &amp; Alamat Konsumen</th>
                                    <th class="py-2.5 px-4 min-w-[240px]">Paket &amp; Barang yang Dipilih</th>
                                    <th class="py-2.5 px-4 min-w-[150px] text-right">Target &amp; Cicilan</th>
                                    <th class="py-2.5 px-4 min-w-[160px]">Progres Pembayaran</th>
                                    <th class="py-2.5 px-4 text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($agent->customers as $cIdx => $customer)
                                    @php
                                        $custSearchPayload = strtolower(
                                            $agent->name . ' ' . 
                                            $customer->name . ' ' . 
                                            $customer->address . ' ' . 
                                            $customer->phone . ' ' . 
                                            $customer->customerPackages->pluck('package.name')->implode(' ')
                                        );
                                    @endphp
                                    <tr 
                                        class="hover:bg-slate-50/70 transition"
                                        x-show="!searchDetail || '{{ addslashes($custSearchPayload) }}'.includes(searchDetail.toLowerCase().trim())"
                                    >
                                        <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]">
                                            {{ $cIdx + 1 }}
                                        </td>
                                        <td class="py-3 px-4 font-bold text-slate-900">
                                            <a href="{{ route('customers.show', $customer) }}" class="hover:text-emerald-700 hover:underline flex items-center space-x-1.5">
                                                <span>{{ $customer->name }}</span>
                                            </a>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $customer->code ?: 'KONS-'.str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 text-[11px]">
                                            <div class="flex items-center space-x-1">
                                                <i class="fa-solid fa-phone text-slate-400 text-[9px]"></i>
                                                <span>{{ $customer->phone ?: '-' }}</span>
                                            </div>
                                            <div class="text-slate-500 line-clamp-1 mt-0.5">
                                                <i class="fa-solid fa-map-pin text-slate-400 text-[9px] mr-0.5"></i>
                                                {{ $customer->address ?: 'Paseh' }}
                                            </div>
                                        </td>

                                        <!-- PAKET YANG DIPILIH KONSUMEN -->
                                        <td class="py-3 px-4">
                                            @forelse($customer->customerPackages as $cp)
                                                @php
                                                    $pkgTheme = $cp->package->isEcer() 
                                                        ? 'bg-blue-50/60 border-blue-200 text-blue-900' 
                                                        : ($cp->package->isTabungan() 
                                                            ? 'bg-amber-50/60 border-amber-200 text-amber-900' 
                                                            : ($cp->package->isMingguan() 
                                                                ? 'bg-purple-50/60 border-purple-200 text-purple-900' 
                                                                : 'bg-emerald-50/60 border-emerald-200 text-emerald-900'));
                                                    
                                                    $badgeColor = $cp->package->isEcer() 
                                                        ? 'bg-blue-600 text-white' 
                                                        : ($cp->package->isTabungan() 
                                                            ? 'bg-amber-600 text-white' 
                                                            : ($cp->package->isMingguan() 
                                                                ? 'bg-purple-600 text-white' 
                                                                : 'bg-emerald-600 text-white'));
                                                @endphp
                                                <div class="p-2.5 rounded-xl border {{ $pkgTheme }} mb-1.5 last:mb-0">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-mono text-[9px] font-extrabold px-1.5 py-0.2 rounded {{ $badgeColor }}">
                                                            {{ $cp->package->code }}
                                                        </span>
                                                        <span class="text-[10px] font-bold text-slate-600">
                                                            {{ $cp->package->installment_count }} {{ $cp->package->period_label }}
                                                        </span>
                                                    </div>
                                                    <div class="font-extrabold text-slate-900 text-xs mt-1">
                                                        {{ $cp->package->name }}
                                                    </div>

                                                    <!-- RINCIAN BARANG ECERAN PILIHAN KONSUMEN -->
                                                    @if($cp->package->isEcer())
                                                        @if($cp->customItems->isNotEmpty())
                                                            <div class="mt-1.5 pt-1.5 border-t border-blue-200/80 flex flex-wrap gap-1">
                                                                @foreach($cp->customItems as $ci)
                                                                    <span class="px-2 py-0.5 rounded-md bg-white border border-blue-200 text-blue-900 text-[10px] font-bold shadow-2xs">
                                                                        <i class="fa-solid fa-cube text-blue-600 mr-1 text-[8px]"></i>
                                                                        {{ $ci->item ? $ci->item->name : 'Item' }} ({{ $ci->quantity }} pcs)
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @elseif($cp->package->packageItems->isNotEmpty())
                                                            <div class="mt-1.5 pt-1.5 border-t border-blue-200/80 flex flex-wrap gap-1">
                                                                @foreach($cp->package->packageItems as $pi)
                                                                    <span class="px-2 py-0.5 rounded-md bg-white border border-blue-200 text-blue-900 text-[10px] font-bold shadow-2xs">
                                                                        <i class="fa-solid fa-cube text-blue-600 mr-1 text-[8px]"></i>
                                                                        {{ $pi->item ? $pi->item->name : 'Item' }} ({{ $pi->quantity }} pcs)
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-slate-400 italic text-[11px]">Belum memilih paket</span>
                                            @endforelse
                                        </td>

                                        <!-- TARGET & CICILAN PERIODE -->
                                        <td class="py-3 px-4 text-right">
                                            @forelse($customer->customerPackages as $cp)
                                                <div class="mb-1.5 last:mb-0">
                                                    <div class="font-extrabold text-slate-900 text-xs">{{ $cp->formatted_target }}</div>
                                                    <div class="text-[10px] text-emerald-700 font-semibold">
                                                        {{ $cp->package->formatted_installment }} / {{ strtolower($cp->package->period_label) }}
                                                    </div>
                                                </div>
                                            @empty
                                                <span class="text-slate-300">-</span>
                                            @endforelse
                                        </td>

                                        <!-- PROGRESS PEMBAYARAN -->
                                        <td class="py-3 px-4">
                                            @forelse($customer->customerPackages as $cp)
                                                <div class="mb-2 last:mb-0 space-y-1">
                                                    <div class="flex justify-between text-[10px] font-semibold">
                                                        <span class="text-emerald-700 font-bold">{{ $cp->formatted_paid }}</span>
                                                        <span class="text-slate-500">{{ $cp->progress_percent }}%</span>
                                                    </div>
                                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $cp->progress_percent }}%"></div>
                                                    </div>
                                                </div>
                                            @empty
                                                <span class="text-slate-300">-</span>
                                            @endforelse
                                        </td>

                                        <!-- AKSI CEKLIS KONSUMEN -->
                                        <td class="py-3 px-4 text-center">
                                            @if($customer->customerPackages->isNotEmpty())
                                                <a href="{{ route('payments.checklist', $customer->customerPackages->first()) }}" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[10px] transition inline-flex items-center">
                                                    <i class="fa-solid fa-list-check mr-1"></i> Ceklis
                                                </a>
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
