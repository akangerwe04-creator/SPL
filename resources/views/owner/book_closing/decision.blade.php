@extends('layouts.app')

@section('title', 'Tentukan Keputusan Pemenuhan Tutup Buku')
@section('page_title', 'Keputusan Pemenuhan Tutup Buku Konsumen')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="decisionFormManager()">

    <a href="{{ route('owner.book-closing.index') }}" class="inline-flex items-center text-xs font-bold text-pink-600 hover:text-pink-700">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Menu Tutup Buku
    </a>

    <!-- DETAIL KONSUMEN & EVALUASI STATUS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
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
                    <i class="fa-solid fa-phone mr-1 text-slate-400"></i> {{ $customerPackage->customer?->phone ?? '-' }} • 
                    <i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> {{ $customerPackage->customer?->address ?? '-' }}
                </div>
            </div>

            <!-- BADGE EVALUASI TITIK AMAN -->
            <div class="text-right">
                @if($customerPackage->isSafeThresholdReached())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                        <i class="fa-solid fa-shield-check mr-1.5 text-sm text-emerald-600"></i> Lolos Titik Aman / Lunas
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5 text-sm text-amber-600"></i> Belum Capai Titik Aman
                    </span>
                @endif
                <div class="text-[10px] text-slate-500 font-medium mt-1">
                    Agen Pembina: <strong class="text-slate-800">{{ $customerPackage->agent?->name ?? 'Tanpa Agen' }}</strong>
                </div>
            </div>
        </div>

        <!-- STATISTIK KEUANGAN PAKET & FORMULA SPL -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Paket Pilihan</div>
                <div class="font-extrabold text-slate-900 text-xs mt-0.5 truncate">{{ $customerPackage->package?->name ?? '-' }}</div>
                <div class="text-[10px] text-slate-400 uppercase font-mono">{{ $customerPackage->package?->type }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Target Tagihan</div>
                <div class="font-black text-slate-900 text-sm font-mono mt-0.5">{{ $customerPackage->formatted_target }}</div>
                <div class="text-[10px] text-slate-400">{{ $customerPackage->package?->installment_count }}x setoran</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-pink-900 uppercase tracking-wider">Harga Semua Barang</div>
                <div class="font-black text-pink-900 text-sm font-mono mt-0.5">Rp {{ number_format($customerPackage->goods_value, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-400">Target - Uang Paket</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Setoran Masuk</div>
                <div class="font-black text-emerald-800 text-sm font-mono mt-0.5">{{ $customerPackage->formatted_paid }}</div>
                <div class="text-[10px] text-emerald-600 font-bold">{{ $customerPackage->progress_percent }}% Lunas</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-purple-900 uppercase tracking-wider">Titik Aman Paket</div>
                <div class="font-black text-purple-900 text-xs font-mono mt-0.5">
                    {{ $customerPackage->package?->hasSafeThreshold() ? $customerPackage->package->formatted_safe_threshold : 'Tanpa Titik Aman' }}
                </div>
                <div class="text-[10px] text-slate-500">
                    @if($customerPackage->package?->hasSafeThreshold())
                        {{ $customerPackage->isSafeThresholdReached() ? 'Terpenuhi (Aman)' : 'Kurang Rp ' . number_format(max(0, ($customerPackage->package->safe_threshold_nominal ?: 0) - $customerPackage->total_paid), 0, ',', '.') }}
                    @else
                        Diserahkan ke Owner
                    @endif
                </div>
            </div>
        </div>

        <!-- BANNER REKOMENDASI UANG TUNAI SPL -->
        <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
            <div class="space-y-0.5">
                <div class="font-black text-xs text-emerald-950 flex items-center">
                    <i class="fa-solid fa-calculator text-emerald-600 mr-2 text-sm"></i>
                    <span>Rekomendasi Uang Tunai Konsumen: <strong class="text-emerald-800 font-mono text-base ml-1">Rp {{ number_format($customerPackage->recommended_cash, 0, ',', '.') }}</strong></span>
                </div>
                <p class="text-[11px] text-emerald-900/90 font-medium">
                    Rumus SPL: Dari setoran masuk <strong>{{ $customerPackage->formatted_paid }}</strong> dikurangi total harga barang paket <strong>Rp {{ number_format($customerPackage->goods_value, 0, ',', '.') }}</strong> = Sisa hak uang tunai konsumen <strong>Rp {{ number_format($customerPackage->recommended_cash, 0, ',', '.') }}</strong>.
                </p>
            </div>
            <button type="button" @click="cashPayout = {{ (float)$customerPackage->recommended_cash }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shrink-0 shadow-xs transition flex items-center self-start sm:self-auto space-x-1.5">
                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>
                <span>Gunakan Rekomendasi (Rp {{ number_format($customerPackage->recommended_cash, 0, ',', '.') }})</span>
            </button>
        </div>
    </div>

    <!-- FORMULIR KEPUTUSAN OWNER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-heading font-extrabold text-slate-900 text-base">
                Formulir Penetapan Keputusan Pemenuhan Tutup Buku
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Pilih apakah konsumen mendapatkan seluruh barang standar paket atau disesuaikan dengan barang pilihan Owner, serta tentukan pencairan uang tunai.
            </p>
        </div>

        <form action="{{ route('owner.book-closing.store-decision', $customerPackage) }}" method="POST" class="space-y-6 text-xs">
            @csrf

            <!-- 1. MODE PEMENUHAN -->
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-2">1. Keputusan Mode Pemenuhan *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3"
                           :class="decisionType === 'safe_full' ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 font-bold' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="decision_type" value="safe_full" x-model="decisionType" class="mt-1 text-emerald-600">
                        <div>
                            <div class="text-xs font-bold">Jaminan Full Barang Paket</div>
                            <div class="text-[11px] font-normal text-slate-500 mt-0.5">
                                Konsumen menerima <strong>100% seluruh barang isi parsel</strong> standar paket secara utuh.
                            </div>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3"
                           :class="decisionType === 'custom_goods' ? 'border-blue-600 bg-blue-50/70 text-blue-950 font-bold' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="decision_type" value="custom_goods" x-model="decisionType" class="mt-1 text-blue-600">
                        <div>
                            <div class="text-xs font-bold">Kustomisasi Barang Pengganti</div>
                            <div class="text-[11px] font-normal text-slate-500 mt-0.5">
                                Owner yang memilihkan langsung barang apa saja dan berapa jumlahnya sesuai saldo stor.
                            </div>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3"
                           :class="decisionType === 'cash_only' ? 'border-amber-600 bg-amber-50/70 text-amber-950 font-bold' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="decision_type" value="cash_only" x-model="decisionType" class="mt-1 text-amber-600">
                        <div>
                            <div class="text-xs font-bold">Pencairan Dana Uang Tunai</div>
                            <div class="text-[11px] font-normal text-slate-500 mt-0.5">
                                Khusus tabungan atau pengembalian sisa uang tunai ke konsumen.
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. DAFTAR BARANG YANG DIBERIKAN (JIKA KUSTOMISASI BARANG) -->
            <div x-show="decisionType === 'custom_goods'" class="space-y-3 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-xs flex items-center">
                            <i class="fa-solid fa-boxes-stacked text-blue-600 mr-2"></i>
                            Pilihan Barang yang Diberikan (Owner yang Memilihkan)
                        </h4>
                        <p class="text-[11px] text-slate-500">Pilih dari katalog master item SPL beserta kuantitasnya.</p>
                    </div>
                    <button type="button" @click="addItemRow()" class="px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs flex items-center space-x-1">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>+ Tambah Barang</span>
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(row, idx) in selectedItems" :key="idx">
                        <div class="flex items-center gap-2 bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs">
                            <div class="w-8 text-center font-bold text-slate-400" x-text="idx + 1"></div>
                            <div class="flex-1 min-w-0">
                                <select :name="`items[${idx}][item_id]`" x-model="row.item_id" @change="onItemSelect(row)" class="w-full p-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:outline-none">
                                    <option value="">-- Pilih Barang Master SPL --</option>
                                    <template x-for="item in masterItems" :key="item.id">
                                        <option :value="item.id" x-text="`${item.name} (${item.category || 'Barang'}) - Rp ${Number(item.unit_price).toLocaleString('id-ID')}`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="w-24">
                                <input type="number" :name="`items[${idx}][quantity]`" x-model.number="row.quantity" min="1" placeholder="Qty" class="w-full p-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-center font-bold">
                            </div>
                            <div class="w-32 text-right font-mono font-bold text-slate-800" x-text="formatRp(row.unit_price * row.quantity)"></div>
                            <button type="button" @click="removeItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-200 font-bold text-xs">
                    <span class="text-slate-700">Total Nilai Barang Pengganti:</span>
                    <span class="font-black text-pink-700 font-mono text-sm" x-text="formatRp(calculateItemsTotal())"></span>
                </div>
            </div>

            <!-- 3. PENETAPAN UANG TUNAI & POTONGAN ADMIN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-amber-50/50 border-2 border-amber-300 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block font-black text-amber-950 text-xs">
                            2. Pencairan Uang Tunai yang Diberikan (Rp)
                        </label>
                        <button type="button" @click="cashPayout = {{ (float)$customerPackage->recommended_cash }}" class="text-[10px] font-extrabold text-emerald-700 hover:text-emerald-900 underline">
                            Pakai Rekomendasi (Rp {{ number_format($customerPackage->recommended_cash, 0, ',', '.') }})
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-amber-700 text-sm pointer-events-none">Rp</span>
                        <input type="number" name="cash_payout" x-model.number="cashPayout" min="0" step="any" placeholder="0" class="w-full pl-10 pr-3 py-2.5 bg-white border border-amber-300 rounded-xl font-mono font-black text-amber-950 text-sm focus:outline-none focus:border-amber-600 shadow-2xs">
                    </div>
                    <p class="text-[10px] text-amber-900/80 font-medium">
                        Jika paket memiliki unsur uang tunai (seperti Tabungan SPL atau Parsel Berisi Uang), tentukan berapa rupiah yang dicairkan kepada konsumen.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-300 space-y-1.5">
                    <label class="block font-bold text-slate-800 text-xs">
                        3. Potongan Biaya Administrasi SPL (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-500 text-sm pointer-events-none">Rp</span>
                        <input type="number" name="admin_fee" x-model.number="adminFee" min="0" step="1000" placeholder="0" class="w-full pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl font-mono font-bold text-slate-900 text-sm focus:outline-none focus:border-slate-500 shadow-2xs">
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium">
                        Potongan admin fee atau penalti sesuai kebijakan SPL (jika ada).
                    </p>
                </div>
            </div>

            <!-- 4. PEMBERIAN BONUS & CATATAN SERAH TERIMA -->
            <div class="space-y-3">
                <label class="flex items-center space-x-3 cursor-pointer p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100/50 transition">
                    <input type="checkbox" name="grant_bonus" value="1" x-model="grantBonus" class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500">
                    <div>
                        <div class="font-bold text-slate-800 text-xs">Berikan Hadiah Bonus Paket Parsel / Tabungan</div>
                        <div class="text-[10px] text-slate-500">Konsumen tetap berhak atas seluruh hadiah bonus paket standar saat Tutup Buku.</div>
                    </div>
                </label>

                <div>
                    <label class="block font-bold text-slate-800 text-xs mb-1">Catatan Keputusan Serah Terima (Dicetak pada Berita Acara)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan khusus serah terima barang atau pencairan dana dari Owner..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:border-pink-500"></textarea>
                </div>
            </div>

            <!-- SUBMIT BUTTONS -->
            <div class="flex items-center justify-end space-x-2.5 pt-4 border-t border-slate-200">
                <a href="{{ route('owner.book-closing.index') }}" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    <span>Simpan Keputusan Tutup Buku & Alokasikan Belanja</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function decisionFormManager() {
    return {
        decisionType: '{{ old('decision_type', $customerPackage->fulfillment_decision ?: ($customerPackage->isSafeThresholdReached() ? 'safe_full' : 'custom_goods')) }}',
        cashPayout: {{ old('cash_payout', $customerPackage->partialFulfillment ? (float)$customerPackage->partialFulfillment->cash_payout : (float)$customerPackage->recommended_cash) }},
        adminFee: {{ old('admin_fee', $customerPackage->partialFulfillment ? (float)$customerPackage->partialFulfillment->admin_fee : 0) }},
        grantBonus: {{ old('grant_bonus', !$customerPackage->is_bonus_forfeited ? 'true' : 'false') }},
        masterItems: @json($masterItems),
        selectedItems: [
            @if($customerPackage->partialFulfillment && $customerPackage->partialFulfillment->items->count() > 0)
                @foreach($customerPackage->partialFulfillment->items as $it)
                    { item_id: '{{ $it->item_id }}', item_name: '{{ addslashes($it->item?->name ?? '') }}', unit_price: {{ (float)($it->unit_price ?: 0) }}, quantity: {{ (int)$it->quantity }}, is_bonus: {{ $it->is_bonus ? 'true' : 'false' }} },
                @endforeach
            @elseif($customerPackage->package && $customerPackage->package->packageItems->count() > 0)
                @foreach($customerPackage->package->packageItems as $pi)
                    { item_id: '{{ $pi->item_id }}', item_name: '{{ addslashes($pi->item?->name ?? '') }}', unit_price: {{ (float)($pi->item?->unit_price ?: 0) }}, quantity: {{ (int)$pi->quantity }}, is_bonus: {{ $pi->is_bonus ? 'true' : 'false' }} },
                @endforeach
            @endif
        ],
        addItemRow() {
            this.selectedItems.push({
                item_id: '',
                item_name: '',
                unit_price: 0,
                quantity: 1,
                is_bonus: false
            });
        },
        removeItemRow(idx) {
            this.selectedItems.splice(idx, 1);
        },
        onItemSelect(row) {
            const found = this.masterItems.find(m => m.id == row.item_id);
            if (found) {
                row.item_name = found.name;
                row.unit_price = Number(found.unit_price || 0);
            }
        },
        calculateItemsTotal() {
            return this.selectedItems.reduce((acc, r) => acc + (Number(r.unit_price || 0) * Number(r.quantity || 1)), 0);
        },
        formatRp(num) {
            return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endpush

