@extends('layouts.app')

@section('title', 'Item Checklist Baru - SBA')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Tambah Pertanyaan Checklist
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Buat parameter checklist baru untuk formulir inspeksi toilet.
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

    <form action="{{ route('admin.toilet_inspection.checklist-items.store') }}" method="POST" class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="name" class="block text-sm font-medium text-[#0F172A]">Nama Pertanyaan / Item</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="misal: Tempat Sampah"
                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
        </div>

        <div class="space-y-1.5">
            <label for="description" class="block text-sm font-medium text-[#0F172A]">Deskripsi</label>
            <textarea name="description" id="description" rows="3" placeholder="Jelaskan bagaimana petugas harus memeriksa item ini..."
                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">{{ old('description') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
            <a href="{{ route('admin.toilet_inspection.checklist-items.index') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
                Batal
            </a>
            <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                Simpan Pertanyaan
            </button>
        </div>
    </form>
</div>
@endsection
