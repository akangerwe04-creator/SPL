@extends('layouts.app')

@section('title', 'Rekonsiliasi Kas Agen')
@section('page_title', 'Rekonsiliasi Kas & Validasi Setoran Agen')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        Kendali Penuh Owner
    </span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- SUMMARY HEADER CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Setoran Menunggu Sah</div>
                <div class="text-xl font-heading font-extrabold text-amber-600 mt-0.5">{{ $submittedCount }} Berkas</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Total Kas Sah Masuk</div>
                <div class="text-xl font-heading font-extrabold text-emerald-700 mt-0.5">Rp {{ number_format($totalApprovedAmount, 0, ',', '.') }}</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Total Riwayat Keranjang</div>
                <div class="text-xl font-heading font-extrabold text-slate-800 mt-0.5">{{ $deposits->total() }} Batch</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <form action="{{ route('owner.deposits.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                <option value="">Semua Status Aktif (Diajukan, Disahkan, Ditolak)</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Diajukan Agen (Menunggu Validasi)</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disahkan (Valid)</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Draft Lapangan (Belum Diajukan)</option>
            </select>

            <select name="agent_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                <option value="">Semua Agen</option>
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                @endforeach
            </select>

            @if(request()->hasAny(['status', 'agent_id']))
                <a href="{{ route('owner.deposits.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-xmark mr-1"></i> Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- DEPOSITS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($deposits->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                <div class="text-sm">Tidak ditemukan data setoran sesuai filter.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                            <th class="py-3 px-4">No. Setoran</th>
                            <th class="py-3 px-4">Nama Agen</th>
                            <th class="py-3 px-4">Nominal Setor</th>
                            <th class="py-3 px-4">Jumlah Ceklis</th>
                            <th class="py-3 px-4">Waktu Pengajuan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($deposits as $dep)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-medium text-slate-800">
                                    {{ $dep->deposit_number }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $dep->agent->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $dep->agent->phone ?: '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-emerald-700 text-sm">
                                    {{ $dep->formatted_total }}
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $dep->payments_count }} Ceklis
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $dep->submitted_at ? $dep->submitted_at->format('d M Y H:i') : ($dep->created_at->format('d M Y')) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($dep->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-circle-check mr-1"></i> Disahkan
                                        </span>
                                    @elseif($dep->status === 'submitted')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                            <i class="fa-solid fa-bell mr-1"></i> Perlu Validasi
                                        </span>
                                    @elseif($dep->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">
                                            <i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('owner.deposits.show', $dep) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold {{ $dep->status === 'submitted' ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} transition">
                                        Periksa <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                                    </a>
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

