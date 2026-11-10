@extends('layouts.app')

@section('title', 'Manajemen Akun Sistem (Admin)')
@section('page_title', 'Pusat Manajemen Akun Pengguna')

@section('header_badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-indigo-100 text-indigo-900 border border-indigo-200">
        <i class="fa-solid fa-shield-halved mr-1 text-indigo-600"></i> Mode Otoritas Admin
    </span>
@endsection

@section('content')
<div x-data="userManagement()" class="space-y-6">

    <!-- HERO / BANNER KHUSUS ADMIN -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold bg-white/20 text-white backdrop-blur mb-2">
                    Akses Rahasia Administrator Sistem
                </span>
                <h2 class="text-xl md:text-2xl font-heading font-extrabold tracking-tight">Kontrol Penuh Seluruh Akun SPL</h2>
                <p class="text-xs md:text-sm text-indigo-100 max-w-2xl mt-1 leading-relaxed">
                    Halaman ini hanya dapat diakses oleh Admin dan tersembunyi dari Owner. Anda memiliki kewenangan penuh untuk menambah, mengedit profil, mereset kata sandi, hingga menghapus akun (termasuk akun Owner).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-white text-indigo-900 font-extrabold text-xs hover:bg-indigo-50 transition shadow-sm flex items-center space-x-1.5">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>+ Tambah Akun Baru</span>
                </button>
            </div>
        </div>
    </div>

    <!-- STATISTIK RINGKAS AKUN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Seluruh Akun</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Terdaftar dalam database</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-pink-700 uppercase tracking-wider">Akun Owner</div>
                <div class="text-2xl font-black text-pink-900 mt-1">{{ $stats['owner'] }}</div>
                <div class="text-[10px] text-pink-600 mt-0.5">Pemilik Usaha (Bisa Diedit/Dihapus)</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-crown"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Akun Admin</div>
                <div class="text-2xl font-black text-indigo-900 mt-1">{{ $stats['admin'] }}</div>
                <div class="text-[10px] text-indigo-600 mt-0.5">Otoritas Tertinggi Tersembunyi</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Akun Agen Lapangan</div>
                <div class="text-2xl font-black text-emerald-900 mt-1">{{ $stats['agent'] }}</div>
                <div class="text-[10px] text-emerald-600 mt-0.5">Penagih & Pengelola Konsumen</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
        </div>
    </div>

    <!-- FILTER & PENCARIAN -->
    <div class="bg-white rounded-2xl p-4 border border-pink-100 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, email, atau no HP..." class="w-full pl-9 pr-3 py-2 text-xs font-semibold bg-pink-50/20 border border-pink-200 rounded-xl focus:outline-none focus:border-pink-500">
                </div>
            </div>

            <div>
                <select name="role" class="w-full py-2 px-3 text-xs font-semibold bg-pink-50/20 border border-pink-200 rounded-xl focus:outline-none focus:border-pink-500">
                    <option value="">Semua Role Pengguna</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner (Pemilik Usaha)</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin (Pengelola Sistem)</option>
                    <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agen Lapangan</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <select name="status" class="w-full py-2 px-3 text-xs font-semibold bg-pink-50/20 border border-pink-200 rounded-xl focus:outline-none focus:border-pink-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Status Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Status Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs rounded-xl transition shadow-xs">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL MANAJEMEN AKUN -->
    <div class="bg-white rounded-2xl border border-pink-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-pink-100 flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-list-check text-pink-600"></i>
                <span>Daftar Seluruh Akun Pengguna</span>
            </h3>
            <span class="text-xs font-semibold text-slate-500">
                Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-pink-50/50 text-slate-700 font-bold border-b border-pink-100">
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3">Pengguna</th>
                        <th class="p-3">Username & Email</th>
                        <th class="p-3">No. HP & Alamat</th>
                        <th class="p-3 text-center">Role / Otoritas</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right pr-4">Aksi Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pink-50">
                    @forelse($users as $idx => $user)
                        <tr class="hover:bg-pink-50/20 transition {{ $user->isOwner() ? 'bg-amber-50/20' : ($user->isAdmin() ? 'bg-indigo-50/20' : '') }}">
                            <td class="p-3 text-center font-bold text-slate-500">
                                {{ $users->firstItem() + $idx }}
                            </td>
                            <td class="p-3">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-xs shadow-xs
                                        {{ $user->isOwner() ? 'bg-pink-600 text-white' : ($user->isAdmin() ? 'bg-indigo-600 text-white' : 'bg-emerald-600 text-white') }}">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                                            <span>{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 border border-indigo-200">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400">ID: #{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="font-mono font-bold text-pink-900">{{ '@' . $user->username }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $user->email }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-slate-800">{{ $user->phone ?: '-' }}</div>
                                <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $user->address ?: '-' }}</div>
                            </td>
                            <td class="p-3 text-center">
                                @if($user->isOwner())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-pink-100 text-pink-900 border border-pink-300">
                                        <i class="fa-solid fa-crown mr-1 text-pink-600"></i> Owner
                                    </span>
                                @elseif($user->isAdmin())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                        <i class="fa-solid fa-user-shield mr-1 text-indigo-600"></i> Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        <i class="fa-solid fa-motorcycle mr-1 text-emerald-600"></i> Agen
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($user->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-right pr-4">
                                <div class="inline-flex items-center space-x-1.5">
                                    <!-- TOMBOL EDIT -->
                                    <button @click="openEditModal({{ json_encode($user) }})" title="Ubah Data Akun" class="p-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- TOMBOL RESET PASSWORD -->
                                    <button @click="openResetPasswordModal({{ json_encode($user) }})" title="Reset Kata Sandi" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition">
                                        <i class="fa-solid fa-key"></i>
                                    </button>

                                    <!-- TOMBOL HAPUS -->
                                    @if($user->id !== auth()->id())
                                        <button @click="openDeleteModal({{ json_encode($user) }})" title="Hapus Akun" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @else
                                        <span title="Akun aktif Anda sendiri tidak dapat dihapus" class="p-1.5 rounded-lg bg-slate-100 text-slate-300 cursor-not-allowed">
                                            <i class="fa-solid fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                                <div>Tidak ada akun yang sesuai dengan kriteria pencarian.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-pink-100 bg-pink-50/20">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== MODAL 1: TAMBAH AKUN BARU ==================== -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showCreateModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-pink-100 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-pink-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-slate-900">Tambah Akun Baru</h3>
                </div>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Role / Peran Pengguna *</label>
                    <select name="role" required class="w-full p-2.5 bg-pink-50/30 border border-pink-200 rounded-xl text-xs font-bold focus:border-pink-500">
                        <option value="agent">Agen Lapangan (Role Standar Operasional)</option>
                        <option value="owner">Owner (Pemilik Usaha SPL)</option>
                        <option value="admin">Admin (Administrator Sistem Tersembunyi)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required placeholder="Contoh: Rina Melati" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Username Unik *</label>
                        <input type="text" name="username" required placeholder="Contoh: rina_spl" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Email Aktif *</label>
                        <input type="email" name="email" required placeholder="rina@spl.com" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812-xxxx-xxxx" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Alamat Operasional</label>
                    <textarea name="address" rows="2" placeholder="Alamat tinggal / domisili penanggung jawab..." class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Kata Sandi Awal *</label>
                    <input type="password" name="password" required minlength="4" placeholder="Minimal 4 karakter" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" id="create_active" checked class="rounded border-pink-300 text-pink-600 focus:ring-pink-500">
                    <label for="create_active" class="text-xs font-bold text-slate-800">Akun Langsung Aktif</label>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-pink-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs rounded-xl transition shadow-sm">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: UBAH DATA AKUN ==================== -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-pink-100 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-pink-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-user-pen text-xs"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-slate-900">Ubah Data Akun</h3>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="editActionUrl" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Role / Peran Pengguna *</label>
                    <select name="role" x-model="selectedUser.role" required class="w-full p-2.5 bg-pink-50/30 border border-pink-200 rounded-xl text-xs font-bold focus:border-pink-500">
                        <option value="owner">Owner (Pemilik Usaha SPL)</option>
                        <option value="admin">Admin (Administrator Sistem Tersembunyi)</option>
                        <option value="agent">Agen Lapangan (Role Standar Operasional)</option>
                    </select>
                    <p class="text-[10px] text-pink-700 font-medium mt-1" x-show="selectedUser.role === 'owner'">
                        * Catatan: Anda dapat mengubah atau menetapkan akun ini sebagai Owner.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="selectedUser.name" required class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Username Unik *</label>
                        <input type="text" name="username" x-model="selectedUser.username" required class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Email *</label>
                        <input type="email" name="email" x-model="selectedUser.email" required class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" x-model="selectedUser.phone" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Alamat Domisili</label>
                    <textarea name="address" x-model="selectedUser.address" rows="2" class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500"></textarea>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" id="edit_active" :checked="selectedUser.is_active" class="rounded border-pink-300 text-pink-600 focus:ring-pink-500">
                    <label for="edit_active" class="text-xs font-bold text-slate-800">Status Akun Aktif</label>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-pink-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs rounded-xl transition shadow-sm">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: RESET PASSWORD ==================== -->
    <div x-show="showResetPasswordModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showResetPasswordModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-pink-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-pink-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-key text-xs"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-slate-900">Reset Kata Sandi Akun</h3>
                </div>
                <button @click="showResetPasswordModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl text-xs text-amber-950 font-medium">
                Mereset kata sandi akun: <strong class="text-amber-900" x-text="selectedUser.name"></strong> (<span class="font-mono" x-text="'@' + selectedUser.username"></span>).
            </div>

            <form :action="resetPasswordActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Kata Sandi Baru *</label>
                    <input type="password" name="new_password" required minlength="4" placeholder="Masukkan kata sandi baru (min 4 karakter)..." class="w-full p-2.5 bg-pink-50/20 border border-pink-200 rounded-xl text-xs font-semibold focus:border-pink-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="showResetPasswordModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl transition shadow-sm">
                        Simpan Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 4: HAPUS AKUN ==================== -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-red-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-red-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-red-950">Konfirmasi Hapus Akun</h3>
                </div>
                <button @click="showDeleteModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="text-xs text-slate-700 space-y-2">
                <p>
                    Apakah Anda yakin ingin menghapus akun berikut secara permanen dari sistem?
                </p>
                <div class="p-3 bg-red-50 border border-red-200 rounded-xl font-medium text-red-950">
                    <div class="font-bold text-sm text-red-900" x-text="selectedUser.name"></div>
                    <div class="font-mono text-xs text-red-800" x-text="'@' + selectedUser.username + ' (' + selectedUser.email + ')'"></div>
                    <div class="text-[10px] uppercase font-extrabold tracking-wider mt-1 text-red-700" x-text="'Peran: ' + selectedUser.role"></div>
                </div>
                <p class="text-[11px] text-slate-500 italic" x-show="selectedUser.role === 'owner'">
                    * Peringatan: Anda sedang menghapus akun bertipe <strong>Owner</strong>. Sesuai hak istimewa Admin, tindakan ini diizinkan.
                </p>
            </div>

            <form :action="deleteActionUrl" method="POST" class="pt-2">
                @csrf
                @method('DELETE')

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl transition shadow-sm">
                        Ya, Hapus Akun Ini
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function userManagement() {
        return {
            showCreateModal: false,
            showEditModal: false,
            showResetPasswordModal: false,
            showDeleteModal: false,
            selectedUser: {},
            editActionUrl: '',
            resetPasswordActionUrl: '',
            deleteActionUrl: '',

            openCreateModal() {
                this.showCreateModal = true;
            },

            openEditModal(user) {
                this.selectedUser = Object.assign({}, user);
                this.editActionUrl = "{{ url('/admin/users') }}/" + user.id;
                this.showEditModal = true;
            },

            openResetPasswordModal(user) {
                this.selectedUser = Object.assign({}, user);
                this.resetPasswordActionUrl = "{{ url('/admin/users') }}/" + user.id + "/reset-password";
                this.showResetPasswordModal = true;
            },

            openDeleteModal(user) {
                this.selectedUser = Object.assign({}, user);
                this.deleteActionUrl = "{{ url('/admin/users') }}/" + user.id;
                this.showDeleteModal = true;
            }
        };
    }
</script>
@endsection

