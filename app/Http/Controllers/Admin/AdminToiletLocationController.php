<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ToiletLocation;
use Illuminate\Http\Request;

class AdminToiletLocationController extends Controller
{
    /**
     * Display a listing of toilet locations.
     */
    public function index()
    {
        $locations = ToiletLocation::orderBy('building')
            ->orderBy('floor')
            ->orderBy('toilet_name')
            ->paginate(10);

        return view('admin.toilet_inspection.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new toilet location.
     */
    public function create()
    {
        return view('admin.toilet_inspection.locations.create');
    }

    /**
     * Store a newly created toilet location in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'building' => ['required', 'string', 'max:255'],
            'floor' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:255'],
            'toilet_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:pria,wanita,unisex'],
        ]);

        ToiletLocation::create($request->all());

        return redirect()->route('admin.toilet_inspection.locations.index')
            ->with('success', 'Lokasi toilet berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified toilet location.
     */
    public function edit(ToiletLocation $location)
    {
        return view('admin.toilet_inspection.locations.edit', compact('location'));
    }

    /**
     * Update the specified toilet location in storage.
     */
    public function update(Request $request, ToiletLocation $location)
    {
        $request->validate([
            'building' => ['required', 'string', 'max:255'],
            'floor' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:255'],
            'toilet_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:pria,wanita,unisex'],
            'is_active' => ['required', 'boolean'],
        ]);

        $location->update($request->all());

        return redirect()->route('admin.toilet_inspection.locations.index')
            ->with('success', 'Lokasi toilet berhasil diperbarui.');
    }

    /**
     * Remove the specified toilet location from storage.
     */
    public function destroy(ToiletLocation $location)
    {
        $location->delete();

        return redirect()->route('admin.toilet_inspection.locations.index')
            ->with('success', 'Lokasi toilet berhasil dihapus.');
    }
}
