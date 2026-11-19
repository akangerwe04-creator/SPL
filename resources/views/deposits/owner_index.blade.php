@extends('layouts.app')

@section('title', 'Rekonsiliasi Kas Agen')
@section('page_title', 'Rekonsiliasi Kas & Validasi Setoran Agen')

@section('header_badge')
    @if(auth()->user()->isAdmin())
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-900 border border-pink-300 shadow-2xs">
            <i class="fa-solid fa-user-shield mr-1.5 text-pink-700"></i> Akun Admin &bull; Fitur Pengesahan Masal Aktif
        </span>
    @else
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
            Kendali Penuh Owner
        </span>
    @endif
@endsection

@section('content')
@php
    $isAdmin = auth()->user()->isAdmin();
    $currentPerPage = request('per_page', '15');
@endphp

<div class="space-y-6" x-data="{
    selectedDeposits: [],
    selectAll: false,
    showApproveModal: false,
    showRejectModal: false,
    deposits: [
        @foreach($deposits as $dep)
        {
            id: {{ $dep->id }},
            number: '{{ $dep->deposit_number }}',
            agent: '{{ addslashes($dep->agent->name) }}',
            amount: {{ (float)$dep->total_amount }},
            formatted_total: '{{ $dep->formatted_total }}',
            status: '{{ $dep->status }}',
            payments_count: {{ (int)$dep->payments_count }}
        },
        @endforeach
    ],
    get eligibleDeposits() {
        return this.deposits.filter(d => d.status === 'submitted');
    },
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedDeposits = this.eligibleDeposits.map(d => d.id);
        } else {
            this.selectedDeposits = [];
        }
    },
    updateSelectAllState() {
        if (this.eligibleDeposits.length === 0) {
            this.selectAll = false;
            return;
        }
        this.selectAll = this.eligibleDeposits.every(d => this.selectedDeposits.includes(d.id));
    },
    clearSelection() {
        this.selectedDeposits = [];
        this.selectAll = false;
    },
    get selectedTotalAmount() {
        return this.deposits
            .filter(d => this.selectedDeposits.includes(d.id))
            .reduce((sum, d) => sum + d.amount, 0);
    },
    get formattedSelectedTotal() {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(this.selectedTotalAmount);
    }
}">

    <!-- ADMIN EXCLUSIVE BANNER -->
    @if($isAdmin)
        <div class="p-4 rounded-2xl bg-gradient-to-r from-pink-50 via-rose-50 to-pink-50 border border-pink-200 text-pink-950 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
            <div class="flex items-start sm:items-center space-x-3">
                <span class="w-10 h-10 rounded-xl bg-pink-700 text-white flex items-center justify-center shrink-0 text-base shadow-xs">
                    <i class="fa-solid fa-user-shield"></i>
                </span>
                <div>
                    <div class="text-xs font-black uppercase tracking-wider flex items-center gap-1.5 text-pink-900">
                        <span>Akses Admin Lapangan: Validasi & Pengesahan Masal</span>
                        <span class="px-2 py-0.2 rounded-full text-[9px] font-black bg-pink-600 text-white uppercase">Khusus Admin</span>
                    </div>
                    <p class="text-xs text-pink-900/90 mt-0.5 leading-relaxed">
                        Anda dapat mencentang berkas setoran agen melalui kolom ceklis untuk <strong>mengesahkan</strong> atau <strong>menolak</strong> setoran kas secara masal. Gunakan opsi <em>"Tampilkan Semua Baris Data"</em> agar seluruh setoran tampil dalam satu halaman tanpa terpotong pagination.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                @if($currentPerPage === 'all')
                    <a href="{{ route('owner.deposits.index', array_merge(request()->except('per_page'), ['per_page' => '15'])) }}" class="px-3.5 py-1.5 rounded-xl bg-white border border-pink-300 hover:bg-pink-100 text-pink-900 font-extrabold text-xs shadow-2xs transition flex items-center space-x-1">
                        <i class="fa-solid fa-list-ol text-pink-600"></i>
                        <span>Batasi 15 / Hal</span>
                    </a>
                @else
                    <a href="{{ route('owner.deposits.index', array_merge(request()->all(), ['per_page' => 'all'])) }}" class="px-3.5 py-1.5 rounded-xl bg-pink-700 hover:bg-pink-800 text-white font-extrabold text-xs shadow-xs transition flex items-center space-x-1">
                        <i class="fa-solid fa-expand"></i>
                        <span>Tampilkan Semua Data</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- SUMMARY HEADER CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Setoran Menunggu Sah</div>
                <div class="text-xl font-heading font-extrabold text-amber-600 mt-0.5">{{ number_format($submittedCount) }} Berkas</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Perlu diverifikasi & disahkan</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Kas Sah Masuk</div>
                <div class="text-xl font-heading font-extrabold text-emerald-700 mt-0.5">Rp {{ number_format($totalApprovedAmount, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Kas resmi terkunci aman</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Berkas Ditampilkan</div>
                <div class="text-xl font-heading font-extrabold text-slate-800 mt-0.5">
                    {{ $deposits->total() }} Berkas
                    @if($currentPerPage === 'all')
                        <span class="text-xs font-bold text-pink-600 ml-1">(Semua Data)</span>
                    @endif
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                    @if($currentPerPage === 'all')
                        Menampilkan seluruh baris tanpa pagination
                    @else
                        Halaman {{ $deposits->currentPage() }} dari {{ $deposits->lastPage() }}
                    @endif
                </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <form action="{{ route('owner.deposits.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <!-- Pencarian No Setoran / Agen -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Setoran / Agen..." class="pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700 focus:outline-none focus:border-pink-500 w-48 sm:w-56 text-xs">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-[11px]"></i>
            </div>

            <!-- Filter Status -->
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                <option value="">Semua Status Aktif (Diajukan, Disahkan, Ditolak)</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Diajukan Agen (Menunggu Validasi)</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disahkan (Valid)</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Draft Lapangan (Belum Diajukan)</option>
            </select>

            <!-- Filter Agen -->
            <select name="agent_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                <option value="">Semua Agen</option>
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                @endforeach
            </select>

            <!-- Pilihan Jumlah Baris Data (Semua Baris / Pagination) -->
            <select name="per_page" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg font-bold text-slate-800">
                <option value="15" {{ $currentPerPage == '15' ? 'selected' : '' }}>15 Baris / Halaman</option>
                <option value="25" {{ $currentPerPage == '25' ? 'selected' : '' }}>25 Baris / Halaman</option>
                <option value="50" {{ $currentPerPage == '50' ? 'selected' : '' }}>50 Baris / Halaman</option>
                <option value="100" {{ $currentPerPage == '100' ? 'selected' : '' }}>100 Baris / Halaman</option>
                <option value="all" {{ $currentPerPage === 'all' ? 'selected' : '' }}>Tampilkan Semua Baris Data (Semuanya)</option>
            </select>

            <button type="submit" class="px-3 py-2 bg-pink-700 hover:bg-pink-800 text-white rounded-lg font-bold shadow-xs transition">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>

            @if(request()->hasAny(['status', 'agent_id', 'search', 'per_page']))
                <a href="{{ route('owner.deposits.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 font-medium">
                    <i class="fa-solid fa-xmark mr-1"></i> Reset
                </a>
            @endif
        </form>

        <!-- Quick Switcher Baris Data -->
        <div class="flex items-center space-x-2">
            @if($currentPerPage === 'all')
                <a href="{{ route('owner.deposits.index', array_merge(request()->except('per_page'), ['per_page' => '15'])) }}" class="px-3 py-1.5 rounded-lg bg-pink-100 text-pink-900 border border-pink-300 font-bold text-xs flex items-center space-x-1 transition">
                    <i class="fa-solid fa-check text-pink-700"></i>
                    <span>Mode: Tampilkan Semuanya</span>
                </a>
            @else
                <a href="{{ route('owner.deposits.index', array_merge(request()->all(), ['per_page' => 'all'])) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center space-x-1 transition">
                    <i class="fa-solid fa-arrows-up-down text-slate-500"></i>
                    <span>Tampilkan Semua Baris</span>
                </a>
            @endif
        </div>
    </div>

    <!-- DEPOSITS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($deposits->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                <div class="text-sm font-medium">Tidak ditemukan data setoran sesuai filter.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500 font-bold bg-slate-50/50">
                            <!-- Kolom Checkbox: HANYA ADA DI AKUN ADMIN -->
                            @if($isAdmin)
                                <th class="py-3 px-3 text-center w-10">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300 cursor-pointer" title="Pilih Semua yang Perlu Validasi">
                                </th>
                            @endif
                            <th class="py-3 px-4">No. Setoran</th>
                            <th class="py-3 px-4">Nama Agen</th>
                            <th class="py-3 px-4">Nominal Setor</th>
                            <th class="py-3 px-4">Jumlah Ceklis</th>
                            <th class="py-3 px-4">Waktu Pengajuan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($deposits as $idx => $dep)
                            <tr class="hover:bg-pink-50/20 transition {{ $dep->status === 'submitted' ? 'bg-amber-50/20' : '' }}">
                                <!-- Checkbox Baris: HANYA ADA DI AKUN ADMIN -->
                                @if($isAdmin)
                                    <td class="py-3.5 px-3 text-center" onclick="event.stopPropagation()">
                                        @if($dep->status === 'submitted')
                                            <input type="checkbox" :value="{{ $dep->id }}" x-model="selectedDeposits" @change="updateSelectAllState()" class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300 cursor-pointer">
                                        @else
                                            <input type="checkbox" disabled class="w-4 h-4 rounded text-slate-200 border-slate-200 cursor-not-allowed opacity-30" title="{{ $dep->status === 'approved' ? 'Sudah Disahkan' : 'Belum Diajukan' }}">
                                        @endif
                                    </td>
                                @endif

                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                    <div class="flex items-center space-x-1.5">
                                        <span>{{ $dep->deposit_number }}</span>
                                    </div>
                                    @if($dep->agent_notes)
                                        <div class="text-[10px] text-slate-400 font-normal truncate max-w-xs" title="{{ $dep->agent_notes }}">
                                            <i class="fa-regular fa-comment-dots mr-0.5"></i> {{ $dep->agent_notes }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $dep->agent->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $dep->agent->phone ?: '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4 font-extrabold text-emerald-700 text-sm">
                                    {{ $dep->formatted_total }}
                                </td>

                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">
                                        {{ $dep->payments_count }} Ceklis
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-slate-500">
                                    <div class="font-medium text-slate-700">
                                        {{ $dep->submitted_at ? $dep->submitted_at->format('d M Y H:i') : ($dep->created_at->format('d M Y H:i')) }}
                                    </div>
                                    @if($dep->approved_at && $dep->approver)
                                        <div class="text-[10px] text-emerald-600">
                                            Sah: {{ $dep->approver->name }} ({{ $dep->approved_at->format('d/m/Y') }})
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($dep->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Disahkan
                                        </span>
                                    @elseif($dep->status === 'submitted')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 animate-pulse border border-amber-300">
                                            <i class="fa-solid fa-bell mr-1 text-amber-600"></i> Perlu Validasi
                                        </span>
                                    @elseif($dep->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            <i class="fa-solid fa-circle-xmark mr-1 text-rose-600"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                            Draft Belum Diajukan
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('owner.deposits.show', $dep) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold {{ $dep->status === 'submitted' ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} transition">
                                        Periksa <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($currentPerPage !== 'all')
                <div class="p-4 border-t border-slate-100">
                    {{ $deposits->links() }}
                </div>
            @else
                <div class="p-4 border-t border-slate-100 text-center text-xs text-slate-500 font-medium bg-slate-50/50 flex items-center justify-between">
                    <span>Menampilkan seluruh <strong>{{ $deposits->total() }} berkas setoran</strong> tanpa pagination.</span>
                    <a href="{{ route('owner.deposits.index', array_merge(request()->except('per_page'), ['per_page' => '15'])) }}" class="text-pink-600 font-bold hover:underline">
                        Kembalikan ke Pagination (15 Baris / Hal)
                    </a>
                </div>
            @endif
        @endif
    </div>

    <!-- FLOATING BULK ACTION TOOLBAR (HANYA ADA DI AKUN ADMIN) -->
    @if($isAdmin)
        <div x-show="selectedDeposits.length > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-6"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-6"
             class="fixed bottom-6 inset-x-4 max-w-3xl mx-auto bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-white/20 z-50 flex flex-wrap items-center justify-between gap-3"
             style="display: none;">
            
            <div class="flex items-center space-x-3">
                <span class="w-8 h-8 rounded-xl bg-pink-600 text-white font-black text-xs flex items-center justify-center shadow-xs">
                    <span x-text="selectedDeposits.length"></span>
                </span>
                <div>
                    <div class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span>Setoran Kas Terpilih</span>
                        <span class="px-2 py-0.2 rounded-full text-[9px] font-black bg-pink-500/40 text-pink-300 border border-pink-400/40 uppercase">Khusus Admin</span>
                    </div>
                    <div class="text-[11px] text-slate-300 font-medium">
                        Total Kas: <strong class="text-emerald-400 text-xs font-extrabold" x-text="formattedSelectedTotal"></strong>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <button type="button" @click="clearSelection()" class="px-3 py-2 text-xs text-slate-400 hover:text-white font-medium transition">
                    Batal
                </button>
                <button type="button" @click="showRejectModal = true" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                    <i class="fa-solid fa-xmark mr-1.5"></i> Tolak Masal (<span x-text="selectedDeposits.length"></span>)
                </button>
                <button type="button" @click="showApproveModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition flex items-center shadow-md">
                    <i class="fa-solid fa-check-double mr-1.5"></i> Sahkan Masal (<span x-text="selectedDeposits.length"></span>)
                </button>
            </div>
        </div>

        <!-- MODAL KONFIRMASI PENGESAHAN MASAL -->
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="showApproveModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 text-left">
                    <form action="{{ route('owner.deposits.bulk-approve') }}" method="POST">
                        @csrf
                        <template x-for="id in selectedDeposits" :key="id">
                            <input type="hidden" name="deposit_ids[]" :value="id">
                        </template>

                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-heading font-black text-slate-900">Sahkan Setoran Agen Secara Masal</h3>
                                <p class="text-xs text-slate-500">Khusus Akun Admin - Verifikasi Kas Fisik Diterima</p>
                            </div>
                        </div>

                        <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl mb-4 text-xs text-emerald-950">
                            Anda akan mengesahkan <strong class="text-emerald-900 font-black" x-text="selectedDeposits.length"></strong> berkas setoran agen dengan total kas fisik <strong class="text-emerald-900 font-black" x-text="formattedSelectedTotal"></strong>.
                            <div class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                                Setelah disahkan, seluruh ceklis pembayaran konsumen dalam berkas setoran ini akan <strong>terkunci permanen</strong> sebagai lunas sah.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pengesahan Masal (Opsional):</label>
                            <input type="text" name="notes" placeholder="Misal: Kas fisik telah diterima dan diverifikasi lengkap di kantor." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-emerald-500">
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showApproveModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition flex items-center">
                                <i class="fa-solid fa-check-double mr-1.5"></i> Ya, Sahkan (<span x-text="selectedDeposits.length"></span> Setoran)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL KONFIRMASI PENOLAKAN MASAL -->
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="showRejectModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 text-left">
                    <form action="{{ route('owner.deposits.bulk-reject') }}" method="POST">
                        @csrf
                        <template x-for="id in selectedDeposits" :key="id">
                            <input type="hidden" name="deposit_ids[]" :value="id">
                        </template>

                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-heading font-black text-slate-900">Tolak Setoran Agen Secara Masal</h3>
                                <p class="text-xs text-slate-500">Khusus Akun Admin - Minta Revisi Kas Fisik ke Agen</p>
                            </div>
                        </div>

                        <div class="p-3.5 bg-rose-50/70 border border-rose-200 rounded-xl mb-4 text-xs text-rose-950">
                            Anda akan menolak <strong class="text-rose-900 font-black" x-text="selectedDeposits.length"></strong> berkas setoran agen dengan total <strong class="text-rose-900 font-black" x-text="formattedSelectedTotal"></strong>.
                            <div class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                                Berkas setoran yang ditolak akan dikembalikan ke agen terkait untuk disesuaikan dan diceklis ulang.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Alasan Penolakan Masal <span class="text-rose-600">*</span>:
                            </label>
                            <textarea name="notes" rows="3" required placeholder="Contoh: Fisik uang belum diserahkan ke kasir kantor / ada selisih nominal ceklis." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-rose-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl shadow-md transition flex items-center">
                                <i class="fa-solid fa-xmark mr-1.5"></i> Tolak (<span x-text="selectedDeposits.length"></span> Setoran)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
