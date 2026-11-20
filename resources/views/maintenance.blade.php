<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSI PARSEL - Server Sedang Dalam Perbaikan</title>
    <link rel="icon" type="image/png" href="{{ !empty($appSetting->logo_path) ? asset($appSetting->logo_path) : asset('icon-192.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        spl: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                            800: '#9d174d',
                            900: '#831843',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-slate-100 min-h-screen flex flex-col justify-between font-sans selection:bg-pink-500 selection:text-white relative overflow-x-hidden">

    <!-- BACKGROUND AMBIENT GLOW -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-pink-500/10 via-amber-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- 1. HEADER RESMI: SUSI PARSEL -->
    <header class="w-full border-b border-white/10 bg-slate-900/60 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                @if(!empty($appSetting->logo_path) && file_exists(public_path($appSetting->logo_path)))
                    <img src="{{ asset($appSetting->logo_path) }}" alt="SUSI PARSEL" class="h-10 w-10 sm:h-12 sm:w-12 object-contain rounded-xl bg-white p-1 shadow-md">
                @else
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-gradient-to-tr from-pink-700 to-rose-500 text-white font-black flex items-center justify-center text-lg sm:text-xl shadow-md border border-white/20">
                        SP
                    </div>
                @endif
                <div>
                    <h1 class="text-lg sm:text-2xl font-black tracking-tight text-white uppercase drop-shadow-xs">
                        SUSI PARSEL
                    </h1>
                    <p class="text-[10px] sm:text-xs text-pink-300 font-semibold tracking-wider uppercase">
                        SISTEM MANAJEMEN PARSEL & TABUNGAN HARI RAYA
                    </p>
                </div>
            </div>

            <!-- BADGE STATUS PEMELIHARAAN -->
            <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-400/30 text-amber-300 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>Mode Pemeliharaan Server</span>
            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTENT AREA -->
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 py-12 relative z-10">
        <div class="max-w-xl w-full text-center">

            <!-- ANIMATED ICON WIDGET -->
            <div class="relative inline-block mb-6">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-amber-500 via-orange-500 to-pink-600 flex items-center justify-center text-white text-4xl sm:text-5xl shadow-2xl mx-auto border-2 border-white/20 relative z-10 animate-pulse">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="absolute -top-2 -right-2 w-10 h-10 rounded-2xl bg-slate-900 border border-amber-400/50 text-amber-400 flex items-center justify-center text-lg shadow-lg z-20">
                    <i class="fa-solid fa-gears animate-spin" style="animation-duration: 6s;"></i>
                </div>
            </div>

            <!-- TITLE & SUBTITLE -->
            <h2 class="text-xl sm:text-3xl font-black text-white tracking-tight mb-4">
                Pemberitahuan Pemeliharaan Server
            </h2>

            <!-- PESAN UTAMA SESUAI PERMINTAAN RESMI -->
            <div class="bg-gradient-to-b from-slate-800/90 to-slate-900/90 border-2 border-amber-500/40 rounded-2xl p-6 sm:p-7 shadow-2xl backdrop-blur-md mb-6 relative overflow-hidden text-left sm:text-center">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-500 via-pink-500 to-amber-500"></div>

                <div class="flex items-center justify-center space-x-2 text-amber-400 text-xs font-black uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Informasi Penting</span>
                </div>

                <p class="text-sm sm:text-base text-slate-100 font-bold leading-relaxed">
                    mohon maaf server kami sedang dalam perbaikan, mohon maaf untuk sementara segala bentuk aktivitas tidak dapat dilakukan
                </p>

                <div class="mt-4 pt-4 border-t border-white/10 text-xs text-slate-400 leading-relaxed">
                    Kami sedang melakukan optimalisasi sistem, pemeliharaan basis data, dan sinkronisasi keamanan data pelanggan. Seluruh data transaksi, tabungan, dan pesanan parsel Anda tersimpan aman 100%. Layanan akan segera dibuka kembali secara normal.
                </div>
            </div>

            <!-- ACTION BUTTON: MUAT ULANG -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white font-extrabold text-xs shadow-lg shadow-pink-600/30 transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Muat Ulang Halaman (Cek Status)</span>
                </button>
            </div>

            <!-- TIMELINE INFO -->
            <div class="mt-6 text-[11px] text-slate-400 flex items-center justify-center space-x-2">
                <i class="fa-regular fa-clock text-slate-400"></i>
                <span>Waktu Server: {{ date('d F Y, H:i') }} WIB</span>
            </div>

        </div>
    </main>

    <!-- 3. FOOTER RESMI: Tim Support IT Maliq Solution -->
    <footer class="w-full border-t border-white/10 bg-slate-900/80 backdrop-blur-md py-5 relative z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="font-bold text-slate-300">Tim Support IT Maliq Solution</span>
            </div>

            <div class="text-[11px] text-slate-400">
                &copy; {{ date('Y') }} SUSI PARSEL &bull; Seluruh hak cipta dilindungi.
            </div>

            <!-- AKSES ADMINISTRATOR (TERSEMBUNYI / DISKRET) -->
            <div>
                <a href="{{ route('login') }}" class="inline-flex items-center space-x-1.5 text-[11px] text-slate-400 hover:text-pink-400 transition py-1 px-2.5 rounded-lg hover:bg-white/5 border border-transparent hover:border-white/10">
                    <i class="fa-solid fa-lock text-[10px]"></i>
                    <span>Akses Administrator</span>
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
