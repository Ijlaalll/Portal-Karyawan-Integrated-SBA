@extends('layouts.app')

@section('title', 'Inspeksi Toilet Baru - SBA')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Inspeksi Toilet Baru
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Lengkapi checklist inspeksi dan unggah bukti.
        </p>
    </header>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="text-sm font-semibold text-red-800">Harap perbaiki kesalahan berikut:</div>
            <ul class="mt-2 list-disc list-inside text-xs text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inspections.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="this.querySelector('button[type=submit]').disabled = true; return true;">
        @csrf

        <!-- Location Selection -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A]">1. Detail Lokasi</h2>
            <div>
                <label for="location_id" class="block text-sm font-medium text-[#0F172A] mb-1.5">Pilih Lokasi Toilet</label>
                <select name="location_id" id="location_id" required
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="" disabled selected>-- Pilih Lokasi --</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->building }}, {{ $loc->floor }} — Toilet {{ $loc->gender === 'pria' ? 'Pria' : ($loc->gender === 'wanita' ? 'Wanita' : 'Unisex') }} ({{ $loc->toilet_name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Checklist Items -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-[#0F172A]">2. Checklist Inspeksi</h2>
            <div class="divide-y divide-[#E5E7EB]">
                @foreach ($checklistItems as $item)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-0.5 flex-1">
                            <label class="text-sm font-semibold text-[#0F172A]">{{ $item->name }}</label>
                            @if ($item->description)
                                <p class="text-xs text-[#64748B]">{{ $item->description }}</p>
                            @endif
                        </div>

                        <!-- Status Toggles -->
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center text-sm font-medium text-[#0F172A] cursor-pointer">
                                    <input type="radio" name="results[{{ $item->id }}][is_ok]" value="1" 
                                        {{ old("results.{$item->id}.is_ok", '1') == '1' ? 'checked' : '' }}
                                        class="h-4 w-4 border-[#E5E7EB] text-[#2563EB] focus:ring-[#2563EB]">
                                    <span class="ml-2 text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-200">✓ OK</span>
                                </label>
                                <label class="inline-flex items-center text-sm font-medium text-[#0F172A] cursor-pointer">
                                    <input type="radio" name="results[{{ $item->id }}][is_ok]" value="0"
                                        {{ old("results.{$item->id}.is_ok") == '0' ? 'checked' : '' }}
                                        class="h-4 w-4 border-[#E5E7EB] text-[#2563EB] focus:ring-[#2563EB]">
                                    <span class="ml-2 text-xs font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200">✗ Tidak OK</span>
                                </label>
                            </div>
                            
                            <input type="text" name="results[{{ $item->id }}][remarks]" 
                                value="{{ old("results.{$item->id}.remarks") }}"
                                placeholder="Keterangan (opsional)"
                                class="rounded-md border border-[#E5E7EB] px-3 py-1 text-xs text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] w-full md:w-48">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Evidence & Notes -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A]">3. Detail Verifikasi</h2>
            
            <div class="space-y-1.5">
                <label for="photo" class="block text-sm font-medium text-[#0F172A]">Unggah Foto Bukti (Wajib)</label>
                <input type="file" name="photo" id="photo" accept="image/png, image/jpeg, image/jpg" required
                    class="block w-full text-sm text-[#475569] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#EFF6FF] file:text-[#2563EB] hover:file:bg-[#DBEAFE] file:cursor-pointer">
                <p class="text-xs text-[#94A3B8]">Format file yang didukung: PNG, JPG, JPEG (Ukuran maks: 2MB)</p>
            </div>

            <div class="space-y-1.5 pt-2">
                <label for="notes" class="block text-sm font-medium text-[#0F172A]">Catatan Tambahan</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Masukkan catatan umum atau komentar di sini..."
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('toilet_inspection.dashboard') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
                Batal
            </a>
            <button type="submit" class="rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                Kirim Inspeksi
            </button>
        </div>
    </form>
</div>
@endsection
