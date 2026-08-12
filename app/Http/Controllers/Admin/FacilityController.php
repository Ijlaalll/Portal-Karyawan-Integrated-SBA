<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Services\FacilityService;

class FacilityController extends Controller
{
    public function __construct(
        protected FacilityService $facilityService
    ) {}

    /**
     * Display a listing of facilities.
     */
    public function index()
    {
        $facilities = Facility::paginate(10);
        return view('admin.facilities.index', compact('facilities'));
    }

    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        return view('admin.facilities.create');
    }

    /**
     * Store a newly created facility in storage.
     */
    public function store(StoreFacilityRequest $request)
    {
        $this->facilityService->createFacility($request->validated());

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Facility created successfully.');
    }

    /**
     * Show the form for editing the specified facility.
     */
    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    /**
     * Update the specified facility in storage.
     */
    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $this->facilityService->updateFacility($facility, $request->validated());

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified facility from storage.
     */
    public function destroy(Facility $facility)
    {
        $this->facilityService->deleteFacility($facility);

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }
}
