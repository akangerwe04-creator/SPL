@extends('layouts.app')

@section('title', 'Riwayat Setoran')
@section('page_title', 'Riwayat Setoran Lapangan')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
        {{ $deposits->total() }} Berkas Setoran
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- RUNNING CART QUICK ACCESS -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium">Keranjang Berjalan Saat Ini</div>
                <div class="text-base font-bold font-heading text-slate-900">{{ $activeCart->formatted_total }}</div>
            </div>
        </div>
        <a href="{{ route('agent.cart') }}" class="px-3.5 py-2 rounded-xl bg-amber-500 text-white font-bold text-xs hover:bg-amber-600 transition shadow-xs">
            Buka Keranjang Aktif <i class="fa-solid fa-arrow-right ml-1"></i>
        </a>
    </div>

    <!-- HISTORY TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-heading font-bold text-slate-900 text-base">Arsip Rekonsiliasi Kas Agen</h3>
        </div>

        @if($deposits->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                <div class="text-sm">Belum ada riwayat setoran lampau.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                            <th class="py-3 px-4">No. Setoran</th>
                            <th class="py-3 px-4">Tanggal Pengajuan</th>
                            <th class="py-3 px-4">Total Setor</th>
                            <th class="py-3 px-4">Jml Ceklis</th>
                            <th class="py-3 px-4">Status Verifikasi</th>
                            <th class="py-3 px-4">Catatan Owner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($deposits as $dep)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-medium text-slate-800">
                                    {{ $dep->deposit_number }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $dep->submitted_at ? $dep->submitted_at->format('d M Y H:i') : $dep->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-emerald-700">
                                    {{ $dep->formatted_total }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $dep->payments_count }} Ceklis
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($dep->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-circle-check mr-1"></i> Disahkan (Valid)
                                        </span>
                                    @elseif($dep->status === 'submitted')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <i class="fa-solid fa-clock mr-1 animate-pulse"></i> Menunggu Sah
                                        </span>
                                    @elseif($dep->status === 'rejected')
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">
                                                <i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak
                                            </span>
                                            <div>
                                                <form action="{{ route('agent.deposits.reopen', $dep) }}" method="POST" class="inline" onsubmit="return confirm('Buka kembali setoran ini ke Keranjang Aktif untuk diperbaiki?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] shadow-2xs transition">
                                                        <i class="fa-solid fa-rotate-left mr-1"></i> Buka / Revisi
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 italic">
                                    {{ $dep->owner_notes ?: ($dep->agent_notes ?: '-') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

