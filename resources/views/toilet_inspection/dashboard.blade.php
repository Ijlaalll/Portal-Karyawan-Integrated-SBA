@extends('layouts.app')

@section('title', 'Toilet Inspection Dashboard - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Toilet Inspection Dashboard
                </h1>
                <p class="text-sm text-[#475569] mt-1">
                    Welcome back, {{ auth()->user()->name }} (Role: {{ ucfirst(auth()->user()->role) }})
                </p>
            </div>
            @if(!auth()->user()->isAdmin() && !auth()->user()->isSpv())
                <a href="{{ route('inspections.create') }}" class="rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all">
                    + New Inspection
                </a>
            @endif
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200 text-sm font-medium text-green-800">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="mb-6 rounded-md bg-amber-50 p-4 border border-amber-200 text-sm font-medium text-amber-800">
                {{ session('error') }}
            </div>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isSpv())
            <!-- Period Selector Form -->
            <form method="GET" action="{{ route('toilet_inspection.dashboard') }}" class="flex flex-wrap gap-4 items-center mb-6 bg-white border border-[#E5E7EB] p-4 rounded-lg shadow-sm">
                <div>
                    <label for="date_filter" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Period</label>
                    <select name="date_filter" id="date_filter" onchange="toggleCustomDates(); this.form.submit();"
                        class="rounded-md border border-[#E5E7EB] bg-white px-3 py-1.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ request('date_filter', 'this_month') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_filter', 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>
                
                <div class="flex gap-4 {{ request('date_filter') === 'custom' ? '' : 'hidden' }}" id="custom-dates">
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                            class="rounded-md border border-[#E5E7EB] px-3 py-1 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                            class="rounded-md border border-[#E5E7EB] px-3 py-1 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    </div>
                    <div class="self-end pb-0.5">
                        <button type="submit" class="rounded bg-[#2563EB] px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all">
                            Apply
                        </button>
                    </div>
                </div>
            </form>
        @endif

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">analytics</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Inspections</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $totalInspections }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">pending_actions</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Pending Review</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $pendingCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#F0FDF4] text-[#16A34A] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">check_circle</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Approved</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $approvedCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#FEF2F2] text-[#DC2626] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">cancel</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Rejected</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $rejectedCount }}</p>
                </div>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isSpv())
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                    <div class="p-3 rounded-lg bg-[#FAF5FF] text-[#7C3AED] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">percent</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Approval Rate</p>
                        <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ isset($approvalRate) ? $approvalRate : 0 }}%</p>
                    </div>
                </div>
            @else
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                    <div class="p-3 rounded-lg bg-[#F8FAFC] text-[#64748B] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">person</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">My Role</p>
                        <p class="text-sm font-bold text-[#0F172A] mt-1">Petugas / Inspector</p>
                    </div>
                </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isSpv())
            @if($totalInspections === 0)
                <!-- Empty State Message for selected period -->
                <div class="rounded-lg border border-[#E5E7EB] bg-white p-12 text-center text-[#64748B] mb-8">
                    <span class="material-symbols-outlined text-[48px] text-[#94A3B8]">analytics</span>
                    <p class="mt-2 font-semibold">No inspections recorded for the selected period.</p>
                    <p class="text-xs text-[#94A3B8] mt-1">Try selecting a different time range or clear filters.</p>
                </div>
            @else
                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Status Chart -->
                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm flex flex-col justify-between lg:col-span-1">
                        <h3 class="text-sm font-bold text-[#0F172A] mb-4">Inspection Status</h3>
                        <div class="w-full max-w-[200px] mx-auto aspect-square relative flex items-center justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Trend Chart -->
                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm flex flex-col justify-between lg:col-span-2">
                        <h3 class="text-sm font-bold text-[#0F172A] mb-4">Inspection Trend (Daily)</h3>
                        <div class="w-full h-full min-h-[200px] relative">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Location Summary Section -->
                <div class="bg-white border border-[#E5E7EB] rounded-lg shadow-sm mb-8 overflow-hidden">
                    <div class="px-6 py-5 border-b border-[#E5E7EB]">
                        <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Location Summary</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#E5E7EB]">
                            <thead class="bg-[#F9FAFB]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Building</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Floor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspections</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Approved</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Rejected</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Approval Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                                @forelse($locationSummary as $loc)
                                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $loc->building }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->floor }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->total }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->approved }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->rejected }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                            {{ $loc->total > 0 ? round(($loc->approved / $loc->total) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-[#64748B]">No location data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        @if(auth()->user()->isAdmin())
            <!-- Admin Logs -->
            <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                <div class="px-6 py-5 border-b border-[#E5E7EB]">
                    <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Recent Inspections</h3>
                </div>
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspector</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($recentInspections as $ins)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-[#0F172A]">
                                    {{ $ins->location->building }}, {{ $ins->location->floor }} ({{ $ins->location->toilet_name }} - {{ ucfirst($ins->location->gender) }})
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-[#64748B]">No recent inspections found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif(auth()->user()->isSpv())
            <!-- SPV Approvals List -->
            <div class="space-y-6">
                <!-- Pending Reviews -->
                <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                    <div class="px-6 py-5 border-b border-[#E5E7EB]">
                        <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Pending Review</h3>
                    </div>
                    <table class="min-w-full divide-y divide-[#E5E7EB]">
                        <thead class="bg-[#F9FAFB]">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspector</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                            @forelse ($pendingInspections as $ins)
                                <tr class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-[#0F172A]">
                                        {{ $ins->location->building }}, {{ $ins->location->floor }} ({{ $ins->location->toilet_name }} - {{ ucfirst($ins->location->gender) }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                        <a href="{{ route('toilet_inspection.review', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">All clean! No pending reviews.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Inspector/User History -->
            <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                <div class="px-6 py-5 border-b border-[#E5E7EB]">
                    <h3 class="text-base font-semibold leading-6 text-[#0F172A]">My Inspections log</h3>
                </div>
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($myInspections as $ins)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-[#0F172A]">
                                    {{ $ins->location->building }}, {{ $ins->location->floor }} ({{ $ins->location->toilet_name }} - {{ ucfirst($ins->location->gender) }})
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
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">You have not submitted any inspections yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </main>
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isSpv())
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

    @if($totalInspections > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Status Chart
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                const statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending Review', 'Approved', 'Rejected'],
                        datasets: [{
                            data: [{{ $pendingCount }}, {{ $approvedCount }}, {{ $rejectedCount }}],
                            backgroundColor: ['#FBBF24', '#16A34A', '#DC2626'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    font: { size: 10, family: 'Inter' }
                                }
                            }
                        }
                    }
                });

                // Trend Chart
                @php
                    $trendLabels = array_keys($trendData);
                    $trendValues = array_values($trendData);
                @endphp
                const trendCtx = document.getElementById('trendChart').getContext('2d');
                const trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendLabels) !!},
                        datasets: [{
                            label: 'Inspections Count',
                            data: {!! json_encode($trendValues) !!},
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.05)',
                            fill: true,
                            tension: 0.25,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 10, family: 'Inter' }
                                }
                            },
                            x: {
                                ticks: {
                                    font: { size: 10, family: 'Inter' }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
@endif
@endsection
