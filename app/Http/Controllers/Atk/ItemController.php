<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkCategory;
use App\Services\AtkMasterService;
use App\Services\AuditLogService;
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

    /**
     * Download CSV template for bulk ATK item import.
     */
    public function downloadTemplate()
    {
        $this->authorizeManager();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="template_master_atk_sba.csv"',
        ];

        $columns = ['kategori', 'nama_barang', 'nama_varian', 'sku', 'satuan', 'stok_awal', 'harga_beli', 'stok_minimum', 'deskripsi'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            // Sample rows
            fputcsv($file, ['Kertas', 'Kertas HVS A4 80gr', 'Rim 80gr', 'ATK-KRT-A480', 'Rim', '50', '48000', '10', 'Kertas HVS PaperOne A4 80 gram']);
            fputcsv($file, ['Alat Tulis', 'Pulpen Gel 0.5mm', 'Hitam', 'ATK-PLP-G05B', 'Pcs', '120', '3500', '20', 'Pulpen gel tinta hitam 0.5mm']);
            fputcsv($file, ['Alat Tulis', 'Pulpen Gel 0.5mm', 'Biru', 'ATK-PLP-G05U', 'Pcs', '80', '3500', '20', 'Pulpen gel tinta biru 0.5mm']);
            fputcsv($file, ['Buku & Map', 'Ordner Bantex Folio', 'Biru', 'ATK-ORD-FBLU', 'Pcs', '30', '32000', '5', 'Ordner dokumen ukuran folio 7cm']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle bulk CSV ATK item import.
     */
    public function import(Request $request)
    {
        $this->authorizeManager();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'update_existing' => 'nullable|boolean',
        ], [
            'file.required' => 'Silakan pilih file CSV yang ingin diimpor.',
            'file.mimes' => 'File harus berformat CSV (.csv).',
            'file.max' => 'Ukuran file tidak boleh melebihi 5MB.',
        ]);

        $file = $request->file('file');
        $updateExisting = $request->boolean('update_existing', true);

        try {
            $result = $this->masterService->importItemsFromCsv($file->getRealPath(), $updateExisting);

            AuditLogService::log(
                'atk',
                'bulk_import_items',
                "Admin/GA mengimpor massal master ATK: {$result['created_variants']} varian dibuat ({$result['created_items']} barang baru, {$result['created_categories']} kategori baru), {$result['updated_variants']} diperbarui.",
                [
                    'created_variants' => $result['created_variants'],
                    'updated_variants' => $result['updated_variants'],
                    'created_items' => $result['created_items'],
                    'created_categories' => $result['created_categories'],
                    'skipped' => $result['skipped'],
                ],
                auth()->user()
            );

            $msg = "Impor master ATK berhasil: {$result['created_variants']} varian barang ditambahkan ({$result['created_items']} barang baru), {$result['updated_variants']} diperbarui.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} baris dilewati).";
            }

            if (!empty($result['errors'])) {
                $warningMsg = implode(' ', array_slice($result['errors'], 0, 3));
                if (count($result['errors']) > 3) {
                    $warningMsg .= ' dan ' . (count($result['errors']) - 3) . ' catatan lainnya.';
                }
                return redirect()->route('atk.items.index')
                    ->with('success', $msg)
                    ->with('error', 'Catatan baris: ' . $warningMsg);
            }

            return redirect()->route('atk.items.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->route('atk.items.index')->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
