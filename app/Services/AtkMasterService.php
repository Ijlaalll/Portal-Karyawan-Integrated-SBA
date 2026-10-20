<?php

namespace App\Services;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkVariant;
use App\Models\AtkStockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class AtkMasterService
{
    /**
     * Create a new category.
     */
    public function createCategory(array $data): AtkCategory
    {
        return AtkCategory::create($data);
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(AtkCategory $category, array $data): AtkCategory
    {
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category (restricted deletion).
     */
    public function deleteCategory(AtkCategory $category): void
    {
        if ($category->items()->exists()) {
            throw new \Exception('Kategori tidak dapat dihapus karena masih memiliki barang. Silakan nonaktifkan kategori ini.');
        }
        $category->delete();
    }

    /**
     * Create a new item.
     */
    public function createItem(array $data): AtkItem
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('atk_items', 'public');
        }

        return AtkItem::create($data);
    }

    /**
     * Update an existing item.
     */
    public function updateItem(AtkItem $item, array $data): AtkItem
    {
        // Handle image removal request
        if (!empty($data['remove_image'])) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = null;
        } elseif (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $data['image']->store('atk_items', 'public');
        } else {
            unset($data['image']);
        }

        unset($data['remove_image']);
        $item->update($data);
        return $item;
    }

    /**
     * Delete an item (restricted deletion).
     */
    public function deleteItem(AtkItem $item): void
    {
        if ($item->variants()->exists()) {
            throw new \Exception('Barang tidak dapat dihapus karena masih memiliki varian. Silakan nonaktifkan barang ini.');
        }

        // Clean up image file if exists
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();
    }

    /**
     * Create a new variant (with auto-generated SKU).
     */
    public function createVariant(array $data): AtkVariant
    {
        return DB::transaction(function () use ($data) {
            $attempts = 0;
            while ($attempts < 5) {
                try {
                    $data['sku'] = $this->generateSku();
                    return AtkVariant::create($data);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Check duplicate entry error (SQL state 23000)
                    if ($e->getCode() == '23000') {
                        $attempts++;
                        continue;
                    }
                    throw $e;
                }
            }
            throw new \Exception('Gagal membuat SKU unik setelah beberapa percobaan.');
        });
    }

    /**
     * Update an existing variant (SKU is readonly/ignored on update).
     */
    public function updateVariant(AtkVariant $variant, array $data): AtkVariant
    {
        // Exclude SKU from being modified on update
        unset($data['sku']);
        $variant->update($data);
        return $variant;
    }

    /**
     * Delete a variant (restricted deletion).
     */
    public function deleteVariant(AtkVariant $variant): void
    {
        if ($variant->requestItems()->exists() || $variant->stockMovements()->exists()) {
            throw new \Exception('Varian tidak dapat dihapus karena memiliki riwayat transaksi/stok. Silakan nonaktifkan varian ini.');
        }
        $variant->delete();
    }

    /**
     * Generate unique SKU (ATK-XXXXX).
     */
    public function generateSku(): string
    {
        $lastId = DB::table('atk_variants')->max('id') ?? 0;
        $nextId = $lastId + 1;
        return 'ATK-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Import ATK items, categories, and variants in bulk from a CSV file.
     *
     * @param string $filePath
     * @param bool $updateExisting
     * @return array{created_variants: int, updated_variants: int, created_items: int, created_categories: int, skipped: int, errors: array<string>}
     */
    public function importItemsFromCsv(string $filePath, bool $updateExisting = true): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Tidak dapat membuka file CSV.');
        }

        // Auto-detect delimiter (, or ;) and support optional sep= directive
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (preg_match('/^sep=([,;\t])/i', trim((string) $firstLine), $matches)) {
            $delimiter = $matches[1];
            fgets($handle); // discard the sep= line so next line is the header
        } else {
            if (strpos((string) $firstLine, ';') !== false && substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',')) {
                $delimiter = ';';
            } elseif (strpos((string) $firstLine, "\t") !== false && substr_count((string) $firstLine, "\t") > substr_count((string) $firstLine, ',')) {
                $delimiter = "\t";
            }
        }

        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (!$rawHeader) {
            fclose($handle);
            throw new \RuntimeException('File CSV kosong atau format tidak valid.');
        }

        // Clean UTF-8 BOM
        $rawHeader[0] = preg_replace('/[\x{FEFF}\x{EF}\x{BB}\x{BF}]/u', '', $rawHeader[0]);

        $headerMap = [];
        foreach ($rawHeader as $idx => $col) {
            $cleanCol = strtolower(trim($col));
            if (in_array($cleanCol, ['category', 'kategori', 'nama_kategori'])) {
                $headerMap['category'] = $idx;
            } elseif (in_array($cleanCol, ['item_name', 'nama_barang', 'barang', 'nama'])) {
                $headerMap['item_name'] = $idx;
            } elseif (in_array($cleanCol, ['variant_name', 'nama_varian', 'varian'])) {
                $headerMap['variant_name'] = $idx;
            } elseif (in_array($cleanCol, ['sku', 'kode_barang', 'kode', 'barcode'])) {
                $headerMap['sku'] = $idx;
            } elseif (in_array($cleanCol, ['unit', 'satuan'])) {
                $headerMap['unit'] = $idx;
            } elseif (in_array($cleanCol, ['stock', 'stok', 'stok_awal', 'jumlah', 'qty'])) {
                $headerMap['stock'] = $idx;
            } elseif (in_array($cleanCol, ['purchase_price', 'harga_beli', 'harga'])) {
                $headerMap['purchase_price'] = $idx;
            } elseif (in_array($cleanCol, ['minimum_stock', 'stok_minimum', 'min_stok', 'stok_min'])) {
                $headerMap['minimum_stock'] = $idx;
            } elseif (in_array($cleanCol, ['description', 'deskripsi', 'keterangan'])) {
                $headerMap['description'] = $idx;
            }
        }

        if (!isset($headerMap['item_name'])) {
            fclose($handle);
            throw new \RuntimeException('Format CSV tidak valid. Pastikan terdapat kolom nama_barang.');
        }

        $createdVariants = 0;
        $updatedVariants = 0;
        $createdItems = 0;
        $createdCategories = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        $actorId = auth()->id() ?? User::where('role', 'admin')->value('id') ?? 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                if (empty(array_filter($row))) {
                    continue;
                }

                $categoryName = isset($headerMap['category']) && isset($row[$headerMap['category']]) ? trim($row[$headerMap['category']]) : 'Umum';
                if (empty($categoryName)) {
                    $categoryName = 'Umum';
                }

                $itemName = isset($headerMap['item_name']) && isset($row[$headerMap['item_name']]) ? trim($row[$headerMap['item_name']]) : null;
                $variantName = isset($headerMap['variant_name']) && isset($row[$headerMap['variant_name']]) ? trim($row[$headerMap['variant_name']]) : 'Standar';
                if (empty($variantName)) {
                    $variantName = 'Standar';
                }

                $sku = isset($headerMap['sku']) && isset($row[$headerMap['sku']]) ? trim(ltrim(trim($row[$headerMap['sku']]), "'\"")) : null;
                if ($sku === '') {
                    $sku = null;
                }
                $unit = isset($headerMap['unit']) && isset($row[$headerMap['unit']]) ? trim($row[$headerMap['unit']]) : 'Pcs';
                if (empty($unit)) {
                    $unit = 'Pcs';
                }

                $rawStock = isset($headerMap['stock']) && isset($row[$headerMap['stock']]) ? trim($row[$headerMap['stock']]) : '0';
                $stock = max(0, (int) preg_replace('/[^\d]/', '', $rawStock));

                $rawPrice = isset($headerMap['purchase_price']) && isset($row[$headerMap['purchase_price']]) ? trim($row[$headerMap['purchase_price']]) : '0';
                $cleanPrice = preg_replace('/[^\d.,]/', '', $rawPrice);
                if (strpos($cleanPrice, '.') !== false && strpos($cleanPrice, ',') !== false) {
                    $cleanPrice = str_replace('.', '', $cleanPrice);
                    $cleanPrice = str_replace(',', '.', $cleanPrice);
                } elseif (strpos($cleanPrice, '.') !== false && substr_count($cleanPrice, '.') === 1 && strlen(substr($cleanPrice, strpos($cleanPrice, '.') + 1)) === 3) {
                    $cleanPrice = str_replace('.', '', $cleanPrice);
                } else {
                    $cleanPrice = str_replace(',', '.', $cleanPrice);
                }
                $purchasePrice = max(0, (float) $cleanPrice);

                $rawMinStock = isset($headerMap['minimum_stock']) && isset($row[$headerMap['minimum_stock']]) ? trim($row[$headerMap['minimum_stock']]) : '5';
                $minimumStock = max(0, (int) preg_replace('/[^\d]/', '', $rawMinStock));
                $description = isset($headerMap['description']) && isset($row[$headerMap['description']]) ? trim($row[$headerMap['description']]) : null;

                if (!$itemName) {
                    $errors[] = "Baris {$rowNumber}: Nama barang wajib diisi.";
                    $skipped++;
                    continue;
                }

                // 1. Category find or create
                $category = AtkCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['description' => 'Kategori hasil impor massal ATK', 'is_active' => true]
                );
                if ($category->wasRecentlyCreated) {
                    $createdCategories++;
                }

                // 2. Item find or create
                $item = AtkItem::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $itemName],
                    ['description' => $description, 'is_active' => true]
                );
                if ($item->wasRecentlyCreated) {
                    $createdItems++;
                }

                // 3. Variant find or create
                $variant = AtkVariant::where('item_id', $item->id)
                    ->where('name', $variantName)
                    ->first();

                if (!$variant && !empty($sku)) {
                    $variant = AtkVariant::where('sku', $sku)->first();
                }

                if ($variant) {
                    if ($updateExisting) {
                        $oldStock = $variant->stock;
                        $variant->unit = $unit;
                        $variant->purchase_price = $purchasePrice;
                        $variant->minimum_stock = $minimumStock;
                        if (!empty($sku)) {
                            $variant->sku = $sku;
                        }

                        if ($stock !== $oldStock) {
                            $variant->stock = $stock;
                            AtkStockMovement::create([
                                'variant_id' => $variant->id,
                                'type' => 'adjustment',
                                'previous_stock' => $oldStock,
                                'quantity' => abs($stock - $oldStock),
                                'new_stock' => $stock,
                                'reason' => 'Penyesuaian stok dari impor massal ATK',
                                'actor_id' => $actorId,
                            ]);
                        }

                        $variant->save();
                        $updatedVariants++;
                    } else {
                        $skipped++;
                    }
                } else {
                    if (empty($sku)) {
                        $sku = $this->generateSku();
                    }

                    $newVariant = AtkVariant::create([
                        'item_id' => $item->id,
                        'name' => $variantName,
                        'sku' => $sku,
                        'unit' => $unit,
                        'purchase_price' => $purchasePrice,
                        'stock' => $stock,
                        'minimum_stock' => $minimumStock,
                        'is_active' => true,
                    ]);

                    if ($stock > 0) {
                        AtkStockMovement::create([
                            'variant_id' => $newVariant->id,
                            'type' => 'restock',
                            'previous_stock' => 0,
                            'quantity' => $stock,
                            'new_stock' => $stock,
                            'reason' => 'Saldo awal impor massal ATK',
                            'actor_id' => $actorId,
                        ]);
                    }

                    $createdVariants++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);

        return [
            'created_variants' => $createdVariants,
            'updated_variants' => $updatedVariants,
            'created_items' => $createdItems,
            'created_categories' => $createdCategories,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
