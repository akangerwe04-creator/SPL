@extends('layouts.app')

@section('title', 'Konfirmasi Stop Konsumen')
@section('page_title', 'Konfirmasi & Penetapan Barang Stop Konsumen')

@section('header_badge')
    @if($pendingCount > 0)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
            <i class="fa-solid fa-clock mr-1.5 text-amber-600"></i> {{ $pendingCount }} Pengajuan Menunggu Konfirmasi
        </span>
    @else
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> Semua Terproses
        </span>
    @endif
@endsection

@section('content')
<div class="space-y-6" x-data="{ rejectModal: false, rejectAction: '', customerName: '', rejectReason: '' }">

    <!-- DESKRIPSI FITUR & INFO OPERASIONAL -->
    <div class="bg-gradient-to-r from-slate-900 via-pink-950 to-slate-900 text-white p-5 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-pink-500/20 text-pink-300 border border-pink-400/30">
                    SOP Penghentian Konsumen SPL
                </span>
                <span class="text-xs text-slate-300">• Wajib Alokasi Barang Pengganti</span>
            </div>
            <h2 class="text-base font-heading font-extrabold text-white">
                Verifikasi Pengajuan Berhenti & Penetapan Barang Sesuai Setoran
            </h2>
            <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                Ketika agen mengajukan konsumen yang tidak ingin melanjutkan, Owner wajib memeriksa total setoran yang telah masuk dan <strong>menentukan rincian barang pengganti yang diberikan</strong> senilai setoran tersebut sebelum pengajuan di-ACC.
            </p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold text-slate-400">Menunggu ACC</div>
                <div class="text-2xl font-black font-mono text-amber-400">{{ $pendingCount }}</div>
            </div>
            <div class="h-8 w-px bg-slate-700"></div>
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold text-slate-400">Sudah di-ACC</div>
                <div class="text-2xl font-black font-mono text-emerald-400">{{ $approvedCount }}</div>
            </div>
        </div>
    </div>

    <!-- TABS & SEARCH BAR -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
        <!-- TABS -->
        <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('owner.customer-stops.index', ['tab' => 'pending', 'search' => request('search')]) }}" 
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center space-x-1.5 shrink-0 {{ $tab === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fa-solid fa-hourglass-half"></i>
                <span>Menunggu Konfirmasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'pending' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800 font-black' }}">{{ $pendingCount }}</span>
            </a>

            <a href="{{ route('owner.customer-stops.index', ['tab' => 'approved', 'search' => request('search')]) }}" 
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center space-x-1.5 shrink-0 {{ $tab === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fa-solid fa-circle-check"></i>
                <span>Disetujui / Selesai (ACC)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'approved' ? 'bg-emerald-800 text-white' : 'bg-emerald-100 text-emerald-800 font-black' }}">{{ $approvedCount }}</span>
            </a>

            <a href="{{ route('owner.customer-stops.index', ['tab' => 'rejected', 'search' => request('search')]) }}" 
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center space-x-1.5 shrink-0 {{ $tab === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fa-solid fa-circle-xmark"></i>
                <span>Ditolak</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'rejected' ? 'bg-rose-800 text-white' : 'bg-rose-100 text-rose-800 font-black' }}">{{ $rejectedCount }}</span>
            </a>

            <a href="{{ route('owner.customer-stops.index', ['tab' => 'all', 'search' => request('search')]) }}" 
               class="px-3 py-2 rounded-xl font-bold transition shrink-0 {{ $tab === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Riwayat
            </a>
        </div>

        <!-- SEARCH -->
        <form action="{{ route('owner.customer-stops.index') }}" method="GET" class="flex items-center space-x-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari konsumen / agen / alasan..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 text-xs w-full sm:w-60 focus:outline-none focus:border-pink-500">
            <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-bold">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('owner.customer-stops.index', ['tab' => $tab]) }}" class="px-2 py-2 text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- DAFTAR PENGAJUAN TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($stopRequests->isEmpty())
            <div class="p-12 text-center text-slate-400 space-y-2">
                <i class="fa-solid fa-inbox text-4xl text-slate-300"></i>
                <div class="font-bold text-slate-700 text-sm">Tidak Ada Pengajuan Stop Konsumen</div>
                <div class="text-xs text-slate-400 max-w-md mx-auto">
                    @if($tab === 'pending')
                        Bagus! Saat ini tidak ada pengajuan stop konsumen dari agen yang menunggu konfirmasi Owner.
                    @else
                        Tidak ditemukan data pengajuan untuk kriteria tab ini.
                    @endif
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/50">
                            <th class="py-3 px-4">Konsumen</th>
                            <th class="py-3 px-4">Agen Penanggung Jawab</th>
                            <th class="py-3 px-4">Paket & Model</th>
                            <th class="py-3 px-4">Total Setoran</th>
                            <th class="py-3 px-4">Alasan Tidak Lanjut</th>
                            <th class="py-3 px-4">Status & Alokasi Barang</th>
                            <th class="py-3 px-4 text-right">Tindakan Owner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($stopRequests as $req)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                                        <a href="{{ route('customers.show', $req->customer) }}" class="hover:text-pink-600 transition">
                                            {{ $req->customer?->name ?? 'Konsumen' }}
                                        </a>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $req->customer?->code ?? '-' }} &bull; {{ $req->customer?->phone ?: 'Tanpa HP' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 flex items-center space-x-1">
                                        <i class="fa-solid fa-user-tie text-slate-400 mr-1"></i>
                                        <span>{{ $req->agent?->name ?? 'Tanpa Agen' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $req->agent?->phone ?: '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">{{ $req->package?->name ?? 'Paket' }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">
                                        {{ $req->package?->type }} &bull; {{ $req->package?->installment_count }} {{ $req->package?->period_label }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-emerald-700 font-mono text-sm">
                                        {{ $req->formatted_paid }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">Target: {{ $req->formatted_target }}</div>
                                </td>

                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="text-xs text-slate-700 font-medium italic break-words line-clamp-2" title="{{ $req->stop_reason }}">
                                        "{{ $req->stop_reason ?: 'Tidak ada alasan rinci' }}"
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Diajukan: {{ $req->stop_requested_at ? $req->stop_requested_at->format('d M Y, H:i') : '-' }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($req->stop_status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                            <i class="fa-solid fa-hourglass-half mr-1 text-amber-600 animate-spin"></i> Menunggu ACC Owner
                                        </span>
                                    @elseif($req->stop_status === 'approved')
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Di-ACC & Barang Dialokasikan
                                            </span>
                                            @if($req->partialFulfillment)
                                                <div class="text-[10px] text-slate-600">
                                                    {{ $req->partialFulfillment->items->count() }} barang (Rp {{ number_format($req->partialFulfillment->total_items_value, 0, ',', '.') }})
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($req->stop_status === 'rejected')
                                        <div class="space-y-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                <i class="fa-solid fa-xmark mr-1 text-rose-600"></i> Ditolak
                                            </span>
                                            <div class="text-[10px] text-rose-700 italic truncate max-w-xs" title="{{ $req->stop_rejection_reason }}">
                                                {{ $req->stop_rejection_reason }}
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        @if($req->stop_status === 'pending')
                                            <!-- TOMBOL PROSES & ACC (Wajib Alokasi Barang) -->
                                            <a href="{{ route('owner.customer-stops.review', $req) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-xs transition" title="Tentukan barang pengganti dan ACC pengajuan">
                                                <i class="fa-solid fa-boxes-packing mr-1.5"></i> Tentukan Barang & ACC
                                            </a>

                                            <!-- TOMBOL TOLAK -->
                                            <button type="button" @click="rejectModal = true; rejectAction = '{{ route('owner.customer-stops.reject', $req) }}'; customerName = '{{ addslashes($req->customer?->name ?? 'Konsumen') }}'; rejectReason = ''" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Tolak Pengajuan">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @elseif($req->stop_status === 'approved')
                                            <!-- LIHAT & CETAK BUKTI SERAH TERIMA -->
                                            <a href="{{ route('owner.customer-stops.receipt', $req) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-xs transition" title="Cetak Surat Serah Terima Barang">
                                                <i class="fa-solid fa-print mr-1.5"></i> Cetak Bukti Serah Terima
                                            </a>
                                        @elseif($req->stop_status === 'rejected')
                                            <a href="{{ route('owner.customer-stops.review', $req) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition" title="Tinjau Ulang">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $stopRequests->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL TOLAK PENGAJUAN STOP -->
    <div x-show="rejectModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4" style="display: none;" @click.self="rejectModal = false" @keydown.escape.window="rejectModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200">
            <div class="p-5 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-rose-950">Tolak Pengajuan Stop Konsumen</h3>
                        <p class="text-[11px] text-rose-700" x-text="`Konsumen: ${customerName}`"></p>
                    </div>
                </div>
                <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form :action="rejectAction" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Alasan Penolakan Pengajuan *</label>
                    <textarea name="rejection_reason" x-model="rejectReason" rows="3" required placeholder="Tuliskan alasan mengapa pengajuan stop ini ditolak (misal: Disepakati untuk tetap lanjut / Salah pengajuan)..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:border-rose-500"></textarea>
                    <p class="text-[10px] text-slate-500 mt-1">Setelah ditolak, status konsumen akan dikembalikan aktif normal pada agen.</p>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold shadow-xs transition">
                        Tolak Pengajuan Stop
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
