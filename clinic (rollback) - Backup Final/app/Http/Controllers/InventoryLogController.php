<?php

namespace App\Http\Controllers\Admin;

use App\Models\InventoryLog;
use App\Http\Controllers\Controller;


class InventoryLogController extends Controller
{
    public function index()
    {
        $logs = InventoryLog::with(['inventory', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.inventory.logs', compact('logs'));
    }
}

