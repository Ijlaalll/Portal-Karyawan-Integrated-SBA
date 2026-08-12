<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F9FAFB]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Meeting Room Reservation System')</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-[#0F172A]">

    @auth
        @php
            $user = auth()->user();
            $activeModule = null;
            if ($user->isAdmin()) {
                if (session()->has('active_admin_module')) {
                    $activeModule = session('active_admin_module');
                }
            } else {
                $userModules = $user->modules()->pluck('module')->toArray();
                if (count($userModules) === 1) {
                    $activeModule = $userModules[0];
                } elseif (session()->has('active_module')) {
                    $activeModule = session('active_module');
                }
            }
        @endphp

        <div class="h-screen flex overflow-hidden">
            <!-- Sidebar -->
            <nav class="w-[280px] h-screen flex flex-col fixed left-0 top-0 bg-white border-r border-[#E5E7EB] z-50">
                <div class="p-6 border-b border-[#E5E7EB]">
                    <h1 class="text-xl font-bold text-[#2563EB]">SBA</h1>
                    <p class="text-[10px] font-semibold text-[#475569] leading-tight mt-0.5">PT. Solusi Bangun Andalas</p>
                    @if($activeModule)
                        <p class="text-[11px] font-bold text-[#1E3A8A] mt-2 border-t border-[#F3F4F6] pt-1">
                            {{ $activeModule === 'meeting_room' ? 'Meeting Room Management' : 'Toilet Inspection Management' }}
                        </p>
                    @endif
                </div>
                
                <!-- Room Action Button -->
                @if($activeModule === 'meeting_room')
                    <div class="p-4 border-b border-[#E5E7EB]">
                        @if($user->isAdmin())
                            <a href="{{ route('admin.rooms.create') }}" class="w-full bg-[#2563EB] text-white rounded h-[36px] font-semibold text-sm flex items-center justify-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">add</span> New Room
                            </a>
                        @else
                            <a href="{{ route('bookings.create') }}" class="w-full bg-[#2563EB] text-white rounded h-[36px] font-semibold text-sm flex items-center justify-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">add</span> Book a Room
                            </a>
                        @endif
                    </div>
                @endif

                <div class="flex-1 overflow-y-auto py-2 h-full flex flex-col text-sm font-medium text-[#475569]">
                    @if($user->isAdmin())
                        <!-- Active Module Header -->
                        @if($activeModule)
                            <div class="px-6 py-3 border-b border-[#E5E7EB] bg-[#F8FAFC] mb-2">
                                <p class="text-[9px] font-semibold text-[#64748B] uppercase tracking-wider">Active Module</p>
                                <p class="text-sm font-bold text-[#0F172A] mt-0.5">
                                    {{ $activeModule === 'meeting_room' ? 'Meeting Room' : 'Toilet Inspection' }}
                                </p>
                                <a href="{{ route('admin.home') }}" class="inline-flex items-center text-xs font-semibold text-[#2563EB] hover:text-[#1D4ED8] mt-2 transition-colors">
                                    Switch Management
                                </a>
                            </div>
                        @endif

                        <!-- Module-specific links -->
                        @if($activeModule === 'meeting_room')
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.dashboard') }}">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('calendar') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('calendar') }}">
                                <span class="material-symbols-outlined">calendar_month</span>
                                <span>Calendar</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.rooms.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.rooms.index') }}">
                                <span class="material-symbols-outlined">meeting_room</span>
                                <span>Rooms</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.facilities.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.facilities.index') }}">
                                <span class="material-symbols-outlined">settings_input_component</span>
                                <span>Facilities</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.users.index', ['management' => 'meeting_room']) }}">
                                <span class="material-symbols-outlined">group</span>
                                <span>Users</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.approvals.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.approvals.index') }}">
                                <span class="material-symbols-outlined">fact_check</span>
                                <span>Approvals</span>
                            </a>
                        @elseif($activeModule === 'toilet_inspection')
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.toilet_inspection.dashboard') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.toilet_inspection.dashboard') }}">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.toilet_inspection.locations.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.toilet_inspection.locations.index') }}">
                                <span class="material-symbols-outlined">map</span>
                                <span>Locations</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.toilet_inspection.checklist-items.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.toilet_inspection.checklist-items.index') }}">
                                <span class="material-symbols-outlined">checklist</span>
                                <span>Checklist Items</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('inspections.index') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('inspections.index') }}">
                                <span class="material-symbols-outlined">analytics</span>
                                <span>Inspections</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('toilet_inspection.approvals.index') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('toilet_inspection.approvals.index') }}">
                                <span class="material-symbols-outlined">fact_check</span>
                                <span>Approvals</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('toilet_inspection.report') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('toilet_inspection.report') }}">
                                <span class="material-symbols-outlined">description</span>
                                <span>Reporting</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('admin.users.index', ['management' => 'toilet_inspection']) }}">
                                <span class="material-symbols-outlined">group</span>
                                <span>Users</span>
                            </a>
                        @else
                            <div class="px-6 py-4 text-xs text-[#94A3B8] italic">
                                Select a management area to begin.
                            </div>
                        @endif
                    @else
                        <!-- Non-admin links filtered by module access -->
                        @if(count($userModules) > 1 && $activeModule)
                            <div class="px-6 py-3 border-b border-[#E5E7EB] bg-[#F8FAFC] mb-2">
                                <p class="text-[9px] font-semibold text-[#64748B] uppercase tracking-wider">Active Module</p>
                                <p class="text-sm font-bold text-[#0F172A] mt-0.5">
                                    {{ $activeModule === 'meeting_room' ? 'Meeting Room' : 'Toilet Inspection' }}
                                </p>
                                <a href="{{ route('select-module') }}" class="inline-flex items-center text-xs font-semibold text-[#2563EB] hover:text-[#1D4ED8] mt-2 transition-colors">
                                    Switch Management
                                </a>
                            </div>
                        @endif

                        @if($activeModule === 'meeting_room')
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('dashboard') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('dashboard') }}">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('calendar') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('calendar') }}">
                                <span class="material-symbols-outlined">calendar_month</span>
                                <span>Calendar</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('rooms.search') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('rooms.search') }}">
                                <span class="material-symbols-outlined">search_check</span>
                                <span>Find Room</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('bookings.*') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('bookings.index') }}">
                                <span class="material-symbols-outlined">book_online</span>
                                <span>My Bookings</span>
                            </a>
                        @elseif($activeModule === 'toilet_inspection')
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('toilet_inspection.dashboard') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('toilet_inspection.dashboard') }}">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span>Toilet Inspection Dashboard</span>
                            </a>
                            <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('inspections.index') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('inspections.index') }}">
                                <span class="material-symbols-outlined">analytics</span>
                                <span>Inspections</span>
                            </a>
                            @if($user->isSpv())
                                <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('toilet_inspection.approvals.index') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('toilet_inspection.approvals.index') }}">
                                    <span class="material-symbols-outlined">fact_check</span>
                                    <span>Approvals</span>
                                </a>
                            @endif
                            @if($user->isSpv() || $user->isAdmin())
                                <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('toilet_inspection.report') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('toilet_inspection.report') }}">
                                    <span class="material-symbols-outlined">description</span>
                                    <span>Reporting</span>
                                </a>
                            @endif
                            @if(!$user->isSpv())
                                <a class="flex items-center gap-3 px-6 py-3 {{ request()->routeIs('inspections.create') ? 'bg-[#EFF6FF] text-[#2563EB] border-l-4 border-[#2563EB]' : 'hover:bg-[#F9FAFB] hover:text-[#0F172A]' }} transition-all" href="{{ route('inspections.create') }}">
                                    <span class="material-symbols-outlined">add_task</span>
                                    <span>Start Inspection</span>
                                </a>
                            @endif
                        @else
                            <div class="px-6 py-4 text-xs text-[#94A3B8] italic">
                                Select a management area to begin.
                            </div>
                        @endif
                    @endif
                </div>

                <!-- User footer info inside sidebar -->
                <div class="p-6 border-t border-[#E5E7EB] flex items-center justify-between text-xs text-[#475569] font-medium bg-[#F9FAFB]">
                    <div class="truncate mr-2">
                        <p class="font-bold text-[#0F172A] truncate">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[10px]">{{ auth()->user()->email }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" title="Logout" class="p-1 hover:text-red-600 flex items-center">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </button>
                    </form>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="flex-1 ml-[280px] flex flex-col h-screen overflow-hidden bg-[#F9FAFB]">
                <!-- Header -->
                <header class="h-16 w-full flex justify-between items-center px-8 bg-white border-b border-[#E5E7EB] z-40 sticky top-0 shrink-0">
                    <h2 class="text-lg font-bold text-[#0F172A]">
                        @if(request()->routeIs('select-module') || (auth()->user()->isAdmin() && !session()->has('active_admin_module') && request()->routeIs('admin.home')))
                            Management Selection
                        @elseif(request()->routeIs('no-access'))
                            No Access
                        @elseif(request()->is('toilet-inspection*') || request()->is('admin/toilet-inspection*') || session('active_admin_module') === 'toilet_inspection')
                            Toilet Inspection Management
                        @else
                            Meeting Room Reservation
                        @endif
                    </h2>
                    
                    <div class="flex items-center gap-4 text-xs text-[#475569]">
                        <span>Timezone: WIB (UTC+7)</span>
                    </div>
                </header>
                
                <!-- Main Body -->
                <main class="flex-1 overflow-auto p-8">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <!-- Guest layouts (like login screen) -->
        <main class="h-full">
            @yield('content')
        </main>
    @endauth

</body>
</html>
