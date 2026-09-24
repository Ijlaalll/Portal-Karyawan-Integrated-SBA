<?php

namespace App\Services;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkVariant;
use Illuminate\Support\Facades\DB;

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
        return AtkItem::create($data);
    }

    /**
     * Update an existing item.
     */
    public function updateItem(AtkItem $item, array $data): AtkItem
    {
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
}
