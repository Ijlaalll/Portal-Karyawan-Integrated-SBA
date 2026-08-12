<?php

namespace App\Services;

use App\Models\Facility;

class FacilityService
{
    /**
     * Create a new facility.
     */
    public function createFacility(array $data): Facility
    {
        return Facility::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update an existing facility.
     */
    public function updateFacility(Facility $facility, array $data): Facility
    {
        $facility->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $facility;
    }

    /**
     * Delete an existing facility.
     */
    public function deleteFacility(Facility $facility): bool
    {
        return $facility->delete();
    }
}
