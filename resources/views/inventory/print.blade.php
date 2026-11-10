@php
    $appSetting = \App\Models\CatalogSetting::current();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Belanja Barang, Bonus, Kemasan & Uang - {{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }}</title>
    <link rel="icon" type="image/png" href="{{ $appSetting->logo_path ? asset($appSetting->logo_path) : asset('icon-192.png') }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 md:p-8 font-sans">

    <div class="max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0 space-y-8">
        
        <!-- ACTION BAR (NO PRINT) -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 no-print">
            <button onclick="window.history.back()" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                &larr; Kembali ke Aplikasi
            </button>
            <button onclick="window.print()" class="px-5 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-bold text-xs rounded-xl shadow-sm transition">
                Cetak Lembar Belanja & Uang (Print)
            </button>
        </div>

        <!-- HEADER KOP RESMI -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div class="flex items-center space-x-4">
                @if($appSetting->logo_path)
                    <img src="{{ asset($appSetting->logo_path) }}" alt="{{ $appSetting->app_name }}" class="h-16 w-16 object-contain rounded-xl border border-pink-200 p-1 bg-white">
                @else
                    <div class="h-14 w-14 rounded-xl bg-pink-700 text-white font-black flex items-center justify-center text-xl">
                        SPL
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-extrabold uppercase tracking-tight text-slate-900">
                        {{ $appSetting->app_name ?: 'SPL (SUSI PARSEL LEBARAN)' }}
                    </h1>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-pink-900 mt-0.5">
                        REKAP BELANJA GROSIR BARANG POKOK, BONUS, KEMASAN & PENYIAPAN UANG TUNAI
                    </h2>
                    <div class="text-[11px] text-slate-500 mt-1">
                        Dicetak pada: {{ date('d F Y H:i') }} • Total Anggaran Disiapkan: <strong>Rp {{ number_format($summary['grand_total_fund'], 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXECUTIVE SUMMARY BOX -->
        <div class="grid grid-cols-5 gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-300 text-xs">
            <div>
                <span class="text-slate-500 block">Total Paket Disiapkan:</span>
                <strong class="text-base font-extrabold text-pink-900">
                    {{ number_format($summary['total_packages_count'] ?? 0) }} Paket
                </strong>
                <span class="block text-[10px] text-slate-500">({{ $summary['total_parsel_count'] ?? 0 }} Parsel, {{ $summary['total_tabungan_count'] ?? 0 }} Tabungan, {{ $summary['total_ecer_count'] ?? 0 }} Ecer)</span>
            </div>
            <div>
                <span class="text-slate-500 block">1. Uang Tunai Konsumen:</span>
                <strong class="text-base font-extrabold text-emerald-800">
                    Rp {{ number_format($summary['total_cash_amount'], 0, ',', '.') }}
                </strong>
                <span class="block text-[10px] text-slate-500">({{ $summary['total_cash_recipients'] }} Konsumen / {{ $summary['total_cash_agents'] }} Agen)</span>
            </div>
            <div>
                <span class="text-slate-500 block">2. Modal Barang Pokok:</span>
                <strong class="text-base font-extrabold text-slate-900">
                    Rp {{ number_format($summary['total_estimated_cost'], 0, ',', '.') }}
                </strong>
                <span class="block text-[10px] text-slate-500">({{ count($items) }} Macam / {{ number_format($summary['total_base_pieces']) }} Pcs)</span>
            </div>
            <div>
                <span class="text-slate-500 block">3. Modal Bonus Paket:</span>
                <strong class="text-base font-extrabold text-amber-800">
                    Rp {{ number_format($summary['total_bonus_cost'] ?? 0, 0, ',', '.') }}
                </strong>
                <span class="block text-[10px] text-slate-500">({{ count($bonusItems ?? []) }} Macam / {{ number_format($summary['total_bonus_pieces'] ?? 0) }} Pcs)</span>
            </div>
            <div>
                <span class="text-slate-500 block">4. Modal Kemasan / Wadah:</span>
                <strong class="text-base font-extrabold text-purple-900">
                    Rp {{ number_format($summary['total_packaging_cost'] ?? 0, 0, ',', '.') }}
                </strong>
                <span class="block text-[10px] text-slate-500">({{ count($packagingItems ?? []) }} Model / {{ number_format($summary['total_packaging_units'] ?? 0) }} Unit)</span>
            </div>
        </div>

        <!-- ================= REKAPITULASI JUMLAH PAKET YANG HARUS DISIAPKAN ================= -->
        <div>
            <div class="flex items-center justify-between pb-2 mb-3 border-b-2 border-slate-900">
                <h3 class="font-extrabold uppercase text-sm tracking-wider text-slate-900">
                    REKAPITULASI JUMLAH PAKET YANG HARUS DISIAPKAN (PERMINTAAN KONSUMEN)
                </h3>
                <span class="text-xs font-bold text-slate-700">
                    Total: {{ number_format($summary['total_packages_count'] ?? 0) }} Paket ({{ $summary['total_packages_pending'] ?? 0 }} Harus Disiapkan)
                </span>
            </div>

            <table class="w-full text-left border-collapse text-xs mb-4">
                <thead>
                    <tr class="border-y-2 border-slate-900 bg-slate-100 font-bold text-slate-900">
                        <th class="py-2 px-2 text-center w-8">No</th>
                        <th class="py-2 px-3">Kode & Nama Paket</th>
                        <th class="py-2 px-2">Jenis & Skema</th>
                        <th class="py-2 px-3 text-center bg-slate-200/80 font-black">Jumlah Paket Disiapkan</th>
                        <th class="py-2 px-3">Komposisi Barang & Uang Total</th>
                        <th class="py-2 px-3">Sebaran Agen Penanggung Jawab</th>
                        <th class="py-2 px-2 text-center no-print">Ceklis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php
                        $orderedPackages = array_filter($packagesToPrepare, fn($p) => $p['total_orders'] > 0);
                    @endphp
                    @forelse($orderedPackages as $idx => $p)
                        <tr>
                            <td class="py-2.5 px-2 font-mono text-center text-slate-500">{{ $loop->iteration }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $p['package_name'] }}</div>
                                <div class="font-mono text-[10px] text-slate-500">Kode: {{ $p['package_code'] }}</div>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-bold text-[10px] uppercase text-slate-700 block">{{ $p['package_type_label'] }}</span>
                                <span class="text-[10px] text-slate-500">{{ $p['installment_label'] }} ({{ $p['installment_count'] }}x)</span>
                            </td>
                            <td class="py-2.5 px-3 text-center bg-slate-50">
                                <div class="font-black text-sm text-slate-900">{{ $p['total_orders'] }} Paket</div>
                                <div class="text-[10px] text-slate-500 font-medium">
                                    {{ $p['pending_orders'] }} Pending / {{ $p['fulfilled_orders'] }} Selesai
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-[11px]">
                                @if($p['total_cash_needed'] > 0)
                                    <div class="font-bold text-emerald-800">
                                        • Uang Tunai: Rp {{ number_format($p['total_cash_needed'], 0, ',', '.') }}
                                    </div>
                                @endif
                                @if(!empty($p['total_goods_needed']))
                                    <div class="text-slate-800">
                                        <span class="font-bold">Barang Pokok:</span>
                                        @foreach($p['total_goods_needed'] as $g)
                                            <span class="inline-block mr-1 text-[10px]">{{ $g['total_qty'] }} {{ $g['unit'] }} {{ $g['item_name'] }},</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($p['bonuses_needed']))
                                    <div class="text-amber-800">
                                        <span class="font-bold">Bonus:</span>
                                        @foreach($p['bonuses_needed'] as $b)
                                            <span class="inline-block mr-1 text-[10px]">{{ $b['total_qty'] }} {{ $b['unit'] }} {{ $b['item_name'] }},</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($p['packagings_needed']))
                                    <div class="text-purple-800">
                                        <span class="font-bold">Wadah:</span>
                                        @foreach($p['packagings_needed'] as $kms)
                                            <span class="inline-block mr-1 text-[10px]">{{ $kms['qty'] }} Unit {{ $kms['name'] }},</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-[10px]">
                                @foreach($p['agents'] as $ag)
                                    <div><strong>{{ $ag['name'] }}:</strong> {{ $ag['count'] }} paket</div>
                                @endforeach
                            </td>
                            <td class="py-2.5 px-2 text-center no-print">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-slate-400">
                                Tidak ada paket aktif dengan pesanan konsumen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900 font-bold bg-slate-50">
                        <td colspan="3" class="py-2.5 px-3 text-right">TOTAL KESELURUHAN PAKET HARUS DISIAPKAN:</td>
                        <td class="py-2.5 px-3 text-center font-extrabold text-slate-900 text-sm">
                            {{ number_format($summary['total_packages_count'] ?? 0) }} Paket
                        </td>
                        <td colspan="3" class="py-2.5 px-3 text-[11px] text-slate-600">
                            ({{ $summary['total_parsel_count'] ?? 0 }} Parsel • {{ $summary['total_tabungan_count'] ?? 0 }} Tabungan • {{ $summary['total_ecer_count'] ?? 0 }} Eceran)
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ================= BAGIAN 1: PERSIAPAN UANG TUNAI ================= -->
        <div class="page-break">
            <div class="flex items-center justify-between pb-2 mb-3 border-b-2 border-slate-900">
                <h3 class="font-extrabold uppercase text-sm tracking-wider text-slate-900">
                    BAGIAN 1: DAFTAR PERSIAPAN UANG TUNAI KONSUMEN & AGEN
                </h3>
                <span class="text-xs font-bold text-slate-700">
                    Subtotal Uang: Rp {{ number_format($summary['total_cash_amount'], 0, ',', '.') }}
                </span>
            </div>

            <table class="w-full text-left border-collapse text-xs mb-4">
                <thead>
                    <tr class="border-y-2 border-slate-900 bg-slate-100 font-bold text-slate-900">
                        <th class="py-2 px-2 text-center">No</th>
                        <th class="py-2 px-3">Nama Konsumen & Kode</th>
                        <th class="py-2 px-3">Agen Penanggung Jawab</th>
                        <th class="py-2 px-3">Paket & Keterangan Uang</th>
                        <th class="py-2 px-3 text-right">Uang Tunai (Rp)</th>
                        <th class="py-2 px-2 text-center no-print">Ceklis Amplop</th>
                        <th class="py-2 px-3 text-center">Paraf Penerima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($cashItems as $idx => $c)
                        <tr>
                            <td class="py-2 px-2 font-mono text-center text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3">
                                <div class="font-bold text-slate-900">{{ $c['customer_name'] }}</div>
                                <div class="text-[10px] text-slate-500">Kode: {{ $c['customer_code'] }} • Telp: {{ $c['customer_phone'] }}</div>
                            </td>
                            <td class="py-2 px-3 font-semibold text-slate-800">
                                {{ $c['agent_name'] }}
                            </td>
                            <td class="py-2 px-3">
                                <div class="font-bold text-slate-900">{{ $c['cash_description'] }}</div>
                                <div class="text-[10px] text-slate-500">{{ $c['package_name'] }} ({{ $c['period_text'] }})</div>
                            </td>
                            <td class="py-2 px-3 text-right font-extrabold text-slate-900">
                                Rp {{ number_format($c['cash_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-2 text-center no-print">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300">
                            </td>
                            <td class="py-2 px-3 text-center text-slate-400 font-mono text-[10px]">
                                ( ..................... )
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-slate-400">
                                Tidak ada penyiapan uang tunai untuk pesanan aktif saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900 font-bold bg-slate-50">
                        <td colspan="4" class="py-2.5 px-3 text-right">TOTAL UANG TUNAI YANG HARUS DISIAPKAN:</td>
                        <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 text-sm">
                            Rp {{ number_format($summary['total_cash_amount'], 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ================= BAGIAN 2: BELANJA GROSIR BARANG POKOK ================= -->
        <div class="page-break">
            <div class="flex items-center justify-between pb-2 mb-3 border-b-2 border-slate-900">
                <h3 class="font-extrabold uppercase text-sm tracking-wider text-slate-900">
                    BAGIAN 2: DAFTAR RENCANA BELANJA GROSIR BARANG FISIK (KONVERSI DUS)
                </h3>
                <span class="text-xs font-bold text-slate-700">
                    Subtotal Barang Pokok: Rp {{ number_format($summary['total_estimated_cost'], 0, ',', '.') }}
                </span>
            </div>

            <table class="w-full text-left border-collapse text-xs mb-4">
                <thead>
                    <tr class="border-y-2 border-slate-900 bg-slate-100 font-bold text-slate-900">
                        <th class="py-2 px-2 text-center">No</th>
                        <th class="py-2 px-3">Nama Barang</th>
                        <th class="py-2 px-3">Kategori</th>
                        <th class="py-2 px-3 text-center">Isi/Dus</th>
                        <th class="py-2 px-3 text-center">Total Unit (Pcs)</th>
                        <th class="py-2 px-3 font-extrabold">Rekomendasi Beli Grosir</th>
                        <th class="py-2 px-3 text-right">Estimasi Modal</th>
                        <th class="py-2 px-2 text-center no-print">Ceklis Beli</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($items as $idx => $r)
                        <tr>
                            <td class="py-2 px-2 font-mono text-center text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 font-bold text-slate-900">{{ $r['item_name'] }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $r['category'] }}</td>
                            <td class="py-2 px-3 text-center font-mono">{{ (int)$r['conversion_rate'] }} {{ $r['base_unit'] }}</td>
                            <td class="py-2 px-3 text-center font-bold">{{ number_format($r['total_base_qty']) }} {{ $r['base_unit'] }}</td>
                            <td class="py-2 px-3 font-extrabold text-slate-900">
                                {{ $r['wholesale_text'] }}
                                <span class="font-normal text-slate-500 text-[10px]">(atau {{ $r['wholesale_ceil'] }} {{ $r['wholesale_unit'] }})</span>
                            </td>
                            <td class="py-2 px-3 text-right font-bold text-slate-800">
                                Rp {{ number_format($r['estimated_cost'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-2 text-center no-print">
                                <input type="checkbox" class="w-4 h-4 rounded text-pink-600 border-slate-300">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-slate-400">Belum ada kebutuhan barang pokok.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900 font-bold bg-slate-50">
                        <td colspan="6" class="py-2.5 px-3 text-right">TOTAL ESTIMASI MODAL BARANG GROSIR:</td>
                        <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 text-sm">
                            Rp {{ number_format($summary['total_estimated_cost'], 0, ',', '.') }}
                        </td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ================= BAGIAN 3: BELANJA BARANG BONUS PAKET ================= -->
        @if(!empty($bonusItems))
            <div class="page-break">
                <div class="flex items-center justify-between pb-2 mb-3 border-b-2 border-slate-900">
                    <h3 class="font-extrabold uppercase text-sm tracking-wider text-slate-900">
                        BAGIAN 3: DAFTAR BELANJA BONUS PAKET (HADIAH GRATIS TABUNGAN & PARSEL)
                    </h3>
                    <span class="text-xs font-bold text-slate-700">
                        Subtotal Bonus: Rp {{ number_format($summary['total_bonus_cost'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <table class="w-full text-left border-collapse text-xs mb-4">
                    <thead>
                        <tr class="border-y-2 border-slate-900 bg-amber-50 font-bold text-slate-900">
                            <th class="py-2 px-2 text-center">No</th>
                            <th class="py-2 px-3">Nama Barang Bonus</th>
                            <th class="py-2 px-3">Kategori</th>
                            <th class="py-2 px-3 text-center">Isi/Dus</th>
                            <th class="py-2 px-3 text-center">Jumlah Bonus (Pcs)</th>
                            <th class="py-2 px-3 font-extrabold">Kebutuhan Grosir</th>
                            <th class="py-2 px-3 text-right">Estimasi Modal</th>
                            <th class="py-2 px-2 text-center no-print">Ceklis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($bonusItems as $idx => $b)
                            <tr>
                                <td class="py-2 px-2 font-mono text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3 font-bold text-slate-900">{{ $b['item_name'] }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $b['category'] }}</td>
                                <td class="py-2 px-3 text-center font-mono">{{ (int)$b['conversion_rate'] }} {{ $b['base_unit'] }}</td>
                                <td class="py-2 px-3 text-center font-bold text-amber-900">{{ number_format($b['total_base_qty']) }} {{ $b['base_unit'] }}</td>
                                <td class="py-2 px-3 font-extrabold text-slate-900">{{ $b['wholesale_text'] }}</td>
                                <td class="py-2 px-3 text-right font-bold text-slate-800">Rp {{ number_format($b['estimated_cost'], 0, ',', '.') }}</td>
                                <td class="py-2 px-2 text-center no-print">
                                    <input type="checkbox" class="w-4 h-4 rounded text-amber-600 border-slate-300">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-900 font-bold bg-amber-50">
                            <td colspan="6" class="py-2.5 px-3 text-right">TOTAL ESTIMASI MODAL BONUS:</td>
                            <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 text-sm">
                                Rp {{ number_format($summary['total_bonus_cost'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- ================= BAGIAN 4: BELANJA KEMASAN / WADAH PARSEL ================= -->
        @if(!empty($packagingItems))
            <div class="page-break">
                <div class="flex items-center justify-between pb-2 mb-3 border-b-2 border-slate-900">
                    <h3 class="font-extrabold uppercase text-sm tracking-wider text-slate-900">
                        BAGIAN 4: DAFTAR BELANJA KEMASAN PARSEL (KOPER, KERANJANG, BOX HAMPERS)
                    </h3>
                    <span class="text-xs font-bold text-slate-700">
                        Subtotal Kemasan: Rp {{ number_format($summary['total_packaging_cost'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <table class="w-full text-left border-collapse text-xs mb-4">
                    <thead>
                        <tr class="border-y-2 border-slate-900 bg-purple-50 font-bold text-slate-900">
                            <th class="py-2 px-2 text-center">No</th>
                            <th class="py-2 px-3">Kode Kemasan</th>
                            <th class="py-2 px-3">Nama Wadah / Kemasan</th>
                            <th class="py-2 px-3 text-right">Harga Modal Kulak</th>
                            <th class="py-2 px-3 text-center font-bold">Total Unit Dibeli</th>
                            <th class="py-2 px-3 text-right font-bold">Total Biaya Kulak</th>
                            <th class="py-2 px-2 text-center no-print">Ceklis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($packagingItems as $idx => $p)
                            <tr>
                                <td class="py-2 px-2 font-mono text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3 font-mono font-bold text-purple-900">{{ $p['code'] }}</td>
                                <td class="py-2 px-3 font-bold text-slate-900">{{ $p['name'] }}</td>
                                <td class="py-2 px-3 text-right font-mono">Rp {{ number_format($p['cost_price'], 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-center font-extrabold text-purple-900">{{ $p['total_qty'] }} Unit</td>
                                <td class="py-2 px-3 text-right font-extrabold text-slate-900">Rp {{ number_format($p['total_cost'], 0, ',', '.') }}</td>
                                <td class="py-2 px-2 text-center no-print">
                                    <input type="checkbox" class="w-4 h-4 rounded text-purple-600 border-slate-300">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-900 font-bold bg-purple-50">
                            <td colspan="5" class="py-2.5 px-3 text-right">TOTAL ESTIMASI MODAL KEMASAN:</td>
                            <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 text-sm">
                                Rp {{ number_format($summary['total_packaging_cost'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- FOOTER SIGNATURES -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs pt-6 border-t-2 border-slate-900">
            <div>
                <div class="text-slate-500 mb-14">Petugas Logistik / Pembelian,</div>
                <div class="font-bold underline text-slate-900">( ......................................... )</div>
            </div>
            <div>
                <div class="text-slate-500 mb-14">Koordinator Agen Lapangan,</div>
                <div class="font-bold underline text-slate-900">( ......................................... )</div>
            </div>
            <div>
                <div class="text-slate-500 mb-14">Mengetahui & Menyetujui (Owner),</div>
                <div class="font-bold underline text-slate-900">{{ $appSetting->owner_name ?: 'Susi' }}</div>
            </div>
        </div>

    </div>

</body>
</html>
