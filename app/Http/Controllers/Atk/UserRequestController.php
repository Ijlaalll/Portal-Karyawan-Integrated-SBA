<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkCategory;
use App\Models\AtkVariant;
use App\Models\AtkRequest;
use App\Services\AtkRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRequestController extends Controller
{
    public function __construct(
        protected AtkRequestService $requestService
    ) {}

    /**
     * Display the ATK catalog page.
     */
    public function catalog(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        // Fetch active categories
        $categories = AtkCategory::where('is_active', true)->get();

        // Fetch active variants belonging to active items and categories
        $query = AtkVariant::where('is_active', true)
            ->whereHas('item', function ($q) {
                $q->where('is_active', true)
                  ->whereHas('category', function ($sub) {
                      $sub->where('is_active', true);
                  });
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($categoryId) {
            $query->whereHas('item', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $variants = $query->with('item.category')->paginate(12);

        return view('atk.user.catalog', compact('categories', 'variants', 'search', 'categoryId'));
    }

    /**
     * Display the user's own requests.
     */
    public function myRequests()
    {
        $requests = AtkRequest::where('user_id', Auth::id())
            ->with(['items.variant.item', 'histories.actor'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('atk.user.requests', compact('requests'));
    }

    /**
     * Submit a new ATK request.
     */
    public function storeRequest(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.variant_id' => ['required', 'integer', 'exists:atk_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->requestService->createRequest(
                Auth::id(),
                $request->input('items'),
                $request->input('note')
            );

            return redirect()
                ->route('atk.requests.user')
                ->with('success', 'Permintaan ATK Anda berhasil diajukan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a pending request.
     */
    public function cancelRequest(Request $request, AtkRequest $atkRequest)
    {
        if (Auth::user()->cannot('cancel', $atkRequest)) {
            abort(403, 'Anda tidak berwenang membatalkan permintaan ini.');
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->requestService->cancelRequest(
                $atkRequest->id,
                $request->input('cancellation_reason'),
                Auth::id()
            );

            return redirect()
                ->route('atk.requests.user')
                ->with('success', 'Permintaan ATK berhasil dibatalkan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
