@extends('layouts.app')

@section('title', 'Edit Konsumen - ' . $customer->name)
@section('page_title', 'Edit Data Konsumen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <a href="{{ route('customers.show', $customer) }}" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
        <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Profil Konsumen
    </a>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-8">
        <div class="pb-4 border-b border-slate-100">
            <h3 class="font-heading font-extrabold text-slate-900 text-base">Edit Data Konsumen: {{ $customer->name }}</h3>
        </div>

        <form action="{{ route('customers.update', $customer) }}" method="POST" class="mt-6 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap Konsumen *</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 text-sm font-bold text-slate-800">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor WhatsApp / Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                </div>

                @if(auth()->user()->isOwner())
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tugaskan ke Agen Lapangan</label>
                        <select name="agent_id" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ $customer->agent_id == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                <textarea name="address" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

