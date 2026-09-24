<?php

namespace App\Services;

use App\Models\CostCenter;
use Illuminate\Pagination\LengthAwarePaginator;

class CostCenterService
{
    /**
     * Retrieve paginated cost centers with search filter and aggregated metrics.
     */
    public function getPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = CostCenter::withCount('users')
            ->withSum('transactions', 'total_amount');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('cost_center_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('person_responsible', 'like', "%{$search}%")
                  ->orWhere('company_code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('cost_center_code', 'asc')->paginate($perPage);
    }

    /**
     * Create a new Cost Center.
     */
    public function create(array $data): CostCenter
    {
        return CostCenter::create($data);
    }

    /**
     * Update an existing Cost Center.
     */
    public function update(CostCenter $costCenter, array $data): bool
    {
        return $costCenter->update($data);
    }

    /**
     * Delete a Cost Center.
     */
    public function delete(CostCenter $costCenter): bool
    {
        if ($costCenter->transactions()->exists()) {
            throw new \Exception('Cost Center tidak dapat dihapus karena memiliki riwayat transaksi pengeluaran.');
        }

        return $costCenter->delete();
    }
}
