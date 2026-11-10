@extends('layouts.app')

@section('title', 'Master Data Kemasan Parsel')
@section('page_title', 'Master Data Kemasan Parsel')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-900 border border-pink-200">
        {{ $packagings->total() }} Kemasan Terdaftar
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ previewImage: null, previewTitle: '' }">

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-box"></i>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Kemasan</div>
                <div class="font-heading font-black text-lg text-slate-800">{{ $stats['total'] }} Jenis</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kemasan Aktif</div>
                <div class="font-heading font-black text-lg text-emerald-700">{{ $stats['active'] }} Siap Pakai</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Dipakai di Paket</div>
                <div class="font-heading font-black text-lg text-indigo-700">{{ $stats['used_in_packages'] }} Kemasan</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Dipilih Konsumen</div>
                <div class="font-heading font-black text-lg text-amber-700">{{ $stats['ordered_by_customers'] }} Pesanan</div>
            </div>
        </div>
    </div>

    <!-- TOP BAR SEARCH & ADD BUTTON -->
    <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
        <form action="{{ route('owner.packagings.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kemasan (koper, keranjang, dll)..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-pink-500 w-full sm:w-72 font-semibold">
            
            <button type="submit" class="px-3.5 py-2 bg-pink-700 hover:bg-pink-800 text-white rounded-lg font-bold transition">
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
            </button>

            @if(request()->filled('search'))
                <a href="{{ route('owner.packagings.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-xmark mr-1"></i> Reset
                </a>
            @endif
        </form>

        <div class="flex items-center space-x-2 flex-shrink-0">
            <a href="{{ route('owner.packagings.create') }}" class="px-4 py-2 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white font-bold rounded-xl shadow-xs transition flex items-center active:scale-95">
                <i class="fa-solid fa-plus mr-1.5"></i> + Tambah Kemasan Baru
            </a>
        </div>
    </div>

    <!-- PACKAGINGS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-500 font-semibold bg-slate-50/60">
                        <th class="py-3 px-4 text-center">Foto Kemasan</th>
                        <th class="py-3 px-4">Kode & Nama Kemasan</th>
                        <th class="py-3 px-4">Harga Beli Modal (Owner)</th>
                        <th class="py-3 px-4">Harga Tambahan Konsumen</th>
                        <th class="py-3 px-4">Margin Usaha</th>
                        <th class="py-3 px-4 text-center">Dipakai Paket</th>
                        <th class="py-3 px-4 text-center">Dipilih Konsumen</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($packagings as $pkg)
                        <tr class="hover:bg-pink-50/30 transition">
                            <!-- FOTO TAMPAK KEMASAN -->
                            <td class="py-3 px-4 text-center">
                                @if($pkg->image_path && file_exists(public_path($pkg->image_path)))
                                    <button type="button" @click="previewImage = '{{ asset($pkg->image_path) }}'; previewTitle = '{{ addslashes($pkg->name) }}'" class="group relative inline-block">
                                        <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-12 h-12 rounded-xl object-cover border border-pink-200 shadow-2xs group-hover:scale-105 transition">
                                        <span class="absolute inset-0 bg-black/30 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] transition">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </span>
                                    </button>
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-lg border border-slate-200">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                @endif
                            </td>

                            <!-- NAMA & KODE KEMASAN -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $pkg->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $pkg->code }}</div>
                                @if($pkg->description)
                                    <div class="text-[11px] text-slate-500 mt-1 line-clamp-1">{{ $pkg->description }}</div>
                                @endif
                            </td>

                            <!-- HARGA BELI MODAL -->
                            <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                                {{ $pkg->formatted_cost_price }}
                                <div class="text-[9px] text-slate-400 font-sans">Belanja Kulak</div>
                            </td>

                            <!-- HARGA JUAL KONSUMEN -->
                            <td class="py-3 px-4 font-mono font-bold text-pink-700">
                                {{ $pkg->formatted_price }}
                                <div class="text-[9px] text-slate-400 font-sans">Biaya Masuk Paket</div>
                            </td>

                            <!-- MARGIN -->
                            <td class="py-3 px-4 font-mono font-semibold text-emerald-700">
                                @php
                                    $margin = (float) $pkg->price - (float) $pkg->cost_price;
                                @endphp
                                +Rp {{ number_format($margin, 0, ',', '.') }}
                            </td>

                            <!-- DIPAKAI PAKET -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pkg->packages_count > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $pkg->packages_count }} Paket
                                </span>
                            </td>

                            <!-- DIPILIH KONSUMEN -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pkg->customer_packages_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $pkg->customer_packages_count }} Pesanan
                                </span>
                            </td>

                            <!-- STATUS -->
                            <td class="py-3 px-4 text-center">
                                @if($pkg->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('owner.packagings.edit', $pkg) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold transition shadow-2xs">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>

                                <form action="{{ route('owner.packagings.destroy', $pkg) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kemasan {{ $pkg->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-500 hover:text-red-700 font-bold transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-3 text-pink-300"></i>
                                <div class="font-bold text-slate-700">Belum Ada Data Kemasan</div>
                                <div class="text-xs mt-1">Tambahkan kemasan seperti Koper, Keranjang Kecil, atau Box Hampers untuk ditawarkan di paket.</div>
                                <div class="mt-4">
                                    <a href="{{ route('owner.packagings.create') }}" class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-xl font-bold transition shadow-xs">
                                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Kemasan Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $packagings->links() }}
        </div>
    </div>

    <!-- MODAL ZOOM FOTO KEMASAN -->
    <div x-show="previewImage" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4" style="display: none;" @click="previewImage = null" @keydown.escape.window="previewImage = null">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-pink-100" @click.stop>
            <div class="p-4 bg-gradient-to-r from-pink-600 to-rose-600 text-white flex items-center justify-between">
                <span class="font-bold text-sm" x-text="previewTitle"></span>
                <button type="button" @click="previewImage = null" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">✕</button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center min-h-[300px]">
                <img :src="previewImage" alt="Preview Foto Kemasan" class="max-h-[450px] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="previewImage = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold text-xs">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection

