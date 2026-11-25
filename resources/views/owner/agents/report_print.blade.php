@php
    $section = request('section', 'all'); // 'all', 'detail', 'summary'
    $selectedAgentId = request('agent_id');
    $singleAgent = ($selectedAgentId && $agents->count() === 1) ? $agents->first() : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $singleAgent ? 'Rincian Konsumen & Paket - Agen ' . $singleAgent->name : ($section === 'detail' ? 'Laporan Rincian Konsumen per Agen' : 'Laporan Rekapitulasi Distribusi Agen & Paket Konsumen') }} - {{ $appSetting->app_name }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4 landscape; margin: 8mm; }
            .page-break { page-break-before: always; }
            .avoid-break { break-inside: avoid; page-break-inside: avoid; }
            .agent-sheet-break { page-break-after: always; break-after: page; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 md:p-8 font-sans">

    <!-- FLOATING ACTION BUTTONS (NO PRINT) -->
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-3 pb-4 no-print text-xs">
        <div class="flex items-center space-x-2 flex-wrap gap-y-2">
            <a href="{{ route('owner.agents.report', request()->all()) }}" class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 flex items-center shadow-2xs">
                &larr; Kembali ke Tampilan Web
            </a>

            <!-- PILIHAN BAGIAN DOKUMEN -->
            <div class="flex items-center space-x-1 bg-white p-1 rounded-xl border border-slate-300 shadow-2xs">
                <a href="{{ route('owner.agents.report.print', array_merge(request()->all(), ['section' => 'all'])) }}" 
                   class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition {{ $section === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    Semua (Lengkap)
                </a>
                <a href="{{ route('owner.agents.report.print', array_merge(request()->all(), ['section' => 'detail'])) }}" 
                   class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition {{ $section === 'detail' ? 'bg-purple-700 text-white shadow-2xs' : 'text-purple-800 hover:bg-purple-50' }}">
                    <i class="fa-solid fa-users-viewfinder mr-1"></i> Khusus Rincian Konsumen
                </a>
                <a href="{{ route('owner.agents.report.print', array_merge(request()->all(), ['section' => 'summary'])) }}" 
                   class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition {{ $section === 'summary' ? 'bg-emerald-700 text-white shadow-2xs' : 'text-emerald-800 hover:bg-emerald-50' }}">
                    Khusus Rekap Matriks
                </a>
            </div>

            <!-- FILTER AGEN CEPAT -->
            <div class="flex items-center space-x-1.5 bg-white px-2.5 py-1 rounded-xl border border-slate-300 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500"><i class="fa-solid fa-user-tie text-slate-400 mr-1"></i> Agen:</span>
                <select onchange="window.location.href = this.value" class="px-2 py-0.5 rounded-lg border border-slate-200 bg-slate-50 font-bold text-xs text-slate-800 focus:outline-none cursor-pointer">
                    <option value="{{ route('owner.agents.report.print', array_merge(request()->except('agent_id'), ['section' => $section])) }}">
                        -- Semua Agen ({{ $allAgentsList->count() }} Orang) --
                    </option>
                    @foreach($allAgentsList as $ag)
                        <option value="{{ route('owner.agents.report.print', array_merge(request()->all(), ['agent_id' => $ag->id])) }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                            Agen: {{ $ag->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <button onclick="window.print()" class="px-5 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-extrabold rounded-xl shadow transition flex items-center shrink-0">
            <i class="fa-solid fa-print mr-1.5"></i> Cetak Dokumen / Simpan PDF
        </button>
    </div>

    <div class="max-w-6xl mx-auto bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">

        <!-- KOP RESMI SURAT SPL -->
        <div class="flex items-center justify-between pb-4 border-b-2 border-slate-900 mb-6">
            <div class="flex items-center space-x-4">
                @if($appSetting->logo_path)
                    <img src="{{ asset($appSetting->logo_path) }}" alt="Logo SPL" class="h-16 w-16 object-contain rounded-lg">
                @else
                    <div class="h-14 w-14 rounded-xl bg-emerald-800 text-white font-extrabold flex items-center justify-center text-xl">
                        SPL
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tight text-slate-900 leading-tight">
                        {{ $appSetting->app_name ?: 'SPL (SUSI PARSEL LEBARAN)' }}
                    </h1>
                    <div class="text-xs font-bold text-emerald-800 mt-0.5">
                        Pimpinan: {{ $appSetting->owner_name ?: 'Susi' }}
                    </div>
                    <div class="text-[11px] text-slate-600">
                        {{ $appSetting->owner_address ?: 'Pusat Distribusi Parsel & Tabungan Lebaran, Paseh' }}
                    </div>
                </div>
            </div>
            <div class="text-right text-[11px] text-slate-600 leading-snug">
                <div><strong>Kontak / WA:</strong> {{ $appSetting->contact_person ?: '0812-3456-7890' }}</div>
                <div><strong>Musim:</strong> {{ $appSetting->program_period ?: 'Lebaran 1447 H / 2026' }}</div>
                <div class="text-slate-400 mt-1">Dicetak: {{ date('d F Y, H:i') }} WIB</div>
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center mb-6">
            @if($singleAgent)
                <h2 class="text-base sm:text-lg font-black uppercase tracking-wide text-slate-900">
                    LEMBAR RESMI RINCIAN KONSUMEN &amp; PAKET YANG DIPILIH
                </h2>
                <div class="text-xs font-bold text-purple-900 mt-1">
                    AGEN PENANGGUNG JAWAB: {{ strtoupper($singleAgent->name) }} &bull; NO. WA: {{ $singleAgent->phone ?: '-' }} &bull; ALAMAT: {{ $singleAgent->address ?: '-' }}
                </div>
            @elseif($section === 'detail')
                <h2 class="text-base sm:text-lg font-black uppercase tracking-wide text-slate-900">
                    LAPORAN RINCIAN KONSUMEN &amp; PAKET YANG DIPILIH PER AGEN
                </h2>
                <div class="text-xs text-slate-500 mt-0.5">
                    Daftar lengkap konsumen binaan dan rincian pilihan paket per masing-masing agen lapangan
                </div>
            @else
                <h2 class="text-base sm:text-lg font-black uppercase tracking-wide text-slate-900">
                    LAPORAN KESELURUHAN DISTRIBUSI AGEN &amp; PERSEBARAN PAKET KONSUMEN
                </h2>
                <div class="text-xs text-slate-500 mt-0.5">
                    Rekapitulasi resmi agen lapangan, alamat, jumlah binaan konsumen, dan jenis paket yang dipesan
                </div>
            @endif
        </div>

        <!-- 4 SUMMARY BADGES -->
        <div class="grid grid-cols-4 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 text-center text-xs mb-6">
            @if($singleAgent)
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Agen Lapangan</div>
                    <div class="text-base font-extrabold text-slate-900 mt-0.5">{{ $singleAgent->name }}</div>
                    <div class="text-[9px] text-slate-500">{{ $singleAgent->phone ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Konsumen Binaan</div>
                    <div class="text-base font-extrabold text-blue-900 mt-0.5">{{ $singleAgent->customers->count() }} Konsumen</div>
                    <div class="text-[9px] text-slate-500">Lolos Aman: {{ $singleAgent->safe_customers_count }} &bull; Belum: {{ $singleAgent->unreached_customers_count }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Paket Terpesan</div>
                    <div class="text-base font-extrabold text-amber-800 mt-0.5">{{ $singleAgent->total_orders }} Paket</div>
                    <div class="text-[9px] text-slate-500">Tingkat Aman: {{ $singleAgent->safe_rate }}%</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Target Nilai Pesanan</div>
                    <div class="text-base font-extrabold text-emerald-800 mt-0.5">Rp {{ number_format($singleAgent->total_target_amount, 0, ',', '.') }}</div>
                    <div class="text-[9px] text-slate-500">Disetor: Rp {{ number_format($singleAgent->total_paid_amount, 0, ',', '.') }}</div>
                </div>
            @else
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Agen Lapangan</div>
                    <div class="text-base font-extrabold text-slate-900 mt-0.5">{{ $agents->count() }} Orang</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Konsumen Binaan</div>
                    <div class="text-base font-extrabold text-blue-900 mt-0.5">{{ $totalCustomersCount }} Konsumen</div>
                    <div class="text-[9px] text-slate-500">H: {{ $totalHarianCustomersCount }} &bull; M: {{ $totalMingguanCustomersCount }} &bull; E: {{ $totalEcerCustomersCount }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Paket Terpesan</div>
                    <div class="text-base font-extrabold text-amber-800 mt-0.5">{{ $totalPackagesCount }} Paket</div>
                    <div class="text-[9px] text-slate-500">H: {{ $totalHarianOrdersCount }} &bull; M: {{ $totalMingguanOrdersCount }} &bull; E: {{ $totalEcerOrdersCount }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Target Nilai Pesanan</div>
                    <div class="text-base font-extrabold text-emerald-800 mt-0.5">Rp {{ number_format($totalTargetNominalSum, 0, ',', '.') }}</div>
                </div>
            @endif
        </div>

        @if($section !== 'detail')
        <!-- BAGIAN I: TABEL REKAPITULASI PARSEL HARIAN -->
        <div class="mb-8">
            <div class="font-extrabold text-xs uppercase tracking-wider text-slate-900 mb-2 flex items-center justify-between">
                <span>I. REKAPITULASI AGEN, ALAMAT &amp; JUMLAH PESANAN PARSEL HARIAN</span>
                <span class="text-[10px] text-slate-500 font-semibold font-mono">{{ $harianPackages->count() }} Jenis Paket Parsel Harian</span>
            </div>

            <table class="w-full text-left border-collapse text-[11px] border border-slate-900">
                <thead>
                    <tr class="bg-emerald-50 text-slate-900 font-bold border-b border-slate-900 text-center">
                        <th class="py-2 px-2 border-r border-slate-300 w-8">No</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">Nama Agen</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">No. WA</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">Alamat Agen</th>
                        <th class="py-2 px-2 border-r border-slate-300 w-24 bg-emerald-100/70">Jml Konsumen (Harian)</th>
                        @foreach($harianPackages as $pkg)
                            <th class="py-1.5 px-1.5 border-r border-slate-300 text-center min-w-[55px]">
                                <div class="font-mono text-[9px] font-bold text-emerald-800">{{ $pkg->code }}</div>
                                <div class="text-[9px] font-normal leading-tight truncate max-w-[85px]">{{ $pkg->name }}</div>
                                <div class="text-[8px] text-slate-500 font-mono">{{ $pkg->installment_count }} Hari</div>
                            </th>
                        @endforeach
                        <th class="py-2 px-2 w-20 bg-emerald-200/60 font-extrabold">Total Parsel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($agents as $idx => $agent)
                        <tr>
                            <td class="py-2 px-2 text-center font-mono border-r border-slate-200">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2.5 font-bold text-slate-900 border-r border-slate-200">{{ $agent->name }}</td>
                            <td class="py-2 px-2.5 font-mono text-[10px] border-r border-slate-200">{{ $agent->phone }}</td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-[10px] text-slate-600">{{ $agent->address ?: '-' }}</td>
                            <td class="py-2 px-2 text-center font-extrabold text-emerald-900 bg-emerald-50/40 border-r border-slate-200">
                                {{ $agent->harian_customers_count }} Orang
                            </td>
                            @foreach($harianPackages as $pkg)
                                @php
                                    $cnt = $agent->harian_package_counts[$pkg->id] ?? 0;
                                @endphp
                                <td class="py-2 px-1.5 text-center border-r border-slate-200 font-semibold {{ $cnt > 0 ? 'text-slate-900 font-bold bg-emerald-50/30' : 'text-slate-300' }}">
                                    {{ $cnt > 0 ? $cnt : '-' }}
                                </td>
                            @endforeach
                            <td class="py-2 px-2 text-center font-extrabold text-emerald-950 bg-emerald-100/40">
                                {{ $agent->total_harian_orders }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-900 text-white font-bold border-t-2 border-slate-900 text-[11px]">
                        <td colspan="4" class="py-2.5 px-3 text-right uppercase tracking-wider font-extrabold">
                            TOTAL SE-SPL (PARSEL HARIAN):
                        </td>
                        <td class="py-2.5 px-2 text-center text-emerald-200 font-extrabold border-r border-slate-800">
                            {{ $totalHarianCustomersCount }}
                        </td>
                        @foreach($harianPackages as $pkg)
                            @php
                                $pkgSum = $harianPackageTotals[$pkg->id] ?? 0;
                            @endphp
                            <td class="py-2.5 px-1.5 text-center border-r border-slate-800 {{ $pkgSum > 0 ? 'text-emerald-300 font-extrabold' : 'text-slate-500' }}">
                                {{ $pkgSum > 0 ? $pkgSum : '-' }}
                            </td>
                        @endforeach
                        <td class="py-2.5 px-2 text-center text-emerald-300 font-extrabold">
                            {{ $totalHarianOrdersCount }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BAGIAN II: TABEL REKAPITULASI PAKET MINGGUAN LAINNYA / STANDAR -->
        @if($mingguanPackages->isNotEmpty())
        <div class="mb-8 avoid-break">
            <div class="font-extrabold text-xs uppercase tracking-wider text-slate-900 mb-2 flex items-center justify-between">
                <span>II. REKAPITULASI AGEN, ALAMAT &amp; JUMLAH PESANAN PAKET MINGGUAN LAINNYA</span>
                <span class="text-[10px] text-purple-700 font-semibold font-mono">{{ $mingguanPackages->count() }} Jenis Paket Mingguan Reguler</span>
            </div>

            <table class="w-full text-left border-collapse text-[11px] border border-slate-900">
                <thead>
                    <tr class="bg-purple-50 text-slate-900 font-bold border-b border-slate-900 text-center">
                        <th class="py-2 px-2 border-r border-slate-300 w-8">No</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">Nama Agen</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">No. WA</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">Alamat Agen</th>
                        <th class="py-2 px-2 border-r border-slate-300 w-24 bg-purple-100/70">Jml Konsumen (Mingguan)</th>
                        @foreach($mingguanPackages as $pkg)
                            <th class="py-1.5 px-1.5 border-r border-slate-300 text-center min-w-[55px]">
                                <div class="font-mono text-[9px] font-bold text-purple-800">{{ $pkg->code }}</div>
                                <div class="text-[9px] font-normal leading-tight truncate max-w-[85px]">{{ $pkg->name }}</div>
                                <div class="text-[8px] text-slate-500 font-mono">{{ $pkg->installment_count }} Minggu</div>
                            </th>
                        @endforeach
                        <th class="py-2 px-2 w-20 bg-purple-200/60 font-extrabold">Total Mingguan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($agents as $idx => $agent)
                        <tr>
                            <td class="py-2 px-2 text-center font-mono border-r border-slate-200">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2.5 font-bold text-slate-900 border-r border-slate-200">{{ $agent->name }}</td>
                            <td class="py-2 px-2.5 font-mono text-[10px] border-r border-slate-200">{{ $agent->phone }}</td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-[10px] text-slate-600">{{ $agent->address ?: '-' }}</td>
                            <td class="py-2 px-2 text-center font-extrabold text-purple-900 bg-purple-50/40 border-r border-slate-200">
                                {{ $agent->mingguan_customers_count }} Orang
                            </td>
                            @foreach($mingguanPackages as $pkg)
                                @php
                                    $cnt = $agent->mingguan_package_counts[$pkg->id] ?? 0;
                                @endphp
                                <td class="py-2 px-1.5 text-center border-r border-slate-200 font-semibold {{ $cnt > 0 ? 'text-slate-900 font-bold bg-purple-50/30' : 'text-slate-300' }}">
                                    {{ $cnt > 0 ? $cnt : '-' }}
                                </td>
                            @endforeach
                            <td class="py-2 px-2 text-center font-extrabold text-purple-950 bg-purple-100/40">
                                {{ $agent->total_mingguan_orders }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-purple-950 text-white font-bold border-t-2 border-slate-900 text-[11px]">
                        <td colspan="4" class="py-2.5 px-3 text-right uppercase tracking-wider font-extrabold">
                            TOTAL SE-SPL (MINGGUAN LAINNYA):
                        </td>
                        <td class="py-2.5 px-2 text-center text-purple-200 font-extrabold border-r border-purple-800">
                            {{ $totalMingguanCustomersCount }}
                        </td>
                        @foreach($mingguanPackages as $pkg)
                            @php
                                $pkgSum = $mingguanPackageTotals[$pkg->id] ?? 0;
                            @endphp
                            <td class="py-2.5 px-1.5 text-center border-r border-purple-800 {{ $pkgSum > 0 ? 'text-purple-300 font-extrabold' : 'text-purple-600/60' }}">
                                {{ $pkgSum > 0 ? $pkgSum : '-' }}
                            </td>
                        @endforeach
                        <td class="py-2.5 px-2 text-center text-purple-200 font-extrabold">
                            {{ $totalMingguanOrdersCount }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- BAGIAN III: TABEL REKAPITULASI PARSEL ECERAN / MINGGUAN (DENGAN NAMA BARANG ECERAN) -->
        <div class="mb-8 avoid-break">
            <div class="font-extrabold text-xs uppercase tracking-wider text-slate-900 mb-2 flex items-center justify-between">
                <span>III. REKAPITULASI AGEN &amp; PERSEBARAN BARANG ECERAN (MINGGUAN)</span>
                <span class="text-[10px] text-blue-700 font-semibold font-mono">{{ count($ecerItemColumns) }} Komoditas Barang Eceran</span>
            </div>

            <table class="w-full text-left border-collapse text-[11px] border border-slate-900">
                <thead>
                    <tr class="bg-blue-50 text-slate-900 font-bold border-b border-slate-900 text-center">
                        <th class="py-2 px-2 border-r border-slate-300 w-8">No</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">Nama Agen</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">No. WA</th>
                        <th class="py-2 px-2.5 border-r border-slate-300 text-left">Alamat Agen</th>
                        <th class="py-2 px-2 border-r border-slate-300 w-24 bg-blue-100/70">Jml Konsumen (Eceran)</th>
                        @foreach($ecerItemColumns as $colName)
                            <th class="py-1.5 px-1.5 border-r border-slate-300 text-center min-w-[65px]">
                                <div class="text-[8px] font-extrabold uppercase text-blue-800">Eceran</div>
                                <div class="text-[9px] font-bold leading-tight truncate max-w-[90px]">{{ $colName }}</div>
                            </th>
                        @endforeach
                        <th class="py-2 px-2 w-20 bg-blue-200/60 font-extrabold">Total Item</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($agents as $idx => $agent)
                        <tr>
                            <td class="py-2 px-2 text-center font-mono border-r border-slate-200">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2.5 font-bold text-slate-900 border-r border-slate-200">{{ $agent->name }}</td>
                            <td class="py-2 px-2.5 font-mono text-[10px] border-r border-slate-200">{{ $agent->phone }}</td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-[10px] text-slate-600">{{ $agent->address ?: '-' }}</td>
                            <td class="py-2 px-2 text-center font-extrabold text-blue-900 bg-blue-50/40 border-r border-slate-200">
                                {{ $agent->ecer_customers_count }} Orang
                            </td>
                            @foreach($ecerItemColumns as $colName)
                                @php
                                    $custCount = $agent->ecer_item_counts[$colName] ?? 0;
                                @endphp
                                <td class="py-2 px-1.5 text-center border-r border-slate-200 font-semibold {{ $custCount > 0 ? 'text-blue-950 font-black bg-blue-50/40' : 'text-slate-300' }}">
                                    {{ $custCount > 0 ? $custCount . ' Org' : '-' }}
                                </td>
                            @endforeach
                            <td class="py-2 px-2 text-center font-extrabold text-blue-950 bg-blue-100/40">
                                {{ $agent->total_ecer_orders }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-blue-950 text-white font-bold border-t-2 border-slate-900 text-[11px]">
                        <td colspan="4" class="py-2.5 px-3 text-right uppercase tracking-wider font-extrabold">
                            TOTAL SE-SPL (PARSEL ECERAN):
                        </td>
                        <td class="py-2.5 px-2 text-center text-blue-200 font-extrabold border-r border-slate-800">
                            {{ $totalEcerCustomersCount }}
                        </td>
                        @foreach($ecerItemColumns as $colName)
                            @php
                                $totCust = $ecerItemTotals[$colName]['customers'] ?? 0;
                            @endphp
                            <td class="py-2.5 px-1.5 text-center border-r border-slate-800 {{ $totCust > 0 ? 'text-blue-200 font-extrabold' : 'text-slate-500' }}">
                                {{ $totCust > 0 ? $totCust . ' Org' : '-' }}
                            </td>
                        @endforeach
                        <td class="py-2.5 px-2 text-center text-blue-200 font-extrabold">
                            {{ $totalEcerOrdersCount }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($tabunganPackages->isNotEmpty())
            <!-- BAGIAN IV: TABEL REKAPITULASI TABUNGAN LEBARAN -->
            <div class="mb-8 avoid-break">
                <div class="font-extrabold text-xs uppercase tracking-wider text-slate-900 mb-2 flex items-center justify-between">
                    <span>IV. REKAPITULASI AGEN &amp; PAKET TABUNGAN LEBARAN</span>
                    <span class="text-[10px] text-amber-700 font-semibold font-mono">{{ $tabunganPackages->count() }} Jenis Paket Tabungan</span>
                </div>

                <table class="w-full text-left border-collapse text-[11px] border border-slate-900">
                    <thead>
                        <tr class="bg-amber-50 text-slate-900 font-bold border-b border-slate-900 text-center">
                            <th class="py-2 px-2 border-r border-slate-300 w-8">No</th>
                            <th class="py-2 px-2.5 border-r border-slate-300 text-left">Nama Agen</th>
                            <th class="py-2 px-2.5 border-r border-slate-300 text-left">No. WA</th>
                            <th class="py-2 px-2.5 border-r border-slate-300 text-left">Alamat Agen</th>
                            <th class="py-2 px-2 border-r border-slate-300 w-24 bg-amber-100/70">Jml Konsumen</th>
                            @foreach($tabunganPackages as $pkg)
                                <th class="py-1.5 px-1.5 border-r border-slate-300 text-center min-w-[55px]">
                                    <div class="font-mono text-[9px] font-bold text-amber-800">{{ $pkg->code }}</div>
                                    <div class="text-[9px] font-normal leading-tight truncate max-w-[85px]">{{ $pkg->name }}</div>
                                    <div class="text-[8px] text-amber-700 font-mono">{{ $pkg->formatted_target }}</div>
                                </th>
                            @endforeach
                            <th class="py-2 px-2 w-20 bg-amber-200/60 font-extrabold">Total Tabungan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($agents as $idx => $agent)
                            <tr>
                                <td class="py-2 px-2 text-center font-mono border-r border-slate-200">{{ $idx + 1 }}</td>
                                <td class="py-2 px-2.5 font-bold text-slate-900 border-r border-slate-200">{{ $agent->name }}</td>
                                <td class="py-2 px-2.5 font-mono text-[10px] border-r border-slate-200">{{ $agent->phone }}</td>
                                <td class="py-2 px-2.5 border-r border-slate-200 text-[10px] text-slate-600">{{ $agent->address ?: '-' }}</td>
                                <td class="py-2 px-2 text-center font-extrabold text-amber-900 bg-amber-50/40 border-r border-slate-200">
                                    {{ $agent->tabungan_customers_count }} Orang
                                </td>
                                @foreach($tabunganPackages as $pkg)
                                    @php
                                        $cnt = $agent->tabungan_package_counts[$pkg->id] ?? 0;
                                    @endphp
                                    <td class="py-2 px-1.5 text-center border-r border-slate-200 font-semibold {{ $cnt > 0 ? 'text-slate-900 font-bold bg-amber-50/30' : 'text-slate-300' }}">
                                        {{ $cnt > 0 ? $cnt : '-' }}
                                    </td>
                                @endforeach
                                <td class="py-2 px-2 text-center font-extrabold text-amber-950 bg-amber-100/40">
                                    {{ $agent->total_tabungan_orders }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-950 text-white font-bold border-t-2 border-slate-900 text-[11px]">
                            <td colspan="4" class="py-2.5 px-3 text-right uppercase tracking-wider font-extrabold">
                                TOTAL SE-SPL (TABUNGAN):
                            </td>
                            <td class="py-2.5 px-2 text-center text-amber-200 font-extrabold border-r border-slate-800">
                                {{ $totalTabunganCustomersCount }}
                            </td>
                            @foreach($tabunganPackages as $pkg)
                                @php
                                    $pkgSum = $tabunganPackageTotals[$pkg->id] ?? 0;
                                @endphp
                                <td class="py-2.5 px-1.5 text-center border-r border-slate-800 {{ $pkgSum > 0 ? 'text-amber-300 font-extrabold' : 'text-slate-500' }}">
                                    {{ $pkgSum > 0 ? $pkgSum : '-' }}
                                </td>
                            @endforeach
                            <td class="py-2.5 px-2 text-center text-amber-300 font-extrabold">
                                {{ $totalTabunganOrdersCount }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
        @endif

        @if($section !== 'summary')
        <!-- BAGIAN II (DETAIL): RINCIAN HIERARKIS SUB-DATA KONSUMEN PER AGEN -->
        <div class="{{ $section !== 'detail' ? 'page-break' : '' }} mb-8">
            <div class="font-extrabold text-xs uppercase tracking-wider text-slate-900 mb-3 flex items-center justify-between">
                <span>{{ $singleAgent ? 'RINCIAN KONSUMEN & PAKET YANG DIPILIH' : 'II. RINCIAN SUB-DATA KONSUMEN & PAKET YANG DIPILIH PER AGEN' }}</span>
                <span class="text-[9px] text-slate-500 font-semibold font-mono">Urutan: Parsel &rarr; Eceran &rarr; Tabungan | Harian &rarr; Mingguan</span>
            </div>

            @foreach($agents as $agent)
                <div class="mb-6 border border-slate-300 rounded-xl overflow-hidden avoid-break {{ ($section === 'detail' && !$loop->last) ? 'agent-sheet-break' : '' }}">
                    <!-- HEADER AGEN -->
                    <div class="bg-slate-100 px-4 py-2.5 border-b border-slate-300 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-black text-slate-900 uppercase">AGEN: {{ $agent->name }}</span>
                            <span class="text-slate-500 ml-2">&bull; WA: {{ $agent->phone }}</span>
                            <span class="text-slate-600 ml-2">&bull; Alamat: <strong>{{ $agent->address ?: '-' }}</strong></span>
                        </div>
                        <div class="font-bold text-slate-700">
                            Binaan: <span class="text-blue-800 font-extrabold">{{ $agent->customers->count() }} Konsumen</span> | 
                            Pesanan: <span class="text-emerald-800 font-extrabold">{{ $agent->total_orders }} Paket</span> | 
                            Aman: <span class="text-emerald-700 font-black">{{ $agent->safe_customers_count }}</span> | 
                            Belum: <span class="text-amber-800 font-black">{{ $agent->unreached_customers_count }}</span>
                        </div>
                    </div>

                    <!-- TABEL SUB-DATA KONSUMEN -->
                    <table class="w-full text-left border-collapse text-[10px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <th class="py-1.5 px-2.5 w-8 text-center">No</th>
                                <th class="py-1.5 px-2.5 w-36">Nama Konsumen</th>
                                <th class="py-1.5 px-2.5 w-28">No. HP</th>
                                <th class="py-1.5 px-2.5">Alamat Konsumen</th>
                                <th class="py-1.5 px-2.5 min-w-[170px]">Paket yang Dipilih</th>
                                <th class="py-1.5 px-2.5 text-right w-24">Target</th>
                                <th class="py-1.5 px-2.5 text-right w-24">Cicilan</th>
                                <th class="py-1.5 px-2.5 text-right w-24">Uang Masuk</th>
                                <th class="py-1.5 px-2.5 text-center w-24">Titik Aman</th>
                                <th class="py-1.5 px-2.5 text-center w-24">Progres &amp; Hari Lunas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($agent->customers as $cIdx => $cust)
                                <tr>
                                    <td class="py-1.5 px-2.5 text-center font-mono text-slate-400">{{ $cIdx + 1 }}</td>
                                    <td class="py-1.5 px-2.5 font-bold text-slate-900">
                                        {{ $cust->name }}
                                        <div class="text-[9px] text-slate-400 font-mono">{{ $cust->code ?: '-' }}</div>
                                    </td>
                                    <td class="py-1.5 px-2.5 text-slate-600 font-mono">{{ $cust->phone ?: '-' }}</td>
                                    <td class="py-1.5 px-2.5 text-slate-600">{{ $cust->address ?: '-' }}</td>
                                    <td class="py-1.5 px-2.5">
                                        @forelse($cust->customerPackages as $cp)
                                            @php
                                                $pkg = $cp->package;
                                            @endphp
                                            @if(!$pkg)
                                                <div class="text-[9px] text-slate-400 italic">
                                                    Paket tidak ditemukan (ID: {{ $cp->package_id }})
                                                </div>
                                                @continue
                                            @endif
                                            <div class="font-bold text-slate-800">
                                                <span class="font-mono text-[9px] font-bold px-1 rounded bg-slate-200 text-slate-800 mr-1">
                                                    {{ $pkg->code }}
                                                </span>
                                                <span class="text-[8px] font-bold uppercase px-1 rounded bg-slate-100 text-slate-700 mr-1">
                                                    {{ $pkg->isParsel() ? 'Parsel' : ($pkg->isEcer() ? 'Eceran' : 'Tabungan') }} ({{ $pkg->isHarian() ? 'Harian' : 'Mingguan' }})
                                                </span>
                                                {{ $pkg->name }}
                                                @if($cp->packaging)
                                                    <div class="text-[9px] text-amber-900 font-bold mt-0.5">
                                                        &bull; Kemasan: {{ $cp->packaging->name }} (+Rp {{ number_format($cp->packaging_price, 0, ',', '.') }})
                                                    </div>
                                                @else
                                                    <div class="text-[8px] text-slate-400 mt-0.5">
                                                        &bull; Kemasan: Standar
                                                    </div>
                                                @endif
                                                <div class="text-[8px] text-emerald-800 font-bold mt-0.5">
                                                    &bull; Lunas: {{ $cp->paid_installments_count }} / {{ $cp->total_installments_count }} {{ $cp->period_unit_label }}
                                                </div>
                                                @if($pkg->isEcer())
                                                    @if($cp->customItems->isNotEmpty())
                                                        <div class="text-[9px] text-blue-800 font-semibold mt-0.5">
                                                            Barang: {{ $cp->customItems->map(fn($ci) => ($ci->item ? $ci->item->name : 'Item') . ' (' . $ci->quantity . ')')->implode(', ') }}
                                                        </div>
                                                    @elseif($pkg->packageItems->isNotEmpty())
                                                        <div class="text-[9px] text-blue-800 font-semibold mt-0.5">
                                                            Barang: {{ $pkg->packageItems->map(fn($pi) => ($pi->item ? $pi->item->name : 'Item') . ' (' . $pi->quantity . ')')->implode(', ') }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-slate-300 italic">Belum memilih</span>
                                        @endforelse
                                    </td>
                                    <td class="py-1.5 px-2.5 text-right font-bold text-slate-900 font-mono">
                                        @forelse($cust->customerPackages as $cp)
                                            <div>{{ $cp->formatted_target }}</div>
                                        @empty
                                            <div class="text-slate-300">-</div>
                                        @endforelse
                                    </td>
                                    <td class="py-1.5 px-2.5 text-right text-slate-700 font-semibold font-mono">
                                        @forelse($cust->customerPackages as $cp)
                                            <div>
                                                @if($cp->package)
                                                    {{ $cp->package->formatted_installment }} / {{ strtolower($cp->package->period_label) }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-slate-300">-</div>
                                        @endforelse
                                    </td>
                                    <td class="py-1.5 px-2.5 text-right font-bold text-emerald-800 font-mono">
                                        @forelse($cust->customerPackages as $cp)
                                            <div>{{ $cp->formatted_paid }}</div>
                                        @empty
                                            <div class="text-slate-300">-</div>
                                        @endforelse
                                    </td>
                                    <td class="py-1.5 px-2.5 text-center">
                                        @forelse($cust->customerPackages as $cp)
                                            @if($cp->progress_percent >= 100)
                                                <span class="font-bold text-emerald-900 bg-emerald-100 px-1.5 py-0.2 rounded text-[8px]">Lunas</span>
                                            @elseif($cp->isSafeThresholdReached())
                                                <span class="font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded text-[8px]">Lolos Aman</span>
                                            @elseif($cp->package && $cp->package->hasSafeThreshold())
                                                <span class="font-bold text-amber-900 bg-amber-100 px-1 py-0.2 rounded text-[8px] whitespace-nowrap">Kurang {{ number_format($cp->safe_deficit_nominal, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-slate-400 text-[8px]">-</span>
                                            @endif
                                        @empty
                                            <span class="text-slate-300 text-[8px]">-</span>
                                        @endforelse
                                    </td>
                                    <td class="py-1.5 px-2.5 text-center font-bold text-slate-700">
                                        @forelse($cust->customerPackages as $cp)
                                            <div class="mb-1 last:mb-0 space-y-0.5">
                                                <div>{{ $cp->progress_percent }}%</div>
                                                <div class="text-[8px] text-emerald-900 font-extrabold bg-emerald-50 border border-emerald-200 px-1 py-0.2 rounded whitespace-nowrap">
                                                    {{ $cp->paid_installments_count }} {{ $cp->period_unit_label }} Lunas
                                                </div>
                                            </div>
                                        @empty
                                            <span class="text-slate-300 text-[8px]">-</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-3 text-center text-slate-400 italic">
                                        Belum ada konsumen binaan terdaftar pada agen ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100 text-slate-800 font-bold border-t border-slate-300 text-[10px]">
                                <td colspan="4" class="py-2 px-2.5 text-right font-extrabold uppercase">
                                    Subtotal Agen {{ $agent->name }} ({{ $agent->customers->count() }} Konsumen):
                                </td>
                                <td class="py-2 px-2.5 font-extrabold text-slate-900">
                                    {{ $agent->total_orders }} Paket Terdaftar
                                </td>
                                <td class="py-2 px-2.5 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($agent->total_target_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-2.5 text-right font-mono text-slate-400">
                                    -
                                </td>
                                <td class="py-2 px-2.5 text-right font-mono font-bold text-emerald-800">
                                    Rp {{ number_format($agent->total_paid_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-2.5 text-center font-extrabold text-emerald-900">
                                    {{ $agent->safe_customers_count }} Lolos
                                </td>
                                <td class="py-2 px-2.5 text-center font-bold text-slate-700">
                                    {{ $agent->safe_rate }}% Aman
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    @if($singleAgent || $section === 'detail')
                        <div class="p-3.5 bg-slate-50 border-t border-slate-200 text-[10px] flex justify-between items-end avoid-break">
                            <div>
                                <div class="text-slate-500 font-medium">Tanda Tangan Penerima / Agen Lapangan:</div>
                                <div class="h-10"></div>
                                <div class="font-extrabold text-slate-900 underline text-[11px]">{{ $agent->name }}</div>
                                <div class="text-[9px] text-slate-400">Agen SPL &bull; Telp: {{ $agent->phone ?: '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-slate-500 font-medium">Paseh, {{ date('d F Y') }}</div>
                                <div class="text-slate-500 font-medium">Pimpinan &amp; Penanggung Jawab SPL:</div>
                                <div class="h-10"></div>
                                <div class="font-extrabold text-slate-900 underline text-[11px]">{{ $appSetting->owner_name ?: 'Hj. Susi Susilawati, S.E.' }}</div>
                                <div class="text-[9px] text-slate-400">Owner Super Admin SPL</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        @if(!$singleAgent && $section !== 'detail')
        <!-- TANDA TANGAN PENGESAHAN DOKUMEN -->
        <div class="mt-8 pt-4 border-t border-slate-300 text-xs flex justify-between items-end avoid-break">
            <div>
                <div class="text-[11px] text-slate-500">
                    Catatan SPL: Laporan ini diterbitkan sebagai rekapitulasi data resmi operasional dan logistik pesanan parsel/tabungan.
                </div>
            </div>
            <div class="text-center w-64">
                <div class="text-slate-600 mb-1">Paseh, {{ date('d F Y') }}</div>
                <div class="font-bold text-slate-800 mb-16">Pengelola & Penanggung Jawab SPL,</div>
                <div class="font-black text-slate-900 underline text-sm">
                    {{ $appSetting->owner_name ?: 'Hj. Susi Susilawati, S.E.' }}
                </div>
                <div class="text-[10px] text-slate-500">Owner Super Admin SPL 2026</div>
            </div>
        </div>
        @endif

    </div>

</body>
</html>

