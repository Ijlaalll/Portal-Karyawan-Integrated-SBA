<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkCategory;
use App\Services\AtkMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
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
     * Display a listing of items.
     */
    public function index()
    {
        $this->authorizeManager();

        $items = AtkItem::with(['category', 'variants'])->paginate(10);
        return view('atk.items.index', compact('items'));
    }

    /**
     * Show the specified item details and variants.
     */
    public function show(AtkItem $item)
    {
        $this->authorizeManager();

        $item->load(['category', 'variants']);
        return view('atk.items.show', compact('item'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        $this->authorizeManager();

        $categories = AtkCategory::where('is_active', true)->get();
        return view('atk.items.create', compact('categories'));
    }

    /**
     * Store a newly created item in storage (including inline category and variants).
     */
    public function store(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'required_without:new_category_name', 'exists:atk_categories,id'],
            'new_category_name' => ['nullable', 'string', 'max:255', 'required_without:category_id'],
            'new_category_description' => ['nullable', 'string', 'max:1000'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.unit' => ['required', 'string', 'max:100'],
            'variants.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.minimum_stock' => ['required', 'integer', 'min:0'],
            'variants.*.is_active' => ['required', 'boolean'],
        ]);

        $item = DB::transaction(function() use ($request) {
            // 1. Create Category inline if provided
            if ($request->filled('new_category_name')) {
                $category = $this->masterService->createCategory([
                    'name' => $request->input('new_category_name'),
                    'description' => $request->input('new_category_description'),
                    'is_active' => true,
                ]);
                $categoryId = $category->id;
            } else {
                $categoryId = $request->input('category_id');
            }

            // 2. Create the Item
            $item = $this->masterService->createItem([
                'category_id' => $categoryId,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'is_active' => $request->input('is_active'),
            ]);

            // 3. Create all initial variants
            if ($request->has('variants')) {
                foreach ($request->input('variants') as $varData) {
                    $this->masterService->createVariant([
                        'item_id' => $item->id,
                        'name' => $varData['name'],
                        'unit' => $varData['unit'],
                        'purchase_price' => $varData['purchase_price'],
                        'stock' => $varData['stock'],
                        'minimum_stock' => $varData['minimum_stock'],
                        'is_active' => $varData['is_active'],
                    ]);
                }
            }

            return $item;
        });

        return redirect()
            ->route('atk.items.show', $item)
            ->with('success', 'Barang ATK dan variasi awalnya berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(AtkItem $item)
    {
        $this->authorizeManager();

        $categories = AtkCategory::where('is_active', true)->get();
        return view('atk.items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, AtkItem $item)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:atk_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $this->masterService->updateItem($item, $validated);

        return redirect()
            ->route('atk.items.show', $item)
            ->with('success', 'Barang ATK berhasil diperbarui.');
    }

    /**
     * Remove the specified item from storage (restricted deletion).
     */
    public function destroy(AtkItem $item)
    {
        $this->authorizeManager();

        try {
            $this->masterService->deleteItem($item);
            return redirect()
                ->route('atk.items.index')
                ->with('success', 'Barang ATK berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('atk.items.index')
                ->with('error', $e->getMessage());
        }
    }
}
