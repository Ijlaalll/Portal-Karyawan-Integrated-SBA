@extends('layouts.app')

@section('title', 'User Details - SBA Admin')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <!-- Header -->
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                User Profile Details
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Manage and view activity log for {{ $user->name }}
            </p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
            Back to Users
        </a>
    </header>

    <!-- Profile Overview & Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Profile Information</h2>
            
            <div class="space-y-3 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Full Name</span>
                    <span class="text-[#0F172A] font-medium text-base">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Email Address</span>
                    <span class="text-[#475569]">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Role</span>
                    @php
                        $roleClasses = [
                            'admin' => 'bg-[#EFF6FF] text-[#2563EB] ring-[#2563EB]/20',
                            'spv' => 'bg-[#FAF5FF] text-[#9333EA] ring-[#9333EA]/20',
                            'user' => 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]',
                        ];
                        $class = $roleClasses[$user->role] ?? 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]';
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 mt-1 text-xs font-medium {{ $class }} ring-1 ring-inset">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Status</span>
                    @if ($user->status === 'active')
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 mt-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-0.5 mt-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Inactive</span>
                    @endif
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Joined At</span>
                    <span class="text-[#475569]">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Management Access</span>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @if ($user->isAdmin())
                            <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2.5 py-0.5 text-xs font-medium text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20">
                                All Management
                            </span>
                        @else
                            @forelse ($user->modules as $mod)
                                @php
                                    $modNames = [
                                        'meeting_room' => 'Meeting Room',
                                        'toilet_inspection' => 'Toilet Inspection',
                                    ];
                                    $modClasses = [
                                        'meeting_room' => 'bg-[#EFF6FF] text-[#2563EB] ring-[#2563EB]/20',
                                        'toilet_inspection' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                    ];
                                    $modName = $modNames[$mod->module] ?? ucfirst(str_replace('_', ' ', $mod->module));
                                    $modClass = $modClasses[$mod->module] ?? 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium {{ $modClass }} ring-1 ring-inset">
                                    {{ $modName }}
                                </span>
                            @empty
                                <span class="text-xs text-[#94A3B8] italic">No access configured</span>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Stats Cards (adapted to access) -->
        <div class="lg:col-span-2 space-y-6 flex flex-col justify-between">
            <!-- Meeting Room Stats -->
            @if ($user->isAdmin() || $user->hasModuleAccess('meeting_room'))
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-2 mb-3">
                        <span class="material-symbols-outlined text-[#2563EB]">meeting_room</span>
                        <h3 class="text-base font-bold text-[#0F172A]">
                            {{ $user->isAdmin() ? 'Meeting Room Approval Summary' : 'Meeting Room Activity Summary' }}
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-[#0F172A]">{{ $meetingRoomStats['total'] }}</p>
                            <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5">Total Bookings</p>
                        </div>
                        <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-green-700">{{ $meetingRoomStats['approved'] }}</p>
                            <p class="text-xs font-semibold text-green-600 uppercase mt-0.5">Approved</p>
                        </div>
                        <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-red-700">{{ $meetingRoomStats['rejected'] }}</p>
                            <p class="text-xs font-semibold text-red-600 uppercase mt-0.5">Rejected</p>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-amber-700">{{ $meetingRoomStats['pending'] }}</p>
                            <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5">Pending</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Toilet Inspection Stats -->
            @if ($user->isAdmin() || $user->hasModuleAccess('toilet_inspection'))
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-2 mb-3">
                        <span class="material-symbols-outlined text-[#D97706]">wc</span>
                        <h3 class="text-base font-bold text-[#0F172A]">
                            @if ($user->isAdmin())
                                Toilet Inspection Monitoring
                            @else
                                Toilet Inspection Activity Summary ({{ $user->isSpv() ? 'Supervisor' : 'Petugas' }})
                            @endif
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-[#0F172A]">{{ $toiletStats['total'] }}</p>
                            <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5">
                                @if ($user->isAdmin())
                                    Total Inspections
                                @elseif ($user->isSpv())
                                    Total Reviews
                                @else
                                    Total Inspections
                                @endif
                            </p>
                        </div>
                        <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-green-700">{{ $toiletStats['approved'] }}</p>
                            <p class="text-xs font-semibold text-green-600 uppercase mt-0.5">
                                @if ($user->isAdmin())
                                    Approved by SPV
                                @else
                                    Approved
                                @endif
                            </p>
                        </div>
                        <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-red-700">{{ $toiletStats['rejected'] }}</p>
                            <p class="text-xs font-semibold text-red-600 uppercase mt-0.5">
                                @if ($user->isAdmin())
                                    Rejected by SPV
                                @else
                                    Rejected
                                @endif
                            </p>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-center">
                            <p class="text-2xl font-bold text-amber-700">{{ $toiletStats['pending'] }}</p>
                            <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5">
                                @if ($user->isAdmin())
                                    Pending Review
                                @elseif ($user->isSpv())
                                    Pending Review
                                @else
                                    Pending
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Activity History Details -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg shadow-sm overflow-hidden">
        <!-- Tabs bar -->
        @php
            $hasMR = $user->isAdmin() || $user->hasModuleAccess('meeting_room');
            $hasTI = $user->isAdmin() || $user->hasModuleAccess('toilet_inspection');
            $showTabs = $hasMR && $hasTI;
        @endphp

        <div class="border-b border-[#E5E7EB] bg-[#F9FAFB] px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-base font-bold text-[#0F172A]">Activity History Logs</h3>
            
            @if ($showTabs)
                <div class="flex rounded-md bg-[#E2E8F0] p-1 text-xs font-semibold text-[#64748B]">
                    <button type="button" onclick="switchTab('all')" id="tab-all" class="rounded px-3 py-1.5 bg-white text-[#0F172A] shadow-sm transition-all focus:outline-none">
                        All
                    </button>
                    <button type="button" onclick="switchTab('meeting_room')" id="tab-meeting_room" class="rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none">
                        Meeting Room
                    </button>
                    <button type="button" onclick="switchTab('toilet_inspection')" id="tab-toilet_inspection" class="rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none">
                        Toilet Inspection
                    </button>
                </div>
            @endif
        </div>

        <div class="divide-y divide-[#E5E7EB]">
            <!-- MEETING ROOM HISTORY SECTION -->
            @if ($hasMR)
                <div id="section-meeting_room" class="tab-section p-6 space-y-4">
                    <h4 class="text-sm font-bold text-[#2563EB] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">meeting_room</span> 
                        {{ $user->isAdmin() ? 'Meeting Room Bookings (Monitoring)' : 'Meeting Room Bookings' }}
                    </h4>
                    
                    <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                        <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                            <thead class="bg-[#F8FAFC]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Date</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Room</th>
                                    @if ($user->isAdmin())
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Booked By</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Booking Title</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Time Slot</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                @forelse ($bookings as $booking)
                                    <tr class="hover:bg-[#F9FAFB] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                                            {{ $booking->booking_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-[#2563EB]">
                                            {{ $booking->room->name }}
                                        </td>
                                        @if ($user->isAdmin())
                                            <td class="px-6 py-4">
                                                {{ $booking->user->name }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 font-medium">{{ $booking->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                            {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $bookingBadges = [
                                                    'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                    'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    'cancelled' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                    'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                ];
                                                $bookingBadgeClass = $bookingBadges[$booking->status] ?? 'bg-slate-50 text-slate-700';
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium {{ $bookingBadgeClass }} ring-1 ring-inset">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                            No booking history logs found for this user.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if ($user->isAdmin() && $approvedBookings->isNotEmpty())
                        <div class="mt-6 space-y-3">
                            <h4 class="text-xs font-bold text-[#475569] uppercase tracking-wider">Bookings Approved by this Admin</h4>
                            <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                                <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                                    <thead class="bg-[#F8FAFC]">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Date</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Room</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Booked By</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Booking Title</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                        @foreach ($approvedBookings as $ab)
                                            <tr class="hover:bg-[#F9FAFB] transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $ab->booking_date->format('d M Y') }}</td>
                                                <td class="px-6 py-4 font-semibold">{{ $ab->room->name }}</td>
                                                <td class="px-6 py-4">{{ $ab->user->name }}</td>
                                                <td class="px-6 py-4 font-medium">{{ $ab->title }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ substr($ab->start_time, 0, 5) }} - {{ substr($ab->end_time, 0, 5) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- TOILET INSPECTION HISTORY SECTION -->
            @if ($hasTI)
                <div id="section-toilet_inspection" class="tab-section p-6 space-y-4">
                    <h4 class="text-sm font-bold text-[#D97706] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">wc</span> 
                        @if ($user->isAdmin())
                            Toilet Inspections (Monitoring)
                        @elseif ($user->isSpv())
                            Toilet Inspection Approvals
                        @else
                            Toilet Inspections Performed
                        @endif
                    </h4>
                    
                    <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                        <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                            <thead class="bg-[#F8FAFC]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Date</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Location</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Status</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Evidence</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">
                                        @if ($user->isAdmin())
                                            Inspector
                                        @elseif ($user->isSpv())
                                            Inspector
                                        @else
                                            Review / Approval
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                @if ($user->isSpv())
                                    @forelse ($approvals as $appr)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ $appr->actioned_at ? $appr->actioned_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-[#D97706]">
                                                {{ $appr->inspection->location->building }} &bull; {{ $appr->inspection->location->floor }} &bull; {{ $appr->inspection->location->toilet_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($appr->action === 'approve')
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Approved</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Rejected</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-[#2563EB]">
                                                @if ($appr->inspection->photo_path)
                                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $appr->inspection->photo_path) }}')" class="inline-flex items-center gap-1 font-semibold text-[#2563EB] hover:text-[#1D4ED8] underline cursor-pointer">
                                                        <span class="material-symbols-outlined text-[18px]">image</span> View Photo
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#94A3B8] italic">No photo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-medium">
                                                {{ $appr->inspection->inspector->name }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                                No inspection approval reviews found for this supervisor.
                                            </td>
                                        </tr>
                                    @endforelse
                                @elseif ($user->isAdmin())
                                    @forelse ($inspections as $ins)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ $ins->inspected_at ? $ins->inspected_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-[#D97706]">
                                                {{ $ins->location->building }} &bull; {{ $ins->location->floor }} &bull; {{ $ins->location->toilet_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $insBadges = [
                                                        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                        'draft' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                    ];
                                                    $insBadgeClass = $insBadges[$ins->status] ?? 'bg-slate-50 text-slate-700';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $insBadgeClass }} ring-1 ring-inset">
                                                    {{ $ins->status === 'submitted' ? 'Pending Review' : ucfirst($ins->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($ins->photo_path)
                                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $ins->photo_path) }}')" class="inline-flex items-center gap-1 font-semibold text-[#2563EB] hover:text-[#1D4ED8] underline cursor-pointer">
                                                        <span class="material-symbols-outlined text-[18px]">image</span> View Photo
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#94A3B8] italic">No photo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-medium">
                                                {{ $ins->inspector->name }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                                No inspections monitored.
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    @forelse ($inspections as $ins)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ $ins->inspected_at ? $ins->inspected_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-[#D97706]">
                                                {{ $ins->location->building }} &bull; {{ $ins->location->floor }} &bull; {{ $ins->location->toilet_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $insBadges = [
                                                        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                        'draft' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                    ];
                                                    $insBadgeClass = $insBadges[$ins->status] ?? 'bg-slate-50 text-slate-700';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $insBadgeClass }} ring-1 ring-inset">
                                                    {{ $ins->status === 'submitted' ? 'Pending Review' : ucfirst($ins->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($ins->photo_path)
                                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $ins->photo_path) }}')" class="inline-flex items-center gap-1 font-semibold text-[#2563EB] hover:text-[#1D4ED8] underline cursor-pointer">
                                                        <span class="material-symbols-outlined text-[18px]">image</span> View Photo
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#94A3B8] italic">No photo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-[#475569]">
                                                @if ($ins->approval)
                                                    <span class="font-medium text-[#0F172A]">{{ $ins->approval->action === 'approve' ? 'Approved' : 'Rejected' }}</span>
                                                    by {{ $ins->approval->approver->name }} on {{ $ins->approval->actioned_at->format('d M Y') }}
                                                @else
                                                    <span class="text-xs italic text-[#94A3B8]">Pending supervisor review</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                                No inspections performed yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Photo Evidence Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 cursor-pointer" onclick="closeLightbox()">
    <div class="max-w-4xl max-h-full relative flex flex-col justify-center items-center">
        <img id="lightbox-img" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" src="" alt="Enlarged Evidence">
        <p class="text-white text-xs mt-3 bg-black/40 px-3 py-1.5 rounded-full">Click anywhere to close</p>
    </div>
</div>

<!-- Tab and Lightbox Scripts -->
<script>
    function switchTab(moduleName) {
        // Toggle Active styles on buttons
        const tabAll = document.getElementById('tab-all');
        const tabMR = document.getElementById('tab-meeting_room');
        const tabTI = document.getElementById('tab-toilet_inspection');
        
        const secMR = document.getElementById('section-meeting_room');
        const secTI = document.getElementById('section-toilet_inspection');

        // Reset buttons to non-active styles
        [tabAll, tabMR, tabTI].forEach(btn => {
            if (btn) {
                btn.className = 'rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none';
            }
        });

        // Set active style to clicked button
        const activeBtn = document.getElementById('tab-' + moduleName);
        if (activeBtn) {
            activeBtn.className = 'rounded px-3 py-1.5 bg-white text-[#0F172A] shadow-sm transition-all focus:outline-none';
        }

        // Show/hide sections
        if (moduleName === 'all') {
            if (secMR) secMR.classList.remove('hidden');
            if (secTI) secTI.classList.remove('hidden');
        } else if (moduleName === 'meeting_room') {
            if (secMR) secMR.classList.remove('hidden');
            if (secTI) secTI.classList.add('hidden');
        } else if (moduleName === 'toilet_inspection') {
            if (secMR) secMR.classList.add('hidden');
            if (secTI) secTI.classList.remove('hidden');
        }
    }

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.getElementById('lightbox-img').src = '';
    }
</script>
@endsection
