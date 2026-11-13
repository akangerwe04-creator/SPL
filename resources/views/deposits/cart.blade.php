@extends('layouts.app')

@section('title', 'Keranjang Setoran Lapangan')
@section('page_title', 'Keranjang Setoran Agen (Batching)')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
        Status: Aktif Pending
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- HERO KERANJANG RUNNING TOTAL -->
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-600 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-[11px] font-bold text-amber-100 uppercase tracking-wider mb-1">
                Keranjang No. {{ $cart->deposit_number }}
            </div>
            <div class="text-3xl font-heading font-extrabold tracking-tight">
                {{ $cart->formatted_total }}
            </div>
            <p class="text-xs text-amber-100 mt-1 max-w-md">
                Akumulasi tagihan dari ceklis yang baru saja Anda tandai di lapangan. Dana ini belum disahkan oleh Bu Susi.
            </p>
        </div>

        <div class="bg-white/20 p-4 rounded-xl backdrop-blur text-right">
            <div class="text-[10px] text-amber-100 uppercase font-bold">Jumlah Ceklis Terkumpul</div>
            <div class="text-2xl font-bold font-heading">{{ $cart->payments->count() }} Kotak</div>
        </div>
    </div>

    <!-- RINCIAN CEKLIS DI DALAM KERANJANG -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 md:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-heading font-bold text-slate-900 text-base">Rincian Pembayaran Masuk Keranjang</h3>
                <div class="text-xs text-slate-400">Seluruh ceklis ini akan dikunci permanen saat disahkan oleh Owner</div>
            </div>
            <a href="{{ route('customers.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Ceklis Lain
            </a>
        </div>

        @if($cart->payments->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-basket-shopping text-4xl mb-3 text-slate-300"></i>
                <div class="font-bold text-slate-700">Keranjang Setoran Masih Kosong (Rp 0)</div>
                <div class="text-xs text-slate-400 mt-1">Buka menu data konsumen lalu centang kotak tagihan (harian/mingguan) di lapangan.</div>
                <a href="{{ route('customers.index') }}" class="inline-block mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold text-xs shadow-xs hover:bg-emerald-700 transition">
                    Pilih Konsumen & Mulai Ceklis
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Konsumen</th>
                            <th class="py-3 px-4">Paket</th>
                            <th class="py-3 px-4">Periode Tagihan</th>
                            <th class="py-3 px-4">Nominal</th>
                            <th class="py-3 px-4">Waktu Ceklis</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($cart->payments as $idx => $p)
                            @php
                                $isHarian = $p->customerPackage?->package?->isHarian();
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $p->customerPackage->customer->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $p->customerPackage->customer->phone }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium">
                                    <div class="font-bold text-slate-800">{{ $p->customerPackage->package->name }}</div>
                                    <div class="text-[10px] {{ $isHarian ? 'text-sky-600 font-semibold' : 'text-slate-400' }}">
                                        {{ $isHarian ? 'Model Harian' : 'Model Mingguan' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $isHarian ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-amber-100 text-amber-800' }}">
                                        <i class="fa-solid {{ $isHarian ? 'fa-calendar-day' : 'fa-calendar-week' }} mr-1 text-[9px]"></i>
                                        {{ $isHarian ? 'Hari ke-' : 'Minggu ke-' }}{{ $p->period_number }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-700">
                                    {{ $p->formatted_amount }}
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-[11px]">
                                    {{ $p->paid_at ? $p->paid_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('payments.toggle', $p) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-[11px]" title="Batalkan ceklis ini">
                                            <i class="fa-solid fa-xmark mr-0.5"></i> Batal
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- FORM AJUKAN SETORAN KE OWNER -->
            <div class="p-6 bg-slate-50 border-t border-slate-100">
                <form action="{{ route('agent.cart.submit', $cart) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Catatan Setoran untuk Ibu Susi / Owner (Opsional)
                        </label>
                        <textarea name="notes" rows="2" placeholder="Misal: Uang fisik Rp {{ number_format($cart->total_amount, 0, ',', '.') }} dititipkan di amplop cokelat..." class="w-full text-xs p-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-amber-500"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                        <div class="text-xs text-slate-500">
                            Setelah diserahkan, Anda akan memulai keranjang baru dari <strong>Rp 0</strong>.
                        </div>
                        <button type="submit" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-md transition flex items-center justify-center">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Ajukan Setoran ({{ $cart->formatted_total }})
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

</div>
@endsection

