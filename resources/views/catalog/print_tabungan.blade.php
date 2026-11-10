



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brosur Katalog Tabungan Lebaran - {{ $setting->title }}</title>
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
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 font-sans">

    <!-- FLOATING ACTION BUTTONS -->
    <div class="max-w-4xl mx-auto flex items-center justify-between pb-4 no-print text-xs">
        <button onclick="window.history.back()" class="font-bold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Pengaturan
        </button>
        <button onclick="window.print()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow transition">
            Cetak Brosur Tabungan (Print / PDF)
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">

        <!-- HEADER BANNER -->
        <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-yellow-600 text-white p-6 rounded-2xl mb-6 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @if($setting->logo_path)
                    <img src="{{ asset($setting->logo_path) }}" alt="{{ $setting->app_name }}" class="h-16 w-16 object-contain rounded-xl bg-white p-1 border border-white/20 shadow-xs">
                @endif
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-amber-900 uppercase tracking-wider mb-2">
                        Program Tabungan Cerdas Idul Fitri
                    </span>
                    <h1 class="text-2xl font-serif-title font-extrabold tracking-wide uppercase">
                        KATALOG TABUNGAN LEBARAN BERHADIAH BONUS
                    </h1>
                    <div class="text-xs text-amber-100 mt-1">
                        {{ $setting->program_period }} &bull; Hubungi: {{ $setting->contact_person }}
                    </div>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <span class="text-xs font-bold text-amber-100 uppercase tracking-wider">{{ $setting->app_name }}</span>
            </div>
        </div>

        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
            Menabung rutin tiap minggu, saat mendekati Lebaran uang tunai <strong>cair 100% utuh</strong> tanpa potongan biaya, ditambah <strong>GRATIS BONUS BARANG PARSEL</strong> siap dibawa pulang!
        </p>

        <!-- NABUNG & ISI PAKET BONUS TABLE (TOTAL NOMINAL DIRAHASIAKAN DARI BROSUR) -->
        <table class="w-full text-left border-collapse text-xs mb-6">
            <thead>
                <tr class="bg-amber-500 text-white font-bold text-center">
                    <th class="py-3 px-3 text-left">Nama Paket Tabungan</th>
                    <th class="py-3 px-3">Setoran Berkala (Angsuran)</th>
                    <th class="py-3 px-3">DURASI</th>
                    <th class="py-3 px-3 bg-pink-700 text-left">ISI TABUNGAN & GRATIS HADIAH BONUS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 border-x border-b border-slate-200">
                @foreach($packages as $pkg)
                    <tr class="hover:bg-amber-50/40 transition">
                        <td class="py-3.5 px-3">
                            <div class="flex items-center space-x-2.5">
                                @if($pkg->image_path)
                                    <img src="{{ asset($pkg->image_path) }}" alt="{{ $pkg->name }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200 shrink-0">
                                @endif
                                <div>
                                    <div class="font-extrabold text-slate-900 text-sm">{{ $pkg->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $pkg->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-3 text-center font-bold text-slate-800 text-sm">
                            <span class="font-extrabold text-pink-700 text-base">{{ $pkg->formatted_installment }}</span>
                            <div class="text-[10px] text-slate-500 font-medium">/ {{ strtolower($pkg->period_label) }}</div>
                        </td>
                        <td class="py-3.5 px-3 text-center font-semibold text-slate-600">
                            {{ $pkg->installment_count }} {{ $pkg->period_label }}
                            <div class="text-[9px] text-emerald-700 font-bold uppercase">Cair Lebaran</div>
                        </td>
                        <td class="py-3.5 px-3 bg-emerald-50/30">
                            <div class="mb-1.5 text-xs font-black text-emerald-900 flex items-center">
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-extrabold mr-1.5 uppercase">Cair</span>
                                <span>Uang Tunai: <strong>{{ $pkg->formatted_base_price }}</strong></span>
                            </div>
                            <div class="text-[10px] font-bold text-pink-900 uppercase tracking-wider mt-1 mb-0.5">Hadiah Bonus Gratis:</div>
                            <ul class="space-y-0.5">
                                @forelse($pkg->bonusItems as $pi)
                                    <li class="flex items-center text-xs font-semibold text-pink-950">
                                        <span class="text-pink-600 font-bold mr-1">&bull;</span>
                                        <span><strong>{{ $pi->quantity }} {{ $pi->unit ?: ($pi->item?->base_unit ?: 'pcs') }}</strong> {{ $pi->item?->name ?: 'Hadiah Bonus' }}</span>
                                    </li>
                                @empty
                                    <li class="text-slate-400 italic text-[11px]">Tanpa bonus barang</li>
                                @endforelse
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ATURAN TABUNGAN HIGHLIGHT -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-xs">
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-900">
                <div class="font-bold flex items-center mb-1 text-red-800">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Aturan Bonus Barang
                </div>
                <div class="text-[11px] leading-relaxed text-red-700">
                    Hadiah bonus barang otomatis <strong>hangus</strong> apabila peserta tidak menyetor iuran selama <strong>3 minggu berturut-turut</strong>.
                </div>
            </div>

            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                <div class="font-bold flex items-center mb-1 text-amber-800">
                    <i class="fa-solid fa-percent mr-1.5"></i> Aturan Pembatalan
                </div>
                <div class="text-[11px] leading-relaxed text-amber-700">
                    Jika konsumen mundur di tengah jalan, dikenakan potongan biaya operasional sebesar <strong>10%</strong> dari total uang yang telah disetorkan.
                </div>
            </div>
        </div>

        <!-- DYNAMIC T&C FOOTER -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 leading-relaxed">
            <div class="font-bold text-slate-800 uppercase tracking-wider mb-1">Syarat & Ketentuan Umum:</div>
            <div class="whitespace-pre-line">{{ $setting->terms_and_conditions }}</div>
        </div>

    </div>

</body>
</html>

