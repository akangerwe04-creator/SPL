@extends('layouts.app')

@section('title', 'Edit Konsumen - ' . $customer->name)
@section('page_title', 'Edit Data Konsumen & Pilihan Paket')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800">
        {{ $customer->code }}
    </span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="customerEditor()">

    <!-- TOP NAVIGATION -->
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('customers.show', $customer) }}" class="inline-flex items-center font-bold text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Profil Konsumen
        </a>
        <span class="text-slate-400">Kode: <strong class="text-slate-700 font-mono">{{ $customer->code }}</strong></span>
    </div>

    <!-- MAIN FORM -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8">
        <div class="pb-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-heading font-extrabold text-slate-900 text-base">Edit Data Konsumen & Pilihan Paket</h3>
                <p class="text-xs text-slate-400 mt-0.5">Perbarui informasi identitas, paket yang diambil, atau pilihan kemasan/wadah.</p>
            </div>
            @if($activePackage && $activePackage->package)
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase {{ $activePackage->package->isParsel() ? 'bg-pink-100 text-pink-800 border border-pink-200' : ($activePackage->package->isTabungan() ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-900 border border-blue-200') }}">
                    Paket: {{ $activePackage->package->name }}
                </span>
            @elseif($activePackage)
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-900 border border-amber-300">
                    Paket Sebelumnya Dihapus (Wajib Memilih Paket)
                </span>
            @endif
        </div>

        <form action="{{ route('customers.update', $customer) }}" method="POST" class="mt-6 space-y-6 text-xs">
            @csrf
            @method('PUT')

            <!-- 1. IDENTITAS KONSUMEN -->
            <div class="space-y-4">
                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-user text-emerald-600 mr-1.5"></i>
                    1. Data Identitas Konsumen
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap Konsumen *</label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm font-bold text-slate-800" placeholder="Nama lengkap konsumen">
                        <span class="text-[10px] text-slate-400 mt-1 block">Nama tidak boleh sama/duplikat dengan konsumen lain di agen yang sama.</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor WhatsApp / Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm" placeholder="Contoh: 081234567890">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea name="address" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500" placeholder="Contoh: Kp. Babakan RT 02/03 Desa Sukamaju">{{ old('address', $customer->address) }}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500" placeholder="Catatan jam tagih, patokan rumah, dll">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>

                @if(auth()->user()->isOwner())
                    <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200">
                        <label class="block font-bold text-amber-950 mb-1 flex items-center">
                            <i class="fa-solid fa-user-tie text-amber-600 mr-1.5"></i>
                            Pindahkan / Tugaskan ke Agen Lapangan Lain (Hak Akses Owner)
                        </label>
                        <select name="agent_id" class="w-full sm:w-1/2 p-2 bg-white border border-amber-300 rounded-lg focus:outline-none focus:border-amber-600 text-xs font-semibold text-slate-800">
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ (old('agent_id', $customer->agent_id) == $ag->id) ? 'selected' : '' }}>
                                    {{ $ag->name }} ({{ $ag->phone ?: 'Tanpa nomor' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- 2. EDIT PILIHAN PAKET & PILIHAN KEMASAN -->
            <div class="space-y-4 pt-6 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-gift text-pink-600 mr-1.5"></i>
                            2. Edit Pilihan Paket & Pilihan Kemasan
                        </h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Ubah paket yang diambil atau ganti kemasan wadah. Sistem otomatis menghitung ulang sisa tagihan dan menyesuaikan ceklis.
                        </p>
                    </div>
                    @if($activePackage && $activePackage->paid_count > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                            <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i>
                            {{ $activePackage->paid_count }} Cicilan Terbayar ({{ $activePackage->formatted_paid }})
                        </span>
                    @endif
                </div>

                @if($activePackage && !$activePackage->package)
                    <div class="p-4 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-950 flex items-start space-x-3">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5"></i>
                        <div>
                            <strong class="font-bold text-xs">Pemberitahuan: Paket Sebelumnya Dihapus Oleh Owner</strong>
                            <p class="text-xs text-amber-900 mt-0.5 leading-relaxed">
                                Pilihan paket/tabungan konsumen ini otomatis dibatalkan.
                                @if($activePackage->total_paid > 0)
                                    Dana setoran yang telah terkumpul sebesar <strong class="font-mono text-emerald-800">{{ $activePackage->formatted_paid }}</strong> tersimpan aman dan akan otomatis memotong total tagihan paket baru yang Anda pilih di bawah.
                                @else
                                    Silakan pilih paket baru pengganti di bawah ini untuk mengaktifkan kembali konsumen.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif

                <!-- DROPDOWN PILIH PAKET -->
                <div class="space-y-1">
                    <label class="block font-semibold text-slate-700">Pilih Paket Program Baru / Pengganti *</label>
                    <select name="package_id" x-model="selectedPackageId" @change="onPackageChange()" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500 text-sm font-bold text-slate-800">
                        <option value="">-- Pilih Paket Program --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}">
                                {{ $pkg->name }} ({{ strtoupper($pkg->type) }} &bull; {{ $pkg->installment_count }} {{ $pkg->period_label }} &bull; Setor: {{ $pkg->formatted_installment }}/{{ strtolower($pkg->period_label) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- LIVE SELECTED PACKAGE CARD -->
                <template x-if="selectedPackageInfo">
                    <div class="p-4 sm:p-5 rounded-2xl bg-white border-2 border-pink-200 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-pink-100">
                            <!-- FOTO & INFO PAKET -->
                            <div class="flex items-center space-x-3.5">
                                <template x-if="selectedPackageInfo.image_url">
                                    <img :src="selectedPackageInfo.image_url" alt="Foto Paket" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl border border-pink-200 shadow-xs shrink-0">
                                </template>
                                <template x-if="!selectedPackageInfo.image_url">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-pink-50 border border-pink-200 flex flex-col items-center justify-center text-pink-700 shrink-0">
                                        <i class="fa-solid fa-gift text-xl mb-0.5"></i>
                                        <span class="text-[8px] font-bold uppercase" x-text="selectedPackageInfo.type"></span>
                                    </div>
                                </template>

                                <div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full"
                                            :class="selectedPackageInfo.type === 'tabungan' ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-pink-100 text-pink-900 border border-pink-200'"
                                            x-text="`Paket ${selectedPackageInfo.type.toUpperCase()}`">
                                        </span>
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200"
                                            x-text="`${selectedPackageInfo.count} ${selectedPackageInfo.label} (${selectedPackageInfo.period})`">
                                        </span>
                                    </div>
                                    <h4 class="font-heading font-black text-slate-900 text-sm sm:text-base mt-1" x-text="selectedPackageInfo.name"></h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5" x-show="selectedPackageInfo.description" x-text="selectedPackageInfo.description"></p>
                                </div>
                            </div>

                            <!-- LIVE RINCIAN HARGA & CICILAN -->
                            <div class="p-3 bg-pink-50/70 rounded-xl border border-pink-200 text-left sm:text-right shrink-0">
                                <template x-if="selectedPackageInfo.type === 'tabungan'">
                                    <div class="mb-1.5 pb-1 border-b border-amber-200/80">
                                        <div class="text-[9px] font-bold text-slate-500 uppercase">Uang Cair:</div>
                                        <div class="text-base font-black text-emerald-800" x-text="formatRupiah(selectedPackageInfo.base_price)"></div>
                                    </div>
                                </template>
                                <div class="text-[9px] font-bold text-slate-500 uppercase">
                                    <span x-text="selectedPackageInfo.type === 'tabungan' ? 'Nominal Stor Mingguan:' : 'Setoran / Cicilan:'"></span>
                                </div>
                                <div class="text-lg font-black text-pink-900">
                                    <span x-text="formatRupiah(currentInstallment)"></span>
                                    <span class="text-xs font-bold text-slate-500">/ <span x-text="selectedPackageInfo.label.toLowerCase()"></span></span>
                                </div>
                                <div class="text-[10px] text-slate-600 font-bold mt-0.5">
                                    Total Target: <span class="font-black text-slate-900" x-text="formatRupiah(currentTargetNominal)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. PILIHAN KEMASAN / WADAH PAKET -->
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between pb-1.5 border-b border-pink-100">
                                <label class="block font-bold text-slate-900 text-xs flex items-center">
                                    <i class="fa-solid fa-box-archive text-pink-600 mr-1.5"></i>
                                    Pilihan Kemasan / Wadah Paket (Dus, Koper, Keranjang Hampers, dll)
                                </label>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-200">
                                    Wadah Kemasan
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <!-- Opsi Standar Dus (Rp 0) -->
                                <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3"
                                       :class="selectedPackagingId === '' ? 'border-pink-600 bg-pink-50/70 text-slate-900 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-slate-50/40 text-slate-600'">
                                    <input type="radio" name="packaging_id" value="" x-model="selectedPackagingId" @change="onPackagingSelect(null)" class="text-pink-600 mt-1">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Kemasan Standar (Dus)</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Wadah dus karton standar tanpa biaya tambahan.</div>
                                        <div class="text-[11px] font-black text-slate-700 mt-1.5">+ Rp 0</div>
                                    </div>
                                </label>

                                <!-- Pilihan Kemasan Khusus Yang Ditawarkan -->
                                <template x-for="pkg in availablePackagings" :key="pkg.id">
                                    <label class="p-3 rounded-xl border-2 cursor-pointer transition flex items-start space-x-3 relative group"
                                           :class="selectedPackagingId === String(pkg.id) ? 'border-pink-600 bg-pink-50/70 text-slate-900 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-600'">
                                        <input type="radio" name="packaging_id" :value="pkg.id" x-model="selectedPackagingId" @change="onPackagingSelect(pkg)" class="text-pink-600 mt-1 shrink-0">
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center relative cursor-pointer"
                                             @click.stop="openPreviewPhoto(pkg.image_url, pkg.name + ' (' + pkg.code + ')')">
                                            <template x-if="pkg.image_url">
                                                <img :src="pkg.image_url" :alt="pkg.name" class="w-full h-full object-cover group-hover:scale-105 transition">
                                            </template>
                                            <template x-if="!pkg.image_url">
                                                <i class="fa-solid fa-box-archive text-slate-300 text-lg"></i>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <span class="font-mono text-[9px] font-bold text-pink-700 bg-pink-100 px-1 py-0.2 rounded" x-text="pkg.code"></span>
                                                <template x-if="pkg.is_default">
                                                    <span class="text-[8px] font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200">Saran</span>
                                                </template>
                                            </div>
                                            <div class="text-xs font-bold text-slate-900 truncate mt-0.5" x-text="pkg.name"></div>
                                            <div class="text-[11px] font-extrabold text-pink-700 mt-1" x-text="`+ ${pkg.formatted_price}`"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- BANNER PENJELASAN ATURAN PERUBAHAN CICILAN -->
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-950 space-y-1">
                            <div class="font-bold flex items-center">
                                <i class="fa-solid fa-circle-info text-amber-600 mr-1.5"></i>
                                Catatan Sinkronisasi Ceklis Tagihan:
                            </div>
                            <p class="text-[11px] text-amber-900 leading-relaxed">
                                Jika konsumen belum pernah menyetor, jadwal ceklis tagihan akan dibuat ulang sepenuhnya sesuai durasi dan cicilan paket baru. Jika sudah ada cicilan yang terbayar, nominal cicilan yang telah disetor tetap aman dan sisa target tagihan akan dibagi merata ke sisa kotak ceklis berikutnya.
                            </p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- BUTTONS ACTION -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-center">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-check mr-1"></i>
                    <span>Simpan Perubahan Data & Paket</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 3. ZONA HAPUS KONSUMEN (JIKA TIDAK JADI IKUT) -->
    <div class="bg-red-50/70 rounded-2xl border-2 border-red-200 p-5 md:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i>
                <h4 class="font-bold text-red-950 text-sm">Konsumen Tidak Jadi Ikut Program SPL?</h4>
            </div>
            <p class="text-xs text-red-800">
                Jika konsumen batal mendaftar atau mundur sebelum mulai, Anda dapat menghapus data konsumen ini secara permanen dari sistem.
            </p>
        </div>

        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data konsumen {{ addslashes($customer->name) }} secara permanen karena tidak jadi ikut? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center justify-center space-x-1.5 shrink-0">
                <i class="fa-solid fa-trash-can mr-1"></i>
                <span>Hapus Konsumen Ini</span>
            </button>
        </form>
    </div>

    <!-- MODAL ZOOM FOTO KEMASAN -->
    <div x-show="previewPhotoSrc" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4" style="display: none;" @click="previewPhotoSrc = null" @keydown.escape.window="previewPhotoSrc = null">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-pink-100" @click.stop>
            <div class="p-4 bg-gradient-to-r from-pink-600 to-rose-600 text-white flex items-center justify-between">
                <span class="font-bold text-sm" x-text="previewPhotoTitle"></span>
                <button type="button" @click="previewPhotoSrc = null" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">✕</button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center min-h-[300px]">
                <img :src="previewPhotoSrc" alt="Foto Kemasan" class="max-h-[450px] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 text-right">
                <button type="button" @click="previewPhotoSrc = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold text-xs">Tutup</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function customerEditor() {
    return {
        selectedPackageId: '{{ old('package_id', $activePackage ? $activePackage->package_id : '') }}',
        selectedPackagingId: '{{ old('packaging_id', $activePackage ? ($activePackage->packaging_id ?: '') : '') }}',
        selectedPackagingPrice: {{ $activePackage ? (float)$activePackage->packaging_price : 0 }},
        selectedPackageInfo: null,
        previewPhotoSrc: null,
        previewPhotoTitle: '',

        packagesLookup: {
            @foreach($packages as $pkg)
                '{{ $pkg->id }}': {
                    id: '{{ $pkg->id }}',
                    name: @json($pkg->name),
                    type: '{{ $pkg->type }}',
                    period: '{{ $pkg->period_type ?? 'mingguan' }}',
                    label: '{{ $pkg->period_label }}',
                    count: {{ (int)$pkg->installment_count }},
                    installment: {{ (int)$pkg->installment_amount }},
                    formatted_installment: '{{ $pkg->formatted_installment }}',
                    base_price: {{ (int)$pkg->base_price }},
                    formatted_base_price: '{{ $pkg->formatted_base_price }}',
                    target: {{ (int)$pkg->target_nominal }},
                    formatted_target: '{{ $pkg->formatted_target }}',
                    admin_fee: {{ (int)($pkg->admin_fee ?: 0) }},
                    image_url: '{{ $pkg->image_path ? asset($pkg->image_path) : '' }}',
                    description: @json($pkg->description),
                    packagings: [
                        @foreach($pkg->packagings as $kms)
                            {
                                id: '{{ $kms->id }}',
                                code: @json($kms->code),
                                name: @json($kms->name),
                                price: {{ $kms->pivot->custom_price !== null ? (int)$kms->pivot->custom_price : (int)$kms->price }},
                                formatted_price: 'Rp {{ number_format($kms->pivot->custom_price !== null ? (float)$kms->pivot->custom_price : (float)$kms->price, 0, ',', '.') }}',
                                image_url: @json($kms->image_url),
                                is_default: {{ $kms->pivot->is_default ? 'true' : 'false' }}
                            },
                        @endforeach
                    ]
                },
            @endforeach
        },

        allMasterPackagings: [
            @foreach($packagings as $mKms)
                {
                    id: '{{ $mKms->id }}',
                    code: @json($mKms->code),
                    name: @json($mKms->name),
                    price: {{ (int)$mKms->price }},
                    formatted_price: '{{ $mKms->formatted_price }}',
                    image_url: @json($mKms->image_url),
                    is_default: false
                },
            @endforeach
        ],

        get availablePackagings() {
            if (this.selectedPackageInfo && this.selectedPackageInfo.packagings && this.selectedPackageInfo.packagings.length > 0) {
                return this.selectedPackageInfo.packagings;
            }
            return this.allMasterPackagings;
        },

        init() {
            if (this.selectedPackageId && this.packagesLookup[this.selectedPackageId]) {
                this.selectedPackageInfo = this.packagesLookup[this.selectedPackageId];
                if (this.selectedPackagingId) {
                    const found = this.availablePackagings.find(k => String(k.id) === String(this.selectedPackagingId));
                    if (found) {
                        this.selectedPackagingPrice = Number(found.price) || 0;
                    }
                }
            }
        },

        onPackageChange() {
            if (!this.selectedPackageId || !this.packagesLookup[this.selectedPackageId]) {
                this.selectedPackageInfo = null;
                this.selectedPackagingId = '';
                this.selectedPackagingPrice = 0;
                return;
            }
            this.selectedPackageInfo = this.packagesLookup[this.selectedPackageId];

            // Cek apakah packaging sebelumnya masih valid di paket baru
            if (this.selectedPackagingId) {
                const found = this.availablePackagings.find(k => String(k.id) === String(this.selectedPackagingId));
                if (found) {
                    this.selectedPackagingPrice = Number(found.price) || 0;
                } else {
                    this.selectedPackagingId = '';
                    this.selectedPackagingPrice = 0;
                }
            }
        },

        onPackagingSelect(pkg) {
            if (!pkg) {
                this.selectedPackagingId = '';
                this.selectedPackagingPrice = 0;
            } else {
                this.selectedPackagingId = String(pkg.id);
                this.selectedPackagingPrice = Number(pkg.price) || 0;
            }
        },

        openPreviewPhoto(src, title) {
            if (!src) return;
            this.previewPhotoSrc = src;
            this.previewPhotoTitle = title || 'Foto Kemasan';
        },

        roundInstallment(val) {
            val = Math.ceil(Number(val) || 0);
            if (val <= 0) return 0;
            const rem = val % 1000;
            if (rem === 0) return val;
            const base = Math.floor(val / 1000) * 1000;
            if (rem <= 500) {
                return base + 500;
            }
            return base + 1000;
        },

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        get currentTargetNominal() {
            if (!this.selectedPackageInfo) return 0;
            const target = Number(this.selectedPackageInfo.target) || 0;
            const pkgPrice = Number(this.selectedPackagingPrice) || 0;
            return target + pkgPrice;
        },

        get currentInstallment() {
            if (!this.selectedPackageInfo) return 0;
            const count = this.selectedPackageInfo.count || 30;
            const target = this.currentTargetNominal;
            return this.roundInstallment(target / count);
        }
    };
}
</script>
@endpush
