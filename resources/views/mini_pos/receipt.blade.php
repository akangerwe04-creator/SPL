@extends('layouts.app')

@section('title', 'Berita Acara Penyesuaian - ' . $fulfillment->customerPackage->customer->name)
@section('page_title', 'Nota & Berita Acara Serah Terima Parsel')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- ACTION BUTTONS -->
    <div class="flex items-center justify-between no-print text-xs">
        <a href="{{ route('mini-pos.index') }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Mini-POS
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm transition flex items-center">
            <i class="fa-solid fa-print mr-1.5"></i> Cetak Berita Acara (Print)
        </button>
    </div>

    <!-- DOCUMENT PAPER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-8 text-slate-800 print:border-none print:shadow-none print:p-0">
        
        @php
            $receiptSetting = \App\Models\CatalogSetting::current();
        @endphp
        <!-- HEADER KOP SURAT RESMI -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div class="flex items-center space-x-4 text-left">
                @if($receiptSetting->logo_path)
                    <img src="{{ asset($receiptSetting->logo_path) }}" alt="{{ $receiptSetting->app_name }}" class="h-16 w-16 object-contain rounded-xl border border-pink-200 p-1 bg-white">
                @else
                    <div class="h-14 w-14 rounded-xl bg-pink-700 text-white font-extrabold flex items-center justify-center text-xl">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                @endif
                <div>
                    <div class="font-heading font-extrabold text-xl tracking-tight text-slate-900 uppercase">
                        {{ $receiptSetting->app_name ?: 'SPL (SUSI PARSEL LEBARAN)' }}
                    </div>
                    <div class="text-xs text-slate-600 font-medium mt-0.5">
                        {{ $receiptSetting->title ?: 'Penyedia Parsel & Tabungan Hari Raya Idul Fitri 1447 H / 2026 M' }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        Alamat: {{ $receiptSetting->owner_address ?: 'Kp. Cibeet' }} &bull; Kontak: {{ $receiptSetting->contact_person ?: '0812-3456-7890' }}
                    </div>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <span class="inline-block px-2.5 py-1 rounded bg-pink-50 text-pink-900 border border-pink-200 text-[10px] font-bold uppercase tracking-wider">Dokumen Sah</span>
            </div>
        </div>

        <div class="text-center my-6">
            <h2 class="text-base font-extrabold uppercase tracking-wider text-slate-900 underline">
                @if($fulfillment->isCashSettlement())
                    KWITANSI & BERITA ACARA SERAH TERIMA DANA TABUNGAN (TABUNGAN MAGOL)
                @else
                    BERITA ACARA PENYESUAIAN BARANG PARSEL (PARTIAL FULFILLMENT)
                @endif
            </h2>
            <div class="text-xs text-slate-500 font-mono mt-1">
                No. Dokumen: {{ $fulfillment->isCashSettlement() ? 'KW-TAB' : 'BAP' }}-{{ date('Ym') }}-{{ str_pad($fulfillment->id, 4, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <!-- INFO DATA KONSUMEN -->
        <div class="grid grid-cols-2 gap-4 text-xs mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div class="space-y-1.5">
                <div><span class="text-slate-400">Nama Konsumen:</span> <strong>{{ $fulfillment->customerPackage->customer->name }}</strong></div>
                <div><span class="text-slate-400">No. Telepon / WA:</span> {{ $fulfillment->customerPackage->customer->phone ?: '-' }}</div>
                <div><span class="text-slate-400">Alamat:</span> {{ $fulfillment->customerPackage->customer->address ?: '-' }}</div>
            </div>
            <div class="space-y-1.5">
                <div><span class="text-slate-400">Paket Pilihan Asal:</span> <strong>{{ $fulfillment->customerPackage->package->name }}</strong></div>
                <div><span class="text-slate-400">Staff Agen Lapangan:</span> {{ $fulfillment->customerPackage->agent->name }}</div>
                <div><span class="text-slate-400">Tanggal Penyesuaian:</span> {{ $fulfillment->created_at->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        @if($fulfillment->isCashSettlement())
            <!-- PENJELASAN SERAH TERIMA TABUNGAN MAGOL -->
            <p class="text-xs text-slate-700 leading-relaxed mb-4">
                Berdasarkan rekapitulasi setoran hingga batas akhir musim, konsumen di atas telah menyetorkan dana tabungan sebesar <strong>{{ $fulfillment->formatted_paid }}</strong> dari total target paket semula (belum lunas penuh / magol). Sesuai aturan SPL, seluruh bonus barang dinyatakan hangus, dan dana diserahterimakan secara tunai kepada konsumen dengan rincian administrasi sebagai berikut:
            </p>

            <!-- TABEL KEUANGAN SERAH TERIMA DANA TABUNGAN -->
            <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-200">
                        <tr class="bg-slate-50">
                            <td class="py-2.5 px-4 text-slate-600 font-medium">Total Dana Tabungan Masuk (Disetor Konsumen):</td>
                            <td class="py-2.5 px-4 text-right font-extrabold text-slate-900">{{ $fulfillment->formatted_paid }}</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-4 text-indigo-700 font-medium">Potongan Biaya Administrasi (SPL):</td>
                            <td class="py-2.5 px-4 text-right font-extrabold text-indigo-700">- {{ $fulfillment->formatted_admin_fee }}</td>
                        </tr>
                        @if($fulfillment->remaining_refund > 0)
                        <tr>
                            <td class="py-2.5 px-4 text-slate-500">Sisa Saldo Kas SPL / Selisih Ditahan:</td>
                            <td class="py-2.5 px-4 text-right font-bold text-slate-600">{{ $fulfillment->formatted_remaining_refund }}</td>
                        </tr>
                        @endif
                        <tr class="bg-emerald-50 border-t-2 border-emerald-600">
                            <td class="py-3 px-4 text-emerald-950 font-heading font-extrabold text-sm uppercase">
                                JUMLAH UANG TUNAI DITERIMA KONSUMEN:
                            </td>
                            <td class="py-3 px-4 text-right font-heading font-extrabold text-lg text-emerald-700">
                                {{ $fulfillment->formatted_cash_payout }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <!-- PENJELASAN SERAH TERIMA BARANG PENGGANTI PARSEL -->
            <p class="text-xs text-slate-700 leading-relaxed mb-4">
                Berdasarkan rekapitulasi setoran hingga batas akhir program, konsumen di atas telah menyetorkan dana sebesar <strong>{{ $fulfillment->formatted_paid }}</strong> dari target paket semula (belum lunas penuh). Sesuai kesepakatan bersama secara adil dan transparan, diserahterimakan paket barang pengganti dengan rincian sebagai berikut:
            </p>

            <!-- TABLE OF REPLACEMENT GOODS -->
            <table class="w-full text-left border-collapse text-xs mb-6">
                <thead>
                    <tr class="border-y-2 border-slate-800 bg-slate-100/70 font-bold text-slate-900">
                        <th class="py-2.5 px-3">No</th>
                        <th class="py-2.5 px-3">Nama Barang Pengganti</th>
                        <th class="py-2.5 px-3 text-center">Banyaknya</th>
                        <th class="py-2.5 px-3 text-right">Nilai Satuan</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($fulfillment->items as $idx => $item)
                        <tr>
                            <td class="py-2 px-3 text-slate-500 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">
                                {{ $item->item->name }}
                            </td>
                            <td class="py-2 px-3 text-center">
                                {{ $item->quantity }} {{ $item->item->base_unit }}
                            </td>
                            <td class="py-2 px-3 text-right">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-right font-bold text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-800 font-bold">
                        <td colspan="4" class="py-2 px-3 text-right">Total Nilai Barang Diterima:</td>
                        <td class="py-2 px-3 text-right font-extrabold text-slate-900">{{ $fulfillment->formatted_items_value }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="py-1 px-3 text-right text-slate-500">Saldo Setoran Konsumen:</td>
                        <td class="py-1 px-3 text-right text-slate-700">{{ $fulfillment->formatted_paid }}</td>
                    </tr>
                    @if($fulfillment->admin_fee > 0)
                    <tr class="text-indigo-700 font-medium">
                        <td colspan="4" class="py-1 px-3 text-right">Potongan Biaya Administrasi (SPL):</td>
                        <td class="py-1 px-3 text-right font-bold text-indigo-700">- {{ $fulfillment->formatted_admin_fee }}</td>
                    </tr>
                    @endif
                    <tr class="text-emerald-800 font-bold">
                        <td colspan="4" class="py-1 px-3 text-right">Sisa Saldo Uang Tunai Dikembalikan:</td>
                        <td class="py-1 px-3 text-right text-emerald-700 font-extrabold">{{ $fulfillment->formatted_remaining_refund }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        @if($fulfillment->notes)
            <div class="text-[11px] text-slate-500 italic mb-8">
                * Catatan: {{ $fulfillment->notes }}
            </div>
        @endif

        <!-- SIGNATURES -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs pt-6 mt-8 border-t border-slate-200">
            <div>
                <div class="text-slate-500 mb-16">Penerima (Konsumen),</div>
                <div class="font-bold underline text-slate-900">{{ $fulfillment->customerPackage->customer->name }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-16">Pihak Pengelola (Owner SPL),</div>
                <div class="font-bold underline text-slate-900">{{ $fulfillment->adjuster->name ?? ($receiptSetting->owner_name ?? 'Susi') }}</div>
            </div>
        </div>

    </div>

</div>

<style>
@media print {
    .no-print, header, aside, nav {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection

