@extends('layouts.app')

@section('title', 'Edit Room - SBA Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Edit Room: {{ $room->name }}
        </h1>
        <a href="{{ route('admin.rooms.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Back to Rooms
        </a>
    </header>
    
    <main>
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errors in form submission:</h3>
                        <ul class="list-disc pl-5 mt-2 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg p-6 sm:p-8 shadow-sm">
            <form action="{{ route('admin.rooms.update', $room) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#0F172A]">Room Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $room->name) }}"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-[#0F172A]">Location</label>
                        <input type="text" name="location" id="location" required value="{{ old('location', $room->location) }}"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-[#0F172A]">Capacity (pax)</label>
                        <input type="number" name="capacity" id="capacity" required min="1" value="{{ old('capacity', $room->capacity) }}"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-[#0F172A]">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">{{ old('description', $room->description) }}</textarea>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-[#0F172A]">Status</label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            <option value="active" {{ old('status', $room->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $room->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-[#0F172A] mb-3">Room Facilities</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @php
                                $selectedFacilities = is_array(old('facilities')) 
                                    ? old('facilities') 
                                    : $room->facilities->pluck('id')->toArray();
                            @endphp
                            @forelse ($facilities as $fac)
                                <div class="relative flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="facility_{{ $fac->id }}" name="facilities[]" type="checkbox" value="{{ $fac->id }}"
                                            {{ in_array($fac->id, $selectedFacilities) ? 'checked' : '' }}
                                            class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="facility_{{ $fac->id }}" class="font-medium text-[#0F172A]">{{ $fac->name }}</label>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-[#475569] italic col-span-full">No facilities available in system. Create facilities first.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                    <a href="{{ route('admin.rooms.index') }}" class="rounded bg-white px-3 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Cancel</a>
                    <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                        Update Room
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
