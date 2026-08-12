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
            abort(403, 'Unauthorized.');
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
            abort(403, 'Unauthorized access.');
        }

        if ($inspection->status !== 'submitted') {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'This inspection has already been processed.');
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
            abort(403, 'Unauthorized access.');
        }

        if ($inspection->status !== 'submitted') {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'This inspection is not in a submittable state.');
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

        return redirect()->route('toilet_inspection.dashboard')
            ->with('success', 'Inspection approved successfully.');
    }

    /**
     * Reject the specified inspection.
     */
    public function reject(Request $request, Inspection $inspection)
    {
        if (!auth()->user()->isSpv()) {
            abort(403, 'Unauthorized access.');
        }

        if ($inspection->status !== 'submitted') {
            return redirect()->route('toilet_inspection.dashboard')
                ->with('error', 'This inspection is not in a submittable state.');
        }

        $request->validate([
            'notes' => ['required', 'string', 'min:5'],
        ], [
            'notes.required' => 'Rejection reason is required.',
            'notes.min' => 'Rejection reason must be at least 5 characters.',
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

        return redirect()->route('toilet_inspection.dashboard')
            ->with('success', 'Inspection rejected successfully.');
    }
}
