<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\User;
use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;



class ConsultationController extends Controller
{
    public function index()
    {
        $consultations = Consultation::with(['user', 'inventories'])->latest()->get();
        $users = User::all();
        $inventories = Inventory::all();

        return view('admin.consultation.index', compact('consultations', 'users', 'inventories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'items' => 'array',
            'items.*.quantity_given' => 'nullable|integer|min:1',
        ]);

        $itemsData = [];

        foreach ($request->items as $itemId => $data) {
            if (isset($data['selected']) && isset($data['quantity_given']) && $data['quantity_given'] > 0) {
                $inventory = Inventory::findOrFail($itemId);

                // Check if enough stock
                if ($inventory->quantity < $data['quantity_given']) {
                    return back()->with('error', "Not enough stock for item: {$inventory->name}");
                }

                // Deduct quantity
                $inventory->decrement('quantity', $data['quantity_given']);

                // Save data for pivot
                $itemsData[$itemId] = ['quantity_given' => $data['quantity_given']];

                // Log the inventory deduction (✅ moved inside loop)
                InventoryLog::create([
                    'inventory_id' => $inventory->id,
                    'user_id' => Auth::id(),
                    'action' => 'deducted',
                    'quantity' => $data['quantity_given'],
                    'unit' => $inventory->unit,
                    'remarks' => "Used in consultation for patient ID: {$request->user_id}"
                ]);
            }
        }

        // Save consultation
        $consultation = Consultation::create([
            'user_id' => $request->user_id,
            'notes' => $request->notes,
        ]);

        // Attach pivot table data
        $consultation->inventories()->attach($itemsData);

        return redirect()->route('admin.consultation.index')->with('success', 'Consultation created and inventory updated.');
    }




    public function show(Consultation $consultation)
    {
        $consultation->load('user', 'inventories');
        return view('admin.consultation.show', compact('consultation'));
    }

    public function destroy(Consultation $consultation)
    {
        $consultation->delete();
        return redirect()->route('admin.consultation.index')->with('success', 'Consultation deleted successfully.');
    }

    
}
