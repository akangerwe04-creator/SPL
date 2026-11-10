@extends('layouts.app')

@section('title', 'Reset Data Sistem')
@section('page_title', 'Pusat Pemeliharaan & Reset Data')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
        <i class="fa-solid fa-triangle-exclamation mr-1 text-red-600"></i> Area Sensitif Owner
    </span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="dataResetHandler()">

    <!-- WARNING BANNER -->
    <div class="p-5 rounded-2xl bg-red-50 border-2 border-red-200 text-red-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-start space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0 text-lg shadow-sm">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h3 class="font-heading font-extrabold text-sm text-red-900">Perhatian: Tindakan Ini Bersifat Permanen</h3>
                <p class="text-xs text-red-800/90 mt-0.5 leading-relaxed">
                    Fitur ini digunakan saat memulai musim baru Lebaran berikutnya, menghapus data uji coba, atau membersihkan transaksi lapangan. Akun Super Admin Anda (<strong>{{ auth()->user()->name }}</strong>) diproteksi dan tidak akan terhapus.
                </p>
            </div>
        </div>
        <div class="shrink-0 flex items-center space-x-2">
            <a href="{{ route('owner.dashboard') }}" class="px-3 py-1.5 bg-white border border-red-200 hover:bg-red-100/50 rounded-xl text-xs font-bold text-red-900 transition">
                &larr; Batal & Kembali
            </a>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8">
        <form action="{{ route('owner.reset.execute') }}" method="POST" @submit.prevent="confirmSubmit($event)" class="space-y-6 text-xs">
            @csrf

            <!-- 1. MASTER TOGGLE: RESET ALL -->
            <div class="p-4 rounded-xl border-2 transition cursor-pointer"
                :class="resetAll ? 'bg-red-50 border-red-500 shadow-xs' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                @click="toggleResetAll()">
                <div class="flex items-start space-x-3">
                    <input type="checkbox" name="reset_all" value="1" x-model="resetAll" @click.stop="toggleResetAll()" class="mt-0.5 w-4 h-4 rounded border-red-300 text-red-600 focus:ring-red-500">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-heading font-extrabold text-slate-900 text-sm">PILIH SEMUA: RESET SELURUH DATA SISTEM (FACTORY RESET)</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-600 text-white uppercase tracking-wider">Reset Total</span>
                        </div>
                        <p class="text-slate-500 text-[11px] mt-1 leading-relaxed">
                            Mencentang opsi ini akan memilih dan menghapus <strong>seluruh data operasional, transaksi, konsumen, feed komunitas, master paket, master barang, dan akun agen</strong> sekaligus. Aplikasi akan kembali bersih seperti baru diinstal.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. INDIVIDUAL CHECKBOX CARDS -->
            <div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
                    <label class="font-bold text-slate-800 text-xs uppercase tracking-wider">Atau Pilih Kategori Data Spesifik:</label>
                    <button type="button" @click="toggleSelectAllItems()" class="text-[11px] font-bold text-emerald-700 hover:underline">
                        <span x-text="allTargetsChecked() ? 'Batal Pilih Semua' : 'Centang Semua Pilihan'"></span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">

                    <!-- Target: Transaksi & Setoran -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                        :class="isTargetChecked('transactions') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="transactions" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-money-bill-transfer text-amber-600 mr-1.5 w-4 text-center"></i>
                                    Transaksi & Setoran Agen
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">
                                    {{ $counts['transactions']['payments'] }} Transaksi
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Mereset 30 minggu tagihan cicilan konsumen kembali ke belum bayar (unpaid) & menghapus seluruh berkas setoran kas agen ({{ $counts['transactions']['deposits'] }} setoran).
                            </p>
                        </div>
                    </label>

                    <!-- Target: Mini-POS Penyerahan -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                        :class="isTargetChecked('fulfillments') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="fulfillments" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-cash-register text-emerald-600 mr-1.5 w-4 text-center"></i>
                                    Mini-POS & Penyerahan Parsel
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700">
                                    {{ $counts['fulfillments']['total'] }} Struk Kasir
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Menghapus riwayat penyesuaian belanja parsel kasir mini & mereset status pemenuhan konsumen ke 'pending'.
                            </p>
                        </div>
                    </label>

                    <!-- Target: Konsumen & Paket Konsumen -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                        :class="isTargetChecked('customers') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="customers" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-users text-blue-600 mr-1.5 w-4 text-center"></i>
                                    Data Konsumen & Paket Binaan
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-blue-100 text-blue-800">
                                    {{ $counts['customers']['customers'] }} Orang
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Menghapus seluruh konsumen, paket yang mereka ambil ({{ $counts['customers']['packages'] }} paket), dan seluruh riwayat pembayaran terkait.
                            </p>
                        </div>
                    </label>

                    <!-- Target: Feed Sosial & Komunitas -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                        :class="isTargetChecked('social') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="social" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-brands fa-instagram text-pink-600 mr-1.5 w-4 text-center"></i>
                                    Feed Sosial & Komentar
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-pink-100 text-pink-800">
                                    {{ $counts['social']['posts'] }} Postingan
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Menghapus semua postingan foto dokumentasi agen di lapangan, riwayat likes, dan {{ $counts['social']['comments'] }} komentar.
                            </p>
                        </div>
                    </label>

                    <!-- Target: Master Paket Parsel & Tabungan -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                        :class="isTargetChecked('packages') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="packages" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-box-open text-emerald-600 mr-1.5 w-4 text-center"></i>
                                    Master Paket Parsel & Tabungan
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">
                                    {{ $counts['packages']['total'] }} Paket
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Menghapus master paket parsel barang, tabungan uang, eceran, dan daftar susunan barang paket.
                            </p>
                        </div>
                    </label>

                    <!-- Target: Master Barang -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                        :class="isTargetChecked('items') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="items" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-tags text-indigo-600 mr-1.5 w-4 text-center"></i>
                                    Daftar Master Barang (50 Item)
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800">
                                    {{ $counts['items']['total'] }} Barang
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Mengosongkan seluruh master barang biskuit, sirup, sembako, dan harga grosir belanja.
                            </p>
                        </div>
                    </label>

                    <!-- Target: Akun Agen Lapangan -->
                    <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3 md:col-span-2"
                        :class="isTargetChecked('agents') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                        <input type="checkbox" name="targets[]" value="agents" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-xs flex items-center">
                                    <i class="fa-solid fa-user-group text-slate-700 mr-1.5 w-4 text-center"></i>
                                    Akun Agen Lapangan
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-800">
                                    {{ $counts['agents']['total'] }} Akun Agen
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Menghapus akun login agen lapangan (Budi Santoso, Siti Aminah). <strong>Catatan:</strong> Akun Ibu Susi (Super Admin) tetap aman dan tidak akan terhapus.
                            </p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- 3. OPTIONAL: RESEED DEMO SAMPLES -->
            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="reseed_demo" value="1" x-model="reseedDemo" class="mt-0.5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <span class="font-bold text-emerald-950 text-xs">Muat Ulang Data Sampel Standar (Demo Seeder) Setelah Reset</span>
                        <p class="text-emerald-800/80 text-[11px] mt-0.5 leading-relaxed">
                            Jika dicentang, setelah pembersihan sistem akan otomatis mengisi kembali 50 Master Barang, 7 Paket Lebaran resmi dengan foto, data sampel konsumen, dan setoran simulasi. Sangat cocok jika Anda hanya ingin mengembalikan aplikasi ke kondisi demo awal yang rapi.
                        </p>
                    </div>
                </label>
            </div>

            <!-- 4. KEAMANAN & KONFIRMASI -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                <div>
                    <label class="block font-bold text-slate-800 text-xs mb-1">
                        Konfirmasi Keamanan Owner *
                    </label>
                    <p class="text-[11px] text-slate-500 mb-2">
                        Untuk mencegah ketidaksengajaan, ketik kata <span class="font-mono font-bold text-red-600 bg-red-50 px-1 py-0.5 rounded border border-red-200">RESET</span> atau masukkan <strong>kata sandi akun login Anda</strong>:
                    </p>
                    <input type="text" name="confirm_text" x-model="confirmText" required placeholder="Ketik kata RESET atau password akun Owner" class="w-full sm:w-80 p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-red-500 text-xs font-semibold">
                </div>
            </div>

            <!-- SUBMIT ACTION -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-[11px] text-slate-400">
                    Diproses langsung pada server database & file storage SPL.
                </div>
                <div class="flex items-center space-x-2 w-full sm:w-auto">
                    <a href="{{ route('owner.dashboard') }}" class="w-1/2 sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 font-semibold text-center transition">
                        Batal
                    </a>
                    <button type="submit"
                        :disabled="!hasValidSelection() || !confirmText"
                        :class="(!hasValidSelection() || !confirmText) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-red-600 hover:bg-red-700 shadow-md'"
                        class="w-1/2 sm:w-auto px-6 py-2.5 text-white font-bold rounded-xl transition flex items-center justify-center">
                        <i class="fa-solid fa-trash-can-arrow-up mr-2"></i>
                        <span>Eksekusi Reset Data</span>
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function dataResetHandler() {
    return {
        resetAll: false,
        targets: [],
        reseedDemo: false,
        confirmText: '',
        allTargetKeys: ['transactions', 'fulfillments', 'customers', 'social', 'packages', 'items', 'agents'],

        toggleResetAll() {
            if (this.resetAll) {
                this.targets = [...this.allTargetKeys];
            } else {
                this.targets = [];
            }
        },

        isTargetChecked(key) {
            return this.resetAll || this.targets.includes(key);
        },

        onTargetChange() {
            if (this.targets.length === this.allTargetKeys.length) {
                this.resetAll = true;
            } else {
                this.resetAll = false;
            }
        },

        allTargetsChecked() {
            return this.resetAll || (this.targets.length === this.allTargetKeys.length);
        },

        toggleSelectAllItems() {
            if (this.allTargetsChecked()) {
                this.resetAll = false;
                this.targets = [];
            } else {
                this.resetAll = true;
                this.targets = [...this.allTargetKeys];
            }
        },

        hasValidSelection() {
            return this.resetAll || this.targets.length > 0;
        },

        confirmSubmit(event) {
            const count = this.resetAll ? 'SEMUA DATA SISTEM' : (this.targets.length + ' kategori data');
            const noteReseed = this.reseedDemo ? '\n(Data sampel demo awal akan dimuat kembali setelah reset)' : '';
            
            const message = `APAKAH ANDA YAKIN INGIN MERESET: ${count}?${noteReseed}\n\nTindakan ini tidak dapat dibatalkan! Tekan OK untuk melanjutkan proses reset.`;
            
            if (confirm(message)) {
                event.target.submit();
            }
        }
    }
}
</script>
@endpush

