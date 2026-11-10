@extends('layouts.app')

@section('title', 'Master Data Barang & Uang')
@section('page_title', 'Master Barang & Konversi Grosir')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-900 border border-pink-200">
        {{ $items->total() }} Item Terdaftar
    </span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- TOP BAR SEARCH & ADD BUTTONS -->
    <div class="bg-white rounded-2xl border border-pink-200 shadow-xs p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
        <form action="{{ route('items.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang / uang / kode..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-pink-500 w-full sm:w-64">
            
            <select name="category" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-medium">
                <option value="">Semua Kategori</option>
                <option value="Uang" {{ request('category') === 'Uang' ? 'selected' : '' }}>💵 Uang (Tunai / Tabungan)</option>
                @foreach($categories as $cat)
                    @if($cat !== 'Uang')
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endif
                @endforeach
            </select>

            <button type="submit" class="px-3.5 py-2 bg-pink-700 hover:bg-pink-800 text-white rounded-lg font-bold transition">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('items.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-xmark mr-1"></i> Reset
                </a>
            @endif
        </form>

        <div class="flex items-center space-x-2 flex-shrink-0">
            <!-- Tombol Tambah Master Uang -->
            <a href="{{ route('items.create', ['type' => 'uang']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-money-bill-wave mr-1.5"></i> + Master Uang
            </a>

            <!-- Tombol Tambah Barang Baru -->
            <a href="{{ route('items.create', ['type' => 'barang']) }}" class="px-3.5 py-2 bg-pink-700 hover:bg-pink-800 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-plus mr-1.5"></i> + Barang Fisik
            </a>
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-500 font-semibold bg-slate-50/60">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Nama Item / Barang</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Harga Ecer / Nominal</th>
                        <th class="py-3 px-4">Satuan Dasar</th>
                        <th class="py-3 px-4">Satuan Grosir</th>
                        <th class="py-3 px-4 text-center">Konversi (Isi/Dus)</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-pink-50/30 transition">
                            <td class="py-3 px-4 font-mono font-medium text-slate-500">
                                {{ $item->code }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 flex items-center">
                                    @if($item->isUang())
                                        <i class="fa-solid fa-money-bill-wave text-emerald-600 mr-1.5"></i>
                                    @endif
                                    {{ $item->name }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($item->isUang())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-money-bill-wave mr-0.5"></i> Uang
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $item->category ?: '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold {{ $item->isUang() ? 'text-emerald-700' : 'text-pink-900' }}">
                                {{ $item->formatted_price }}
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">
                                {{ $item->base_unit }}
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                @if($item->isUang())
                                    <span class="text-slate-400 italic">- (Uang Tunai)</span>
                                @else
                                    {{ $item->wholesale_unit }}
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->isUang())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Tunai Langsung (Tanpa Dus)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                        1 {{ $item->wholesale_unit }} = {{ (int)$item->conversion_rate }} {{ $item->base_unit }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                <a href="{{ route('items.edit', $item) }}" class="p-1.5 text-slate-500 hover:text-pink-700 rounded transition" title="Edit Item">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus item ini dari master data?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded transition" title="Hapus Item">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Belum ada barang atau uang di database master data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
    </div>

</div>
@endsection
