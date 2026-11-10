<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brosur Katalog Parsel - {{ $setting->title }}</title>
    <link rel="icon" type="image/png" href="{{ $setting->logo_path ? asset($setting->logo_path) : asset('icon-192.png') }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
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
        <button onclick="window.print()" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow transition">
            Cetak Brosur Parsel (Print / PDF)
        </button>
    </div>

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">

        <!-- KOP KATALOG MEWAH LEBARAN -->
        <div class="bg-gradient-to-r from-pink-800 via-rose-700 to-pink-900 text-white p-5 rounded-2xl mb-6 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-4">
                @if($setting->logo_path)
                    <img src="{{ asset($setting->logo_path) }}" alt="{{ $setting->app_name }}" class="h-16 w-16 object-contain rounded-xl bg-white p-1 border border-white/20 shadow-xs">
                @endif
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950 uppercase tracking-wider mb-1">
                        Katalog Resmi Idul Fitri
                    </span>
                    <h1 class="text-2xl font-serif-title font-extrabold tracking-wide uppercase">
                        {{ $setting->title }}
                    </h1>
                    <div class="text-xs text-pink-100 font-medium">
                        {{ $setting->program_period }}
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-pink-200 uppercase font-semibold">Layanan & Pemesanan:</div>
                <div class="text-sm font-extrabold text-white mt-0.5">{{ $setting->contact_person }}</div>
                @if($setting->bank_account_info)
                    <div class="text-[10px] text-pink-200 mt-0.5">{{ $setting->bank_account_info }}</div>
                @endif
            </div>
        </div>

        <!-- 4-COLUMN CSS GRID LAYOUT -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($packages as $pkg)
                <div class="border-2 border-emerald-600/30 rounded-2xl p-4 flex flex-col justify-between bg-white hover:border-emerald-600 transition shadow-xs">
                    <div>
                        <!-- CODE & TITLE -->
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                {{ $pkg->code }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">
                                {{ $pkg->installment_count }} {{ $pkg->period_label }}
                            </span>
                        </div>

                        @if($pkg->image_path)
                            <div class="h-32 w-full bg-slate-50 rounded-xl overflow-hidden mt-2 border border-slate-200">
                                <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <h3 class="font-bold text-sm text-slate-900 mt-2 line-clamp-2 min-h-[40px]">
                            {{ $pkg->name }}
                        </h3>

                        <!-- CICILAN HIGHLIGHT (BIAYA ADMIN & TOTAL NOMINAL DIRAHASIAKAN DARI BROSUR) -->
                        <div class="my-3 p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                            <div class="text-[10px] uppercase font-bold text-emerald-800">Cicilan Cukup</div>
                            <div class="text-xl font-extrabold text-emerald-700 leading-tight">
                                {{ $pkg->formatted_installment }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium">/ {{ strtolower($pkg->period_label) }} ({{ $pkg->installment_count }}x setoran)</div>
                        </div>

                        <!-- LIST ISI BARANG -->
                        <div class="space-y-1 text-xs text-slate-700 mt-2">
                            <div class="font-bold text-[11px] text-slate-900 uppercase tracking-wider mb-1 flex items-center">
                                <span>Isi Paket Lengkap:</span>
                            </div>
                            <ul class="space-y-1">
                                @foreach($pkg->packageItems as $pi)
                                    <li class="flex items-start text-[11px] leading-tight">
                                        <span class="text-emerald-600 font-bold mr-1.5">&bull;</span>
                                        @if($pi->item && $pi->item->isUang())
                                            <span class="text-emerald-900 font-bold flex items-center">
                                                <i class="fa-solid fa-money-bill-wave text-emerald-600 mr-1 text-[10px]"></i>
                                                <strong>{{ $pi->quantity }} {{ $pi->item->base_unit }}</strong> {{ $pi->item->name }}
                                            </span>
                                        @else
                                            <span><strong>{{ $pi->quantity }} {{ $pi->item->base_unit }}</strong> {{ $pi->item->name }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @if($pkg->packagings->isNotEmpty())
                            <div class="mt-2.5 pt-2 border-t border-slate-100 text-[10px] text-pink-900 bg-pink-50/60 p-1.5 rounded-lg">
                                <span class="font-extrabold flex items-center">
                                    <i class="fa-solid fa-box-archive mr-1 text-pink-600"></i> Pilihan Wadah Tersedia:
                                </span>
                                <div class="text-[9px] text-slate-600 mt-0.5">
                                    {{ $pkg->packagings->pluck('name')->implode(', ') }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 text-center text-[10px] font-bold text-slate-400">
                        Kemasan Parsel Box Elegan & Rapi
                    </div>
                </div>
            @endforeach
        </div>

        @if(!empty($packagings) && $packagings->isNotEmpty())
            <!-- PILIHAN WADAH & KEMASAN MEWAH (KOPER, KERANJANG, BOX HAMPERS) -->
            <div class="mb-6 p-4 rounded-2xl bg-pink-50/50 border-2 border-pink-200">
                <div class="flex items-center justify-between pb-2 border-b border-pink-200/80 mb-3">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-pink-600 inline-block"></span>
                        <h3 class="font-heading font-extrabold text-xs uppercase tracking-wider text-pink-950">
                            Pilihan Wadah & Kemasan Parsel Eksklusif (Koper, Keranjang Rotan, Box Hampers)
                        </h3>
                    </div>
                    <span class="text-[10px] font-bold text-pink-800">Dapat Dipilih Sesuai Selera Konsumen</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($packagings as $pkgKms)
                        <div class="p-2.5 bg-white rounded-xl border border-pink-100 flex flex-col justify-between shadow-2xs">
                            <div>
                                @if($pkgKms->image_path && file_exists(public_path($pkgKms->image_path)))
                                    <div class="h-24 w-full rounded-lg overflow-hidden border border-slate-100 mb-2">
                                        <img src="{{ asset($pkgKms->image_path) }}" alt="{{ $pkgKms->name }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="font-bold text-xs text-slate-900 leading-tight">{{ $pkgKms->name }}</div>
                                @if($pkgKms->description)
                                    <div class="text-[9px] text-slate-500 mt-1 line-clamp-2">{{ $pkgKms->description }}</div>
                                @endif
                            </div>
                            <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between">
                                <span class="font-mono text-[9px] font-bold text-pink-700 bg-pink-50 px-1 py-0.2 rounded">{{ $pkgKms->code }}</span>
                                <span class="text-[10px] font-black text-pink-900">+ {{ $pkgKms->formatted_price }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- DYNAMIC T&C FOOTER -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 leading-relaxed">
            <div class="font-bold text-slate-800 uppercase tracking-wider mb-1">
                Catatan Syarat & Ketentuan (T&C):
            </div>
            <div class="whitespace-pre-line">
                {{ $setting->terms_and_conditions }}
            </div>
        </div>

        <div class="text-center text-[10px] text-slate-400 mt-4">
            Dikeluarkan secara resmi oleh Pengelola {{ $setting->app_name ?: 'SPL (Susi Parsel Lebaran)' }} • Hak Cipta &copy; {{ date('Y') }}
        </div>

    </div>

</body>
</html>

