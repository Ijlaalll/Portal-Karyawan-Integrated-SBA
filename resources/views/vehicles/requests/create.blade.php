@extends('layouts.app')

@section('title', 'Ajukan Peminjaman Kendaraan - SBA System')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Pengajuan Peminjaman Kendaraan</h1>
            <p class="text-sm text-[#64748B] mt-1">Isi formulir permohonan kendaraan dinas operasional.</p>
        </div>
        <a href="{{ route('vehicles.requests.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-semibold text-[#334155] shadow-xs hover:bg-[#F8FAFC] transition-colors">
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
        <form action="{{ route('vehicles.requests.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="purpose" class="block text-sm font-semibold text-[#334155]">Keperluan Perjalanan Dinas <span class="text-rose-500">*</span></label>
                <textarea name="purpose" id="purpose" rows="3" required placeholder="Contoh: Kunjungan monitoring operasional pabrik dan pertemuan koordinasi vendor..." class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">{{ old('purpose') }}</textarea>
            </div>

            <div>
                <label for="destination" class="block text-sm font-semibold text-[#334155]">Lokasi Tujuan <span class="text-rose-500">*</span></label>
                <input type="text" name="destination" id="destination" value="{{ old('destination') }}" required placeholder="Contoh: Banda Aceh - Pabrik Lhoknga" class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_datetime" class="block text-sm font-semibold text-[#334155]">Jadwal Berangkat <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="start_datetime" id="start_datetime" value="{{ old('start_datetime', now()->addHour()->format('Y-m-d\TH:00')) }}" required class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label for="end_datetime" class="block text-sm font-semibold text-[#334155]">Jadwal Perkiraan Kembali <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="end_datetime" id="end_datetime" value="{{ old('end_datetime', now()->addHours(3)->format('Y-m-d\TH:00')) }}" required class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
            </div>

            <div>
                <label for="vehicle_id" class="block text-sm font-semibold text-[#334155]">
                    Preferensi Kendaraan <span class="text-xs font-normal text-[#64748B]">(Opsional)</span>
                </label>
                <p class="text-xs text-[#64748B] mt-0.5 mb-1.5">Preferensi akan menjadi pertimbangan GA dalam menentukan armada.</p>
                <select name="vehicle_id" id="vehicle_id" class="w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="">-- Tanpa Preferensi (Bebas Ditentukan oleh GA) --</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }} ({{ $v->plate_number }}) - {{ $v->fuel_type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-[#334155]">Catatan Tambahan</label>
                <textarea name="notes" id="notes" rows="2" placeholder="Informasi lain jika diperlukan..." class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-3 border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                <a href="{{ route('vehicles.requests.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-[#64748B] hover:bg-slate-100 transition-colors">Batal</a>
                <button type="submit" class="rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">Kirim Permohonan</button>
            </div>
        </form>
    </div>
</div>
@endsection
