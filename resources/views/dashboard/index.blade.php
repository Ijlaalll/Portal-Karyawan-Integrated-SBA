@extends('layouts.app')

@section('title', 'Dashboard - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-y-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Welcome back, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-[#475569] mt-1">Here is a summary of your room reservations.</p>
        </div>
        <div class="flex gap-x-3">
            <a href="{{ route('calendar') }}" class="rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">
                View Calendar
            </a>
            <a href="{{ route('bookings.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                Book a Room
            </a>
        </div>
    </header>

    <main>
        <!-- Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg">
                <span class="text-xs font-semibold text-[#475569] uppercase tracking-wider block">Total Bookings</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['total'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-yellow-500">
                <span class="text-xs font-semibold text-yellow-800 uppercase tracking-wider block">Pending</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['pending'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-green-500">
                <span class="text-xs font-semibold text-green-800 uppercase tracking-wider block">Approved</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['approved'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-red-500">
                <span class="text-xs font-semibold text-red-800 uppercase tracking-wider block">Rejected</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['rejected'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-gray-400">
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider block">Cancelled</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['cancelled'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-blue-500">
                <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider block">Completed</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['completed'] }}</span>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-[#E5E7EB] bg-[#F9FAFB] flex justify-between items-center">
                <h3 class="text-base font-bold text-[#0F172A]">Recent Bookings</h3>
                <a href="{{ route('bookings.index') }}" class="text-xs font-semibold text-[#2563EB] hover:text-[#1D4ED8]">View All</a>
            </div>
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
                    @forelse ($recentBookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $booking->room->name }}</td>
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
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $colorClass }} ring-1 ring-inset">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[#475569] italic bg-white">
                                No recent booking activities.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection
