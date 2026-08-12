@extends('layouts.app')

@section('title', 'Booking Approvals - SBA Admin')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Booking Approvals
        </h1>
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

        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <ul class="list-disc pl-5 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Room</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date & Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Meeting Details</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium">{{ $booking->user->name }}</div>
                                <div class="text-xs text-[#475569]">{{ $booking->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-[#2563EB]">{{ $booking->room->name }}</div>
                                <div class="text-xs text-[#475569]">{{ $booking->room->location }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium">{{ $booking->booking_date->format('d M Y') }}</div>
                                <div class="text-xs text-[#475569]">
                                    {{ date('H:i', strtotime($booking->start_time)) }} - {{ date('H:i', strtotime($booking->end_time)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold">{{ $booking->title }}</div>
                                @if ($booking->description)
                                    <div class="text-xs text-[#475569] mt-0.5 line-clamp-2 max-w-xs">{{ $booking->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                <div class="flex items-center justify-end gap-x-4">
                                    <a href="{{ route('bookings.show', $booking) }}" class="text-[#2563EB] hover:text-[#1D4ED8] text-sm">
                                        Review Detail
                                    </a>

                                    <form action="{{ route('admin.approvals.approve', $booking) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to approve this booking request?');">
                                        @csrf
                                        <button type="submit" class="rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-500">
                                            Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.approvals.reject', $booking) }}" method="POST" class="inline flex items-center gap-x-2"
                                        onsubmit="return confirm('Are you sure you want to reject this booking request?');">
                                        @csrf
                                        <input type="text" name="rejection_reason" required placeholder="Reason to reject"
                                            class="rounded border border-[#E5E7EB] px-2 py-1 text-xs text-[#0F172A] placeholder-[#475569] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] w-40">
                                        <button type="submit" class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-500">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-[#475569] italic bg-white">
                                No pending bookings require approval.
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
