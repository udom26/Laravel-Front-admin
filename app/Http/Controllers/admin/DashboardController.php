<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Dummy summary
        $stats = [
            'queued' => 5,
            'done' => 12,
            'failed' => 2,
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
