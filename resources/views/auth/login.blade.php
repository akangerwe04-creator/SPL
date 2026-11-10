@php
    $appSetting = \App\Models\CatalogSetting::current();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }}</title>
    
    <!-- PWA & Favicon Meta (Icon Pintasan SPL 2026) -->
    <link rel="manifest" href="{{ asset('manifest.json') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}?v={{ file_exists(public_path('favicon.png')) ? filemtime(public_path('favicon.png')) : time() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}?v={{ file_exists(public_path('favicon.png')) ? filemtime(public_path('favicon.png')) : time() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ file_exists(public_path('favicon.ico')) ? filemtime(public_path('favicon.ico')) : time() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ file_exists(public_path('apple-touch-icon.png')) ? filemtime(public_path('apple-touch-icon.png')) : time() }}">
    <meta name="theme-color" content="#db2777">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }}">
    <meta name="application-name" content="{{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pink: {
                            50: '#fff1f6',
                            100: '#fce7f1',
                            200: '#fbcfe8',
                            300: '#f9a8d4',
                            400: '#f472b6',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                            800: '#9d174d',
                            900: '#831843',
                            950: '#500724',
                        },
                        rose: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                            950: '#4c0519',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #fff0f5 0%, #fdf2f7 50%, #ffe4ec 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full my-auto">
        <!-- HEADER BRAND DENGAN LOGO USAHA YANG DIUNGGAH -->
        <div class="text-center mb-6">
            @if($appSetting->logo_path)
                <div class="w-20 h-20 mx-auto mb-3 p-2 bg-white rounded-2xl shadow-md border border-pink-200 flex items-center justify-center">
                    <img src="{{ $appSetting->logo_url }}" alt="{{ $appSetting->app_name }}" class="w-full h-full object-contain rounded-xl">
                </div>
            @else
                <div class="w-16 h-16 bg-gradient-to-tr from-pink-600 to-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-md text-white text-2xl font-bold">
                    <i class="fa-solid fa-gift"></i>
                </div>
            @endif
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">{{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }}</h1>
            <p class="text-xs text-slate-600 font-semibold mt-1">{{ $appSetting->title ?: 'Sistem ERP Mini Parsel & Tabungan Idul Fitri' }}</p>
        </div>

        <!-- CARD LOGIN (LIGHT PALETTE PINK CERAH) -->
        <div class="bg-white rounded-3xl shadow-xl shadow-pink-200/50 border border-pink-100 p-6 md:p-8">
            <h2 class="text-lg font-extrabold text-slate-900 mb-1">Masuk ke Sistem</h2>
            <p class="text-xs text-slate-600 font-medium mb-6">Gunakan akun Anda untuk mengelola tagihan, setoran, atau operasional parsel.</p>

            @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg text-xs font-semibold text-red-900 flex items-start">
                    <i class="fa-solid fa-circle-exclamation text-red-600 mr-2 mt-0.5"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-pink-50 border-l-4 border-pink-600 p-3 rounded-r-lg text-xs font-semibold text-pink-950 flex items-start">
                    <i class="fa-solid fa-circle-check text-pink-600 mr-2 mt-0.5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg text-xs font-semibold text-red-900 flex items-start">
                    <i class="fa-solid fa-circle-exclamation text-red-600 mr-2 mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- LOGIN FORM -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">Email atau Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-user text-sm"></i>
                        </span>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                            placeholder="Masukkan username atau email..."
                            class="w-full pl-10 pr-3.5 py-2.5 text-sm font-medium bg-pink-50/20 border border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200/60 focus:bg-white text-slate-900 transition placeholder:text-slate-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-3.5 py-2.5 text-sm font-medium bg-pink-50/20 border border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200/60 focus:bg-white text-slate-900 transition placeholder:text-slate-400">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-pink-300 text-pink-600 focus:ring-pink-400 mr-2">
                        <span class="font-medium text-slate-700">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 active:from-pink-800 active:to-rose-800 text-white font-bold text-sm rounded-xl shadow-md shadow-pink-500/25 transition flex items-center justify-center space-x-2">
                    <span>Masuk Sekarang</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-4 text-xs text-slate-500 font-medium">
            &copy; {{ date('Y') }} {{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }} &bull; Musim 1447 H / 2026
        </div>
    </div>

    <!-- PWA & Companion Script (Badging, In-App WhatsApp Detect, Offline SW) -->
    <script>
        window.SPL_BADGE_API_URL = "{{ route('api.badge-count') }}";
        window.SPL_SW_URL = "{{ asset('sw.js') }}?v={{ file_exists(public_path('sw.js')) ? filemtime(public_path('sw.js')) : time() }}";
    </script>
    <script src="{{ asset('pwa-helper.js') }}?v={{ file_exists(public_path('pwa-helper.js')) ? filemtime(public_path('pwa-helper.js')) : time() }}"></script>
</body>
</html>
