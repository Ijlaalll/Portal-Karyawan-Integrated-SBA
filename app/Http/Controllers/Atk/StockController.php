<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkVariant;
use App\Models\AtkStockMovement;
use App\Services\AtkStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function __construct(
        protected AtkStockService $stockService
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

    /**
     * Display a list of variant stocks.
     */
    public function index(Request $request)
    {
        $this->authorizeManager();

        $search = $request->query('search');

        $query = AtkVariant::with('item.category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $variants = $query->paginate(10);

        return view('atk.stock.index', compact('variants', 'search'));
    }

    /**
     * Handle manual stock adjustment (opname).
     */
    public function adjust(Request $request, AtkVariant $variant)
    {
        $this->authorizeManager();

        $request->validate([
            'physical_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->stockService->adjustStock(
                $variant,
                $request->input('physical_stock'),
                $request->input('reason'),
                Auth::id()
            );

            return redirect()
                ->route('atk.stock.index')
                ->with('success', "Stok untuk varian {$variant->name} berhasil disesuaikan.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the stock movement history.
     */
    public function history(Request $request)
    {
        $this->authorizeManager();

        $movements = AtkStockMovement::with(['variant.item', 'actor'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('atk.stock.history', compact('movements'));
    }
}
