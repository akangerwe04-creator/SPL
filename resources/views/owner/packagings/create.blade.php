@extends('layouts.app')

@section('title', 'Tambah Kemasan Baru')
@section('page_title', 'Tambah Kemasan Parsel / Hampers')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    costPrice: {{ old('cost_price', 0) }},
    price: {{ old('price', 0) }},
    imagePreview: null,
    formatRupiah(val) {
        if (!val || isNaN(val)) return 'Rp 0';
        return 'Rp ' + parseInt(val).toLocaleString('id-ID');
    },
    get margin() {
        return Math.max(0, (parseInt(this.price) || 0) - (parseInt(this.costPrice) || 0));
    },
    previewImage(e) {
        const file = e.target.files[0];
        if (file) {
            this.imagePreview = URL.createObjectURL(file);
        }
    }
}">

    <a href="{{ route('owner.packagings.index') }}" class="inline-flex items-center text-xs font-bold text-pink-700 hover:text-pink-800">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Data Kemasan
    </a>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-base">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-slate-900 text-base">Formulir Kemasan Baru</h3>
                    <p class="text-xs text-slate-500">Tambahkan jenis wadah/kemasan seperti Koper, Keranjang Rotan, atau Box Hampers.</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                Menu Owner
            </span>
        </div>

        <form action="{{ route('owner.packagings.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5 text-xs">
            @csrf

            <!-- Kode & Nama Kemasan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label class="block font-semibold text-slate-700 mb-1">Kode Kemasan</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Auto: KMS-XXX" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 font-mono text-slate-900 text-xs uppercase">
                    <span class="text-[10px] text-slate-400 mt-1 block">Kosongkan untuk otomatis</span>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Nama Kemasan *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Koper Eksklusif Fiber 20 Inch / Keranjang Rotan Pita" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900 font-medium">
                    @error('name')
                        <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Harga Kulak vs Harga Tambahan Konsumen -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-pink-50/50 border border-pink-100">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Harga Kulak / Modal Beli Owner *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 font-bold text-slate-400 text-xs">Rp</span>
                        <input type="number" name="cost_price" x-model.number="costPrice" required min="0" step="1000" placeholder="0" class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900 font-mono font-semibold">
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Masuk ke kalkulator belanja barang owner di akhir periode.</span>
                    @error('cost_price')
                        <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Harga Tambahan ke Konsumen / Paket *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 font-bold text-slate-400 text-xs">Rp</span>
                        <input type="number" name="price" x-model.number="price" required min="0" step="1000" placeholder="0" class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900 font-mono font-semibold">
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Ditambahkan ke target paket lalu dibagi jumlah hari/minggu setoran.</span>
                    @error('price')
                        <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Margin preview badge -->
                <div class="sm:col-span-2 pt-2 border-t border-pink-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-600 font-medium">Margin Keuntungan Bersih per Kemasan:</span>
                    <span class="font-bold font-mono px-2 py-0.5 rounded bg-emerald-100 text-emerald-800" x-text="formatRupiah(margin)"></span>
                </div>
            </div>

            <!-- Upload Foto Tampak Kemasan (Wajib/Disarankan) -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">
                    Foto Tampak Kemasan <span class="text-pink-600 font-bold">* Wajib agar tampak di katalog & akun agen</span>
                </label>
                <div class="mt-1 flex flex-col sm:flex-row items-center gap-4 p-4 border-2 border-dashed border-slate-200 rounded-xl hover:border-pink-400 transition bg-slate-50/50">
                    <div class="w-28 h-28 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-inner">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center p-2 text-slate-400">
                                <i class="fa-solid fa-camera text-2xl mb-1 block"></i>
                                <span class="text-[10px]">Preview Foto</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex-1 space-y-2 text-left w-full">
                        <input type="file" name="image" @change="previewImage" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-pink-100 file:text-pink-700 hover:file:bg-pink-200 cursor-pointer">
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Format yang didukung: JPG, PNG, WEBP. Maksimal 8MB. Gambar akan otomatis dioptimasi agar ringan saat diakses oleh agen dari HP.
                        </p>
                    </div>
                </div>
                @error('image')
                    <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Deskripsi / Spesifikasi Kemasan -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Deskripsi / Spesifikasi Kemasan (Opsional)</label>
                <textarea name="description" rows="3" placeholder="Contoh: Koper fiber tahan banting ukuran cabin 20 inch, roda putar 360 derajat, muat hingga 10 toples kue kering dan 2 botol sirup." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-slate-900">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Status Aktif Switch -->
            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                <div>
                    <div class="font-bold text-slate-800 text-xs">Status Aktif Kemasan</div>
                    <div class="text-[11px] text-slate-500">Tampilkan kemasan ini dalam daftar pilihan saat membuat paket atau mendaftarkan konsumen.</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('owner.packagings.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-xs">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-bold rounded-xl shadow-xs transition text-xs flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Kemasan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

