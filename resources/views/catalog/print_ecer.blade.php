<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brosur Katalog Eceran Bebas Rakit - {{ $setting->title }}</title>
    <link rel="icon" type="image/png" href="{{ $setting->logo_path ? asset($setting->logo_path) : asset('icon-192.png') }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4 landscape; margin: 8mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 font-sans">

    <!-- FLOATING ACTION BUTTONS -->
    <div class="max-w-7xl mx-auto flex items-center justify-between pb-4 no-print text-xs">
        <button onclick="window.history.back()" class="font-bold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Pengaturan
        </button>
        <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow transition">
            Cetak Brosur Eceran (Print / PDF)
        </button>
    </div>

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">

        <!-- HEADER BANNER -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white p-5 rounded-2xl mb-6 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-4">
                @if($setting->logo_path)
                    <img src="{{ asset($setting->logo_path) }}" alt="{{ $setting->app_name }}" class="h-16 w-16 object-contain rounded-xl bg-white p-1 border border-white/20 shadow-xs">
                @endif
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950 uppercase tracking-wider mb-1">
                        Format Campuran: Rakit Sendiri & Bundling
                    </span>
                    <h1 class="text-2xl font-serif-title font-extrabold tracking-wide uppercase">
                        KATALOG PARSEL ECERAN BEBAS PILIH
                    </h1>
                    <div class="text-xs text-blue-200">
                        {{ $setting->program_period }} &bull; Konsumen Bebas Merakit Isi Parsel Sesuai Keinginan
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-blue-200 uppercase font-semibold">Kontak Agen / CS:</div>
                <div class="text-sm font-extrabold text-white mt-0.5">{{ $setting->contact_person }}</div>
            </div>
        </div>

        <!-- 2-COLUMN MIXED LAYOUT (LEFT: 50 ITEMS TABLE, RIGHT: BUNDLING PACKAGES) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: 50 DAFTAR BARANG (7 COLS) -->
            <div class="lg:col-span-7 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 flex items-center">
                        <i class="fa-solid fa-list-check mr-1.5 text-blue-600"></i> Daftar {{ $items->count() }} Pilihan Barang Rakitan
                    </h3>
                    <span class="text-[10px] text-slate-400">Centang kotak untuk merakit parsel</span>
                </div>

                <div class="overflow-x-auto max-h-[700px] overflow-y-auto">
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold sticky top-0">
                                <th class="py-2 px-2 text-center w-8">Pilih</th>
                                <th class="py-2 px-2">Nama Barang</th>
                                <th class="py-2 px-2">Kategori</th>
                                <th class="py-2 px-2 text-right">Harga/Item</th>
                                <th class="py-2 px-2 text-center w-12">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $idx => $item)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-1.5 px-2 text-center">
                                        <input type="checkbox" class="w-3.5 h-3.5 rounded text-blue-600 border-slate-300">
                                    </td>
                                    <td class="py-1.5 px-2 font-semibold text-slate-900">
                                        {{ $item->name }}
                                    </td>
                                    <td class="py-1.5 px-2 text-[10px] text-slate-500">
                                        {{ $item->category }}
                                    </td>
                                    <td class="py-1.5 px-2 text-right font-bold text-emerald-700">
                                        {{ $item->formatted_price }}
                                    </td>
                                    <td class="py-1.5 px-2 text-center">
                                        <span class="inline-block w-8 border-b border-slate-300 text-center text-slate-400">...</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-2.5 rounded-lg bg-blue-50 border border-blue-200 text-[10px] text-blue-900">
                    <strong>Cara Hitung Cicilan Ecer:</strong> Total nilai belanja barang yang dicentang &divide; 30 Minggu = Nominal cicilan mingguan konsumen.
                </div>
            </div>

            <!-- RIGHT COLUMN: PAKET HEMAT BUNDLING (5 COLS) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="border-b border-slate-200 pb-2">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 flex items-center">
                        <i class="fa-solid fa-boxes-packing mr-1.5 text-amber-500"></i> Rekomendasi Paket Bundling Hemat
                    </h3>
                </div>

                <!-- BUNDLING CARD 1 -->
                <div class="border-2 border-amber-300 rounded-xl p-4 bg-amber-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-200 text-amber-900 uppercase">Hemat Lebaran</span>
                        <span class="text-xs font-bold text-slate-500">Rp 10.000 / minggu</span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900">Bundling Sembako & Sirup Manis</h4>
                    <p class="text-[11px] text-slate-600 leading-tight">
                        Kombinasi 2 Botol Sirup ABC Jeruk + 1 Pouch Minyak Goreng 2L + 2 Pcs Gula Pasir 1Kg + 1 Box Teh Celup.
                    </p>
                    <div class="text-right pt-2 border-t border-amber-200">
                        <span class="text-[10px] font-bold text-amber-900">Durasi: 30 Minggu Setoran</span>
                    </div>
                </div>

                <!-- BUNDLING CARD 2 -->
                <div class="border-2 border-emerald-300 rounded-xl p-4 bg-emerald-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 uppercase">Favorit Tamu</span>
                        <span class="text-xs font-bold text-slate-500">Rp 15.000 / minggu</span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900">Bundling Biskuit Kaleng & Kurma</h4>
                    <p class="text-[11px] text-slate-600 leading-tight">
                        Kombinasi 1 Kaleng Wafer Tango + 1 Kaleng Roma Kelapa + 2 Botol Marjan Melon + 1 Box Kurma Khalas.
                    </p>
                    <div class="text-right pt-2 border-t border-emerald-200">
                        <span class="text-[10px] font-bold text-emerald-900">Durasi: 30 Minggu Setoran</span>
                    </div>
                </div>

                <!-- BUNDLING CARD 3 -->
                <div class="border-2 border-indigo-300 rounded-xl p-4 bg-indigo-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-200 text-indigo-900 uppercase">Paket Sultan</span>
                        <span class="text-xs font-bold text-slate-500">Rp 25.000 / minggu</span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900">Bundling Khong Guan & Butter Cookies</h4>
                    <p class="text-[11px] text-slate-600 leading-tight">
                        Kombinasi Khong Guan Kaleng Merah 1600g + Monde Butter Cookies 454g + 2 Botol Marjan Cocopandan + 1 Pouch Nata de Coco.
                    </p>
                    <div class="text-right pt-2 border-t border-indigo-200">
                        <span class="text-[10px] font-bold text-indigo-900">Durasi: 30 Minggu Setoran</span>
                    </div>
                </div>

                <!-- T&C BOX -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[10px] text-slate-600 leading-relaxed">
                    <div class="font-bold text-slate-800 uppercase mb-1">Syarat & Ketentuan Rakitan:</div>
                    <div class="whitespace-pre-line">{{ $setting->terms_and_conditions }}</div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>

