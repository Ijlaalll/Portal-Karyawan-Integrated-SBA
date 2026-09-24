@extends('layouts.app')

@section('title', 'Tambah Kategori ATK - SBA')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.categories.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Tambah Kategori ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Buat kategori baru untuk mengelompokkan barang alat tulis kantor.
            </p>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-md bg-red-50 p-4 border border-red-200 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm">
        <form action="{{ route('atk.categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name Input -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-[#0F172A]">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Kertas & Pembukuan"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
            </div>

            <!-- Description Input -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-semibold text-[#0F172A]">Deskripsi Kategori</label>
                <textarea name="description" id="description" rows="4" placeholder="Tulis deskripsi detail tentang kategori ini..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">{{ old('description') }}</textarea>
            </div>

            <!-- Status (is_active) Input -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#0F172A]">Status</label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <span class="text-sm font-medium text-slate-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}
                            class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <span class="text-sm font-medium text-slate-700">Tidak Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('atk.categories.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded text-sm font-semibold hover:bg-[#1D4ED8] transition-colors shadow-sm">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
