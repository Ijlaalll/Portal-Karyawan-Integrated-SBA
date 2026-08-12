@extends('layouts.app')

@section('title', 'Booking Details - SBA')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Booking Details
        </h1>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.approvals.index') : route('bookings.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Back to {{ auth()->user()->isAdmin() ? 'Approvals' : 'My Bookings' }}
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
            <div class="px-6 py-5 border-b border-[#E5E7EB] flex justify-between items-center bg-[#F9FAFB]">
                <div>
                    <h3 class="text-lg font-bold leading-6 text-[#0F172A]">{{ $booking->title }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-[#475569]">{{ $booking->room->name }}</p>
                </div>
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
                <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-semibold {{ $colorClass }} ring-1 ring-inset">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>

            <div class="px-6 py-5">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Meeting Room</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->room->name }} ({{ $booking->room->location }})</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Booking Date</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->booking_date->format('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Schedule Time</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">
                            {{ date('H:i', strtotime($booking->start_time)) }} - {{ date('H:i', strtotime($booking->end_time)) }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Requested By</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->user->name }} ({{ $booking->user->email }})</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Requested At</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    
                    @if ($booking->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-semibold text-[#475569]">Agenda / Purpose</dt>
                            <dd class="mt-1 text-sm text-[#0F172A] whitespace-pre-line">{{ $booking->description }}</dd>
                        </div>
                    @endif
                    
                    @if ($booking->status === 'approved' && $booking->approver)
                        <div class="sm:col-span-2 border-t border-[#E5E7EB] pt-4">
                            <h4 class="text-sm font-semibold text-[#475569] mb-1">Approval Information</h4>
                            <p class="text-sm text-[#0F172A]">
                                Approved by <span class="font-medium">{{ $booking->approver->name }}</span>
                                @if ($booking->approved_at)
                                    on {{ $booking->approved_at->format('d M Y, H:i') }}
                                @endif
                            </p>
                        </div>
                    @elseif ($booking->status === 'rejected')
                        <div class="sm:col-span-2 border-t border-[#E5E7EB] pt-4">
                            <h4 class="text-sm font-semibold text-red-800 mb-1">Rejection Details</h4>
                            <p class="text-sm text-[#0F172A]"><span class="font-semibold text-[#475569]">Reason:</span> {{ $booking->rejection_reason ?: 'No reason provided.' }}</p>
                        </div>
                    @endif
                </dl>
            </div>

            @can('cancel', $booking)
                <div class="bg-[#F9FAFB] px-6 py-4 border-t border-[#E5E7EB] flex justify-end">
                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to cancel this booking request?');">
                        @csrf
                        <button type="submit" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                            Cancel Booking Request
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </main>
</div>
@endsection
