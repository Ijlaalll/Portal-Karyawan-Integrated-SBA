@extends('layouts.app')

@section('title', 'My Bookings - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            My Bookings
        </h1>
        <a href="{{ route('bookings.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2">
            Book a Room
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Room</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-[#2563EB]">{{ $booking->room->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $booking->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $booking->booking_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ date('H:i', strtotime($booking->start_time)) }} - {{ date('H:i', strtotime($booking->end_time)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        'cancelled' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                                        'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    ];
                                    $colorClass = $statusColors[$booking->status] ?? 'bg-gray-50 text-gray-700';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $colorClass }} ring-1 ring-inset">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#475569] italic bg-white">
                                You have not made any booking requests.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($bookings->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
