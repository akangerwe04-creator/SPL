<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Distribusi Agen & Paket Konsumen - {{ $appSetting->app_name }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4 landscape; margin: 10mm; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 md:p-8 font-sans">

    <!-- FLOATING ACTION BUTTONS (NO PRINT) -->
    <div class="max-w-6xl mx-auto flex items-center justify-between pb-4 no-print text-xs">
        <button onclick="window.history.back()" class="font-bold text-slate-600 hover:text-slate-900 flex items-center">
            &larr; Kembali ke Tampilan Web
        </button>
        <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow transition flex items-center">
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
            <h2 class="text-base sm:text-lg font-black uppercase tracking-wide text-slate-900">
                LAPORAN KESELURUHAN DISTRIBUSI AGEN & PERSEBARAN PAKET KONSUMEN
            </h2>
            <div class="text-xs text-slate-500 mt-0.5">
                Rekapitulasi resmi agen lapangan, alamat, jumlah binaan konsumen, dan jenis paket yang dipesan
            </div>
        </div>

        <!-- 4 SUMMARY BADGES -->
        <div class="grid grid-cols-4 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 text-center text-xs mb-6">
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
        </div>

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

        <!-- BAGIAN V: RINCIAN HIERARKIS SUB-DATA KONSUMEN PER AGEN -->
        <div class="page-break mb-8">
            <div class="font-extrabold text-xs uppercase tracking-wider text-slate-900 mb-3 flex items-center justify-between">
                <span>V. RINCIAN SUB-DATA KONSUMEN &amp; PAKET YANG DIPILIH PER AGEN</span>
                <span class="text-[10px] text-slate-400 font-normal">Data detail konsumen binaan lapangan</span>
            </div>

            @foreach($agents as $agent)
                <div class="mb-6 border border-slate-300 rounded-xl overflow-hidden avoid-break">
                    <!-- HEADER AGEN -->
                    <div class="bg-slate-100 px-4 py-2.5 border-b border-slate-300 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-black text-slate-900 uppercase">AGEN: {{ $agent->name }}</span>
                            <span class="text-slate-500 ml-2">&bull; WA: {{ $agent->phone }}</span>
                            <span class="text-slate-600 ml-2">&bull; Alamat: <strong>{{ $agent->address ?: '-' }}</strong></span>
                        </div>
                        <div class="font-bold text-slate-700">
                            Binaan: <span class="text-blue-800 font-extrabold">{{ $agent->customers->count() }} Konsumen</span> | 
                            Pesanan: <span class="text-emerald-800 font-extrabold">{{ $agent->total_orders }} Paket</span>
                        </div>
                    </div>

                    <!-- TABEL SUB-DATA KONSUMEN -->
                    <table class="w-full text-left border-collapse text-[10px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <th class="py-1.5 px-2.5 w-8 text-center">No</th>
                                <th class="py-1.5 px-2.5 w-44">Nama Konsumen</th>
                                <th class="py-1.5 px-2.5 w-32">No. HP</th>
                                <th class="py-1.5 px-2.5">Alamat Konsumen</th>
                                <th class="py-1.5 px-2.5 min-w-[200px]">Paket yang Dipilih</th>
                                <th class="py-1.5 px-2.5 text-right w-28">Target Nominal</th>
                                <th class="py-1.5 px-2.5 text-right w-28">Cicilan/Periode</th>
                                <th class="py-1.5 px-2.5 text-center w-20">Progres</th>
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
                                            <div class="font-bold text-slate-800">
                                                <span class="font-mono text-[9px] font-bold px-1 rounded bg-slate-200 text-slate-800 mr-1">
                                                    {{ $cp->package->code }}
                                                </span>
                                                {{ $cp->package->name }}
                                                @if($cp->package->isEcer())
                                                    @if($cp->customItems->isNotEmpty())
                                                        <div class="text-[9px] text-blue-800 font-semibold mt-0.5">
                                                            Barang: {{ $cp->customItems->map(fn($ci) => ($ci->item ? $ci->item->name : 'Item') . ' (' . $ci->quantity . ')')->implode(', ') }}
                                                        </div>
                                                    @elseif($cp->package->packageItems->isNotEmpty())
                                                        <div class="text-[9px] text-blue-800 font-semibold mt-0.5">
                                                            Barang: {{ $cp->package->packageItems->map(fn($pi) => ($pi->item ? $pi->item->name : 'Item') . ' (' . $pi->quantity . ')')->implode(', ') }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-slate-300 italic">Belum memilih</span>
                                        @endforelse
                                    </td>
                                    <td class="py-1.5 px-2.5 text-right font-bold text-slate-900 font-mono">
                                        @foreach($cust->customerPackages as $cp)
                                            <div>{{ $cp->formatted_target }}</div>
                                        @endforeach
                                    </td>
                                    <td class="py-1.5 px-2.5 text-right text-emerald-800 font-semibold font-mono">
                                        @foreach($cust->customerPackages as $cp)
                                            <div>{{ $cp->package->formatted_installment }} / {{ strtolower($cp->package->period_label) }}</div>
                                        @endforeach
                                    </td>
                                    <td class="py-1.5 px-2.5 text-center font-bold text-slate-700">
                                        @foreach($cust->customerPackages as $cp)
                                            <div>{{ $cp->progress_percent }}%</div>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-3 text-center text-slate-400 italic">
                                        Belum ada konsumen binaan terdaftar pada agen ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <!-- TANDA TANGAN PENGESAHAN DOKUMEN -->
        <div class="mt-8 pt-4 border-t border-slate-300 text-xs flex justify-between items-end">
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

    </div>

</body>
</html>

