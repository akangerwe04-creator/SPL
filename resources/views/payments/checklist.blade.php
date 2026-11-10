@extends('layouts.app')

@section('title', 'Ceklis Tagihan - ' . $customerPackage->customer->name)
@section('page_title', 'Smart Payment Checklist')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        {{ $customerPackage->package->name }}
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="paymentChecklist()">

    <!-- TOP BREADCRUMB & RETURN LINK -->
    <div class="flex items-center justify-between text-xs text-slate-500">
        <a href="{{ route('customers.show', $customerPackage->customer) }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Profil Konsumen
        </a>
        <div class="font-mono">ID: {{ $customerPackage->customer->code }}</div>
    </div>

    <!-- RUNNING CART NOTIFICATION BANNER (BATCHING SYSTEM) -->
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-4 text-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg font-bold backdrop-blur">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-amber-100 uppercase tracking-wider">Keranjang Setoran Agen (Batching)</div>
                <div class="text-lg font-heading font-extrabold" id="banner-cart-total">
                    {{ $activeCart->formatted_total }}
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-amber-100 hidden md:inline">Ceklis otomatis terkumpul di keranjang pending</span>
            <a href="{{ route('agent.cart') }}" class="px-3.5 py-2 rounded-xl bg-white text-amber-800 font-bold text-xs hover:bg-amber-50 transition shadow-xs flex items-center">
                <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Buka Keranjang
            </a>
        </div>
    </div>

    <!-- CUSTOMER & PACKAGE INFORMATION CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 md:p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                    {{ ucfirst($customerPackage->package->type) }}
                </span>
                <h2 class="text-xl font-heading font-extrabold text-slate-900 mt-1">
                    {{ $customerPackage->customer->name }}
                </h2>
                <div class="text-xs text-slate-500 flex flex-wrap items-center gap-3 mt-1">
                    <span><i class="fa-solid fa-phone mr-1 text-slate-400"></i> {{ $customerPackage->customer->phone ?: 'Tidak ada nomor telepon' }}</span>
                    <span><i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> {{ $customerPackage->customer->address ?: 'Paseh' }}</span>
                    <span><i class="fa-solid fa-user-tie mr-1 text-slate-400"></i> Agen: <strong>{{ $customerPackage->agent->name }}</strong></span>
                </div>
            </div>

            <!-- FINANCIAL PROGRESS SUMMARY -->
            <div class="flex items-center space-x-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Total Masuk</div>
                    <div class="text-base font-heading font-extrabold text-emerald-700" id="customer-total-paid">
                        {{ $customerPackage->formatted_paid }}
                    </div>
                </div>
                @if(auth()->user()->isOwnerOrAdmin())
                    <div class="text-slate-300">/</div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Target Nominal</div>
                        <div class="text-base font-heading font-extrabold text-slate-800">
                            {{ $customerPackage->formatted_target }}
                        </div>
                    </div>
                @else
                    <div class="text-slate-300">•</div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Setoran Terbayar</div>
                        <div class="text-base font-heading font-extrabold text-slate-800">
                            {{ $customerPackage->paid_count }} / {{ $customerPackage->package->installment_count }} {{ $customerPackage->package->period_label }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- PROGRESS BAR -->
        <div class="pt-4">
            <div class="flex justify-between text-xs font-semibold mb-1">
                <span class="text-slate-600">Progres Pembayaran</span>
                <span class="text-emerald-700 font-bold" id="customer-progress-percent">
                    {{ $customerPackage->progress_percent }}%
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500" id="progress-bar-fill" style="width: {{ $customerPackage->progress_percent }}%"></div>
            </div>
        </div>

        <!-- TABUNGAN RULE NOTIFICATION: BONUS FORFEITED WARNING -->
        @if($customerPackage->package->isTabungan())
            <div class="mt-4 p-3.5 rounded-xl {{ $customerPackage->is_bonus_forfeited ? 'bg-red-50 border border-red-200 text-red-900' : 'bg-emerald-50 border border-emerald-200 text-emerald-900' }} flex items-start space-x-3 text-xs">
                <div class="mt-0.5 text-base {{ $customerPackage->is_bonus_forfeited ? 'text-red-600' : 'text-emerald-600' }}">
                    <i class="fa-solid {{ $customerPackage->is_bonus_forfeited ? 'fa-ban' : 'fa-gift' }}"></i>
                </div>
                <div class="flex-1">
                    @if($customerPackage->is_bonus_forfeited)
                        <div class="font-bold text-red-800 uppercase tracking-wide">STATUS: BONUS BARANG HANGUS</div>
                        <div class="text-red-700 mt-0.5">{{ $customerPackage->forfeit_reason }}</div>
                    @else
                        <div class="font-bold text-emerald-800">Bonus Barang Tabungan Aktif</div>
                        <div class="text-emerald-700 mt-0.5">
                            Aturan SPL: Bonus barang hangus apabila konsumen bolong menyetor selama <strong>3 minggu berturut-turut</strong>.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- LEGEND / PANDUAN WARNA KOTAK -->
    <div class="flex flex-wrap items-center justify-between gap-2 bg-white px-4 py-2.5 rounded-xl border border-slate-200 text-xs shadow-xs">
        <div class="font-bold text-slate-700 flex items-center">
            <i class="fa-solid fa-circle-info text-emerald-600 mr-1.5"></i> Status Tagihan:
        </div>
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[11px]">
            <div class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded bg-emerald-600 inline-block"></span>
                <span class="text-slate-700 font-semibold">Lunas Sah (Owner)</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded bg-blue-600 inline-block"></span>
                <span class="text-slate-700 font-semibold">Menunggu Validasi</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded bg-amber-400 inline-block"></span>
                <span class="text-slate-700 font-semibold">Keranjang Agen</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded bg-white border border-slate-300 inline-block"></span>
                <span class="text-slate-600 font-medium">Belum Bayar</span>
            </div>
        </div>
    </div>

    <!-- 30/40 WEEKS SMART PAYMENT GRID (COMPACT 10-COLUMNS) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="font-heading font-bold text-slate-900 text-sm sm:text-base">
                    Kotak Tagihan {{ $customerPackage->package->installment_label ?? 'Mingguan' }} ({{ $customerPackage->payments->count() }} {{ $customerPackage->package->period_label ?? 'Minggu' }})
                </h3>
                <span class="text-[11px] text-slate-400">Klik kotak untuk mencentang atau membatalkan ceklis tagihan</span>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 uppercase tracking-wider">
                Grid 10 Kolom
            </span>
        </div>

        <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-10 gap-1.5 sm:gap-2">
            @foreach($customerPackage->payments as $payment)
                @php
                    $isVerified = $payment->is_locked || $payment->status === 'verified' || ($payment->deposit && $payment->deposit->status === 'approved');
                    $isSubmitted = !$isVerified && $payment->deposit && $payment->deposit->status === 'submitted';
                    $isCart = !$isVerified && !$isSubmitted && $payment->is_paid;
                    $isUnpaid = !$payment->is_paid;
                @endphp

                <div 
                    id="payment-box-{{ $payment->id }}"
                    @click="togglePayment({{ $payment->id }}, {{ $isVerified ? 'true' : 'false' }}, {{ $isSubmitted ? 'true' : 'false' }})"
                    class="relative rounded-lg sm:rounded-xl p-1.5 border transition-all cursor-pointer select-none flex flex-col justify-between h-14 sm:h-16 text-center
                        {{ $isVerified ? 'bg-emerald-600 border-emerald-700 text-white shadow-xs' : '' }}
                        {{ $isSubmitted ? 'bg-blue-600 border-blue-700 text-white shadow-xs' : '' }}
                        {{ $isCart ? 'bg-amber-400 border-amber-500 text-amber-950 shadow-xs font-semibold' : '' }}
                        {{ $isUnpaid ? 'bg-white border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/20 text-slate-700 hover:shadow-xs' : '' }}"
                    title="{{ $isVerified ? 'Sudah disahkan Owner (Terkunci permanen)' : ($isSubmitted ? 'Sedang diajukan ke Owner (Menunggu validasi)' : ($isCart ? 'Dalam keranjang agen - Klik untuk batalkan' : 'Klik untuk ceklis bayar')) }}"
                >
                    <!-- TOP HEADER: PERIOD NUMBER & ICON -->
                    <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-bold leading-none">
                        <span class="opacity-90">{{ ($customerPackage->package && $customerPackage->package->isHarian()) ? 'H' : 'M' }}{{ $payment->period_number }}</span>
                        <span class="status-icon">
                            @if($isVerified)
                                <i class="fa-solid fa-lock text-[9px] text-emerald-200"></i>
                            @elseif($isSubmitted)
                                <i class="fa-solid fa-hourglass-half text-[9px] text-blue-200"></i>
                            @elseif($isCart)
                                <i class="fa-solid fa-basket-shopping text-[9px] text-amber-900"></i>
                            @else
                                <i class="fa-regular fa-square text-[9px] text-slate-300"></i>
                            @endif
                        </span>
                    </div>

                    <!-- CENTER NOMINAL -->
                    <div class="my-auto leading-none">
                        <div class="font-heading font-extrabold text-[10px] sm:text-xs tracking-tight">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- BOTTOM STATUS BADGE -->
                    <div class="status-badge text-[8px] sm:text-[9px] font-bold uppercase tracking-wider leading-none opacity-90">
                        @if($isVerified)
                            LUNAS
                        @elseif($isSubmitted)
                            MENUNGGU
                        @elseif($isCart)
                            KERANJANG
                        @else
                            BELUM
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- CANCELLATION / MUNDUR KONSUMEN BUTTON (ATURAN 10%) -->
    @if($customerPackage->status === 'active')
        <div class="bg-slate-100 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
            <div>
                <div class="font-bold text-slate-800">Pengajuan Berhenti / Batal di Tengah Jalan?</div>
                <div class="text-slate-500">Sesuai aturan SPL, pembatalan dikenakan potongan 10% dari total dana yang telah disetor.</div>
            </div>
            <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-800 font-bold transition flex-shrink-0">
                <i class="fa-solid fa-user-xmark mr-1"></i> Form Pembatalan (Potongan 10%)
            </button>
        </div>
    @endif

</div>

<!-- MODAL PEMBATALAN KONSUMEN -->
<div id="cancel-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-heading font-bold text-slate-900 text-base">Konfirmasi Pembatalan Konsumen</h3>
            <button onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('customers.package.cancel', $customerPackage) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl text-xs text-amber-900 space-y-1">
                <div><strong>Total Setoran Masuk:</strong> {{ $customerPackage->formatted_paid }}</div>
                <div><strong>Potongan Biaya SPL (10%):</strong> Rp {{ number_format($customerPackage->total_paid * 0.10, 0, ',', '.') }}</div>
                <div class="pt-1 border-t border-amber-200 text-emerald-800 font-bold">
                    <strong>Sisa Dana Dikembalikan (90%):</strong> Rp {{ number_format($customerPackage->total_paid * 0.90, 0, ',', '.') }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Berhenti / Pembatalan</label>
                <textarea name="reason" rows="3" required placeholder="Tuliskan alasan pembatalan konsumen..." class="w-full text-xs p-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-red-600 hover:bg-red-700 text-white transition">
                    Proses Pembatalan (10%)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentChecklist() {
    return {
        async togglePayment(paymentId, isVerified, isSubmitted) {
            if (isVerified) {
                alert('Ceklis ini sudah disahkan oleh Ibu Susi / Owner dan terkunci permanen.');
                return;
            }
            if (isSubmitted) {
                alert('Tagihan ini ada dalam berkas setoran yang sedang diajukan dan menunggu validasi Owner.');
                return;
            }

            const box = document.getElementById(`payment-box-${paymentId}`);
            if (!box) return;

            box.style.opacity = '0.5';

            try {
                const response = await fetch("{{ url('payments') }}/" + paymentId + "/toggle", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                box.style.opacity = '1';

                if (data.success) {
                    // Update running cart banner
                    if (data.formatted_cart_total) {
                        const cartElem = document.getElementById('banner-cart-total');
                        if (cartElem) cartElem.innerText = data.formatted_cart_total;
                    }

                    // Update customer paid display
                    if (data.formatted_customer_paid) {
                        const paidElem = document.getElementById('customer-total-paid');
                        if (paidElem) paidElem.innerText = data.formatted_customer_paid;
                    } else if (data.customer_paid !== undefined) {
                        const paidElem = document.getElementById('customer-total-paid');
                        if (paidElem) paidElem.innerText = 'Rp ' + Number(data.customer_paid).toLocaleString('id-ID');
                    }

                    // Update progress percent
                    if (data.customer_progress !== undefined) {
                        const progText = document.getElementById('customer-progress-percent');
                        if (progText) progText.innerText = data.customer_progress + '%';
                        const progBar = document.getElementById('progress-bar-fill');
                        if (progBar) progBar.style.width = data.customer_progress + '%';
                    }

                    // Toggle box styling
                    const iconElem = box.querySelector('.status-icon');
                    const badgeElem = box.querySelector('.status-badge');

                    if (data.action === 'checked') {
                        box.className = 'relative rounded-lg sm:rounded-xl p-1.5 border transition-all cursor-pointer select-none flex flex-col justify-between h-14 sm:h-16 text-center bg-amber-400 border-amber-500 text-amber-950 shadow-xs font-semibold';
                        if (iconElem) {
                            iconElem.innerHTML = '<i class="fa-solid fa-basket-shopping text-[9px] text-amber-900"></i>';
                        }
                        if (badgeElem) badgeElem.innerText = 'KERANJANG';
                    } else {
                        box.className = 'relative rounded-lg sm:rounded-xl p-1.5 border transition-all cursor-pointer select-none flex flex-col justify-between h-14 sm:h-16 text-center bg-white border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/20 text-slate-700 hover:shadow-xs';
                        if (iconElem) {
                            iconElem.innerHTML = '<i class="fa-regular fa-square text-[9px] text-slate-300"></i>';
                        }
                        if (badgeElem) badgeElem.innerText = 'BELUM';
                    }
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            } catch (err) {
                box.style.opacity = '1';
                console.error(err);
                alert('Gagal memproses ceklis. Silakan coba kembali.');
            }
        }
    }
}
</script>
@endpush

