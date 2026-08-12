@extends('layouts.app')

@section('title', 'Toilet Inspection Report - SBA')

@section('content')
<style>
    @media print {
        /* Hide everything not related to the print report */
        nav, 
        header, 
        #filter-form-container, 
        .no-print, 
        .pagination-container {
            display: none !important;
        }
        
        /* Expand content to full screen */
        body {
            background-color: white !important;
            color: black !important;
            font-size: 12px !important;
        }
        
        .flex-1, main {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        
        /* Table styles for printing */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 15px !important;
        }
        
        th, td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            text-align: left !important;
        }
        
        th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 600 !important;
        }
        
        /* Stats cards print styles */
        .stats-grid-print {
            display: grid !important;
            grid-template-cols: repeat(4, 1fr) !important;
            gap: 15px !important;
            margin-bottom: 20px !important;
        }
        
        .stat-card-print {
            border: 1px solid #cbd5e1 !important;
            padding: 10px !important;
            border-radius: 6px !important;
            background-color: #f8fafc !important;
        }
    }
</style>

<div class="min-h-full">
    <!-- Non-Print Header -->
    <header class="mb-8 flex justify-between items-center no-print">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Toilet Inspection Reporting
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Generate and print structured reports of toilet inspection activities.
            </p>
        </div>
        <div>
            <button onclick="window.print();" class="rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print Report
            </button>
        </div>
    </header>

    <!-- Print-only Title Header -->
    <div class="hidden print:block mb-6 border-b border-[#cbd5e1] pb-4">
        <h1 class="text-2xl font-bold text-black">Toilet Inspection Report</h1>
        <p class="text-xs text-gray-600 mt-1">Generated on: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
        <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
            <div>
                <strong>Period:</strong> 
                {{ request('date_filter') ? ucfirst(str_replace('_', ' ', request('date_filter'))) : 'All Periods' }}
                @if(request('date_filter') === 'custom')
                    ({{ request('start_date') }} s/d {{ request('end_date') }})
                @endif
            </div>
            <div>
                <strong>Active Filters:</strong>
                @php
                    $filters = [];
                    if(request('building')) $filters[] = "Building: " . request('building');
                    if(request('floor')) $filters[] = "Floor: " . request('floor');
                    if(request('gender')) $filters[] = "Gender: " . request('gender');
                    if(request('status')) $filters[] = "Status: " . request('status');
                @endphp
                {{ count($filters) > 0 ? implode(', ', $filters) : 'None' }}
            </div>
        </div>
    </div>

    <!-- Filters Container (no-print) -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm mb-6 space-y-4 no-print" id="filter-form-container">
        <form action="{{ route('toilet_inspection.report') }}" method="GET" class="space-y-4" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Search by inspector name, building, or area..."
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label for="date_filter" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Date Period</label>
                    <select name="date_filter" id="date_filter" onchange="toggleCustomDates(); this.form.submit();"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">All Periods</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>
            </div>

            <!-- Custom Dates Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden" id="custom-dates">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
            </div>

            <!-- Parameters Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-[#F1F5F9]">
                <div>
                    <label for="status" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" id="status" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">All Statuses</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label for="building" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Building</label>
                    <select name="building" id="building" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">All Buildings</option>
                        @foreach ($buildings as $b)
                            <option value="{{ $b }}" {{ request('building') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="floor" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Floor</label>
                    <select name="floor" id="floor" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">All Floors</option>
                        @foreach ($floors as $f)
                            <option value="{{ $f }}" {{ request('floor') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="gender" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" id="gender" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">All Genders</option>
                        <option value="pria" {{ request('gender') === 'pria' ? 'selected' : '' }}>Male</option>
                        <option value="wanita" {{ request('gender') === 'wanita' ? 'selected' : '' }}>Female</option>
                        <option value="unisex" {{ request('gender') === 'unisex' ? 'selected' : '' }}>Unisex</option>
                    </select>
                </div>
            </div>

            <!-- Inspector Filter & Action Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-2">
                <div class="w-full sm:w-72">
                    <label for="inspector_id" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Inspector</label>
                    <select name="inspector_id" id="inspector_id" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">All Inspectors</option>
                        @foreach ($inspectors as $ins)
                            <option value="{{ $ins->id }}" {{ request('inspector_id') == $ins->id ? 'selected' : '' }}>{{ $ins->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('toilet_inspection.report') }}"
                        class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#475569] shadow-sm hover:bg-[#F9FAFB] transition-colors text-center w-full sm:w-auto">
                        Clear Filter
                    </a>
                    <button type="submit"
                        class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors text-center w-full sm:w-auto">
                        Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Indicators (no-print) -->
    @if(request('search') || request('date_filter') || request('status') || request('building') || request('floor') || request('gender') || request('inspector_id'))
        <div class="flex flex-wrap gap-2 items-center mb-6 text-xs text-[#475569] no-print">
            <span class="font-bold">Active Filters:</span>
            @if(request('search'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Search: "{{ request('search') }}"</span>
            @endif
            @if(request('date_filter'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">
                    Period: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}
                    @if(request('date_filter') === 'custom')
                        ({{ request('start_date') }} s/d {{ request('end_date') }})
                    @endif
                </span>
            @endif
            @if(request('status'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Status: {{ request('status') === 'submitted' ? 'Pending Review' : ucfirst(request('status')) }}</span>
            @endif
            @if(request('building'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Building: {{ request('building') }}</span>
            @endif
            @if(request('floor'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Floor: {{ request('floor') }}</span>
            @endif
            @if(request('gender'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Gender: {{ ucfirst(request('gender')) }}</span>
            @endif
            @if(request('inspector_id'))
                @php
                    $activeInspector = $inspectors->firstWhere('id', request('inspector_id'));
                @endphp
                @if($activeInspector)
                    <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Inspector: {{ $activeInspector->name }}</span>
                @endif
            @endif
        </div>
    @endif

    <!-- Summary Metrics (Printed and Displayed) -->
    <div class="stats-grid-print grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Inspections</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $totalInspections }}</p>
        </div>
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Pending Review</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $pendingCount }}</p>
        </div>
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Approved</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $approvedCount }}</p>
        </div>
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Rejected</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $rejectedCount }}</p>
        </div>
    </div>

    <!-- Table Listing -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Time</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspector</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Gender</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Approved By</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider no-print">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($inspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->inspector->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            {{ $ins->location->building }}, Floor {{ str_replace('Floor ', '', $ins->location->floor) }} ({{ $ins->location->area }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ ucfirst($ins->location->gender) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badges = [
                                    'submitted' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                    'approved' => 'bg-[#F0FDF4] text-[#16A34A] ring-[#16A34A]/20',
                                    'rejected' => 'bg-[#FEF2F2] text-[#DC2626] ring-[#DC2626]/20',
                                    'draft' => 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]',
                                ];
                                $badgeClass = $badges[$ins->status] ?? $badges['draft'];
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $badgeClass }} ring-1 ring-inset">
                                {{ $ins->status === 'submitted' ? 'Pending Review' : ucfirst($ins->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->approval && $ins->approval->approver ? $ins->approval->approver->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold no-print">
                            <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-[#64748B]">No inspections found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Container (no-print) -->
    <div class="mt-6 no-print pagination-container">
        {{ $inspections->links() }}
    </div>
</div>

<script>
    function toggleCustomDates() {
        const select = document.getElementById('date_filter');
        const customDatesDiv = document.getElementById('custom-dates');
        if (select.value === 'custom') {
            customDatesDiv.classList.remove('hidden');
        } else {
            customDatesDiv.classList.add('hidden');
        }
    }
    
    // Run toggle on page load to restore state if customized
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomDates();
    });
</script>
@endsection
