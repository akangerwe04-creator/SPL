@extends('layouts.app')

@section('title', 'Data Konsumen')
@section('page_title', 'Manajemen Konsumen & Pelanggan Agen')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        {{ $customers->total() }} Konsumen Terdaftar
    </span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- SEARCH & ACTION BAR -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
        <form action="{{ route('customers.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telp, alamat..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-emerald-500 w-full sm:w-60">

            <select name="package_type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium">
                <option value="">Semua Tipe Paket</option>
                <option value="parsel" {{ request('package_type') === 'parsel' ? 'selected' : '' }}>Parsel Barang</option>
                <option value="tabungan" {{ request('package_type') === 'tabungan' ? 'selected' : '' }}>Tabungan Uang</option>
                <option value="ecer" {{ request('package_type') === 'ecer' ? 'selected' : '' }}>Eceran Dinamis</option>
            </select>

            <select name="safe_status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium">
                <option value="">Semua Titik Aman</option>
                <option value="safe" {{ request('safe_status') === 'safe' ? 'selected' : '' }}>✅ Lolos Titik Aman</option>
                <option value="unreached" {{ request('safe_status') === 'unreached' ? 'selected' : '' }}>⚠️ Belum Titik Aman</option>
            </select>

            <select name="stop_status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium">
                <option value="">Semua Status Stop</option>
                <option value="pending" {{ request('stop_status') === 'pending' ? 'selected' : '' }}>⏳ Menunggu ACC Stop</option>
                <option value="approved" {{ request('stop_status') === 'approved' ? 'selected' : '' }}>⛔ Berhenti (Barang Dialokasikan)</option>
            </select>

            @if(auth()->user()->isOwner())
                <select name="agent_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium">
                    <option value="">Semua Agen</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>Agen: {{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-3 py-2 bg-emerald-600 text-white rounded-lg font-bold">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            @if(request()->hasAny(['search', 'package_type', 'agent_id', 'safe_status']))
                <a href="{{ route('customers.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-xmark mr-1"></i> Reset
                </a>
            @endif
        </form>

        <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-user-plus mr-1.5"></i> Daftarkan Konsumen Baru
        </a>
    </div>

    <!-- CUSTOMERS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($customers->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-users text-4xl mb-3 text-slate-300"></i>
                <div class="font-bold text-slate-700">Belum Ada Konsumen</div>
                <div class="text-xs mt-1">Daftarkan konsumen baru untuk memulai pencatatan ceklis tagihan.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/30">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Konsumen</th>
                            <th class="py-3 px-4">Paket Diambil</th>
                            <th class="py-3 px-4">Agen Penagih</th>
                            <th class="py-3 px-4">Total Setoran</th>
                            <th class="py-3 px-4">Progres</th>
                            <th class="py-3 px-4">Status & Catatan</th>
                            <th class="py-3 px-4 text-right">Lembar Ceklis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($customers as $c)
                            @php
                                $pkg = $c->activePackage;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-medium text-slate-400">
                                    {{ $c->code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('customers.show', $c) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition">
                                        {{ $c->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $c->phone ?: '-' }} &bull; {{ $c->address ?: '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($pkg && $pkg->package)
                                        <div class="font-bold text-slate-800">{{ $pkg->package->name }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ $pkg->package->type }} &bull; {{ $pkg->package->installment_count }} {{ $pkg->package->period_label }} ({{ $pkg->package->installment_label }})</div>
                                    @elseif($pkg)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300" title="Paket sebelumnya telah dibatalkan karena dihapus oleh Owner. Silakan pilih paket baru.">
                                            <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i> Wajib Pilih Paket Baru
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum memilih paket</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 flex items-center space-x-1">
                                        <i class="fa-solid fa-user-tie text-emerald-600 text-xs mr-1"></i>
                                        <span>{{ $c->agent->name }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $c->agent->phone ?: '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($pkg)
                                        <div class="font-bold text-emerald-700">{{ $pkg->formatted_paid }}</div>
                                        @if(auth()->user()->isOwnerOrAdmin())
                                            <div class="text-[10px] text-slate-400">dari {{ $pkg->formatted_target }}</div>
                                        @else
                                            <div class="text-[10px] text-slate-400">{{ $pkg->paid_count }}/{{ $pkg->package ? $pkg->package->installment_count : '-' }} setoran</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($pkg)
                                        <div class="flex items-center space-x-2">
                                            <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $pkg->progress_percent }}%"></div>
                                            </div>
                                            <span class="font-bold text-[10px] text-slate-700">{{ $pkg->progress_percent }}%</span>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($pkg)
                                        @php
                                            $isSafe = $pkg->isSafeThresholdReached();
                                        @endphp
                                        <div class="space-y-1">
                                            @if($pkg->isStopApproved())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-slate-200 text-slate-800 border border-slate-300">
                                                    <i class="fa-solid fa-hand mr-1 text-slate-600"></i> Berhenti (Barang Dialokasikan)
                                                </span>
                                            @elseif($pkg->isStopPending())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                                    <i class="fa-solid fa-clock mr-1 text-amber-600"></i> Menunggu ACC Stop
                                                </span>
                                            @elseif($pkg->status === 'canceled')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    Batal (10% Fee)
                                                </span>
                                            @elseif($pkg->status === 'completed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    <i class="fa-solid fa-circle-check mr-1"></i> Lunas 100%
                                                </span>
                                            @elseif($isSafe)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" title="Konsumen lolos titik aman. Berhak full paket saat tutup buku.">
                                                    <i class="fa-solid fa-shield-check mr-1 text-[9px]"></i> Lolos Titik Aman
                                                </span>
                                            @elseif($pkg->package && $pkg->package->hasSafeThreshold())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200" title="Belum mencapai titik aman. Kurang Rp {{ number_format($pkg->safe_deficit_nominal, 0, ',', '.') }}">
                                                    <i class="fa-solid fa-triangle-exclamation mr-1 text-[9px]"></i> Belum Aman (-Rp {{ number_format($pkg->safe_deficit_nominal, 0, ',', '.') }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                                    Tanpa Titik Aman
                                                </span>
                                            @endif

                                            @if($pkg->is_bonus_forfeited)
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200" title="{{ $pkg->forfeit_reason }}">
                                                        <i class="fa-solid fa-ban mr-1"></i> Bonus Hangus
                                                    </span>
                                                </div>
                                            @elseif($pkg->consecutive_unpaid_weeks > 0)
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                        {{ $pkg->consecutive_unpaid_weeks }} {{ ($pkg->package && $pkg->package->isHarian()) ? 'Hari' : 'Mgg' }} Bolong
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        @if($pkg && $pkg->isStopApproved())
                                            <a href="{{ route('owner.customer-stops.receipt', $pkg) }}" class="p-1.5 rounded-lg text-slate-700 hover:text-pink-600 hover:bg-pink-50 transition" title="Cetak Bukti Serah Terima Barang">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        @elseif($pkg && $pkg->isStopPending() && auth()->user()->isOwner())
                                            <a href="{{ route('owner.customer-stops.review', $pkg) }}" class="px-2 py-1 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-[10px] shadow-2xs transition flex items-center space-x-1" title="Tentukan barang & ACC Stop">
                                                <i class="fa-solid fa-boxes-packing"></i>
                                                <span>ACC</span>
                                            </a>
                                        @elseif($pkg && $pkg->package && !$pkg->isStopPending() && $pkg->status !== 'canceled')
                                            <a href="{{ route('payments.checklist', $pkg) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs" title="Buka Ceklis Tagihan">
                                                <i class="fa-solid fa-list-check mr-1"></i> Ceklis
                                            </a>
                                        @elseif(!$pkg || !$pkg->package)
                                            <a href="{{ route('customers.edit', $c) }}" class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-500 hover:bg-amber-600 text-white transition shadow-2xs" title="Pilih Paket Kembali">
                                                <i class="fa-solid fa-gift mr-1"></i> Pilih Paket
                                            </a>
                                        @endif
                                        <a href="{{ route('customers.edit', $c) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition" title="Edit Data & Paket">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Hapus konsumen {{ addslashes($c->name) }} (tidak jadi ikut)? Seluruh riwayat akan dihapus permanen.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus konsumen (tidak jadi ikut)">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

