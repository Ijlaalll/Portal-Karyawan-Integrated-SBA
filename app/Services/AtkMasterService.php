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
     * Import ATK items from either CSV or Excel (.xlsx) file.
     *
     * @param string $filePath
     * @param bool $updateExisting
     * @param string|null $extension
     * @return array{created_variants: int, updated_variants: int, created_items: int, created_categories: int, skipped: int, errors: array<string>}
     */
    public function importItemsFromFile(string $filePath, bool $updateExisting = true, ?string $extension = null): array
    {
        $rows = $this->extractRowsFromFile($filePath, $extension);
        if (empty($rows)) {
            throw new \RuntimeException('File kosong atau format tidak valid.');
        }

        $rawHeader = array_shift($rows);
        if (empty($rawHeader)) {
            throw new \RuntimeException('Header file tidak ditemukan.');
        }

        // Clean UTF-8 BOM if present
        $rawHeader[0] = preg_replace('/[\x{FEFF}\x{EF}\x{BB}\x{BF}]/u', '', (string) $rawHeader[0]);

        $headerMap = [];
        foreach ($rawHeader as $idx => $col) {
            $cleanCol = strtolower(trim((string) $col));
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
            throw new \RuntimeException('Format file tidak valid. Pastikan terdapat kolom nama_barang.');
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
            foreach ($rows as $row) {
                $rowNumber++;
                if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                    continue;
                }

                $categoryName = isset($headerMap['category']) && isset($row[$headerMap['category']]) ? trim((string)$row[$headerMap['category']]) : 'Umum';
                if (empty($categoryName)) {
                    $categoryName = 'Umum';
                }

                $itemName = isset($headerMap['item_name']) && isset($row[$headerMap['item_name']]) ? trim((string)$row[$headerMap['item_name']]) : null;
                $variantName = isset($headerMap['variant_name']) && isset($row[$headerMap['variant_name']]) ? trim((string)$row[$headerMap['variant_name']]) : 'Standar';
                if (empty($variantName)) {
                    $variantName = 'Standar';
                }

                $sku = isset($headerMap['sku']) && isset($row[$headerMap['sku']]) ? trim(ltrim(trim((string)$row[$headerMap['sku']]), "'\"")) : null;
                if ($sku === '') {
                    $sku = null;
                }
                $unit = isset($headerMap['unit']) && isset($row[$headerMap['unit']]) ? trim((string)$row[$headerMap['unit']]) : 'Pcs';
                if (empty($unit)) {
                    $unit = 'Pcs';
                }

                $rawStock = isset($headerMap['stock']) && isset($row[$headerMap['stock']]) ? trim((string)$row[$headerMap['stock']]) : '0';
                $stock = max(0, (int) preg_replace('/[^\d]/', '', $rawStock));

                $rawPrice = isset($headerMap['purchase_price']) && isset($row[$headerMap['purchase_price']]) ? trim((string)$row[$headerMap['purchase_price']]) : '0';
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

                $rawMinStock = isset($headerMap['minimum_stock']) && isset($row[$headerMap['minimum_stock']]) ? trim((string)$row[$headerMap['minimum_stock']]) : '5';
                $minimumStock = max(0, (int) preg_replace('/[^\d]/', '', $rawMinStock));
                $description = isset($headerMap['description']) && isset($row[$headerMap['description']]) ? trim((string)$row[$headerMap['description']]) : null;

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
            throw $e;
        }

        return [
            'created_variants' => $createdVariants,
            'updated_variants' => $updatedVariants,
            'created_items' => $createdItems,
            'created_categories' => $createdCategories,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Backward-compatible alias for CSV import.
     */
    public function importItemsFromCsv(string $filePath, bool $updateExisting = true): array
    {
        return $this->importItemsFromFile($filePath, $updateExisting, 'csv');
    }

    /**
     * Extract tabular data from file (supports CSV, XLSX).
     */
    protected function extractRowsFromFile(string $filePath, ?string $extension = null): array
    {
        $ext = strtolower($extension ?: pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'xlsx' || $ext === 'xls') {
            try {
                return $this->extractRowsFromXlsx($filePath);
            } catch (\Throwable $e) {
                if ($ext === 'xls') {
                    throw new \RuntimeException('File .xls tidak dapat diproses secara langsung. Harap simpan file sebagai format Microsoft Excel (.xlsx) atau CSV (.csv).');
                }
                throw $e;
            }
        }

        return $this->extractRowsFromCsv($filePath);
    }

    /**
     * Extract rows natively from .xlsx using ZipArchive & SimpleXML.
     */
    protected function extractRowsFromXlsx(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('File Excel tidak valid atau rusak.');
        }

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $str = '';
                        foreach ($si->r as $r) {
                            $str .= (string) $r->t;
                        }
                        $sharedStrings[] = $str;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Find primary sheet
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetContent === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (preg_match('#xl/worksheets/sheet\d+\.xml#', $entry)) {
                    $sheetContent = $zip->getFromName($entry);
                    break;
                }
            }
        }

        if ($sheetContent === false) {
            $zip->close();
            throw new \RuntimeException('Lembar kerja (sheet) tidak ditemukan di dalam file Excel.');
        }

        $sheetXml = simplexml_load_string($sheetContent);
        $rows = [];
        if ($sheetXml && isset($sheetXml->sheetData->row)) {
            foreach ($sheetXml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $c) {
                    preg_match('/^([A-Z]+)/', (string) $c['r'], $m);
                    $colLetters = $m[1] ?? 'A';
                    $colIdx = 0;
                    for ($i = 0; $i < strlen($colLetters); $i++) {
                        $colIdx = $colIdx * 26 + (ord($colLetters[$i]) - 64);
                    }
                    $colIdx -= 1;

                    $type = (string) $c['t'];
                    $val = (string) $c->v;
                    if ($type === 's') {
                        $val = $sharedStrings[(int) $val] ?? '';
                    } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                        $val = (string) $c->is->t;
                    }
                    $rowData[$colIdx] = $val;
                }

                $maxCol = empty($rowData) ? -1 : max(array_keys($rowData));
                $denseRow = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $denseRow[$i] = $rowData[$i] ?? '';
                }
                if (!empty(array_filter($denseRow, fn($v) => trim((string)$v) !== ''))) {
                    $rows[] = $denseRow;
                }
            }
        }
        $zip->close();

        return $rows;
    }

    /**
     * Extract rows from CSV file with automatic delimiter detection.
     */
    protected function extractRowsFromCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Tidak dapat membuka file CSV.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (preg_match('/^sep=([,;\t])/i', trim((string) $firstLine), $matches)) {
            $delimiter = $matches[1];
            fgets($handle);
        } else {
            if (strpos((string) $firstLine, ';') !== false && substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',')) {
                $delimiter = ';';
            } elseif (strpos((string) $firstLine, "\t") !== false && substr_count((string) $firstLine, "\t") > substr_count((string) $firstLine, ',')) {
                $delimiter = "\t";
            }
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        return $rows;
    }
}
