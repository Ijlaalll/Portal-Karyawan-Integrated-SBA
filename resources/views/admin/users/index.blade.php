@extends('layouts.app')

@section('title', 'Users Management - SBA Admin')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Users Management
            </h1>
            <a href="{{ route('admin.users.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                + Add User
            </a>
        </div>
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

        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Search and Filters -->
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- Search bar -->
            <div class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
                    class="block w-full rounded-md border border-[#E5E7EB] pl-10 pr-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                </div>
            </div>

            <!-- Dropdowns & Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <select name="management" onchange="this.form.submit()"
                    class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="all" {{ request('management') === 'all' ? 'selected' : '' }}>All Management</option>
                    <option value="meeting_room" {{ request('management') === 'meeting_room' ? 'selected' : '' }}>Meeting Room</option>
                    <option value="toilet_inspection" {{ request('management') === 'toilet_inspection' ? 'selected' : '' }}>Toilet Inspection</option>
                </select>

                <select name="role" onchange="this.form.submit()"
                    class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="spv" {{ request('role') === 'spv' ? 'selected' : '' }}>SPV</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                @if(request()->filled('search') || (request()->filled('management') && request('management') !== 'all') || (request()->filled('role') && request('role') !== 'all') || (request()->filled('status') && request('status') !== 'all'))
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-[#475569] hover:text-[#0F172A] font-medium underline transition-all">
                        Reset Filters
                    </a>
                @endif
                
                <button type="submit" class="hidden">Search</button>
            </div>
        </form>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">group</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $totalUsers }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">meeting_room</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider">Meeting Room</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $meetingRoomCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">wc</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider">Toilet Inspect.</p>
                    <p class="text-2xl font-bold text-[#0F172A] mt-0.5">{{ $toiletInspectionCount }}</p>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Management</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Joined At</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="px-6 py-4.5 whitespace-nowrap font-semibold text-[#0F172A]">{{ $user->name }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-[#475569]">{{ $user->email }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @php
                                    $roleClasses = [
                                        'admin' => 'bg-[#EFF6FF] text-[#2563EB] ring-[#2563EB]/20',
                                        'spv' => 'bg-[#FAF5FF] text-[#9333EA] ring-[#9333EA]/20',
                                        'user' => 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]',
                                    ];
                                    $class = $roleClasses[$user->role] ?? 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $class }} ring-1 ring-inset">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    @if ($user->isAdmin())
                                        <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2.5 py-1 text-xs font-medium text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20">
                                            All Management
                                        </span>
                                    @else
                                        @forelse ($user->modules as $mod)
                                            @php
                                                $modNames = [
                                                    'meeting_room' => 'Meeting Room',
                                                    'toilet_inspection' => 'Toilet Inspection',
                                                ];
                                                $modName = $modNames[$mod->module] ?? ucfirst(str_replace('_', ' ', $mod->module));
                                                
                                                $modClasses = [
                                                    'meeting_room' => 'bg-[#EFF6FF] text-[#2563EB] ring-[#2563EB]/20',
                                                    'toilet_inspection' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                                ];
                                                $modClass = $modClasses[$mod->module] ?? 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]';
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $modClass }} ring-1 ring-inset">
                                                {{ $modName }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-[#94A3B8] italic">No access</span>
                                        @endforelse
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @if ($user->status === 'active')
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-[#475569]">
                                {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-sm font-semibold flex items-center gap-3">
                                 <a href="{{ route('admin.users.show', $user) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">View</a>
                                 <a href="{{ route('admin.users.edit', $user) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">Edit</a>
                                 
                                 @php
                                     $hasHistory = $user->hasHistory();
                                 @endphp
                                 
                                 @if (!$hasHistory)
                                     <button type="button" 
                                         onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.destroy', $user) }}', 'delete')" 
                                         class="text-red-600 hover:text-red-900 font-semibold focus:outline-none cursor-pointer">
                                         Delete
                                     </button>
                                 @else
                                     @if ($user->status === 'active')
                                         <button type="button" 
                                             onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.deactivate', $user) }}', 'deactivate')" 
                                             class="text-red-600 hover:text-red-900 font-semibold focus:outline-none cursor-pointer">
                                             Deactivate
                                         </button>
                                     @else
                                         <button type="button" 
                                             onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.activate', $user) }}', 'activate')" 
                                             class="text-green-600 hover:text-green-900 font-semibold focus:outline-none cursor-pointer">
                                             Activate
                                         </button>
                                     @endif
                                 @endif
                             </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-[#475569] bg-white">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="material-symbols-outlined text-4xl text-[#94A3B8]">group_off</span>
                                    <p class="font-medium text-[#475569]">No users found.</p>
                                    <p class="text-xs text-[#94A3B8]">Try adding a new user or adjusting your search filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </main>
</div>

<!-- Dynamic Status Confirmation Modal -->
<div id="status-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-[#0F172A]/40 transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div class="sm:flex sm:items-start">
                <div id="modal-icon-container" class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <span id="modal-icon" class="material-symbols-outlined text-[24px]">warning</span>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-[#0F172A]" id="modal-title">Action Title</h3>
                    <div class="mt-2">
                        <p class="text-sm text-[#475569]" id="modal-text">
                            Action description.
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                <form id="modal-form" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="_method" id="modal-method" value="POST">
                    <button type="submit" id="modal-submit-btn" class="inline-flex w-full justify-center rounded px-4 py-2 text-sm font-semibold text-white shadow-sm sm:w-auto transition-colors">
                        Submit Action
                    </button>
                </form>
                <button type="button" onclick="closeStatusModal()" class="mt-3 inline-flex w-full justify-center rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB] sm:mt-0 sm:w-auto transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openStatusModal(userId, userName, actionUrl, actionType) {
        const modal = document.getElementById('status-modal');
        const title = document.getElementById('modal-title');
        const text = document.getElementById('modal-text');
        const form = document.getElementById('modal-form');
        const methodInput = document.getElementById('modal-method');
        const submitBtn = document.getElementById('modal-submit-btn');
        const iconContainer = document.getElementById('modal-icon-container');
        const icon = document.getElementById('modal-icon');

        form.action = actionUrl;

        if (actionType === 'delete') {
            title.textContent = 'Delete User';
            iconContainer.className = 'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'material-symbols-outlined text-red-600 text-[24px]';
            icon.textContent = 'warning';
            text.innerHTML = 'Are you sure you want to delete <span class="font-bold text-[#0F172A]">' + userName + '</span> permanently? This action is only available because this user has no historical records. This action cannot be undone.';
            methodInput.value = 'DELETE';
            submitBtn.textContent = 'Delete User';
            submitBtn.className = 'inline-flex w-full justify-center rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors';
        } else if (actionType === 'deactivate') {
            title.textContent = 'Deactivate User';
            iconContainer.className = 'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-50 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'material-symbols-outlined text-amber-600 text-[24px]';
            icon.textContent = 'block';
            text.innerHTML = 'Are you sure you want to deactivate <span class="font-bold text-[#0F172A]">' + userName + '</span>? This will prevent the user from logging in, but their historical records will be preserved.';
            methodInput.value = 'POST';
            submitBtn.textContent = 'Deactivate';
            submitBtn.className = 'inline-flex w-full justify-center rounded bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-505 sm:w-auto transition-colors';
        } else if (actionType === 'activate') {
            title.textContent = 'Activate User';
            iconContainer.className = 'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-50 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'material-symbols-outlined text-green-600 text-[24px]';
            icon.textContent = 'check_circle';
            text.innerHTML = 'Are you sure you want to activate <span class="font-bold text-[#0F172A]">' + userName + '</span>? This will restore the user\'s access to the application.';
            methodInput.value = 'POST';
            submitBtn.textContent = 'Activate';
            submitBtn.className = 'inline-flex w-full justify-center rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:w-auto transition-colors';
        }

        modal.classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('status-modal').classList.add('hidden');
    }
</script>
@endsection
