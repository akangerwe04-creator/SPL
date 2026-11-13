@extends('layouts.app')

@section('title', 'Verifikasi Setoran ' . $deposit->deposit_number)
@section('page_title', 'Rincian & Validasi Setoran Agen')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800">
        {{ $deposit->deposit_number }}
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- TOP RETURN LINK -->
    <a href="{{ route('owner.deposits.index') }}" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Rekonsiliasi
    </a>

    <!-- DETAIL CARD WITH ACTION BUTTONS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Laporan Setoran Lapangan</div>
                <h2 class="text-2xl font-heading font-extrabold text-slate-900 mt-0.5">
                    {{ $deposit->formatted_total }}
                </h2>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                    <span><i class="fa-solid fa-user mr-1 text-slate-400"></i> Agen: <strong>{{ $deposit->agent->name }}</strong></span>
                    <span><i class="fa-solid fa-calendar mr-1 text-slate-400"></i> Diajukan: {{ $deposit->submitted_at ? $deposit->submitted_at->format('d M Y H:i') : $deposit->created_at->format('d M Y') }}</span>
                    <span><i class="fa-solid fa-list-check mr-1 text-slate-400"></i> {{ $deposit->payments->count() }} Kotak Ceklis</span>
                </div>
            </div>

            <!-- STATUS BADGE -->
            <div>
                @if($deposit->status === 'approved')
                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center">
                        <i class="fa-solid fa-shield-check mr-1.5 text-base text-emerald-600"></i>
                        <div>
                            <div>SUDAH DISAHKAN (VALID)</div>
                            <div class="text-[10px] text-emerald-600 font-normal">Oleh {{ $deposit->approver->name ?? 'Ibu Susi' }} pada {{ $deposit->approved_at ? $deposit->approved_at->format('d/m/Y H:i') : '-' }}</div>
                        </div>
                    </div>
                @elseif($deposit->status === 'submitted')
                    <div class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center">
                        <i class="fa-solid fa-bell mr-1.5 text-base text-amber-600 animate-bounce"></i>
                        <div>
                            <div>MENUNGGU VALIDASI OWNER</div>
                            <div class="text-[10px] text-amber-600 font-normal">Pastikan uang fisik sesuai dengan nominal</div>
                        </div>
                    </div>
                @elseif($deposit->status === 'rejected')
                    <div class="px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold">
                        <i class="fa-solid fa-xmark mr-1"></i> DITOLAK
                        <div class="text-[10px] font-normal text-red-600">{{ $deposit->owner_notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        @if($deposit->agent_notes)
            <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600">
                <span class="font-bold text-slate-700">Catatan dari Agen:</span> {{ $deposit->agent_notes }}
            </div>
        @endif

        @if($deposit->status === 'submitted')
            <!-- OWNER APPROVAL ACTION PANEL -->
            <div class="mt-6 p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-xs text-emerald-950">
                    <div class="font-bold"><i class="fa-solid fa-circle-info mr-1 text-emerald-600"></i> Konfirmasi Kesesuaian Fisik Kas:</div>
                    <div>Pengesahan akan <strong>mengunci seluruh ceklis secara permanen</strong> dan memperbarui saldo lunas konsumen.</div>
                </div>

                <div class="flex items-center space-x-2 flex-shrink-0">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-800 text-xs font-bold transition">
                        <i class="fa-solid fa-xmark mr-1"></i> Tolak Setoran
                    </button>
                    
                    <form action="{{ route('owner.deposits.approve', $deposit) }}" method="POST" onsubmit="return confirm('Apakah uang fisik sebesar {{ $deposit->formatted_total }} sudah diterima dengan benar?');">
                        @csrf
                        <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                            <i class="fa-solid fa-check-double mr-1"></i> Sahkan Setoran Sekarang
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- TABLE OF PAYMENTS INSIDE THIS DEPOSIT -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 md:p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-heading font-bold text-slate-800 text-sm">Lembar Rincian Tagihan Konsumen</h3>
            <span class="text-[11px] font-semibold text-slate-500">{{ $deposit->payments->count() }} transaksi tagihan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Konsumen</th>
                        <th class="py-3 px-4">Paket Pilihan</th>
                        <th class="py-3 px-4">Periode Tagihan</th>
                        <th class="py-3 px-4">Nominal Tagihan</th>
                        <th class="py-3 px-4">Status Penguncian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($deposit->payments as $idx => $p)
                        @php
                            $isHarian = $p->customerPackage?->package?->isHarian();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $p->customerPackage->customer->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $p->customerPackage->customer->phone }} • {{ $p->customerPackage->customer->address }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">
                                <div class="font-bold text-slate-800">{{ $p->customerPackage->package->name }}</div>
                                <div class="text-[10px] {{ $isHarian ? 'text-sky-600 font-semibold' : 'text-slate-400' }}">
                                    {{ $isHarian ? 'Model Harian' : 'Model Mingguan' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $isHarian ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-slate-100 text-slate-700' }}">
                                    <i class="fa-solid {{ $isHarian ? 'fa-calendar-day' : 'fa-calendar-week' }} mr-1 text-[9px]"></i>
                                    {{ $isHarian ? 'Hari ke-' : 'Minggu ke-' }}{{ $p->period_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-700 text-sm">
                                {{ $p->formatted_amount }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($p->is_locked)
                                    <span class="inline-flex items-center text-emerald-700 font-bold text-[11px]">
                                        <i class="fa-solid fa-lock mr-1"></i> Terkunci Sah
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-amber-600 font-semibold text-[11px]">
                                        <i class="fa-solid fa-clock mr-1"></i> Belum Dikunci
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- REJECT MODAL -->
<div id="reject-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-heading font-bold text-slate-900 text-base">Tolak Setoran Agen</h3>
            <button onclick="document.getElementById('reject-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('owner.deposits.reject', $deposit) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan / Catatan Selisih Fisik</label>
                <textarea name="notes" rows="3" required placeholder="Contoh: Uang fisik kurang Rp 25.000, mohon dicek kembali ceklis konsumen Pak Hendra..." class="w-full text-xs p-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-red-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-red-600 hover:bg-red-700 text-white transition">
                    Tolak Setoran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

