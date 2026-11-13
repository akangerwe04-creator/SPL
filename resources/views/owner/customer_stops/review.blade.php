@extends('layouts.app')

@section('title', 'Tentukan Barang & ACC Stop Konsumen')
@section('page_title', 'Penetapan Barang Pengganti Stop Konsumen')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="stopReviewManager()">

    <a href="{{ route('owner.customer-stops.index') }}" class="inline-flex items-center text-xs font-bold text-pink-600 hover:text-pink-700">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Antrean Konfirmasi Stop
    </a>

    <!-- DETAIL KONSUMEN & PENGAJUAN AGEN -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-pink-100 text-pink-800 font-mono">
                        {{ $customerPackage->customer?->code ?? '-' }}
                    </span>
                    <h2 class="text-base font-heading font-extrabold text-slate-900">
                        {{ $customerPackage->customer?->name }}
                    </h2>
                </div>
                <div class="text-xs text-slate-500">
                    <i class="fa-solid fa-phone mr-1 text-slate-400"></i> {{ $customerPackage->customer?->phone ?: '-' }} • 
                    <i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> {{ $customerPackage->customer?->address ?: 'Paseh' }}
                </div>
            </div>

            <div class="sm:text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                    <i class="fa-solid fa-clock mr-1.5 text-amber-600"></i> Pengajuan Berhenti (Stop)
                </span>
                <div class="text-[11px] text-slate-600 font-medium mt-1">
                    Agen Penanggung Jawab: <strong class="text-slate-800">{{ $customerPackage->agent?->name ?? 'Tanpa Agen' }}</strong>
                </div>
            </div>
        </div>

        <!-- ALASAN BERHENTI DARI AGEN -->
        <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200 space-y-1">
            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-900 flex items-center">
                <i class="fa-solid fa-comment-dots text-amber-600 mr-1.5"></i> Alasan Konsumen Tidak Melanjutkan (Dari Agen)
            </div>
            <p class="text-xs text-amber-950 font-medium italic leading-relaxed">
                "{{ $customerPackage->stop_reason ?: 'Tidak ada alasan khusus yang dicantumkan' }}"
            </p>
            <div class="text-[10px] text-amber-800/80 pt-1">
                Diajukan oleh agen pada: {{ $customerPackage->stop_requested_at ? $customerPackage->stop_requested_at->format('d F Y, H:i') : '-' }}
            </div>
        </div>

        <!-- STATISTIK KEUANGAN & TOTAL SETORAN MASUK (FOKUS UTAMA) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-slate-900 text-white shadow-xs">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Paket Pilihan</div>
                <div class="font-extrabold text-white text-xs mt-0.5 truncate">{{ $customerPackage->package?->name ?? '-' }}</div>
                <div class="text-[10px] text-slate-400 uppercase font-mono">{{ $customerPackage->package?->type }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Awal</div>
                <div class="font-black text-white text-xs font-mono mt-0.5">{{ $customerPackage->formatted_target }}</div>
                <div class="text-[10px] text-slate-400">{{ $customerPackage->package?->installment_count }}x setoran</div>
            </div>

            <div class="col-span-2 bg-gradient-to-r from-emerald-600 to-teal-600 -m-2 p-3.5 rounded-xl border border-emerald-400/30 flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-emerald-100 flex items-center">
                        <i class="fa-solid fa-wallet mr-1 text-xs"></i> TOTAL SETORAN MASUK KONSUMEN
                    </div>
                    <div class="font-black text-white text-xl font-mono mt-0.5">
                        {{ $customerPackage->formatted_paid }}
                    </div>
                    <div class="text-[10px] text-emerald-100">
                        Dasar perhitungan nilai barang yang wajib diberikan
                    </div>
                </div>
                <div class="text-right hidden sm:block">
                    <span class="px-2.5 py-1 rounded-lg bg-white/20 text-white font-extrabold text-xs">
                        {{ $customerPackage->progress_percent }}% Dari Target
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULIR PENETAPAN BARANG OLEH OWNER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base flex items-center">
                    <i class="fa-solid fa-boxes-stacked mr-2 text-pink-600"></i>
                    Penetapan Barang Pengganti Sesuai Setoran Masuk
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pastikan Anda telah menentukan komoditas barang apa saja yang akan diserahkan kepada konsumen senilai <strong class="text-slate-900 font-mono">{{ $customerPackage->formatted_paid }}</strong>.
                </p>
            </div>
            <button type="button" @click="addItemRow()" class="px-3.5 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-extrabold text-xs border border-pink-200 transition flex items-center self-start sm:self-auto space-x-1.5 shadow-2xs">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Barang Lain</span>
            </button>
        </div>

        <form action="{{ route('owner.customer-stops.approve', $customerPackage) }}" method="POST" class="space-y-6 text-xs" @submit="return validateForm($event)">
            @csrf

            <!-- 1. DAFTAR PEMILIHAN BARANG PENGGANTI (DYNAMIC ALPINE.JS) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span>Daftar Barang yang Diberikan ke Konsumen:</span>
                    <span class="text-[11px] text-slate-400 font-normal">Pilih komoditas dari katalog SPL</span>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(row, idx) in selectedItems" :key="idx">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center gap-2.5 transition hover:border-pink-300">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Pilih Komoditas / Barang</label>
                                <select :name="`items[${idx}][item_id]`" x-model="row.item_id" @change="onItemChange(idx)" required class="w-full p-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:outline-none focus:border-pink-500">
                                    <option value="">-- Pilih Barang Komoditas --</option>
                                    <template x-for="item in masterItems" :key="item.id">
                                        <option :value="item.id" x-text="`${item.name} (${item.category || 'Barang'}) - Rp ${Number(item.unit_price).toLocaleString('id-ID')}`"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="w-full sm:w-28">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 text-center">Jumlah (Qty)</label>
                                <div class="flex items-center space-x-1">
                                    <button type="button" @click="if(row.quantity > 1) row.quantity--" class="px-2 py-1.5 bg-slate-200 hover:bg-slate-300 rounded-lg font-bold text-slate-700">-</button>
                                    <input type="number" :name="`items[${idx}][quantity]`" x-model.number="row.quantity" min="1" required class="w-full p-1.5 bg-white border border-slate-300 rounded-lg text-xs text-center font-bold font-mono focus:outline-none focus:border-pink-500">
                                    <button type="button" @click="row.quantity++" class="px-2 py-1.5 bg-slate-200 hover:bg-slate-300 rounded-lg font-bold text-slate-700">+</button>
                                </div>
                            </div>

                            <div class="w-full sm:w-32">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 text-right">Harga Satuan (Rp)</label>
                                <input type="number" :name="`items[${idx}][unit_price]`" x-model.number="row.unit_price" min="0" step="100" class="w-full p-2 bg-white border border-slate-300 rounded-lg text-xs text-right font-mono font-bold focus:outline-none focus:border-pink-500">
                            </div>

                            <div class="w-full sm:w-36 text-right">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Subtotal Nilai</label>
                                <div class="font-mono font-black text-pink-700 text-sm py-1.5" x-text="formatRp(row.unit_price * row.quantity)"></div>
                            </div>

                            <div class="sm:pt-4 flex justify-end">
                                <button type="button" @click="removeItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-2 rounded-lg hover:bg-rose-50 transition" title="Hapus baris barang">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- TOMBOL TAMBAH BARANG KOSONG JIKA BELUM ADA -->
                <div x-show="selectedItems.length === 0" class="p-8 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 space-y-2">
                    <i class="fa-solid fa-cart-flatbed text-3xl text-slate-300"></i>
                    <div class="font-bold text-slate-700">Belum Ada Barang yang Ditentukan</div>
                    <p class="text-xs text-slate-500">Klik tombol di bawah untuk memilih komoditas barang yang akan diberikan kepada konsumen.</p>
                    <button type="button" @click="addItemRow()" class="mt-2 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                        <i class="fa-solid fa-plus mr-1"></i> Pilih Barang Pengganti
                    </button>
                </div>
            </div>

            <!-- 2. REKAPITULASI FINANSIAL & SELISIH REALTIME -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-pink-50/40 border border-slate-200 space-y-3">
                <div class="font-bold text-xs text-slate-800 border-b border-slate-200 pb-2 flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fa-solid fa-scale-balanced text-pink-600 mr-1.5"></i>
                        Rekapitulasi Nilai Barang vs Setoran Masuk
                    </span>
                    <span class="text-[10px] text-slate-500">Perhitungan Real-Time</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 bg-white rounded-xl border border-slate-200">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Setoran Masuk Konsumen</div>
                        <div class="font-black text-slate-900 text-sm font-mono mt-0.5">
                            Rp {{ number_format($customerPackage->total_paid, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-pink-200">
                        <div class="text-[10px] font-bold text-pink-700 uppercase">Total Nilai Barang Ditentukan</div>
                        <div class="font-black text-pink-700 text-sm font-mono mt-0.5" x-text="formatRp(calculateItemsTotal())">
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-xl border" :class="calculateRemainingRefund() >= 0 ? 'border-emerald-300 bg-emerald-50/30' : 'border-rose-300 bg-rose-50/30'">
                        <div class="text-[10px] font-bold uppercase" :class="calculateRemainingRefund() >= 0 ? 'text-emerald-800' : 'text-rose-700'">
                            <span x-text="calculateRemainingRefund() >= 0 ? 'Sisa Kembalian Uang Tunai' : 'Nilai Barang Melebihi Setoran'"></span>
                        </div>
                        <div class="font-black text-sm font-mono mt-0.5" :class="calculateRemainingRefund() >= 0 ? 'text-emerald-800' : 'text-rose-700'" x-text="formatRp(Math.abs(calculateRemainingRefund()))">
                        </div>
                    </div>
                </div>

                <!-- OPSIONAL: POTONGAN ADMIN ATAU PENGATURAN CASH REFUND -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            Kembalian Uang Tunai Konsumen (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-500 pointer-events-none">Rp</span>
                            <input type="number" name="cash_refund" x-model.number="cashRefund" min="0" step="any" placeholder="0" class="w-full pl-10 pr-3 py-2 bg-white border border-slate-300 rounded-xl font-mono font-bold text-xs focus:outline-none focus:border-pink-500">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5">Otomatis disesuaikan dari sisa setoran setelah dikurangi nilai barang.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            Potongan Biaya Administrasi SPL (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-500 pointer-events-none">Rp</span>
                            <input type="number" name="admin_fee" x-model.number="adminFee" min="0" step="1000" placeholder="0" class="w-full pl-10 pr-3 py-2 bg-white border border-slate-300 rounded-xl font-mono font-bold text-xs focus:outline-none focus:border-pink-500">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5">Biaya penalti administrasi jika berlaku (default Rp 0).</p>
                    </div>
                </div>
            </div>

            <!-- 3. CATATAN SERAH TERIMA -->
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">
                    Catatan Serah Terima Barang dari Owner (Dicetak di Tanda Terima)
                </label>
                <textarea name="notes" rows="2" placeholder="Contoh: Barang diserahkan melalui agen lapangan. Kondisi barang telah diperiksa dan disetujui konsumen." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:border-pink-500"></textarea>
            </div>

            <!-- ALERT PERINGATAN VALIDASI -->
            <div x-show="selectedItems.length === 0" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-center space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span><strong>Perhatian:</strong> Sesuai SOP, Owner wajib menentukan minimal 1 jenis barang yang diberikan sebelum pengajuan stop dapat di-ACC.</span>
            </div>

            <!-- SUBMIT BUTTONS -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                <a href="{{ route('owner.customer-stops.index') }}" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition">
                    Batal
                </a>

                <div class="flex items-center space-x-2">
                    <button type="submit" :disabled="selectedItems.length === 0" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-md transition flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Setujui (ACC) & Alokasikan Barang Pengganti</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function stopReviewManager() {
    const totalPaid = {{ (float)$customerPackage->total_paid }};
    const masterItems = @json($masterItems);

    return {
        totalPaid: totalPaid,
        masterItems: masterItems,
        adminFee: {{ old('admin_fee', 0) }},
        cashRefund: 0,
        selectedItems: [
            @if($customerPackage->partialFulfillment && $customerPackage->partialFulfillment->items->count() > 0)
                @foreach($customerPackage->partialFulfillment->items as $it)
                    { item_id: '{{ $it->item_id }}', quantity: {{ (int)$it->quantity }}, unit_price: {{ (float)($it->unit_price ?: 0) }} },
                @endforeach
            @elseif($customerPackage->package && $customerPackage->package->packageItems->count() > 0)
                {{-- Sugestikan barang dari paket aslinya sebagai kemudahan untuk owner --}}
                @foreach($customerPackage->package->packageItems->take(3) as $pi)
                    @if($pi->item && !$pi->item->isUang())
                        { item_id: '{{ $pi->item_id }}', quantity: 1, unit_price: {{ (float)($pi->item->unit_price ?: 0) }} },
                    @endif
                @endforeach
            @endif
        ],

        addItemRow() {
            // Default ambil item pertama di master jika ada
            const firstItem = this.masterItems.length > 0 ? this.masterItems[0] : null;
            this.selectedItems.push({
                item_id: firstItem ? firstItem.id : '',
                quantity: 1,
                unit_price: firstItem ? Number(firstItem.unit_price) : 0,
            });
            this.syncRefund();
        },

        removeItemRow(index) {
            this.selectedItems.splice(index, 1);
            this.syncRefund();
        },

        onItemChange(index) {
            const row = this.selectedItems[index];
            const found = this.masterItems.find(i => String(i.id) === String(row.item_id));
            if (found) {
                row.unit_price = Number(found.unit_price);
            }
            this.syncRefund();
        },

        calculateItemsTotal() {
            return this.selectedItems.reduce((acc, row) => {
                const sub = (Number(row.unit_price) || 0) * (Number(row.quantity) || 1);
                return acc + sub;
            }, 0);
        },

        calculateRemainingRefund() {
            const itemsTotal = this.calculateItemsTotal();
            const fee = Number(this.adminFee) || 0;
            return this.totalPaid - itemsTotal - fee;
        },

        syncRefund() {
            const diff = this.calculateRemainingRefund();
            this.cashRefund = diff > 0 ? diff : 0;
        },

        formatRp(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        validateForm(e) {
            if (this.selectedItems.length === 0) {
                alert('Peringatan: Anda wajib menentukan minimal 1 barang sebelum melakukan ACC.');
                e.preventDefault();
                return false;
            }
            return true;
        },

        init() {
            this.syncRefund();
        }
    }
}
</script>
@endpush
