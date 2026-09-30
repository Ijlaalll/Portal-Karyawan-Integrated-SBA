<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Display a paginated listing of system audit logs with filters and summary metrics.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $period = $request->input('period', 'all');
        $module = $request->input('module', 'all');
        $action = $request->input('action', 'all');

        $query = AuditLog::query()
            ->search($search)
            ->filterPeriod($period)
            ->filterModule($module)
            ->filterAction($action)
            ->latest('id');

        $logs = $query->paginate(25)->withQueryString();

        // Metrics for summary cards
        $now = now();
        $totalLogs = AuditLog::count();
        $todayLogins = AuditLog::whereDate('created_at', $now->toDateString())
            ->where('action', 'login')
            ->count();
        $todayActions = AuditLog::whereDate('created_at', $now->toDateString())
            ->whereIn('action', ['create', 'update', 'delete', 'approve', 'reject', 'scan'])
            ->count();
        $uniqueIps = AuditLog::whereNotNull('ip_address')
            ->distinct('ip_address')
            ->count('ip_address');

        $availableModules = [
            'all' => 'Semua Modul',
            'auth' => 'Autentikasi (Login/Logout)',
            'meeting_room' => 'Ruang Meeting',
            'sik' => 'Surat Izin Keluar (SIK)',
            'atk' => 'Manajemen ATK',
            'vehicle' => 'Manajemen Kendaraan',
            'toilet_inspection' => 'Inspeksi Toilet',
            'settings' => 'Pengaturan Sistem',
        ];

        $availableActions = [
            'all' => 'Semua Aksi',
            'login' => 'Login Berhasil',
            'logout' => 'Logout',
            'login_failed' => 'Gagal Login',
            'create' => 'Tambah / Buat Data',
            'update' => 'Ubah Data',
            'delete' => 'Hapus Data',
            'approve' => 'Persetujuan (Approve)',
            'reject' => 'Penolakan (Reject)',
            'scan' => 'Scan Pos Security',
        ];

        return view('admin.settings.audit_logs.index', compact(
            'logs',
            'totalLogs',
            'todayLogins',
            'todayActions',
            'uniqueIps',
            'availableModules',
            'availableActions',
            'search',
            'period',
            'module',
            'action'
        ));
    }

    /**
     * Display the specified audit log entry (JSON or view).
     */
    public function show(AuditLog $auditLog, Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $auditLog,
            ]);
        }

        return view('admin.settings.audit_logs.show', compact('auditLog'));
    }

    /**
     * Export filtered audit logs to CSV for official IT audit inspection.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $period = $request->input('period', 'all');
        $module = $request->input('module', 'all');
        $action = $request->input('action', 'all');

        $query = AuditLog::query()
            ->search($search)
            ->filterPeriod($period)
            ->filterModule($module)
            ->filterAction($action)
            ->latest('id');

        $filename = 'audit-trail-sba-' . now()->format('Ymd-His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Insert UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'ID Log',
                'Waktu & Tanggal',
                'Nama Pengguna',
                'Email',
                'Peran (Role)',
                'Modul',
                'Aksi',
                'Deskripsi Aktivitas',
                'Alamat IP',
                'User Agent / Perangkat',
                'Detail Metadata (JSON)',
            ]);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user_name,
                        $log->user_email,
                        strtoupper($log->user_role ?: '-'),
                        $log->module,
                        strtoupper($log->action),
                        $log->description,
                        $log->ip_address ?: '-',
                        $log->user_agent ?: '-',
                        $log->details ? json_encode($log->details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '-',
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
