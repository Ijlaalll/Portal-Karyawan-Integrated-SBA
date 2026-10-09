<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F9FAFB]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GA One - Portal Karyawan Terintegrasi SBA')</title>
    <!-- Favicon GA One (Multi-size Transparent Squircle) -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ga_one_icon.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/ga_one_icon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <!-- 1. Critical Font Preloads (Zero-FOUT & Zero-FOIC Instant Loading) -->
    <link rel="preload" href="{{ asset('fonts/inter-variable-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>

    <!-- Fallback Google Fonts CDN (Menjamin simbol selalu muncul di semua perangkat tanpa kompromi) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- 2. Local Hotwired Turbo (SPA-like instant transitions, 100% intranet-ready) -->
    <script src="{{ asset('js/turbo.js') }}" data-turbo-track="reload"></script>

    <script>
        (function() {
            try {
                if (localStorage.getItem('sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch(e) {}
        })();
    </script>
    <style>
        /* 1. Self-hosted Inter Variable Font (100% Offline & Intranet Guaranteed) */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url('{{ asset('fonts/inter-variable-latin.woff2') }}') format('woff2');
        }

        /* 2. Self-hosted Material Symbols Font (Intranet / Offline Guaranteed) with CDN Fallback */
        @font-face {
            font-family: 'Material Symbols Outlined';
            font-style: normal;
            font-weight: 100 700;
            font-display: block;
            src: url('{{ asset('fonts/material-symbols-outlined.woff2') }}') format('woff2'),
                 url('https://fonts.gstatic.com/s/materialsymbolsoutlined/v218/kJF1BvYX7BgnkSrUwT8OhrdQw4oELdPIeeII9v6oDMzByHX9rA6RzaxHMPdY43zj-jCxv3fzvRNU22ZXGJpEpYo3REOpAiUM425vgw.woff2') format('woff2');
        }

        /* 3. Zero-FOIC / Zero-TOFU CSS Guard */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga';
            /* ZERO-FOIC / ZERO-TOFU GUARD: Mencegah kebocoran teks mentah ligature sebelum font siap */
            overflow: hidden;
            text-overflow: clip;
            user-select: none;
            vertical-align: middle;
        }

        /* Custom scrollbar for sidebar */
        .custom-sidebar-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-sidebar-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        .custom-sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        /* Accordion CSS rules ensuring guaranteed single-open behavior */
        .accordion-menu-wrapper {
            display: grid !important;
            grid-template-rows: 0fr;
            transition: grid-template-rows 200ms ease-in-out;
        }
        .accordion-menu-wrapper.is-open {
            grid-template-rows: 1fr !important;
        }
        .sidebar-arrow-icon {
            display: inline-block !important;
            transition: transform 200ms ease-in-out !important;
        }
        .sidebar-arrow-icon.rotate-180 {
            transform: rotate(180deg) !important;
        }

        /* Premium Enterprise White Profile Popover Styling */
        #sidebar-floating-user-popover {
            background-color: #FFFFFF !important;
            color: #0F172A !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 16px !important;
            padding: 16px !important;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.04) !important;
            z-index: 9999 !important;
        }
        #sidebar-floating-user-popover #user-popover-name {
            color: #0F172A !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            margin: 0 !important;
        }
        #sidebar-floating-user-popover #user-popover-email {
            color: #64748B !important;
            font-size: 11px !important;
            margin-top: 2px !important;
        }
        #sidebar-floating-user-popover #user-popover-role {
            color: #2563EB !important;
            background-color: #EFF6FF !important;
            border: 1px solid #BFDBFE !important;
            padding: 3px 8px !important;
            border-radius: 6px !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
        }

        /* Sidebar Collapse Mechanics & Explicit Transitions */
        html.sidebar-collapsed #sidebar-nav {
            width: 72px !important;
        }
        html.sidebar-collapsed #main-content {
            margin-left: 72px !important;
        }
        html.sidebar-collapsed .sidebar-text-label {
            opacity: 0 !important;
            max-width: 0 !important;
            display: none !important;
            pointer-events: none !important;
        }
        html.sidebar-collapsed .sidebar-expanded-header {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-collapsed-header {
            display: flex !important;
        }
        html:not(.sidebar-collapsed) .sidebar-collapsed-header {
            display: none !important;
        }
        html:not(.sidebar-collapsed) .sidebar-expanded-header {
            display: flex !important;
        }
        html.sidebar-collapsed .sidebar-expanded-footer {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-collapsed-footer {
            display: flex !important;
        }
        html:not(.sidebar-collapsed) .sidebar-collapsed-footer {
            display: none !important;
        }
        html:not(.sidebar-collapsed) .sidebar-expanded-footer {
            display: flex !important;
        }
        html.sidebar-collapsed .accordion-menu-wrapper {
            display: none !important;
        }
        html.sidebar-collapsed .nav-item-btn {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        html.sidebar-collapsed .sidebar-arrow-icon {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-action-btn-text {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-action-btn {
            width: 40px !important;
            height: 40px !important;
            padding: 0 !important;
            margin: 0 auto !important;
            justify-content: center !important;
        }

        /* Mobile-Only Overrides (Strictly Viewports < 1024px) */
        @media (max-width: 1023px) {
            #sidebar-nav {
                transform: translateX(-100%);
                transition: transform 300ms ease-in-out;
                width: 280px !important;
                max-width: 85vw !important;
                height: 100vh !important;
                height: 100dvh !important;
                max-height: 100dvh !important;
                bottom: 0 !important;
                z-index: 50 !important;
                overscroll-behavior: contain;
            }
            #sidebar-nav.mobile-open {
                transform: translateX(0) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            }
            #sidebar-menu-container {
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 1.5rem !important;
            }
            #sidebar-user-footer {
                padding-bottom: max(0.75rem, env(safe-area-inset-bottom)) !important;
            }
            #main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .sidebar-text-label {
                opacity: 1 !important;
                max-width: none !important;
                display: inline !important;
                pointer-events: auto !important;
            }
            .sidebar-expanded-header {
                display: flex !important;
            }
            .sidebar-collapsed-header {
                display: none !important;
            }
            .sidebar-expanded-footer {
                display: flex !important;
            }
            .sidebar-collapsed-footer {
                display: none !important;
            }
            .sidebar-arrow-icon {
                display: inline-block !important;
            }
            .sidebar-action-btn-text {
                display: inline !important;
            }
            .sidebar-action-btn {
                width: 100% !important;
                height: 42px !important;
                padding: 0 12px !important;
                margin: 0 !important;
                justify-content: center !important;
            }
            .nav-item-btn {
                justify-content: flex-start !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            #app-layout-header {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            #app-layout-main {
                padding: 1rem !important;
            }
            @media (min-width: 640px) {
                #app-layout-header {
                    padding-left: 1.5rem !important;
                    padding-right: 1.5rem !important;
                }
                #app-layout-main {
                    padding: 1.5rem !important;
                }
            }
            @media (max-width: 639px) {
                .header-timezone-text {
                    display: none !important;
                }
            }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#0F172A]">

    @auth
        @php
            $user = auth()->user();

            // 1. Single Source of Truth for Menu & Navigation
            $navService = app(\App\Services\NavigationService::class);
            $sidebarMenu = $navService->getMenuForUser($user);

            // 2. Identify the active navigation item and parent group
            $activeGroupId = null;
            $activeGroupTitle = null;

            foreach ($sidebarMenu as $groupItem) {
                if (!empty($groupItem['active'])) {
                    $activeGroupId = $groupItem['id'] ?? null;
                    $activeGroupTitle = $groupItem['title'] ?? null;
                    break;
                }
            }

            // 3. Normalized Module Key Mapping (for backwards-compatibility)
            $groupIdToModuleKey = [
                'meeting_room_menu'     => 'meeting_room',
                'toilet_inspection_menu'=> 'toilet_inspection',
                'atk_menu'              => 'atk',
                'sik_menu'              => 'sik',
                'vehicle_menu'          => 'vehicle',
                'settings_menu'         => 'settings',
            ];

            $sidebarActiveModule = $groupIdToModuleKey[$activeGroupId] ?? null;
            $isGlobalDashboard = ($user->isAdmin() && request()->routeIs('admin.home'));
            if ($isGlobalDashboard) {
                $sidebarActiveModule = 'global';
            }

            // 4. Dynamic Brand Subtitle (under "SBA System - PT. Solusi Bangun Andalas")
            $currentModuleName = null;
            if (!$isGlobalDashboard && $activeGroupId && $activeGroupId !== 'settings_menu') {
                $moduleBrandTitles = [
                    'meeting_room_menu'     => 'Manajemen Ruang Meeting',
                    'toilet_inspection_menu'=> 'Manajemen Inspeksi Toilet',
                    'atk_menu'              => 'Manajemen ATK',
                    'sik_menu'              => 'Surat Izin Keluar (SIK)',
                    'vehicle_menu'          => 'Manajemen Kendaraan',
                ];
                $currentModuleName = $moduleBrandTitles[$activeGroupId] ?? $activeGroupTitle;
            } elseif ($activeGroupId === 'settings_menu') {
                $currentModuleName = 'Pengaturan System';
            }

            // 5. Dynamic Navbar Header Title (Pure & free from silent fallbacks)
            if ($isGlobalDashboard) {
                $navbarHeaderTitle = 'Dashboard Admin Global';
            } elseif (request()->routeIs('select-module')) {
                $navbarHeaderTitle = 'Pilihan Manajemen';
            } elseif (request()->routeIs('no-access')) {
                $navbarHeaderTitle = 'Tidak Ada Akses';
            } elseif ($activeGroupId === 'settings_menu') {
                $navbarHeaderTitle = 'Pengaturan Sistem';
            } elseif ($activeGroupId === 'meeting_room_menu') {
                $navbarHeaderTitle = 'Reservasi Ruang Meeting';
            } elseif ($activeGroupId === 'toilet_inspection_menu') {
                $navbarHeaderTitle = 'Manajemen Inspeksi Toilet';
            } elseif ($activeGroupId === 'atk_menu') {
                $navbarHeaderTitle = 'Manajemen ATK';
            } elseif ($activeGroupId === 'sik_menu') {
                $navbarHeaderTitle = 'Surat Izin Keluar (SIK)';
            } elseif ($activeGroupId === 'vehicle_menu') {
                $navbarHeaderTitle = 'Manajemen Kendaraan';
            } elseif ($activeGroupTitle) {
                $navbarHeaderTitle = $activeGroupTitle;
            } else {
                $navbarHeaderTitle = 'SBA System';
            }

            $sidebarMenuDataForJs = array_map(function($item) {
                if (isset($item['submenu'])) {
                    $item['submenu'] = array_map(function($sub) {
                        $sub['url'] = route($sub['route']);
                        return $sub;
                    }, $item['submenu']);
                } elseif (isset($item['route'])) {
                    $item['url'] = route($item['route']);
                }
                return $item;
            }, $sidebarMenu);
        @endphp

        <div class="h-screen flex overflow-hidden" id="app-layout-container">
            <!-- Mobile Sidebar Backdrop Overlay -->
            <div id="mobile-sidebar-backdrop" 
                 onclick="closeMobileSidebar()" 
                 class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden pointer-events-none" 
                 style="backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);" 
                 aria-hidden="true"></div>

            <!-- Sidebar -->
            <nav id="sidebar-nav" class="w-[280px] h-screen flex flex-col fixed left-0 top-0 bg-white border-r border-[#E5E7EB] z-50 transition-[width] duration-300 ease-in-out">
                <!-- Expanded Header / Brand & Multi-Module Identity Section -->
                <div class="sidebar-expanded-header px-4 py-3 flex items-center justify-between border-b border-[#E5E7EB] shrink-0 min-h-[64px] bg-white">
                    <div class="flex items-center gap-3 overflow-hidden min-w-0">
                        <img src="{{ asset('images/ga_one_icon.png') }}" alt="GA One" class="w-9 h-9 rounded-xl shrink-0 shadow-xs object-contain">
                        <div class="whitespace-nowrap overflow-hidden min-w-0">
                            <h1 class="text-sm font-extrabold text-[#0F172A] leading-none tracking-tight">GA One</h1>
                            <p class="text-[10px] font-medium text-[#64748B] mt-0.5 leading-tight truncate">PT. Solusi Bangun Andalas</p>
                            @if($currentModuleName)
                                <p class="text-[11px] font-bold text-[#2563EB] mt-1 pt-1 border-t border-[#F1F5F9] leading-tight truncate">
                                    {{ $currentModuleName }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <button type="button" onclick="closeMobileSidebar()" aria-label="Tutup Menu" class="lg:hidden p-1.5 rounded-lg text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9] transition-colors shrink-0 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                    <button type="button" onclick="toggleSidebar()" aria-label="Collapse Sidebar" title="Ciutkan Sidebar" class="hidden lg:flex p-1.5 rounded-lg text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9] transition-colors shrink-0 items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">keyboard_double_arrow_left</span>
                    </button>
                </div>

                <!-- Collapsed Header / Compact Brand Section -->
                <div class="sidebar-collapsed-header hidden flex-col items-center justify-center gap-2 py-3 px-2 border-b border-[#E5E7EB] shrink-0 bg-white min-h-[64px]">
                    <img src="{{ asset('images/ga_one_icon.png') }}" alt="GA One" class="w-9 h-9 rounded-xl shadow-xs shrink-0 cursor-pointer object-contain"
                         onmouseenter="handleItemMouseEnter(event, 'GA One{{ $currentModuleName ? ' - ' . e($currentModuleName) : '' }}')"
                         onmouseleave="handleItemMouseLeave(event)"
                         title="GA One">
                    <button type="button" onclick="toggleSidebar()" aria-label="Expand Sidebar" title="Buka Sidebar" class="p-1 rounded-lg text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9] transition-colors flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">keyboard_double_arrow_right</span>
                    </button>
                </div>
                
                <!-- Room Action Button (Admin) -->
                @if($user->isAdmin() && $activeGroupId === 'meeting_room_menu')
                    <div class="p-3 border-b border-[#E5E7EB] shrink-0 flex justify-center">
                        <a href="{{ route('admin.rooms.create') }}" 
                           onmouseenter="handleItemMouseEnter(event, 'Tambah Ruangan')"
                           onmouseleave="handleItemMouseLeave(event)"
                           class="sidebar-action-btn w-full bg-[#2563EB] text-white rounded-xl h-[42px] font-semibold text-xs flex items-center justify-center gap-2 hover:bg-[#1D4ED8] transition-colors shadow-xs" 
                           title="Tambah Ruangan">
                            <span class="material-symbols-outlined text-[20px] shrink-0">add</span> 
                            <span class="sidebar-action-btn-text sidebar-text-label whitespace-nowrap overflow-hidden transition-opacity duration-200">Tambah Ruangan</span>
                        </a>
                    </div>
                @endif

                <!-- Navigation List Section -->
                @php
                    $hasRenderedModulSectionLabel = false;
                @endphp

                <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 custom-sidebar-scrollbar" id="sidebar-menu-container">
                    @foreach($sidebarMenu as $group)
                        @php
                            $groupId = $group['id'] ?? '';
                            $isApplicationModule = !empty($groupId) && $groupId !== 'settings_menu';
                        @endphp

                        @if($isApplicationModule && !$hasRenderedModulSectionLabel)
                            @php $hasRenderedModulSectionLabel = true; @endphp
                            <!-- Non-interactive MODUL Section Label -->
                            <div class="pt-3 pb-1 px-3 sidebar-text-label pointer-events-none select-none">
                                <p class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider leading-none">MODUL</p>
                            </div>
                        @endif

                        @if($groupId === 'settings_menu')
                            <!-- Non-interactive PENGATURAN Section Label -->
                            <div class="pt-4 pb-1 px-3 sidebar-text-label pointer-events-none select-none">
                                <p class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider leading-none">PENGATURAN</p>
                            </div>
                        @endif

                        @if(isset($group['submenu']))
                            <!-- Group Parent Menu -->
                            <div class="nav-group-item relative" data-group-id="{{ $group['id'] ?? '' }}">
                                @if(isset($group['id']))
                                    @php
                                        $isGroupExpanded = ($group['id'] === $activeGroupId);
                                    @endphp
                                    <!-- Parent Collapsible Button -->
                                    <button type="button" 
                                            onclick="handleParentClick(event, '{{ $group['id'] }}')" 
                                            onmouseenter="handleParentMouseEnter(event, '{{ $group['id'] }}')"
                                            onmouseleave="handleParentMouseLeave(event)"
                                            class="nav-item-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors duration-150 {{ $group['active'] ? 'bg-[#EFF6FF] text-[#2563EB]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}"
                                            data-group-id="{{ $group['id'] }}"
                                            title="{{ $group['title'] }}">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="material-symbols-outlined text-[20px] shrink-0 {{ $group['active'] ? 'text-[#2563EB]' : 'text-[#64748B]' }}">{{ $group['icon'] }}</span>
                                            <span class="sidebar-text-label truncate transition-opacity duration-200">{{ $group['title'] }}</span>
                                        </div>
                                        <span class="sidebar-arrow-icon material-symbols-outlined text-[18px] text-[#94A3B8] inline-block transition-transform duration-200 shrink-0 {{ $isGroupExpanded ? 'rotate-180' : '' }}" id="{{ str_replace('_menu', '_arrow', $group['id']) }}">
                                            keyboard_arrow_down
                                        </span>
                                    </button>
                                    
                                    <!-- Expanded Accordion Submenu (.is-open controls height) -->
                                    <div class="accordion-menu-wrapper {{ $isGroupExpanded ? 'is-open' : '' }}" id="{{ $group['id'] }}">
                                        <div class="overflow-hidden space-y-1 pl-4 mt-1 border-l-2 border-[#E2E8F0] ml-5">
                                            @foreach($group['submenu'] as $subItem)
                                                <a href="{{ route($subItem['route']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs transition-colors duration-150 {{ $subItem['active'] ? 'bg-[#2563EB] text-white font-semibold shadow-2xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                                                    <span class="material-symbols-outlined text-[18px] shrink-0">{{ $subItem['icon'] }}</span>
                                                    <span class="sidebar-text-label truncate">{{ $subItem['title'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <!-- Parallel Static Group Header -->
                                    <div class="mt-4 mb-2 px-3 sidebar-text-label">
                                        <p class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-wider">{{ $group['title'] }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($group['submenu'] as $subItem)
                                            <a href="{{ route($subItem['route']) }}"
                                               onmouseenter="handleItemMouseEnter(event, '{{ e($subItem['title']) }}')"
                                               onmouseleave="handleItemMouseLeave(event)"
                                               class="nav-item-btn flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors duration-150 {{ $subItem['active'] ? 'bg-[#EFF6FF] text-[#2563EB]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}"
                                               title="{{ $subItem['title'] }}">
                                                <span class="material-symbols-outlined text-[20px] shrink-0 {{ $subItem['active'] ? 'text-[#2563EB]' : 'text-[#64748B]' }}">{{ $subItem['icon'] }}</span>
                                                <span class="sidebar-text-label truncate transition-opacity duration-200">{{ $subItem['title'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Single Top Level Menu Item -->
                            <a href="{{ route($group['route']) }}"
                               onmouseenter="handleItemMouseEnter(event, '{{ e($group['title']) }}')"
                               onmouseleave="handleItemMouseLeave(event)"
                               class="nav-item-btn flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors duration-150 {{ $group['active'] ? 'bg-[#EFF6FF] text-[#2563EB]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}"
                               title="{{ $group['title'] }}">
                                <span class="material-symbols-outlined text-[20px] shrink-0 {{ $group['active'] ? 'text-[#2563EB]' : 'text-[#64748B]' }}">{{ $group['icon'] }}</span>
                                <span class="sidebar-text-label truncate transition-opacity duration-200">{{ $group['title'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>

                <!-- User Footer Section (Fixed at bottom) -->
                <div class="p-3 border-t border-[#E5E7EB] bg-white shrink-0" id="sidebar-user-footer">
                    @php
                        $roleKey = strtolower($user->role ?? 'user');
                        $roleMap = [
                            'admin' => ['label' => 'Admin', 'icon' => 'admin_panel_settings', 'badge' => 'bg-blue-100 text-blue-700 border-blue-200', 'bg' => 'bg-blue-50 text-blue-600 border-blue-200'],
                            'spv' => ['label' => 'SPV', 'icon' => 'supervisor_account', 'badge' => 'bg-purple-100 text-purple-700 border-purple-200', 'bg' => 'bg-purple-50 text-purple-600 border-purple-200'],
                            'security' => ['label' => 'Security', 'icon' => 'local_police', 'badge' => 'bg-rose-100 text-rose-700 border-rose-200', 'bg' => 'bg-rose-50 text-rose-600 border-rose-200'],
                            'it' => ['label' => 'IT', 'icon' => 'terminal', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'bg' => 'bg-emerald-50 text-emerald-600 border-emerald-200'],
                            'ga' => ['label' => 'GA', 'icon' => 'home_work', 'badge' => 'bg-amber-100 text-amber-700 border-amber-200', 'bg' => 'bg-amber-50 text-amber-600 border-amber-200'],
                            'user' => ['label' => 'Pengguna', 'icon' => 'person', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'bg' => 'bg-slate-100 text-slate-600 border-slate-200'],
                        ];
                        $roleInfo = $roleMap[$roleKey] ?? $roleMap['user'];
                    @endphp

                    <!-- Expanded User Card -->
                    <div class="sidebar-expanded-footer flex items-center justify-between gap-2.5 p-2.5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0]">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border shadow-2xs {{ $roleInfo['bg'] }}">
                                <span class="material-symbols-outlined text-[20px]">{{ $roleInfo['icon'] }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-[#0F172A] truncate leading-tight">{{ $user->name }}</p>
                                <p class="text-[11px] font-medium text-[#64748B] truncate leading-tight mt-0.5" title="{{ $user->email }}">{{ $user->email }}</p>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md border text-[9px] font-semibold tracking-wide uppercase leading-none {{ $roleInfo['badge'] }}">
                                        {{ $roleInfo['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" title="Logout" aria-label="Logout" class="p-2 text-[#64748B] hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                            </button>
                        </form>
                    </div>

                    <!-- Collapsed User Card: STRICTLY ROLE ICON ONLY -->
                    <div class="sidebar-collapsed-footer hidden flex-col items-center justify-center gap-2 py-1">
                        <div class="relative" 
                             onmouseenter="handleUserRoleMouseEnter(event, '{{ e($user->name) }}', '{{ e($roleInfo['label']) }}', '{{ e($user->email) }}', '{{ e($roleInfo['icon']) }}')"
                             onmouseleave="handleUserRoleMouseLeave(event)">
                            <button type="button" 
                                    class="w-10 h-10 rounded-xl flex items-center justify-center border shadow-2xs transition-colors {{ $roleInfo['bg'] }}"
                                    aria-label="{{ $user->name }} ({{ $roleInfo['label'] }})">
                                <span class="material-symbols-outlined text-[20px]">{{ $roleInfo['icon'] }}</span>
                            </button>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" title="Logout" aria-label="Logout" class="p-1.5 text-[#64748B] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div id="main-content" class="flex-1 ml-[280px] flex flex-col h-screen overflow-hidden bg-[#F9FAFB] transition-[margin-left] duration-300 ease-in-out">
                <!-- Header -->
                <header id="app-layout-header" class="h-16 w-full flex justify-between items-center px-4 sm:px-6 lg:px-6 xl:px-8 bg-white border-b border-[#E5E7EB] z-40 sticky top-0 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" onclick="openMobileSidebar()" aria-label="Buka Menu" class="lg:hidden p-2 -ml-2 rounded-xl text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9] transition-colors flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[24px]">menu</span>
                        </button>
                        <h2 class="text-lg font-bold text-[#0F172A] truncate">
                            {{ $navbarHeaderTitle }}
                        </h2>
                    </div>
                    
                    <div class="flex items-center gap-4 text-xs text-[#475569] shrink-0">
                        <span class="header-timezone-text">Zona Waktu: WIB (UTC+7)</span>

                        @auth
                            @php
                                $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                                    ->where('is_read', false)
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                                $allNotifications = \App\Models\Notification::where('user_id', auth()->id())
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();
                            @endphp
                            <div class="relative inline-block text-left" id="notificationDropdownContainer">
                                <button type="button" onclick="toggleNotificationDropdown()" class="relative p-1 text-[#475569] hover:text-[#0F172A] rounded-full focus:outline-none hover:bg-slate-100 transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                                    @if($unreadNotifications->count() > 0)
                                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                                    @endif
                                </button>
                                
                                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white border border-[#E5E7EB] rounded-xl shadow-xl z-50 overflow-hidden py-1">
                                    <div class="px-4 py-2 border-b border-[#E5E7EB] flex justify-between items-center bg-[#F9FAFB]">
                                        <span class="font-bold text-[#0F172A] text-xs uppercase tracking-wider">Notifikasi</span>
                                        @if($unreadNotifications->count() > 0)
                                            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-[10px] text-[#2563EB] hover:underline font-bold">Tandai semua dibaca</button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="max-h-64 overflow-y-auto divide-y divide-[#E5E7EB]">
                                        @forelse($allNotifications as $notif)
                                            <div class="px-4 py-3 hover:bg-slate-50 transition-colors relative {{ !$notif->is_read ? 'bg-[#EFF6FF]/30' : '' }}">
                                                <a href="{{ route('notifications.read', $notif) }}" class="block">
                                                    <p class="text-xs font-bold text-slate-800 {{ !$notif->is_read ? 'text-[#2563EB]' : '' }}">{{ $notif->title }}</p>
                                                    <p class="text-[11px] text-slate-600 mt-0.5 whitespace-normal break-words">{{ $notif->message }}</p>
                                                    <span class="text-[9px] text-slate-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                                                </a>
                                            </div>
                                        @empty
                                            <div class="px-4 py-6 text-center text-xs text-slate-400 italic">
                                                Tidak ada notifikasi baru.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </div>
                </header>
                
                <!-- Main Body -->
                <main id="app-layout-main" class="flex-1 overflow-auto p-4 sm:p-6 lg:p-6 xl:p-8">
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

    @auth
        <!-- Floating Flyout Container for Collapsed Parent Menus -->
        <div id="sidebar-floating-flyout" 
             onmouseenter="keepFlyoutOpen()" 
             onmouseleave="handleFlyoutMouseLeave()" 
             class="fixed z-[100] hidden bg-white border border-[#E2E8F0] rounded-xl shadow-xl py-2 px-1 min-w-[210px] max-w-[270px] text-xs transition-opacity duration-150 opacity-0 pointer-events-none">
            <div id="flyout-header" class="px-3 py-1.5 font-bold text-[#0F172A] border-b border-[#F1F5F9] text-[11px] uppercase tracking-wider"></div>
            <div id="flyout-body" class="mt-1 space-y-0.5"></div>
        </div>

        <!-- Floating Tooltip Container for Collapsed Menu Items -->
        <div id="sidebar-floating-tooltip" 
             class="fixed z-[100] hidden bg-[#0F172A] text-white rounded-lg px-2.5 py-1 text-[11px] font-semibold shadow-md transition-opacity duration-150 opacity-0 pointer-events-none whitespace-nowrap"></div>

        <!-- Floating User Role Popover Container for Collapsed User Section -->
        <div id="sidebar-floating-user-popover" 
             class="fixed z-[9999] hidden bg-white text-[#0F172A] rounded-2xl p-4 shadow-2xl text-xs w-64 border border-[#E2E8F0] transition-all duration-150 opacity-0 pointer-events-none">
            <div class="flex items-center gap-3">
                <div id="user-popover-avatar" class="w-10 h-10 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-[20px]" id="user-popover-icon">person</span>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 id="user-popover-name" class="font-bold text-[#0F172A] text-sm truncate leading-tight"></h4>
                    <p id="user-popover-email" class="text-xs text-[#64748B] mt-0.5 truncate font-medium"></p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-[#F1F5F9] flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-[#94A3B8] tracking-wider">Akses System</span>
                <span id="user-popover-role" class="text-xs font-bold text-[#2563EB] uppercase px-2.5 py-0.5 rounded-lg bg-[#EFF6FF] border border-[#BFDBFE]"></span>
            </div>
        </div>

        <script>
            window.sidebarMenuData = @json($sidebarMenuDataForJs ?? []);

            let flyoutTimer = null;
            let activeFlyoutGroupId = null;

            function isSidebarCollapsed() {
                return document.documentElement.classList.contains('sidebar-collapsed');
            }

            function toggleSidebar() {
                const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
                } catch(e) {}

                const toggleIcon = document.getElementById('sidebar-toggle-icon');
                if (toggleIcon) {
                    toggleIcon.textContent = isCollapsed ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
                }

                closeFlyout();
                closeTooltip();
                closeUserPopover();
            }

            function initPageNavigation() {
                const toggleIcon = document.getElementById('sidebar-toggle-icon');
                if (toggleIcon) {
                    toggleIcon.textContent = isSidebarCollapsed() ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
                }
                closeFlyout();
                closeTooltip();
                closeUserPopover();
                closeMobileSidebar();
                restoreTurboScroll();
            }

            window.onPageLoad = function(fn) {
                if (document.readyState !== 'loading') {
                    try { fn(); } catch(e) {}
                }
                document.addEventListener('DOMContentLoaded', fn);
                document.addEventListener('turbo:load', fn);
            };

            window.navigateTo = function(url) {
                const mainEl = document.querySelector('main');
                if (mainEl) {
                    sessionStorage.setItem('turbo_same_page_scroll', mainEl.scrollTop);
                }
                if (window.Turbo) {
                    Turbo.visit(url);
                } else {
                    window.location.href = url;
                }
            };

            window.submitFormWithTurbo = function(element) {
                const form = element ? (element.tagName === 'FORM' ? element : element.form) : null;
                if (!form) return;

                const mainEl = document.querySelector('main');
                if (mainEl) {
                    sessionStorage.setItem('turbo_same_page_scroll', mainEl.scrollTop);
                }

                if (window.Turbo) {
                    const method = (form.getAttribute('method') || 'GET').toUpperCase();
                    if (method === 'GET') {
                        const formData = new FormData(form);
                        const params = new URLSearchParams();
                        for (const [key, value] of formData.entries()) {
                            if (value !== null && value !== '') {
                                params.append(key, value);
                            }
                        }
                        const baseUrl = form.getAttribute('action') || window.location.pathname;
                        const queryString = params.toString();
                        const targetUrl = queryString ? (baseUrl.includes('?') ? `${baseUrl}&${queryString}` : `${baseUrl}?${queryString}`) : baseUrl;
                        Turbo.visit(targetUrl);
                    } else if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                } else {
                    form.submit();
                }
            };

            function restoreTurboScroll() {
                const savedScroll = sessionStorage.getItem('turbo_same_page_scroll');
                if (savedScroll !== null) {
                    const mainEl = document.querySelector('main');
                    if (mainEl) {
                        const pos = parseInt(savedScroll);
                        mainEl.scrollTop = pos;
                        requestAnimationFrame(() => { if (mainEl) mainEl.scrollTop = pos; });
                        setTimeout(() => { if (mainEl) mainEl.scrollTop = pos; }, 50);
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', initPageNavigation);
            document.addEventListener('turbo:load', initPageNavigation);
            document.addEventListener('turbo:render', restoreTurboScroll);
            document.addEventListener('turbo:before-visit', function(event) {
                const currentPath = window.location.pathname;
                let targetPath = '';
                try {
                    if (event.detail && event.detail.url) {
                        targetPath = new URL(event.detail.url).pathname;
                    }
                } catch(e) {}

                if (currentPath && targetPath && currentPath === targetPath) {
                    const mainEl = document.querySelector('main');
                    if (mainEl) {
                        sessionStorage.setItem('turbo_same_page_scroll', mainEl.scrollTop);
                    }
                } else {
                    sessionStorage.removeItem('turbo_same_page_scroll');
                }

                closeFlyout();
                closeTooltip();
                closeUserPopover();
                closeMobileSidebar();
            });

            // ==============================================================
            // INSTANT SPA PREFETCHING (Zero-Latency Navigation Desktop/Mobile)
            // ==============================================================
            const prefetchedUrls = new Set();
            function prefetchUrl(url) {
                if (!url || prefetchedUrls.has(url) || url.includes('#') || url.startsWith('javascript:') || url.includes('/logout')) return;
                try {
                    const parsed = new URL(url, window.location.origin);
                    if (parsed.origin !== window.location.origin) return;
                    if (parsed.pathname === window.location.pathname && parsed.search === window.location.search) return;
                    prefetchedUrls.add(url);
                    const link = document.createElement('link');
                    link.rel = 'prefetch';
                    link.href = url;
                    link.as = 'document';
                    document.head.appendChild(link);
                } catch(e) {}
            }

            // Desktop hover prefetch (loads in background ~100ms before click)
            document.addEventListener('mouseover', function(e) {
                const anchor = e.target.closest('a');
                if (anchor && anchor.href && !anchor.hasAttribute('download') && anchor.getAttribute('target') !== '_blank') {
                    prefetchUrl(anchor.href);
                }
            }, { passive: true });

            // Mobile touch prefetch (initiates background fetch on screen touch)
            document.addEventListener('touchstart', function(e) {
                const anchor = e.target.closest('a');
                if (anchor && anchor.href && !anchor.hasAttribute('download') && anchor.getAttribute('target') !== '_blank') {
                    prefetchUrl(anchor.href);
                }
            }, { passive: true });

            // EXPANDED ACCORDION: Single parent group open at a time
            function handleParentClick(event, groupId) {
                if (isSidebarCollapsed()) {
                    handleParentMouseEnter(event, groupId);
                    return;
                }

                const clickedWrapper = document.getElementById(groupId);
                const clickedArrow = document.getElementById(groupId.replace('_menu', '_arrow'));
                const isCurrentlyOpen = clickedWrapper && (clickedWrapper.classList.contains('is-open') || clickedWrapper.classList.contains('grid-rows-[1fr]'));

                // Close all parent accordions
                document.querySelectorAll('.accordion-menu-wrapper').forEach(wrapper => {
                    wrapper.classList.remove('is-open', 'grid-rows-[1fr]');
                    wrapper.classList.add('grid-rows-[0fr]');
                });
                document.querySelectorAll('.sidebar-arrow-icon').forEach(arrow => {
                    arrow.classList.remove('rotate-180');
                });

                // Open clicked group if it wasn't already open
                if (!isCurrentlyOpen && clickedWrapper) {
                    clickedWrapper.classList.add('is-open', 'grid-rows-[1fr]');
                    clickedWrapper.classList.remove('grid-rows-[0fr]');
                    if (clickedArrow) {
                        clickedArrow.classList.add('rotate-180');
                    }
                }
            }

            // COLLAPSED FLYOUT HANDLERS
            function handleParentMouseEnter(event, groupId) {
                if (!isSidebarCollapsed() || window.innerWidth < 1024) return;

                if (flyoutTimer) clearTimeout(flyoutTimer);

                const groupData = (window.sidebarMenuData || []).find(item => item.id === groupId);
                if (!groupData || !groupData.submenu) return;

                activeFlyoutGroupId = groupId;

                const flyout = document.getElementById('sidebar-floating-flyout');
                const header = document.getElementById('flyout-header');
                const body = document.getElementById('flyout-body');

                header.textContent = groupData.title;

                body.innerHTML = groupData.submenu.map(sub => `
                    <a href="${sub.url}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs transition-colors duration-150 ${sub.active ? 'bg-[#EFF6FF] text-[#2563EB] font-semibold' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]'}">
                        <span class="material-symbols-outlined text-[18px] shrink-0">${sub.icon}</span>
                        <span>${sub.title}</span>
                    </a>
                `).join('');

                const target = event.currentTarget;
                const rect = target.getBoundingClientRect();

                flyout.style.left = (rect.right + 8) + 'px';
                flyout.style.top = Math.max(12, Math.min(rect.top, window.innerHeight - 300)) + 'px';
                flyout.classList.remove('hidden');
                
                void flyout.offsetWidth;
                flyout.classList.remove('opacity-0', 'pointer-events-none');
                flyout.classList.add('opacity-100', 'pointer-events-auto');
            }

            function handleParentMouseLeave(event) {
                if (!isSidebarCollapsed() || window.innerWidth < 1024) return;
                flyoutTimer = setTimeout(closeFlyout, 180);
            }

            function keepFlyoutOpen() {
                if (flyoutTimer) clearTimeout(flyoutTimer);
            }

            function handleFlyoutMouseLeave() {
                flyoutTimer = setTimeout(closeFlyout, 180);
            }

            function closeFlyout() {
                const flyout = document.getElementById('sidebar-floating-flyout');
                if (flyout) {
                    flyout.classList.remove('opacity-100', 'pointer-events-auto');
                    flyout.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => flyout.classList.add('hidden'), 150);
                }
                activeFlyoutGroupId = null;
            }

            // COLLAPSED TOOLTIP HANDLERS
            function handleItemMouseEnter(event, title) {
                if (!isSidebarCollapsed() || window.innerWidth < 1024) return;

                const tooltip = document.getElementById('sidebar-floating-tooltip');
                if (!tooltip) return;
                tooltip.textContent = title;

                const rect = event.currentTarget.getBoundingClientRect();
                tooltip.style.left = (rect.right + 10) + 'px';
                tooltip.style.top = (rect.top + (rect.height / 2) - 12) + 'px';

                tooltip.classList.remove('hidden');
                void tooltip.offsetWidth;
                tooltip.classList.remove('opacity-0', 'pointer-events-none');
                tooltip.classList.add('opacity-100', 'pointer-events-auto');
            }

            function handleItemMouseLeave(event) {
                if (!isSidebarCollapsed() || window.innerWidth < 1024) return;
                closeTooltip();
            }

            function closeTooltip() {
                const tooltip = document.getElementById('sidebar-floating-tooltip');
                if (tooltip) {
                    tooltip.classList.remove('opacity-100', 'pointer-events-auto');
                    tooltip.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => tooltip.classList.add('hidden'), 150);
                }
            }

            // COLLAPSED USER ROLE POPOVER HANDLERS
            function handleUserRoleMouseEnter(event, name, role, email, icon) {
                if (!isSidebarCollapsed() || window.innerWidth < 1024) return;

                const popover = document.getElementById('sidebar-floating-user-popover');
                if (!popover) return;
                document.getElementById('user-popover-name').textContent = name;
                document.getElementById('user-popover-email').textContent = email;
                document.getElementById('user-popover-role').textContent = role;
                if (icon && document.getElementById('user-popover-icon')) {
                    document.getElementById('user-popover-icon').textContent = icon;
                }

                popover.classList.remove('hidden');
                void popover.offsetWidth;

                const rect = event.currentTarget.getBoundingClientRect();
                const popoverHeight = popover.offsetHeight || 110;
                
                // Position clear of the 72px sidebar and aligned vertically centered with the role icon
                popover.style.left = Math.max(84, rect.right + 14) + 'px';
                
                const topPos = rect.top + (rect.height / 2) - (popoverHeight / 2);
                popover.style.top = Math.max(12, Math.min(topPos, window.innerHeight - popoverHeight - 12)) + 'px';

                popover.classList.remove('opacity-0', 'pointer-events-none');
                popover.classList.add('opacity-100', 'pointer-events-auto');
            }

            function handleUserRoleMouseLeave(event) {
                if (!isSidebarCollapsed() || window.innerWidth < 1024) return;
                closeUserPopover();
            }

            function closeUserPopover() {
                const popover = document.getElementById('sidebar-floating-user-popover');
                if (popover) {
                    popover.classList.remove('opacity-100', 'pointer-events-auto');
                    popover.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => popover.classList.add('hidden'), 150);
                }
            }

            // Mobile Drawer Controls
            function openMobileSidebar() {
                if (window.innerWidth >= 1024) return;
                const sidebar = document.getElementById('sidebar-nav');
                const backdrop = document.getElementById('mobile-sidebar-backdrop');
                if (sidebar) {
                    sidebar.classList.add('mobile-open');
                }
                if (backdrop) {
                    backdrop.classList.remove('hidden', 'pointer-events-none');
                    void backdrop.offsetWidth;
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                }
            }

            function closeMobileSidebar() {
                const sidebar = document.getElementById('sidebar-nav');
                const backdrop = document.getElementById('mobile-sidebar-backdrop');
                if (sidebar) {
                    sidebar.classList.remove('mobile-open');
                }
                if (backdrop) {
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => {
                        if (backdrop && sidebar && !sidebar.classList.contains('mobile-open')) {
                            backdrop.classList.add('hidden', 'pointer-events-none');
                        }
                    }, 300);
                }
            }

            window.openMobileSidebar = openMobileSidebar;
            window.closeMobileSidebar = closeMobileSidebar;

            // Global single-instance listeners for drawer dismissal
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeMobileSidebar();
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeMobileSidebar();
                }
            });

            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) {
                    const navLink = event.target.closest('#sidebar-nav a');
                    if (navLink) {
                        closeMobileSidebar();
                    }
                }
            });

            // Fallback for any legacy toggleModule calls
            function toggleModule(menuId) {
                handleParentClick(null, menuId);
            }

            function toggleNotificationDropdown() {
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown) {
                    dropdown.classList.toggle('hidden');
                }
            }

            document.addEventListener('click', function(event) {
                const container = document.getElementById('notificationDropdownContainer');
                const dropdown = document.getElementById('notificationDropdown');
                if (container && !container.contains(event.target) && dropdown) {
                    dropdown.classList.add('hidden');
                }

                const flyout = document.getElementById('sidebar-floating-flyout');
                if (flyout && !flyout.contains(event.target) && !event.target.closest('.nav-item-btn')) {
                    closeFlyout();
                }
            });
        </script>
        @if(auth()->user()->isAdmin())
            <!-- Admin scripts and styling -->
        @else


            @php
                \App\Models\Booking::syncCompletedBookings();
                
                $now = \Carbon\Carbon::now('Asia/Jakarta');

                // 0. Cancellation Popup:
                $pendingCancellationBooking = \App\Models\Booking::where('user_id', auth()->id())
                    ->where('status', 'cancelled')
                    ->whereNotNull('cancellation_reason')
                    ->where('cancellation_popup_acknowledged', false)
                    ->with('room')
                    ->orderBy('updated_at', 'desc')
                    ->first();

                // 1. Physical Room (GA) Ready Popup (New and Legacy Physical):
                $pendingGAReadyBooking = \App\Models\Booking::where('user_id', auth()->id())
                    ->where('status', 'approved')
                    ->where(function($outerQ) {
                        $outerQ->where(function($q) {
                            // New flow (both offline and hybrid use ga_status for room ready)
                            $q->whereNotNull('ga_status')
                              ->where('ga_status', 'ready')
                              ->where('ga_ready_popup_acknowledged', false);
                        })->orWhere(function($q) {
                            // Legacy offline
                            $q->whereNull('ga_status')
                              ->where('meeting_type', 'offline')
                              ->where('preparation_status', 'Siap')
                              ->where('ready_popup_acknowledged', false);
                        });
                    })
                    ->with('room')
                    ->orderBy('booking_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->first();

                // 2. Hybrid IT Ready Popup (New Hybrid IT task is ready):
                $pendingITReadyBooking = \App\Models\Booking::where('user_id', auth()->id())
                    ->where('status', 'approved')
                    ->where('meeting_type', 'hybrid')
                    ->whereNotNull('it_status')
                    ->where('it_status', 'ready')
                    ->where('it_ready_popup_acknowledged', false)
                    ->with('room')
                    ->orderBy('booking_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->first();

                // 3. Legacy Online Booking Popups:
                $pendingOnlineReadyBooking1 = null;
                $pendingOnlineReadyBooking2 = null;

                $pendingReviewBooking = \App\Models\Booking::where('user_id', auth()->id())
                    ->where('status', 'completed')
                    ->whereNull('rating')
                    ->whereNotIn('id', session('snoozed_bookings', []))
                    ->with('room')
                    ->orderBy('booking_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->first();
            @endphp

            @if($pendingCancellationBooking)
                <!-- Cancellation Modal -->
                @php
                    $reasonLabels = [
                        'GA_FAILURE' => 'Persiapan ruangan oleh Tim GA tidak siap.',
                        'IT_FAILURE' => 'Persiapan teknis oleh Tim IT tidak siap.',
                        'BOTH_FAILURE' => 'Persiapan Tim GA dan Tim IT tidak siap.',
                        'GA_TIMEOUT' => 'Persiapan ruangan oleh Tim GA belum selesai sampai batas waktu meeting.',
                        'IT_TIMEOUT' => 'Persiapan teknis oleh Tim IT belum selesai sampai batas waktu meeting.',
                        'BOTH_TIMEOUT' => 'Persiapan Tim GA dan Tim IT belum selesai sampai batas waktu meeting.'
                    ];
                    $cancellationReasonText = $reasonLabels[$pendingCancellationBooking->cancellation_reason] ?? 'Persiapan meeting tidak siap.';
                @endphp
                <div id="bookingCancellationModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-[40px] text-red-500 mb-2">cancel</span>
                            <h3 class="text-lg font-bold text-[#0F172A]">Meeting Dibatalkan</h3>
                            <p class="text-xs text-slate-500 mt-1">Meeting "{{ $pendingCancellationBooking->title }}" dibatalkan.</p>
                            
                            <div class="text-left bg-rose-50/50 border border-rose-100 rounded-xl p-4 mt-3 space-y-2 text-xs text-[#334155]">
                                <div><span class="font-bold text-red-700">Alasan:</span> {{ $cancellationReasonText }}</div>
                                @if($pendingCancellationBooking->cancellation_note)
                                    <div><span class="font-bold text-[#475569]">Detail:</span> {{ $pendingCancellationBooking->cancellation_note }}</div>
                                @endif
                                @if($pendingCancellationBooking->room)
                                    <div><span class="font-bold text-[#475569]">Ruangan:</span> {{ $pendingCancellationBooking->room->name }} ({{ $pendingCancellationBooking->room->location }})</div>
                                @endif
                                <div><span class="font-bold text-[#475569]">Waktu:</span> {{ substr($pendingCancellationBooking->start_time, 0, 5) }} - {{ substr($pendingCancellationBooking->end_time, 0, 5) }} WIB</div>
                                <div><span class="font-bold text-[#475569]">Jenis:</span> {{ ucfirst($pendingCancellationBooking->meeting_type) }}</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2 pt-2">
                            <button type="button" onclick="acknowledgeCancellationBooking({{ $pendingCancellationBooking->id }})" class="w-full bg-red-600 text-white text-xs font-bold py-2.5 rounded-lg hover:bg-red-700 transition-colors shadow-sm text-center">
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    function acknowledgeCancellationBooking(bookingId) {
                        fetch('/bookings/' + bookingId + '/acknowledge-cancellation', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('bookingCancellationModal').remove();
                                window.location.reload();
                            }
                        })
                        .catch(err => {
                            console.error('Error acknowledging cancellation booking:', err);
                            document.getElementById('bookingCancellationModal').remove();
                        });
                    }
                </script>
            @elseif($pendingGAReadyBooking)
                <!-- Physical Room (GA) Ready Modal -->
                <div id="bookingReadyModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-[40px] text-green-500 mb-2">check_circle</span>
                            <h3 class="text-lg font-bold text-[#0F172A]">Ruangan Siap Digunakan!</h3>
                            <p class="text-xs text-slate-500 mt-1">Tim GA telah menyiapkan ruangan Anda dan siap untuk digunakan.</p>
                            <div class="text-left bg-slate-50 border border-[#E2E8F0] rounded-xl p-4 mt-3 space-y-2 text-xs text-[#334155]">
                                <div><span class="font-bold text-[#475569]">Ruangan:</span> {{ $pendingGAReadyBooking->room ? $pendingGAReadyBooking->room->name : 'N/A' }} ({{ $pendingGAReadyBooking->room ? $pendingGAReadyBooking->room->location : '?' }})</div>
                                <div><span class="font-bold text-[#475569]">Agenda:</span> {{ $pendingGAReadyBooking->title }}</div>
                                <div><span class="font-bold text-[#475569]">Tanggal:</span> {{ $pendingGAReadyBooking->booking_date->format('d M Y') }}</div>
                                <div><span class="font-bold text-[#475569]">Waktu:</span> {{ substr($pendingGAReadyBooking->start_time, 0, 5) }} - {{ substr($pendingGAReadyBooking->end_time, 0, 5) }} WIB</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2 pt-2">
                            <button type="button" onclick="acknowledgeReadyBooking({{ $pendingGAReadyBooking->id }})" class="w-full bg-[#2563EB] text-white text-xs font-bold py-2.5 rounded-lg hover:bg-[#1D4ED8] transition-colors shadow-sm text-center">
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    function acknowledgeReadyBooking(bookingId) {
                        fetch('/bookings/' + bookingId + '/acknowledge-ready', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('bookingReadyModal').remove();
                                window.location.reload();
                            }
                        })
                        .catch(err => {
                            console.error('Error acknowledging ready booking:', err);
                            document.getElementById('bookingReadyModal').remove();
                        });
                    }
                </script>
            @elseif($pendingITReadyBooking)
                <!-- Hybrid IT Ready Modal -->
                <div id="bookingReadyModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-[40px] text-blue-500 mb-2">settings_input_antenna</span>
                            <h3 class="text-lg font-bold text-[#0F172A]">Fasilitas Hybrid Siap!</h3>
                            <p class="text-xs text-slate-500 mt-1">Tim IT telah menyiapkan fasilitas hybrid untuk pertemuan Anda.</p>
                            <div class="text-left bg-slate-50 border border-[#E2E8F0] rounded-xl p-4 mt-3 space-y-2 text-xs text-[#334155]">
                                <div><span class="font-bold text-[#475569]">Ruangan:</span> {{ $pendingITReadyBooking->room ? $pendingITReadyBooking->room->name : 'N/A' }} ({{ $pendingITReadyBooking->room ? $pendingITReadyBooking->room->location : '?' }})</div>
                                <div><span class="font-bold text-[#475569]">Agenda:</span> {{ $pendingITReadyBooking->title }}</div>
                                <div><span class="font-bold text-[#475569]">Tanggal:</span> {{ $pendingITReadyBooking->booking_date->format('d M Y') }}</div>
                                <div><span class="font-bold text-[#475569]">Waktu:</span> {{ substr($pendingITReadyBooking->start_time, 0, 5) }} - {{ substr($pendingITReadyBooking->end_time, 0, 5) }} WIB</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2 pt-2">
                            <button type="button" onclick="acknowledgeActiveBooking({{ $pendingITReadyBooking->id }})" class="w-full bg-[#2563EB] text-white text-xs font-bold py-2.5 rounded-lg hover:bg-[#1D4ED8] transition-colors shadow-sm text-center">
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    function acknowledgeActiveBooking(bookingId) {
                        fetch('/bookings/' + bookingId + '/acknowledge-active', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('bookingReadyModal').remove();
                                window.location.reload();
                            }
                        })
                        .catch(err => {
                            console.error('Error acknowledging active booking:', err);
                            document.getElementById('bookingReadyModal').remove();
                        });
                    }
                </script>

            @elseif($pendingReviewBooking)
                <!-- Post-Meeting Review Modal -->
                <div id="postMeetingReviewModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-[40px] text-amber-500 mb-2">rate_review</span>
                            <h3 class="text-lg font-bold text-[#0F172A]">Bagaimana meeting Anda?</h3>
                            <p class="text-xs text-slate-500 mt-1">Silakan berikan rating pengalaman Anda di <span class="font-bold text-[#2563EB]">{{ $pendingReviewBooking->room ? $pendingReviewBooking->room->name : 'Meeting Online (Teams)' }}</span></p>
                        </div>

                        <form action="{{ route('bookings.review.store', $pendingReviewBooking) }}" method="POST" id="postMeetingReviewForm" class="space-y-4">
                            @csrf
                            <!-- Stars Rating -->
                            <div class="flex items-center justify-center gap-2 py-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" onclick="setPopupRating({{ $i }})" class="popup-star-btn text-slate-300 hover:text-amber-400 focus:outline-none transition-colors" data-star="{{ $i }}">
                                        <span class="material-symbols-outlined text-[36px]" id="popup-star-icon-{{ $i }}">star</span>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="popup_rating_input" value="">

                            <!-- Comment Textarea -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ceritakan pengalaman Anda...</label>
                                <textarea name="feedback" rows="3" placeholder="Komentar tambahan (opsional)..." class="w-full border border-gray-200 rounded-lg p-3 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] resize-none"></textarea>
                            </div>
 
                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                <button type="submit" id="submitReviewBtn" disabled class="flex-1 bg-[#2563EB] text-white text-xs font-bold py-2.5 rounded-lg hover:bg-[#1D4ED8] disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm text-center">
                                    Kirim Ulasan
                                </button>
                                <button type="button" onclick="snoozePopupReview({{ $pendingReviewBooking->id }})" class="flex-1 border border-gray-200 text-xs font-bold py-2.5 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors shadow-sm text-center">
                                    Ingatkan Saya Nanti
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function setPopupRating(rating) {
                        document.getElementById('popup_rating_input').value = rating;
                        document.getElementById('submitReviewBtn').disabled = false;
                        for (let i = 1; i <= 5; i++) {
                            const icon = document.getElementById('popup-star-icon-' + i);
                            if (i <= rating) {
                                icon.classList.remove('text-slate-300');
                                icon.classList.add('text-amber-500');
                                icon.style.fontVariationSettings = "'FILL' 1";
                            } else {
                                icon.classList.remove('text-amber-500');
                                icon.classList.add('text-slate-300');
                                icon.style.fontVariationSettings = "'FILL' 0";
                            }
                        }
                    }

                    function snoozePopupReview(bookingId) {
                        fetch('/bookings/' + bookingId + '/snooze', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('postMeetingReviewModal').remove();
                            }
                        })
                        .catch(err => {
                            console.error('Error snoozing review:', err);
                            document.getElementById('postMeetingReviewModal').remove();
                        });
                    }
                </script>
            @endif
        @endif
    @endauth
</body>
</html>
