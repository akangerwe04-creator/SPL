@extends('layouts.app')

@section('title', 'Master Paket Parsel & Tabungan')
@section('page_title', 'Master Paket Parsel, Tabungan & Ecer')

@section('header_badge')
    <div class="flex items-center space-x-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
            {{ $packages->count() }} Paket Tersedia
        </span>
        @php
            $noSafeCount = $packages->filter(fn($p) => !$p->hasSafeThreshold())->count();
        @endphp
        @if($noSafeCount > 0)
            <a href="{{ route('packages.index', ['safe_status' => 'no_safe']) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200 transition shadow-2xs" title="Filter paket yang belum memiliki titik aman">
                <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600"></i> {{ $noSafeCount }} Belum Ada Titik Aman
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    showImportModal: false,
    showBulkAdjustModal: false,
    bulkAdjustScope: 'selected',
    bulkAdjustCount: 30,
    bulkAdjustMode: 'keep_target',
    bulkAdjustSyncCustomers: false,
    viewMode: localStorage.getItem('spl_package_view_mode') || 'grid',
    searchQuery: '{{ request('search', '') }}',
    selectedPackages: [],
    selectAll: false,
    allPackages: [
        @foreach($packages as $pkg)
        {
            id: {{ $pkg->id }},
            code: '{{ $pkg->code }}',
            name: '{{ addslashes($pkg->name) }}',
            type: '{{ $pkg->type }}',
            period_type: '{{ $pkg->period_type ?? 'mingguan' }}',
            installment_count: {{ (int) $pkg->installment_count }},
            installment_amount: {{ (float) $pkg->installment_amount }},
            target_nominal: {{ (float) $pkg->target_nominal }},
            has_safe_threshold: {{ $pkg->hasSafeThreshold() ? 'true' : 'false' }},
            search: '{{ addslashes(strtolower($pkg->name . ' ' . $pkg->code . ' ' . $pkg->type . ' ' . $pkg->period_type . ' ' . $pkg->installment_label . ' ' . ($pkg->hasSafeThreshold() ? 'aman ' . $pkg->formatted_safe_threshold : 'belum ada titik aman tanpa titik aman') . ' ' . ($pkg->description ?? '') . ' ' . $pkg->packageItems->map(fn($pi) => $pi->item?->name)->filter()->implode(' '))) }}'
        },
        @endforeach
    ],
    setViewMode(mode) {
        this.viewMode = mode;
        localStorage.setItem('spl_package_view_mode', mode);
    },
    matchesSearch(dataStr) {
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase().trim();
        return dataStr.toLowerCase().includes(q);
    },
    toggleSelectAll() {
        if (this.selectAll) {
            const q = this.searchQuery.toLowerCase().trim();
            this.selectedPackages = this.allPackages
                .filter(p => !q || p.search.includes(q))
                .map(p => p.id);
        } else {
            this.selectedPackages = [];
        }
    },
    updateSelectAllState() {
        const q = this.searchQuery.toLowerCase().trim();
        const visible = this.allPackages.filter(p => !q || p.search.includes(q));
        if (visible.length === 0) {
            this.selectAll = false;
            return;
        }
        this.selectAll = visible.every(p => this.selectedPackages.includes(p.id));
    },
    clearSelection() {
        this.selectedPackages = [];
        this.selectAll = false;
    },
    openBulkAdjustModal() {
        if (this.selectedPackages.length > 0) {
            this.bulkAdjustScope = 'selected';
            const first = this.allPackages.find(p => p.id === this.selectedPackages[0]);
            if (first) {
                this.bulkAdjustCount = first.installment_count || (first.period_type === 'harian' ? 100 : 30);
            }
        } else {
            this.bulkAdjustScope = 'all_mingguan';
            this.bulkAdjustCount = 30;
        }
        this.showBulkAdjustModal = true;
    },
    openBulkAdjustModalForSelected() {
        this.bulkAdjustScope = 'selected';
        const first = this.allPackages.find(p => p.id === this.selectedPackages[0]);
        if (first) {
            this.bulkAdjustCount = first.installment_count || (first.period_type === 'harian' ? 100 : 30);
        }
        this.showBulkAdjustModal = true;
    },
    getAffectedPackages() {
        if (this.bulkAdjustScope === 'selected') {
            return this.allPackages.filter(p => this.selectedPackages.includes(p.id));
        } else if (this.bulkAdjustScope === 'all_mingguan') {
            return this.allPackages.filter(p => p.period_type === 'mingguan');
        } else if (this.bulkAdjustScope === 'all_harian') {
            return this.allPackages.filter(p => p.period_type === 'harian');
        } else if (this.bulkAdjustScope === 'all_parsel') {
            return this.allPackages.filter(p => p.type === 'parsel');
        } else if (this.bulkAdjustScope === 'all_tabungan') {
            return this.allPackages.filter(p => p.type === 'tabungan');
        }
        return this.allPackages;
    },
    calculateNewInstallment(pkg, newCount, mode) {
        if (!newCount || newCount <= 0) return pkg.installment_amount;
        if (mode === 'keep_target') {
            const target = pkg.target_nominal > 0 ? pkg.target_nominal : (pkg.installment_amount * pkg.installment_count);
            const raw = target / newCount;
            const intVal = Math.ceil(raw);
            const rem = intVal % 1000;
            if (rem === 0) return intVal;
            const base = Math.floor(intVal / 1000) * 1000;
            return rem <= 500 ? (base + 500) : (base + 1000);
        } else {
            return pkg.installment_amount;
        }
    },
    calculateNewTarget(pkg, newCount, mode) {
        if (!newCount || newCount <= 0) return pkg.target_nominal;
        if (mode === 'keep_target') {
            return pkg.target_nominal;
        } else {
            return pkg.installment_amount * newCount;
        }
    },
    formatRupiah(number) {
        return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
    }
}">

    <!-- TOP TOOLBAR: SEARCH, FILTERS & VIEW MODE -->
    <div class="bg-white rounded-2xl border border-pink-100 shadow-xs p-4 space-y-3 text-xs">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <!-- SEARCH BAR & FILTER DROPDOWNS -->
            <form id="packages-filter-form" action="{{ route('packages.index') }}" method="GET" class="flex flex-1 flex-wrap items-center gap-2.5">
                <!-- SEARCH INPUT DENGAN PENCARIAN REAL-TIME & SERVER -->
                <div class="relative flex-1 min-w-[240px] max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        x-model="searchQuery" 
                        value="{{ request('search') }}" 
                        placeholder="Cari nama paket, kode (PAR/TAB), isi barang..."
                        class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-200 transition"
                    >
                    <button 
                        type="button" 
                        x-show="searchQuery" 
                        @click="searchQuery = ''; if('{{ request('search') }}') window.location.href='{{ route('packages.index') }}';" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                        title="Bersihkan Pencarian"
                    >
                        <i class="fa-solid fa-circle-xmark text-xs"></i>
                    </button>
                </div>

                <!-- FILTER TIPE PAKET -->
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:border-pink-500 focus:outline-none">
                    <option value="">Semua Jenis Paket</option>
                    <option value="parsel" {{ request('type') === 'parsel' ? 'selected' : '' }}>📦 Parsel (Barang)</option>
                    <option value="tabungan" {{ request('type') === 'tabungan' ? 'selected' : '' }}>💵 Tabungan (Uang + Bonus)</option>
                    <option value="ecer" {{ request('type') === 'ecer' ? 'selected' : '' }}>🛒 Eceran (Bebas Rakit)</option>
                </select>

                <!-- FILTER SKEMA PERIODE -->
                <select name="period_type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:border-pink-500 focus:outline-none">
                    <option value="">Semua Skema (Mingguan & Harian)</option>
                    <option value="mingguan" {{ request('period_type') === 'mingguan' ? 'selected' : '' }}>📅 Skema Mingguan</option>
                    <option value="harian" {{ request('period_type') === 'harian' ? 'selected' : '' }}>☀️ Skema Harian</option>
                </select>

                <!-- FILTER STATUS TITIK AMAN -->
                <select name="safe_status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:border-pink-500 focus:outline-none">
                    <option value="">Semua Status Titik Aman</option>
                    <option value="has_safe" {{ request('safe_status') === 'has_safe' ? 'selected' : '' }}>🛡️ Memiliki Titik Aman</option>
                    <option value="no_safe" {{ request('safe_status') === 'no_safe' ? 'selected' : '' }}>⚠️ Belum Ada Titik Aman</option>
                </select>

                @if(request()->filled('type') || request()->filled('period_type') || request()->filled('safe_status') || request()->filled('search'))
                    <a href="{{ route('packages.index') }}" class="px-2.5 py-1.5 text-slate-500 hover:text-pink-600 font-bold flex items-center">
                        <i class="fa-solid fa-xmark mr-1"></i> Reset Filter
                    </a>
                @endif
            </form>

            <!-- VIEW MODE SWITCHER & QUICK ACTIONS -->
            <div class="flex flex-wrap items-center gap-2 justify-end">
                <!-- VIEW SWITCHER (GRID VS LIST) -->
                <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 shrink-0">
                    <button type="button" 
                        @click="setViewMode('grid')" 
                        :class="viewMode === 'grid' ? 'bg-white text-pink-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'" 
                        class="px-3 py-1.5 rounded-lg text-xs flex items-center transition"
                        title="Tampilan Grid Kartu Foto"
                    >
                        <i class="fa-solid fa-grip mr-1.5"></i> Grid Kartu
                    </button>
                    <button type="button" 
                        @click="setViewMode('list')" 
                        :class="viewMode === 'list' ? 'bg-white text-pink-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'" 
                        class="px-3 py-1.5 rounded-lg text-xs flex items-center transition"
                        title="Tampilan List / Tabel Rinci"
                    >
                        <i class="fa-solid fa-list mr-1.5"></i> List Tabel
                    </button>
                </div>

                <!-- IMPORT & BELANJA -->
                @if(auth()->user()->isOwnerOrAdmin())
                    <button type="button" @click="showImportModal = true" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center shrink-0 cursor-pointer" title="Import Master Data Paket via Excel (.xlsx) atau CSV">
                        <i class="fa-solid fa-file-arrow-up mr-1.5 text-emerald-200"></i> Import (.xlsx)
                    </button>
                    <button type="button" @click="openBulkAdjustModal()" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition flex items-center shrink-0 cursor-pointer" title="Atur Jumlah Tagihan (Mingguan & Harian) Serentak">
                        <i class="fa-solid fa-sliders mr-1.5 text-indigo-200"></i> Atur Tagihan Serentak
                    </button>
                @endif

                <a href="{{ route('inventory.planner') }}" class="px-3 py-2 bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold rounded-xl border border-pink-200 transition flex items-center shrink-0">
                    <i class="fa-solid fa-clipboard-list mr-1.5 text-pink-600"></i> Rekap Belanja
                </a>

                <!-- CETAK BROSUR DROPDOWN -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center">
                        <i class="fa-solid fa-print mr-1.5 text-slate-600"></i> Cetak <i class="fa-solid fa-chevron-down ml-1 text-[10px]"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1.5 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-20">
                        <a href="{{ route('catalog.print.parsel') }}" target="_blank" class="block px-3.5 py-2 text-slate-700 hover:bg-pink-50 hover:text-pink-700 font-semibold">
                            <i class="fa-solid fa-gift mr-1.5 text-pink-600"></i> Brosur Parsel (Grid Foto)
                        </a>
                        <a href="{{ route('catalog.print.tabungan') }}" target="_blank" class="block px-3.5 py-2 text-slate-700 hover:bg-amber-50 hover:text-amber-700 font-semibold">
                            <i class="fa-solid fa-coins mr-1.5 text-amber-500"></i> Brosur Tabungan + Hadiah
                        </a>
                        <a href="{{ route('catalog.print.ecer') }}" target="_blank" class="block px-3.5 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-semibold">
                            <i class="fa-solid fa-cubes mr-1.5 text-blue-500"></i> Brosur Paket Eceran Bebas
                        </a>
                    </div>
                </div>

                <!-- TAMBAH PAKET BARU -->
                <a href="{{ route('packages.create') }}" class="px-3.5 py-2 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white font-bold rounded-xl shadow-xs transition flex items-center shrink-0">
                    <i class="fa-solid fa-plus mr-1.5"></i> Buat Paket Baru
                </a>
            </div>
        </div>

        <!-- SEARCH INDICATOR BANNER -->
        <div x-show="searchQuery" x-cloak class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <div class="text-slate-600 flex items-center">
                <i class="fa-solid fa-filter text-pink-600 mr-1.5"></i>
                <span>Menampilkan hasil pencarian untuk: <strong class="text-pink-900 font-black" x-text="`&ldquo;${searchQuery}&rdquo;`"></strong></span>
            </div>
            <button type="button" @click="searchQuery = ''; if('{{ request('search') }}') window.location.href='{{ route('packages.index') }}';" class="text-pink-600 hover:text-pink-800 font-bold underline text-[11px]">
                Reset Pencarian
            </button>
        </div>
    </div>

    <!-- ================= 1. TAMPILAN LIST (TABEL RINCI) ================= -->
    <div x-show="viewMode === 'list'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px] tracking-wider">
                        @if(auth()->user()->isOwnerOrAdmin())
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="w-4 h-4 rounded border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer" title="Pilih Semua Paket">
                            </th>
                        @endif
                        <th class="py-3 px-4">Paket &amp; Kode</th>
                        <th class="py-3 px-4">Tipe &amp; Skema</th>
                        <th class="py-3 px-4 text-right">Setoran / Cicilan</th>
                        <th class="py-3 px-4 text-right">Nilai Pokok / Cair</th>
                        <th class="py-3 px-4">Rincian Isi / Bonus</th>
                        <th class="py-3 px-4 text-center">Pemesan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($packages as $pkg)
                        @php
                            $searchKey = strtolower($pkg->name . ' ' . $pkg->code . ' ' . $pkg->type . ' ' . $pkg->period_type . ' ' . $pkg->installment_label . ' ' . ($pkg->description ?? '') . ' ' . $pkg->packageItems->map(fn($pi) => $pi->item?->name)->filter()->implode(' '));
                        @endphp
                        <tr 
                            x-show="matchesSearch('{{ addslashes($searchKey) }}')" 
                            :class="selectedPackages.includes({{ $pkg->id }}) ? 'bg-pink-50/70 font-semibold' : '{{ !$pkg->hasSafeThreshold() ? 'bg-amber-50/20' : '' }} hover:bg-pink-50/30'"
                            class="transition group"
                        >
                            @if(auth()->user()->isOwnerOrAdmin())
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <input type="checkbox" :value="{{ $pkg->id }}" x-model="selectedPackages" @change="updateSelectAllState()" class="pkg-item-checkbox w-4 h-4 rounded border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                </td>
                            @endif
                            <!-- 1. PAKET & KODE -->
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200 flex items-center justify-center">
                                        @if($pkg->image_path)
                                            <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid {{ $pkg->isParsel() ? 'fa-gift text-pink-500' : ($pkg->isTabungan() ? 'fa-coins text-amber-500' : 'fa-cubes text-blue-500') }} text-lg"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center space-x-1.5 flex-wrap gap-y-1">
                                            <span class="font-mono text-[10px] font-extrabold px-1.5 py-0.2 rounded {{ $pkg->isParsel() ? 'bg-pink-100 text-pink-800' : ($pkg->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ $pkg->code }}
                                            </span>
                                            <a href="{{ route('packages.show', $pkg) }}" class="font-bold text-slate-900 hover:text-pink-600 text-xs truncate max-w-xs block">
                                                {{ $pkg->name }}
                                            </a>
                                            @if(!$pkg->hasSafeThreshold())
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs" title="Paket ini belum memiliki pengaturan Titik Aman">
                                                    <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i> Belum Ada Titik Aman
                                                </span>
                                            @endif
                                        </div>
                                        @if($pkg->description)
                                            <p class="text-[10px] text-slate-400 truncate max-w-sm mt-0.5">{{ $pkg->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 2. TIPE & SKEMA -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $pkg->isParsel() ? 'bg-pink-100 text-pink-900 border border-pink-200' : ($pkg->isTabungan() ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-900 border border-blue-200') }}">
                                            {{ $pkg->type }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $pkg->isHarian() ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-pink-50 text-pink-900 border border-pink-200' }}">
                                            {{ $pkg->installment_label }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] font-semibold text-slate-500">
                                        {{ $pkg->installment_count }} {{ $pkg->period_label }} ({{ $pkg->installment_count }}x tagih)
                                    </div>
                                </div>
                            </td>

                            <!-- 3. SETORAN / CICILAN -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="font-black text-sm {{ $pkg->isTabungan() ? 'text-amber-900' : 'text-pink-800' }}">
                                    {{ $pkg->formatted_installment }}
                                    <span class="text-[10px] font-bold text-slate-500">/ {{ strtolower($pkg->period_label) }}</span>
                                </div>
                                @if($pkg->hasSafeThreshold())
                                    <div class="text-[9px] text-emerald-700 font-bold mt-0.5 flex items-center justify-end">
                                        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i> Aman: {{ $pkg->formatted_safe_threshold }}
                                    </div>
                                @else
                                    <div class="text-[9px] text-amber-700 font-bold mt-0.5 flex items-center justify-end">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-1"></i> Belum Ada Titik Aman
                                    </div>
                                @endif
                            </td>

                            <!-- 4. NILAI POKOK / CAIR -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($pkg->isTabungan())
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">Cair:</div>
                                    <div class="font-black text-emerald-800 text-sm">{{ $pkg->formatted_base_price }}</div>
                                @else
                                    <div class="font-bold text-slate-800 text-xs">{{ $pkg->formatted_target }}</div>
                                    @if($pkg->admin_fee > 0)
                                        <div class="text-[9px] text-amber-700 font-semibold">+ Admin {{ $pkg->formatted_admin_fee }}</div>
                                    @endif
                                @endif
                            </td>

                            <!-- 5. RINCIAN ISI / BONUS -->
                            <td class="py-3 px-4 max-w-xs">
                                @if($pkg->isParsel())
                                    <div class="text-[11px] font-bold text-slate-700">
                                        <i class="fa-solid fa-boxes-stacked text-emerald-600 mr-1"></i>
                                        {{ $pkg->packageItems->count() }} Macam Barang
                                    </div>
                                    <div class="text-[10px] text-slate-500 truncate mt-0.5">
                                        {{ $pkg->packageItems->take(3)->map(fn($pi) => ($pi->quantity . ' ' . $pi->item?->name))->implode(', ') }}
                                        @if($pkg->packageItems->count() > 3)
                                            <span class="text-slate-400 italic font-medium">+{{ $pkg->packageItems->count() - 3 }} lainnya</span>
                                        @endif
                                    </div>
                                @elseif($pkg->isTabungan())
                                    @if($pkg->bonusItems->isNotEmpty())
                                        <div class="text-[11px] font-bold text-pink-800">
                                            <i class="fa-solid fa-gift text-pink-600 mr-1"></i> Bonus:
                                        </div>
                                        <div class="text-[10px] text-pink-900 truncate mt-0.5 font-medium">
                                            {{ $pkg->bonusItems->map(fn($pi) => ($pi->quantity . ' ' . ($pi->unit ?: 'pcs') . ' ' . ($pi->item?->name ?: 'Bonus')))->implode(', ') }}
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Tanpa bonus barang</span>
                                    @endif
                                @else
                                    <div class="text-[10px] text-blue-700 font-semibold">
                                        <i class="fa-solid fa-cubes mr-1"></i> Pilihan dinamis rakitan bebas
                                    </div>
                                @endif

                                @if($pkg->packagings->isNotEmpty())
                                    <div class="mt-1 text-[9px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.2 rounded border border-pink-200 inline-block">
                                        {{ $pkg->packagings->count() }} Pilihan Kemasan
                                    </div>
                                @endif
                            </td>

                            <!-- 6. PEMESAN -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-1 rounded-full {{ ($pkg->customer_packages_count ?? 0) > 0 ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                                    <i class="fa-solid fa-users mr-1"></i>
                                    {{ $pkg->customer_packages_count ?? 0 }} Orang
                                </span>
                            </td>

                            <!-- 7. AKSI (DENGAN PRESERVE SCROLL) -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">
                                    <a href="{{ route('packages.show', $pkg) }}" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition" title="Lihat Detail Lengkap">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('packages.edit', $pkg) }}" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition" title="Edit Paket">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="inline-block"
                                        onsubmit="rememberScroll(); return confirm('{{ $pkg->customer_packages_count > 0 ? 'Paket ini memiliki ' . $pkg->customer_packages_count . ' konsumen terdaftar. Jika dihapus, konsumen yang memilih ' . ($pkg->isTabungan() ? 'tabungan' : 'paket') . ' ini akan otomatis dibatalkan pilihannya dan wajib memilih paket kembali. Lanjutkan hapus?' : 'Hapus paket ini?' }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus Paket">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isOwnerOrAdmin() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-3 text-slate-300"></i>
                                <div class="font-bold text-slate-700">Belum Ada Master Paket</div>
                                <div class="text-xs mt-1">Buat paket parsel atau tabungan untuk mulai menerima pesanan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= 2. TAMPILAN GRID KARTU (VISUAL) ================= -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packages as $pkg)
            @php
                $searchKey = strtolower($pkg->name . ' ' . $pkg->code . ' ' . $pkg->type . ' ' . $pkg->period_type . ' ' . $pkg->installment_label . ' ' . ($pkg->description ?? '') . ' ' . $pkg->packageItems->map(fn($pi) => $pi->item?->name)->filter()->implode(' '));
            @endphp
            <div 
                x-show="matchesSearch('{{ addslashes($searchKey) }}')" 
                :class="selectedPackages.includes({{ $pkg->id }}) ? 'ring-2 ring-pink-500 border-pink-500 bg-pink-50/20' : '{{ !$pkg->hasSafeThreshold() ? 'border-amber-300' : 'border-slate-200' }} hover:border-pink-500'"
                class="bg-white rounded-2xl border shadow-xs overflow-hidden flex flex-col justify-between hover:shadow-md transition group relative"
            >
                <div>
                    @if($pkg->image_path)
                        <div class="h-44 w-full bg-slate-100 overflow-hidden relative border-b border-slate-100">
                            <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @if(auth()->user()->isOwnerOrAdmin())
                                <div class="absolute top-3 left-3 z-10">
                                    <label class="inline-flex items-center justify-center w-7 h-7 bg-white/95 backdrop-blur-xs rounded-lg shadow-sm border border-slate-200 cursor-pointer hover:bg-white hover:scale-105 transition" @click.stop title="Pilih Paket Ini">
                                        <input type="checkbox" :value="{{ $pkg->id }}" x-model="selectedPackages" @change="updateSelectAllState()" class="pkg-item-checkbox w-4 h-4 rounded border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                    </label>
                                </div>
                            @endif
                            <div class="absolute top-3 {{ auth()->user()->isOwnerOrAdmin() ? 'left-12' : 'left-3' }}">
                                <span class="font-mono text-[10px] font-extrabold px-2.5 py-1 rounded-full shadow-xs {{ $pkg->isParsel() ? 'bg-pink-600 text-white' : ($pkg->isTabungan() ? 'bg-amber-600 text-white' : 'bg-blue-600 text-white') }}">
                                    {{ $pkg->code }} &bull; {{ strtoupper($pkg->type) }} &bull; {{ strtoupper($pkg->period_type ?? 'mingguan') }}
                                </span>
                            </div>
                            <div class="absolute top-3 right-3 flex items-center space-x-1.5">
                                @if(!$pkg->hasSafeThreshold())
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-xs bg-amber-500 text-white border border-amber-400 flex items-center">
                                        <i class="fa-solid fa-triangle-exclamation mr-1 text-white"></i> Belum Ada Titik Aman
                                    </span>
                                @endif
                                <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shadow-xs {{ $pkg->isHarian() ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-white/95 text-pink-900 border border-pink-200' }}">
                                    <i class="fa-solid {{ $pkg->isHarian() ? 'fa-calendar-day text-amber-600' : 'fa-calendar-week text-pink-600' }} mr-1"></i>
                                    {{ $pkg->installment_count }} {{ $pkg->period_label }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="p-5">
                        @if(!$pkg->image_path)
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                    @if(auth()->user()->isOwnerOrAdmin())
                                        <input type="checkbox" :value="{{ $pkg->id }}" x-model="selectedPackages" @change="updateSelectAllState()" class="pkg-item-checkbox w-4 h-4 rounded border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer" title="Pilih Paket Ini">
                                    @endif
                                    <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded {{ $pkg->isParsel() ? 'bg-pink-100 text-pink-800 border border-pink-200' : ($pkg->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $pkg->code }} &bull; {{ strtoupper($pkg->type) }} &bull; {{ strtoupper($pkg->period_type ?? 'mingguan') }}
                                    </span>
                                    @if(!$pkg->hasSafeThreshold())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                            <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i> Belum Ada Titik Aman
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full {{ $pkg->isHarian() ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-pink-100 text-pink-900 border border-pink-200' }}">
                                    <i class="fa-solid {{ $pkg->isHarian() ? 'fa-calendar-day text-amber-600' : 'fa-calendar-week text-pink-600' }} mr-1"></i>
                                    {{ $pkg->installment_count }} {{ $pkg->period_label }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-2 {{ $pkg->image_path ? 'mt-1' : 'mt-3' }}">
                            <h3 class="font-heading font-extrabold text-slate-900 text-base">
                                {{ $pkg->name }}
                            </h3>
                            @if(!$pkg->hasSafeThreshold() && $pkg->image_path)
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                    <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i> Belum Ada Titik Aman
                                </span>
                            @endif
                        </div>

                        @if($pkg->description)
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 font-medium">{{ $pkg->description }}</p>
                        @endif

                        <!-- PRICING & INSTALLMENT BADGE -->
                        <div class="my-4 p-3 rounded-xl {{ $pkg->isTabungan() ? 'bg-amber-50 border border-amber-200' : 'bg-pink-50/60 border border-pink-200' }}">
                            @if($pkg->isTabungan())
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-600 font-medium">Uang Tunai Cair:</span>
                                    <span class="font-black text-emerald-800 text-sm">{{ $pkg->formatted_base_price }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs mt-1.5 pt-1.5 border-t border-amber-200/60">
                                    <span class="font-bold text-slate-800 flex items-center">
                                        <i class="fa-solid fa-coins text-amber-600 mr-1"></i>
                                        Nominal Stor ({{ $pkg->installment_label }}):
                                    </span>
                                    <span class="font-black text-sm text-amber-900">
                                        {{ $pkg->formatted_installment }} <span class="text-[10px] font-bold text-slate-500">/ {{ strtolower($pkg->period_label) }}</span>
                                    </span>
                                </div>
                            @else
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-600 font-medium">Harga Pokok Paket:</span>
                                    <span class="font-extrabold text-slate-800">{{ $pkg->formatted_base_price }}</span>
                                </div>
                                @if($pkg->admin_fee > 0)
                                    <div class="flex justify-between items-center text-[10px] mt-1 text-amber-800">
                                        <span class="font-semibold"><i class="fa-solid fa-eye-slash mr-1"></i> Biaya Admin SPL:</span>
                                        <span class="font-bold">+ {{ $pkg->formatted_admin_fee }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center text-xs mt-1.5 pt-1.5 border-t border-slate-200/60">
                                    <span class="text-slate-700 font-bold">Total Target Tagihan:</span>
                                    <span class="font-black text-slate-900 text-sm">{{ $pkg->formatted_target }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs mt-1.5 pt-1.5 border-t border-slate-200/60">
                                    <span class="font-bold text-slate-800 flex items-center">
                                        <i class="fa-solid {{ $pkg->isHarian() ? 'fa-calendar-day text-amber-600' : 'fa-calendar-week text-pink-600' }} mr-1"></i>
                                        Cicilan {{ $pkg->installment_label }}:
                                    </span>
                                    <span class="font-black text-sm text-pink-800">
                                        {{ $pkg->formatted_installment }} <span class="text-[10px] font-bold text-slate-500">/ {{ strtolower($pkg->period_label) }}</span>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs mt-1.5 pt-1.5 border-t border-slate-200/60">
                                    <span class="font-bold text-slate-700 flex items-center">
                                        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1 text-[11px]"></i>
                                        Titik Aman Full:
                                    </span>
                                    @if($pkg->hasSafeThreshold())
                                        <span class="font-extrabold text-emerald-700 text-xs bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            {{ $pkg->formatted_safe_threshold }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center font-extrabold text-amber-900 text-[11px] bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-md">
                                            <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i>
                                            Belum Ada Titik Aman
                                        </span>
                                    @endif
                                </div>
                            @endif
                            <div class="text-[10px] text-slate-500 font-semibold mt-1 text-right">
                                Dilaksanakan selama {{ $pkg->installment_count }} {{ $pkg->period_label }} ({{ $pkg->installment_count }}x tagih)
                            </div>
                            @if($pkg->start_date && $pkg->end_date)
                                <div class="text-[9px] text-pink-900 font-medium mt-1 text-right flex items-center justify-end">
                                    <i class="fa-solid fa-calendar-days mr-1 text-pink-600"></i>
                                    <span>Acuan: {{ $pkg->formatted_date_range }} ({{ $pkg->calculated_days }} Hari / {{ $pkg->calculated_weeks }} Mgg)</span>
                                </div>
                            @endif
                        </div>

                        <!-- ITEMS LIST PREVIEW -->
                        <div class="text-xs text-slate-600 space-y-1">
                            @if($pkg->isParsel())
                                <div class="font-bold text-slate-800 text-[11px] mb-1">
                                    <i class="fa-solid fa-gift mr-1 text-emerald-600"></i> Isi Barang ({{ $pkg->packageItems->count() }} Macam):
                                </div>
                                <ul class="space-y-0.5 text-[11px] max-h-32 overflow-y-auto pr-1">
                                    @foreach($pkg->packageItems->take(5) as $pi)
                                        <li class="flex items-center text-slate-700">
                                            <span class="text-emerald-500 mr-1.5">&bull;</span>
                                            <span class="font-bold mr-1">{{ $pi->quantity }} {{ $pi->item->base_unit }}</span> {{ $pi->item->name }}
                                        </li>
                                    @endforeach
                                    @if($pkg->packageItems->count() > 5)
                                        <li class="text-[10px] text-slate-400 italic font-medium">+ {{ $pkg->packageItems->count() - 5 }} barang lainnya...</li>
                                    @endif
                                </ul>
                            @elseif($pkg->isTabungan())
                                <div class="font-bold text-slate-800 text-[11px] mb-1">
                                    <i class="fa-solid fa-gift mr-1 text-pink-600"></i> Hadiah Bonus Barang Gratis:
                                </div>
                                <ul class="space-y-0.5 text-[11px]">
                                    @forelse($pkg->bonusItems as $pi)
                                        <li class="flex items-center text-pink-900 font-semibold">
                                            <i class="fa-solid fa-check text-pink-500 mr-1.5 text-[10px]"></i>
                                            <span>{{ $pi->quantity }} {{ $pi->unit ?: ($pi->item?->base_unit ?: 'pcs') }} {{ $pi->item?->name ?: 'Bonus' }}</span>
                                        </li>
                                    @empty
                                        <li class="text-slate-400 italic">Tanpa bonus barang</li>
                                    @endforelse
                                </ul>
                            @else
                                <div class="text-[11px] text-slate-500">
                                    <i class="fa-solid fa-cubes mr-1 text-blue-600"></i> Pilihan dinamis rakitan bebas konsumen dari 50 barang master.
                                </div>
                            @endif
                        </div>

                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center {{ ($pkg->customer_packages_count ?? 0) > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-50 text-slate-500 border border-slate-200' }}">
                                <i class="fa-solid fa-users mr-1 {{ ($pkg->customer_packages_count ?? 0) > 0 ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                {{ $pkg->customer_packages_count ?? 0 }} Konsumen Memesan
                            </span>
                            @if(($pkg->customer_packages_count ?? 0) > 0)
                                <a href="{{ route('inventory.planner', ['search' => $pkg->name]) }}" class="text-[10px] font-bold text-pink-600 hover:text-pink-800 flex items-center">
                                    Rekap Belanja <i class="fa-solid fa-arrow-right ml-1 text-[8px]"></i>
                                </a>
                            @endif
                        </div>

                        @if($pkg->packagings->isNotEmpty())
                            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-pink-700 bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-md flex items-center">
                                    <i class="fa-solid fa-box-archive mr-1"></i> {{ $pkg->packagings->count() }} Pilihan Kemasan
                                </span>
                                <span class="text-[10px] text-slate-400">Koper / Keranjang</span>
                            </div>
                        @endif

                        <!-- ACTIONS (DENGAN PRESERVE SCROLL) -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <a href="{{ route('packages.show', $pkg) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                                Detail Lengkap &rarr;
                            </a>
                            <div class="space-x-1">
                                <a href="{{ route('packages.edit', $pkg) }}" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="inline-block" 
                                    onsubmit="rememberScroll(); return confirm('{{ $pkg->customer_packages_count > 0 ? 'Paket ini memiliki ' . $pkg->customer_packages_count . ' konsumen terdaftar. Jika dihapus, konsumen yang memilih ' . ($pkg->isTabungan() ? 'tabungan' : 'paket') . ' ini akan otomatis dibatalkan pilihannya dan wajib memilih paket kembali. Lanjutkan hapus?' : 'Hapus paket ini?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                <i class="fa-solid fa-box-open text-4xl mb-3 text-slate-300"></i>
                <div class="font-bold text-slate-700">Belum Ada Master Paket</div>
                <div class="text-xs mt-1">Buat paket parsel atau tabungan untuk mulai menerima pesanan.</div>
            </div>
        @endforelse
    </div>

    <!-- ================= FLOATING BULK ACTION BAR (KHUSUS ADMIN / OWNER) ================= -->
    @if(auth()->user()->isOwnerOrAdmin())
        <div 
            x-show="selectedPackages.length > 0" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-8"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
            x-cloak 
            class="fixed bottom-6 inset-x-0 mx-auto max-w-2xl z-50 px-4"
        >
            <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl p-4 border border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <span class="w-9 h-9 rounded-xl bg-pink-600 flex items-center justify-center font-black text-sm text-white shadow-xs shrink-0">
                        <span x-text="selectedPackages.length"></span>
                    </span>
                    <div>
                        <div class="font-bold text-sm text-white flex items-center">
                            <span>Paket Dipilih</span>
                            <span class="ml-2 text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full border border-slate-700">Aksi Massal Admin</span>
                        </div>
                        <div class="text-[11px] text-slate-300">Siap untuk diatur tagihan atau dihapus massal</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2 shrink-0">
                    <button 
                        type="button" 
                        @click="clearSelection()" 
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-800 transition cursor-pointer"
                    >
                        Batal
                    </button>

                    <!-- TOMBOL ATUR JUMLAH TAGIHAN SERENTAK -->
                    <button 
                        type="button" 
                        @click="openBulkAdjustModalForSelected()"
                        class="px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer"
                        title="Atur Jumlah Tagihan Serentak untuk paket yang dipilih"
                    >
                        <i class="fa-solid fa-sliders mr-1 text-indigo-200"></i>
                        <span>Atur Tagihan</span>
                    </button>
                    
                    <form 
                        action="{{ route('packages.bulk-destroy') }}" 
                        method="POST" 
                        onsubmit="rememberScroll(); return confirm('PERINGATAN HAPUS SEKALIGUS:\n\nAnda yakin ingin menghapus ' + selectedPackages.length + ' paket yang dipilih sekaligus?\n\nJika ada konsumen yang sudah terdaftar, status pemilihan mereka akan dibatalkan otomatis dan saldo tabungan/angsuran mereka tetap aman tersimpan.\n\nLanjutkan penghapusan massal?');"
                    >
                        @csrf
                        <input type="hidden" name="package_ids" :value="selectedPackages.join(',')">
                        <button 
                            type="submit" 
                            class="px-3.5 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer"
                        >
                            <i class="fa-solid fa-trash-can mr-1 text-red-200"></i>
                            <span>Hapus (<span x-text="selectedPackages.length"></span>)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ================= MODAL ATUR JUMLAH TAGIHAN SERENTAK (BULK ADJUST) ================= -->
    <div x-show="showBulkAdjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-2xl w-full flex flex-col max-h-[92vh] text-xs overflow-hidden" @click.away="showBulkAdjustModal = false">
            <!-- MODAL HEADER (STICKY) -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-white shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold shadow-2xs shrink-0">
                        <i class="fa-solid fa-sliders text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-sm text-slate-900">Atur Jumlah Tagihan Serentak</h3>
                        <p class="text-[10px] text-slate-500">Sesuaikan kali tagih mingguan atau harian untuk paket terpilih secara cepat & serentak</p>
                    </div>
                </div>
                <button type="button" @click="showBulkAdjustModal = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- FORM ATUR SERENTAK (FLEX WITH SCROLLABLE BODY & STICKY FOOTER) -->
            <form action="{{ route('packages.bulk-adjust-installments') }}" method="POST" onsubmit="rememberScroll();" class="flex flex-col flex-1 overflow-hidden min-h-0">
                @csrf
                <input type="hidden" name="package_ids" :value="selectedPackages.join(',')">

                <!-- SCROLLABLE BODY -->
                <div class="p-4 sm:p-5 overflow-y-auto space-y-3.5 flex-1">
                    <!-- 1. PILIH TARGET PAKET YANG DIATUR -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1 flex items-center justify-between text-[11px]">
                            <span>1. Pilih Lingkup Paket yang Akan Diatur *</span>
                            <span class="text-[10px] text-indigo-600 font-extrabold" x-text="getAffectedPackages().length + ' Paket Terpilih'"></span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                            <!-- OPSI: PAKET DIPILIH DI TABEL -->
                            <label 
                                :class="bulkAdjustScope === 'selected' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 ring-1 ring-indigo-500' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                class="p-2 rounded-xl border flex flex-col justify-between cursor-pointer transition relative"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[11px]">Paket Dipilih</span>
                                    <input type="radio" name="target_scope" value="selected" x-model="bulkAdjustScope" class="text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-indigo-700" x-text="selectedPackages.length"></span> paket dari tabel
                                </div>
                            </label>

                            <!-- OPSI: SEMUA MINGGUAN -->
                            <label 
                                :class="bulkAdjustScope === 'all_mingguan' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 ring-1 ring-indigo-500' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                class="p-2 rounded-xl border flex flex-col justify-between cursor-pointer transition relative"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[11px]">Semua Mingguan</span>
                                    <input type="radio" name="target_scope" value="all_mingguan" x-model="bulkAdjustScope" class="text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-indigo-700" x-text="allPackages.filter(p => p.period_type === 'mingguan').length"></span> paket mingguan
                                </div>
                            </label>

                            <!-- OPSI: SEMUA HARIAN -->
                            <label 
                                :class="bulkAdjustScope === 'all_harian' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 ring-1 ring-indigo-500' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                class="p-2 rounded-xl border flex flex-col justify-between cursor-pointer transition relative"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[11px]">Semua Harian</span>
                                    <input type="radio" name="target_scope" value="all_harian" x-model="bulkAdjustScope" class="text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-indigo-700" x-text="allPackages.filter(p => p.period_type === 'harian').length"></span> paket harian
                                </div>
                            </label>

                            <!-- OPSI: SEMUA PARSEL -->
                            <label 
                                :class="bulkAdjustScope === 'all_parsel' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 ring-1 ring-indigo-500' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                class="p-2 rounded-xl border flex flex-col justify-between cursor-pointer transition relative"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[11px]">Semua Parsel</span>
                                    <input type="radio" name="target_scope" value="all_parsel" x-model="bulkAdjustScope" class="text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-indigo-700" x-text="allPackages.filter(p => p.type === 'parsel').length"></span> paket barang
                                </div>
                            </label>

                            <!-- OPSI: SEMUA TABUNGAN -->
                            <label 
                                :class="bulkAdjustScope === 'all_tabungan' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 ring-1 ring-indigo-500' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                class="p-2 rounded-xl border flex flex-col justify-between cursor-pointer transition relative"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[11px]">Semua Tabungan</span>
                                    <input type="radio" name="target_scope" value="all_tabungan" x-model="bulkAdjustScope" class="text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-indigo-700" x-text="allPackages.filter(p => p.type === 'tabungan').length"></span> paket tabungan
                                </div>
                            </label>

                            <!-- OPSI: SEMUA PAKET -->
                            <label 
                                :class="bulkAdjustScope === 'all' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 ring-1 ring-indigo-500' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                class="p-2 rounded-xl border flex flex-col justify-between cursor-pointer transition relative"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[11px]">Semua Paket SPL</span>
                                    <input type="radio" name="target_scope" value="all" x-model="bulkAdjustScope" class="text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Total <span class="font-bold text-indigo-700" x-text="allPackages.length"></span> paket
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. INPUT JUMLAH TAGIHAN BARU & QUICK PRESETS -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <label class="block font-bold text-slate-800 text-[11px]">2. Masukkan Jumlah Tagihan Baru (Kali Tagih) *</label>
                        <div class="flex items-center gap-2.5">
                            <div class="relative w-36 shrink-0">
                                <input 
                                    type="number" 
                                    name="new_installment_count" 
                                    x-model.number="bulkAdjustCount" 
                                    min="1" 
                                    max="500" 
                                    required 
                                    class="w-full pl-3 pr-12 py-1.5 bg-white border border-slate-300 rounded-lg font-black text-base text-indigo-900 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 text-center"
                                >
                                <span class="absolute inset-y-0 right-2.5 flex items-center text-slate-400 font-bold text-xs pointer-events-none">
                                    Kali
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-500 leading-tight">
                                Durasi disesuaikan menjadi <strong class="text-indigo-800 font-bold" x-text="bulkAdjustCount"></strong> kali tagihan (mingguan / harian).
                            </div>
                        </div>

                        <!-- PRESETS BUTTONS -->
                        <div class="pt-1.5 border-t border-slate-200/80 space-y-1">
                            <div class="flex items-center flex-wrap gap-1">
                                <span class="text-[10px] font-bold text-slate-500 w-20">Mingguan:</span>
                                <template x-for="cnt in [25, 30, 32, 35, 40, 42]" :key="cnt">
                                    <button 
                                        type="button" 
                                        @click="bulkAdjustCount = cnt" 
                                        :class="bulkAdjustCount === cnt ? 'bg-pink-600 text-white font-black shadow-2xs' : 'bg-white hover:bg-pink-50 text-slate-700 border border-slate-200 font-semibold'"
                                        class="px-2 py-0.5 rounded text-[10px] transition cursor-pointer"
                                        x-text="cnt + ' Mgg'"
                                    ></button>
                                </template>
                            </div>
                            <div class="flex items-center flex-wrap gap-1">
                                <span class="text-[10px] font-bold text-slate-500 w-20">Harian:</span>
                                <template x-for="cnt in [40, 60, 100, 120, 150, 300]" :key="cnt">
                                    <button 
                                        type="button" 
                                        @click="bulkAdjustCount = cnt" 
                                        :class="bulkAdjustCount === cnt ? 'bg-amber-600 text-white font-black shadow-2xs' : 'bg-white hover:bg-amber-50 text-slate-700 border border-slate-200 font-semibold'"
                                        class="px-2 py-0.5 rounded text-[10px] transition cursor-pointer"
                                        x-text="cnt + ' Hari'"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- 3. METODE PENYESUAIAN NOMINAL -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1 text-[11px]">3. Metode Penyesuaian Nilai Nominal Paket *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label 
                                :class="bulkAdjustMode === 'keep_target' ? 'bg-indigo-50/80 border-indigo-400 ring-1 ring-indigo-400' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                                class="p-2.5 rounded-xl border cursor-pointer transition flex items-start space-x-2"
                            >
                                <input type="radio" name="adjustment_mode" value="keep_target" x-model="bulkAdjustMode" class="mt-0.5 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                <div>
                                    <div class="font-extrabold text-slate-900 text-[11px] flex items-center">
                                        <span>Target Tetap, Cicilan Dihitung</span>
                                        <span class="ml-1 text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1 py-0.2 rounded">Rekomendasi</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-0.5 leading-tight">
                                        Total target tetap. Nominal cicilan dihitung ulang (<span class="font-mono">Target &divide; Jumlah</span>) &amp; dibulatkan.
                                    </div>
                                </div>
                            </label>

                            <label 
                                :class="bulkAdjustMode === 'keep_installment' ? 'bg-indigo-50/80 border-indigo-400 ring-1 ring-indigo-400' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                                class="p-2.5 rounded-xl border cursor-pointer transition flex items-start space-x-2"
                            >
                                <input type="radio" name="adjustment_mode" value="keep_installment" x-model="bulkAdjustMode" class="mt-0.5 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                <div>
                                    <div class="font-extrabold text-slate-900 text-[11px]">Cicilan Tetap, Target Dihitung</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5 leading-tight">
                                        Nominal cicilan tetap. Total target dihitung ulang (<span class="font-mono">Cicilan &times; Jumlah</span>).
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 4. OPSI SINKRONISASI KONSUMEN -->
                    <div class="p-2.5 bg-amber-50/80 border border-amber-200 rounded-xl">
                        <label class="flex items-start space-x-2 cursor-pointer">
                            <input type="checkbox" name="sync_customers" value="1" x-model="bulkAdjustSyncCustomers" class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5">
                            <div>
                                <div class="font-bold text-amber-950 text-[11px]">
                                    Sinkronkan jadwal tagihan ke konsumen aktif yang mengambil paket ini (Opsional)
                                </div>
                                <div class="text-[10px] text-amber-800 mt-0.5 leading-tight">
                                    Menambah kotak tagihan baru jika jumlah tagihan naik, atau menghapus kotak belum bayar jika berkurang.
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- 5. PRATINJAU REAL-TIME (LIVE SIMULATION) -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-800 text-[11px]">
                                Pratinjau Hasil (<span x-text="getAffectedPackages().length"></span> Paket Terdampak)
                            </span>
                            <span class="text-[10px] text-slate-400 italic">Simulasi 4 paket:</span>
                        </div>

                        <div class="border border-slate-200 rounded-xl overflow-hidden max-h-32 overflow-y-auto">
                            <table class="w-full text-left border-collapse text-[11px]">
                                <thead class="bg-slate-100 text-slate-600 font-bold sticky top-0">
                                    <tr>
                                        <th class="py-1.5 px-3">Kode &amp; Paket</th>
                                        <th class="py-1.5 px-3 text-center">Tagihan</th>
                                        <th class="py-1.5 px-3 text-right">Cicilan per Kali</th>
                                        <th class="py-1.5 px-3 text-right">Total Target</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="pkg in getAffectedPackages().slice(0, 4)" :key="pkg.id">
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-1.5 px-3">
                                                <span class="font-mono font-bold text-slate-800" x-text="pkg.code"></span>
                                                <div class="text-[10px] text-slate-500 truncate max-w-[160px]" x-text="pkg.name"></div>
                                            </td>
                                            <td class="py-1.5 px-3 text-center whitespace-nowrap">
                                                <span class="text-slate-400 line-through text-[10px]" x-text="pkg.installment_count + 'x'"></span>
                                                <i class="fa-solid fa-arrow-right text-[9px] text-slate-400 mx-1"></i>
                                                <span class="font-black text-indigo-700" x-text="bulkAdjustCount + 'x'"></span>
                                            </td>
                                            <td class="py-1.5 px-3 text-right whitespace-nowrap">
                                                <div class="text-[10px] text-slate-400 line-through" x-text="formatRupiah(pkg.installment_amount)"></div>
                                                <div class="font-extrabold text-emerald-700" x-text="formatRupiah(calculateNewInstallment(pkg, bulkAdjustCount, bulkAdjustMode))"></div>
                                            </td>
                                            <td class="py-1.5 px-3 text-right whitespace-nowrap">
                                                <div class="text-[10px] text-slate-400 line-through" x-text="formatRupiah(pkg.target_nominal)"></div>
                                                <div class="font-bold text-slate-800" x-text="formatRupiah(calculateNewTarget(pkg, bulkAdjustCount, bulkAdjustMode))"></div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="getAffectedPackages().length === 0">
                                        <td colspan="4" class="py-4 text-center text-slate-400 text-xs">
                                            Tidak ada paket yang sesuai dengan filter yang dipilih.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL AKSI (STICKY FOOTER) -->
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
                    <button type="button" @click="showBulkAdjustModal = false" class="px-3.5 py-2 rounded-xl text-slate-600 hover:bg-slate-200 font-semibold transition cursor-pointer text-xs">
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        :disabled="getAffectedPackages().length === 0 || !bulkAdjustCount || bulkAdjustCount <= 0"
                        class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 disabled:opacity-50 text-white font-extrabold rounded-xl shadow-md transition flex items-center cursor-pointer text-xs"
                    >
                        <i class="fa-solid fa-check mr-1.5"></i>
                        <span>Terapkan ke <span x-text="getAffectedPackages().length"></span> Paket Serentak</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL IMPORT DATA PAKET -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 text-xs space-y-4" @click.away="showImportModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-file-arrow-up text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-sm text-slate-900">Import Master Data Paket</h3>
                        <p class="text-[10px] text-slate-400">Unggah file Excel (.xlsx) atau CSV untuk input paket massal</p>
                    </div>
                </div>
                <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- STEP 1: DOWNLOAD TEMPLATE RESMI -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                <div class="font-bold text-slate-800 flex items-center justify-between">
                    <span class="flex items-center text-xs">
                        <i class="fa-solid fa-file-excel mr-1.5 text-emerald-600 text-sm"></i> Langkah 1: Unduh Format Template Resmi
                    </span>
                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full border border-emerald-200">5 Varian Lengkap</span>
                </div>
                
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    Template Excel telah dirancang khusus bebas kesalahan dan mencakup contoh nyata untuk <strong>5 variasi paket</strong>:
                </p>

                <!-- GRID 5 VARIAN CONTOH -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[10px]">
                    <div class="p-2 bg-white rounded-lg border border-pink-100 shadow-2xs">
                        <div class="font-bold text-pink-700 flex items-center">
                            <i class="fa-solid fa-gift mr-1"></i> 1. Parsel Mingguan
                        </div>
                        <div class="text-slate-500 mt-0.5">30 Mgg, Rp 25rb/mgg, Target Rp 750rb, isi sembako & kue.</div>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-pink-100 shadow-2xs">
                        <div class="font-bold text-pink-700 flex items-center">
                            <i class="fa-solid fa-gift mr-1"></i> 2. Parsel Harian
                        </div>
                        <div class="text-slate-500 mt-0.5">100 Hari, Rp 7.500/hari, Target Rp 750rb, sembako praktis.</div>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-amber-100 shadow-2xs">
                        <div class="font-bold text-amber-800 flex items-center">
                            <i class="fa-solid fa-coins mr-1 text-amber-600"></i> 3. Tabungan Mingguan
                        </div>
                        <div class="text-slate-500 mt-0.5">30 Mgg, Rp 10rb/mgg, Cair Rp 300rb + hadiah bonus.</div>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-amber-100 shadow-2xs">
                        <div class="font-bold text-amber-800 flex items-center">
                            <i class="fa-solid fa-coins mr-1 text-amber-600"></i> 4. Tabungan Harian
                        </div>
                        <div class="text-slate-500 mt-0.5">100 Hari, Rp 5rb/hari, Cair Rp 500rb + hadiah bonus.</div>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-blue-100 shadow-2xs sm:col-span-2">
                        <div class="font-bold text-blue-700 flex items-center">
                            <i class="fa-solid fa-cubes mr-1 text-blue-500"></i> 5. Paket Eceran (Bebas Rakit)
                        </div>
                        <div class="text-slate-500 mt-0.5">Skema mingguan/harian untuk paket rakitan bebas pilihan konsumen.</div>
                    </div>
                </div>

                <!-- DOWNLOAD BUTTONS -->
                <div class="pt-2 border-t border-slate-200 flex flex-wrap items-center gap-2">
                    <a href="{{ route('packages.import.template', ['format' => 'xlsx', 'type' => 'all']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition inline-flex items-center text-xs shadow-xs">
                        <i class="fa-solid fa-file-excel mr-1.5 text-white"></i> Download Template Lengkap (.xlsx)
                    </a>
                    <a href="{{ route('packages.import.template', ['format' => 'csv', 'type' => 'all']) }}" class="px-3 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold rounded-xl transition inline-flex items-center text-xs">
                        <i class="fa-solid fa-file-csv mr-1.5 text-slate-500"></i> Format CSV
                    </a>
                </div>

                <div class="text-[10px] text-slate-500 flex items-center flex-wrap gap-1.5 pt-1">
                    <span class="font-semibold text-slate-600">Download Template Spesifik:</span>
                    <a href="{{ route('packages.import.template', ['format' => 'xlsx', 'type' => 'parsel']) }}" class="text-pink-600 hover:text-pink-800 font-bold underline">Parsel Saja</a> &bull;
                    <a href="{{ route('packages.import.template', ['format' => 'xlsx', 'type' => 'tabungan']) }}" class="text-amber-600 hover:text-amber-800 font-bold underline">Tabungan Saja</a> &bull;
                    <a href="{{ route('packages.import.template', ['format' => 'xlsx', 'type' => 'ecer']) }}" class="text-blue-600 hover:text-blue-800 font-bold underline">Eceran Saja</a>
                </div>
            </div>

            <!-- STEP 2: FORM UPLOAD FILE -->
            <form action="{{ route('packages.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Langkah 2: Pilih File yang Sudah Diisi *</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-pink-600 file:text-white hover:file:bg-pink-700 cursor-pointer">
                    <span class="text-[10px] text-slate-400 mt-1 block">Format didukung: Microsoft Excel (.xlsx), .xls, atau .csv (Maks. 10 MB)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Jika Kode Paket Sudah Ada di Sistem:</label>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="duplicate_action" value="update" checked class="text-pink-600 focus:ring-pink-500">
                            <div>
                                <div class="font-bold text-slate-800 text-[11px]">Perbarui Data Paket (Update)</div>
                                <div class="text-[10px] text-slate-400">Nama, nominal angsuran, target nominal, dan deskripsi akan diperbarui</div>
                            </div>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="duplicate_action" value="skip" class="text-pink-600 focus:ring-pink-500">
                            <div>
                                <div class="font-bold text-slate-800 text-[11px]">Lewati Paket Tersebut (Skip)</div>
                                <div class="text-[10px] text-slate-400">Pertahankan data lama dan hanya buat paket baru yang kodenya belum terdaftar</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                        <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Mulai Import Paket
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- SCRIPT SCROLL RESTORATION SAAT ACTION HAPUS / EDIT / SUBMIT -->
<script>
    function rememberScroll() {
        sessionStorage.setItem('spl_packages_scroll', window.scrollY);
    }

    // Auto restore scroll position if saved
    (function() {
        const savedY = sessionStorage.getItem('spl_packages_scroll');
        if (savedY !== null) {
            sessionStorage.removeItem('spl_packages_scroll');
            const targetY = parseInt(savedY, 10);
            if (!isNaN(targetY)) {
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
                const restore = function() {
                    window.scrollTo({ top: targetY, behavior: 'instant' });
                };
                restore();
                requestAnimationFrame(restore);
                setTimeout(restore, 50);
                setTimeout(restore, 150);
                setTimeout(restore, 300);
            }
        }
    })();
</script>
@endsection
