@extends('layouts.app')

@section('title', 'Tanda Terima Serah Terima Barang Stop Konsumen')
@section('page_title', 'Tanda Terima Barang Stop Konsumen')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- ACTION BUTTONS (HIDDEN IN PRINT) -->
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('owner.customer-stops.index') }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Stop Konsumen
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.show', $customerPackage->customer) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                <i class="fa-solid fa-user mr-1"></i> Profil Konsumen
            </a>
            <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- OFFICIAL PRINTABLE SHEET -->
    <div class="bg-white rounded-2xl border border-slate-300 p-8 shadow-xs text-slate-900 print:border-none print:shadow-none print:p-0 space-y-6">

        <!-- KOP SURAT SPL -->
        <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between">
            <div class="space-y-0.5">
                <h1 class="text-xl font-heading font-black tracking-tight text-pink-700 uppercase">
                    {{ $appSetting->app_name ?? 'SUSI PARSEL LEBARAN (SPL)' }}
                </h1>
                <p class="text-[11px] text-slate-600 font-medium">
                    {{ $appSetting->address ?? 'Paseh - Kab. Sumedang / Jawa Barat' }}
                </p>
                <p class="text-[10px] text-slate-500">
                    Kontak Layanan: {{ $appSetting->phone ?? '0812-xxxx-xxxx' }} • Sistem Manajemen Parsel & Tabungan
                </p>
            </div>
            <div class="text-right">
                <div class="px-3 py-1 rounded-lg bg-pink-50 border border-pink-200 text-pink-900 font-bold text-[11px] uppercase inline-block">
                    SURAT TANDA TERIMA BARANG
                </div>
                <div class="text-[10px] text-slate-400 font-mono mt-1">
                    No: STB-{{ date('ymd', strtotime($customerPackage->stop_approved_at ?: now())) }}-{{ $customerPackage->id }}
                </div>
            </div>
        </div>

        <!-- JUDUL SURAT -->
        <div class="text-center space-y-1">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 underline">
                BERITA ACARA SERAH TERIMA BARANG PENGGANTI TABUNGAN
            </h2>
            <p class="text-[11px] text-slate-600">
                Pemberhentian Kepesertaan Konsumen Atas Kesepakatan Bersama
            </p>
        </div>

        <!-- DATA KONSUMEN & AGEN -->
        <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200 print:bg-white print:border print:border-slate-300">
            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400">Data Konsumen:</div>
                <div class="font-bold text-slate-900 text-sm">{{ $customerPackage->customer?->name }}</div>
                <div class="text-[11px] text-slate-600 font-mono">Kode: {{ $customerPackage->customer?->code }}</div>
                <div class="text-[11px] text-slate-600">Telp/WA: {{ $customerPackage->customer?->phone ?: '-' }}</div>
                <div class="text-[11px] text-slate-600">Alamat: {{ $customerPackage->customer?->address ?: 'Paseh' }}</div>
            </div>

            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400">Data Agen Pembina:</div>
                <div class="font-bold text-slate-900 text-sm">{{ $customerPackage->agent?->name ?? 'Tanpa Agen' }}</div>
                <div class="text-[11px] text-slate-600">Telp/WA: {{ $customerPackage->agent?->phone ?: '-' }}</div>
                <div class="text-[11px] text-slate-600">Paket Diikuti: {{ $customerPackage->package?->name }} ({{ $customerPackage->package?->type }})</div>
                <div class="text-[11px] text-slate-600">Tanggal Disahkan: {{ $customerPackage->stop_approved_at ? $customerPackage->stop_approved_at->format('d/m/Y H:i') : now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- PERNYATAAN PEMBUKA & TOTAL SETORAN -->
        <div class="text-xs text-slate-700 leading-relaxed">
            Menyatakan bahwa konsumen di atas berhenti di tengah jalan atas pengajuan agen dengan alasan: 
            <em class="font-semibold text-slate-900">"{{ $customerPackage->stop_reason ?: 'Tidak melanjutkan' }}"</em>. 
            Sesuai aturan SPL, seluruh setoran masuk senilai <strong class="font-mono text-emerald-800 text-sm">Rp {{ number_format($customerPackage->total_paid, 0, ',', '.') }}</strong> telah dialokasikan ke dalam komoditas barang pengganti berikut:
        </div>

        <!-- TABEL RINCIAN BARANG PENGGANTI DARI OWNER -->
        <div>
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold text-left border-b border-slate-300">
                        <th class="p-2 text-center w-10">No</th>
                        <th class="p-2">Nama Komoditas / Barang</th>
                        <th class="p-2 text-center w-24">Jumlah</th>
                        <th class="p-2 text-right w-32">Harga Satuan</th>
                        <th class="p-2 text-right w-36">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php $no = 1; @endphp
                    @forelse($customerPackage->partialFulfillment?->items ?? [] as $it)
                        <tr>
                            <td class="p-2 text-center font-mono">{{ $no++ }}</td>
                            <td class="p-2 font-bold text-slate-900">
                                {{ $it->item?->name ?? 'Barang Komoditas' }}
                                <div class="text-[10px] text-slate-500 font-normal">{{ $it->item?->category ?? 'SPL' }}</div>
                            </td>
                            <td class="p-2 text-center font-mono font-bold">
                                {{ $it->quantity }} {{ $it->item?->base_unit ?? 'pcs' }}
                            </td>
                            <td class="p-2 text-right font-mono">
                                Rp {{ number_format($it->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="p-2 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($it->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-400 italic">Tidak ada rincian barang.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-slate-900 font-bold bg-slate-50">
                    <tr>
                        <td colspan="4" class="p-2 text-right text-slate-700">Total Nilai Barang Diterima:</td>
                        <td class="p-2 text-right font-mono text-pink-700 text-sm">
                            Rp {{ number_format($customerPackage->partialFulfillment?->total_items_value ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if(($customerPackage->partialFulfillment?->cash_payout ?? 0) > 0)
                        <tr>
                            <td colspan="4" class="p-2 text-right text-slate-700">Sisa Kembalian Uang Tunai Konsumen:</td>
                            <td class="p-2 text-right font-mono text-emerald-700 text-sm">
                                Rp {{ number_format($customerPackage->partialFulfillment->cash_payout, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if(($customerPackage->partialFulfillment?->admin_fee ?? 0) > 0)
                        <tr>
                            <td colspan="4" class="p-2 text-right text-slate-500">Potongan Biaya Administrasi SPL:</td>
                            <td class="p-2 text-right font-mono text-slate-500">
                                Rp {{ number_format($customerPackage->partialFulfillment->admin_fee, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <tr class="bg-slate-200 text-slate-900 font-black">
                        <td colspan="4" class="p-2 text-right uppercase">Total Rekonsiliasi (Sesuai Setoran Masuk):</td>
                        <td class="p-2 text-right font-mono text-sm">
                            Rp {{ number_format($customerPackage->total_paid, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- CATATAN DARI OWNER -->
        @if($customerPackage->partialFulfillment?->notes)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                <span class="font-bold text-slate-700">Catatan Khusus Owner:</span>
                <span class="text-slate-600 italic ml-1">{{ $customerPackage->partialFulfillment->notes }}</span>
            </div>
        @endif

        <!-- TANDA TANGAN (3 PIHAK) -->
        <div class="pt-6">
            <div class="grid grid-cols-3 gap-4 text-center text-xs">
                <div class="space-y-16">
                    <div class="text-[11px] text-slate-500">Konsumen Penerima,</div>
                    <div class="border-t border-slate-400 pt-1 font-bold text-slate-900">
                        {{ $customerPackage->customer?->name }}
                    </div>
                </div>

                <div class="space-y-16">
                    <div class="text-[11px] text-slate-500">Agen Penyerah,</div>
                    <div class="border-t border-slate-400 pt-1 font-bold text-slate-900">
                        {{ $customerPackage->agent?->name ?? 'Agen Lapangan' }}
                    </div>
                </div>

                <div class="space-y-16">
                    <div class="text-[11px] text-slate-500">Owner / Pengelola SPL,</div>
                    <div class="border-t border-slate-400 pt-1 font-bold text-slate-900">
                        {{ $customerPackage->stopApprovedBy?->name ?? 'Owner SPL' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="text-[10px] text-center text-slate-400 pt-4 border-t border-slate-100">
            Dokumen ini sah dan diterbitkan secara digital oleh Sistem Manajemen Susi Parsel Lebaran (SPL).
        </div>
    </div>

</div>
@endsection
