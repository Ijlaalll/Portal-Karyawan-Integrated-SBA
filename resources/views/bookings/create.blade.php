@extends('layouts.app')

@section('title', 'Book a Room - SBA')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Book a Room
        </h1>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('bookings.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Back to {{ auth()->user()->isAdmin() ? 'Dashboard' : 'My Bookings' }}
        </a>
    </header>

    <main>
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
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
            <form action="{{ route('bookings.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="room_id" class="block text-sm font-medium text-[#0F172A]">Select Room</label>
                        <select name="room_id" id="room_id" required
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            <option value="" disabled {{ !$selectedRoomId ? 'selected' : '' }}>-- Choose a meeting room --</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id', $selectedRoomId) == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }} (Capacity: {{ $room->capacity }} pax, Location: {{ $room->location }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-[#0F172A]">Meeting Title</label>
                        <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="e.g. Project Sync, Strategy Meeting"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-[#0F172A]">Description / Purpose</label>
                        <textarea name="description" id="description" rows="3" placeholder="Brief description of the meeting agenda"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="booking_date" class="block text-sm font-medium text-[#0F172A]">Date</label>
                            <input type="date" name="booking_date" id="booking_date" required value="{{ old('booking_date', $date ?: date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                                class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-medium text-[#0F172A]">Start Time</label>
                            <input type="time" name="start_time" id="start_time" required value="{{ old('start_time', $startTime ?: '09:00') }}"
                                class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-[#0F172A]">End Time</label>
                            <input type="time" name="end_time" id="end_time" required value="{{ old('end_time', $endTime ?: '10:00') }}"
                                class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('bookings.index') }}" class="rounded bg-white px-3 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Cancel</a>
                    <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                        Confirm Reservation
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
