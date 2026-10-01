<?php

namespace App\Http\Controllers\ToiletInspection;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionApprovalController extends Controller
{
    /**
     * Display a listing of pending approvals for SPV.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->isSpv() && !$user->isAdmin()) {
            abort(403, 'Akses tidak sah.');
        }

        $pendingInspections = \App\Models\Inspection::with(['location', 'inspector'])
            ->where('status', 'submitted')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('toilet_inspection.approvals.index', compact('pendingInspections'));
    }

    /**
     * Show the inspection review screen.
     */
    public function showReview(Inspection $inspection)
    {
        // Enforce SPV role access
        if (!auth()->user()->isSpv()) {
            abort(403, 'Akses tidak sah.');
        }

        if ($inspection->status !== 'submitted') {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'Inspeksi ini sudah diproses.');
        }

        $inspection->load(['location', 'inspector', 'results.checklistItem']);

        return view('toilet_inspection.approvals.review', compact('inspection'));
    }

    /**
     * Approve the specified inspection.
     */
    public function approve(Request $request, Inspection $inspection)
    {
        if (!auth()->user()->isSpv()) {
            abort(403, 'Akses tidak sah.');
        }

        if ($inspection->status !== 'submitted') {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'Inspeksi ini tidak dalam status dapat dikirim.');
        }

        DB::transaction(function () use ($inspection, $request) {
            $inspection->update(['status' => 'approved']);
            $inspection->approval()->create([
                'approver_id' => auth()->id(),
                'action' => 'approve',
                'notes' => $request->input('notes'),
                'actioned_at' => now(),
            ]);
        });

        app(\App\Services\EmailService::class)->sendToiletInspectionStatusUpdated(
            $inspection,
            'approved',
            $request->input('notes'),
            auth()->user()->name
        );

        return redirect()->route('toilet_inspection.dashboard')
            ->with('success', 'Inspeksi berhasil disetujui.');
    }

    /**
     * Reject the specified inspection.
     */
    public function reject(Request $request, Inspection $inspection)
    {
        if (!auth()->user()->isSpv()) {
            abort(403, 'Akses tidak sah.');
        }

        if ($inspection->status !== 'submitted') {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'Inspeksi ini tidak dalam status dapat dikirim.');
        }

        $request->validate([
            'notes' => ['required', 'string', 'min:5'],
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
            'notes.min' => 'Alasan penolakan harus minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($inspection, $request) {
            $inspection->update(['status' => 'rejected']);
            $inspection->approval()->create([
                'approver_id' => auth()->id(),
                'action' => 'reject',
                'notes' => $request->input('notes'),
                'actioned_at' => now(),
            ]);
        });

        app(\App\Services\EmailService::class)->sendToiletInspectionStatusUpdated(
            $inspection,
            'rejected',
            $request->input('notes'),
            auth()->user()->name
        );

        return redirect()->route('toilet_inspection.dashboard')
            ->with('success', 'Inspeksi berhasil ditolak.');
    }
}
