@extends('layouts.app')

@section('title', 'Edit Paket - ' . $package->name)
@section('page_title', 'Edit Master Paket')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="packageEditor()">

    <a href="{{ route('packages.index') }}" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Master Paket
    </a>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8">
        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base">Edit Paket: {{ $package->name }}</h3>
                <p class="text-xs text-slate-400">Perbarui skema tagihan, harga paket, atau barang yang termasuk dalam kemasan.</p>
            </div>
            <span class="inline-block px-3 py-1.5 rounded-lg font-bold uppercase text-xs {{ $package->isParsel() ? 'bg-emerald-100 text-emerald-800' : ($package->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                {{ ucfirst($package->type) }}
            </span>
        </div>

        <form action="{{ route('packages.update', $package) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="{{ $package->type }}">

            <!-- 1. SKEMA PERIODE & JUMLAH DURASI TAGIHAN (MINGGUAN / HARIAN) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-pink-50/40 border border-pink-200 space-y-4">
                <div>
                    <label class="block font-bold text-slate-900 text-sm mb-1">
                        1. Skema & Durasi Periode Tagihan *
                    </label>
                    <p class="text-xs text-slate-600 font-medium">
                        Tentukan rentang tanggal program sebagai acuan, lalu tentukan apakah paket ini dilaksanakan dengan tagihan <strong>Mingguan</strong> (berapa minggu) atau <strong>Harian</strong> (berapa hari).
                    </p>
                </div>

                <!-- KALKULATOR RENTANG TANGGAL MULAI & BERAKHIR (ACUAN / REFERENSI OWNER) -->
                <div class="p-4 rounded-xl bg-white border border-pink-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-calendar-days text-pink-600 mr-1.5 text-sm"></i>
                            Rentang Tanggal Pelaksanaan (Mulai & Berakhir Ditentukan Owner)
                        </label>
                        <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-900 border border-pink-200">
                            Acuan / Referensi Saja
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium">
                        Masukkan tanggal mulai dan tanggal berakhir program. Sistem otomatis menghitung jumlah hari dan minggu sebagai <strong>bahan acuan/referensi</strong> untuk Anda menentukan periode paket.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 text-[11px] mb-1">Tanggal Mulai Program / Setoran</label>
                            <input type="date" name="start_date" x-model="startDate" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 text-[11px] mb-1">Tanggal Berakhir / Pembagian (Target Lebaran)</label>
                            <input type="date" name="end_date" x-model="endDate" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-pink-500">
                        </div>
                    </div>

                    <!-- HASIL HITUNGAN HARI & MINGGU (REFERENSI ACUAN) -->
                    <div class="p-3.5 rounded-xl bg-pink-50/70 border border-pink-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="space-y-1">
                            <div class="text-[10px] font-bold text-pink-900 uppercase tracking-wider flex items-center">
                                <i class="fa-solid fa-calculator text-pink-600 mr-1.5"></i> Hasil Hitungan Kalender (Acuan Owner):
                            </div>
                            <div class="text-xs text-slate-800 font-medium flex flex-wrap items-center gap-2">
                                <span>Hitungan Hari: <strong class="text-pink-900 font-extrabold text-sm" x-text="`${calculateDateDifference().days} Hari`"></strong></span>
                                <span class="text-slate-300">•</span>
                                <span>Hitungan Minggu: <strong class="text-pink-900 font-extrabold text-sm" x-text="`${calculateDateDifference().weeks} Minggu`"></strong></span>
                                <span x-show="calculateDateDifference().remDays > 0" class="text-[10px] text-slate-500 font-semibold" x-text="`(+${calculateDateDifference().remDays} hari)`"></span>
                            </div>
                            <div class="text-[10px] text-slate-500 italic">
                                *Catatan: Hitungan kalender ini hanya menjadi acuan atau referensi. Periode paket tetap ditentukan oleh Anda pada kolom durasi di bawah.
                            </div>
                        </div>

                        <!-- TOMBOL BANTU OPSIONAL TERAPKAN ACUAN KE DURASI PAKET -->
                        <div class="shrink-0 flex items-center gap-1.5">
                            <button type="button" 
                                @click="setInstallmentCount(calculateDateDifference().weeks > 0 ? calculateDateDifference().weeks : 30)" 
                                x-show="periodType === 'mingguan'" 
                                class="px-3 py-1.5 rounded-lg bg-pink-700 hover:bg-pink-800 text-white font-bold text-[11px] shadow-xs transition flex items-center">
                                <i class="fa-solid fa-arrow-down mr-1"></i> Terapkan Acuan (<span x-text="calculateDateDifference().weeks"></span> Mgg)
                            </button>
                            <button type="button" 
                                @click="setInstallmentCount(calculateDateDifference().days > 0 ? calculateDateDifference().days : 100)" 
                                x-show="periodType === 'harian'" 
                                class="px-3 py-1.5 rounded-lg bg-pink-700 hover:bg-pink-800 text-white font-bold text-[11px] shadow-xs transition flex items-center">
                                <i class="fa-solid fa-arrow-down mr-1"></i> Terapkan Acuan (<span x-text="calculateDateDifference().days"></span> Hari)
                            </button>
                        </div>
                    </div>
                </div>

                @if($package->isEcer())
                    <!-- BANNER PAKET ECERAN: FIKS MODEL MINGGUAN -->
                    <div class="p-4 rounded-xl bg-blue-50 border-2 border-blue-200 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg font-black shadow-xs">
                                <i class="fa-solid fa-calendar-week"></i>
                            </span>
                            <div>
                                <div class="text-sm font-black text-blue-950 flex items-center gap-2">
                                    <span>Model Tagihan: Mingguan (Fiks)</span>
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-blue-200 text-blue-900 uppercase">Terkunci</span>
                                </div>
                                <div class="text-xs text-blue-800 font-medium mt-0.5">
                                    Paket Eceran fiks menggunakan model setoran mingguan (Standar SPL: 30 Minggu).
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="period_type" value="mingguan">
                    </div>
                @else
                    <!-- PILIHAN SKEMA: MINGGUAN VS HARIAN -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3.5 bg-white shadow-xs"
                            :class="periodType === 'mingguan' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-300/50' : 'border-slate-200 hover:border-pink-300'">
                            <input type="radio" name="period_type" value="mingguan" x-model="periodType" @change="onPeriodTypeChange('mingguan')" class="mt-0.5 text-pink-600 focus:ring-pink-400">
                            <div class="flex-1">
                                <div class="text-sm font-bold text-slate-900 flex items-center justify-between">
                                    <span class="flex items-center text-pink-900">
                                        <i class="fa-solid fa-calendar-week mr-2 text-pink-600"></i> Tagihan Mingguan
                                    </span>
                                    <span x-show="periodType === 'mingguan'" class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-pink-100 text-pink-900 border border-pink-200">Aktif</span>
                                </div>
                                <div class="text-xs text-slate-600 font-medium mt-1">Ditagih 1 kali setiap minggu (misal: 25, 30, 35, atau 40 Minggu).</div>
                            </div>
                        </label>

                        <label class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3.5 bg-white shadow-xs"
                            :class="periodType === 'harian' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-300/50' : 'border-slate-200 hover:border-pink-300'">
                            <input type="radio" name="period_type" value="harian" x-model="periodType" @change="onPeriodTypeChange('harian')" class="mt-0.5 text-pink-600 focus:ring-pink-400">
                            <div class="flex-1">
                                <div class="text-sm font-bold text-slate-900 flex items-center justify-between">
                                    <span class="flex items-center text-pink-900">
                                        <i class="fa-solid fa-calendar-day mr-2 text-pink-600"></i> Tagihan Harian
                                    </span>
                                    <span x-show="periodType === 'harian'" class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-pink-100 text-pink-900 border border-pink-200">Aktif</span>
                                </div>
                                <div class="text-xs text-slate-600 font-medium mt-1">Ditagih rutin setiap hari (misal: 60, 90, 100, 150, atau 200 Hari).</div>
                            </div>
                        </label>
                    </div>
                @endif

                <!-- DURATION SPECIFICATION (PRESET PILLS & NUMERIC INPUT) -->
                <div class="p-4 rounded-xl bg-white border border-pink-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <label class="block font-bold text-slate-900 text-xs">
                                <span x-show="periodType === 'mingguan'">Tentukan berapa Minggu tagihan paket ini akan dilaksanakan? *</span>
                                <span x-show="periodType === 'harian'">Tentukan berapa Hari tagihan paket ini akan dilaksanakan? *</span>
                            </label>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Jumlah ini menentukan banyaknya kotak ceklis kartu setoran konsumen sampai paket dinyatakan lunas.
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <span class="text-xs font-bold text-slate-700">Durasi:</span>
                            <div class="relative w-36">
                                <input 
                                    type="number" 
                                    name="installment_count" 
                                    x-model.number="installmentCount" 
                                    @input="calculateInstallment()" 
                                    required 
                                    min="1" 
                                    step="1"
                                    class="w-full pl-3 pr-12 py-2 text-center font-extrabold text-pink-900 bg-pink-50/30 border-2 border-pink-300 rounded-xl text-base focus:border-pink-600 focus:ring-2 focus:ring-pink-200/50"
                                >
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-500 pointer-events-none" x-text="periodType === 'harian' ? 'Hari' : 'Mgg'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- PRESET SHORTCUT BUTTONS -->
                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-slate-500 mr-1">Pilihan Cepat:</span>
                        <template x-if="periodType === 'mingguan'">
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="setInstallmentCount(20)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 20 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">20 Minggu</button>
                                <button type="button" @click="setInstallmentCount(25)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 25 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">25 Minggu</button>
                                <button type="button" @click="setInstallmentCount(30)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 30 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">30 Minggu (Standar SPL)</button>
                                <button type="button" @click="setInstallmentCount(35)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 35 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">35 Minggu</button>
                                <button type="button" @click="setInstallmentCount(40)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 40 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">40 Minggu</button>
                            </div>
                        </template>
                        <template x-if="periodType === 'harian'">
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="setInstallmentCount(60)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 60 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">60 Hari</button>
                                <button type="button" @click="setInstallmentCount(90)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 90 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">90 Hari</button>
                                <button type="button" @click="setInstallmentCount(100)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 100 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">100 Hari (Standar)</button>
                                <button type="button" @click="setInstallmentCount(120)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 120 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">120 Hari</button>
                                <button type="button" @click="setInstallmentCount(150)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 150 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">150 Hari</button>
                                <button type="button" @click="setInstallmentCount(200)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="installmentCount === 200 ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50 text-pink-900 hover:bg-pink-100 border border-pink-200'">200 Hari</button>
                            </div>
                        </template>
                    </div>

                    <!-- SUMMARY HIGHLIGHT BANNER -->
                    <div class="mt-2 p-2.5 rounded-lg bg-pink-100/60 border border-pink-200 text-xs text-pink-950 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <span class="flex items-center font-medium">
                            <i class="fa-solid fa-circle-info mr-2 text-pink-600"></i>
                            <span>Rencana: Tagihan dilaksanakan sebanyak 
                                <strong class="text-pink-900 font-extrabold" x-text="`${installmentCount} kali tagih`"></strong> 
                                (<span x-text="periodType === 'harian' ? 'Harian' : 'Mingguan'"></span>)
                            </span>
                        </span>
                        <span class="font-bold text-[11px] text-pink-900">
                            Kotak Ceklis: <span x-text="periodType === 'harian' ? `H1 s/d H${installmentCount}` : `M1 s/d M${installmentCount}`"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. NAME & CODE -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Nama Paket *</label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Paket *</label>
                    <input type="text" name="code" value="{{ old('code', $package->code) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 font-mono">
                </div>
            </div>

            <!-- 3. DAFTAR ISI BARANG / KOMPOSISI PAKET -->
            @if($package->isParsel())
                <!-- TABEL KHUSUS PAKET PARSEL -->
                <div class="space-y-3 pt-4 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-sm flex items-center">
                                <i class="fa-solid fa-boxes-stacked text-emerald-600 mr-2"></i>
                                <span>Daftar Isi Barang Parsel</span>
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5 font-medium">
                                Owner cukup input <strong>Nama Barang</strong> dan <strong>Qty (Banyaknya)</strong>. Anda dapat memilih dari daftar master atau langsung mengetik nama barang baru.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="addRow('barang')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition flex items-center text-xs shadow-xs">
                                <i class="fa-solid fa-plus mr-1.5"></i> + Tambah Barang
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-2xs">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-emerald-50/70 text-emerald-950 border-emerald-200 font-bold border-b text-[11px] uppercase tracking-wider">
                                    <th class="py-2.5 px-3 w-12 text-center">No</th>
                                    <th class="py-2.5 px-4">Nama Barang Parsel</th>
                                    <th class="py-2.5 px-3 w-32 text-center">Qty / Jumlah</th>
                                    <th class="py-2.5 px-3 w-16 text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(row, idx) in itemsList" :key="idx">
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-4">
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    :name="`items[${idx}][item_name]`" 
                                                    x-model="row.item_name" 
                                                    @input="onItemNameInput(row)"
                                                    list="parselItemsDatalist" 
                                                    placeholder="Ketik atau pilih nama barang (misal: Biskuit Khong Guan, Sirup Marjan)" 
                                                    required 
                                                    class="w-full p-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-emerald-500"
                                                >
                                                <input type="hidden" :name="`items[${idx}][item_id]`" :value="row.item_id">
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <div class="flex items-center justify-center space-x-1">
                                                <input 
                                                    type="number" 
                                                    :name="`items[${idx}][quantity]`" 
                                                    x-model.number="row.quantity" 
                                                    min="1" 
                                                    required 
                                                    class="w-20 p-2 text-center bg-slate-50/50 border border-slate-200 rounded-lg font-extrabold text-slate-900 text-xs focus:outline-none focus:border-emerald-500"
                                                >
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <button type="button" @click="removeRow(idx)" class="p-1.5 text-slate-300 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Hapus baris barang">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="itemsList.length === 0">
                                    <td colspan="4" class="py-6 text-center text-slate-400 italic">
                                        Belum ada barang dimasukkan. Klik <strong>+ Tambah Barang</strong> di atas untuk memasukkan isi paket.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-slate-200 bg-slate-50/60 font-semibold text-slate-600">
                                <tr>
                                    <td colspan="2" class="py-2 px-4 text-xs font-bold text-slate-700">
                                        Total Item Terdaftar: <span class="text-emerald-700 font-extrabold" x-text="itemsList.length"></span> Jenis Barang
                                    </td>
                                    <td class="py-2 px-3 text-center font-extrabold text-slate-900">
                                        <span x-text="itemsList.reduce((acc, r) => acc + (Number(r.quantity) || 0), 0)"></span> Pcs / Paket
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif($package->isEcer())
                <!-- TABEL KHUSUS PAKET ECERAN SESUAI FORM ISIAN OWNER -->
                <div class="space-y-3 pt-4 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-sm flex items-center">
                                <i class="fa-solid fa-cubes text-blue-600 mr-2"></i>
                                <span>Daftar Barang Pilihan Paket Eceran &amp; Nominal Storan</span>
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5 font-medium">
                                Owner menginput daftar barang yang nantinya bisa dipilih sesuai keinginan konsumen. Form isian: <strong>No, Nama Barang Eceran, Qty, Satuan, dan Nominal Storan</strong> (per minggu).
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="addRow('barang')" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition flex items-center text-xs shadow-xs">
                                <i class="fa-solid fa-plus mr-1.5"></i> + Tambah Barang Eceran
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-2xs">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-blue-50/70 text-blue-950 border-blue-200 font-bold border-b text-[11px] uppercase tracking-wider">
                                    <th class="py-2.5 px-3 w-12 text-center">No</th>
                                    <th class="py-2.5 px-4 min-w-[200px]">Nama Barang Eceran</th>
                                    <th class="py-2.5 px-3 w-24 text-center">Qty</th>
                                    <th class="py-2.5 px-3 w-28 text-center">Satuan</th>
                                    <th class="py-2.5 px-4 w-44 text-right">Nominal Storan (Rp/Mgg)</th>
                                    <th class="py-2.5 px-3 w-16 text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(row, idx) in itemsList" :key="idx">
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-400" x-text="idx + 1"></td>
                                        
                                        <!-- NAMA BARANG ECERAN -->
                                        <td class="py-2 px-4">
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    :name="`items[${idx}][item_name]`" 
                                                    x-model="row.item_name" 
                                                    @input="onItemNameInput(row)"
                                                    list="parselItemsDatalist" 
                                                    placeholder="Contoh: Daging Sapi, Minyak Goreng 2L, Daging Ayam" 
                                                    required 
                                                    class="w-full p-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-blue-500"
                                                >
                                                <input type="hidden" :name="`items[${idx}][item_id]`" :value="row.item_id">
                                            </div>
                                        </td>

                                        <!-- QTY -->
                                        <td class="py-2 px-3 text-center">
                                            <input 
                                                type="number" 
                                                :name="`items[${idx}][quantity]`" 
                                                x-model.number="row.quantity" 
                                                min="1" 
                                                required 
                                                class="w-20 p-2 text-center bg-slate-50/50 border border-slate-200 rounded-lg font-extrabold text-slate-900 text-xs focus:outline-none focus:border-blue-500"
                                            >
                                        </td>

                                        <!-- SATUAN -->
                                        <td class="py-2 px-3 text-center">
                                            <input 
                                                type="text" 
                                                :name="`items[${idx}][unit]`" 
                                                x-model="row.unit" 
                                                list="unitDatalist"
                                                placeholder="kg / liter / pcs" 
                                                class="w-24 p-2 text-center bg-slate-50/50 border border-slate-200 rounded-lg font-bold text-slate-800 text-xs focus:outline-none focus:border-blue-500"
                                            >
                                        </td>

                                        <!-- NOMINAL STORAN -->
                                        <td class="py-2 px-4 text-right">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center font-bold text-blue-600 text-[11px] pointer-events-none">Rp</span>
                                                <input 
                                                    type="number" 
                                                    :name="`items[${idx}][installment_amount]`" 
                                                    x-model.number="row.installment_amount" 
                                                    @input="recalculateEcerTotal()"
                                                    min="0"
                                                    step="any"
                                                    placeholder="Contoh: 2500" 
                                                    class="w-full pl-8 pr-2.5 py-2 text-right bg-slate-50/50 border border-slate-200 rounded-lg font-black text-blue-950 font-mono text-xs focus:outline-none focus:border-blue-500"
                                                >
                                            </div>
                                        </td>

                                        <!-- HAPUS -->
                                        <td class="py-2 px-3 text-center">
                                            <button type="button" @click="removeRow(idx)" class="p-1.5 text-slate-300 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Hapus baris barang eceran">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="itemsList.length === 0">
                                    <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                        Belum ada barang eceran dimasukkan. Klik <strong>+ Tambah Barang Eceran</strong> di atas.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-slate-200 bg-slate-50/60 font-semibold text-slate-600">
                                <tr>
                                    <td colspan="4" class="py-2 px-4 text-xs font-bold text-slate-700">
                                        Total Pilihan Tersedia: <span class="text-blue-700 font-extrabold" x-text="itemsList.length"></span> Jenis Barang Eceran
                                    </td>
                                    <td class="py-2 px-4 text-right font-black font-mono text-blue-950 text-xs">
                                        <div class="text-[9px] font-bold text-slate-400 uppercase">Total Jika Semua Dipilih:</div>
                                        <span x-text="formatRupiah(totalEcerItemsInstallment)"></span> / mgg
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fa-solid fa-circle-info text-blue-600 mr-2 text-sm"></i>
                            <span>Nantinya konsumen dapat memilih barang mana saja yang diminati. Sistem otomatis menjumlahkan nominal storan dari barang-barang yang dipilih konsumen.</span>
                        </span>
                        <button type="button" @click="recalculateEcerTotal()" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] shrink-0 ml-2">
                            Terapkan ke Paket
                        </button>
                    </div>

                    <!-- DATALIST SATUAN -->
                    <datalist id="unitDatalist">
                        <option value="kg">kg (Kilogram)</option>
                        <option value="liter">liter</option>
                        <option value="bungkus">bungkus</option>
                        <option value="botol">botol</option>
                        <option value="pcs">pcs</option>
                        <option value="kaleng">kaleng</option>
                        <option value="ekor">ekor</option>
                        <option value="dus">dus</option>
                        <option value="pack">pack</option>
                    </datalist>

                    <!-- DATALIST ACUAN NAMA BARANG -->
                    <datalist id="parselItemsDatalist">
                        @foreach($items as $it)
                            <option value="{{ $it->name }}">{{ $it->category }}</option>
                        @endforeach
                    </datalist>
                </div>
            @elseif($package->isTabungan())
                <!-- FORM KHUSUS EDIT PAKET TABUNGAN SPL -->
                <div class="space-y-4 pt-4 border-t border-slate-200">
                    <div class="bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 text-white p-4 sm:p-5 rounded-2xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center space-x-3.5">
                            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl shrink-0 shadow-inner">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white text-amber-900 mb-1">
                                    Formulir Paket Tabungan SPL
                                </span>
                                <h4 class="text-base font-black tracking-tight leading-snug">
                                    Tabungan SPL (Uang Tunai Cair + Hadiah Bonus)
                                </h4>
                                <p class="text-xs text-amber-100 font-medium">
                                    Cukup tentukan <strong>Nominal Stor</strong>, <strong>Nominal yang Akan Didapat</strong>, serta <strong>1 atau Lebih Barang Bonus</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- INPUT UTAMA: NOMINAL STOR & NOMINAL YANG AKAN DIDAPAT -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- 1. NOMINAL STOR PER MINGGU -->
                        <div class="p-4 bg-white rounded-xl border-2 border-amber-300 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block font-black text-amber-950 text-xs">
                                    <i class="fa-solid fa-coins text-amber-600 mr-1"></i> Nominal Stor per Minggu (Rp) *
                                </label>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 uppercase">
                                    Setoran
                                </span>
                            </div>
                            <input type="number" step="any" min="0" required
                                name="installment_amount"
                                x-model.number="installmentAmount"
                                placeholder="Contoh: 10000 atau 25000"
                                class="w-full p-2.5 bg-amber-50/20 border-2 border-amber-300 rounded-lg focus:outline-none focus:border-amber-600 font-black text-amber-950 font-mono text-base shadow-2xs">
                            
                            <!-- PRESET CEPAT -->
                            <div class="flex items-center gap-1.5 pt-1 flex-wrap">
                                <span class="text-[10px] font-bold text-slate-400">Preset:</span>
                                <button type="button" @click="installmentAmount = 5000" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-900 text-[10px] font-bold border border-amber-200">5rb</button>
                                <button type="button" @click="installmentAmount = 10000" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-900 text-[10px] font-bold border border-amber-200">10rb</button>
                                <button type="button" @click="installmentAmount = 20000" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-900 text-[10px] font-bold border border-amber-200">20rb</button>
                                <button type="button" @click="installmentAmount = 25000" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-900 text-[10px] font-bold border border-amber-200">25rb</button>
                                <button type="button" @click="installmentAmount = 50000" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-900 text-[10px] font-bold border border-amber-200">50rb</button>
                            </div>
                            <div class="text-[11px] text-slate-600 font-medium">
                                Disetor nasabah: <strong class="text-amber-800 font-black" x-text="formatRupiah(installmentAmount)"></strong> / minggu.
                            </div>
                        </div>

                        <!-- 2. NOMINAL YANG AKAN DIDAPAT (UANG CAIR) -->
                        <div class="p-4 bg-white rounded-xl border-2 border-emerald-300 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block font-black text-emerald-950 text-xs">
                                    <i class="fa-solid fa-money-bill-wave text-emerald-600 mr-1"></i> Nominal yang Akan Didapat (Rp) *
                                </label>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 uppercase">
                                    Uang Cair
                                </span>
                            </div>
                            <input type="number" step="any" min="0" required
                                name="target_nominal"
                                x-model.number="targetNominal"
                                @input="basePrice = targetNominal"
                                placeholder="Contoh: 300000 atau 750000"
                                class="w-full p-2.5 bg-emerald-50/20 border-2 border-emerald-300 rounded-lg focus:outline-none focus:border-emerald-600 font-black text-emerald-950 font-mono text-base shadow-2xs">
                            
                            <div class="text-[11px] text-slate-600 font-medium pt-1">
                                Konsumen menerima uang tunai: <strong class="text-emerald-700 font-black" x-text="formatRupiah(targetNominal)"></strong> saat Lebaran.
                            </div>
                        </div>
                    </div>

                    <!-- HIDDEN SINKRONISASI BASE PRICE & ADMIN FEE KHUSUS TABUNGAN -->
                    <input type="hidden" name="base_price" :value="targetNominal">
                    <input type="hidden" name="admin_fee" value="0">
                    <input type="hidden" name="period_type" value="mingguan">

                    <!-- HADIAH BONUS BARANG TABUNGAN (1 ATAU LEBIH BARANG) -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/50 border border-amber-300 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="font-extrabold text-xs text-slate-900 flex items-center">
                                    <i class="fa-solid fa-gift text-pink-600 mr-1.5"></i>
                                    <span>Hadiah Bonus Barang (Bisa diisi 1 atau lebih barang)</span>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium mt-0.5">
                                    Hadiah bonus fisik gratis yang dibawa pulang konsumen untuk tabungan ini.
                                </p>
                            </div>
                            <button type="button" @click="addBonusItem()" class="px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-lg transition flex items-center text-xs shadow-2xs shrink-0 self-start sm:self-auto">
                                <i class="fa-solid fa-plus mr-1"></i> + Tambah Barang Bonus
                            </button>
                        </div>

                        <!-- DAFTAR BARANG BONUS -->
                        <div class="space-y-2">
                            <template x-for="(bonus, bIdx) in itemsList" :key="bIdx">
                                <div class="p-2.5 bg-white rounded-xl border border-pink-200 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                    <div class="flex items-center space-x-2 shrink-0">
                                        <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-700 font-bold text-[10px] flex items-center justify-center shrink-0" x-text="bIdx + 1"></span>
                                        <span class="text-[11px] font-bold text-slate-700">Bonus:</span>
                                    </div>

                                    <!-- PILIH DARI MASTER BARANG ATAU INPUT KETIK LANGSUNG -->
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <select :name="`items[${bIdx}][item_id]`"
                                            x-model="bonus.item_id"
                                            @change="onBonusItemSelect(bonus)"
                                            class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:border-pink-500">
                                            <option value="">-- Pilih dari Master Barang --</option>
                                            @foreach($items->where('category', '!=', 'Uang') as $it)
                                                <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->category }} - {{ $it->base_unit }})</option>
                                            @endforeach
                                        </select>
                                        <input type="text"
                                            :name="`items[${bIdx}][item_name]`"
                                            x-model="bonus.item_name"
                                            placeholder="Atau ketik nama barang bonus baru..."
                                            class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:border-pink-500">
                                    </div>

                                    <!-- BANYAKNYA (QTY) & SATUAN -->
                                    <div class="flex items-center space-x-1.5 shrink-0 justify-end sm:justify-start">
                                        <div class="flex items-center space-x-1">
                                            <span class="text-[10px] text-slate-400">Qty:</span>
                                            <input type="number" min="1"
                                                :name="`items[${bIdx}][quantity]`"
                                                x-model.number="bonus.quantity"
                                                class="w-16 p-2 text-center bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-pink-500" placeholder="1">
                                        </div>
                                        <input type="text"
                                            :name="`items[${bIdx}][unit]`"
                                            x-model="bonus.unit"
                                            class="w-16 p-2 text-center bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:border-pink-500" placeholder="pcs">

                                        <input type="hidden" :name="`items[${bIdx}][is_bonus]`" value="1">

                                        <button type="button" @click="removeBonusItem(bIdx)" class="text-slate-300 hover:text-red-600 p-1.5 transition ml-1" title="Hapus barang bonus ini">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 4. PENENTUAN HARGA PAKET, BIAYA ADMIN & KALKULASI SETORAN -->
            @if($package->isEcer())
                <!-- TAMPILAN KHUSUS PAKET ECERAN -->
                <div class="p-5 rounded-2xl bg-white border-2 border-blue-200 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-blue-100 pb-3">
                        <div>
                            <span class="font-extrabold text-blue-950 text-sm flex items-center">
                                <i class="fa-solid fa-calculator text-blue-600 mr-2 text-base"></i>
                                Kalkulator Setoran & Target Tagihan Eceran
                            </span>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                                Paket Eceran fiks menggunakan model mingguan. Cukup tentukan nominal setoran per minggu dan durasi minggu tagihan.
                            </p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-blue-900 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                            <i class="fa-solid fa-calendar-week text-blue-600 mr-1"></i> Paket Eceran (Mingguan)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <!-- 1. NOMINAL SETORAN PER MINGGU -->
                        <div class="p-4 bg-blue-50/50 rounded-2xl border-2 border-blue-300 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-black text-blue-950 text-xs">
                                        Setoran per Minggu (Rp) *
                                    </label>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-blue-200 text-blue-900 uppercase">
                                        Mingguan
                                    </span>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-blue-700 text-sm pointer-events-none">Rp</span>
                                    <input 
                                        type="number" 
                                        name="installment_amount" 
                                        x-model.number="installmentAmount" 
                                        @input="onEcerInstallmentChange()" 
                                        min="0" 
                                        step="any" 
                                        placeholder="Contoh: 2500 / 10000" 
                                        required
                                        class="w-full pl-10 pr-3 py-2.5 bg-white border-2 border-blue-400 rounded-xl focus:outline-none focus:border-blue-600 font-black text-blue-950 font-mono text-base shadow-xs"
                                    >
                                </div>
                                <span class="text-[10px] text-blue-900/80 font-medium block mt-1.5 leading-snug">
                                    Nominal cicilan bebas yang wajib disetor konsumen setiap minggu (tanpa batasan kelipatan).
                                </span>
                            </div>

                            <!-- PRESET CEPAT SETORAN MINGGUAN -->
                            <div class="mt-2.5 pt-2 border-t border-blue-200/80">
                                <div class="flex flex-wrap items-center gap-1">
                                    <button type="button" @click="setEcerInstallment(2500)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">2.5rb</button>
                                    <button type="button" @click="setEcerInstallment(5000)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">5rb</button>
                                    <button type="button" @click="setEcerInstallment(10000)" class="px-2 py-0.5 rounded bg-blue-600 text-white text-[10px] font-black shadow-xs">10rb</button>
                                    <button type="button" @click="setEcerInstallment(15000)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">15rb</button>
                                    <button type="button" @click="setEcerInstallment(20000)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">20rb</button>
                                    <button type="button" @click="setEcerInstallment(25000)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">25rb</button>
                                </div>
                            </div>
                        </div>

                        <!-- 2. PERIODE TAGIHAN (MODEL MINGGUAN) -->
                        <div class="p-4 bg-amber-50/50 rounded-2xl border-2 border-amber-300 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-black text-amber-950 text-xs">
                                        Periode Tagihan (Fiks Mingguan) *
                                    </label>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-amber-200 text-amber-900 uppercase">
                                        Minggu
                                    </span>
                                </div>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="installment_count" 
                                        x-model.number="installmentCount" 
                                        @input="onEcerInstallmentChange()" 
                                        min="1" 
                                        max="100"
                                        required
                                        class="w-full pl-3 pr-16 py-2.5 bg-white border-2 border-amber-400 rounded-xl focus:outline-none focus:border-amber-600 font-black text-amber-950 font-mono text-base text-center shadow-xs"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center font-bold text-amber-700 text-xs pointer-events-none">Minggu</span>
                                </div>
                                <span class="text-[10px] text-amber-900/80 font-medium block mt-1.5 leading-snug">
                                    Banyaknya minggu setoran sampai lunas.
                                </span>
                            </div>

                            <!-- PRESET MINGGU CEPAT -->
                            <div class="mt-2.5 pt-2 border-t border-amber-200/80">
                                <div class="flex flex-wrap items-center gap-1">
                                    <button type="button" @click="setInstallmentCount(20)" class="px-2 py-0.5 rounded bg-white hover:bg-amber-100 text-amber-900 border border-amber-200 text-[10px] font-bold transition">20 Mgg</button>
                                    <button type="button" @click="setInstallmentCount(25)" class="px-2 py-0.5 rounded bg-white hover:bg-amber-100 text-amber-900 border border-amber-200 text-[10px] font-bold transition">25 Mgg</button>
                                    <button type="button" @click="setInstallmentCount(30)" class="px-2 py-0.5 rounded bg-amber-600 text-white text-[10px] font-black shadow-xs">30 Mgg</button>
                                    <button type="button" @click="setInstallmentCount(35)" class="px-2 py-0.5 rounded bg-white hover:bg-amber-100 text-amber-900 border border-amber-200 text-[10px] font-bold transition">35 Mgg</button>
                                    <button type="button" @click="setInstallmentCount(40)" class="px-2 py-0.5 rounded bg-white hover:bg-amber-100 text-amber-900 border border-amber-200 text-[10px] font-bold transition">40 Mgg</button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. TOTAL TARGET TAGIHAN (DIHITUNG OTOMATIS) -->
                        <div class="p-4 bg-emerald-50/50 rounded-2xl border-2 border-emerald-300 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-black text-emerald-950 text-xs">
                                        Total Target Tagihan Paket
                                    </label>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 uppercase">
                                        Otomatis
                                    </span>
                                </div>
                                <div class="p-2.5 bg-white border-2 border-emerald-400 rounded-xl">
                                    <div class="text-xs font-bold text-slate-500">Total Nilai Tagihan:</div>
                                    <div class="text-lg font-black text-emerald-800 font-mono mt-0.5" x-text="formatRupiah(targetNominal)"></div>
                                </div>
                                <span class="text-[10px] text-emerald-900/80 font-medium block mt-1.5 leading-snug">
                                    Dihitung dari: <span class="font-bold font-mono" x-text="formatRupiah(installmentAmount)"></span> &times; <span class="font-bold" x-text="installmentCount || 1"></span> Minggu.
                                </span>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-emerald-200/80 text-[11px] text-emerald-900 font-bold flex items-center justify-between">
                                <span>Harga Pokok Paket:</span>
                                <span class="font-black font-mono text-emerald-800" x-text="formatRupiah(targetNominal)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- HIDDEN INPUTS KHUSUS ECERAN -->
                    <input type="hidden" name="period_type" value="mingguan">
                    <input type="hidden" name="base_price" :value="targetNominal">
                    <input type="hidden" name="target_nominal" :value="targetNominal">
                    <input type="hidden" name="admin_fee" value="0">

                    <!-- LIVE SUMMARY BANNER ECERAN -->
                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 text-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-lg bg-blue-600 text-white shrink-0"><i class="fa-solid fa-calendar-week"></i></span>
                            <div class="space-y-0.5">
                                <div class="font-extrabold text-slate-900">
                                    Paket Eceran: Total Target <span class="text-blue-700 font-black" x-text="formatRupiah(targetNominal)"></span>
                                </div>
                                <div class="text-[11px] text-slate-600 font-medium">
                                    Model Mingguan: Diangsur rutin selama <strong class="text-blue-900" x-text="`${installmentCount} Minggu`"></strong> (Kotak ceklis M1 s/d M<span x-text="installmentCount"></span>).
                                </div>
                            </div>
                        </div>
                        <div class="text-left sm:text-right shrink-0 bg-white sm:bg-transparent p-2.5 sm:p-0 rounded-lg border sm:border-0 border-blue-200">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Setoran Mingguan</div>
                            <div class="text-base font-black text-blue-800">
                                <span x-text="formatRupiah(installmentAmount)"></span>
                                <span class="text-xs font-bold text-slate-500">/ minggu</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($package->isParsel())
                <!-- 4. PENENTUAN HARGA PAKET & MODEL HARIAN (PARSEL) -->
                <div class="p-5 rounded-2xl bg-white border-2 border-emerald-200 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-emerald-100 pb-3">
                        <div>
                            <span class="font-extrabold text-slate-900 text-sm flex items-center">
                                <i class="fa-solid fa-gift text-emerald-600 mr-2 text-base"></i>
                                Penentuan Harga Paket & Periode Pembayaran Harian
                            </span>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                                Cukup tentukan Harga Paket dan durasi hari tagihan. Nominal cicilan setoran harian dihitung secara otomatis dengan pembulatan SPL.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <!-- 1. HARGA PAKET PARSEL -->
                        <div class="p-4 bg-emerald-50/50 rounded-2xl border-2 border-emerald-300 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-black text-emerald-950 text-xs">
                                        Harga Paket Parsel (Rp) *
                                    </label>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 uppercase">
                                        Total Paket
                                    </span>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-emerald-700 text-sm pointer-events-none">Rp</span>
                                    <input 
                                        type="number" 
                                        name="target_nominal" 
                                        x-model.number="targetNominal" 
                                        @input="onParselPriceChange()" 
                                        min="0" 
                                        step="any"
                                        placeholder="Contoh: 750000" 
                                        required
                                        class="w-full pl-10 pr-3 py-2.5 bg-white border-2 border-emerald-400 rounded-xl focus:outline-none focus:border-emerald-600 font-black text-emerald-950 font-mono text-base shadow-xs"
                                    >
                                </div>
                                <span class="text-[10px] text-emerald-900/80 font-medium block mt-1.5 leading-snug">
                                    Total harga jual paket parsel ke konsumen.
                                </span>
                            </div>

                            <div class="mt-3 pt-2 border-t border-emerald-200/80 flex items-center justify-between text-[11px] text-emerald-900 font-bold">
                                <span>Total Tagihan:</span>
                                <span class="font-black font-mono text-emerald-800" x-text="formatRupiah(targetNominal)"></span>
                            </div>
                        </div>

                        <!-- 2. PERIODE TAGIHAN (MODEL HARIAN) -->
                        <div class="p-4 bg-blue-50/50 rounded-2xl border-2 border-blue-300 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-black text-blue-950 text-xs">
                                        Periode Tagihan (Model Harian) *
                                    </label>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-blue-200 text-blue-900 uppercase">
                                        Harian
                                    </span>
                                </div>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="installment_count" 
                                        x-model.number="installmentCount" 
                                        @input="calculateInstallment()" 
                                        min="1" 
                                        step="1"
                                        required
                                        class="w-full pl-3 pr-14 py-2.5 bg-white border-2 border-blue-400 rounded-xl focus:outline-none focus:border-blue-600 font-black text-blue-950 font-mono text-base text-center shadow-xs"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center font-bold text-blue-700 text-xs pointer-events-none">Hari</span>
                                </div>
                                <span class="text-[10px] text-blue-900/80 font-medium block mt-1.5 leading-snug">
                                    Tagihan dilakukan rutin harian sampai lunas.
                                </span>
                            </div>

                            <!-- PRESET HARI CEPAT -->
                            <div class="mt-2.5 pt-2 border-t border-blue-200/80">
                                <div class="flex flex-wrap items-center gap-1">
                                    <button type="button" @click="setInstallmentCount(60)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">60 Hr</button>
                                    <button type="button" @click="setInstallmentCount(90)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">90 Hr</button>
                                    <button type="button" @click="setInstallmentCount(100)" class="px-2 py-0.5 rounded bg-blue-600 text-white text-[10px] font-black shadow-xs">100 Hr</button>
                                    <button type="button" @click="setInstallmentCount(120)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">120 Hr</button>
                                    <button type="button" @click="setInstallmentCount(150)" class="px-2 py-0.5 rounded bg-white hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-bold transition">150 Hr</button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. SETORAN PER HARI (PEMBULATAN SPL) -->
                        <div class="p-4 bg-pink-50/60 rounded-2xl border-2 border-pink-400 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-black text-pink-950 text-xs">
                                        Setoran per Hari (Konsumen) *
                                    </label>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-pink-600 text-white uppercase">
                                        Auto Bulat SPL
                                    </span>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-pink-700 text-sm pointer-events-none">Rp</span>
                                    <input 
                                        type="number" 
                                        name="installment_amount" 
                                        x-model.number="installmentAmount" 
                                        @input="onInstallmentAmountChange()" 
                                        min="0" 
                                        step="any"
                                        placeholder="7500" 
                                        required
                                        class="w-full pl-10 pr-3 py-2.5 bg-white border-2 border-pink-500 rounded-xl focus:outline-none focus:border-pink-700 font-black text-pink-950 font-mono text-base shadow-xs"
                                    >
                                </div>
                                <span class="text-[10px] text-pink-950/80 font-medium block mt-1.5 leading-snug">
                                    Rumus: Target &divide; <span class="font-bold" x-text="installmentCount || 1"></span> Hari (pembulatan ke 500/1000).
                                </span>
                            </div>

                            <!-- TOMBOL PEMBULATAN CEPAT -->
                            <div class="mt-2.5 pt-2 border-t border-pink-300 flex items-center justify-between gap-1">
                                <span class="text-[9px] font-bold text-pink-900 uppercase">Bulatkan:</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="roundInstallment(500)" class="px-2 py-0.5 rounded bg-white hover:bg-pink-100 text-pink-900 border border-pink-300 text-[10px] font-bold shadow-2xs transition">500</button>
                                    <button type="button" @click="roundInstallment(1000)" class="px-2 py-0.5 rounded bg-white hover:bg-pink-100 text-pink-900 border border-pink-300 text-[10px] font-bold shadow-2xs transition">1rb</button>
                                    <button type="button" @click="roundInstallment(5000)" class="px-2 py-0.5 rounded bg-white hover:bg-pink-100 text-pink-900 border border-pink-300 text-[10px] font-bold shadow-2xs transition">5rb</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TITIK AMAN FULL PARSEL SPL (JUMLAH SETORAN: HARI / MINGGU) -->
                    <div class="p-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 rounded-2xl border-2 border-emerald-300 shadow-2xs space-y-2.5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                            <div>
                                <label class="block font-black text-emerald-950 text-xs flex items-center">
                                    <i class="fa-solid fa-shield-halved text-emerald-600 mr-1.5 text-sm"></i>
                                    Titik Aman Full Parsel SPL (Jumlah Setoran)
                                    <span class="text-[10px] font-semibold text-slate-500 ml-1.5 font-normal">(Opsional)</span>
                                </label>
                                <p class="text-[11px] text-emerald-900/80 font-medium">
                                    Batas minimal jumlah setoran (<strong x-text="periodType === 'harian' ? 'berapa Hari' : 'berapa Minggu'"></strong>) agar saat <strong>Tutup Buku</strong> konsumen dijamin menerima <strong>100% barang paket parsel</strong>. Uang tunai disesuaikan oleh Owner dengan hitungan rekomendasi otomatis.
                                </p>
                            </div>
                            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 shrink-0">
                                Jaminan Tutup Buku
                            </span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-1">
                            <div class="relative sm:w-64">
                                <input 
                                    type="number" 
                                    name="safe_threshold" 
                                    x-model.number="safeThreshold" 
                                    min="0" 
                                    step="1"
                                    placeholder="Contoh: 130" 
                                    class="w-full pl-3 pr-14 py-2 bg-white border-2 border-emerald-400 rounded-xl focus:outline-none focus:border-emerald-600 font-black text-emerald-950 font-mono text-sm text-center shadow-xs"
                                >
                                <span class="absolute inset-y-0 right-0 pr-3 flex items-center font-bold text-emerald-700 text-xs pointer-events-none" x-text="periodType === 'harian' ? 'Hari' : 'Minggu'"></span>
                            </div>
                            <!-- PRESET CEPAT TITIK AMAN -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] font-bold text-slate-500">Preset:</span>
                                <button type="button" @click="setSafeThresholdPeriods(Math.round((installmentCount || 100) * 0.5))" class="px-2 py-1 rounded-lg bg-white hover:bg-emerald-100 text-emerald-900 border border-emerald-200 text-[10px] font-bold transition shadow-2xs">50% Durasi</button>
                                <button type="button" @click="setSafeThresholdPeriods(Math.round((installmentCount || 100) * 0.7))" class="px-2 py-1 rounded-lg bg-white hover:bg-emerald-100 text-emerald-900 border border-emerald-200 text-[10px] font-bold transition shadow-2xs">70% Durasi</button>
                                <button type="button" @click="setSafeThresholdPeriods(Math.round((installmentCount || 100) * 0.8))" class="px-2 py-1 rounded-lg bg-white hover:bg-emerald-100 text-emerald-900 border border-emerald-200 text-[10px] font-bold transition shadow-2xs">80% Durasi</button>
                                <button type="button" @click="setSafeThresholdPeriods(installmentCount || 100)" class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-extrabold transition shadow-2xs">100% (Lunas Penuh)</button>
                                <button type="button" @click="safeThreshold = 0" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold transition">Tanpa Batas (0)</button>
                            </div>
                        </div>
                        <template x-if="safeThreshold > 0">
                            <div class="text-[11px] font-semibold text-emerald-800 bg-emerald-100/70 p-2 rounded-lg border border-emerald-200 flex items-center justify-between">
                                <span>Setara Nominal Setoran: <strong class="font-mono text-emerald-950 font-bold" x-text="formatRupiah(safeThreshold * (installmentAmount || 0))"></strong></span>
                                <span class="text-[10px] text-emerald-700" x-text="`(${safeThreshold} ${periodType === 'harian' ? 'hari' : 'minggu'} &times; ${formatRupiah(installmentAmount)})`"></span>
                            </div>
                        </template>
                    </div>

                    <!-- HIDDEN SINKRONISASI BASE PRICE & ADMIN FEE KHUSUS PARSEL -->
                    <input type="hidden" name="base_price" :value="targetNominal">
                    <input type="hidden" name="admin_fee" value="0">

                    <!-- LIVE SUMMARY BANNER PARSEL -->
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 text-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-lg bg-emerald-600 text-white shrink-0"><i class="fa-solid fa-gift"></i></span>
                            <div class="space-y-0.5">
                                <div class="font-extrabold text-slate-900">
                                    Paket Parsel: Harga Total <span class="text-emerald-700 font-black" x-text="formatRupiah(targetNominal)"></span>
                                </div>
                                <div class="text-[11px] text-slate-600 font-medium">
                                    Model Harian: Diangsur rutin selama <strong class="text-emerald-900" x-text="`${installmentCount} Hari`"></strong> (Kotak ceklis H1 s/d H<span x-text="installmentCount"></span>).
                                </div>
                            </div>
                        </div>
                        <div class="text-left sm:text-right shrink-0 bg-white sm:bg-transparent p-2.5 sm:p-0 rounded-lg border sm:border-0 border-emerald-200">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Setoran Harian</div>
                            <div class="text-base font-black text-emerald-800">
                                <span x-text="formatRupiah(installmentAmount)"></span>
                                <span class="text-xs font-bold text-slate-500">/ hari</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(!$package->isTabungan())
            <!-- 5. OPSI KEMASAN YANG DITAWARKAN (KOPER, KERANJANG, BOX HAMPERS) -->
            <div class="p-5 rounded-2xl bg-white border border-pink-200 shadow-2xs space-y-4">
                <div class="flex items-start justify-between">
                    <div class="space-y-0.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-600 inline-block"></span>
                            <label class="font-bold text-slate-900 text-sm">
                                5. Opsi Kemasan yang Ditawarkan (Koper, Keranjang, Box Hampers)
                            </label>
                        </div>
                        <p class="text-xs text-slate-500">
                            Pilih kemasan yang ditawarkan untuk paket ini. Konsumen dapat memilih salah satu saat mendaftar, dan harga kemasan akan otomatis masuk ke hitungan cicilan.
                        </p>
                    </div>
                    <a href="{{ route('owner.packagings.create') }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-[11px] border border-pink-200 shrink-0 flex items-center space-x-1">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Kemasan</span>
                    </a>
                </div>

                @php
                    $selectedPackagings = $package->packagings->keyBy('id');
                @endphp

                @if($packagings->isEmpty())
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                        Belum ada master kemasan. Silakan tambahkan kemasan terlebih dahulu melalui <a href="{{ route('owner.packagings.create') }}" target="_blank" class="font-bold text-pink-600 underline">Menu Data Kemasan</a>.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($packagings as $pkg)
                            @php
                                $isAttached = $selectedPackagings->has($pkg->id);
                                $attachedPivot = $isAttached ? $selectedPackagings->get($pkg->id)->pivot : null;
                                $customPriceVal = $attachedPivot ? $attachedPivot->custom_price : null;
                                $isDefVal = $attachedPivot ? (bool)$attachedPivot->is_default : false;
                            @endphp
                            <div class="p-3 rounded-xl border border-slate-200 hover:border-pink-300 bg-slate-50/50 flex flex-col justify-between transition" x-data="{ selected: {{ $isAttached ? 'true' : 'false' }} }">
                                <div class="flex items-start space-x-3">
                                    <label class="relative flex items-center pt-1.5 cursor-pointer">
                                        <input type="checkbox" name="packagings[{{ $pkg->id }}][selected]" value="1" x-model="selected" class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300">
                                    </label>
                                    
                                    <!-- Thumbnail Foto Kemasan -->
                                    <div class="w-14 h-14 rounded-lg bg-white border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center cursor-pointer shadow-2xs group relative"
                                         @click="previewPackagingModalImage = '{{ $pkg->image_url ?: '' }}'; previewPackagingModalTitle = '{{ addslashes($pkg->name) }} ({{ $pkg->code }})'">
                                        @if($pkg->image_path && file_exists(public_path($pkg->image_path)))
                                            <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition text-xs">
                                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                                            </div>
                                        @else
                                            <i class="fa-solid fa-box-archive text-slate-300 text-xl"></i>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-[10px] font-bold text-pink-700 bg-pink-100/70 px-1.5 py-0.2 rounded">{{ $pkg->code }}</span>
                                            <span class="text-[11px] font-bold text-slate-900">{{ $pkg->formatted_price }}</span>
                                        </div>
                                        <div class="font-bold text-slate-800 text-xs truncate mt-0.5" title="{{ $pkg->name }}">{{ $pkg->name }}</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Modal Belanja: {{ $pkg->formatted_cost_price }}</div>
                                    </div>
                                </div>

                                <!-- Custom Price Override & Default Option when checked -->
                                <div x-show="selected" x-transition class="mt-3 pt-2.5 border-t border-slate-200/80 grid grid-cols-2 gap-2 text-[11px]">
                                    <div>
                                        <label class="block text-[10px] text-slate-500 font-semibold mb-0.5">Harga Tambahan Paket</label>
                                        <input type="number" name="packagings[{{ $pkg->id }}][custom_price]" value="{{ $customPriceVal !== null ? (int)$customPriceVal : '' }}" placeholder="{{ (int)$pkg->price }}" min="0" step="1000" class="w-full p-1.5 bg-white border border-slate-200 rounded text-xs font-mono">
                                        <span class="text-[9px] text-slate-400">Kosongkan jika default</span>
                                    </div>
                                    <div class="flex items-center justify-end pt-3">
                                        <label class="inline-flex items-center cursor-pointer text-[10px] text-slate-700 font-medium">
                                            <input type="checkbox" name="packagings[{{ $pkg->id }}][is_default]" value="1" {{ $isDefVal ? 'checked' : '' }} class="rounded text-pink-600 focus:ring-pink-500 mr-1.5">
                                            <span>Opsi Standar</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            <!-- 6. DESKRIPSI PAKET -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">6. Deskripsi Paket</label>
                <textarea name="description" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">{{ old('description', $package->description) }}</textarea>
            </div>

            <!-- 7. FOTO / GAMBAR PAKET -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block font-bold text-slate-800">7. Foto / Gambar Paket</label>
                        <p class="text-[10px] text-slate-500">Tampil pada kartu paket, katalog cetak brosur, dan HP agen. Otomatis dikompresi (~100-160 KB).</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Auto-Compress GD</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                    <!-- Dropzone / Input -->
                    <div class="sm:col-span-2 space-y-2">
                        <label for="packageImageInput" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl cursor-pointer bg-white transition text-center group">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 group-hover:text-emerald-600 transition mb-1.5"></i>
                            <span class="text-xs font-bold text-slate-700 group-hover:text-emerald-700">Pilih Foto Baru untuk Mengganti</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, WebP (Maks. 8 MB)</span>
                            <input id="packageImageInput" type="file" name="image" accept="image/*" @change="previewImage($event)" class="hidden">
                        </label>

                        @if($package->image_path)
                            <div class="flex items-center space-x-2 pt-1">
                                <label class="inline-flex items-center space-x-2 cursor-pointer text-red-600 hover:text-red-700">
                                    <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span class="font-semibold text-xs">Hapus foto paket saat ini</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <!-- Live Preview or Current Image Box -->
                    <div class="flex flex-col items-center justify-center p-3 bg-white border border-slate-200 rounded-xl min-h-[110px]">
                        <template x-if="imagePreview">
                            <div class="relative w-full text-center">
                                <img :src="imagePreview" alt="Preview Foto Baru" class="h-24 w-full object-cover rounded-lg border border-slate-200 shadow-xs">
                                <button type="button" @click="clearImage()" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md transition" title="Batal ganti foto">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <span class="block text-[9px] font-bold text-emerald-700 mt-1">Foto baru dipilih</span>
                            </div>
                        </template>
                        <template x-if="!imagePreview">
                            <div>
                                @if($package->image_path)
                                    <div class="text-center">
                                        <img src="{{ asset($package->image_path) }}" alt="{{ $package->name }}" class="h-24 w-full object-cover rounded-lg border border-slate-200 shadow-xs">
                                        <span class="block text-[9px] font-bold text-slate-500 mt-1">Foto aktif saat ini</span>
                                    </div>
                                @else
                                    <div class="text-center text-slate-400 py-2">
                                        <i class="fa-regular fa-image text-2xl mb-1 text-slate-300"></i>
                                        <div class="text-[10px] font-medium">Belum ada foto paket</div>
                                    </div>
                                @endif
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 8. STATUS ACTIVE -->
            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600">
                    <span class="font-semibold text-slate-700">Paket Aktif (Tampil dalam katalog & pendaftaran konsumen)</span>
                </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200">
                <a href="{{ route('packages.index') }}" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-check mr-1"></i>
                    <span>Simpan Perubahan Paket</span>
                </button>
            </div>
        </form>
    </div>

    <!-- MODAL PREVIEW FOTO KEMASAN -->
    <div x-show="previewPackagingModalImage" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4" style="display: none;" @click="previewPackagingModalImage = null" @keydown.escape.window="previewPackagingModalImage = null">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-pink-100" @click.stop>
            <div class="p-4 bg-gradient-to-r from-pink-600 to-rose-600 text-white flex items-center justify-between">
                <span class="font-bold text-sm" x-text="previewPackagingModalTitle"></span>
                <button type="button" @click="previewPackagingModalImage = null" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">✕</button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center min-h-[300px]">
                <img :src="previewPackagingModalImage" alt="Foto Kemasan" class="max-h-[450px] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="previewPackagingModalImage = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold text-xs">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function packageEditor() {
    return {
        isParsel: {{ $package->isParsel() ? 'true' : 'false' }},
        isEcer: {{ $package->isEcer() ? 'true' : 'false' }},
        periodType: '{{ old('period_type', $package->period_type ?? 'mingguan') }}',
        startDate: '{{ old('start_date', $defaultStartDate) }}',
        endDate: '{{ old('end_date', $defaultEndDate) }}',
        basePrice: {{ old('base_price', (int)$package->base_price) }},
        adminFee: {{ old('admin_fee', (int)$package->admin_fee) }},
        targetNominal: {{ (int)$package->target_nominal }},
        safeThreshold: {{ old('safe_threshold', (int)($package->safe_period_count ?: 0)) }},
        installmentCount: {{ (int)$package->installment_count }},
        installmentAmount: {{ (int)$package->installment_amount }},
        totalItemsValue: 0,
        totalBarangValue: 0,
        totalUangValue: 0,
        isBasePriceManual: true,
        imagePreview: null,
        previewPackagingModalImage: null,
        previewPackagingModalTitle: '',
        itemsLookup: @json($items->keyBy('id')),
        itemsList: [
            @if($package->packageItems->count() > 0)
                @foreach($package->packageItems as $pi)
                    { item_id: '{{ $pi->item_id }}', item_name: '{{ addslashes($pi->item ? $pi->item->name : '') }}', quantity: {{ $pi->quantity }}, unit: '{{ addslashes($pi->unit ?: ($pi->item?->base_unit ?: 'pcs')) }}', installment_amount: {{ (float)($pi->installment_amount ?: 0) }}, is_bonus: {{ $pi->is_bonus ? 'true' : 'false' }} },
                @endforeach
            @else
                { item_id: '', item_name: '', quantity: 1, unit: '{{ $package->isEcer() ? 'kg' : 'pcs' }}', installment_amount: 0, is_bonus: false },
            @endif
        ],

        addBonusItem() {
            this.itemsList.push({
                item_id: '',
                item_name: '',
                quantity: 1,
                unit: 'pcs',
                installment_amount: 0,
                is_bonus: true
            });
        },

        setSafeThresholdPeriods(periods) {
            this.safeThreshold = Math.max(0, parseInt(periods) || 0);
        },

        removeBonusItem(idx) {
            if (this.itemsList.length > 1) {
                this.itemsList.splice(idx, 1);
            } else {
                this.itemsList[0] = {
                    item_id: '',
                    item_name: '',
                    quantity: 1,
                    unit: 'pcs',
                    installment_amount: 0,
                    is_bonus: true
                };
            }
        },

        onBonusItemSelect(row) {
            if (row.item_id && this.itemsLookup[row.item_id]) {
                const it = this.itemsLookup[row.item_id];
                row.item_name = it.name;
                if (it.base_unit) row.unit = it.base_unit;
            }
        },

        // Modal State Uang Tunai (Edit)
        showUangModal: false,
        uangModalNominal: 500000,
        uangModalName: 'Uang Tunai Rp 500.000',
        uangModalUnit: 'amplop',
        uangLoading: false,

        openUangModal() {
            this.showUangModal = true;
            this.uangModalNominal = 500000;
            this.uangModalName = 'Uang Tunai Rp 500.000';
            this.uangModalUnit = 'amplop';
        },

        closeUangModal() {
            this.showUangModal = false;
        },

        setUangPreset(nom) {
            this.uangModalNominal = nom;
            this.uangModalName = 'Uang Tunai ' + this.formatRupiah(nom);
        },

        onUangModalNominalInput() {
            if (this.uangModalNominal > 0) {
                this.uangModalName = 'Uang Tunai ' + this.formatRupiah(this.uangModalNominal);
            }
        },

        async submitUangModal() {
            if (!this.uangModalNominal || this.uangModalNominal <= 0) return;
            this.uangLoading = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route("items.quick-uang") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        nominal: this.uangModalNominal,
                        name: this.uangModalName || ('Uang Tunai ' + this.formatRupiah(this.uangModalNominal)),
                        base_unit: this.uangModalUnit || 'amplop',
                    })
                });
                const data = await res.json();
                if (data.success && data.item) {
                    this.itemsLookup[data.item.id] = data.item;
                    if (this.itemsList.length === 1 && !this.itemsList[0].item_id) {
                        this.itemsList = [];
                    }
                    this.itemsList.push({
                        item_id: String(data.item.id),
                        quantity: 1,
                        is_bonus: false
                    });
                    this.closeUangModal();
                    this.recalculateTotalItems(true);

                    if (!this.isParsel) {
                        this.basePrice = this.totalUangValue;
                        this.onBasePriceChange();
                    }
                } else {
                    alert('Gagal menambahkan uang: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (e) {
                alert('Koneksi bermasalah: ' + e.message);
            } finally {
                this.uangLoading = false;
            }
        },

        isUangItem(itemId) {
            if (!itemId || !this.itemsLookup[itemId]) return false;
            const cat = (this.itemsLookup[itemId].category || '').toLowerCase();
            return cat === 'uang' || Boolean(this.itemsLookup[itemId].is_uang);
        },

        onItemSelectionChange(row) {
            if (this.isUangItem(row.item_id)) {
                row.is_bonus = false;
            } else if (!this.isParsel) {
                row.is_bonus = true;
            }
            this.recalculateTotalItems(true);
        },

        calculateDateDifference() {
            if (!this.startDate || !this.endDate) return { days: 0, weeks: 0, remDays: 0 };
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);
            const diffTime = end.getTime() - start.getTime();
            const diffDays = Math.max(0, Math.round(diffTime / (1000 * 60 * 60 * 24)));
            const weeks = Math.floor(diffDays / 7);
            const remDays = diffDays % 7;
            return { days: diffDays, weeks: weeks, remDays: remDays };
        },

        init() {
            this.recalculateTotalItems(false);
            if (this.isEcer) {
                this.periodType = 'mingguan';
                if (!this.installmentCount) this.installmentCount = 30;
                if (!this.installmentAmount && this.targetNominal > 0 && this.installmentCount > 0) {
                    this.installmentAmount = Math.round(this.targetNominal / this.installmentCount);
                } else if (!this.installmentAmount) {
                    this.installmentAmount = 10000;
                }
                this.onEcerInstallmentChange();
            } else {
                if (this.basePrice === 0 && this.targetNominal > 0) {
                    this.basePrice = Math.max(0, this.targetNominal - (Number(this.adminFee) || 0));
                }
                this.calculateInstallment();
            }
        },

        setEcerInstallment(amount) {
            this.installmentAmount = Number(amount) || 0;
            this.onEcerInstallmentChange();
        },

        onEcerInstallmentChange() {
            const amount = Number(this.installmentAmount) || 0;
            const count = Number(this.installmentCount) || 1;
            this.targetNominal = Math.max(0, Math.round(amount * count));
            this.basePrice = this.targetNominal;
            this.adminFee = 0;
        },

        onParselPriceChange() {
            const price = Number(this.targetNominal) || 0;
            this.basePrice = price;
            this.adminFee = 0;
            this.isBasePriceManual = true;
            this.calculateInstallment();
        },

        onItemNameInput(row) {
            if (!row.item_name) {
                row.item_id = '';
                return;
            }
            const trimmed = row.item_name.trim().toLowerCase();
            const found = Object.values(this.itemsLookup).find(it => it.name && it.name.trim().toLowerCase() === trimmed);
            if (found) {
                row.item_id = String(found.id);
            } else {
                row.item_id = '';
            }
        },

        onPeriodTypeChange(val) {
            this.periodType = val;
            this.calculateInstallment();
        },

        setInstallmentCount(count) {
            this.installmentCount = count;
            if (this.isEcer) {
                this.onEcerInstallmentChange();
            } else {
                this.calculateInstallment();
            }
        },

        onBasePriceChange() {
            this.isBasePriceManual = true;
            const base = Number(this.basePrice) || 0;
            const fee = Number(this.adminFee) || 0;
            this.targetNominal = Math.max(0, base + fee);
            this.calculateInstallment();
        },

        onAdminFeeChange() {
            const base = Number(this.basePrice) || 0;
            const fee = Number(this.adminFee) || 0;
            this.targetNominal = Math.max(0, base + fee);
            this.calculateInstallment();
        },

        onTargetNominalChange() {
            const target = Number(this.targetNominal) || 0;
            const fee = Number(this.adminFee) || 0;
            this.basePrice = Math.max(0, target - fee);
            this.isBasePriceManual = true;
            this.calculateInstallment();
        },

        onInstallmentAmountChange() {
            const amount = Number(this.installmentAmount) || 0;
            const count = Number(this.installmentCount) || 1;
            const fee = Number(this.adminFee) || 0;
            this.targetNominal = Math.max(0, Math.round(amount * count));
            this.basePrice = Math.max(0, this.targetNominal - fee);
            this.isBasePriceManual = true;
        },

        splRoundInstallment(val) {
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

        roundInstallment(step) {
            if (!step || step <= 0) return;
            const current = Number(this.installmentAmount) || 0;
            this.installmentAmount = Math.ceil(current / step) * step;
            this.onInstallmentAmountChange();
        },

        useTotalItemsValue() {
            this.basePrice = this.totalItemsValue;
            this.isBasePriceManual = false;
            this.onBasePriceChange();
        },

        recalculateTotalItems(autoSetTarget = false) {
            let sumBarang = 0;
            let sumUang = 0;
            this.itemsList.forEach(r => {
                if (r.item_id && this.itemsLookup[r.item_id]) {
                    const price = Number(this.itemsLookup[r.item_id].unit_price) || 0;
                    const q = Number(r.quantity) || 1;
                    if (this.isUangItem(r.item_id)) {
                        sumUang += (price * q);
                    } else {
                        sumBarang += (price * q);
                    }
                }
            });
            this.totalBarangValue = sumBarang;
            this.totalUangValue = sumUang;
            this.totalItemsValue = sumBarang + sumUang;

            if (autoSetTarget && this.totalItemsValue > 0 && this.isParsel) {
                this.basePrice = this.totalItemsValue;
                const fee = Number(this.adminFee) || 0;
                this.targetNominal = this.basePrice + fee;
            } else if (autoSetTarget && this.totalUangValue > 0 && !this.isParsel) {
                this.basePrice = this.totalUangValue;
                const fee = Number(this.adminFee) || 0;
                this.targetNominal = this.basePrice + fee;
            }
            this.calculateInstallment();
        },

        getItemUnitPrice(itemId) {
            if (!itemId || !this.itemsLookup[itemId]) return '-';
            return this.formatRupiah(this.itemsLookup[itemId].unit_price);
        },

        getRowSubtotal(row) {
            if (!row.item_id || !this.itemsLookup[row.item_id]) return '-';
            const price = Number(this.itemsLookup[row.item_id].unit_price) || 0;
            const q = Number(row.quantity) || 1;
            return this.formatRupiah(price * q);
        },

        calculateInstallment() {
            if (this.targetNominal > 0 && this.installmentCount > 0) {
                const raw = this.targetNominal / this.installmentCount;
                this.installmentAmount = this.splRoundInstallment(raw);
            } else {
                this.installmentAmount = 0;
            }
        },

        get totalEcerItemsInstallment() {
            return this.itemsList.reduce((acc, r) => acc + (Number(r.installment_amount) || 0), 0);
        },

        recalculateEcerTotal() {
            if (this.isEcer) {
                const sum = this.totalEcerItemsInstallment;
                if (sum > 0) {
                    this.installmentAmount = sum;
                }
                this.onEcerInstallmentChange();
            }
        },

        addRow(type = 'barang') {
            if (type === 'uang') {
                this.openUangModal();
                return;
            }
            this.itemsList.push({ 
                item_name: '',
                item_id: '', 
                quantity: 1, 
                unit: (this.isEcer ? 'kg' : 'pcs'),
                installment_amount: (this.isEcer ? 10000 : 0),
                is_bonus: (!this.isParsel && !this.isEcer) 
            });
            if (this.isEcer) {
                this.recalculateEcerTotal();
            }
        },

        removeRow(index) {
            this.itemsList.splice(index, 1);
            this.recalculateTotalItems(true);
            if (this.isEcer) {
                this.recalculateEcerTotal();
            }
        },

        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.imagePreview = null;
            }
        },

        clearImage() {
            this.imagePreview = null;
            const input = document.getElementById('packageImageInput');
            if (input) input.value = '';
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
