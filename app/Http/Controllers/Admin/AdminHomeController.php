<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminHomeController extends Controller
{
    /**
     * Display Admin Home / Management Selection.
     */
    public function index()
    {
        // Clear active module when visiting selector page
        session()->forget('active_admin_module');

        return view('admin.home');
    }

    /**
     * Handle choosing a management module.
     */
    public function selectModule(Request $request)
    {
        $request->validate([
            'module' => ['required', 'string', 'in:meeting_room,toilet_inspection'],
        ]);

        $module = $request->input('module');
        session(['active_admin_module' => $module]);

        if ($module === 'meeting_room') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.toilet_inspection.dashboard');
    }
}
