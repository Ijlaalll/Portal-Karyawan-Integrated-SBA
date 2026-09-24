<?php

namespace App\Services;

use App\Models\AtkVariant;
use App\Models\AtkStockMovement;
use Illuminate\Support\Facades\DB;

class AtkStockService
{
    /**
     * Perform stock adjustment based on physical stock opname.
     */
    public function adjustStock(AtkVariant $variant, int $physicalStock, string $reason, int $actorId): AtkVariant
    {
        return DB::transaction(function () use ($variant, $physicalStock, $reason, $actorId) {
            // Lock variant for update
            $variant = AtkVariant::lockForUpdate()->findOrFail($variant->id);
            
            $previousStock = $variant->stock;
            $quantity = $physicalStock - $previousStock;
            
            if ($physicalStock < 0) {
                throw new \Exception('Jumlah fisik stok tidak boleh bernilai negatif.');
            }
            
            // Only write log if there is a change
            if ($quantity !== 0) {
                $variant->stock = $physicalStock;
                $variant->save();
                
                AtkStockMovement::create([
                    'variant_id' => $variant->id,
                    'type' => 'adjustment',
                    'previous_stock' => $previousStock,
                    'quantity' => $quantity,
                    'new_stock' => $physicalStock,
                    'reason' => $reason,
                    'actor_id' => $actorId,
                ]);
            }
            
            return $variant;
        });
    }

    /**
     * Record a restock event (positive addition).
     */
    public function restock(AtkVariant $variant, int $quantity, ?string $reason, int $actorId): AtkVariant
    {
        return DB::transaction(function () use ($variant, $quantity, $reason, $actorId) {
            if ($quantity <= 0) {
                throw new \Exception('Kuantitas restock harus lebih besar dari 0.');
            }

            $variant = AtkVariant::lockForUpdate()->findOrFail($variant->id);
            $previousStock = $variant->stock;
            $newStock = $previousStock + $quantity;
            
            $variant->stock = $newStock;
            $variant->save();
            
            AtkStockMovement::create([
                'variant_id' => $variant->id,
                'type' => 'restock',
                'previous_stock' => $previousStock,
                'quantity' => $quantity,
                'new_stock' => $newStock,
                'reason' => $reason ?: 'Restock barang',
                'actor_id' => $actorId,
            ]);
            
            return $variant;
        });
    }
}
