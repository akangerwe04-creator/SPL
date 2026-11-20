@extends('layouts.app')

@section('title', 'Mode Maintenance Server (Admin)')
@section('page_title', 'Kontrol Mode Maintenance Server')

@section('header_badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
        <i class="fa-solid fa-screwdriver-wrench mr-1 text-amber-600"></i> Otoritas Khusus Administrator
    </span>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl">

    <!-- 1. HERO BANNER OTORITAS ADMINISTRATOR -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-white/10">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-400/30 mb-2">
                    <i class="fa-solid fa-lock"></i>
                    <span>Fitur Eksklusif Akun Administrator</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-heading font-black tracking-tight text-white">
                    Pusat Kontrol Pemeliharaan Server
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mt-1.5 leading-relaxed">
                    Fitur ini hanya dapat dikendalikan oleh Administrator (tidak tersedia di akun Owner maupun Agen). 
                    Saat diaktifkan, seluruh pengguna umum, agen, dan owner akan dialihkan ke tampilan pemeliharaan server, 
                    sementara Administrator tetap memiliki hak penuh mengakses aplikasi seutuhnya.
                </p>
            </div>

            <!-- STATUS BADGE BESAR -->
            <div class="shrink-0">
                @if($isActive)
                    <div class="px-5 py-3.5 rounded-2xl bg-amber-500/20 border-2 border-amber-400 text-amber-300 text-center shadow-lg backdrop-blur">
                        <div class="flex items-center justify-center space-x-2 text-xs font-black uppercase tracking-wider mb-0.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                            <span>MAINTENANCE AKTIF</span>
                        </div>
                        <div class="text-[11px] text-amber-200/80 font-medium">Akses publik & owner teralihkan</div>
                    </div>
                @else
                    <div class="px-5 py-3.5 rounded-2xl bg-emerald-500/20 border-2 border-emerald-400 text-emerald-300 text-center shadow-lg backdrop-blur">
                        <div class="flex items-center justify-center space-x-2 text-xs font-black uppercase tracking-wider mb-0.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span>SERVER NORMAL</span>
                        </div>
                        <div class="text-[11px] text-emerald-200/80 font-medium">Semua pengguna dapat mengakses</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. KARTU STATUS & AKSI KONTROL -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- KARTU STATUS KIRI (2 KOLOM) -->
        <div class="md:col-span-2 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl {{ $isActive ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center text-lg font-black">
                        <i class="fa-solid {{ $isActive ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Status Operasional Aplikasi</h3>
                        <p class="text-xs text-slate-500">Kondisi akses server saat ini</p>
                    </div>
                </div>

                <a href="{{ route('admin.maintenance.preview') }}" target="_blank" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>Lihat Halaman Publik</span>
                </a>
            </div>

            @if($isActive)
                <!-- DETAIL MAINTENANCE AKTIF -->
                <div class="bg-amber-50/70 border-2 border-amber-300/80 rounded-2xl p-5 space-y-3">
                    <div class="flex items-center space-x-2 text-amber-900 font-extrabold text-sm">
                        <i class="fa-solid fa-circle-exclamation text-amber-600"></i>
                        <span>Pemberitahuan Sistem Sedang Dialihkan</span>
                    </div>

                    <div class="text-xs text-amber-950 font-medium space-y-1.5 bg-white/70 p-3.5 rounded-xl border border-amber-200">
                        <div><strong>Waktu Diaktifkan:</strong> {{ $info['started_at'] ? date('d F Y, H:i', strtotime($info['started_at'])) . ' WIB' : 'Baru saja' }}</div>
                        <div><strong>Oleh Administrator:</strong> {{ $info['started_by_name'] ?: 'Administrator' }}</div>
                        <div><strong>Teks Pesan Publik:</strong></div>
                        <div class="p-2.5 rounded-lg bg-amber-100/60 font-mono text-[11px] text-amber-900 border border-amber-200/80">
                            "{{ $info['message'] }}"
                        </div>
                    </div>

                    <div class="text-[11px] text-amber-800 leading-relaxed">
                        <i class="fa-solid fa-shield text-amber-600 mr-1"></i>
                        <strong>Catatan:</strong> Sebagai Administrator, Anda tetap dapat menggunakan seluruh fitur aplikasi secara normal. Jika Anda membuka website dalam mode <em>Incognito / Private Window</em> tanpa login admin, Anda akan melihat halaman pemeliharaan.
                    </div>
                </div>

                <!-- FORM HENTIKAN MAINTENANCE -->
                <div class="pt-2">
                    <form action="{{ route('admin.maintenance.stop') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHENTIKAN mode maintenance? Aplikasi akan langsung dapat diakses kembali oleh seluruh pengguna dan publik.')">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-power-off text-base"></i>
                            <span>Hentikan Mode Maintenance Sekarang (Buka Akses Publik)</span>
                        </button>
                    </form>
                </div>
            @else
                <!-- FORM AKTIFKAN MAINTENANCE -->
                <form action="{{ route('admin.maintenance.start') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGAKTIFKAN mode maintenance? Pengguna publik, agen, dan akun owner akan segera dialihkan ke tampilan pemeliharaan.')" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">
                            Pesan Tampilan Pemeliharaan untuk Pengunjung & Pengguna
                        </label>
                        <textarea name="message" rows="3" class="w-full text-xs font-medium bg-slate-50 border border-slate-300 rounded-xl p-3 focus:bg-white focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-slate-800 leading-relaxed">mohon maaf server kami sedang dalam perbaikan, mohon maaf untuk sementara segala bentuk aktivitas tidak dapat dilakukan</textarea>
                        <p class="text-[11px] text-slate-500 mt-1">
                            * Pesan di atas sesuai format standar resmi yang diminta, dapat Anda sesuaikan jika diperlukan.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                        <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                            <i class="fa-solid fa-circle-info text-indigo-600"></i>
                            <span>Apa yang terjadi saat mode maintenance diaktifkan?</span>
                        </div>
                        <ul class="list-disc pl-5 text-[11px] space-y-1 text-slate-600">
                            <li>Pengunjung umum dan publik langsung dialihkan ke halaman pemeliharaan (HTTP 503).</li>
                            <li>Akun Agen Lapangan dan Akun Owner tidak dapat mengakses halaman dan akan dikeluarkan secara otomatis demi keamanan.</li>
                            <li><strong>Hanya Akun Administrator</strong> yang memiliki kunci bypass untuk tetap masuk, bekerja, dan menghentikan pemeliharaan.</li>
                        </ul>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 hover:from-amber-700 hover:to-orange-700 text-white font-extrabold text-sm shadow-lg shadow-amber-600/30 transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>Mulai Mode Maintenance Sekarang</span>
                    </button>
                </form>
            @endif

        </div>

        <!-- KOLOM KANAN: STATISTIK & INFORMASI -->
        <div class="space-y-6">
            
            <!-- RINGKASAN PENGGUNA TERKENA DAMPAK -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
                    Pengguna Terdampak Pengalihan
                </h4>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-crown"></i>
                            </span>
                            <span class="text-xs font-bold text-slate-700">Akun Owner</span>
                        </div>
                        <span class="text-xs font-black text-slate-900">{{ $stats['owner_count'] }} Akun</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-users"></i>
                            </span>
                            <span class="text-xs font-bold text-slate-700">Agen Lapangan</span>
                        </div>
                        <span class="text-xs font-black text-slate-900">{{ $stats['agent_count'] }} Agen</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-globe"></i>
                            </span>
                            <span class="text-xs font-bold text-slate-700">Publik / Pengunjung</span>
                        </div>
                        <span class="text-xs font-black text-emerald-600">Semua Akses</span>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-[11px] leading-relaxed">
                    <i class="fa-solid fa-shield-halved text-indigo-600 mr-1"></i>
                    <strong>Hanya Administrator</strong> yang dikecualikan dari pemblokiran pemeliharaan server ini.
                </div>
            </div>

            <!-- DESAIN TAMPILAN RESMI -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
                    Elemen Desain Halaman Maintenance
                </h4>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Header:</span>
                        <span class="font-extrabold text-slate-900">SUSI PARSEL</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Footer:</span>
                        <span class="font-extrabold text-slate-900">Tim Support IT Maliq Solution</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-slate-500">Kunci Bypass:</span>
                        <span class="font-bold text-indigo-600">Login Administrator</span>
                    </div>
                </div>

                <a href="{{ route('admin.maintenance.preview') }}" target="_blank" class="w-full mt-2 py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold flex items-center justify-center space-x-1.5 transition">
                    <i class="fa-regular fa-eye"></i>
                    <span>Buka Pratinjau Lengkap</span>
                </a>
            </div>

        </div>

    </div>

    <!-- 3. PREVIEW VISUAL LANGSUNG DI DALAM HALAMAN -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Pratinjau Live Tampilan Publik</h3>
                <p class="text-xs text-slate-500">Berikut simulasi tampilan layar yang dilihat oleh pengunjung dan konsumen saat maintenance aktif:</p>
            </div>
            <a href="{{ route('admin.maintenance.preview') }}" target="_blank" class="text-xs font-bold text-pink-600 hover:text-pink-700 flex items-center space-x-1">
                <span>Buka di Tab Baru</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>

        <div class="rounded-2xl border-2 border-slate-800 overflow-hidden shadow-xl bg-slate-950">
            <iframe src="{{ route('admin.maintenance.preview') }}" class="w-full h-[480px] border-0" title="Pratinjau Halaman Maintenance"></iframe>
        </div>
    </div>

</div>
@endsection
