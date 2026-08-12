@extends('layouts.app')

@section('title', 'Edit Location - SBA')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Edit Toilet Location
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Update location ID: #{{ $location->id }} details.
        </p>
    </header>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="text-sm font-semibold text-red-800">Please correct the following errors:</div>
            <ul class="mt-2 list-disc list-inside text-xs text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.toilet_inspection.locations.update', $location) }}" method="POST" class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label for="building" class="block text-sm font-medium text-[#0F172A]">Building / Gedung</label>
                <input type="text" name="building" id="building" value="{{ old('building', $location->building) }}" required placeholder="e.g. Gedung A"
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>

            <div class="space-y-1.5">
                <label for="floor" class="block text-sm font-medium text-[#0F172A]">Floor / Lantai</label>
                <input type="text" name="floor" id="floor" value="{{ old('floor', $location->floor) }}" required placeholder="e.g. Lantai 1"
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="area" class="block text-sm font-medium text-[#0F172A]">Area / Location Description</label>
            <input type="text" name="area" id="area" value="{{ old('area', $location->area) }}" required placeholder="e.g. Area Office Divisi Finance"
                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label for="toilet_name" class="block text-sm font-medium text-[#0F172A]">Toilet Name</label>
                <input type="text" name="toilet_name" id="toilet_name" value="{{ old('toilet_name', $location->toilet_name) }}" required placeholder="e.g. Toilet Utama"
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>

            <div class="space-y-1.5">
                <label for="gender" class="block text-sm font-medium text-[#0F172A]">Gender Category</label>
                <select name="gender" id="gender" required
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="pria" {{ old('gender', $location->gender) == 'pria' ? 'selected' : '' }}>Pria / Male</option>
                    <option value="wanita" {{ old('gender', $location->gender) == 'wanita' ? 'selected' : '' }}>Wanita / Female</option>
                    <option value="unisex" {{ old('gender', $location->gender) == 'unisex' ? 'selected' : '' }}>Unisex</option>
                </select>
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="is_active" class="block text-sm font-medium text-[#0F172A]">Location Status</label>
            <select name="is_active" id="is_active" required
                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                <option value="1" {{ old('is_active', $location->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active (Available for inspections)</option>
                <option value="0" {{ old('is_active', $location->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive (Hidden from inspectors)</option>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
            <a href="{{ route('admin.toilet_inspection.locations.index') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
                Cancel
            </a>
            <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                Update Location
            </button>
        </div>
    </form>
</div>
@endsection
