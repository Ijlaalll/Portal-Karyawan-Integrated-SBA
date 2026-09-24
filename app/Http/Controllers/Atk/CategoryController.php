<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkCategory;
use App\Services\AtkMasterService;
use Illuminate\Http\Request;

class CategoryController extends Controller
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
     * Display a listing of categories.
     */
    public function index()
    {
        $this->authorizeManager();

        $categories = AtkCategory::withCount('items')->paginate(10);
        return view('atk.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $this->authorizeManager();

        return view('atk.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $this->masterService->createCategory($validated);

        return redirect()
            ->route('atk.categories.index')
            ->with('success', 'Kategori ATK berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(AtkCategory $category)
    {
        $this->authorizeManager();

        return view('atk.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, AtkCategory $category)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $this->masterService->updateCategory($category, $validated);

        return redirect()
            ->route('atk.categories.index')
            ->with('success', 'Kategori ATK berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage (restricted deletion).
     */
    public function destroy(AtkCategory $category)
    {
        $this->authorizeManager();

        try {
            $this->masterService->deleteCategory($category);
            return redirect()
                ->route('atk.categories.index')
                ->with('success', 'Kategori ATK berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('atk.categories.index')
                ->with('error', $e->getMessage());
        }
    }
}
