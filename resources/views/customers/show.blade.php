@extends('layouts.app')

@section('title', 'Profil Konsumen - ' . $customer->name)
@section('page_title', 'Rincian Konsumen & Riwayat Tagihan')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800">
        {{ $customer->code }}
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ previewPhoto: null, previewTitle: '' }">

    <!-- TOP BREADCRUMB -->
    <div class="flex items-center justify-between text-xs text-slate-500">
        <a href="{{ route('customers.index') }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Konsumen
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.edit', $customer) }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 transition">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Data & Paket
            </a>
            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data konsumen {{ addslashes($customer->name) }} secara permanen karena tidak jadi ikut program?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 font-bold border border-red-200 transition" title="Hapus jika tidak jadi ikut">
                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- MAIN PROFILE & PACKAGE BANNER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-100">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold font-heading flex-shrink-0 border border-emerald-200">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-heading font-extrabold text-slate-900">
                        {{ $customer->name }}
                    </h2>
                    <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                        <div><i class="fa-solid fa-phone mr-1.5 text-slate-400"></i> {{ $customer->phone ?: 'Tidak ada nomor telp' }}</div>
                        <div><i class="fa-solid fa-location-dot mr-1.5 text-slate-400"></i> {{ $customer->address ?: 'Paseh' }}</div>
                        <div><i class="fa-solid fa-user-tie mr-1.5 text-slate-400"></i> Agen Lapangan: <strong>{{ $customer->agent->name }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- SMART CHECKLIST CTA BUTTON -->
            @if($activePackage)
                <div class="flex-shrink-0">
                    <a href="{{ route('payments.checklist', $activePackage) }}" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center">
                        <i class="fa-solid fa-list-check mr-2 text-sm"></i> Buka Lembar Ceklis Tagihan
                    </a>
                </div>
            @endif
        </div>

        @if($activePackage)
            <!-- PACKAGE STATUS & FINANCIAL PROGRESS -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Paket Diambil</div>
                    <div class="font-heading font-bold text-sm text-slate-900 mt-0.5">{{ $activePackage->package->name }}</div>
                    <div class="text-[11px] text-slate-500 uppercase font-semibold mt-1">{{ $activePackage->package->type }} &bull; {{ $activePackage->package->installment_count }} {{ $activePackage->package->period_label }} ({{ $activePackage->package->installment_label }})</div>
                    @if($activePackage->packaging)
                        <div class="mt-2.5 pt-2 border-t border-slate-200/80 flex items-center space-x-2.5">
                            @if($activePackage->packaging->image_path && file_exists(public_path($activePackage->packaging->image_path)))
                                <img src="{{ asset($activePackage->packaging->image_path) }}" alt="{{ $activePackage->packaging->name }}" class="w-9 h-9 rounded-lg object-cover border border-pink-200 shrink-0 cursor-pointer shadow-2xs hover:scale-105 transition" @click="previewPhoto = '{{ $activePackage->packaging->image_url }}'; previewTitle = '{{ addslashes($activePackage->packaging->name) }} ({{ $activePackage->packaging->code }})'">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center text-xs shrink-0 border border-pink-200">
                                    <i class="fa-solid fa-box-archive"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="text-[9px] font-bold text-pink-700 uppercase">Kemasan Dipilih:</div>
                                <div class="text-xs font-bold text-slate-900 truncate">{{ $activePackage->packaging->name }}</div>
                            </div>
                        </div>
                    @else
                        <div class="mt-2.5 pt-2 border-t border-slate-200/80 text-[10px] text-slate-500 flex items-center">
                            <i class="fa-solid fa-box mr-1.5 text-slate-400"></i> Kemasan Standar (Dus)
                        </div>
                    @endif
                </div>

                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                    <div class="text-[10px] font-bold text-emerald-800 uppercase">Total Setoran Masuk</div>
                    <div class="font-heading font-extrabold text-lg text-emerald-700 mt-0.5">{{ $activePackage->formatted_paid }}</div>
                    @if(auth()->user()->isOwnerOrAdmin())
                        <div class="text-[11px] text-emerald-800 mt-1">
                            Sisa: <strong>{{ $activePackage->formatted_remaining }}</strong> dari {{ $activePackage->formatted_target }}
                        </div>
                    @else
                        <div class="text-[11px] text-emerald-800 mt-1">
                            Terbayar: <strong>{{ $activePackage->paid_count }}x</strong> dari {{ $activePackage->package->installment_count }}x setoran ({{ $activePackage->package->period_label }})
                        </div>
                    @endif
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Status & Kepatuhan</div>
                    <div class="mt-1">
                        @if($activePackage->status === 'canceled')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-700">
                                Batal (Potongan 10%)
                            </span>
                        @elseif($activePackage->status === 'completed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                <i class="fa-solid fa-circle-check mr-1"></i> Lunas Penuh
                            </span>
                        @elseif($activePackage->is_bonus_forfeited)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 border border-red-200" title="{{ $activePackage->forfeit_reason }}">
                                <i class="fa-solid fa-ban mr-1"></i> Bonus Hangus (≥3 Mgg)
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                Setoran Aktif Lancar
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- PROGRESS BAR -->
            <div class="mt-4 pt-2">
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-slate-600">Persentase Pelunasan</span>
                    <span class="text-emerald-700 font-bold">{{ $activePackage->progress_percent }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $activePackage->progress_percent }}%"></div>
                </div>
            </div>

            <!-- SPECIAL TABUNGAN ALERT -->
            @if($activePackage->package->isTabungan() && $activePackage->is_bonus_forfeited)
                <div class="mt-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-900 flex items-start space-x-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5 text-base"></i>
                    <div>
                        <div class="font-bold text-red-800">Perhatian: Hadiah Bonus Barang Telah Hangus</div>
                        <div class="text-[11px] text-red-700 mt-0.5">{{ $activePackage->forfeit_reason }}</div>
                    </div>
                </div>
            @endif

            <!-- DAFTAR BARANG ECERAN YANG DIPILIH KONSUMEN -->
            @if($activePackage->customItems->isNotEmpty())
                <div class="mt-5 p-4 rounded-xl bg-blue-50/60 border border-blue-200">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-blue-200">
                        <div class="font-bold text-blue-950 text-xs flex items-center">
                            <i class="fa-solid fa-cubes text-blue-600 mr-2"></i>
                            Daftar Barang Eceran Pilihan Konsumen
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                            {{ $activePackage->customItems->count() }} Macam Barang
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($activePackage->customItems as $cItem)
                            <div class="p-3 rounded-xl bg-white border border-blue-200 shadow-2xs flex flex-col justify-between space-y-2.5">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm break-words whitespace-normal leading-snug">
                                        {{ $cItem->item?->name ?? 'Barang Eceran' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-1">
                                        Jumlah: <span class="font-extrabold text-blue-900 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">{{ $cItem->quantity }} {{ $cItem->unit ?: ($cItem->item?->base_unit ?? 'pcs') }}</span>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-[10px] text-slate-500 font-semibold">Setoran:</span>
                                    <div class="text-right">
                                        <div class="font-black text-blue-900">
                                            {{ $cItem->installment_amount > 0 ? ('Rp ' . number_format($cItem->installment_amount, 0, ',', '.') . ' / mgg') : ('Rp ' . number_format($cItem->unit_price, 0, ',', '.')) }}
                                        </div>
                                        @if($cItem->subtotal > 0)
                                            <div class="text-[10px] text-slate-500">
                                                Target: <span class="font-bold text-slate-700">Rp {{ number_format($cItem->subtotal, 0, ',', '.') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- MINI POS ADJUSTMENT RECORD (IF ANY) -->
            @if($activePackage->partialFulfillment)
                <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-950 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="font-bold text-purple-900 flex items-center">
                            <i class="fa-solid fa-receipt mr-1.5 text-purple-600"></i> Paket Ini Telah Disesuaikan via Mini-POS
                        </div>
                        <div class="text-[11px] text-purple-800 mt-0.5">
                            Total barang pengganti senilai <strong>{{ $activePackage->partialFulfillment->formatted_items_value }}</strong> &bull; Sisa dana kembalian: <strong>{{ $activePackage->partialFulfillment->formatted_remaining_refund }}</strong>
                        </div>
                    </div>
                    <a href="{{ route('mini-pos.receipt', $activePackage->partialFulfillment) }}" class="px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg text-xs transition flex-shrink-0">
                        <i class="fa-solid fa-print mr-1"></i> Cetak Berita Acara
                    </a>
                </div>
            @endif
        @endif
    </div>

    @if($activePackage)
        <!-- PAYMENTS SCHEDULE CHECKLIST PREVIEW -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-heading font-bold text-slate-900 text-sm">
                    Jadwal Ceklis Tagihan ({{ $activePackage->package->installment_count }} {{ $activePackage->package->period_label }})
                </h3>
                <a href="{{ route('payments.checklist', $activePackage) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                    Buka Interaktif &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-10 gap-2 pt-4">
                @foreach($activePackage->payments as $p)
                    <div class="p-2 rounded-lg border text-center text-xs
                        {{ $p->is_locked ? 'bg-emerald-600 border-emerald-600 text-white font-bold' : '' }}
                        {{ ($p->is_paid && !$p->is_locked) ? 'bg-amber-400 border-amber-400 text-amber-950 font-bold' : '' }}
                        {{ (!$p->is_paid) ? 'bg-slate-50 border-slate-200 text-slate-500' : '' }}"
                        title="{{ ($activePackage->package->isHarian() ? 'Hari ke-' : 'Minggu ke-') . $p->period_number }} ({{ $p->formatted_amount }})">
                        <div class="text-[10px] opacity-80">{{ $activePackage->package->isHarian() ? 'H' : 'M' }}-{{ $p->period_number }}</div>
                        <div class="text-[11px] font-extrabold mt-0.5">
                            @if($p->is_locked)
                                <i class="fa-solid fa-check"></i>
                            @elseif($p->is_paid)
                                <i class="fa-solid fa-clock"></i>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- MODAL ZOOM FOTO KEMASAN / PRODUK -->
    <div x-show="previewPhoto" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4" style="display: none;" @click="previewPhoto = null" @keydown.escape.window="previewPhoto = null">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-pink-100" @click.stop>
            <div class="p-4 bg-gradient-to-r from-pink-600 to-rose-600 text-white flex items-center justify-between">
                <span class="font-bold text-sm" x-text="previewTitle"></span>
                <button type="button" @click="previewPhoto = null" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">✕</button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center min-h-[300px]">
                <img :src="previewPhoto" alt="Foto Kemasan" class="max-h-[450px] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="previewPhoto = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold text-xs">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection

