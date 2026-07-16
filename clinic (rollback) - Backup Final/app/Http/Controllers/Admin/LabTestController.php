<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LabTest;
use Illuminate\Support\Str;

class LabTestController extends Controller
{
    public function index()
    {
        $labTests = LabTest::latest()->get();
        return view('admin.labtest.index', compact('labTests'));
    }

    public function create()
    {
        return view('admin.labtest.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_name' => 'required|string',
            'blood_type' => 'required|string',
            'blood_test' => 'nullable|string',
            'urine_test' => 'required|string',
            'color' => 'nullable|string',
            'transparency' => 'nullable|string',
            'rbc' => 'nullable|string',
            'wbc' => 'nullable|string',
            'platelet' => 'nullable|string',
            'neutrophils' => 'nullable|string',
            'lymphocytes' => 'nullable|string',
            'eosinophils' => 'nullable|string',
            'basophils' => 'nullable|string',
            'protein' => 'nullable|string',
            'specific_gravity' => 'nullable|string',
            'glucose' => 'nullable|string',
            'hemoglobin' => 'nullable|string',
            'hematocrit' => 'nullable|string',
            'monocytes' => 'nullable|string',
        ]);

        $validated['test_code'] = 'LT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        LabTest::create($validated);

        return redirect()->route('admin.labtests.index')->with('success', 'Lab test created successfully.');
    }


    public function show(LabTest $test)
    {
        return response()->json($test);
    }

    public function destroy(LabTest $labTest)
    {
        $labTest->delete();
        return redirect()->route('admin.labtests.index')->with('success', 'Lab test deleted.');
    }
}
