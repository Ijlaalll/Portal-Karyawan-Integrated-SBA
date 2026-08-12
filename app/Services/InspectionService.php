<?php

namespace App\Services;

use App\Models\Inspection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class InspectionService
{
    /**
     * Create a toilet inspection record in a database transaction.
     *
     * @param  array  $data
     * @param  \Illuminate\Http\UploadedFile|null  $photo
     * @return \App\Models\Inspection
     */
    public function createInspection(array $data, ?UploadedFile $photo = null): Inspection
    {
        return DB::transaction(function () use ($data, $photo) {
            $photoPath = null;
            if ($photo) {
                // Save photo to public storage (evidence directory)
                $photoPath = $photo->store('evidence', 'public');
            }

            $inspection = Inspection::create([
                'location_id' => $data['location_id'],
                'inspector_id' => auth()->id(),
                'status' => $data['status'] ?? 'submitted',
                'notes' => $data['notes'] ?? null,
                'photo_path' => $photoPath,
                'inspected_at' => now(),
            ]);

            // Save child checklist item results
            if (isset($data['results']) && is_array($data['results'])) {
                foreach ($data['results'] as $itemId => $resultData) {
                    $inspection->results()->create([
                        'checklist_item_id' => $itemId,
                        'is_ok' => filter_var($resultData['is_ok'], FILTER_VALIDATE_BOOLEAN),
                        'remarks' => $resultData['remarks'] ?? null,
                    ]);
                }
            }

            return $inspection;
        });
    }
}
