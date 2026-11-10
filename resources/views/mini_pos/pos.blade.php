@extends('layouts.app')

@section('title', 'Kasir Mini-POS Penyesuaian - ' . $customerPackage->customer->name)
@section('page_title', 'Mini-POS Kasir Penyesuaian Barang')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
        Kasir Mini Penyesuaian
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="miniPosApp({{ $customerPackage->total_paid }})">

    <!-- TOP RETURN & INFO BANNER -->
    <div class="flex items-center justify-between text-xs text-slate-500">
        <a href="{{ route('mini-pos.index') }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Tunggakan
        </a>
        <div class="font-mono">Konsumen: {{ $customerPackage->customer->name }}</div>
    </div>

    <!-- MAIN TWO-COLUMN SPLIT (LEFT: CART & BUDGET DEDUCTION, RIGHT: CATALOG) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: CASHER CART & REALTIME BUDGET DEDUCTION (5 COLS) -->
        <div class="lg:col-span-5 space-y-4 sticky top-20">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-5 overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keranjang Pengganti</span>
                        <h3 class="font-heading font-extrabold text-slate-900 text-base">Kasir Penyesuaian</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                        <span x-text="cart.length"></span> Barang Dipilih
                    </span>
                </div>

                <!-- CONSUMER & BUDGET METRICS -->
                <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Konsumen:</span>
                        <strong class="text-slate-900">{{ $customerPackage->customer->name }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Paket Terdaftar:</span>
                        <span class="font-medium text-slate-800">{{ $customerPackage->package->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600 pt-2 border-t border-slate-200">
                        <span class="font-bold text-slate-700">Saldo Setoran Konsumen:</span>
                        <span class="font-extrabold text-base text-slate-900" x-text="formatRupiah(budget)"></span>
                    </div>

                    <!-- POTONGAN BIAYA ADMIN SPL -->
                    <div class="pt-2 border-t border-slate-200 space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-indigo-700 flex items-center">
                                <i class="fa-solid fa-percent mr-1 text-[10px]"></i> Potongan Biaya Admin:
                            </span>
                            <span class="font-bold text-indigo-700 font-mono" x-text="'- ' + formatRupiah(adminFee)"></span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[11px] font-bold text-slate-400">Rp</span>
                            <input 
                                type="number" 
                                min="0" 
                                :max="budget" 
                                step="1000"
                                x-model.number="adminFee" 
                                @input="onAdminFeeChange()"
                                placeholder="0" 
                                class="w-full text-xs font-bold text-slate-800 pl-8 pr-2.5 py-1.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-indigo-600"
                            >
                        </div>
                        <div class="flex flex-wrap items-center gap-1 pt-0.5">
                            <button type="button" @click="setAdminNominal(0)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 transition">
                                Rp 0
                            </button>
                            <button type="button" @click="setAdminPercent(5)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition">
                                5%
                            </button>
                            <button type="button" @click="setAdminPercent(10)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 hover:bg-indigo-200 text-indigo-800 transition">
                                10%
                            </button>
                            <button type="button" @click="setAdminNominal(15000)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                15rb
                            </button>
                            <button type="button" @click="setAdminNominal(25000)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                25rb
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-slate-800 pt-2 border-t border-slate-200 font-bold bg-emerald-50/60 p-2 rounded-lg border border-emerald-200/60">
                        <span class="text-emerald-900">Maks. Anggaran Belanja Barang:</span>
                        <span class="font-extrabold text-sm text-emerald-700" x-text="formatRupiah(availableBudget)"></span>
                    </div>
                </div>

                <!-- REALTIME DEDUCTION CARD -->
                <div class="mt-4 p-4 rounded-xl border-2 transition-all duration-300"
                    :class="isOverBudget ? 'bg-red-50 border-red-500 text-red-900 animate-pulse' : 'bg-emerald-50 border-emerald-400 text-emerald-950'">
                    
                    <div class="flex justify-between items-center text-xs font-semibold mb-1">
                        <span>Total Nilai Barang Dipilih:</span>
                        <span class="text-base font-extrabold" :class="isOverBudget ? 'text-red-700' : 'text-slate-900'" x-text="formatRupiah(totalCart)"></span>
                    </div>

                    <div class="flex justify-between items-center text-xs font-semibold pt-2 border-t"
                        :class="isOverBudget ? 'border-red-200' : 'border-emerald-200'">
                        <span x-text="isOverBudget ? 'Defisit / Melebihi Anggaran:' : 'Sisa Saldo Uang Tunai Dikembalikan:'"></span>
                        <span class="text-base font-extrabold"
                            :class="isOverBudget ? 'text-red-700' : 'text-emerald-700'"
                            x-text="formatRupiah(Math.abs(remaining))"></span>
                    </div>

                    <!-- VISUAL RED WARNING IF OVER BUDGET -->
                    <template x-if="isOverBudget">
                        <div class="mt-3 p-2 bg-red-600 text-white rounded-lg text-xs font-bold flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                            <span>PERINGATAN: TOTAL BELANJA BARANG MELEBIHI ANGGARAN (SETELAH POTONGAN ADMIN)!</span>
                        </div>
                    </template>
                </div>

                <!-- SELECTED ITEMS TABLE -->
                <div class="mt-4 max-h-72 overflow-y-auto space-y-2 pr-1">
                    <template x-if="cart.length === 0">
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-cart-shopping text-3xl mb-2 text-slate-300"></i>
                            <div>Belum ada barang pengganti yang dipilih.</div>
                            <div class="text-[10px] text-slate-400">Pilih dari katalog di sebelah kanan &rarr;</div>
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-white hover:bg-slate-50 transition text-xs">
                            <div class="flex-1 pr-2">
                                <div class="font-bold text-slate-800" x-text="item.name"></div>
                                <div class="text-[10px] text-slate-400">
                                    <span x-text="formatRupiah(item.price)"></span> / <span x-text="item.unit"></span>
                                </div>
                            </div>

                            <!-- QTY CONTROLLER -->
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="decreaseQty(index)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                    -
                                </button>
                                <span class="w-6 text-center font-bold text-slate-800" x-text="item.qty"></span>
                                <button type="button" @click="increaseQty(index)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                    +
                                </button>
                            </div>

                            <div class="w-20 text-right font-extrabold text-slate-900 ml-2" x-text="formatRupiah(item.price * item.qty)"></div>

                            <button type="button" @click="removeItem(index)" class="text-slate-300 hover:text-red-500 ml-2 p-1">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- SUBMIT ADJUSTMENT FORM -->
                <form action="{{ route('mini-pos.adjust', $customerPackage) }}" method="POST" class="mt-5 pt-4 border-t border-slate-100 space-y-3">
                    @csrf
                    <!-- HIDDEN INPUT FOR ADMIN FEE -->
                    <input type="hidden" name="admin_fee" :value="adminFee">

                    <!-- HIDDEN INPUTS FOR CART DATA -->
                    <template x-for="(item, idx) in cart" :key="item.id">
                        <div>
                            <input type="hidden" :name="`items[${idx}][item_id]`" :value="item.id">
                            <input type="hidden" :name="`items[${idx}][quantity]`" :value="item.qty">
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kesepakatan Penyesuaian</label>
                        <input type="text" name="notes" placeholder="Misal: Kesepakatan ganti barang dengan Ibu Dewi..." class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                    </div>

                    <button type="submit" 
                        :disabled="cart.length === 0 || isOverBudget"
                        :class="(cart.length === 0 || isOverBudget) ? 'bg-slate-300 cursor-not-allowed text-slate-500' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm cursor-pointer'"
                        class="w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition flex items-center justify-center">
                        <i class="fa-solid fa-check-double mr-1.5"></i> Simpan & Terbitkan Berita Acara
                    </button>
                </form>

            </div>
        </div>

        <!-- RIGHT COLUMN: PRODUCT CATALOG WITH SEARCH & CATEGORY FILTER (7 COLS) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-heading font-extrabold text-slate-900 text-base">Katalog Barang Pengganti</h3>
                        <div class="text-xs text-slate-400">Pilih barang master untuk menggantikan paket parsel yang belum lunas</div>
                    </div>
                </div>

                <!-- SEARCH & CATEGORY FILTER -->
                <div class="pt-4 space-y-3">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama barang (Marjan, Khong Guan, Minyak...)" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- CATEGORY PILLS -->
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="selectedCategory = ''"
                            :class="selectedCategory === '' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg text-[11px] transition">
                            Semua
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" @click="selectedCategory = '{{ $cat }}'"
                                :class="selectedCategory === '{{ $cat }}' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[11px] transition">
                                {{ $cat }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- PRODUCT GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-4">
                    @foreach($items as $item)
                        <div 
                            x-show="matchesFilter('{{ strtolower($item->name) }}', '{{ $item->category }}')"
                            class="p-3 rounded-xl border border-slate-200 hover:border-emerald-400 hover:shadow-xs transition bg-white flex flex-col justify-between"
                        >
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">
                                    {{ $item->category ?: 'Umum' }}
                                </span>
                                <h4 class="font-bold text-xs text-slate-800 mt-1 line-clamp-2" title="{{ $item->name }}">
                                    {{ $item->name }}
                                </h4>
                            </div>

                            <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="font-extrabold text-xs text-emerald-700">
                                        {{ $item->formatted_price }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">/ {{ $item->base_unit }}</div>
                                </div>

                                <button type="button" 
                                    @click="addItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->unit_price }}, '{{ $item->base_unit }}')"
                                    class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white font-bold text-xs flex items-center justify-center transition">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function miniPosApp(budgetAmount) {
    return {
        budget: Number(budgetAmount),
        adminFee: 0,
        cart: [],
        searchQuery: '',
        selectedCategory: '',

        get availableBudget() {
            return Math.max(0, this.budget - this.adminFee);
        },

        get totalCart() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        get remaining() {
            return this.budget - this.totalCart - this.adminFee;
        },

        get isOverBudget() {
            return (this.totalCart + this.adminFee) > this.budget;
        },

        setAdminPercent(pct) {
            this.adminFee = Math.round(this.budget * (pct / 100));
        },

        setAdminNominal(val) {
            this.adminFee = Math.min(this.budget, Number(val) || 0);
        },

        onAdminFeeChange() {
            if (this.adminFee < 0) this.adminFee = 0;
            if (this.adminFee > this.budget) this.adminFee = this.budget;
        },

        addItem(id, name, price, unit) {
            const existing = this.cart.find(i => i.id === id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({ id, name, price, unit, qty: 1 });
            }
        },

        increaseQty(index) {
            this.cart[index].qty++;
        },

        decreaseQty(index) {
            if (this.cart[index].qty > 1) {
                this.cart[index].qty--;
            } else {
                this.removeItem(index);
            }
        },

        removeItem(index) {
            this.cart.splice(index, 1);
        },

        matchesFilter(itemName, category) {
            const matchesSearch = !this.searchQuery || itemName.includes(this.searchQuery.toLowerCase());
            const matchesCat = !this.selectedCategory || category === this.selectedCategory;
            return matchesSearch && matchesCat;
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush

