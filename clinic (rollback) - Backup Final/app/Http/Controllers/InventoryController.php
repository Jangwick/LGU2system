<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryLog;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;

class InventoryController extends Controller
{
    public function index()
    {
        $items = Inventory::where('archive', 0)->latest()->get();
        return view('admin.inventory.index', compact('items'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required_unless:category,Equipment|nullable|string',
            'status' => 'required_if:category,Equipment|nullable|string',
            'reorder_level' => 'nullable_unless:category,Equipment|nullable|integer|min:0',
            'supplier' => 'nullable|string',
            'purchase_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
        ]);

        $inventory = Inventory::create([
            'name' => $request->name,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'status' => $request->status,
            'reorder_level' => $request->category !== 'Equipment' ? $request->reorder_level : null,
            'supplier' => $request->supplier,
            'purchase_date' => $request->purchase_date,
            'expiration_date' => $request->expiration_date,
            'notes' => $request->notes,
        ]);

        InventoryLog::create([
            'inventory_id' => $inventory->id,
            'user_id' => auth()->id(),
            'action' => 'added',
            'quantity' => $inventory->quantity,
            'unit' => $inventory->unit,
            'remarks' => 'Item added to inventory.'
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Item added to inventory.');
    }


    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required_unless:category,Equipment|nullable|string',
            'status' => 'required_if:category,Equipment|nullable|string',
            'reorder_level' => 'nullable_unless:category,Equipment|nullable|integer|min:0',
            'supplier' => 'nullable',
            'purchase_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:purchase_date',
        ]);

        $originalQty = $inventory->quantity;

        // Only update allowed fields based on category
        $inventory->update([
            'name' => $request->name,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'unit' => $request->category !== 'Equipment' ? $request->unit : null,
            'status' => $request->category === 'Equipment' ? $request->status : null,
            'reorder_level' => $request->category !== 'Equipment' ? $request->reorder_level : null,
            'supplier' => $request->supplier,
            'purchase_date' => $request->purchase_date,
            'expiration_date' => $request->expiration_date,
            'notes' => $request->notes,
        ]);

        $newQty = $inventory->quantity;

        if ($newQty != $originalQty) {
            $action = $newQty > $originalQty ? 'added' : 'deducted';
            $diff = abs($newQty - $originalQty);

            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'action' => $action,
                'quantity' => $diff,
                'unit' => $inventory->unit,
                'remarks' => "Quantity {$action} manually. Changed from {$originalQty} to {$newQty}."
            ]);
        } else {
            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'quantity' => $newQty,
                'unit' => $inventory->unit,
                'remarks' => 'Inventory details updated (no quantity change).'
            ]);
        }

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory updated.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('admin.inventory.index')->with('success', 'Item deleted.');
    }

    public function archive(Inventory $inventory)
    {
        $inventory->update(['archive' => 1]);

        return redirect()->route('admin.inventory.index')->with('success', 'Item archived.');
    }

    public function exportCsv()
{
    $fileName = 'inventory_export.csv';

    return response()->streamDownload(function () {
        $writer = SimpleExcelWriter::createFromFileHandle(fopen('php://output', 'w'));

        $items = \App\Models\Inventory::where('archive', 0)->get();

        foreach ($items as $item) {
            $writer->addRow([
                'Name' => $item->name,
                'Category' => $item->category,
                'Quantity' => $item->category === 'Equipment' ? 'x' . $item->quantity : $item->quantity,
                'Unit' => $item->category === 'Equipment' ? 'N/A' : $item->unit,
                'Status' => $item->category === 'Equipment' ? $item->status : 'N/A',
                'Purchase Date' => $item->purchase_date,
                'Expiration Date' => $item->expiration_date ?? 'N/A',
            ]);
        }

        $writer->close();
    }, 'inventory_export.csv', [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="inventory_export.csv"',
    ]);
}
}
