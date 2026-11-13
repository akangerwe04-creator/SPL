@php
    $custSearchPayload = strtolower(
        $agent->name . ' ' . 
        $customer->name . ' ' . 
        $customer->address . ' ' . 
        $customer->phone . ' ' . 
        $customer->customerPackages->pluck('package.name')->implode(' ')
    );
    $isSafeCust = $customer->is_safe ?? $customer->isSafeThresholdReached();
@endphp
<tr 
    class="hover:bg-slate-50/80 transition"
    x-show="(!searchDetail || '{{ addslashes($custSearchPayload) }}'.includes(searchDetail.toLowerCase().trim())) && (typeof filterSafe === 'undefined' || filterSafe === 'all' || (filterSafe === 'safe' && {{ $isSafeCust ? 'true' : 'false' }}) || (filterSafe === 'unreached' && {{ !$isSafeCust ? 'true' : 'false' }}))"
>
    <!-- 1. NOMOR URUT -->
    <td class="py-3 px-3 text-center font-mono text-slate-400 text-[11px]">
        {{ $cIdx + 1 }}
    </td>

    <!-- 2. NAMA KONSUMEN -->
    <td class="py-3 px-3.5 font-bold text-slate-900">
        <a href="{{ route('customers.show', $customer) }}" class="hover:text-emerald-700 hover:underline flex items-center space-x-1.5">
            <span>{{ $customer->name }}</span>
        </a>
        <div class="text-[10px] text-slate-400 font-mono">{{ $customer->code ?: 'KONS-'.str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</div>
    </td>

    <!-- 3. KONTAK & ALAMAT -->
    <td class="py-3 px-3.5 text-slate-600 text-[11px]">
        <div class="flex items-center space-x-1">
            <i class="fa-solid fa-phone text-slate-400 text-[9px]"></i>
            <span>{{ $customer->phone ?: '-' }}</span>
        </div>
        <div class="text-slate-500 line-clamp-1 mt-0.5">
            <i class="fa-solid fa-map-pin text-slate-400 text-[9px] mr-0.5"></i>
            {{ $customer->address ?: 'Paseh' }}
        </div>
    </td>

    <!-- 4. PAKET & BARANG PILIHAN -->
    <td class="py-3 px-3.5">
        @forelse($customer->customerPackages as $cp)
            @php
                $pkgTheme = $cp->package->isEcer() 
                    ? 'bg-blue-50/60 border-blue-200 text-blue-900' 
                    : ($cp->package->isTabungan() 
                        ? 'bg-amber-50/60 border-amber-200 text-amber-900' 
                        : ($cp->package->isMingguan() 
                            ? 'bg-purple-50/60 border-purple-200 text-purple-900' 
                            : 'bg-emerald-50/60 border-emerald-200 text-emerald-900'));
                
                $badgeColor = $cp->package->isEcer() 
                    ? 'bg-blue-600 text-white' 
                    : ($cp->package->isTabungan() 
                        ? 'bg-amber-600 text-white' 
                        : ($cp->package->isMingguan() 
                            ? 'bg-purple-600 text-white' 
                            : 'bg-emerald-600 text-white'));
            @endphp
            <div class="p-2 rounded-xl border {{ $pkgTheme }} mb-1.5 last:mb-0">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[9px] font-extrabold px-1.5 py-0.2 rounded {{ $badgeColor }}">
                        {{ $cp->package->code }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-600">
                        {{ $cp->package->installment_count }} {{ $cp->package->period_label }}
                    </span>
                </div>
                <div class="font-extrabold text-slate-900 text-xs mt-1">
                    {{ $cp->package->name }}
                </div>

                <!-- RINCIAN BARANG ECERAN PILIHAN KONSUMEN -->
                @if($cp->package->isEcer())
                    @if($cp->customItems->isNotEmpty())
                        <div class="mt-1.5 pt-1.5 border-t border-blue-200/80 flex flex-wrap gap-1">
                            @foreach($cp->customItems as $ci)
                                <span class="px-2 py-0.5 rounded-md bg-white border border-blue-200 text-blue-900 text-[10px] font-bold shadow-2xs">
                                    <i class="fa-solid fa-cube text-blue-600 mr-1 text-[8px]"></i>
                                    {{ $ci->item ? $ci->item->name : 'Item' }} ({{ $ci->quantity }} pcs)
                                </span>
                            @endforeach
                        </div>
                    @elseif($cp->package->packageItems->isNotEmpty())
                        <div class="mt-1.5 pt-1.5 border-t border-blue-200/80 flex flex-wrap gap-1">
                            @foreach($cp->package->packageItems as $pi)
                                <span class="px-2 py-0.5 rounded-md bg-white border border-blue-200 text-blue-900 text-[10px] font-bold shadow-2xs">
                                    <i class="fa-solid fa-cube text-blue-600 mr-1 text-[8px]"></i>
                                    {{ $pi->item ? $pi->item->name : 'Item' }} ({{ $pi->quantity }} pcs)
                                </span>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        @empty
            <span class="text-slate-400 italic text-[11px]">Belum memilih paket</span>
        @endforelse
    </td>

    <!-- 5. TARGET & CICILAN -->
    <td class="py-3 px-3.5 text-right font-mono">
        @forelse($customer->customerPackages as $cp)
            <div class="mb-1.5 last:mb-0">
                <div class="font-extrabold text-slate-900 text-xs">{{ $cp->formatted_target }}</div>
                <div class="text-[10px] text-emerald-700 font-semibold">
                    {{ $cp->package->formatted_installment }} / {{ strtolower($cp->package->period_label) }}
                </div>
            </div>
        @empty
            <span class="text-slate-300">-</span>
        @endforelse
    </td>

    <!-- 6. EVALUASI STATUS TITIK AMAN -->
    <td class="py-3 px-3.5 text-center">
        @forelse($customer->customerPackages as $cp)
            @php
                $isSafe = $cp->isSafeThresholdReached();
            @endphp
            <div class="mb-1.5 last:mb-0">
                @if($cp->progress_percent >= 100)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white shadow-2xs">
                        <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> Lunas 100%
                    </span>
                @elseif($isSafe)
                    <div class="space-y-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="fa-solid fa-shield-check mr-1 text-[9px]"></i> Lolos Titik Aman
                        </span>
                        <div class="text-[9px] text-emerald-700 font-mono font-medium">
                            Min: {{ $cp->package?->formatted_safe_threshold }}
                        </div>
                    </div>
                @elseif($cp->package && $cp->package->hasSafeThreshold())
                    <div class="space-y-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                            <i class="fa-solid fa-triangle-exclamation mr-1 text-[9px]"></i> Belum Aman
                        </span>
                        <div class="text-[10px] text-amber-800 font-black font-mono">
                            Kurang Rp {{ number_format($cp->safe_deficit_nominal, 0, ',', '.') }}
                        </div>
                    </div>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                        Tanpa Titik Aman
                    </span>
                @endif

                @if($cp->consecutive_unpaid_weeks > 0)
                    <div class="text-[9px] font-bold text-amber-700 mt-0.5">
                        {{ $cp->consecutive_unpaid_weeks }} {{ ($cp->package && $cp->package->isHarian()) ? 'Hari' : 'Mgg' }} Bolong
                    </div>
                @endif
            </div>
        @empty
            <span class="text-slate-300">-</span>
        @endforelse
    </td>

    <!-- 7. PROGRES PEMBAYARAN -->
    <td class="py-3 px-3.5">
        @forelse($customer->customerPackages as $cp)
            <div class="mb-2 last:mb-0 space-y-1">
                <div class="flex justify-between text-[10px] font-semibold">
                    <span class="text-emerald-700 font-bold font-mono">{{ $cp->formatted_paid }}</span>
                    <span class="text-slate-600 font-bold font-mono">{{ $cp->progress_percent }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full {{ $cp->progress_percent >= 100 ? 'bg-emerald-600' : ($cp->isSafeThresholdReached() ? 'bg-emerald-500' : 'bg-amber-500') }}" style="width: {{ min(100, $cp->progress_percent) }}%"></div>
                </div>
            </div>
        @empty
            <span class="text-slate-300">-</span>
        @endforelse
    </td>

    <!-- 8. AKSI CEKLIS -->
    <td class="py-3 px-3 text-center">
        @if($customer->customerPackages->isNotEmpty())
            <a href="{{ route('payments.checklist', $customer->customerPackages->first()) }}" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[10px] transition inline-flex items-center shadow-2xs">
                <i class="fa-solid fa-list-check mr-1"></i> Ceklis
            </a>
        @else
            <span class="text-slate-300">-</span>
        @endif
    </td>
</tr>
