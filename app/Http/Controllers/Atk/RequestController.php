<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkRequest;
use App\Services\AtkRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function __construct(
        protected AtkRequestService $requestService
    ) {}

    protected function authorizeManager()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return;
        }
        if ($user->isGa() && $user->hasModuleAccess('atk')) {
            return;
        }
        abort(403, 'Akses ditolak. Diperlukan hak akses Manajemen ATK.');
    }

    protected function authorizeOperator()
    {
        $user = auth()->user();
        if ($user->isGa() && $user->hasModuleAccess('atk')) {
            return;
        }
        abort(403, 'Akses ditolak. Hanya operator GA yang dapat memproses request.');
    }

    /**
     * Display active requests (Diajukan, Disetujui, Diproses).
     */
    public function index(Request $request)
    {
        $this->authorizeManager();

        $status = $request->query('status');

        $query = AtkRequest::with(['user', 'items.variant.item']);

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['diajukan', 'disetujui', 'diproses']);
        }

        $requests = $query->orderBy('created_at', 'asc')->paginate(10);

        return view('atk.requests.index', compact('requests', 'status'));
    }

    /**
     * Display a specific request and its timeline history.
     */
    public function show(AtkRequest $atkRequest)
    {
        $this->authorizeManager();

        $atkRequest->load(['user', 'items.variant.item', 'histories.actor', 'approver', 'processor', 'completer', 'rejecter', 'canceller']);

        $request = $atkRequest;

        return view('atk.requests.show', compact('request'));
    }

    /**
     * Approve a request.
     */
    public function approve(AtkRequest $atkRequest)
    {
        $this->authorizeOperator();

        try {
            $this->requestService->approveRequest($atkRequest->id, Auth::id());
            return redirect()
                ->route('atk.requests.show', $atkRequest->id)
                ->with('success', 'Permintaan ATK berhasil disetujui.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a request.
     */
    public function reject(Request $formRequest, AtkRequest $atkRequest)
    {
        $this->authorizeOperator();

        $formRequest->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->requestService->rejectRequest(
                $atkRequest->id,
                $formRequest->input('rejection_reason'),
                Auth::id()
            );
            return redirect()
                ->route('atk.requests.show', $atkRequest->id)
                ->with('success', 'Permintaan ATK berhasil ditolak.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process a request (handover and deduct stock safely).
     */
    public function process(AtkRequest $atkRequest)
    {
        $this->authorizeOperator();

        try {
            $this->requestService->processRequest($atkRequest->id, Auth::id());
            return redirect()
                ->route('atk.requests.show', $atkRequest->id)
                ->with('success', 'Permintaan ATK berhasil diproses dan stok telah dipotong.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Complete a request.
     */
    public function complete(AtkRequest $atkRequest)
    {
        $this->authorizeOperator();

        try {
            $this->requestService->completeRequest($atkRequest->id, Auth::id());
            return redirect()
                ->route('atk.requests.show', $atkRequest->id)
                ->with('success', 'Permintaan ATK telah diselesaikan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display historical requests (Selesai, Ditolak, Dibatalkan).
     */
    public function historyIndex()
    {
        $this->authorizeManager();

        $requests = AtkRequest::whereIn('status', ['selesai', 'ditolak', 'dibatalkan'])
            ->with(['user', 'items.variant.item'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('atk.requests.history', compact('requests'));
    }
}
