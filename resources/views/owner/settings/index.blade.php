@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi & Reset Data')
@section('page_title', 'Pengaturan Aplikasi & Pemeliharaan')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        Menu ke-7: Pengaturan
    </span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="settingsPageHandler('{{ request('tab', 'profile') }}')">

    <!-- TAB SELECTOR -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-2 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
        <button type="button" @click="activeTab = 'profile'"
            class="py-2.5 px-3 rounded-xl font-bold transition flex items-center justify-center space-x-2"
            :class="activeTab === 'profile' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-sliders"></i>
            <span>1. Profil Aplikasi & Logo</span>
        </button>

        <button type="button" @click="activeTab = 'backup'"
            class="py-2.5 px-3 rounded-xl font-bold transition flex items-center justify-center space-x-2"
            :class="activeTab === 'backup' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-indigo-700 hover:bg-indigo-50/50'">
            <i class="fa-solid fa-database"></i>
            <span>2. Cadangan Database (Backup)</span>
        </button>

        <button type="button" @click="activeTab = 'reset'"
            class="py-2.5 px-3 rounded-xl font-bold transition flex items-center justify-center space-x-2"
            :class="activeTab === 'reset' ? 'bg-red-600 text-white shadow-xs' : 'text-slate-600 hover:text-red-700 hover:bg-red-50/50'">
            <i class="fa-solid fa-trash-can-arrow-up"></i>
            <span>3. Reset Data Sistem</span>
        </button>
    </div>

    <!-- ==================== TAB 1: PROFIL APLIKASI, LOGO & OWNER ==================== -->
    <div x-show="activeTab === 'profile'" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8 space-y-6 text-xs">
        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base">Identitas Usaha & Tampilan Brosur</h3>
                <p class="text-xs text-slate-400">Pengaturan nama aplikasi, logo, alamat owner, dan kop cetak katalog parsel.</p>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Tersimpan Aktif</span>
        </div>

        <form action="{{ route('owner.settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- APP NAME & OWNER NAME -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Aplikasi / Usaha *</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $setting->app_name) }}" required placeholder="Contoh: SPL (Susi Parsel Lebaran)" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-900">
                    <span class="text-[10px] text-slate-400">Muncul pada judul navbar sidebar & kop dokumen</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Pemilik (Owner) *</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $setting->owner_name) }}" required placeholder="Contoh: Susi" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold">
                </div>
            </div>

            <!-- LOGO UPLOAD SECTION -->
            <div class="p-4 rounded-xl bg-pink-50/40 border border-pink-200 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block font-bold text-slate-800">Logo Usaha / Parsel</label>
                        <p class="text-[10px] text-slate-600 font-medium">Logo disimpan di <strong>Storage Permanen</strong> agar aman dan tidak pernah hilang saat reset data atau pembaruan sistem.</p>
                    </div>
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center">
                        <i class="fa-solid fa-shield-halved mr-1 text-emerald-600"></i> Storage Permanen Aktif
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                    <div class="sm:col-span-2 space-y-2">
                        <label for="appLogoInput" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-pink-300 hover:border-pink-600 rounded-xl cursor-pointer bg-white transition text-center group">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-pink-400 group-hover:text-pink-600 transition mb-1"></i>
                            <span class="text-xs font-bold text-slate-700 group-hover:text-pink-700">Pilih File Logo Baru</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">PNG transparan / JPG / WebP (Maks. 8 MB)</span>
                            <input id="appLogoInput" type="file" name="logo" accept="image/*" @change="previewLogo($event)" class="hidden">
                        </label>

                        @if($setting->logo_path)
                            <div class="pt-1">
                                <label class="inline-flex items-center space-x-2 cursor-pointer text-red-600 hover:text-red-700 font-semibold text-xs">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600">
                                    <span>Hapus logo saat ini (gunakan icon default)</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <!-- Live Preview Box -->
                    <div class="flex flex-col items-center justify-center p-3 bg-white border border-pink-200 rounded-xl min-h-[100px] shadow-xs">
                        <template x-if="logoPreview">
                            <div class="text-center relative">
                                <img :src="logoPreview" alt="Preview Logo" class="h-16 max-w-full object-contain mx-auto rounded shadow-xs">
                                <button type="button" @click="clearLogoPreview()" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px]" title="Batal ganti">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <span class="block text-[9px] font-bold text-pink-700 mt-1">Logo baru dipilih</span>
                            </div>
                        </template>
                        <template x-if="!logoPreview">
                            <div>
                                @if($setting->logo_path)
                                    <div class="text-center">
                                        <img src="{{ asset($setting->logo_path) }}?v={{ file_exists(public_path($setting->logo_path)) ? filemtime(public_path($setting->logo_path)) : time() }}" alt="Logo Saat Ini" class="h-16 max-w-full object-contain mx-auto rounded">
                                        <span class="inline-flex items-center text-[9px] font-extrabold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 mt-1">
                                            <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Logo Tersimpan Permanen
                                        </span>
                                    </div>
                                @else
                                    <div class="text-center text-slate-400 py-1">
                                        <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-700 mx-auto flex items-center justify-center text-xl mb-1">
                                            <i class="fa-solid fa-gift"></i>
                                        </div>
                                        <div class="text-[10px]">Icon Gift Default</div>
                                    </div>
                                @endif
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ALAMAT & KONTAK -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Owner / Kantor Operasional</label>
                    <textarea name="owner_address" rows="3" placeholder="Alamat lengkap usaha atau tempat pengambilan parsel..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg">{{ old('owner_address', $setting->owner_address) }}</textarea>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Kontak / WhatsApp Resmi *</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $setting->contact_person) }}" required placeholder="0812-3456-7890 (Ibu Susi)" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Periode Musim Program *</label>
                        <input type="text" name="program_period" value="{{ old('program_period', $setting->program_period) }}" required placeholder="Periode Musim Lebaran 1447 H / 2026 (Tagihan 30 Minggu)" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <label class="block font-semibold text-slate-700 text-[11px] mb-1">Tanggal Mulai Musim</label>
                            <input type="date" name="program_start_date" value="{{ old('program_start_date', $setting->program_start_date ? $setting->program_start_date->format('Y-m-d') : '') }}" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 text-[11px] mb-1">Tanggal Berakhir (Target Lebaran)</label>
                            <input type="date" name="program_end_date" value="{{ old('program_end_date', $setting->program_end_date ? $setting->program_end_date->format('Y-m-d') : '') }}" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-pink-500">
                        </div>
                    </div>
                    @if($setting->program_start_date && $setting->program_end_date)
                        <div class="p-2.5 rounded-lg bg-pink-50 border border-pink-200 text-[11px] text-pink-900 font-medium">
                            <i class="fa-solid fa-calculator text-pink-600 mr-1"></i> Referensi Musim: <strong>{{ $setting->calculated_days }} Hari</strong> atau <strong>{{ $setting->calculated_weeks }} Minggu</strong> (sebagai acuan paket).
                        </div>
                    @endif
                </div>
            </div>

            <!-- BROSUR KOP & BANK -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                <h4 class="font-bold text-slate-900 text-xs">Informasi Tambahan Brosur & Pembayaran</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Kop Katalog Brosur *</label>
                        <input type="text" name="title" value="{{ old('title', $setting->title) }}" required class="w-full p-2.5 bg-white border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Info Rekening Bank Setoran</label>
                        <input type="text" name="bank_account_info" value="{{ old('bank_account_info', $setting->bank_account_info) }}" placeholder="Bank BCA: 123-456-789 a.n. Susi Susilawati" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Syarat & Ketentuan (T&C) di Brosur Cetak</label>
                    <textarea name="terms_and_conditions" rows="3" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg">{{ old('terms_and_conditions', $setting->terms_and_conditions) }}</textarea>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Pengaturan Profil & Logo
                </button>
            </div>
        </form>
    </div>

    <!-- ==================== TAB 2: CADANGAN DATABASE (BACKUP & RESTORE) ==================== -->
    <div x-show="activeTab === 'backup'" class="space-y-6 text-xs" style="display: none;">
        <!-- INFORMATIONAL BANNER -->
        <div class="p-5 rounded-2xl bg-indigo-50/80 border border-indigo-200 text-indigo-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 text-lg shadow-sm">
                    <i class="fa-solid fa-shield-cat"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-sm text-indigo-900">Perlindungan Data Aman & Mudah Dipulihkan</h3>
                    <p class="text-xs text-indigo-800/90 mt-0.5 leading-relaxed">
                        Data Anda saat ini aman dan tidak akan terhapus ataupun berubah kecuali Anda menjalankan reset data atau memulihkan file cadangan. Unduh cadangan database secara berkala untuk disimpan di komputer atau Google Drive Anda.
                    </p>
                </div>
            </div>
            <a href="{{ route('owner.backup.download-current') }}" class="shrink-0 inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-xs transition">
                <i class="fa-solid fa-download"></i>
                <span>Unduh Database Saat Ini</span>
            </a>
        </div>

        <!-- TWO PANELS: 1. BUAT SNAPSHOT SERVER, 2. UNGGAH & PULIHKAN -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- PANEL A: SNAPSHOT SERVER -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-2 text-slate-800 font-bold mb-1">
                        <i class="fa-solid fa-server text-indigo-600 text-sm"></i>
                        <h4 class="text-sm">Simpan Titik Cadangan (Snapshot di Server)</h4>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">
                        Buat salinan instan database saat ini dan simpan di folder server aman. Anda dapat mengunduh atau mengembalikannya kapan saja dengan sekali klik.
                    </p>
                </div>
                <form action="{{ route('owner.backup.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-camera-retro"></i>
                        <span>Buat Snapshot Cadangan Baru</span>
                    </button>
                </form>
            </div>

            <!-- PANEL B: UNGGAH DARI KOMPUTER/HP -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div>
                    <div class="flex items-center space-x-2 text-slate-800 font-bold mb-1">
                        <i class="fa-solid fa-cloud-arrow-up text-emerald-600 text-sm"></i>
                        <h4 class="text-sm">Pulihkan dari File Cadangan (.sqlite)</h4>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">
                        Unggah file cadangan yang pernah diunduh sebelumnya. Sistem akan otomatis membackup database aktif terlebih dahulu sebelum proses pemulihan (safety backup).
                    </p>
                </div>
                <form action="{{ route('owner.backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('APAKAH ANDA YAKIN?\n\nDatabase saat ini akan ditimpa dengan file cadangan yang Anda unggah. Sistem akan membuat safety-backup otomatis sebelum menimpa.')" class="space-y-3">
                    @csrf
                    <input type="file" name="backup_file" accept=".sqlite,.db,.sqlite3" required class="w-full text-[11px] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-lg p-1.5 bg-slate-50">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center justify-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <span>Pulihkan Database Sekarang</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- DAFTAR FILE CADANGAN TERSIMPAN -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h4 class="font-heading font-extrabold text-slate-900 text-sm">Daftar Cadangan Tersimpan di Server</h4>
                    <p class="text-slate-400 text-[11px]">Riwayat snapshot cadangan database yang tersimpan di sistem.</p>
                </div>
                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                    {{ count($backups) }} File
                </span>
            </div>

            @if(count($backups) > 0)
                <div class="divide-y divide-slate-100 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                <th class="pb-2">Nama Berkas</th>
                                <th class="pb-2">Ukuran</th>
                                <th class="pb-2">Waktu Pembuatan</th>
                                <th class="pb-2 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($backups as $b)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 pr-2 font-mono font-bold text-[11px] text-slate-800 flex items-center space-x-2">
                                        <i class="fa-solid fa-file-shield {{ str_contains($b['filename'], 'Auto_Pre_Restore') ? 'text-amber-500' : 'text-indigo-500' }}"></i>
                                        <span>{{ $b['filename'] }}</span>
                                        @if(str_contains($b['filename'], 'Auto_Pre_Restore'))
                                            <span class="text-[9px] font-sans font-extrabold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">Auto Safety</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 pr-2 text-slate-500 font-mono text-[11px]">{{ $b['size'] }}</td>
                                    <td class="py-2.5 pr-2 text-slate-500 text-[11px]">{{ $b['created_at'] }}</td>
                                    <td class="py-2.5 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('owner.backup.download', $b['filename']) }}" class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[10px] transition" title="Unduh File">
                                            <i class="fa-solid fa-download mr-1"></i> Unduh
                                        </a>

                                        <form action="{{ route('owner.backup.restore') }}" method="POST" class="inline" onsubmit="return confirm('Pulihkan database menggunakan file {{ $b['filename'] }}? Database aktif akan ditimpa dengan cadangan ini.')">
                                            @csrf
                                            <input type="hidden" name="saved_filename" value="{{ $b['filename'] }}">
                                            <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[10px] transition" title="Pulihkan File Ini">
                                                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Pulihkan
                                            </button>
                                        </form>

                                        <form action="{{ route('owner.backup.destroy', $b['filename']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus file cadangan {{ $b['filename'] }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 font-bold text-[10px] transition" title="Hapus Cadangan">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-slate-400 space-y-2">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-xl">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <p class="font-medium text-xs text-slate-500">Belum ada file cadangan tersimpan di server.</p>
                    <p class="text-[11px] text-slate-400">Klik tombol "Buat Snapshot Cadangan Baru" di atas untuk membuat cadangan pertama Anda.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- ==================== TAB 3: FITUR RESET DATA SISTEM ==================== -->
    <div x-show="activeTab === 'reset'" class="space-y-6">

        <!-- WARNING BANNER -->
        <div class="p-5 rounded-2xl bg-red-50 border-2 border-red-200 text-red-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0 text-lg shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-sm text-red-900">Perhatian: Tindakan Ini Bersifat Permanen</h3>
                    <p class="text-xs text-red-800/90 mt-0.5 leading-relaxed">
                        Fitur reset digunakan saat pergantian musim Lebaran baru atau menghapus data simulasi uji coba. Akun Super Admin Anda (<strong>{{ auth()->user()->name }}</strong>) diproteksi dan tidak akan terhapus.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8">
            <form action="{{ route('owner.reset.execute') }}" method="POST" @submit.prevent="confirmSubmit($event)" class="space-y-6 text-xs">
                @csrf

                <!-- 1. MASTER TOGGLE: RESET ALL -->
                <div class="p-4 rounded-xl border-2 transition cursor-pointer"
                    :class="resetAll ? 'bg-red-50 border-red-500 shadow-xs' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                    @click="toggleResetAll()">
                    <div class="flex items-start space-x-3">
                        <input type="checkbox" name="reset_all" value="1" x-model="resetAll" @click.stop="toggleResetAll()" class="mt-0.5 w-4 h-4 rounded border-red-300 text-red-600 focus:ring-red-500">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-heading font-extrabold text-slate-900 text-sm">PILIH SEMUA: RESET SELURUH DATA SISTEM (FACTORY RESET)</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-600 text-white uppercase tracking-wider">Reset Total</span>
                            </div>
                            <p class="text-slate-500 text-[11px] mt-1 leading-relaxed">
                                Mencentang opsi ini akan memilih dan menghapus <strong>seluruh transaksi, setoran, konsumen, feed sosial, master paket, master barang, dan akun agen</strong> sekaligus. Aplikasi kembali bersih seperti baru diinstal.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. INDIVIDUAL CHECKBOX CARDS -->
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
                        <label class="font-bold text-slate-800 text-xs uppercase tracking-wider">Atau Pilih Kategori Data Spesifik:</label>
                        <button type="button" @click="toggleSelectAllItems()" class="text-[11px] font-bold text-emerald-700 hover:underline">
                            <span x-text="allTargetsChecked() ? 'Batal Pilih Semua' : 'Centang Semua Pilihan'"></span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <!-- Transaksi & Setoran -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                            :class="isTargetChecked('transactions') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="transactions" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-solid fa-money-bill-transfer text-amber-600 mr-1.5 w-4 text-center"></i>
                                        Transaksi & Setoran Agen
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">
                                        {{ $counts['transactions']['payments'] }} Tagihan
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Mereset cicilan 30 minggu konsumen kembali ke belum bayar & menghapus berkas setoran kas agen ({{ $counts['transactions']['deposits'] }} setoran).
                                </p>
                            </div>
                        </label>

                        <!-- Mini-POS Penyerahan -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                            :class="isTargetChecked('fulfillments') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="fulfillments" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-solid fa-cash-register text-emerald-600 mr-1.5 w-4 text-center"></i>
                                        Mini-POS & Penyerahan Parsel
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700">
                                        {{ $counts['fulfillments']['total'] }} Struk
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Menghapus struk penyesuaian belanja parsel kasir mini & mereset status pemenuhan ke 'pending'.
                                </p>
                            </div>
                        </label>

                        <!-- Konsumen & Paket -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                            :class="isTargetChecked('customers') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="customers" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-solid fa-users text-blue-600 mr-1.5 w-4 text-center"></i>
                                        Data Konsumen & Paket Binaan
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-blue-100 text-blue-800">
                                        {{ $counts['customers']['customers'] }} Konsumen
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Menghapus seluruh profil konsumen, paket terdaftar ({{ $counts['customers']['packages'] }} paket), dan kartu tagihannya.
                                </p>
                            </div>
                        </label>

                        <!-- Feed Sosial -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                            :class="isTargetChecked('social') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="social" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-brands fa-instagram text-pink-600 mr-1.5 w-4 text-center"></i>
                                        Feed Sosial & Komunitas
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-pink-100 text-pink-800">
                                        {{ $counts['social']['posts'] }} Postingan
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Menghapus semua postingan foto lapangan, likes, dan {{ $counts['social']['comments'] }} komentar.
                                </p>
                            </div>
                        </label>

                        <!-- Master Paket -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                            :class="isTargetChecked('packages') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="packages" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-solid fa-box-open text-emerald-600 mr-1.5 w-4 text-center"></i>
                                        Master Paket Parsel & Tabungan
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">
                                        {{ $counts['packages']['total'] }} Paket
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Menghapus master paket parsel barang, tabungan uang, dan komposisi barangnya.
                                </p>
                            </div>
                        </label>

                        <!-- Master Barang -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3"
                            :class="isTargetChecked('items') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="items" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-solid fa-tags text-indigo-600 mr-1.5 w-4 text-center"></i>
                                        Daftar Master Barang (50 Item)
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800">
                                        {{ $counts['items']['total'] }} Barang
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Mengosongkan seluruh master barang biskuit, sirup, sembako, dan harga grosir belanja.
                                </p>
                            </div>
                        </label>

                        <!-- Akun Agen -->
                        <label class="p-3.5 rounded-xl border transition cursor-pointer flex items-start space-x-3 md:col-span-2"
                            :class="isTargetChecked('agents') ? 'bg-amber-50/70 border-amber-500 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300'">
                            <input type="checkbox" name="targets[]" value="agents" x-model="targets" @change="onTargetChange()" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-xs flex items-center">
                                        <i class="fa-solid fa-user-group text-slate-700 mr-1.5 w-4 text-center"></i>
                                        Akun Agen Lapangan
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-800">
                                        {{ $counts['agents']['total'] }} Akun Agen
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Menghapus akun agen lapangan. <strong>Akun Owner tetap tersimpan aman.</strong>
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 3. RESEED DEMO SAMPLES -->
                <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="reseed_demo" value="1" x-model="reseedDemo" class="mt-0.5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-emerald-950 text-xs">Muat Ulang Data Sampel Standar (Demo Seeder) Setelah Reset</span>
                            <p class="text-emerald-800/80 text-[11px] mt-0.5 leading-relaxed">
                                Setelah pembersihan, sistem akan otomatis mengisi kembali 50 Master Barang, 7 Paket Lebaran resmi dengan foto, data sampel konsumen, dan setoran awal.
                            </p>
                        </div>
                    </label>
                </div>

                <!-- 4. SECURITY CONFIRMATION -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div>
                        <label class="block font-bold text-slate-800 text-xs mb-1">
                            Konfirmasi Keamanan Owner *
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">
                            Ketik kata <span class="font-mono font-bold text-red-600 bg-red-50 px-1 py-0.5 rounded border border-red-200">RESET</span> atau masukkan <strong>kata sandi akun login Anda</strong>:
                        </p>
                        <input type="text" name="confirm_text" x-model="confirmText" required placeholder="Ketik kata RESET atau password akun Owner" class="w-full sm:w-80 p-2.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold">
                    </div>
                </div>

                <!-- SUBMIT ACTION -->
                <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="submit"
                        :disabled="!hasValidSelection() || !confirmText"
                        :class="(!hasValidSelection() || !confirmText) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-red-600 hover:bg-red-700 shadow-md'"
                        class="px-6 py-2.5 text-white font-bold rounded-xl transition flex items-center justify-center">
                        <i class="fa-solid fa-trash-can-arrow-up mr-2"></i>
                        <span>Eksekusi Reset Data</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function settingsPageHandler(initialTab) {
    return {
        activeTab: initialTab || 'profile',
        logoPreview: null,

        // Reset Data state
        resetAll: false,
        targets: [],
        reseedDemo: false,
        confirmText: '',
        allTargetKeys: ['transactions', 'fulfillments', 'customers', 'social', 'packages', 'items', 'agents'],

        previewLogo(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { this.logoPreview = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.logoPreview = null;
            }
        },

        clearLogoPreview() {
            this.logoPreview = null;
            const input = document.getElementById('appLogoInput');
            if (input) input.value = '';
        },

        toggleResetAll() {
            if (this.resetAll) {
                this.targets = [...this.allTargetKeys];
            } else {
                this.targets = [];
            }
        },

        isTargetChecked(key) {
            return this.resetAll || this.targets.includes(key);
        },

        onTargetChange() {
            if (this.targets.length === this.allTargetKeys.length) {
                this.resetAll = true;
            } else {
                this.resetAll = false;
            }
        },

        allTargetsChecked() {
            return this.resetAll || (this.targets.length === this.allTargetKeys.length);
        },

        toggleSelectAllItems() {
            if (this.allTargetsChecked()) {
                this.resetAll = false;
                this.targets = [];
            } else {
                this.resetAll = true;
                this.targets = [...this.allTargetKeys];
            }
        },

        hasValidSelection() {
            return this.resetAll || this.targets.length > 0;
        },

        confirmSubmit(event) {
            const count = this.resetAll ? 'SEMUA DATA SISTEM' : (this.targets.length + ' kategori data');
            const noteReseed = this.reseedDemo ? '\n(Data sampel demo awal akan dimuat kembali setelah reset)' : '';
            
            const message = `APAKAH ANDA YAKIN INGIN MERESET: ${count}?${noteReseed}\n\nTindakan ini tidak dapat dibatalkan! Tekan OK untuk melanjutkan proses reset.`;
            
            if (confirm(message)) {
                event.target.submit();
            }
        }
    }
}
</script>
@endpush

