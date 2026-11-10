@php
    $appSetting = \App\Models\CatalogSetting::current();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor') - {{ $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)' }}</title>

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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
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
                        teal: {
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
                        },
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
                        },
                        amber: {
                            500: '#f59e0b',
                            600: '#d97706',
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

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Light Palette Pink Cerah & Ultra Legible */
        :root {
            color-scheme: light;
        }
        body {
            background-color: #fff5f8;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #fbcfe8;
            border-radius: 4px;
        }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#fff5f8] text-slate-800 antialiased min-h-screen flex flex-col md:flex-row">

    <!-- SIDEBAR (DESKTOP) -->
    <aside class="w-full md:w-64 bg-white border-r border-pink-100 flex-shrink-0 flex flex-col justify-between hidden md:flex min-h-screen sticky top-0 shadow-sm z-30">
        <div>
            <!-- BRAND / LOGO -->
            <div class="h-16 px-6 border-b border-pink-100 flex items-center justify-between bg-gradient-to-r from-pink-600 via-rose-600 to-pink-700 text-white shadow-xs">
                <a href="{{ route('owner.dashboard') }}" class="flex items-center space-x-3">
                    @if($appSetting->logo_path)
                        <img src="{{ $appSetting->logo_url }}" alt="{{ $appSetting->app_name }}" class="w-9 h-9 object-contain rounded-lg bg-white/20 p-1 backdrop-blur shadow-xs">
                    @else
                        <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center font-bold text-lg text-white backdrop-blur">
                            <i class="fa-solid fa-gift"></i>
                        </div>
                    @endif
                    <div>
                        <div class="font-heading font-extrabold text-sm tracking-wide leading-tight uppercase">{{ $appSetting->app_name ?: 'SUSI PARSEL' }}</div>
                        <div class="text-[10px] text-pink-100 font-semibold">ERP MINI LEBARAN</div>
                    </div>
                </a>
            </div>

            <!-- MODE SWITCHER BANNER -->
            <div class="p-4 border-b border-pink-100 bg-pink-50/70">
                <div class="text-[11px] font-bold text-pink-900 uppercase tracking-wider mb-2 flex items-center justify-between">
                    <span>Mode Tampilan</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-pink-100 text-pink-900 border border-pink-200">
                        {{ auth()->user()->role === 'owner' ? 'Super Admin' : 'Staff Agen' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-1.5 bg-pink-100/60 p-1 rounded-xl">
                    <a href="{{ route('owner.dashboard') }}" class="px-2 py-1.5 rounded-lg text-xs font-bold text-center transition {{ request()->routeIs('owner.dashboard') ? 'bg-white text-pink-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="fa-solid fa-chart-pie mr-1"></i> Dasbor
                    </a>
                    <a href="{{ route('social.feed') }}" class="px-2 py-1.5 rounded-lg text-xs font-bold text-center transition relative flex items-center justify-center space-x-1 {{ request()->routeIs('social.*') ? 'bg-white text-pink-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="fa-brands fa-instagram mr-1"></i>
                        <span>Feed</span>
                        <span class="feed-badge-indicator w-2 h-2 rounded-full bg-rose-600" style="display: none;"></span>
                    </a>
                </div>
            </div>

            <!-- NAVIGATION LINKS -->
            <nav class="p-4 space-y-1 text-sm font-medium sidebar-scroll overflow-y-auto max-h-[calc(100vh-240px)]">
                @if(auth()->user()->isOwnerOrAdmin())
                    <div class="text-[11px] uppercase tracking-wider font-bold text-slate-400 px-3 pt-2 pb-1">
                        {{ auth()->user()->isAdmin() ? 'Menu Utama (Hak Akses Owner & Admin)' : 'Menu Utama Owner' }}
                    </div>
                    
                    <!-- 1. Dashboard -->
                    <a href="{{ route('owner.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('owner.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-gauge-high w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('owner.dashboard') ? '!text-emerald-600' : '' }}"></i>
                        <span>1. Dashboard</span>
                    </a>

                    <!-- 2. Menu Agen & Laporan Persebaran Paket -->
                    <div class="space-y-0.5">
                        <a href="{{ route('owner.agents.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('owner.agents.index') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-users-gear w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('owner.agents.index') ? '!text-emerald-600' : '' }}"></i>
                                <span>2. Menu Agen</span>
                            </div>
                            @php
                                $agCount = \App\Models\User::where('role', 'agent')->count();
                            @endphp
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $agCount }} Agen</span>
                        </a>
                        <a href="{{ route('owner.agents.report') }}" class="flex items-center justify-between pl-8 pr-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('owner.agents.report*') ? 'bg-emerald-50 text-emerald-800 font-bold border-l-2 border-emerald-500' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">
                            <span class="flex items-center">
                                <i class="fa-solid fa-chart-pie mr-1.5 text-[10px] text-emerald-600"></i> Laporan Agen & Paket
                            </span>
                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">Rekap</span>
                        </a>
                    </div>

                    <!-- 3. Data Barang -->
                    <a href="{{ route('items.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('items.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-tags w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('items.*') ? '!text-emerald-600' : '' }}"></i>
                        <span>3. Data Barang</span>
                    </a>

                    <!-- 4. Data Kemasan -->
                    <a href="{{ route('owner.packagings.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('owner.packagings.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-box-archive w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('owner.packagings.*') ? '!text-emerald-600' : '' }}"></i>
                            <span>4. Data Kemasan</span>
                        </div>
                        @php
                            $kmsCount = \App\Models\Packaging::count();
                        @endphp
                        @if($kmsCount > 0)
                            <span class="bg-pink-100 text-pink-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $kmsCount }}</span>
                        @endif
                    </a>

                    <!-- 5. Menu Paket -->
                    <a href="{{ route('packages.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('packages.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-box-open w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('packages.*') ? '!text-emerald-600' : '' }}"></i>
                        <span>5. Menu Paket</span>
                    </a>

                    <!-- 6. Rekonsiliasi Agen -->
                    <a href="{{ route('owner.deposits.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('owner.deposits.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-money-bill-transfer w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('owner.deposits.*') ? '!text-emerald-600' : '' }}"></i>
                            <span>6. Rekonsiliasi Agen</span>
                        </div>
                        @php
                            $subCount = \App\Models\AgentDeposit::where('status', 'submitted')->count();
                        @endphp
                        @if($subCount > 0)
                            <span class="bg-amber-500 text-white text-[11px] font-bold px-1.5 py-0.5 rounded-full animate-pulse">{{ $subCount }}</span>
                        @endif
                    </a>

                    <!-- 7. Belanja Barang -->
                    <a href="{{ route('inventory.planner') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('inventory.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-cart-flatbed w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('inventory.*') ? '!text-emerald-600' : '' }}"></i>
                        <span>7. Belanja Barang</span>
                    </a>

                    <!-- 8. Tutup Buku (Hak Akses Owner & Admin) -->
                    <a href="{{ route('owner.book-closing.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('owner.book-closing.*') ? 'bg-rose-50 text-rose-700 font-bold border-l-4 border-rose-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-lock w-5 text-center mr-2.5 {{ $appSetting->is_book_closed ? 'text-rose-600 animate-pulse' : 'text-slate-400' }} {{ request()->routeIs('owner.book-closing.*') ? '!text-rose-600' : '' }}"></i>
                            <span>8. Tutup Buku</span>
                        </div>
                        @if($appSetting->is_book_closed)
                            <span class="bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full animate-pulse">Tutup</span>
                        @endif
                    </a>

                    <!-- 9. Pengaturan Aplikasi -->
                    <a href="{{ route('owner.settings.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('owner.settings.*') || request()->routeIs('owner.reset.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-sliders w-5 text-center mr-2.5 text-slate-400 {{ request()->routeIs('owner.settings.*') || request()->routeIs('owner.reset.*') ? '!text-emerald-600' : '' }}"></i>
                        <span>9. Pengaturan Aplikasi</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                        <div class="text-[11px] uppercase tracking-wider font-bold text-indigo-500 px-3 pt-3 pb-1">Hak Akses Khusus Admin</div>
                        <!-- 10. Manajemen Akun (Khusus Admin Tersembunyi) -->
                        <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700 font-bold border-l-4 border-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <div class="flex items-center">
                                <i class="fa-solid fa-user-shield w-5 text-center mr-2.5 text-indigo-600"></i>
                                <span>10. Manajemen Akun</span>
                            </div>
                            <span class="bg-indigo-100 text-indigo-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">Admin</span>
                        </a>
                    @endif
                @else
                    <!-- MENU AGEN LAPANGAN -->
                    <div class="text-[11px] uppercase tracking-wider font-bold text-slate-400 px-3 pt-2 pb-1">Menu Agen</div>

                    <a href="{{ route('social.feed') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('social.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center">
                            <i class="fa-brands fa-instagram w-5 text-center mr-2.5 text-pink-600"></i>
                            <span>Feed Komunitas</span>
                        </div>
                        <span class="feed-badge-indicator px-1.5 py-0.2 rounded-full text-[9px] font-black bg-rose-600 text-white shadow-xs" style="display: none;"></span>
                    </a>

                    <a href="{{ route('customers.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('customers.*') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-users w-5 text-center mr-2.5 text-slate-400"></i>
                        <span>Konsumen Binaan</span>
                    </a>

                    <a href="{{ route('agent.cart') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('agent.cart') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-basket-shopping w-5 text-center mr-2.5 text-amber-500"></i>
                            <span>Keranjang Setoran</span>
                        </div>
                        @php
                            $myCart = \App\Models\AgentDeposit::where('agent_id', auth()->id())->where('status', 'pending')->first();
                        @endphp
                        @if($myCart && $myCart->total_amount > 0)
                            <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">Rp {{ number_format($myCart->total_amount, 0, ',', '.') }}</span>
                        @endif
                    </a>

                    <a href="{{ route('agent.deposits.history') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('agent.deposits.history') ? 'bg-emerald-50 text-emerald-700 font-semibold border-l-4 border-emerald-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center mr-2.5 text-slate-400"></i>
                        <span>Riwayat Setoran</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- FOOTER USER PROFILE -->
        <div class="p-4 border-t border-slate-200 bg-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center border border-emerald-200">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-500 capitalize">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="text-slate-400 hover:text-red-600 p-2 rounded-lg hover:bg-slate-100 transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MOBILE TOP BAR -->
    <header class="md:hidden bg-white/95 backdrop-blur border-b border-pink-200 sticky top-0 z-40 px-4 py-3 flex items-center justify-between shadow-xs">
        <div class="flex items-center space-x-2.5">
            @if($appSetting->logo_path)
                <img src="{{ $appSetting->logo_url }}" alt="{{ $appSetting->app_name }}" class="w-8 h-8 object-contain rounded-lg bg-pink-50 p-1 border border-pink-200 shadow-xs">
            @else
                <div class="w-8 h-8 rounded-lg bg-pink-600 text-white flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-gift"></i>
                </div>
            @endif
            <div>
                <div class="font-heading font-extrabold text-sm text-pink-900 leading-tight uppercase">{{ $appSetting->app_name ?: 'SUSI PARSEL' }}</div>
                <div class="text-[10px] text-pink-700 font-semibold">ERP LEBARAN 2026</div>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if(auth()->user()->isOwnerOrAdmin())
                <a href="{{ request()->routeIs('social.*') ? route('owner.dashboard') : route('social.feed') }}" class="px-2.5 py-1 text-xs font-bold rounded-full bg-pink-100 text-pink-800 border border-pink-200 shadow-xs relative flex items-center">
                    <i class="fa-solid {{ request()->routeIs('social.*') ? 'fa-chart-pie' : 'fa-camera' }} mr-1 text-pink-600"></i>
                    {{ request()->routeIs('social.*') ? 'Dasbor' : 'Feed' }}
                    <span class="feed-badge-indicator ml-1 w-2 h-2 rounded-full bg-rose-600" style="display: none;"></span>
                </a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-red-600 p-1.5 transition">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 min-w-0 flex flex-col pb-20 md:pb-8">
        <!-- TOP NAVBAR (DESKTOP) -->
        <header class="hidden md:flex h-16 bg-white/90 backdrop-blur border-b border-pink-100 px-8 items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                @if($appSetting->logo_path)
                    <img src="{{ $appSetting->logo_url }}" alt="{{ $appSetting->app_name }}" class="w-8 h-8 object-contain rounded-lg shadow-2xs border border-pink-200 bg-white p-0.5 shrink-0">
                @endif
                <h1 class="text-lg font-heading font-extrabold text-slate-900">@yield('page_title', $appSetting->app_name ?: 'SPL (Susi Parsel Lebaran)')</h1>
                @yield('header_badge')
            </div>
            <div class="flex items-center space-x-4">
                @if(auth()->user()->isOwnerOrAdmin())
                    <a href="{{ route('social.feed') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-pink-600 to-rose-600 text-white hover:from-pink-700 hover:to-rose-700 transition shadow-xs relative">
                        <i class="fa-brands fa-instagram mr-1.5 text-sm"></i> Mode Komunitas / Feed
                        <span class="feed-badge-indicator ml-1.5 px-1.5 py-0.2 rounded-full text-[10px] font-black bg-white text-rose-600 shadow-xs" style="display: none;"></span>
                    </a>
                @endif
                <div class="text-right">
                    <div class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-pink-800 font-semibold">{{ date('d M Y') }} • Musim 1447 H</div>
                </div>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        <div class="px-4 md:px-8 pt-4">
            @if($appSetting->is_book_closed && auth()->user()->isOwnerOrAdmin() && !request()->routeIs('owner.book-closing.*'))
                <div class="mb-4 bg-gradient-to-r from-rose-900 to-pink-900 border-l-4 border-rose-500 p-3.5 sm:p-4 rounded-r-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-white shadow-sm">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0 text-amber-300">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <div>
                            <div class="text-[11px] font-black uppercase tracking-wider text-rose-200">PERIODE TUTUP BUKU AKTIF</div>
                            <div class="text-xs text-pink-50 font-medium">Musim parsel telah berakhir. Keputusan pemenuhan barang dan uang tunai sedang diproses.</div>
                        </div>
                    </div>
                    <a href="{{ route('owner.book-closing.index') }}" class="px-3.5 py-1.5 rounded-lg bg-white text-rose-800 hover:bg-rose-50 font-extrabold text-xs shrink-0 shadow-xs flex items-center space-x-1.5">
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        <span>Buka Menu Tutup Buku</span>
                    </a>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg flex items-start justify-between shadow-xs">
                    <div class="flex items-start">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 mr-3 text-base"></i>
                        <div class="text-sm text-emerald-900 font-medium">{{ session('success') }}</div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-sm ml-4">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg flex items-start justify-between shadow-xs">
                    <div class="flex items-start">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5 mr-3 text-base"></i>
                        <div class="text-sm text-amber-900 font-medium">{{ session('warning') }}</div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700 text-sm ml-4">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg flex items-start justify-between shadow-xs">
                    <div class="flex items-start">
                        <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5 mr-3 text-base"></i>
                        <div class="text-sm text-red-900 font-medium">{{ session('error') }}</div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm ml-4">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-xs">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-1 flex items-center">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i> Mohon periksa kembali isian formulir:
                    </div>
                    <ul class="list-disc list-inside text-xs text-amber-900 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- PAGE CONTENT INJECTION -->
        <div class="px-4 md:px-8 py-2 flex-1">
            @yield('content')
        </div>
    </main>

    <!-- MOBILE BOTTOM NAVIGATION (AGEN & OWNER MOBILE) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-pink-200 z-50 px-2 py-1.5 flex items-center justify-around shadow-lg">
        <a href="{{ route('social.feed') }}" class="flex flex-col items-center py-1 px-3 text-xs relative {{ request()->routeIs('social.*') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
            <i class="fa-brands fa-instagram text-lg mb-0.5"></i>
            <span class="text-[10px]">Feed</span>
            <span class="feed-badge-indicator absolute top-0.5 right-2 px-1 py-0.2 rounded-full text-[9px] font-black bg-rose-600 text-white leading-none shadow-xs" style="display: none;"></span>
        </a>
        <a href="{{ route('customers.index') }}" class="flex flex-col items-center py-1 px-3 text-xs {{ request()->routeIs('customers.*') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
            <i class="fa-solid fa-users text-lg mb-0.5"></i>
            <span class="text-[10px]">Konsumen</span>
        </a>
        @if(auth()->user()->isOwnerOrAdmin())
            <a href="{{ route('owner.dashboard') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('owner.dashboard') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-gauge-high text-lg mb-0.5"></i>
                <span class="text-[10px]">Dasbor</span>
            </a>
            <a href="{{ route('owner.agents.index') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('owner.agents.*') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-users-gear text-lg mb-0.5"></i>
                <span class="text-[10px]">Agen</span>
            </a>
            <a href="{{ route('owner.deposits.index') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('owner.deposits.*') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-money-bill-transfer text-lg mb-0.5"></i>
                <span class="text-[10px]">Setoran</span>
            </a>
            <a href="{{ route('inventory.planner') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('inventory.*') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-cart-flatbed text-lg mb-0.5"></i>
                <span class="text-[10px]">Belanja</span>
            </a>
            <a href="{{ route('owner.book-closing.index') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('owner.book-closing.*') ? 'text-rose-600 font-extrabold' : 'text-slate-600 hover:text-rose-600' }}">
                <i class="fa-solid fa-lock text-lg mb-0.5 {{ $appSetting->is_book_closed ? 'text-rose-600' : '' }}"></i>
                <span class="text-[10px]">Tutup Buku</span>
            </a>
            <a href="{{ route('owner.settings.index') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('owner.settings.*') || request()->routeIs('owner.reset.*') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-sliders text-lg mb-0.5"></i>
                <span class="text-[10px]">Pengaturan</span>
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center py-1 px-2 text-xs {{ request()->routeIs('admin.users.*') ? 'text-indigo-600 font-extrabold' : 'text-slate-600 hover:text-indigo-600' }}">
                    <i class="fa-solid fa-user-shield text-lg mb-0.5"></i>
                    <span class="text-[10px]">Akun</span>
                </a>
            @endif
        @else
            <a href="{{ route('agent.cart') }}" class="flex flex-col items-center py-1 px-3 text-xs relative {{ request()->routeIs('agent.cart') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-basket-shopping text-lg mb-0.5"></i>
                <span class="text-[10px]">Keranjang</span>
                @php
                    $mobCart = \App\Models\AgentDeposit::where('agent_id', auth()->id())->where('status', 'pending')->first();
                @endphp
                @if($mobCart && $mobCart->total_amount > 0)
                    <span class="absolute top-0 right-2 w-2.5 h-2.5 bg-amber-500 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('agent.deposits.history') }}" class="flex flex-col items-center py-1 px-3 text-xs {{ request()->routeIs('agent.deposits.history') ? 'text-pink-600 font-extrabold' : 'text-slate-600 hover:text-pink-600' }}">
                <i class="fa-solid fa-clock-rotate-left text-lg mb-0.5"></i>
                <span class="text-[10px]">Riwayat</span>
            </a>
        @endif
    </nav>

    <!-- PWA & Companion Script (Badging, In-App WhatsApp Detect, Offline SW) -->
    <script>
        window.SPL_BADGE_API_URL = "{{ route('api.badge-count') }}";
        window.SPL_SW_URL = "{{ asset('sw.js') }}?v={{ file_exists(public_path('sw.js')) ? filemtime(public_path('sw.js')) : time() }}";
    </script>
    <script src="{{ asset('pwa-helper.js') }}?v={{ file_exists(public_path('pwa-helper.js')) ? filemtime(public_path('pwa-helper.js')) : time() }}"></script>

    @stack('scripts')
</body>
</html>

