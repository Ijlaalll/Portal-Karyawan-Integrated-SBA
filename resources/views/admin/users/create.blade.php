@extends('layouts.app')

@section('title', 'Create User - SBA Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Create User
        </h1>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Back to Users
        </a>
    </header>
    
    <main>
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg p-6 sm:p-8 shadow-sm">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#0F172A]">Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="e.g. Andi Wijaya"
                            class="mt-1 block w-full rounded-md border @error('name') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600" id="name-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-[#0F172A]">Email</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="e.g. andi@example.com"
                            class="mt-1 block w-full rounded-md border @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-[#0F172A]">Password</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full rounded-md border @error('password') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-[#0F172A]">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-[#0F172A]">Role</label>
                        <select name="role" id="role" required
                            class="mt-1 block w-full rounded-md border @error('role') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                            <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="spv" {{ old('role') === 'spv' ? 'selected' : '' }}>SPV</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600" id="role-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="management-access-container">
                        <span class="block text-sm font-medium text-[#0F172A] mb-3">Management Access</span>
                        <div class="space-y-3">
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_meeting_room" name="modules[]" type="checkbox" value="meeting_room"
                                        {{ is_array(old('modules', ['meeting_room'])) && in_array('meeting_room', old('modules', ['meeting_room'])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_meeting_room" class="font-medium text-[#0F172A]">Meeting Room</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_toilet_inspection" name="modules[]" type="checkbox" value="toilet_inspection"
                                        {{ is_array(old('modules')) && in_array('toilet_inspection', old('modules')) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_toilet_inspection" class="font-medium text-[#0F172A]">Toilet Inspection</label>
                                </div>
                            </div>
                        </div>
                        @error('modules')
                            <p class="mt-1 text-sm text-red-600" id="modules-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="admin-management-info" class="hidden">
                        <span class="block text-sm font-medium text-[#0F172A] mb-1">Management Access</span>
                        <p class="text-sm text-[#475569] bg-[#F8FAFC] border border-[#E2E8F0] rounded-md px-3 py-2">
                            All Management (automatically assigned for Admin)
                        </p>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                    <a href="{{ route('admin.users.index') }}" class="rounded bg-white px-3 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Cancel</a>
                    <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const managementContainer = document.getElementById('management-access-container');
        const adminInfo = document.getElementById('admin-management-info');

        function toggleManagementAccess() {
            if (roleSelect.value === 'admin') {
                managementContainer.style.display = 'none';
                if (adminInfo) adminInfo.classList.remove('hidden');
            } else {
                managementContainer.style.display = 'block';
                if (adminInfo) adminInfo.classList.add('hidden');
            }
        }

        roleSelect.addEventListener('change', toggleManagementAccess);
        toggleManagementAccess(); // Run on load
    });
</script>
@endsection
