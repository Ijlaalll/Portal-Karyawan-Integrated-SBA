@extends('layouts.app')

@section('title', 'Edit Supir - SBA System')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Edit Supir</h1>
            <p class="text-sm text-[#64748B] mt-1">Perbarui data pengemudi {{ $driver->name }}.</p>
        </div>
        <a href="{{ route('vehicles.drivers.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-semibold text-[#334155] shadow-xs hover:bg-[#F8FAFC] transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200">
            <div class="flex items-center gap-2 text-sm font-semibold text-rose-800 mb-1">
                <span class="material-symbols-outlined text-[20px]">error</span>
                Terdapat kesalahan pengisian formulir:
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
        <form action="{{ route('vehicles.drivers.update', $driver) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-[#334155]">Nama Lengkap Supir <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $driver->name) }}" required class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-semibold text-[#334155]">Nomor Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $driver->phone_number) }}" required class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="user_id" class="block text-sm font-semibold text-[#334155]">Tautkan Akun Karyawan <span class="text-xs text-[#94A3B8]">(Opsional)</span></label>
                    <select name="user_id" id="user_id" class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">-- Tidak ditautkan (Supir non-login) --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id', $driver->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-[#334155]">Status <span class="text-rose-500">*</span></label>
                    <select name="status" id="status" required class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="available" {{ old('status', $driver->status) === 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="inactive" {{ old('status', $driver->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-[#334155]">Catatan / Keterangan</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">{{ old('notes', $driver->notes) }}</textarea>
            </div>

            <div class="pt-3 border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                <a href="{{ route('vehicles.drivers.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-[#64748B] hover:bg-slate-100 transition-colors">Batal</a>
                <button type="submit" class="rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
