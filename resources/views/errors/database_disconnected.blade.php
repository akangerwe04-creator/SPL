<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi Database Terputus - SPL 2026</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 50%, #fecdd3 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-lg w-full bg-white rounded-3xl shadow-2xl border border-red-200 overflow-hidden my-auto">
        <!-- HEADER ALERT -->
        <div class="bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 p-6 text-white text-center relative">
            <div class="w-16 h-16 mx-auto mb-3 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                <i class="fa-solid fa-database animate-pulse"></i>
            </div>
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-300"></i> Mode Penyimpanan Permanen
            </div>
            <h1 class="text-xl md:text-2xl font-black tracking-tight leading-snug">Koneksi Database Terputus</h1>
            <p class="text-xs text-red-100 font-medium mt-1">Aplikasi tidak dapat berjalan tanpa koneksi database aktif.</p>
        </div>

        <!-- BODY CONTENT -->
        <div class="p-6 md:p-8 space-y-4">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                <h3 class="text-xs font-bold text-red-900 uppercase tracking-wide flex items-center">
                    <i class="fa-solid fa-shield-halved text-red-600 mr-2"></i> Proteksi Integritas Data
                </h3>
                <p class="text-xs text-red-800 font-medium mt-1.5 leading-relaxed">
                    Sistem <strong>SPL 2026</strong> tidak menggunakan penyimpanan sementara (<em>temporary cache</em>). Seluruh data transaksi, konsumen, setoran agen, parsel, dan pengaturan aplikasi wajib tersimpan secara <strong>permanen</strong> di dalam database.
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2 text-xs">
                <div class="font-bold text-slate-800 flex items-center justify-between pb-2 border-b border-slate-200">
                    <span>Status Sistem</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-red-100 text-red-700 font-bold text-[10px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-ping"></span> OFFLINE / DISCONNECTED
                    </span>
                </div>
                <div class="flex justify-between text-slate-600 py-1">
                    <span>Driver Database:</span>
                    <span class="font-mono font-bold text-slate-800">{{ config('database.default') }}</span>
                </div>
                <div class="flex justify-between text-slate-600 py-1">
                    <span>Penyimpanan Sementara:</span>
                    <span class="font-bold text-emerald-700"><i class="fa-solid fa-ban text-red-500 mr-1"></i> Dinonaktifkan (Zero Temporary)</span>
                </div>
                <div class="flex justify-between text-slate-600 py-1">
                    <span>Penyimpanan Aktif:</span>
                    <span class="font-bold text-slate-800"><i class="fa-solid fa-hard-drive text-pink-600 mr-1"></i> Database Permanen</span>
                </div>
            </div>

            @if(config('app.debug') && !empty($errorMessage))
                <div class="bg-slate-900 text-slate-200 p-3.5 rounded-xl font-mono text-[11px] leading-snug overflow-x-auto border border-slate-800">
                    <div class="text-red-400 font-bold mb-1 flex items-center">
                        <i class="fa-solid fa-bug mr-1.5 text-xs"></i> Pesan Teknis:
                    </div>
                    <div>{{ $errorMessage }}</div>
                </div>
            @endif

            <!-- ACTION BUTTONS -->
            <div class="pt-2">
                <button type="button" onclick="window.location.reload()" class="w-full py-3 px-4 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 active:from-red-800 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-red-500/25 transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-rotate text-sm"></i>
                    <span>Coba Sambungkan Ulang Database</span>
                </button>
            </div>

            <div class="text-center text-[11px] text-slate-500">
                Pastikan service database (SQLite file / MySQL XAMPP) telah berjalan normal di server Anda.
            </div>
        </div>
    </div>

</body>
</html>

