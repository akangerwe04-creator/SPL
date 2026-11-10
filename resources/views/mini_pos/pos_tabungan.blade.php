@extends('layouts.app')

@section('title', 'Kasir Tabungan Magol - ' . $customerPackage->customer->name)
@section('page_title', 'Kasir Penyesuaian Tabungan Magol')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
        <i class="fa-solid fa-money-bill-wave mr-1"></i> Penyesuaian Dana Tabungan
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="tabunganSettlementApp({{ (float)$customerPackage->total_paid }})">

    <!-- TOP RETURN & INFO BANNER -->
    <div class="flex items-center justify-between text-xs text-slate-500">
        <a href="{{ route('mini-pos.index') }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Tunggakan
        </a>
        <div class="font-mono">ID Konsumen: {{ $customerPackage->customer->code }}</div>
    </div>

    <!-- HEADER CARD -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-800 to-slate-900 rounded-2xl p-6 text-white shadow-md">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-white/20 text-indigo-100">
                    Paket Tabungan (Magol / Tidak Lunas)
                </span>
                <h2 class="text-xl font-heading font-extrabold mt-2">
                    Penyerahan Saldo Tabungan: {{ $customerPackage->customer->name }}
                </h2>
                <p class="text-xs text-indigo-200 mt-1 max-w-2xl leading-relaxed">
                    Khusus konsumen paket tabungan yang macet/bolong setoran (magol). Sesuai aturan SPL, penyelesaian <strong>tidak memilih barang</strong>, melainkan diserahkan dalam bentuk dana tunai dengan nominal yang ditentukan oleh Owner setelah dipotong biaya administrasi.
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-xs rounded-xl p-3 border border-white/20 text-right flex-shrink-0">
                <div class="text-[10px] text-indigo-200 uppercase font-bold">Total Tabungan Masuk</div>
                <div class="text-2xl font-heading font-extrabold text-amber-300">
                    {{ $customerPackage->formatted_paid }}
                </div>
                <div class="text-[10px] text-indigo-200">dari Target {{ $customerPackage->formatted_target }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: CONSUMER DATA & TABUNGAN RULES (4 COLS) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Identitas Konsumen</span>
                    <h3 class="font-heading font-bold text-slate-900 text-base mt-0.5">
                        {{ $customerPackage->customer->name }}
                    </h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span>Paket Tabungan:</span>
                        <strong class="text-slate-900">{{ $customerPackage->package->name }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span>Agen Pembina:</span>
                        <span class="font-bold text-indigo-700">{{ $customerPackage->agent->name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span>Kontak Konsumen:</span>
                        <span>{{ $customerPackage->customer->phone ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span>Alamat:</span>
                        <span class="text-right max-w-[180px]">{{ $customerPackage->customer->address ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span>Target Tabungan:</span>
                        <span class="font-bold text-slate-800">{{ $customerPackage->formatted_target }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-600">
                        <span>Realisasi Setoran:</span>
                        <strong class="text-emerald-700 font-extrabold text-sm">{{ $customerPackage->formatted_paid }}</strong>
                    </div>
                </div>

                <!-- TABUNGAN RULES ALERT -->
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-xs space-y-1.5">
                    <div class="font-bold flex items-center text-amber-900">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600"></i> Ketentuan Tabungan Magol:
                    </div>
                    <ul class="list-disc list-inside text-[11px] text-amber-800 space-y-1">
                        <li>Bonus barang tabungan (misal emas/kue) <strong>hangus</strong> karena tidak mencapai target lunas penuh.</li>
                        <li>Owner menentukan jumlah nominal yang diserahkan kembali kepada konsumen secara manual.</li>
                        <li>Dikenakan potongan biaya administrasi SPL sesuai kesepakatan usaha.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: CASH SETTLEMENT FORM (7 COLS) -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6">
                <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Formulir Kasir</span>
                        <h3 class="font-heading font-extrabold text-slate-900 text-base">
                            Penetapan Nominal & Potongan Admin
                        </h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Input Bebas Owner
                    </span>
                </div>

                <form action="{{ route('mini-pos.adjust.tabungan', $customerPackage) }}" method="POST" class="mt-5 space-y-5">
                    @csrf

                    <!-- 1. SALDO TABUNGAN MASUK (READONLY) -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-slate-500 font-medium">Total Dana Tabungan Masuk:</span>
                            <div class="text-xs text-slate-400">Dari ceklis setoran yang telah disahkan</div>
                        </div>
                        <div class="text-lg font-heading font-extrabold text-slate-900">
                            {{ $customerPackage->formatted_paid }}
                        </div>
                    </div>

                    <!-- 2. POTONGAN BIAYA ADMINISTRASI SPL -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-800 flex items-center">
                                <i class="fa-solid fa-percent text-indigo-600 mr-1.5"></i> Potongan Biaya Administrasi (SPL)
                            </label>
                            <span class="text-[11px] text-slate-500 font-mono" x-text="formatRupiah(adminFee)"></span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input 
                                type="number" 
                                name="admin_fee" 
                                min="0" 
                                :max="totalPaid"
                                step="1000"
                                x-model.number="adminFee" 
                                @input="onAdminFeeInput()"
                                placeholder="0" 
                                class="w-full text-sm font-bold text-slate-900 pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600"
                            >
                        </div>
                        <!-- PRESET BUTTONS FOR QUICK ADMIN FEE -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <span class="text-[10px] text-slate-400 font-semibold mr-1">Preset:</span>
                            <button type="button" @click="setAdminPercent(0)" class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                Tanpa Potongan (Rp 0)
                            </button>
                            <button type="button" @click="setAdminPercent(5)" class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition">
                                5% (<span x-text="formatRupiah(Math.round(totalPaid * 0.05))"></span>)
                            </button>
                            <button type="button" @click="setAdminPercent(10)" class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 hover:bg-indigo-200 text-indigo-800 transition">
                                10% (<span x-text="formatRupiah(Math.round(totalPaid * 0.10))"></span>)
                            </button>
                            <button type="button" @click="setAdminNominal(25000)" class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                Rp 25.000
                            </button>
                            <button type="button" @click="setAdminNominal(50000)" class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                Rp 50.000
                            </button>
                        </div>
                    </div>

                    <!-- 3. JUMLAH NOMINAL DITERIMA KONSUMEN (DIISI MANUAL OLEH OWNER) -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-extrabold text-slate-900 flex items-center">
                                <i class="fa-solid fa-hand-holding-dollar text-emerald-600 mr-1.5 text-sm"></i> Jumlah Nominal yang Diterima Konsumen (Manual)
                            </label>
                            <span class="text-[11px] text-emerald-700 font-bold" x-text="formatRupiah(nominalReceived)"></span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-extrabold text-emerald-600">Rp</span>
                            <input 
                                type="number" 
                                name="nominal_received" 
                                required 
                                min="0" 
                                :max="totalPaid"
                                step="1000"
                                x-model.number="nominalReceived" 
                                @input="onNominalReceivedInput()"
                                class="w-full text-lg font-heading font-extrabold text-emerald-800 pl-11 pr-4 py-3 bg-emerald-50/50 border-2 border-emerald-500 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            >
                        </div>
                        <p class="text-[11px] text-slate-500">
                            * Diisi manual oleh Ibu Susi / Owner sesuai kesepakatan nominal uang tunai yang diserahkan ke konsumen.
                        </p>
                    </div>

                    <!-- 4. REALTIME BREAKDOWN & SUMMARY CARD -->
                    <div class="p-4 rounded-xl border-2 transition-all duration-300"
                        :class="isOverTotal ? 'bg-red-50 border-red-500 text-red-900 animate-pulse' : 'bg-slate-50 border-slate-200 text-slate-800'">
                        
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Total Setoran Tabungan Masuk:</span>
                                <strong class="font-bold text-slate-900" x-text="formatRupiah(totalPaid)"></strong>
                            </div>
                            <div class="flex justify-between items-center text-indigo-700">
                                <span>Potongan Biaya Administrasi (SPL):</span>
                                <strong class="font-bold" x-text="'- ' + formatRupiah(adminFee)"></strong>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200 text-emerald-800">
                                <span class="font-extrabold text-sm">Uang Tunai Diterima Konsumen:</span>
                                <strong class="text-base font-extrabold" x-text="formatRupiah(nominalReceived)"></strong>
                            </div>
                            <div class="flex justify-between items-center text-slate-500 text-[11px] pt-1">
                                <span>Sisa Selisih / Saldo Kas Ditahan:</span>
                                <span class="font-bold" :class="remainingRefund < 0 ? 'text-red-600 font-extrabold' : 'text-slate-700'" x-text="formatRupiah(remainingRefund)"></span>
                            </div>
                        </div>

                        <!-- WARNING IF OVER TOTAL -->
                        <template x-if="isOverTotal">
                            <div class="mt-3 p-2 bg-red-600 text-white rounded-lg text-xs font-bold flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                                <span>TOTAL (DITERIMA + ADMIN) MELEBIHI SALDO SETORAN TABUNGAN!</span>
                            </div>
                        </template>
                    </div>

                    <!-- 5. CATATAN KESEPAKATAN SERAH TERIMA -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Berita Acara Penyerahan</label>
                        <textarea 
                            name="notes" 
                            rows="2" 
                            placeholder="Misal: Penyerahan dana sisa tabungan kepada Ibu/Bapak... setelah dipotong administrasi SPL." 
                            class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500"
                        ></textarea>
                    </div>

                    <!-- 6. SUBMIT BUTTON -->
                    <button 
                        type="submit" 
                        :disabled="isOverTotal || nominalReceived <= 0"
                        :class="(isOverTotal || nominalReceived <= 0) ? 'bg-slate-300 cursor-not-allowed text-slate-500' : 'bg-indigo-700 hover:bg-indigo-800 text-white shadow-sm cursor-pointer'"
                        class="w-full py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider transition flex items-center justify-center space-x-2"
                    >
                        <i class="fa-solid fa-receipt text-sm"></i>
                        <span>Simpan Penyesuaian & Terbitkan Kwitansi Serah Terima</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function tabunganSettlementApp(initialPaid) {
    const paid = Number(initialPaid) || 0;
    return {
        totalPaid: paid,
        adminFee: Math.round(paid * 0.10), // Default 10% potongan admin SPL
        nominalReceived: Math.max(0, paid - Math.round(paid * 0.10)),

        get remainingRefund() {
            return this.totalPaid - this.nominalReceived - this.adminFee;
        },

        get isOverTotal() {
            return (this.nominalReceived + this.adminFee) > this.totalPaid;
        },

        setAdminPercent(percent) {
            this.adminFee = Math.round(this.totalPaid * (percent / 100));
            this.nominalReceived = Math.max(0, this.totalPaid - this.adminFee);
        },

        setAdminNominal(amount) {
            this.adminFee = Math.min(this.totalPaid, Number(amount) || 0);
            this.nominalReceived = Math.max(0, this.totalPaid - this.adminFee);
        },

        onAdminFeeInput() {
            if (this.adminFee < 0) this.adminFee = 0;
            if (this.adminFee > this.totalPaid) this.adminFee = this.totalPaid;
            this.nominalReceived = Math.max(0, this.totalPaid - this.adminFee);
        },

        onNominalReceivedInput() {
            if (this.nominalReceived < 0) this.nominalReceived = 0;
            // Jika nominal yang diisi manual diubah, sesuaikan potongan admin jika perlu
            if (this.nominalReceived > this.totalPaid) {
                this.adminFee = 0;
            } else if ((this.nominalReceived + this.adminFee) > this.totalPaid) {
                this.adminFee = Math.max(0, this.totalPaid - this.nominalReceived);
            }
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush

