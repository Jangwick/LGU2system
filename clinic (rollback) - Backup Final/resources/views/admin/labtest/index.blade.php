@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Lab Tests</h2>
        <button 
            onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-labtest' }))"
            class="bg-blue-600 text-white px-4 py-2 rounded">
            + New Lab Test
        </button>
    </div>

    @if(session('success'))
        <div class="text-green-600 mb-4">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border text-sm text-left">
          <thead class="bg-gray-100">
              <tr>
                  <th class="p-2 border">Test Code</th>
                  <th class="p-2 border">Student Name</th>
                  <th class="p-2 border text-center">Actions</th>
              </tr>
          </thead>
          <tbody>
              @forelse($labTests as $test)
                  <tr>
                      <td class="p-2 border">{{ $test->test_code }}</td>
                      <td class="p-2 border">{{ $test->student_name }}</td>
                      <td class="p-2 border text-center space-x-2">
                          <a href="#" onclick="loadLabTest('{{ $test->test_code }}')" class="text-blue-600 hover:underline">View</a>

                          <a href="{{ route('admin.labtests.edit', $test->test_code) }}"
                            class="text-yellow-600 hover:underline">Edit</a>
                      </td>
                  </tr>
              @empty
                  <tr>
                      <td colspan="3" class="text-center text-gray-500 p-4">No lab tests found.</td>
                  </tr>
              @endforelse
          </tbody>
      </table>
    </div>
</div>

{{-- Modal for creating lab test --}}
<x-modal name="create-labtest" maxWidth="2xl">
    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">New Lab Test</h2>

        <form method="POST" action="{{ route('admin.labtests.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Student Name</label>
                    <input type="text" name="student_name" class="w-full border rounded p-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Blood Type</label>
                    <input type="text" name="blood_type" class="w-full border rounded p-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Blood Test</label>
                    <input type="text" name="blood_test" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Urine Test</label>
                    <input type="text" name="urine_test" class="w-full border rounded p-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Color</label>
                    <input type="text" name="color" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Transparency</label>
                    <input type="text" name="transparency" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">RBC</label>
                    <input type="text" name="rbc" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">WBC</label>
                    <input type="text" name="wbc" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Platelet</label>
                    <input type="text" name="platelet" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Neutrophils</label>
                    <input type="text" name="neutrophils" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Lymphocytes</label>
                    <input type="text" name="lymphocytes" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Eosinophils</label>
                    <input type="text" name="eosinophils" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Basophils</label>
                    <input type="text" name="basophils" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Protein</label>
                    <input type="text" name="protein" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Specific Gravity</label>
                    <input type="text" name="specific_gravity" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Glucose</label>
                    <input type="text" name="glucose" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Hemoglobin</label>
                    <input type="text" name="hemoglobin" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Hematocrit</label>
                    <input type="text" name="hematocrit" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Monocytes</label>
                    <input type="text" name="monocytes" class="w-full border rounded p-2">
                </div>
            </div>

            <div class="text-right mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Lab Result Modal --}}
