@extends('layouts.app')

@section('title', 'Toilet Inspections Log - SBA Admin')

@section('content')
<div class="min-h-full">
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Toilet Inspections
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                View all submitted, approved, and rejected toilet inspections.
            </p>
        </div>
    </header>

    <!-- Filters -->
    <form action="{{ route('admin.toilet_inspection.inspections.index') }}" method="GET" class="flex flex-wrap gap-4 mb-6">
        <select name="status" onchange="this.form.submit()"
            class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            <option value="">All Statuses</option>
            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>

        <select name="location_id" onchange="this.form.submit()"
            class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->building }}, {{ $loc->floor }} ({{ $loc->toilet_name }})
                </option>
            @endforeach
        </select>

        @if(request()->filled('status') || request()->filled('location_id'))
            <a href="{{ route('admin.toilet_inspection.inspections.index') }}" class="text-sm text-[#475569] hover:text-[#0F172A] font-medium underline flex items-center">
                Reset Filters
            </a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
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
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($inspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
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
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspected_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                            <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-[#64748B]">No inspections found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($inspections->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $inspections->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
