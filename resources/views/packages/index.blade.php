@extends('layouts.app')

@section('title', 'Master Paket Parsel & Tabungan')
@section('page_title', 'Master Paket Parsel, Tabungan & Ecer')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        {{ $packages->count() }} Paket Tersedia
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ showImportModal: false }">

    <!-- TOP BAR FILTER & ADD BUTTON -->
    <div class="bg-white rounded-2xl border border-pink-100 shadow-xs p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
        <form action="{{ route('packages.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-bold focus:border-pink-500">
                <option value="">Semua Jenis Paket</option>
                <option value="parsel" {{ request('type') === 'parsel' ? 'selected' : '' }}>Paket Parsel (Barang)</option>
                <option value="tabungan" {{ request('type') === 'tabungan' ? 'selected' : '' }}>Paket Tabungan (Uang + Bonus)</option>
                <option value="ecer" {{ request('type') === 'ecer' ? 'selected' : '' }}>Paket Eceran (Bebas Rakit)</option>
            </select>

            <select name="period_type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-bold focus:border-pink-500">
                <option value="">Semua Skema (Mingguan & Harian)</option>
                <option value="mingguan" {{ request('period_type') === 'mingguan' ? 'selected' : '' }}>Skema Mingguan</option>
                <option value="harian" {{ request('period_type') === 'harian' ? 'selected' : '' }}>Skema Harian</option>
            </select>

            @if(request()->filled('type') || request()->filled('period_type'))
                <a href="{{ route('packages.index') }}" class="px-3 py-2 text-slate-500 hover:text-pink-600 font-bold">
                    <i class="fa-solid fa-xmark mr-1"></i> Reset Filter
                </a>
            @endif
        </form>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showImportModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center shrink-0" title="Import Master Data Paket via Excel (.xlsx) atau CSV">
                <i class="fa-solid fa-file-arrow-up mr-1.5 text-emerald-200"></i> Import Paket (.xlsx)
            </button>

            <a href="{{ route('inventory.planner') }}" class="px-3.5 py-2 bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold rounded-xl border border-pink-200 transition flex items-center shrink-0">
                <i class="fa-solid fa-clipboard-list mr-1.5 text-pink-600"></i> Rekap Kebutuhan & Belanja
            </a>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center">
                    <i class="fa-solid fa-print mr-1.5 text-slate-600"></i> Cetak Brosur <i class="fa-solid fa-chevron-down ml-1.5 text-[10px]"></i>
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

            <a href="{{ route('packages.create') }}" class="px-4 py-2 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center">
                <i class="fa-solid fa-plus mr-1.5"></i> Buat Paket Baru
            </a>
        </div>
    </div>

    <!-- PACKAGES GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packages as $pkg)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-between hover:border-pink-500 hover:shadow-md transition group">
                <div>
                    @if($pkg->image_path)
                        <div class="h-44 w-full bg-slate-100 overflow-hidden relative border-b border-slate-100">
                            <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute top-3 left-3">
                                <span class="font-mono text-[10px] font-extrabold px-2.5 py-1 rounded-full shadow-xs {{ $pkg->isParsel() ? 'bg-pink-600 text-white' : ($pkg->isTabungan() ? 'bg-amber-600 text-white' : 'bg-blue-600 text-white') }}">
                                    {{ $pkg->code }} &bull; {{ strtoupper($pkg->type) }} &bull; {{ strtoupper($pkg->period_type ?? 'mingguan') }}
                                </span>
                            </div>
                            <div class="absolute top-3 right-3">
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
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded {{ $pkg->isParsel() ? 'bg-pink-100 text-pink-800 border border-pink-200' : ($pkg->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $pkg->code }} &bull; {{ strtoupper($pkg->type) }} &bull; {{ strtoupper($pkg->period_type ?? 'mingguan') }}
                                </span>
                                <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full {{ $pkg->isHarian() ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-pink-100 text-pink-900 border border-pink-200' }}">
                                    <i class="fa-solid {{ $pkg->isHarian() ? 'fa-calendar-day text-amber-600' : 'fa-calendar-week text-pink-600' }} mr-1"></i>
                                    {{ $pkg->installment_count }} {{ $pkg->period_label }}
                                </span>
                            </div>
                        @endif

                        <h3 class="font-heading font-extrabold text-slate-900 text-base {{ $pkg->image_path ? 'mt-1' : 'mt-3' }}">
                            {{ $pkg->name }}
                        </h3>

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
                                        Nominal Stor:
                                    </span>
                                    <span class="font-black text-sm text-amber-900">
                                        {{ $pkg->formatted_installment }} <span class="text-[10px] font-bold text-slate-500">/ minggu</span>
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
                                        <span class="font-medium text-slate-400 text-[11px] italic">
                                            Tanpa Titik Aman
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

                        <!-- ACTIONS -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <a href="{{ route('packages.show', $pkg) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                                Detail Lengkap &rarr;
                            </a>
                            <div class="space-x-1">
                                <a href="{{ route('packages.edit', $pkg) }}" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus paket ini?');">
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
            <div class="p-3.5 bg-pink-50/70 border border-pink-200 rounded-xl space-y-2">
                <div class="font-bold text-pink-900 flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fa-solid fa-circle-info mr-1.5 text-pink-600"></i> Langkah 1: Unduh Format Template Resmi
                    </span>
                </div>
                <p class="text-[11px] text-pink-800 leading-relaxed">
                    Template sudah dilengkapi contoh pengisian untuk jenis <strong>Parsel</strong>, <strong>Tabungan</strong>, dan <strong>Eceran</strong> agar tidak keliru saat mengisi data.
                </p>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('packages.import.template', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-lg transition inline-flex items-center text-[11px] shadow-xs">
                        <i class="fa-solid fa-download mr-1.5"></i> Download Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('packages.import.template', ['format' => 'csv']) }}" class="px-3 py-1.5 bg-white hover:bg-pink-100 text-pink-800 border border-pink-300 font-bold rounded-lg transition inline-flex items-center text-[11px]">
                        <i class="fa-solid fa-file-csv mr-1.5"></i> Template CSV
                    </a>
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
@endsection

