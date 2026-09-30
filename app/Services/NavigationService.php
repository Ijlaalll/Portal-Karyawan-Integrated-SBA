<?php

namespace App\Services;

use App\Models\User;

class NavigationService
{
    /**
     * Get the dynamic menu array for the authenticated user based on role and module accesses.
     */
    public function getMenuForUser(User $user): array
    {
        $menu = [];

        // 1. ADMIN MENU
        if ($user->isAdmin()) {
            // Global Dashboard
            $menu[] = [
                'title' => 'Dashboard Global',
                'icon' => 'dns',
                'route' => 'admin.home',
                'active' => request()->routeIs('admin.home'),
            ];

            // Settings Group
            $menu[] = [
                'title' => 'Pengaturan',
                'icon' => 'settings',
                'id' => 'settings_menu',
                'active' => (request()->is('admin/settings*') || request()->routeIs('admin.users.*')) && !request()->is('admin/settings/department-approvals*') && !request()->routeIs('admin.settings.department-approvals.*'),
                'submenu' => [
                    [
                        'title' => 'Pengguna',
                        'icon' => 'group',
                        'route' => 'admin.users.index',
                        'active' => request()->routeIs('admin.users.*'),
                    ],
                    [
                        'title' => 'Cost Center',
                        'icon' => 'payments',
                        'route' => 'admin.settings.cost-centers.index',
                        'active' => request()->routeIs('admin.settings.cost-centers.*'),
                    ],
                    [
                        'title' => 'SMTP Settings',
                        'icon' => 'mail',
                        'route' => 'admin.settings.smtp.show',
                        'active' => request()->routeIs('admin.settings.smtp.*'),
                    ],
                    [
                        'title' => 'Audit Trail (Log Aktivitas)',
                        'icon' => 'receipt_long',
                        'route' => 'admin.settings.audit-logs.index',
                        'active' => request()->routeIs('admin.settings.audit-logs.*'),
                    ],
                ]
            ];

            // Meeting Room Module Group (Admin version)
            $menu[] = [
                'title' => 'Ruang Meeting',
                'icon' => 'meeting_room',
                'id' => 'meeting_room_menu',
                'active' => request()->is('admin/rooms*') || request()->is('admin/facilities*') || request()->is('admin/dashboard*') || request()->is('admin/reviews*') || request()->is('admin/booking-history*') || request()->is('admin/booking_history*') || request()->routeIs('calendar') || request()->routeIs('admin.reviews') || request()->routeIs('admin.booking_history'),
                'submenu' => [
                    [
                        'title' => 'Dashboard',
                        'icon' => 'dashboard',
                        'route' => 'admin.dashboard',
                        'active' => request()->routeIs('admin.dashboard'),
                    ],
                    [
                        'title' => 'Kalender',
                        'icon' => 'calendar_month',
                        'route' => 'calendar',
                        'active' => request()->routeIs('calendar'),
                    ],
                    [
                        'title' => 'Ruangan',
                        'icon' => 'meeting_room',
                        'route' => 'admin.rooms.index',
                        'active' => request()->routeIs('admin.rooms.*'),
                    ],
                    [
                        'title' => 'Fasilitas',
                        'icon' => 'settings_input_component',
                        'route' => 'admin.facilities.index',
                        'active' => request()->routeIs('admin.facilities.*'),
                    ],
                    [
                        'title' => 'Rating',
                        'icon' => 'grade',
                        'route' => 'admin.reviews',
                        'active' => request()->routeIs('admin.reviews'),
                    ],
                    [
                        'title' => 'Riwayat Penggunaan',
                        'icon' => 'history',
                        'route' => 'admin.booking_history',
                        'active' => request()->routeIs('admin.booking_history'),
                    ],
                ]
            ];

            // Toilet Inspection Module Group (Admin version)
            $menu[] = [
                'title' => 'Inspeksi Toilet',
                'icon' => 'wc',
                'id' => 'toilet_inspection_menu',
                'active' => request()->is('admin/toilet-inspection*') || request()->routeIs('toilet_inspection.report'),
                'submenu' => [
                    [
                        'title' => 'Dashboard',
                        'icon' => 'dashboard',
                        'route' => 'admin.toilet_inspection.dashboard',
                        'active' => request()->routeIs('admin.toilet_inspection.dashboard'),
                    ],
                    [
                        'title' => 'Inspeksi',
                        'icon' => 'analytics',
                        'route' => 'admin.toilet_inspection.inspections.index',
                        'active' => request()->routeIs('admin.toilet_inspection.inspections.index'),
                    ],
                    [
                        'title' => 'Checklist',
                        'icon' => 'checklist',
                        'route' => 'admin.toilet_inspection.checklist-items.index',
                        'active' => request()->routeIs('admin.toilet_inspection.checklist-items.*'),
                    ],
                    [
                        'title' => 'Lokasi',
                        'icon' => 'map',
                        'route' => 'admin.toilet_inspection.locations.index',
                        'active' => request()->routeIs('admin.toilet_inspection.locations.*'),
                    ],
                    [
                        'title' => 'Persetujuan',
                        'icon' => 'fact_check',
                        'route' => 'admin.toilet_inspection.approvals.index',
                        'active' => request()->routeIs('admin.toilet_inspection.approvals.index'),
                    ],
                    [
                        'title' => 'Laporan',
                        'icon' => 'description',
                        'route' => 'toilet_inspection.report',
                        'active' => request()->routeIs('toilet_inspection.report'),
                    ],
                ]
            ];

            // ATK Module Group (Admin version)
            $menu[] = [
                'title' => 'ATK',
                'icon' => 'inventory_2',
                'id' => 'atk_menu',
                'active' => request()->is('atk*') || request()->routeIs('atk.*'),
                'submenu' => [
                    [
                        'title' => 'Dashboard',
                        'icon' => 'dashboard',
                        'route' => 'atk.dashboard',
                        'active' => request()->routeIs('atk.dashboard'),
                    ],
                    [
                        'title' => 'Master ATK',
                        'icon' => 'inventory_2',
                        'route' => 'atk.items.index',
                        'active' => request()->routeIs('atk.items.*') || request()->routeIs('atk.categories.*') || request()->routeIs('atk.variants.*'),
                    ],
                    [
                        'title' => 'Stok & Log',
                        'icon' => 'inventory',
                        'route' => 'atk.stock.index',
                        'active' => request()->routeIs('atk.stock.*'),
                    ],
                    [
                        'title' => 'Riwayat',
                        'icon' => 'history',
                        'route' => 'atk.history.index',
                        'active' => request()->routeIs('atk.history.index'),
                    ],
                ]
            ];

            // SIK Module Group (Admin version)
            $sikSubmenu = [];

            // If Admin is also an Employee, include Requester menu
            if ($user->isEmployee()) {
                $sikSubmenu[] = [
                    'title' => 'Ajukan SIK',
                    'icon' => 'post_add',
                    'route' => 'sik.create',
                    'active' => request()->routeIs('sik.create'),
                ];
                $sikSubmenu[] = [
                    'title' => 'SIK Saya',
                    'icon' => 'assignment_ind',
                    'route' => 'sik.index',
                    'active' => request()->routeIs('sik.index') || (request()->is('sik/*') && !request()->routeIs('sik.create') && !request()->is('sik-management*') && !request()->is('admin/sik*')),
                ];
            }

            // If Admin is also configured as HOD or Approver in a department, include Persetujuan
            if ($user->isSikApprover()) {
                $sikSubmenu[] = [
                    'title' => 'Persetujuan SIK',
                    'icon' => 'fact_check',
                    'route' => 'sik.approvals.index',
                    'active' => request()->routeIs('sik.approvals.*'),
                ];
            }

            // Administrative SIK Menu (Standard for all Admins)
            $sikSubmenu[] = [
                'title' => 'Dashboard SIK',
                'icon' => 'dashboard',
                'route' => 'admin.sik.dashboard',
                'active' => request()->routeIs('admin.sik.dashboard'),
            ];
            $sikSubmenu[] = [
                'title' => 'Semua SIK',
                'icon' => 'list_alt',
                'route' => 'admin.sik.index',
                'active' => request()->routeIs('admin.sik.index'),
            ];
            $sikSubmenu[] = [
                'title' => 'Monitoring',
                'icon' => 'corporate_fare',
                'route' => 'sik.department.index',
                'active' => request()->routeIs('sik.department.index'),
            ];
            $sikSubmenu[] = [
                'title' => 'Riwayat SIK',
                'icon' => 'history',
                'route' => 'sik.history.index',
                'active' => request()->routeIs('sik.history.index'),
            ];
            $sikSubmenu[] = [
                'title' => 'Riwayat Scan',
                'icon' => 'qr_code_scanner',
                'route' => 'sik.scan-logs',
                'active' => request()->routeIs('sik.scan-logs'),
            ];
            $sikSubmenu[] = [
                'title' => 'Konfigurasi Approval',
                'icon' => 'tune',
                'route' => 'admin.settings.department-approvals.index',
                'active' => request()->routeIs('admin.settings.department-approvals.*'),
            ];
            $sikSubmenu[] = [
                'title' => 'Audit Log',
                'icon' => 'receipt_long',
                'route' => 'admin.sik.audit-logs',
                'active' => request()->routeIs('admin.sik.audit-logs'),
            ];

            $menu[] = [
                'title' => 'Surat Izin Keluar (SIK)',
                'icon' => 'badge',
                'id' => 'sik_menu',
                'active' => request()->is('sik*') || request()->is('sik-management*') || request()->is('admin/sik*') || request()->is('admin/settings/department-approvals*') || request()->routeIs('sik.*') || request()->routeIs('admin.sik.*') || request()->routeIs('admin.settings.department-approvals.*'),
                'submenu' => $sikSubmenu,
            ];

            // Vehicle Management Group (Admin version)
            $menu[] = [
                'title' => 'Kendaraan',
                'icon' => 'directions_car',
                'id' => 'vehicle_menu',
                'active' => request()->is('vehicles*') || request()->routeIs('vehicles.*'),
                'submenu' => [
                    [
                        'title' => 'Dashboard Kendaraan',
                        'icon' => 'dashboard',
                        'route' => 'vehicles.dashboard',
                        'active' => request()->routeIs('vehicles.dashboard'),
                    ],
                    [
                        'title' => 'Monitoring Permohonan',
                        'icon' => 'assignment',
                        'route' => 'vehicles.requests.index',
                        'active' => request()->routeIs('vehicles.requests.*'),
                    ],
                    [
                        'title' => 'Daftar Mobil',
                        'icon' => 'directions_car',
                        'route' => 'vehicles.index',
                        'active' => request()->routeIs('vehicles.index') || request()->routeIs('vehicles.create') || request()->routeIs('vehicles.edit') || request()->routeIs('vehicles.show'),
                    ],
                    [
                        'title' => 'Daftar Supir',
                        'icon' => 'person',
                        'route' => 'vehicles.drivers.index',
                        'active' => request()->routeIs('vehicles.drivers.*'),
                    ],
                    [
                        'title' => 'Konsumsi BBM',
                        'icon' => 'local_gas_station',
                        'route' => 'vehicles.fuel.index',
                        'active' => request()->routeIs('vehicles.fuel.*'),
                    ],
                ]
            ];

            return $menu;
        }

        // 2. SECURITY ROLE MENU
        if ($user->isSecurity()) {
            $menu[] = [
                'title' => 'Surat Izin Keluar (SIK)',
                'icon' => 'local_police',
                'id' => 'sik_menu',
                'active' => request()->is('sik*') || request()->routeIs('sik.*'),
                'submenu' => [
                    [
                        'title' => 'Dashboard',
                        'icon' => 'dashboard',
                        'route' => 'sik.security.dashboard',
                        'active' => request()->routeIs('sik.security.dashboard'),
                    ],
                    [
                        'title' => 'Scanner SIK',
                        'icon' => 'qr_code_scanner',
                        'route' => 'sik.scanner',
                        'active' => request()->routeIs('sik.scanner'),
                    ],
                    [
                        'title' => 'Riwayat SIK',
                        'icon' => 'history',
                        'route' => 'sik.scan-logs',
                        'active' => request()->routeIs('sik.scan-logs'),
                    ],
                ]
            ];

            return $menu;
        }

        // 3. OPERATIONAL & REGULAR ROLES
        
        // --- MEETING ROOM SECTION ---
        if ($user->hasModuleAccess('meeting_room')) {
            if ($user->isGa()) {
                // GA Meeting Room (collapsible)
                $menu[] = [
                    'title' => 'Ruang Meeting',
                    'icon' => 'meeting_room',
                    'id' => 'meeting_room_menu',
                    'active' => request()->is('ga/preparations*') || request()->is('admin/rooms*') || request()->is('admin/facilities*') || request()->is('admin/dashboard*') || request()->is('admin/booking-history*') || request()->is('admin/reviews*') || request()->routeIs('calendar'),
                    'submenu' => [
                        [
                            'title' => 'Tugas GA',
                            'icon' => 'checklist_rtl',
                            'route' => 'ga.preparations',
                            'active' => request()->routeIs('ga.preparations'),
                        ],
                        [
                            'title' => 'Dashboard',
                            'icon' => 'dashboard',
                            'route' => 'admin.dashboard',
                            'active' => request()->routeIs('admin.dashboard'),
                        ],
                        [
                            'title' => 'Kalender',
                            'icon' => 'calendar_month',
                            'route' => 'calendar',
                            'active' => request()->routeIs('calendar'),
                        ],
                        [
                            'title' => 'Ruangan',
                            'icon' => 'meeting_room',
                            'route' => 'admin.rooms.index',
                            'active' => request()->routeIs('admin.rooms.*'),
                        ],
                        [
                            'title' => 'Fasilitas',
                            'icon' => 'settings_input_component',
                            'route' => 'admin.facilities.index',
                            'active' => request()->routeIs('admin.facilities.*'),
                        ],
                        [
                            'title' => 'Rating',
                            'icon' => 'grade',
                            'route' => 'admin.reviews',
                            'active' => request()->routeIs('admin.reviews'),
                        ],
                        [
                            'title' => 'Riwayat Penggunaan',
                            'icon' => 'history',
                            'route' => 'admin.booking_history',
                            'active' => request()->routeIs('admin.booking_history'),
                        ],
                    ]
                ];
            } elseif ($user->isIt()) {
                // IT Meeting Room Section (Collapsible)
                $menu[] = [
                    'title' => 'Ruang Meeting',
                    'icon' => 'meeting_room',
                    'id' => 'meeting_room_menu',
                    'active' => request()->is('it/preparations*') || request()->is('preparations*'),
                    'submenu' => [
                        [
                            'title' => 'Tugas IT',
                            'icon' => 'settings_accessibility',
                            'route' => 'it.preparations',
                            'active' => request()->routeIs('it.preparations'),
                        ]
                    ]
                ];
            } elseif ($user->isUser()) {
                // Regular User Meeting Room Section (Collapsible)
                $menu[] = [
                    'title' => 'Ruang Meeting',
                    'icon' => 'meeting_room',
                    'id' => 'meeting_room_menu',
                    'active' => request()->is('dashboard*') || request()->is('bookings*') || request()->is('calendar*') || request()->is('rooms*'),
                    'submenu' => [
                        [
                            'title' => 'Dashboard',
                            'icon' => 'dashboard',
                            'route' => 'dashboard',
                            'active' => request()->routeIs('dashboard'),
                        ],
                        [
                            'title' => 'Pesan Ruangan',
                            'icon' => 'book_online',
                            'route' => 'bookings.create',
                            'active' => request()->routeIs('bookings.create'),
                        ],
                        [
                            'title' => 'Pemesanan Saya',
                            'icon' => 'assignment',
                            'route' => 'bookings.index',
                            'active' => request()->routeIs('bookings.index') || (request()->is('bookings*') && !request()->routeIs('bookings.create')),
                        ],
                    ]
                ];
            }
        }

        // --- TOILET INSPECTION SECTION ---
        if ($user->hasModuleAccess('toilet_inspection')) {
            if ($user->isGa()) {
                // GA Toilet Inspection (collapsible)
                $menu[] = [
                    'title' => 'Inspeksi Toilet',
                    'icon' => 'wc',
                    'id' => 'toilet_inspection_menu',
                    'active' => request()->is('admin/toilet-inspection*') || request()->is('toilet-inspection*') || request()->is('inspections*') || request()->routeIs('admin.toilet_inspection.*') || request()->routeIs('toilet_inspection.*') || request()->routeIs('inspections.*'),
                    'submenu' => [
                        [
                            'title' => 'Dashboard',
                            'icon' => 'dashboard',
                            'route' => 'admin.toilet_inspection.dashboard',
                            'active' => request()->routeIs('admin.toilet_inspection.dashboard') || request()->routeIs('toilet_inspection.dashboard'),
                        ],
                        [
                            'title' => 'Inspeksi',
                            'icon' => 'analytics',
                            'route' => 'admin.toilet_inspection.inspections.index',
                            'active' => request()->routeIs('admin.toilet_inspection.inspections.index') || request()->routeIs('inspections.index'),
                        ],
                        [
                            'title' => 'Checklist',
                            'icon' => 'checklist',
                            'route' => 'admin.toilet_inspection.checklist-items.index',
                            'active' => request()->routeIs('admin.toilet_inspection.checklist-items.*'),
                        ],
                        [
                            'title' => 'Lokasi',
                            'icon' => 'map',
                            'route' => 'admin.toilet_inspection.locations.index',
                            'active' => request()->routeIs('admin.toilet_inspection.locations.*'),
                        ],
                        [
                            'title' => 'Persetujuan',
                            'icon' => 'fact_check',
                            'route' => 'admin.toilet_inspection.approvals.index',
                            'active' => request()->routeIs('admin.toilet_inspection.approvals.index') || request()->routeIs('toilet_inspection.approvals.index'),
                        ],
                        [
                            'title' => 'Laporan',
                            'icon' => 'description',
                            'route' => 'toilet_inspection.report',
                            'active' => request()->routeIs('toilet_inspection.report'),
                        ],
                    ]
                ];
            } elseif ($user->isSpv()) {
                // SPV Toilet Inspection Section (Collapsible)
                $menu[] = [
                    'title' => 'Inspeksi Toilet',
                    'icon' => 'wc',
                    'id' => 'toilet_inspection_menu',
                    'active' => request()->is('toilet-inspection*') || request()->is('inspections*') || request()->routeIs('toilet_inspection.*') || request()->routeIs('inspections.*'),
                    'submenu' => [
                        [
                            'title' => 'Dashboard Inspeksi',
                            'icon' => 'dashboard',
                            'route' => 'toilet_inspection.dashboard',
                            'active' => request()->routeIs('toilet_inspection.dashboard'),
                        ],
                        [
                            'title' => 'Inspeksi',
                            'icon' => 'analytics',
                            'route' => 'inspections.index',
                            'active' => request()->routeIs('inspections.index'),
                        ],
                        [
                            'title' => 'Persetujuan',
                            'icon' => 'fact_check',
                            'route' => 'toilet_inspection.approvals.index',
                            'active' => request()->routeIs('toilet_inspection.approvals.index'),
                        ],
                        [
                            'title' => 'Laporan',
                            'icon' => 'description',
                            'route' => 'toilet_inspection.report',
                            'active' => request()->routeIs('toilet_inspection.report'),
                        ],
                    ]
                ];
            } elseif ($user->isUser()) {
                // Regular Inspector Toilet Inspection Section (Collapsible)
                $menu[] = [
                    'title' => 'Inspeksi Toilet',
                    'icon' => 'wc',
                    'id' => 'toilet_inspection_menu',
                    'active' => request()->is('toilet-inspection*') || request()->is('inspections*') || request()->routeIs('toilet_inspection.*') || request()->routeIs('inspections.*'),
                    'submenu' => [
                        [
                            'title' => 'Dashboard Inspeksi',
                            'icon' => 'dashboard',
                            'route' => 'toilet_inspection.dashboard',
                            'active' => request()->routeIs('toilet_inspection.dashboard'),
                        ],
                        [
                            'title' => 'Inspeksi',
                            'icon' => 'analytics',
                            'route' => 'inspections.index',
                            'active' => request()->routeIs('inspections.index'),
                        ],
                        [
                            'title' => 'Mulai Inspeksi',
                            'icon' => 'add_task',
                            'route' => 'inspections.create',
                            'active' => request()->routeIs('inspections.create'),
                        ],
                    ]
                ];
            }
        }

        // --- ATK SECTION ---
        if ($user->hasModuleAccess('atk')) {
            if ($user->isGa()) {
                // GA ATK (collapsible)
                $menu[] = [
                    'title' => 'ATK',
                    'icon' => 'inventory_2',
                    'id' => 'atk_menu',
                    'active' => request()->is('atk*') || request()->routeIs('atk.*'),
                    'submenu' => [
                        [
                            'title' => 'Dashboard',
                            'icon' => 'dashboard',
                            'route' => 'atk.dashboard',
                            'active' => request()->routeIs('atk.dashboard'),
                        ],
                        [
                            'title' => 'Master ATK',
                            'icon' => 'inventory_2',
                            'route' => 'atk.items.index',
                            'active' => request()->routeIs('atk.items.*') || request()->routeIs('atk.categories.*') || request()->routeIs('atk.variants.*'),
                        ],
                        [
                            'title' => 'Stok & Log',
                            'icon' => 'inventory',
                            'route' => 'atk.stock.index',
                            'active' => request()->routeIs('atk.stock.*'),
                        ],
                        [
                            'title' => 'Request',
                            'icon' => 'fact_check',
                            'route' => 'atk.requests.index',
                            'active' => request()->routeIs('atk.requests.index') || request()->routeIs('atk.requests.show'),
                        ],
                        [
                            'title' => 'Riwayat',
                            'icon' => 'history',
                            'route' => 'atk.history.index',
                            'active' => request()->routeIs('atk.history.index'),
                        ],
                    ]
                ];
            } else {
                // Normal User / SPV / IT ATK Section (Requesters, Collapsible)
                $menu[] = [
                    'title' => 'ATK',
                    'icon' => 'inventory_2',
                    'id' => 'atk_menu',
                    'active' => request()->is('atk*') || request()->routeIs('atk.*'),
                    'submenu' => [
                        [
                            'title' => 'Katalog ATK',
                            'icon' => 'apps',
                            'route' => 'atk.catalog',
                            'active' => request()->routeIs('atk.catalog'),
                        ],
                        [
                            'title' => 'Request Saya',
                            'icon' => 'assignment',
                            'route' => 'atk.requests.user',
                            'active' => request()->routeIs('atk.requests.user') || (request()->is('atk/requests*') && !request()->routeIs('atk.catalog')),
                        ],
                    ]
                ];
            }
        }

        // --- SIK SECTION ---
        if ($user->hasModuleAccess('sik')) {
            $deptService = app(DepartmentApprovalService::class);
            $isAuthorizedApprover = $deptService->isUserAuthorizedApprover($user);

            $sikSubmenu = [];

            if ($user->isGa()) {
                // GA gets identical SIK menu to Admin, EXCEPT 'Konfigurasi Approval' which is Admin-only
                // Requester menu for GA
                $sikSubmenu[] = [
                    'title' => 'Ajukan SIK',
                    'icon' => 'post_add',
                    'route' => 'sik.create',
                    'active' => request()->routeIs('sik.create'),
                ];
                $sikSubmenu[] = [
                    'title' => 'SIK Saya',
                    'icon' => 'assignment_ind',
                    'route' => 'sik.index',
                    'active' => request()->routeIs('sik.index') || (request()->is('sik/*') && !request()->routeIs('sik.create') && !request()->is('sik-management*') && !request()->is('admin/sik*')),
                ];

                if ($user->isSikApprover() || $isAuthorizedApprover) {
                    $sikSubmenu[] = [
                        'title' => 'Persetujuan SIK',
                        'icon' => 'fact_check',
                        'route' => 'sik.approvals.index',
                        'active' => request()->routeIs('sik.approvals.*'),
                    ];
                }

                // Administrative SIK Menu for GA (matches Admin without Konfigurasi Approval)
                $sikSubmenu[] = [
                    'title' => 'Dashboard SIK',
                    'icon' => 'dashboard',
                    'route' => 'admin.sik.dashboard',
                    'active' => request()->routeIs('admin.sik.dashboard'),
                ];
                $sikSubmenu[] = [
                    'title' => 'Semua SIK',
                    'icon' => 'list_alt',
                    'route' => 'admin.sik.index',
                    'active' => request()->routeIs('admin.sik.index'),
                ];
                $sikSubmenu[] = [
                    'title' => 'Monitoring',
                    'icon' => 'corporate_fare',
                    'route' => 'sik.department.index',
                    'active' => request()->routeIs('sik.department.index') || request()->routeIs('sik.department.*'),
                ];
                $sikSubmenu[] = [
                    'title' => 'Riwayat SIK',
                    'icon' => 'history',
                    'route' => 'sik.history.index',
                    'active' => request()->routeIs('sik.history.index'),
                ];
                $sikSubmenu[] = [
                    'title' => 'Riwayat Scan',
                    'icon' => 'qr_code_scanner',
                    'route' => 'sik.scan-logs',
                    'active' => request()->routeIs('sik.scan-logs'),
                ];
                $sikSubmenu[] = [
                    'title' => 'Audit Log',
                    'icon' => 'receipt_long',
                    'route' => 'admin.sik.audit-logs',
                    'active' => request()->routeIs('admin.sik.audit-logs'),
                ];

                $menu[] = [
                    'title' => 'Surat Izin Keluar (SIK)',
                    'icon' => 'badge',
                    'id' => 'sik_menu',
                    'active' => request()->is('sik*') || request()->is('sik-management*') || request()->is('admin/sik*') || request()->routeIs('sik.*') || request()->routeIs('admin.sik.*'),
                    'submenu' => $sikSubmenu,
                ];
            } else {
                // Non-GA normal users (Employee / Department Approvers)
                $isManager = $deptService->isUserHod($user) || $deptService->isUserHodApprover($user);

                // Requester menu items always available for users with SIK module access
                $sikSubmenu[] = [
                    'title' => 'Ajukan SIK',
                    'icon' => 'post_add',
                    'route' => 'sik.create',
                    'active' => request()->routeIs('sik.create'),
                ];
                $sikSubmenu[] = [
                    'title' => 'SIK Saya',
                    'icon' => 'assignment_ind',
                    'route' => 'sik.index',
                    'active' => request()->routeIs('sik.index') || (request()->is('sik/*') && !request()->routeIs('sik.create') && !request()->is('sik.scanner')),
                ];

                if ($isAuthorizedApprover || $user->isSikApprover()) {
                    $sikSubmenu[] = [
                        'title' => 'Persetujuan SIK',
                        'icon' => 'fact_check',
                        'route' => 'sik.approvals.index',
                        'active' => request()->routeIs('sik.approvals.*'),
                    ];
                }

                if ($isManager) {
                    $sikSubmenu[] = [
                        'title' => 'Monitoring Departemen',
                        'icon' => 'corporate_fare',
                        'route' => 'sik.department.index',
                        'active' => request()->routeIs('sik.department.*'),
                    ];
                    $sikSubmenu[] = [
                        'title' => 'Riwayat Departemen',
                        'icon' => 'history',
                        'route' => 'sik.history.index',
                        'active' => request()->routeIs('sik.history.index'),
                    ];
                }

                if (!empty($sikSubmenu)) {
                    $menu[] = [
                        'title' => 'Surat Izin Keluar (SIK)',
                        'icon' => 'badge',
                        'id' => 'sik_menu',
                        'active' => request()->is('sik*') || request()->is('sik-management*') || request()->routeIs('sik.*'),
                        'submenu' => $sikSubmenu,
                    ];
                }
            }
        }

        // --- VEHICLE SECTION ---
        if ($user->hasModuleAccess('vehicle')) {
            if ($user->isGa()) {
                // GA Vehicle Management Menu
                $menu[] = [
                    'title' => 'Kendaraan',
                    'icon' => 'directions_car',
                    'id' => 'vehicle_menu',
                    'active' => request()->is('vehicles*') || request()->routeIs('vehicles.*'),
                    'submenu' => [
                        [
                            'title' => 'Dashboard Kendaraan',
                            'icon' => 'dashboard',
                            'route' => 'vehicles.dashboard',
                            'active' => request()->routeIs('vehicles.dashboard'),
                        ],
                        [
                            'title' => 'Permohonan Kendaraan',
                            'icon' => 'assignment',
                            'route' => 'vehicles.requests.index',
                            'active' => request()->routeIs('vehicles.requests.*'),
                        ],
                        [
                            'title' => 'Daftar Mobil',
                            'icon' => 'directions_car',
                            'route' => 'vehicles.index',
                            'active' => request()->routeIs('vehicles.index') || request()->routeIs('vehicles.create') || request()->routeIs('vehicles.edit') || request()->routeIs('vehicles.show'),
                        ],
                        [
                            'title' => 'Daftar Supir',
                            'icon' => 'person',
                            'route' => 'vehicles.drivers.index',
                            'active' => request()->routeIs('vehicles.drivers.*'),
                        ],
                        [
                            'title' => 'Konsumsi BBM',
                            'icon' => 'local_gas_station',
                            'route' => 'vehicles.fuel.index',
                            'active' => request()->routeIs('vehicles.fuel.*'),
                        ],
                    ]
                ];
            } else {
                // Regular Employee Vehicle Menu (Requester)
                $menu[] = [
                    'title' => 'Kendaraan',
                    'icon' => 'directions_car',
                    'id' => 'vehicle_menu',
                    'active' => request()->is('vehicles*') || request()->routeIs('vehicles.*'),
                    'submenu' => [
                        [
                            'title' => 'Permohonan Saya',
                            'icon' => 'assignment',
                            'route' => 'vehicles.requests.index',
                            'active' => request()->routeIs('vehicles.requests.*'),
                        ],
                        [
                            'title' => 'Daftar Mobil',
                            'icon' => 'directions_car',
                            'route' => 'vehicles.index',
                            'active' => request()->routeIs('vehicles.index') || request()->routeIs('vehicles.create') || request()->routeIs('vehicles.edit') || request()->routeIs('vehicles.show'),
                        ],
                    ]
                ];
            }
        }

        return $menu;
    }
}
