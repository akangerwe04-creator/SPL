@extends('layouts.app')

@section('title', 'Daftarkan Konsumen Baru')
@section('page_title', 'Pendaftaran Konsumen & Pilihan Paket')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20 sm:pb-6" x-data="customerRegistration()">

    <a href="{{ route('customers.index') }}" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Data Konsumen
    </a>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8">
        <div class="pb-4 border-b border-slate-100">
            <h3 class="font-heading font-extrabold text-slate-900 text-base">Formulir Pendaftaran Konsumen</h3>
            <p class="text-xs text-slate-400">Pilih paket yang diambil. Untuk Paket Ecer Dinamis, agen dapat mencentang pilihan barang rakitan langsung di bawah.</p>
        </div>

        <form action="{{ route('customers.store') }}" method="POST" class="mt-6 space-y-6 text-xs">
            @csrf

            <!-- CONSUMER BIODATA -->
            <div class="space-y-4">
                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">1. Data Pribadi Konsumen</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap Konsumen *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Ibu Hj. Rodiah" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 0812-3456-7890" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alamat Lengkap / Blok Rumah</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Contoh: Kp. Cibeet RT 02/03 Desa Paseh" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                    </div>

                    @if($user->isOwner())
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tugaskan ke Agen Lapangan *</label>
                            <select name="agent_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 font-medium text-slate-800">
                                @foreach($agents as $ag)
                                    <option value="{{ $ag->id }}" {{ old('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }} ({{ $ag->phone }})</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Staff Agen Penagih</label>
                            <input type="text" readonly value="{{ $user->name }} (Akun Anda)" class="w-full p-2.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-600 font-bold">
                        </div>
                    @endif
                </div>
            </div>

            <!-- PACKAGE SELECTION -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">2. Pemilihan Paket Parsel / Tabungan</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pilih Paket *</label>
                        <select name="package_id" x-model="selectedPackageId" @change="onPackageChange()" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-sm font-bold text-slate-800">
                            <option value="">-- Pilih Paket Program --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}">
                                    @if($pkg->isEcer() && $pkg->installment_amount <= 0 && $pkg->packageItems->isEmpty())
                                        {{ $pkg->name }} (Eceran Bebas - {{ $pkg->installment_count }} {{ $pkg->period_label }})
                                    @else
                                        {{ $pkg->name }} ({{ $pkg->formatted_installment }}/{{ strtolower($pkg->period_label) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Mulai Program *</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500">
                    </div>
                </div>

                <!-- LIVE SELECTED PACKAGE CARD (KHUSUS PAKET REGULER: PARSEL / TABUNGAN) -->
                <template x-if="selectedPackageInfo && !isEcerPackage">
                    <div class="p-4 rounded-2xl bg-white border-2 border-pink-200 shadow-xs space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-pink-100">
                            <!-- FOTO PAKET & NAMA PAKET -->
                            <div class="flex items-center space-x-3.5">
                                <template x-if="selectedPackageInfo.image_url">
                                    <img :src="selectedPackageInfo.image_url" alt="Foto Paket" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border border-pink-200 shadow-xs shrink-0">
                                </template>
                                <template x-if="!selectedPackageInfo.image_url">
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-pink-50 border border-pink-200 flex flex-col items-center justify-center text-pink-700 shrink-0">
                                        <i class="fa-solid fa-gift text-2xl mb-1"></i>
                                        <span class="text-[9px] font-bold uppercase" x-text="selectedPackageInfo.type"></span>
                                    </div>
                                </template>

                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full"
                                            :class="selectedPackageInfo.type === 'tabungan' ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-pink-100 text-pink-900 border border-pink-200'"
                                            x-text="`Paket ${selectedPackageInfo.type.toUpperCase()}`">
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200"
                                            x-text="`${selectedPackageInfo.count} ${selectedPackageInfo.label} (${selectedPackageInfo.period})`">
                                        </span>
                                    </div>
                                    <h3 class="font-heading font-black text-slate-900 text-base" x-text="selectedPackageInfo.name"></h3>
                                    <p class="text-[11px] text-slate-500 font-medium" x-show="selectedPackageInfo.description" x-text="selectedPackageInfo.description"></p>
                                </div>
                            </div>

                            <!-- CICILAN / SETORAN (CUKUP ANGSURAN SAJA SESUAI ARAHAN OWNER) -->
                            <div class="text-left sm:text-right shrink-0 bg-pink-50/70 p-3 rounded-xl border border-pink-200 sm:border-0 sm:bg-transparent">
                                <template x-if="selectedPackageInfo.type === 'tabungan'">
                                    <div class="mb-2 pb-1.5 border-b border-amber-200 text-left sm:text-right">
                                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Uang Tunai Cair:</div>
                                        <div class="text-lg font-black text-emerald-800" x-text="formatRupiah(selectedPackageInfo.base_price)"></div>
                                    </div>
                                </template>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">
                                    <span x-text="selectedPackageInfo.type === 'tabungan' ? 'Nominal Stor per Minggu:' : 'Cicilan / Setoran:'"></span>
                                </div>
                                <div class="text-xl font-black" :class="selectedPackageInfo.type === 'tabungan' ? 'text-amber-900' : 'text-pink-700'">
                                    <span x-text="formatRupiah(currentInstallment)"></span>
                                    <span class="text-xs font-bold text-slate-500">/ <span x-text="selectedPackageInfo.label.toLowerCase()"></span></span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-medium mt-1">
                                    Dilaksanakan selama <span class="font-bold text-slate-700" x-text="selectedPackageInfo.count"></span> kali tagih (<span x-text="selectedPackageInfo.period"></span>)
                                </div>
                                <template x-if="selectedPackagingPrice > 0">
                                    <div class="mt-1 text-[10px] font-bold text-pink-800 bg-pink-100/80 px-2 py-0.5 rounded border border-pink-200 inline-block">
                                        Termasuk Wadah (+ <span x-text="formatRupiah(selectedPackagingPrice)"></span>)
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- DAFTAR ISI BARANG PAKET (HANYA DAFTAR & JUMLAH, TANPA HARGA SATUAN BARANG SESUAI ARAHAN OWNER) -->
                        <div class="pt-1">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-extrabold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-list-check text-pink-600 mr-1.5"></i>
                                    <span x-text="selectedPackageInfo.type === 'tabungan' ? 'Daftar Hadiah Bonus Barang Tabungan (Gratis):' : 'Daftar Isi Barang Paket:'"></span>
                                    <span class="ml-1 text-[11px] font-normal text-slate-500" x-text="`(${selectedPackageInfo.items.length} macam barang)`"></span>
                                </span>
                                <span class="text-[10px] font-bold text-slate-400 italic">
                                    *Kemasan komplit paket
                                </span>
                            </div>

                            <template x-if="selectedPackageInfo.items.length === 0">
                                <div class="p-3 rounded-lg bg-slate-50 text-slate-400 text-xs italic">
                                    Belum ada daftar barang yang dimasukkan dalam paket ini.
                                </div>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(itm, idx) in selectedPackageInfo.items" :key="idx">
                                    <div class="p-2 rounded-lg bg-pink-50/40 border border-pink-100 flex items-center justify-between text-xs">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500 shrink-0"></span>
                                            <span class="font-bold text-slate-800" x-text="itm.name"></span>
                                            <template x-if="itm.is_bonus">
                                                <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-amber-200 text-amber-900 uppercase">Bonus</span>
                                            </template>
                                        </div>
                                        <span class="font-black text-pink-900 text-xs shrink-0 ml-2" x-text="`${itm.quantity} ${itm.base_unit}`"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- 3. PILIHAN KEMASAN / WADAH PAKET (KOPER, KERANJANG, BOX HAMPERS) -->
                <template x-if="selectedPackageInfo && selectedPackageInfo.type !== 'tabungan' && selectedPackageInfo.packagings && selectedPackageInfo.packagings.length > 0">
                    <div class="p-4 sm:p-5 rounded-2xl bg-white border-2 border-pink-200 shadow-xs space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-pink-100">
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center">
                                    <i class="fa-solid fa-box-archive text-pink-600 mr-1.5"></i>
                                    Pilih Kemasan / Wadah Paket (Koper, Keranjang, Box Hampers)
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Konsumen dapat memilih kemasan yang diminati. Harga kemasan akan otomatis ditambahkan ke total target lalu dibagi jumlah periode cicilan.
                                </p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-200">
                                Opsi Pilihan
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-1">
                            <!-- Opsi Standar (Tanpa Kemasan Tambahan) -->
                            <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3"
                                   :class="selectedPackagingId === '' ? 'border-pink-600 bg-pink-50/70 text-slate-900 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50 text-slate-600'">
                                <input type="radio" name="packaging_id" value="" x-model="selectedPackagingId" @change="onPackagingSelect(null)" class="text-pink-600 mt-1">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Kemasan Standar (Dus)</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Wadah dus karton standar tanpa biaya tambahan.</div>
                                    <div class="text-[11px] font-black text-slate-700 mt-1.5">+ Rp 0</div>
                                </div>
                            </label>

                            <!-- Pilihan Kemasan Khusus Yang Ditawarkan Paket -->
                            <template x-for="pkg in selectedPackageInfo.packagings" :key="pkg.id">
                                <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3 relative group"
                                       :class="selectedPackagingId === String(pkg.id) ? 'border-pink-600 bg-pink-50/70 text-slate-900 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-600'">
                                    <input type="radio" name="packaging_id" :value="pkg.id" x-model="selectedPackagingId" @change="onPackagingSelect(pkg)" class="text-pink-600 mt-1 shrink-0">
                                    <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center relative cursor-pointer"
                                         @click.stop="openPreviewPhoto(pkg.image_url, pkg.name + ' (' + pkg.code + ')')">
                                        <template x-if="pkg.image_url">
                                            <img :src="pkg.image_url" :alt="pkg.name" class="w-full h-full object-cover group-hover:scale-105 transition">
                                        </template>
                                        <template x-if="!pkg.image_url">
                                            <i class="fa-solid fa-box-archive text-slate-300 text-xl"></i>
                                        </template>
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition text-xs">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-[9px] font-bold text-pink-700 bg-pink-100 px-1 py-0.2 rounded" x-text="pkg.code"></span>
                                            <template x-if="pkg.is_default">
                                                <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200">Saran</span>
                                            </template>
                                        </div>
                                        <div class="text-xs font-bold text-slate-900 truncate mt-0.5" x-text="pkg.name"></div>
                                        <div class="text-[11px] font-extrabold text-pink-700 mt-1" x-text="`+ ${pkg.formatted_price}`"></div>
                                        <button type="button" @click.stop="openPreviewPhoto(pkg.image_url, pkg.name + ' (' + pkg.code + ')')" class="text-[10px] text-pink-600 hover:underline font-bold mt-1 inline-flex items-center">
                                            <i class="fa-solid fa-image mr-1 text-[9px]"></i> Lihat Foto
                                        </button>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- DYNAMIC ECERAN RAKITAN SECTION (SPECIAL PRD 3.1 & ARAHAN OWNER) -->
                <div x-show="isEcerPackage" x-cloak class="p-4 sm:p-5 rounded-2xl bg-blue-50/60 border-2 border-blue-200 space-y-4">
                    <!-- HEADER PAKET ECERAN -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-blue-200 pb-3">
                        <div class="flex items-center space-x-3">
                            <template x-if="selectedPackageInfo && selectedPackageInfo.image_url">
                                <img :src="selectedPackageInfo.image_url" alt="Foto Eceran" class="w-14 h-14 object-cover rounded-xl border border-blue-300 shadow-xs shrink-0">
                            </template>
                            <template x-if="!selectedPackageInfo || !selectedPackageInfo.image_url">
                                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid fa-cubes"></i>
                                </div>
                            </template>
                            <div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-blue-200 text-blue-900">
                                        Paket Eceran Bebas
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white text-blue-900 border border-blue-200"
                                        x-text="selectedPackageInfo ? `${selectedPackageInfo.count} ${selectedPackageInfo.label}` : '30 Minggu'">
                                    </span>
                                </div>
                                <h4 class="font-heading font-black text-blue-950 text-base mt-0.5" x-text="selectedPackageInfo ? selectedPackageInfo.name : 'Paket Eceran Bebas'"></h4>
                                <p class="text-[11px] text-blue-800 font-medium">Konsumen bebas memilih barang. Nominal setoran per minggu dari setiap barang yang dipilih akan dijumlahkan otomatis.</p>
                            </div>
                        </div>

                        <!-- LIVE CALCULATION BADGE ECERAN -->
                        <div class="p-3.5 bg-white rounded-xl border-2 border-blue-300 text-left sm:text-right shadow-xs shrink-0">
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Setoran Mingguan Konsumen:</div>
                            <div class="text-xl font-heading font-black text-blue-900">
                                <span x-text="formatRupiah(currentInstallment)"></span>
                                <span class="text-xs font-bold text-slate-500">/ <span x-text="selectedPackageInfo ? selectedPackageInfo.label.toLowerCase() : 'minggu'"></span></span>
                            </div>
                            <div class="text-[10px] text-slate-600 font-bold mt-0.5">
                                Total Target: <span class="text-blue-950 font-black" x-text="formatRupiah(currentInstallment * (selectedPackageInfo ? selectedPackageInfo.count : 30))"></span>
                            </div>
                            <div class="text-[10px] text-blue-800 font-medium">
                                (<span x-text="totalSelectedItemsCount"></span> barang dipilih)
                            </div>
                        </div>
                    </div>

                    <!-- 1. DAFTAR PILIHAN BARANG ECERAN YANG DISEDIAKAN OWNER DALAM PAKET INI -->
                    <template x-if="selectedPackageInfo && selectedPackageInfo.items && selectedPackageInfo.items.length > 0">
                        <div class="space-y-3.5">
                            <!-- HEADER & SEARCH / FILTER BAR -->
                            <div class="bg-white p-3 sm:p-4 rounded-xl border border-blue-200 shadow-2xs space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-100">
                                    <div>
                                        <h5 class="font-extrabold text-blue-950 text-xs sm:text-sm flex items-center gap-1.5">
                                            <i class="fa-solid fa-boxes-packing text-blue-600"></i>
                                            Pilihan Barang Eceran (<span x-text="selectedPackageInfo.items.length"></span> Barang)
                                        </h5>
                                        <p class="text-[11px] text-slate-600 mt-0.5">
                                            Nama barang dan cicilan per minggu tampil lengkap tanpa terpotong. Centang dan sesuaikan kuantiti belanjaan konsumen.
                                        </p>
                                    </div>
                                    <div class="text-[11px] text-blue-800 font-bold bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200 shrink-0 self-start sm:self-auto">
                                        <span x-text="totalSelectedItemsCount"></span> macam barang dipilih
                                    </div>
                                </div>

                                <!-- LIVE SEARCH & QUICK FILTER -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" 
                                               x-model="searchEcerItem" 
                                               placeholder="Ketik cari nama barang (cth: beras, minyak, wafer, sirup)..." 
                                               class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 focus:bg-white font-medium text-slate-800 transition">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 sm:top-3 text-slate-400 text-xs"></i>
                                        <button type="button" 
                                                x-show="searchEcerItem" 
                                                @click="searchEcerItem = ''"
                                                class="absolute right-2.5 top-2 sm:top-2.5 text-slate-400 hover:text-slate-600 p-0.5">
                                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                                        </button>
                                    </div>

                                    <!-- FILTER TABS -->
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button type="button"
                                                @click="filterEcerOnlySelected = false"
                                                :class="!filterEcerOnlySelected ? 'bg-blue-600 text-white shadow-xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                                                class="px-2.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1">
                                            <span>Semua</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="!filterEcerOnlySelected ? 'bg-blue-800 text-white' : 'bg-slate-200 text-slate-700'" x-text="selectedPackageInfo.items.length"></span>
                                        </button>
                                        <button type="button"
                                                @click="filterEcerOnlySelected = true"
                                                :class="filterEcerOnlySelected ? 'bg-emerald-600 text-white shadow-xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                                                class="px-2.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1">
                                            <i class="fa-solid fa-cart-shopping text-[10px]"></i>
                                            <span>Terpilih</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filterEcerOnlySelected ? 'bg-emerald-800 text-white' : 'bg-slate-200 text-slate-700'" x-text="totalSelectedItemsCount"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- RESPONSIVE GRID (KOMPATIBEL PC & HP, BEBAS TRUNCATE / POTONG TEKS) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <template x-for="(itm, idx) in filteredEcerItems" :key="itm.id">
                                    <div class="p-3 sm:p-3.5 rounded-2xl border-2 transition-all duration-150 flex flex-col justify-between shadow-2xs select-none relative overflow-hidden"
                                         :class="(customQuantities[itm.id] && customQuantities[itm.id].qty > 0) ? 'border-blue-500 bg-white ring-2 ring-blue-300 shadow-sm' : 'border-slate-200 bg-white hover:border-blue-300'">
                                        
                                        <!-- BAGIAN ATAS: CHECKBOX, NAMA LENGKAP & SATUAN -->
                                        <div class="flex items-start gap-3 cursor-pointer" @click="toggleEcerItem(itm)">
                                            <!-- CHECKBOX -->
                                            <div class="pt-0.5 shrink-0">
                                                <input type="checkbox" 
                                                       :checked="customQuantities[itm.id] && customQuantities[itm.id].qty > 0"
                                                       @click.stop="toggleEcerItem(itm)"
                                                       class="w-5 h-5 text-blue-600 rounded-md border-slate-300 focus:ring-blue-500 cursor-pointer transition">
                                            </div>

                                            <!-- NAMA BARANG (LENGKAP TANPA POTONGAN DI HP & PC) -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 shrink-0" 
                                                          x-text="`${itm.quantity} ${itm.base_unit}`">
                                                    </span>
                                                    <template x-if="itm.is_bonus">
                                                        <span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                                                            BONUS
                                                        </span>
                                                    </template>
                                                    <template x-if="customQuantities[itm.id] && customQuantities[itm.id].qty > 0">
                                                        <span class="text-[10px] font-black px-1.5 py-0.2 rounded bg-emerald-600 text-white shadow-2xs shrink-0 flex items-center gap-1">
                                                            <i class="fa-solid fa-check text-[9px]"></i>
                                                            <span x-text="`${customQuantities[itm.id].qty} Dipilih`"></span>
                                                        </span>
                                                    </template>
                                                </div>

                                                <!-- NAMA BARANG: FULL WORD-BREAK, MULTI-LINE SUPPORTED -->
                                                <h5 class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug break-words whitespace-normal tracking-tight" 
                                                    x-text="itm.name">
                                                </h5>
                                            </div>
                                        </div>

                                        <!-- BAGIAN BAWAH: HARGA CICILAN MINGGUAN & STEPPER TOUCH-FRIENDLY -->
                                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                                            <!-- INFO CICILAN & SUBTOTAL -->
                                            <div class="min-w-0">
                                                <div class="inline-flex items-center gap-1 text-xs sm:text-sm font-black text-blue-900 bg-blue-50 px-2 py-1 rounded-lg border border-blue-200">
                                                    <span x-text="formatRupiah(itm.installment_amount)"></span>
                                                    <span class="text-[10px] font-bold text-slate-500">/ mgg</span>
                                                </div>

                                                <template x-if="customQuantities[itm.id] && customQuantities[itm.id].qty > 1">
                                                    <div class="text-[10px] text-slate-600 font-bold mt-1">
                                                        Subtotal: <span class="text-blue-950 font-black" x-text="formatRupiah(customQuantities[itm.id].qty * itm.installment_amount)"></span>
                                                        <span class="text-[9px] font-normal text-slate-400">/ mgg</span>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- STEPPER TOUCH FRIENDLY (MUDAH DISENTUH DI HP) -->
                                            <div class="flex items-center space-x-1 shrink-0 bg-slate-100/90 p-1 rounded-xl border border-slate-200 shadow-2xs">
                                                <button type="button" 
                                                        @click.stop="decrementEcerItem(itm)" 
                                                        class="w-8 h-8 rounded-lg bg-white hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-black text-base flex items-center justify-center border border-slate-200 transition shadow-2xs"
                                                        title="Kurangi kuantiti">
                                                    -
                                                </button>
                                                <input type="number" 
                                                       :name="`custom_items[${itm.id}]`"
                                                       min="0" 
                                                       :value="customQuantities[itm.id] ? customQuantities[itm.id].qty : 0"
                                                       @input.stop="updateEcerItemQty(itm, $event.target.value)"
                                                       class="w-11 h-8 text-center bg-white border border-slate-200 rounded-lg font-black text-blue-900 text-xs sm:text-sm focus:outline-none focus:border-blue-500 shadow-2xs">
                                                <button type="button" 
                                                        @click.stop="incrementEcerItem(itm)" 
                                                        class="w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black text-base flex items-center justify-center transition shadow-2xs"
                                                        title="Tambah kuantiti">
                                                    +
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>

                            <!-- EMPTY STATE FILTER -->
                            <template x-if="filteredEcerItems.length === 0">
                                <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 space-y-2.5">
                                    <i class="fa-solid fa-box-open text-3xl text-slate-300"></i>
                                    <p class="text-xs font-semibold">Tidak ada barang eceran yang cocok dengan filter pencarian.</p>
                                    <button type="button" 
                                            @click="searchEcerItem = ''; filterEcerOnlySelected = false" 
                                            class="px-3.5 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold rounded-lg transition inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-rotate-left"></i> Reset Filter Pencarian
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- 2. FALLBACK MASTER ITEMS (JIKA OWNER TIDAK MENDAFTARKAN BARANG SPESIFIK DALAM PAKET ECER INI) -->
                    <template x-if="selectedPackageInfo && (!selectedPackageInfo.items || selectedPackageInfo.items.length === 0)">
                        <div class="space-y-3">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <div class="relative w-full">
                                        <input type="text" x-model="ecerSearch" placeholder="Cari nama barang eceran (biskuit, sirup, minyak, beras)..." class="w-full pl-8 pr-3 py-2 text-xs bg-white border border-blue-200 rounded-lg focus:outline-none focus:border-blue-500 font-medium">
                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-1">
                                @foreach($items as $item)
                                    <div 
                                        x-show="!ecerSearch || '{{ strtolower($item->name) }}'.includes(ecerSearch.toLowerCase()) || '{{ strtolower($item->category ?? '') }}'.includes(ecerSearch.toLowerCase())"
                                        class="p-3 sm:p-3.5 rounded-2xl border-2 bg-white transition-all flex flex-col justify-between text-xs shadow-2xs gap-2"
                                        :class="(customQuantities[{{ $item->id }}] && customQuantities[{{ $item->id }}].qty > 0) ? 'border-blue-500 bg-blue-50/40 ring-2 ring-blue-300 shadow-sm' : 'border-slate-200 hover:border-blue-300'"
                                    >
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-900 leading-snug break-words whitespace-normal text-xs sm:text-sm tracking-tight">{{ $item->name }}</div>
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                <span class="text-[10px] font-bold text-slate-500">Harga Ecer:</span>
                                                <span class="font-black text-pink-700 text-xs sm:text-sm">
                                                    {{ $item->formatted_price }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium">/ {{ $item->base_unit }}</span>
                                                @if($item->isUang())
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-extrabold border border-emerald-200">
                                                        <i class="fa-solid fa-money-bill-wave mr-0.5"></i> Uang Tunai
                                                    </span>
                                                @elseif($item->category)
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">{{ $item->category }}</span>
                                                @endif
                                            </div>
                                            <template x-if="customQuantities[{{ $item->id }}] && customQuantities[{{ $item->id }}].qty > 0">
                                                <div class="text-[10px] font-bold text-blue-800 mt-1">
                                                    Subtotal: <span class="font-black" x-text="formatRupiah(customQuantities[{{ $item->id }}].qty * {{ (int)$item->unit_price }})"></span>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                                            <span class="text-[11px] font-bold text-slate-500">Jumlah:</span>
                                            <div class="flex items-center space-x-1 shrink-0 bg-slate-100/90 p-1 rounded-xl border border-slate-200 shadow-2xs">
                                                <button type="button" @click="decrementQty({{ $item->id }}, {{ (int)$item->unit_price }})" class="w-8 h-8 rounded-lg bg-white hover:bg-slate-200 text-slate-700 font-black text-base flex items-center justify-center border border-slate-200 transition shadow-2xs">
                                                    -
                                                </button>
                                                <input type="number" 
                                                    name="custom_items[{{ $item->id }}]"
                                                    min="0" 
                                                    :value="customQuantities[{{ $item->id }}] ? customQuantities[{{ $item->id }}].qty : 0"
                                                    @input="updateEcerCalculation({{ $item->id }}, {{ (int)$item->unit_price }}, $event.target.value)"
                                                    class="w-11 h-8 text-center bg-white border border-slate-200 rounded-lg font-black text-blue-900 text-xs sm:text-sm focus:outline-none focus:border-blue-500 shadow-2xs">
                                                <button type="button" @click="incrementQty({{ $item->id }}, {{ (int)$item->unit_price }})" class="w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-black text-base flex items-center justify-center transition shadow-2xs">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Misal: Patokan rumah samping masjid Al-Ikhlas..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500"></textarea>
                </div>
            </div>

            <!-- SUBMIT -->
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200">
                <a href="{{ route('customers.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                    Daftarkan Konsumen & Terbitkan Lembar Ceklis
                </button>
            </div>
        </form>
    </div>

    <!-- FLOATING MOBILE BOTTOM BAR UNTUK AGEN DI HP -->
    <div x-show="isEcerPackage && totalSelectedItemsCount > 0" 
         x-cloak 
         class="fixed bottom-0 inset-x-0 sm:hidden z-40 bg-white/95 backdrop-blur-md border-t-2 border-blue-500 shadow-[0_-4px_25px_rgba(0,0,0,0.12)] p-3 px-4">
        <div class="flex items-center justify-between gap-3 max-w-lg mx-auto">
            <div>
                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span x-text="totalSelectedItemsCount"></span> Barang Terpilih
                </div>
                <div class="text-base font-heading font-black text-blue-900 leading-tight">
                    <span x-text="formatRupiah(currentInstallment)"></span>
                    <span class="text-xs font-bold text-slate-500">/ mgg</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="filterEcerOnlySelected = !filterEcerOnlySelected"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="filterEcerOnlySelected ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-slate-100 text-slate-700 border-slate-200'">
                    <i class="fa-solid fa-list-check text-xs"></i>
                    <span x-text="filterEcerOnlySelected ? 'Lihat Semua' : 'Cek Pilihan'"></span>
                </button>
                <button type="button" 
                        @click="document.querySelector('button[type=submit]').scrollIntoView({behavior: 'smooth'})"
                        class="px-3.5 py-2 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center gap-1.5">
                    <span>Lanjut</span>
                    <i class="fa-solid fa-arrow-down text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL ZOOM FOTO KEMASAN / PRODUK -->
    <div x-show="previewPhotoSrc" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4" style="display: none;" @click="previewPhotoSrc = null" @keydown.escape.window="previewPhotoSrc = null">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-pink-100" @click.stop>
            <div class="p-4 bg-gradient-to-r from-pink-600 to-rose-600 text-white flex items-center justify-between">
                <span class="font-bold text-sm" x-text="previewPhotoTitle"></span>
                <button type="button" @click="previewPhotoSrc = null" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">✕</button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center min-h-[300px]">
                <img :src="previewPhotoSrc" alt="Foto Kemasan" class="max-h-[450px] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="previewPhotoSrc = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold text-xs">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function customerRegistration() {
    return {
        selectedPackageId: '{{ old('package_id') }}',
        selectedPackageInfo: null,
        isEcerPackage: false,
        selectedPackagingId: '{{ old('packaging_id', '') }}',
        selectedPackagingPrice: 0,
        previewPhotoSrc: null,
        previewPhotoTitle: '',
        searchEcerItem: '',
        filterEcerOnlySelected: false,
        ecerSearch: '',
        customQuantities: (() => {
            const oldItems = @json(old('custom_items', []));
            const res = {};
            if (oldItems && typeof oldItems === 'object') {
                for (const k in oldItems) {
                    const q = parseInt(oldItems[k]) || 0;
                    if (q > 0) res[k] = { qty: q };
                }
            }
            return res;
        })(),
        calculatedTotal: 0,
        calculatedInstallment: 0,
        totalSelectedItemsCount: 0,
        packagesLookup: {
            @foreach($packages as $pkg)
                '{{ $pkg->id }}': {
                    id: '{{ $pkg->id }}',
                    name: @json($pkg->name),
                    type: '{{ $pkg->type }}',
                    period: '{{ $pkg->period_type ?? 'mingguan' }}',
                    label: '{{ $pkg->period_label }}',
                    count: {{ (int)$pkg->installment_count }},
                    installment: {{ (int)$pkg->installment_amount }},
                    formatted_installment: '{{ $pkg->formatted_installment }}',
                    base_price: {{ (int)$pkg->base_price }},
                    formatted_base_price: '{{ $pkg->formatted_base_price }}',
                    target: {{ (int)$pkg->target_nominal }},
                    formatted_target: '{{ $pkg->formatted_target }}',
                    admin_fee: {{ (int)($pkg->admin_fee ?: 0) }},
                    formatted_admin_fee: '{{ $pkg->formatted_admin_fee }}',
                    image_url: '{{ $pkg->image_path ? asset($pkg->image_path) : '' }}',
                    description: @json($pkg->description),
                    packagings: [
                        @foreach($pkg->packagings as $kms)
                            {
                                id: '{{ $kms->id }}',
                                code: @json($kms->code),
                                name: @json($kms->name),
                                price: {{ $kms->pivot->custom_price !== null ? (int)$kms->pivot->custom_price : (int)$kms->price }},
                                formatted_price: 'Rp {{ number_format($kms->pivot->custom_price !== null ? (float)$kms->pivot->custom_price : (float)$kms->price, 0, ',', '.') }}',
                                image_url: @json($kms->image_url),
                                description: @json($kms->description),
                                is_default: {{ $kms->pivot->is_default ? 'true' : 'false' }}
                            },
                        @endforeach
                    ],
                    items: [
                        @foreach($pkg->packageItems as $pi)
                            {
                                id: {{ (int)$pi->id }},
                                package_item_id: {{ (int)$pi->id }},
                                item_id: {{ (int)($pi->item_id ?: $pi->id) }},
                                name: @json($pi->item?->name ?? 'Barang Eceran'),
                                quantity: {{ (int)$pi->quantity }},
                                base_unit: @json($pi->unit ?: ($pi->item?->base_unit ?? 'pcs')),
                                installment_amount: {{ (float)$pi->installment_amount }},
                                formatted_installment: '{{ $pi->formatted_installment }}',
                                is_bonus: {{ $pi->is_bonus ? 'true' : 'false' }}
                            },
                        @endforeach
                    ]
                },
            @endforeach
        },

        init() {
            if (this.selectedPackageId && this.packagesLookup[this.selectedPackageId]) {
                this.onPackageChange();
            }
        },

        openPreviewPhoto(src, title) {
            if (!src) return;
            this.previewPhotoSrc = src;
            this.previewPhotoTitle = title || 'Foto Produk Kemasan';
        },

        onPackagingSelect(pkg) {
            if (!pkg) {
                this.selectedPackagingId = '';
                this.selectedPackagingPrice = 0;
            } else {
                this.selectedPackagingId = String(pkg.id);
                this.selectedPackagingPrice = Number(pkg.price) || 0;
            }
            if (this.isEcerPackage) {
                this.recalculateEcer();
            }
        },

        roundInstallment(val) {
            val = Math.ceil(Number(val) || 0);
            if (val <= 0) return 0;
            const rem = val % 1000;
            if (rem === 0) return val;
            const base = Math.floor(val / 1000) * 1000;
            if (rem <= 500) {
                return base + 500;
            }
            return base + 1000;
        },

        get currentInstallment() {
            if (!this.selectedPackageInfo) return 0;
            if (this.isEcerPackage) {
                return this.calculatedInstallment;
            } else if (this.selectedPackageInfo.type === 'tabungan') {
                return this.selectedPackageInfo.installment;
            } else {
                const count = this.selectedPackageInfo.count || 30;
                const pkgExtra = this.selectedPackagingPrice || 0;
                const target = this.selectedPackageInfo.target || 0;
                return this.roundInstallment((target + pkgExtra) / count);
            }
        },

        get filteredEcerItems() {
            if (!this.selectedPackageInfo || !this.selectedPackageInfo.items) return [];
            return this.selectedPackageInfo.items.filter(itm => {
                if (this.filterEcerOnlySelected) {
                    const q = (this.customQuantities[itm.id] && this.customQuantities[itm.id].qty) ? this.customQuantities[itm.id].qty : 0;
                    if (q <= 0) return false;
                }
                if (!this.searchEcerItem || !this.searchEcerItem.trim()) return true;
                const s = this.searchEcerItem.toLowerCase().trim();
                const name = (itm.name || '').toLowerCase();
                const unit = `${itm.quantity || ''} ${itm.base_unit || ''}`.toLowerCase();
                return name.includes(s) || unit.includes(s);
            });
        },

        onPackageChange() {
            this.searchEcerItem = '';
            this.filterEcerOnlySelected = false;
            if (!this.selectedPackageId || !this.packagesLookup[this.selectedPackageId]) {
                this.selectedPackageInfo = null;
                this.isEcerPackage = false;
                this.selectedPackagingId = '';
                this.selectedPackagingPrice = 0;
                this.customQuantities = {};
                this.recalculateEcer();
                return;
            }

            this.selectedPackageInfo = this.packagesLookup[this.selectedPackageId];
            this.isEcerPackage = (this.selectedPackageInfo.type === 'ecer');
            this.customQuantities = {};

            // Auto-select default packaging if available
            this.selectedPackagingId = '';
            this.selectedPackagingPrice = 0;
            if (this.selectedPackageInfo.packagings && this.selectedPackageInfo.packagings.length > 0) {
                const def = this.selectedPackageInfo.packagings.find(p => p.is_default);
                if (def) {
                    this.selectedPackagingId = String(def.id);
                    this.selectedPackagingPrice = Number(def.price) || 0;
                }
            }

            this.recalculateEcer();
        },

        toggleEcerItem(itm) {
            const current = (this.customQuantities[itm.id] && this.customQuantities[itm.id].qty) ? this.customQuantities[itm.id].qty : 0;
            if (current > 0) {
                this.updateEcerItemQty(itm, 0);
            } else {
                this.updateEcerItemQty(itm, 1);
            }
        },

        incrementEcerItem(itm) {
            const current = (this.customQuantities[itm.id] && this.customQuantities[itm.id].qty) ? this.customQuantities[itm.id].qty : 0;
            this.updateEcerItemQty(itm, current + 1);
        },

        decrementEcerItem(itm) {
            const current = (this.customQuantities[itm.id] && this.customQuantities[itm.id].qty) ? this.customQuantities[itm.id].qty : 0;
            if (current > 0) {
                this.updateEcerItemQty(itm, current - 1);
            }
        },

        updateEcerItemQty(itm, qty) {
            qty = parseInt(qty) || 0;
            if (qty < 0) qty = 0;
            this.customQuantities[itm.id] = {
                qty: qty,
                unitPrice: itm.installment_amount,
                name: itm.name
            };
            this.recalculateEcer();
        },

        incrementQty(itemId, unitPrice) {
            const current = (this.customQuantities[itemId] && this.customQuantities[itemId].qty) ? this.customQuantities[itemId].qty : 0;
            this.updateEcerCalculation(itemId, unitPrice, current + 1);
        },

        decrementQty(itemId, unitPrice) {
            const current = (this.customQuantities[itemId] && this.customQuantities[itemId].qty) ? this.customQuantities[itemId].qty : 0;
            if (current > 0) {
                this.updateEcerCalculation(itemId, unitPrice, current - 1);
            }
        },

        updateEcerCalculation(itemId, unitPrice, qty) {
            qty = parseInt(qty) || 0;
            if (qty < 0) qty = 0;
            this.customQuantities[itemId] = { unitPrice: unitPrice, qty: qty };
            this.recalculateEcer();
        },

        recalculateEcer() {
            let totalWeekly = 0;
            let totalItemsValue = 0;
            let countSelected = 0;

            const duration = (this.selectedPackageInfo && this.selectedPackageInfo.count) ? Number(this.selectedPackageInfo.count) : 30;
            const pkgExtra = this.selectedPackagingPrice || 0;
            const packagingWeekly = duration > 0 ? (pkgExtra / duration) : 0;

            const hasConfiguredItems = this.selectedPackageInfo && this.selectedPackageInfo.items && this.selectedPackageInfo.items.length > 0;

            if (hasConfiguredItems) {
                this.selectedPackageInfo.items.forEach(itm => {
                    const q = (this.customQuantities[itm.id] && this.customQuantities[itm.id].qty) ? Number(this.customQuantities[itm.id].qty) : 0;
                    if (q > 0) {
                        const itemWeekly = Number(itm.installment_amount) || 0;
                        totalWeekly += (itemWeekly * q);
                        countSelected += q;
                    }
                });

                this.totalSelectedItemsCount = countSelected;
                this.calculatedTotal = totalWeekly * duration;

                if (totalWeekly > 0) {
                    if (packagingWeekly > 0) {
                        this.calculatedInstallment = this.roundInstallment(totalWeekly + packagingWeekly);
                    } else {
                        // Nilai nominal storan paket eceran bebas tanpa paksaan pembulatan (misal 2.500 atau 1.100)
                        this.calculatedInstallment = Math.round(totalWeekly);
                    }
                } else {
                    this.calculatedInstallment = 0;
                }
            } else {
                for (const key in this.customQuantities) {
                    const q = this.customQuantities[key].qty || 0;
                    if (q > 0) {
                        totalItemsValue += (this.customQuantities[key].unitPrice * q);
                        countSelected += q;
                    }
                }
                this.calculatedTotal = totalItemsValue;
                this.totalSelectedItemsCount = countSelected;

                const adminFee = (this.selectedPackageInfo && this.selectedPackageInfo.admin_fee) ? Number(this.selectedPackageInfo.admin_fee) : 0;
                if (totalItemsValue > 0) {
                    this.calculatedInstallment = this.roundInstallment((totalItemsValue + adminFee + pkgExtra) / duration);
                } else {
                    this.calculatedInstallment = 0;
                }
            }
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush

