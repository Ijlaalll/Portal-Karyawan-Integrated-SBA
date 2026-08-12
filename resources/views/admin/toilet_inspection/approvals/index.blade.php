@extends('layouts.app')

@section('title', 'Review Approvals Log - SBA Admin')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Supervisor Reviews
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Log history of supervisor approvals and rejections.
        </p>
    </header>

    <!-- Table -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspector</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Decision</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Supervisor</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Notes / Feedback</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($approvals as $app)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 font-medium">
                            {{ $app->inspection->location->building }}, {{ $app->inspection->location->floor }} ({{ $app->inspection->location->toilet_name }} - {{ ucfirst($app->inspection->location->gender) }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $app->inspection->inspector->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badges = [
                                    'approve' => 'bg-[#F0FDF4] text-[#16A34A] ring-[#16A34A]/20',
                                    'reject' => 'bg-[#FEF2F2] text-[#DC2626] ring-[#DC2626]/20',
                                ];
                                $badgeClass = $badges[$app->action] ?? 'bg-[#F8FAFC] text-[#64748B]';
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $badgeClass }} ring-1 ring-inset">
                                {{ $app->action === 'approve' ? 'Approved' : 'Rejected' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $app->approver->name }}</td>
                        <td class="px-6 py-4 text-[#475569] max-w-xs truncate" title="{{ $app->notes }}">{{ $app->notes ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $app->actioned_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                            <a href="{{ route('inspections.show', $app->inspection) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#64748B]">No supervisor decisions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($approvals->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $approvals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
