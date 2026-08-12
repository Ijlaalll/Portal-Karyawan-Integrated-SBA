@extends('layouts.app')

@section('title', 'Pending Reviews - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Pending Reviews
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Review and approve or reject submitted toilet inspections.
        </p>
    </header>

    <!-- Table -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Location</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspector</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($pendingInspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            {{ $ins->location->building }}, {{ $ins->location->floor }} ({{ $ins->location->toilet_name }} - {{ ucfirst($ins->location->gender) }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspected_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                            <a href="{{ route('toilet_inspection.review', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">All clean! No pending reviews found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($pendingInspections->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $pendingInspections->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
