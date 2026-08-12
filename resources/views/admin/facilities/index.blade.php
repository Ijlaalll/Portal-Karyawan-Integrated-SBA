@extends('layouts.app')

@section('title', 'Facilities Management - SBA Admin')

@section('content')
<div class="min-h-full">
    <header class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Facilities Management
        </h1>
        <a href="{{ route('admin.facilities.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
            Create Facility
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

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Facility Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Description</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($facilities as $facility)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $facility->name }}</td>
                            <td class="px-6 py-4 text-[#475569]">{{ $facility->description ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end gap-x-3">
                                <a href="{{ route('admin.facilities.edit', $facility) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">Edit</a>
                                
                                <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this facility? Removing this will also unassign it from all rooms.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-[#475569] italic bg-white">
                                No facilities created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if ($facilities->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $facilities->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
