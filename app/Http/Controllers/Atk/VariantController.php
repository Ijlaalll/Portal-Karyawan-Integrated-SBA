<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkVariant;
use App\Models\AtkItem;
use App\Services\AtkMasterService;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function __construct(
        protected AtkMasterService $masterService
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
     * Display a listing of variants.
     */
    public function index()
    {
        $this->authorizeManager();

        // Redirect variants index to master items index since they are unified
        return redirect()->route('atk.items.index');
    }

    /**
     * Show the form for creating a new variant.
     */
    public function create(Request $request)
    {
        $this->authorizeManager();

        $items = AtkItem::where('is_active', true)->get();
        // Generate pre-calculated SKU for display
        $nextSku = $this->masterService->generateSku();
        $selectedItemId = $request->query('item_id');

        return view('atk.variants.create', compact('items', 'nextSku', 'selectedItemId'));
    }

    /**
     * Store a newly created variant in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'item_id' => ['required', 'integer', 'exists:atk_items,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $variant = $this->masterService->createVariant($validated);

        return redirect()
            ->route('atk.items.show', $variant->item_id)
            ->with('success', 'Varian ATK berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified variant.
     */
    public function edit(AtkVariant $variant)
    {
        $this->authorizeManager();

        $items = AtkItem::where('is_active', true)->get();
        return view('atk.variants.edit', compact('variant', 'items'));
    }

    /**
     * Update the specified variant in storage.
     */
    public function update(Request $request, AtkVariant $variant)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'item_id' => ['required', 'integer', 'exists:atk_items,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $this->masterService->updateVariant($variant, $validated);

        return redirect()
            ->route('atk.items.show', $variant->item_id)
            ->with('success', 'Varian ATK berhasil diperbarui.');
    }

    /**
     * Remove the specified variant from storage (restricted deletion).
     */
    public function destroy(AtkVariant $variant)
    {
        $this->authorizeManager();

        $itemId = $variant->item_id;
        try {
            $this->masterService->deleteVariant($variant);
            return redirect()
                ->route('atk.items.show', $itemId)
                ->with('success', 'Varian ATK berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('atk.items.show', $itemId)
                ->with('error', $e->getMessage());
        }
    }
}
