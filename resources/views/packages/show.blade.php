@extends('layouts.app')

@section('title', 'Detail Paket - ' . $package->name)
@section('page_title', 'Detail Master Paket')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        {{ $package->code }}
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ previewPhoto: null, previewTitle: '' }">

    <div class="flex items-center justify-between text-xs text-slate-500">
        <a href="{{ route('packages.index') }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Master Paket
        </a>
        <div class="space-x-2">
            <a href="{{ route('packages.edit', $package) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg font-bold text-slate-700">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Paket
            </a>
        </div>
    </div>

    <!-- MAIN DETAIL CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($package->image_path)
            <div class="h-64 w-full bg-slate-100 overflow-hidden relative border-b border-slate-100">
                <img src="{{ asset($package->image_path) }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-4 left-6 text-white">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded {{ $package->isParsel() ? 'bg-emerald-600 text-white' : ($package->isTabungan() ? 'bg-amber-600 text-white' : 'bg-blue-600 text-white') }}">
                        Tipe: {{ ucfirst($package->type) }} &bull; {{ strtoupper($package->period_type ?? 'mingguan') }} &bull; {{ $package->code }}
                    </span>
                    <h1 class="text-2xl font-heading font-extrabold text-white mt-1 drop-shadow-sm">{{ $package->name }}</h1>
                </div>
            </div>
        @endif

        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    @if(!$package->image_path)
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded {{ $package->isParsel() ? 'bg-emerald-100 text-emerald-800' : ($package->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                            Tipe: {{ ucfirst($package->type) }} &bull; {{ strtoupper($package->period_type ?? 'mingguan') }}
                        </span>
                        <h2 class="text-xl font-heading font-extrabold text-slate-900 mt-1.5">
                            {{ $package->name }}
                        </h2>
                    @endif
                    @if($package->description)
                        <p class="text-xs text-slate-500 {{ $package->image_path ? '' : 'mt-1' }} max-w-xl">{{ $package->description }}</p>
                    @endif
                </div>

                <!-- PRICING BADGE -->
                <div class="p-4 rounded-xl {{ $package->isTabungan() ? 'bg-amber-50/80 border border-amber-200' : 'bg-pink-50/60 border border-pink-200' }} text-right shrink-0">
                    <div class="flex items-center justify-end space-x-1.5 mb-1">
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ $package->isHarian() ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-pink-100 text-pink-900 border border-pink-200' }}">
                            <i class="fa-solid {{ $package->isHarian() ? 'fa-calendar-day' : 'fa-calendar-week' }} mr-1"></i>
                            Skema {{ $package->installment_label }} ({{ $package->installment_count }} {{ $package->period_label }})
                        </span>
                    </div>
                    @if($package->isTabungan())
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Setoran per Minggu</div>
                        <div class="text-2xl font-heading font-black text-amber-900">{{ $package->formatted_installment }} <span class="text-xs font-bold text-slate-500">/ minggu</span></div>
                        
                        <div class="mt-2 pt-2 border-t border-amber-200/60 space-y-1 text-[11px]">
                            <div class="flex justify-between items-center text-slate-700">
                                <span class="font-bold">Uang Tunai Cair:</span>
                                <span class="font-black text-emerald-800 text-sm">{{ $package->formatted_base_price }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Cicilan {{ $package->installment_label }}</div>
                        <div class="text-2xl font-heading font-black text-pink-700">{{ $package->formatted_installment }} <span class="text-xs font-bold text-slate-500">/ {{ strtolower($package->period_label) }}</span></div>
                        
                        <div class="mt-2 pt-2 border-t border-pink-200/60 space-y-0.5 text-[11px]">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Harga Pokok Paket:</span>
                                <span class="font-bold text-slate-800">{{ $package->formatted_base_price }}</span>
                            </div>
                            @if($package->admin_fee > 0)
                                <div class="flex justify-between items-center text-amber-800">
                                    <span><i class="fa-solid fa-eye-slash mr-1"></i> Biaya Admin SPL:</span>
                                    <span class="font-bold">+ {{ $package->formatted_admin_fee }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center text-slate-900 font-extrabold pt-1 border-t border-slate-200/40">
                                <span>Total Target Tagihan:</span>
                                <span>{{ $package->formatted_target }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="text-[10px] text-slate-500 font-semibold mt-1">
                        Dilaksanakan selama {{ $package->installment_count }} kali tagih
                    </div>

                    @if($package->start_date && $package->end_date)
                        <div class="text-[10px] text-pink-900 mt-1 font-medium bg-pink-100/50 px-2 py-0.5 rounded border border-pink-200 inline-block">
                            <i class="fa-solid fa-calendar-days mr-1 text-pink-600"></i> Acuan Kalender: {{ $package->formatted_date_range }} ({{ $package->calculated_days }} Hari / {{ $package->calculated_weeks }} Mgg)
                        </div>
                    @endif
                </div>
            </div>

            @if(!$package->isTabungan())
            <!-- OPSI KEMASAN YANG DITAWARKAN -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-7 h-7 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-slate-900 text-sm">
                                Opsi Kemasan yang Ditawarkan ({{ $package->packagings->count() }} Pilihan)
                            </h3>
                            <p class="text-[11px] text-slate-500">Konsumen dapat memilih wadah/kemasan ini dengan penyesuaian cicilan.</p>
                        </div>
                    </div>
                    @if($package->packagings->isNotEmpty())
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-pink-50 text-pink-700 border border-pink-200">
                            Pilihan Fleksibel
                        </span>
                    @endif
                </div>

                @if($package->packagings->isEmpty())
                    <div class="p-4 bg-slate-50 rounded-xl text-center text-xs text-slate-400">
                        Belum ada opsi kemasan khusus yang ditawarkan untuk paket ini (menggunakan kemasan standar / tanpa biaya tambahan).
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($package->packagings as $pkg)
                            @php
                                $pkgPrice = $pkg->pivot->custom_price !== null ? (float)$pkg->pivot->custom_price : (float)$pkg->price;
                                $simTarget = (float)$package->target_nominal + $pkgPrice;
                                $simInstallment = $package->installment_count > 0 ? \App\Models\Package::roundInstallment($simTarget / $package->installment_count) : 0;
                            @endphp
                            <div class="p-3.5 bg-white rounded-xl border border-pink-100 hover:border-pink-300 shadow-2xs space-y-3 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start space-x-3">
                                        <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center cursor-pointer relative group shadow-2xs"
                                             @click="previewPhoto = '{{ $pkg->image_url ?: '' }}'; previewTitle = '{{ addslashes($pkg->name) }} ({{ $pkg->code }})'">
                                            @if($pkg->image_path && file_exists(public_path($pkg->image_path)))
                                                <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition text-xs">
                                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                                </div>
                                            @else
                                                <i class="fa-solid fa-box-archive text-slate-300 text-2xl"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <span class="font-mono text-[9px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.2 rounded border border-pink-200">{{ $pkg->code }}</span>
                                                @if($pkg->pivot->is_default)
                                                    <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Default</span>
                                                @endif
                                            </div>
                                            <div class="font-bold text-slate-800 text-xs truncate mt-1" title="{{ $pkg->name }}">{{ $pkg->name }}</div>
                                            <div class="text-[11px] font-extrabold text-pink-700 mt-0.5">+ Rp {{ number_format($pkgPrice, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    @if($pkg->description)
                                        <p class="text-[10px] text-slate-500 mt-2 line-clamp-2">{{ $pkg->description }}</p>
                                    @endif
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500">Cicilan jika dipilih:</span>
                                    <span class="font-black text-slate-900 font-mono">Rp {{ number_format($simInstallment, 0, ',', '.') }}/{{ $package->isHarian() ? 'hari' : 'mgg' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

        <!-- ITEMS INCLUDED -->
        <div class="mt-6">
            <h3 class="font-heading font-bold text-slate-900 text-sm mb-3">
                {{ $package->isTabungan() ? 'Barang Bonus Tabungan (Gratis)' : 'Daftar Isi Paket' }}
            </h3>

            @if($package->packageItems->isEmpty())
                <div class="p-6 bg-slate-50 rounded-xl text-center text-xs text-slate-400">
                    {{ $package->isEcer() ? 'Paket ini bertipe Eceran Dinamis (isi ditentukan saat konsumen mendaftar).' : 'Belum ada barang di dalam paket ini.' }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/50">
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Nama Barang</th>
                                <th class="py-2.5 px-3">Kategori</th>
                                <th class="py-2.5 px-3 text-center">Banyaknya</th>
                                <th class="py-2.5 px-3 text-right">Harga Ecer Satuan</th>
                                <th class="py-2.5 px-3 text-right">Subtotal Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($package->packageItems as $idx => $pi)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-2.5 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-800">
                                        {{ $pi->item->name }}
                                        @if($pi->item->isUang())
                                            @if($package->isTabungan() && !$pi->is_bonus)
                                                <span class="ml-1 text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-300">💵 UANG POKOK</span>
                                            @elseif($package->isParsel())
                                                <span class="ml-1 text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-300">💵 UANG TUNAI</span>
                                            @endif
                                        @endif
                                        @if($pi->is_bonus)
                                            <span class="ml-1 text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">BONUS</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($pi->item->isUang())
                                            <span class="font-bold text-emerald-700">💵 Uang</span>
                                        @else
                                            <span class="text-slate-500">{{ $pi->item->category ?: 'Barang' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700">
                                        {{ $pi->quantity }} {{ $pi->item->base_unit }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-slate-600">
                                        {{ $pi->item->formatted_price }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900">
                                        Rp {{ number_format($pi->item->unit_price * $pi->quantity, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- CONSUMERS TAKING THIS PACKAGE -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <h3 class="font-heading font-bold text-slate-900 text-sm mb-3">
                Konsumen Terdaftar Mengambil Paket Ini ({{ $package->customerPackages->count() }} Orang)
            </h3>

            @if($package->customerPackages->isEmpty())
                <div class="text-xs text-slate-400 italic">Belum ada konsumen yang memilih paket ini.</div>
            @else
                <div class="space-y-2">
                    @foreach($package->customerPackages as $cp)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-800">{{ $cp->customer->name }}</div>
                                <div class="text-[10px] text-slate-400">Total Setor: {{ $cp->formatted_paid }} / {{ $cp->formatted_target }}</div>
                            </div>
                            <a href="{{ route('customers.show', $cp->customer) }}" class="text-emerald-600 hover:text-emerald-700 font-bold">
                                Profil Konsumen &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        </div>
    </div>

    <!-- MODAL PREVIEW FOTO KEMASAN / PRODUK -->
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

