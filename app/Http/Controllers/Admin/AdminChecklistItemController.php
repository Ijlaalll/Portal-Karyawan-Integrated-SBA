<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;

class AdminChecklistItemController extends Controller
{
    /**
     * Display a listing of checklist items.
     */
    public function index()
    {
        $items = ChecklistItem::orderBy('name')
            ->paginate(10);

        return view('admin.toilet_inspection.checklist_items.index', compact('items'));
    }

    /**
     * Show the form for creating a new checklist item.
     */
    public function create()
    {
        return view('admin.toilet_inspection.checklist_items.create');
    }

    /**
     * Store a newly created checklist item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:checklist_items,name'],
            'description' => ['nullable', 'string'],
        ]);

        ChecklistItem::create($request->all());

        return redirect()->route('admin.toilet_inspection.checklist-items.index')
            ->with('success', 'Checklist berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified checklist item.
     */
    public function edit(ChecklistItem $checklist_item)
    {
        return view('admin.toilet_inspection.checklist_items.edit', compact('checklist_item'));
    }

    /**
     * Update the specified checklist item in storage.
     */
    public function update(Request $request, ChecklistItem $checklist_item)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:checklist_items,name,' . $checklist_item->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $checklist_item->update($request->all());

        return redirect()->route('admin.toilet_inspection.checklist-items.index')
            ->with('success', 'Checklist berhasil diperbarui.');
    }

    /**
     * Remove the specified checklist item from storage.
     */
    public function destroy(ChecklistItem $checklist_item)
    {
        $checklist_item->delete();

        return redirect()->route('admin.toilet_inspection.checklist-items.index')
            ->with('success', 'Checklist berhasil dihapus.');
    }
}