<div id="resultModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg w-11/12 md:w-3/4 p-6 shadow-lg relative">
        <button onclick="closeResultModal()" class="absolute top-3 right-4 text-2xl font-bold text-gray-600 hover:text-black">&times;</button>
        <h3 class="text-center text-xl font-semibold text-cyan-600 mb-4 border-b pb-2">Laboratory Result</h3>

        <p><strong>Student Name:</strong> <span id="modalStudentName"></span></p>
        <p><strong>Blood Type:</strong> <span id="modalBloodType"></span></p>

        <h4 class="text-lg font-bold mt-4 mb-2 text-gray-700">Hematology</h4>
        <table class="w-full border text-sm mb-4">
            <thead>
                <tr class="bg-cyan-500 text-white">
                    <th class="py-2 px-3">Test</th>
                    <th class="py-2 px-3">Result</th>
                    <th class="py-2 px-3">Normal Values</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="border px-3 py-1">Hemoglobin</td><td id="modalHemoglobin" class="border px-3 py-1"></td><td class="border px-3 py-1">M: 140-180, F: 120-160 g/L</td></tr>
                <tr><td class="border px-3 py-1">Hematocrit</td><td id="modalHematocrit" class="border px-3 py-1"></td><td class="border px-3 py-1">M: 0.40-0.54, F: 0.37-0.47</td></tr>
                <tr><td class="border px-3 py-1">RBC</td><td id="modalRBC" class="border px-3 py-1"></td><td class="border px-3 py-1">M: 4.5-5.0, F: 4.0-4.5 g/L</td></tr>
                <tr><td class="border px-3 py-1">WBC</td><td id="modalWBC" class="border px-3 py-1"></td><td class="border px-3 py-1">4.0 - 10 x 10⁹/L</td></tr>
                <tr><td class="border px-3 py-1">Platelet Count</td><td id="modalPlatelet" class="border px-3 py-1"></td><td class="border px-3 py-1">150 - 450 x 10⁹/L</td></tr>
                <tr><td class="border px-3 py-1">Monocytes</td><td id="modalMonocytes" class="border px-3 py-1"></td><td class="border px-3 py-1">2-6%</td></tr>
                <tr><td class="border px-3 py-1">Eosinophils</td><td id="modalEosinophils" class="border px-3 py-1"></td><td class="border px-3 py-1">1-4%</td></tr>
                <tr><td class="border px-3 py-1">Basophils</td><td id="modalBasophils" class="border px-3 py-1"></td><td class="border px-3 py-1">0.00-0.05%</td></tr>
            </tbody>
        </table>

        <h4 class="text-lg font-bold mt-4 mb-2 text-gray-700">Urinalysis</h4>
        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-cyan-500 text-white">
                    <th class="py-2 px-3">Test</th>
                    <th class="py-2 px-3">Result</th>
                    <th class="py-2 px-3">Normal Values</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="border px-3 py-1">Color</td><td id="modalColor" class="border px-3 py-1"></td><td class="border px-3 py-1">Yellow</td></tr>
                <tr><td class="border px-3 py-1">Transparency</td><td id="modalTransparency" class="border px-3 py-1"></td><td class="border px-3 py-1">Clear</td></tr>
                <tr><td class="border px-3 py-1">Protein</td><td id="modalProtein" class="border px-3 py-1"></td><td class="border px-3 py-1">Negative</td></tr>
                <tr><td class="border px-3 py-1">Specific Gravity</td><td id="modalSpecificGravity" class="border px-3 py-1"></td><td class="border px-3 py-1">1.005 - 1.030</td></tr>
                <tr><td class="border px-3 py-1">Glucose</td><td id="modalGlucose" class="border px-3 py-1"></td><td class="border px-3 py-1">Negative</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadLabTest(code) {
    fetch(`/admin/labtests/${code}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalStudentName').textContent = data.student_name;
            document.getElementById('modalBloodType').textContent = data.blood_type;
            document.getElementById('modalHemoglobin').textContent = data.hemoglobin;
            document.getElementById('modalHematocrit').textContent = data.hematocrit;
            document.getElementById('modalRBC').textContent = data.rbc;
            document.getElementById('modalWBC').textContent = data.wbc;
            document.getElementById('modalPlatelet').textContent = data.platelet;
            document.getElementById('modalMonocytes').textContent = data.monocytes;
            document.getElementById('modalEosinophils').textContent = data.eosinophils;
            document.getElementById('modalBasophils').textContent = data.basophils;
            document.getElementById('modalColor').textContent = data.color;
            document.getElementById('modalTransparency').textContent = data.transparency;
            document.getElementById('modalProtein').textContent = data.protein;
            document.getElementById('modalSpecificGravity').textContent = data.specific_gravity;
            document.getElementById('modalGlucose').textContent = data.glucose;

            document.getElementById('resultModal').classList.remove('hidden');
        });
}

function closeResultModal() {
    document.getElementById('resultModal').classList.add('hidden');
}
</script>

@endsection
