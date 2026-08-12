@extends('layouts.app')

@section('title', 'Rooms Management - SBA Admin')

@section('content')
<div class="min-h-full">
    <header class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Rooms Management
        </h1>
        <a href="{{ route('admin.rooms.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
            Create Room
        </a>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Capacity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Facilities</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($rooms as $room)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $room->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $room->location }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $room->capacity }} pax</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $room->isActive() ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20' }}">
                                    {{ ucfirst($room->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($room->facilities as $fac)
                                        <span class="inline-flex items-center rounded bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                            {{ $fac->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-[#475569] italic">No facilities</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.rooms.show', $room) }}" class="text-[#2563EB] hover:text-[#1D4ED8] mr-4">View</a>
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[#475569] italic bg-white">
                                No rooms created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if ($rooms->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $rooms->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
