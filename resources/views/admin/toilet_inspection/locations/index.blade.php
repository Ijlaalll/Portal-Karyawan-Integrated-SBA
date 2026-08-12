@extends('layouts.app')

@section('title', 'Manage Locations - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Toilet Locations
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Manage buildings, floors, areas, and toilet facilities.
            </p>
        </div>
        <a href="{{ route('admin.toilet_inspection.locations.create') }}" class="rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
            + New Location
        </a>
    </header>

    @if (session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Building</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Floor</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Area</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Toilet Name</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Gender</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($locations as $loc)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $loc->building }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->floor }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->area }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->toilet_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded bg-gray-50 px-2 py-0.5 text-xs font-medium text-[#475569] border border-gray-200">
                                {{ ucfirst($loc->gender) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($loc->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold flex items-center gap-3">
                            <a href="{{ route('admin.toilet_inspection.locations.edit', $loc) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">Edit</a>
                            <form action="{{ route('admin.toilet_inspection.locations.destroy', $loc) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this location? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#64748B]">No locations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($locations->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
