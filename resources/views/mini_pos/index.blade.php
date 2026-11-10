@extends('layouts.app')

@section('title', 'Mini-POS Penyesuaian Parsel')
@section('page_title', 'Mini-POS Kasir Penyesuaian Tunggakan')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
        Partial Fulfillment
    </span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- INTRO BANNER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-extrabold text-slate-900">Sistem Kasir Mini-POS (Penyesuaian Parsel Menunggak)</h2>
            <p class="text-xs text-slate-500 max-w-2xl mt-1">
                Dirancang khusus untuk konsumen yang tidak melunasi setoran sampai akhir musim Lebaran. Ibu Susi dapat memilihkan paket barang pengganti yang memotong saldo setoran masuk secara otomatis dan adil.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 font-bold text-xs border border-amber-200">
                <i class="fa-solid fa-scale-balanced mr-1"></i> Transparan & Adil
            </span>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <form action="{{ route('mini-pos.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama konsumen..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-emerald-500">
            
            <select name="filter" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700">
                <option value="">Semua Konsumen Belum Lunas</option>
                <option value="unadjusted" {{ request('filter') === 'unadjusted' ? 'selected' : '' }}>Belum Disesuaikan</option>
                <option value="adjusted" {{ request('filter') === 'adjusted' ? 'selected' : '' }}>Sudah Disesuaikan (Ada Nota)</option>
            </select>

            <button type="submit" class="px-3 py-2 bg-emerald-600 text-white rounded-lg font-bold">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- TABLE OF UNPAID CONSUMERS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($customerPackages->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-500"></i>
                <div class="text-sm font-semibold">Tidak ada konsumen menunggak sesuai filter.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                            <th class="py-3 px-4">Konsumen</th>
                            <th class="py-3 px-4">Paket Asal</th>
                            <th class="py-3 px-4">Target Awal</th>
                            <th class="py-3 px-4">Saldo Setoran Masuk</th>
                            <th class="py-3 px-4">Sisa Kurang</th>
                            <th class="py-3 px-4">Status Pemenuhan</th>
                            <th class="py-3 px-4 text-right">Aksi Mini-POS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($customerPackages as $cp)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $cp->customer->name }}</div>
                                    <div class="text-[10px] text-slate-400">Agen: {{ $cp->agent->name }} • {{ $cp->customer->phone }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">
                                    <div>{{ $cp->package->name }}</div>
                                    @if($cp->isTabungan())
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-100 text-indigo-800 mt-0.5">
                                            <i class="fa-solid fa-money-bill-wave mr-1"></i> Tabungan Magol (Uang)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 mt-0.5">
                                            <i class="fa-solid fa-box-open mr-1"></i> Paket Parsel (Barang)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $cp->formatted_target }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-emerald-700 text-sm">
                                    {{ $cp->formatted_paid }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-red-600">
                                    {{ $cp->formatted_remaining }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($cp->fulfillment_status === 'partial')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                                            <i class="fa-solid fa-receipt mr-1"></i> Sudah Disesuaikan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <i class="fa-solid fa-clock mr-1"></i> Perlu Penyesuaian
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    @if($cp->partialFulfillment)
                                        <a href="{{ route('mini-pos.receipt', $cp->partialFulfillment) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                            <i class="fa-solid fa-print mr-1"></i> {{ $cp->isTabungan() ? 'Kwitansi' : 'Nota' }}
                                        </a>
                                    @endif
                                    @if($cp->isTabungan())
                                        <a href="{{ route('mini-pos.pos', $cp) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-xs">
                                            <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Kasir Tabungan
                                        </a>
                                    @else
                                        <a href="{{ route('mini-pos.pos', $cp) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white transition shadow-xs">
                                            <i class="fa-solid fa-cash-register mr-1"></i> Kasir Barang
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $customerPackages->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

