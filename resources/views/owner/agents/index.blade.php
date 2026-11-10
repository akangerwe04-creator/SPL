@extends('layouts.app')

@section('title', 'Menu Agen & Tim Lapangan')
@section('page_title', 'Menu Agen Lapangan & Konsumen Binaan')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
        {{ $totalAgents }} Agen Terdaftar
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="agentManagement()">

    <!-- KPI SUMMARY -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Agen Lapangan</div>
                <div class="text-xl font-heading font-extrabold text-slate-900 mt-1">{{ $totalAgents }} Staff Agen</div>
                <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Aktif menagih & mendata</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-users-gear"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Konsumen Binaan</div>
                <div class="text-xl font-heading font-extrabold text-slate-900 mt-1">{{ $totalCustomers }} Orang</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">Terdaftar di berbagai paket</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-id-card-clip"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kas Masuk Terkumpul</div>
                <div class="text-xl font-heading font-extrabold text-emerald-700 mt-1">Rp {{ number_format($totalCollected ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">Dari tagihan mingguan lapangan</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>
    </div>

    <!-- TABS NAVIGATION & ACTION -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
        <div class="flex items-center space-x-2 bg-slate-100 p-1 rounded-xl">
            <button type="button" @click="activeTab = 'agents'"
                class="px-4 py-2 rounded-lg font-bold transition flex items-center space-x-2"
                :class="activeTab === 'agents' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <i class="fa-solid fa-user-group"></i>
                <span>Tim Agen Lapangan ({{ $totalAgents }})</span>
            </button>
            <button type="button" @click="activeTab = 'customers'"
                class="px-4 py-2 rounded-lg font-bold transition flex items-center space-x-2"
                :class="activeTab === 'customers' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <i class="fa-solid fa-users"></i>
                <span>Semua Konsumen Binaan ({{ $totalCustomers }})</span>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showImportModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center" title="Import Data Agen secara Massal (.xlsx / .csv)">
                <i class="fa-solid fa-file-arrow-up mr-1.5 text-emerald-200"></i> Import Agen (.xlsx)
            </button>
            <a href="{{ route('owner.agents.export-accounts') }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition flex items-center" title="Export Master Akun Agen (Nama, No WA, Alamat, Username, Password) ke Excel (.xlsx)">
                <i class="fa-solid fa-file-excel mr-1.5 text-indigo-200"></i> Export Akun & Password (.xlsx)
            </a>
            <a href="{{ route('owner.agents.report.export') }}" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-xs transition flex items-center" title="Export Laporan ke Excel (.xlsx)">
                <i class="fa-solid fa-file-invoice mr-1.5 text-emerald-100"></i> Laporan Pesanan (.xlsx)
            </a>
            <a href="{{ route('owner.agents.report') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-file-invoice mr-1.5 text-pink-400"></i> Laporan Keseluruhan Agen & Paket
            </a>
            <button type="button" @click="showAddModal = true" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                <i class="fa-solid fa-user-plus mr-1.5"></i> Daftarkan Agen Baru
            </button>
        </div>
    </div>

    <!-- TAB 1: DAFTAR TIM AGEN LAPANGAN -->
    <div x-show="activeTab === 'agents'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($agents as $agent)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between hover:border-emerald-500 transition">
                <div>
                    <!-- AGENT HEADER -->
                    <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-lg border border-emerald-200 shadow-xs">
                                {{ strtoupper(substr($agent->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-slate-900 text-sm">{{ $agent->name }}</h3>
                                <div class="text-[11px] text-slate-400 font-mono">@<span>{{ $agent->username }}</span></div>
                            </div>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $agent->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $agent->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <!-- AGENT CONTACT & STATS -->
                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="text-slate-400">Nomor WhatsApp:</span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $agent->phone) }}" target="_blank" class="font-bold text-emerald-600 hover:underline flex items-center">
                                <i class="fa-brands fa-whatsapp mr-1 text-emerald-500"></i> {{ $agent->phone }}
                            </a>
                        </div>
                        <div class="flex items-start justify-between text-slate-600">
                            <span class="text-slate-400 shrink-0">Alamat:</span>
                            <span class="text-right font-medium text-slate-700 ml-2 line-clamp-2">{{ $agent->address ?: '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="text-slate-400">Konsumen Binaan:</span>
                            <span class="font-extrabold text-slate-900">{{ $agent->customers_count }} Orang</span>
                        </div>
                    </div>

                    <!-- KREDENSIAL LOGIN & PASSWORD AKUN -->
                    <div class="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs" x-data="{ showPass: false }">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">
                            <span>Kredensial Login Agen</span>
                            <button type="button" @click.stop="showPass = !showPass" class="text-slate-500 hover:text-emerald-700 font-bold normal-case flex items-center space-x-1">
                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                <span x-text="showPass ? 'Tutup' : 'Lihat Sandi'"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-between font-mono text-[11px] bg-white p-2 rounded-lg border border-slate-100">
                            <div>
                                <span class="text-slate-400 text-[10px]">User:</span>
                                <strong class="text-slate-900">{{ $agent->username }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px]">Pass:</span>
                                <span class="font-bold text-pink-700" x-text="showPass ? '{{ $agent->plain_password ?: 'password' }}' : '••••••••'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- FINANCIAL MINI CARD -->
                    <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Kas Terkumpul:</span>
                            <span class="font-extrabold text-emerald-700">Rp {{ number_format($agent->total_collected ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px] pt-1 border-t border-slate-200">
                            <span class="text-slate-400">Target Total:</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format($agent->total_target ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS FOOTER -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('owner.agents.index', ['agent_id' => $agent->id]) }}" @click="activeTab = 'customers'" class="font-bold text-emerald-600 hover:text-emerald-700">
                        Lihat Konsumen &rarr;
                    </a>
                    <div class="flex items-center space-x-1">
                        @php
                            $agentPayload = [
                                'id' => $agent->id,
                                'name' => $agent->name,
                                'username' => $agent->username,
                                'plain_password' => $agent->plain_password ?: 'password',
                                'email' => $agent->email,
                                'phone' => $agent->phone,
                                'address' => $agent->address,
                                'is_active' => (bool)$agent->is_active,
                                'customers_count' => (int)($agent->customers_count ?? 0),
                                'total_collected' => (int)($agent->total_collected ?? 0),
                            ];
                        @endphp

                        <!-- Tombol Edit Data Agen -->
                        <button type="button" @click.stop="openEditModal(@js($agentPayload))" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100 transition" title="Edit Data Agen">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <!-- Tombol Cepat Toggle Status Aktif / Nonaktif -->
                        <form action="{{ route('owner.agents.destroy', $agent) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ $agent->is_active ? 'Nonaktifkan akun agen ini? (Data binaan tetap aman)' : 'Aktifkan kembali akun agen ini?' }}');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="action" value="{{ $agent->is_active ? 'deactivate' : 'activate' }}">
                            <button type="submit" class="p-2 {{ $agent->is_active ? 'text-amber-500 hover:text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50' }} rounded-lg transition" title="{{ $agent->is_active ? 'Nonaktifkan Agen' : 'Aktifkan Kembali Agen' }}">
                                <i class="fa-solid {{ $agent->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                            </button>
                        </form>

                        <!-- Tombol Hapus Akun Agen -->
                        <button type="button" @click.stop="openDeleteModal(@js($agentPayload))" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Hapus Akun Agen">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                <i class="fa-solid fa-user-xmark text-4xl mb-3 text-slate-300"></i>
                <div class="font-bold text-slate-700">Belum Ada Agen Lapangan</div>
                <div class="text-xs mt-1">Daftarkan akun agen lapangan untuk mulai merekrut konsumen binaan.</div>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: SEMUA KONSUMEN BINAAN -->
    <div x-show="activeTab === 'customers'" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
            <form action="{{ route('owner.agents.index') }}" method="GET" class="flex items-center space-x-2">
                <select name="agent_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                    <option value="">Semua Agen Pembina</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                            Binaan: {{ $ag->name }}
                        </option>
                    @endforeach
                </select>
                @if(request()->filled('agent_id'))
                    <a href="{{ route('owner.agents.index') }}" class="text-slate-500 hover:text-slate-800">
                        <i class="fa-solid fa-xmark mr-1"></i> Reset Filter
                    </a>
                @endif
            </form>
            <div class="text-slate-500 font-medium">
                Menampilkan <strong>{{ $customers->total() }}</strong> Konsumen
            </div>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Konsumen</th>
                        <th class="py-3 px-4">Agen Pembina</th>
                        <th class="py-3 px-4">Paket Diambil</th>
                        <th class="py-3 px-4 text-right">Target Tagihan</th>
                        <th class="py-3 px-4 text-right">Sudah Setor</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $cust)
                        @php
                            $activePkg = $cust->activePackage;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $cust->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $cust->phone ?: 'Tanpa nomor HP' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800">{{ $cust->agent ? $cust->agent->name : '-' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($activePkg && $activePkg->package)
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $activePkg->package->isParsel() ? 'bg-emerald-100 text-emerald-800' : ($activePkg->package->isTabungan() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $activePkg->package->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum ambil paket</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                {{ $activePkg ? $activePkg->formatted_target : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-700">
                                {{ $activePkg ? $activePkg->formatted_paid : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($activePkg)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $activePkg->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($activePkg->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($activePkg->status) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($activePkg)
                                    <a href="{{ route('payments.checklist', $activePkg) }}" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold rounded-lg border border-emerald-200 transition">
                                        <i class="fa-solid fa-list-check mr-1"></i> Tagihan
                                    </a>
                                @endif
                                <a href="{{ route('customers.show', $cust) }}" class="p-1.5 text-slate-400 hover:text-slate-800 ml-1" title="Profil Konsumen">
                                    <i class="fa-solid fa-circle-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada data konsumen pada kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL IMPORT DATA AGEN -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 text-xs space-y-4" @click.away="showImportModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-file-arrow-up text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-sm text-slate-900">Import Data Agen Lapangan</h3>
                        <p class="text-[10px] text-slate-400">Unggah file Excel (.xlsx) atau CSV untuk input agen massal</p>
                    </div>
                </div>
                <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- STEP 1: DOWNLOAD TEMPLATE -->
            <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-2">
                <div class="font-bold text-emerald-900 flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fa-solid fa-circle-info mr-1.5 text-emerald-600"></i> Langkah 1: Unduh Format Template
                    </span>
                </div>
                <p class="text-[11px] text-emerald-800 leading-relaxed">
                    Gunakan template resmi agar format kolom (Nama, Username, Password, No WA, Alamat) tepat dan tidak tertukar.
                </p>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('owner.agents.import.template', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition inline-flex items-center text-[11px] shadow-xs">
                        <i class="fa-solid fa-download mr-1.5"></i> Download Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('owner.agents.import.template', ['format' => 'csv']) }}" class="px-3 py-1.5 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold rounded-lg transition inline-flex items-center text-[11px]">
                        <i class="fa-solid fa-file-csv mr-1.5"></i> Template CSV
                    </a>
                </div>
            </div>

            <!-- STEP 2: FORM UPLOAD FILE -->
            <form action="{{ route('owner.agents.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Langkah 2: Pilih File yang Sudah Diisi *</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                    <span class="text-[10px] text-slate-400 mt-1 block">Format didukung: Microsoft Excel (.xlsx), .xls, atau .csv (Maks. 10 MB)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Jika Username Agen Sudah Terdaftar di Sistem:</label>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="duplicate_action" value="update" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="font-bold text-slate-800 text-[11px]">Perbarui Data (Update)</div>
                                <div class="text-[10px] text-slate-400">Nama, No WA, alamat, dan kata sandi baru akan diperbarui</div>
                            </div>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="duplicate_action" value="skip" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="font-bold text-slate-800 text-[11px]">Lewati Akun Tersebut (Skip)</div>
                                <div class="text-[10px] text-slate-400">Pertahankan data lama dan hanya daftarkan agen yang belum ada</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center">
                        <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Mulai Import Agen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH AGEN BARU -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 text-xs space-y-4" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-heading font-extrabold text-sm text-slate-900">Daftarkan Akun Agen Baru</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form action="{{ route('owner.agents.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Agen *</label>
                    <input type="text" name="name" required placeholder="Contoh: Ahmad Hidayat" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username Login * (Tanpa spasi)</label>
                    <input type="text" name="username" required placeholder="Contoh: ahmad" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg font-mono text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp / HP *</label>
                    <input type="text" name="phone" required placeholder="Contoh: 0812-3456-7890" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Lengkap Agen</label>
                    <textarea name="address" rows="2" placeholder="Contoh: Kp. Babakan RT 02/RW 04, Paseh" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kata Sandi Default *</label>
                    <input type="password" name="password" required value="password" placeholder="Minimal 4 karakter" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                    <span class="text-[10px] text-slate-400">Default: password (agen dapat menggantinya nanti)</span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                        Simpan Agen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT DATA AGEN -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 text-xs space-y-4" @click.away="showEditModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <h3 class="font-heading font-extrabold text-sm text-slate-900">Edit Akun Agen: <span x-text="editAgent.name"></span></h3>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form :action="editActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Agen *</label>
                    <input type="text" name="name" x-model="editAgent.name" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username Login * (Tanpa spasi)</label>
                    <input type="text" name="username" x-model="editAgent.username" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg font-mono text-xs font-semibold focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp / HP *</label>
                    <input type="text" name="phone" x-model="editAgent.phone" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Lengkap Agen</label>
                    <textarea name="address" rows="2" x-model="editAgent.address" placeholder="Contoh: Kp. Babakan RT 02/RW 04, Paseh" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:border-emerald-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kata Sandi Baru (Kosongkan jika tidak diganti)</label>
                    <input type="password" name="password" placeholder="Isi hanya jika ingin ganti password" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                    <div class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                        <span>Biarkan kosong jika kata sandi lama tidak diubah.</span>
                        <span>Sandi saat ini: <strong class="font-mono text-pink-700" x-text="editAgent.plain_password || 'password'"></strong></span>
                    </div>
                </div>

                <div class="pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="editAgent.is_active" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="font-bold text-slate-700">Status Agen Aktif (Bisa Login & Menagih)</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HAPUS / KELOLA AKUN AGEN -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 text-xs space-y-4" @click.away="showDeleteModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </div>
                    <h3 class="font-heading font-extrabold text-sm text-slate-900">Kelola / Hapus Akun Agen</h3>
                </div>
                <button type="button" @click="showDeleteModal = false" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                <div class="font-bold text-slate-800 text-sm" x-text="deleteAgent.name"></div>
                <div class="text-[11px] text-slate-500 font-mono" x-text="'@' + deleteAgent.username"></div>
                <div class="text-[11px] text-slate-600 pt-1 flex justify-between">
                    <span>Konsumen Binaan:</span>
                    <strong class="text-slate-900" x-text="(deleteAgent.customers_count || 0) + ' Orang'"></strong>
                </div>
            </div>

            <!-- KONDISI JIKA AGEN MEMILIKI KONSUMEN / DATA SETORAN -->
            <div x-show="(deleteAgent.customers_count > 0 || (deleteAgent.total_collected && deleteAgent.total_collected > 0))" class="space-y-3">
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs leading-relaxed">
                    <div class="font-bold flex items-center text-amber-800 mb-1">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Agen Memiliki Data Konsumen Terkait!
                    </div>
                    Akun ini memiliki data konsumen binaan atau riwayat setoran.
                    Pilih <strong>Nonaktifkan</strong> jika ingin akun tidak bisa login namun data arsip tetap aman, atau pilih <strong>Hapus Permanen</strong> jika ingin menghapus akun beserta seluruh data binaannya.
                </div>

                <div class="space-y-2 pt-2">
                    <!-- FORM NONAKTIFKAN -->
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="action" value="deactivate">
                        <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-user-slash"></i>
                            <span>Nonaktifkan Akun Agen Saja (Aman)</span>
                        </button>
                    </form>

                    <!-- FORM HAPUS PERMANEN -->
                    <form :action="deleteActionUrl" method="POST" onsubmit="return confirm('PERINGATAN: Menghapus permanen akan menghapus akun agen ini beserta seluruh data konsumen binaan dan riwayatnya. Lanjutkan?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="force" value="1">
                        <button type="submit" class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Hapus Permanen Beserta Seluruh Data</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- KONDISI JIKA AGEN BELUM MEMILIKI DATA (BERSIH) -->
            <div x-show="!(deleteAgent.customers_count > 0 || (deleteAgent.total_collected && deleteAgent.total_collected > 0))" class="space-y-3">
                <p class="text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun agen <strong x-text="deleteAgent.name"></strong>? Akun yang belum memiliki data konsumen ini akan dihapus secara permanen dari sistem.
                </p>

                <form :action="deleteActionUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="force" value="1">
                    <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2">
                        <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-xs transition">
                            Ya, Hapus Akun Agen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function agentManagement() {
        return {
            activeTab: '{{ request()->filled('agent_id') ? 'customers' : 'agents' }}',
            showAddModal: false,
            showImportModal: false,
            showEditModal: false,
            showDeleteModal: false,
            editAgent: {
                id: null,
                name: '',
                username: '',
                plain_password: '',
                email: '',
                phone: '',
                address: '',
                is_active: true
            },
            deleteAgent: {
                id: null,
                name: '',
                username: '',
                customers_count: 0,
                total_collected: 0
            },
            editActionUrl: '',
            deleteActionUrl: '',

            openEditModal(agent) {
                this.editAgent = Object.assign({}, agent);
                this.editActionUrl = "{{ route('owner.agents.update', ':id') }}".replace(':id', agent.id);
                this.showEditModal = true;
            },

            openDeleteModal(agent) {
                this.deleteAgent = Object.assign({}, agent);
                this.deleteActionUrl = "{{ route('owner.agents.destroy', ':id') }}".replace(':id', agent.id);
                this.showDeleteModal = true;
            }
        };
    }
</script>
@endpush
@endsection
