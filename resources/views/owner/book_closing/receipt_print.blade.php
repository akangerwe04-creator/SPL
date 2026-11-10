<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Tutup Buku - {{ $customerPackage->customer?->name }} ({{ $customerPackage->customer?->code }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; color: black !important; }
            .print-border { border: 1px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 md:p-8 font-sans antialiased text-xs">

    <!-- FLOATING ACTIONS BAR (NO PRINT) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('owner.book-closing.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition flex items-center space-x-1.5">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Menu Tutup Buku</span>
        </a>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold shadow-md transition flex items-center space-x-1.5">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Simpan PDF Berita Acara</span>
        </button>
    </div>

    <!-- PRINT SHEET (A4) -->
    <div class="max-w-3xl mx-auto bg-white p-8 md:p-10 rounded-2xl shadow-sm border border-slate-200 space-y-6">
        
        <!-- KOP SURAT RESMI SPL -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-4">
            <div class="flex items-center space-x-4">
                @if($appSetting->logo_path)
                    <img src="{{ $appSetting->logo_url }}" alt="Logo" class="w-14 h-14 object-contain">
                @else
                    <div class="w-14 h-14 rounded-xl bg-pink-600 text-white font-black text-2xl flex items-center justify-center">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                @endif
                <div>
                    <h1 class="font-extrabold text-base tracking-wider uppercase text-slate-900 font-heading">
                        {{ $appSetting->app_name ?: 'SUSI PARSEL LEBARAN (SPL)' }}
                    </h1>
                    <div class="text-[11px] text-slate-600 font-medium">{{ $appSetting->title ?: 'PROGRAM PARSEL & TABUNGAN LEBARAN' }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">
                        {{ $appSetting->owner_address ?: 'Jl. Pasar Tradisional No. 88, Jawa Barat' }} • Telp: {{ $appSetting->contact_person }}
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Lampiran Keputusan</div>
                <div class="text-xs font-mono font-black text-pink-700 mt-0.5">
                    BA-TB/{{ date('Y') }}/{{ str_pad($customerPackage->id, 5, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ date('d F Y') }}</div>
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center space-y-1">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 underline">
                BERITA ACARA SERAH TERIMA & KEPUTUSAN TUTUP BUKU
            </h2>
            <p class="text-[11px] text-slate-500">
                Pengesahan Pemenuhan Hak Konsumen pada Penutupan Musim Parsel Lebaran
            </p>
        </div>

        <!-- BIODATA KONSUMEN & PAKET -->
        <div class="grid grid-cols-2 gap-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <div class="space-y-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Identitas Konsumen:</div>
                <div class="font-extrabold text-slate-900 text-xs">{{ $customerPackage->customer?->name }}</div>
                <div class="text-slate-600">Kode: <span class="font-mono font-bold">{{ $customerPackage->customer?->code ?? '-' }}</span></div>
                <div class="text-slate-600">No. HP: {{ $customerPackage->customer?->phone ?? '-' }}</div>
                <div class="text-slate-600">Alamat: {{ $customerPackage->customer?->address ?? '-' }}</div>
            </div>
            <div class="space-y-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Informasi Paket & Agen:</div>
                <div class="font-extrabold text-slate-900 text-xs">{{ $customerPackage->package?->name ?? '-' }}</div>
                <div class="text-slate-600">Jenis: <span class="capitalize font-bold">{{ $customerPackage->package?->type }}</span> • Wadah: {{ $customerPackage->packaging?->name ?? 'Standar' }}</div>
                <div class="text-slate-600">Agen Pembina: <span class="font-bold text-slate-900">{{ $customerPackage->agent?->name ?? 'Tanpa Agen' }}</span></div>
                <div class="text-slate-600">Keputusan Disahkan: <span class="font-semibold">{{ $customerPackage->decided_at?->format('d/m/Y H:i') ?? date('d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- REKAPITULASI SETORAN & TITIK AMAN -->
        <div class="grid grid-cols-3 gap-3 p-3 rounded-xl bg-pink-50/50 border border-pink-200 text-center font-mono">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase">Target Paket</div>
                <div class="text-xs font-black text-slate-900 mt-0.5">{{ $customerPackage->formatted_target }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-emerald-800 uppercase">Total Setor Terkumpul</div>
                <div class="text-xs font-black text-emerald-800 mt-0.5">{{ $customerPackage->formatted_paid }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-purple-900 uppercase">Status Titik Aman</div>
                <div class="text-xs font-black mt-0.5 {{ $customerPackage->isSafeThresholdReached() ? 'text-emerald-700' : 'text-amber-800' }}">
                    {{ $customerPackage->isSafeThresholdReached() ? 'Lolos Titik Aman / Lunas' : 'Penyesuaian Owner' }}
                </div>
            </div>
        </div>

        <!-- RINCIAN BARANG YANG DISERAHKAN -->
        @php
            $pf = $customerPackage->partialFulfillment;
            $items = $pf?->items ?? collect();
        @endphp
        <div class="space-y-2">
            <div class="font-bold text-slate-900 text-xs flex items-center justify-between border-b border-slate-200 pb-1">
                <span>Rincian Barang yang Diserahkan:</span>
                <span class="text-[10px] text-slate-500 font-mono">{{ $items->count() }} Macam Barang</span>
            </div>

            <table class="w-full text-left border-collapse text-[11px]">
                <thead>
                    <tr class="bg-slate-100 font-bold uppercase text-[9px] text-slate-700 border-b border-slate-200">
                        <th class="py-1.5 px-2 w-8 text-center">No</th>
                        <th class="py-1.5 px-2">Nama Barang</th>
                        <th class="py-1.5 px-2">Kategori</th>
                        <th class="py-1.5 px-2 text-center w-20">Jumlah</th>
                        <th class="py-1.5 px-2 text-right w-24">Harga Satuan</th>
                        <th class="py-1.5 px-2 text-right w-28">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $iIdx => $it)
                        <tr>
                            <td class="py-1.5 px-2 text-center text-slate-400 font-bold">{{ $iIdx + 1 }}</td>
                            <td class="py-1.5 px-2 font-bold text-slate-900">
                                {{ $it->item?->name ?? 'Barang Parsel' }}
                                @if($it->is_bonus)
                                    <span class="text-[9px] font-extrabold text-amber-800 bg-amber-100 px-1 py-0.2 rounded ml-1">BONUS</span>
                                @endif
                            </td>
                            <td class="py-1.5 px-2 text-slate-500 text-[10px]">{{ $it->item?->category ?? 'Sembako' }}</td>
                            <td class="py-1.5 px-2 text-center font-bold">{{ $it->quantity }} {{ $it->item?->base_unit ?? 'pcs' }}</td>
                            <td class="py-1.5 px-2 text-right font-mono text-slate-600">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                            <td class="py-1.5 px-2 text-right font-mono font-bold text-slate-900">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-slate-400 italic">
                                Pemenuhan dalam bentuk pencairan uang tunai / tanpa rincian barang fisik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-bold border-t-2 border-slate-300 text-xs bg-slate-50">
                        <td colspan="5" class="py-2 px-2 text-right uppercase">Total Nilai Barang:</td>
                        <td class="py-2 px-2 text-right font-mono font-black text-pink-700">
                            Rp {{ number_format($pf?->total_items_value ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- RINCIAN DANA & BIAYA -->
        <div class="grid grid-cols-2 gap-4 p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs">
            <div class="space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-600">Pencairan Uang Tunai Konsumen:</span>
                    <span class="font-bold font-mono text-slate-900">Rp {{ number_format($pf?->cash_payout ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Potongan Administrasi SPL:</span>
                    <span class="font-bold font-mono text-slate-900">Rp {{ number_format($pf?->admin_fee ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-1 font-bold">
                    <span>Sisa Saldo Pengembalian:</span>
                    <span class="font-mono text-emerald-700">Rp {{ number_format($pf?->remaining_refund ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Catatan Keputusan Owner:</div>
                <p class="text-[11px] text-slate-700 italic mt-0.5 leading-relaxed">
                    {{ $pf?->notes ?: 'Telah disetujui dan disahkan sesuai ketentuan Tutup Buku Musim Parsel Lebaran SPL.' }}
                </p>
            </div>
        </div>

        <!-- TANDA TANGAN 3 PIHAK -->
        <div class="pt-6 border-t border-slate-200">
            <div class="grid grid-cols-3 gap-4 text-center">
                <!-- Pihak 1: Konsumen -->
                <div class="space-y-16">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Konsumen Penerima,</div>
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-36">
                            {{ $customerPackage->customer?->name }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Tanda Tangan & Nama Terang</div>
                    </div>
                </div>

                <!-- Pihak 2: Agen Pembina -->
                <div class="space-y-16">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Agen Pembina Lapangan,</div>
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-36">
                            {{ $customerPackage->agent?->name ?? 'Staff Agen' }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Tanda Tangan & Nama Terang</div>
                    </div>
                </div>

                <!-- Pihak 3: Owner / Admin SPL -->
                <div class="space-y-16">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Owner / Super Admin SPL,</div>
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-36">
                            {{ $appSetting->owner_name ?: 'Ibu Hj. Susi Susilawati' }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Penanggung Jawab Usaha</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER VERIFIKASI -->
        <div class="text-[9px] text-slate-400 text-center pt-4 border-t border-slate-100 flex items-center justify-between">
            <span>Dicetak otomatis oleh Sistem Antigravity ERP MINI SPL • Dokumen sah penutupan buku musim parsel.</span>
            <span>ID: CP-{{ $customerPackage->id }}-{{ date('YmdHi') }}</span>
        </div>

    </div>

</body>
</html>

