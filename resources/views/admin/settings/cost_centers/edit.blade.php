@extends('layouts.app')

@section('title', 'Edit Cost Center - Admin SBA')

@section('content')
<div class="max-w-xl mx-auto py-8">
    <header class="mb-8">
        <a href="{{ route('admin.settings.cost-centers.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8] inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Daftar
        </a>
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A] mt-2">
            Edit Cost Center
        </h1>
    </header>

    <main class="bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-xs">
        <form action="{{ route('admin.settings.cost-centers.update', $costCenter) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Cost Center Code -->
            <div>
                <label for="cost_center_code" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-2">Kode Cost Center</label>
                <input type="text" name="cost_center_code" id="cost_center_code" value="{{ old('cost_center_code', $costCenter->cost_center_code) }}" placeholder="Contoh: 10501" required
                    class="block w-full rounded-md border @error('cost_center_code') border-red-300 @else border-[#E5E7EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                @error('cost_center_code')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-2">Nama Unit / Departemen</label>
                <input type="text" name="name" id="name" value="{{ old('name', $costCenter->name) }}" placeholder="Contoh: IT Department"
                    class="block w-full rounded-md border @error('name') border-red-300 @else border-[#E5E7EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                @error('name')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-2">Deskripsi Lengkap</label>
                <input type="text" name="description" id="description" value="{{ old('description', $costCenter->description) }}" placeholder="Contoh: SBA PME Limestone Mine Linkage" required
                    class="block w-full rounded-md border @error('description') border-red-300 @else border-[#E5E7EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                @error('description')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Person Responsible -->
            <div>
                <label for="person_responsible" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-2">Penanggung Jawab (PJ)</label>
                <input type="text" name="person_responsible" id="person_responsible" value="{{ old('person_responsible', $costCenter->person_responsible) }}" placeholder="Contoh: ADI HANDARBENI" required
                    class="block w-full rounded-md border @error('person_responsible') border-red-300 @else border-[#E5E7EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                @error('person_responsible')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Company Code -->
            <div>
                <label for="company_code" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-2">Company Code</label>
                <input type="text" name="company_code" id="company_code" value="{{ old('company_code', $costCenter->company_code) }}" placeholder="Contoh: ID50" required
                    class="block w-full rounded-md border @error('company_code') border-red-300 @else border-[#E5E7EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                @error('company_code')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-2">Status</label>
                <select name="status" id="status" required
                    class="block w-full rounded-md border @error('status') border-red-300 @else border-[#E5E7EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    <option value="active" {{ old('status', $costCenter->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $costCenter->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('admin.settings.cost-centers.index') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Batal</a>
                <button type="submit" class="rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] cursor-pointer">Perbarui</button>
            </div>
        </form>
    </main>
</div>
@endsection
