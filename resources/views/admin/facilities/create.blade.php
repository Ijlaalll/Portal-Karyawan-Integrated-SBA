@extends('layouts.app')

@section('title', 'Create Facility - SBA Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Create Facility
        </h1>
        <a href="{{ route('admin.facilities.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Back to Facilities
        </a>
    </header>
    
    <main>
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errors in form submission:</h3>
                        <ul class="list-disc pl-5 mt-2 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg p-6 sm:p-8 shadow-sm">
            <form action="{{ route('admin.facilities.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#0F172A]">Facility Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="e.g. Projector, Whiteboard"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-[#0F172A]">Description</label>
                        <textarea name="description" id="description" rows="3" placeholder="Brief description of the facility"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                    <a href="{{ route('admin.facilities.index') }}" class="rounded bg-white px-3 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Cancel</a>
                    <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                        Create Facility
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
