@extends('layouts.admin')

@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Consultations</h2>
        <button 
            onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-consultation' }))"
            class="bg-blue-600 text-white px-4 py-2 rounded">
            + New Consultation
        </button>
    </div>

    @if(session('success'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 2000)" 
            x-show="show"
            x-transition
            class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Month Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <!-- Search Input -->
            <input 
                type="text" 
                id="search-input" 
                placeholder="Search by patient..." 
                class="w-full sm:w-56 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                oninput="filterConsultations()"
            >

            <!-- Month Filter -->
            <select 
                id="month-filter" 
                onchange="filterConsultations()" 
                class="w-full sm:w-36 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
            >
                <option value="">Months</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <table class="w-full border text-sm text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Patient</th>
                <th class="p-2 border">Notes</th>
                <th class="p-2 border">Items Given</th>
                <th class="p-2 border">Date</th>
            </tr>
        </thead>
        <tbody id="consultation-table-body">
            @forelse($consultations as $c)
                <tr>
                    <td class="p-2 border">{{ $c->user->name }}</td>
                    <td class="p-2 border">{{ $c->notes }}</td>
                    <td class="p-2 border">
                        <ul class="list-disc ml-4">
                            @foreach($c->inventories as $item)
                                <li>{{ $item->name }} - {{ $item->pivot->quantity_given }} {{ $item->unit }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="p-2 border">{{ $c->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center p-4 text-gray-500">No consultations found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal for creating consultation --}}
<x-modal name="create-consultation" maxWidth="2xl">
    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">New Consultation</h2>
        <form method="POST" action="{{ route('admin.consultation.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium">Patient</label>
                <select name="user_id" class="w-full border rounded p-2" required>
                    <option value="">Select Patient</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Items Given</label>
                <div class="space-y-2">
                    @foreach($inventories as $item)
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1" class="mr-2">
                            <span>{{ $item->name }} ({{ $item->quantity }} {{ $item->unit }})</span>
                            <input type="number" name="items[{{ $item->id }}][quantity_given]" class="border rounded p-1 w-24" placeholder="Qty" min="1">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Notes</label>
                <textarea name="notes" class="w-full border rounded p-2"></textarea>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</x-modal>

<script>
    let debounceTimeout;

    function filterConsultations() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const search = document.getElementById('search-input').value.trim().toLowerCase();
            const selectedMonth = document.getElementById('month-filter').value;
            const tbody = document.getElementById('consultation-table-body');
            const rows = tbody.querySelectorAll("tr");
            let hasResults = false;

            rows.forEach(row => {
                const name = row.children[0]?.textContent.toLowerCase() || "";
                const dateText = row.children[3]?.textContent || "";
                const month = new Date(dateText).getMonth() + 1;

                const matchesSearch = name.includes(search);
                const matchesMonth = !selectedMonth || parseInt(selectedMonth) === month;

                const shouldShow = matchesSearch && matchesMonth;
                row.style.display = shouldShow ? "" : "none";

                if (shouldShow) hasResults = true;
            });

            const noRow = document.getElementById('no-results');
            if (!hasResults && !noRow) {
                const tr = document.createElement('tr');
                tr.id = 'no-results';
                tr.innerHTML = `<td colspan="4" class="text-center p-4 text-sm text-gray-500">No matching consultations found.</td>`;
                tbody.appendChild(tr);
            } else if (hasResults && noRow) {
                noRow.remove();
            }
        }, 200);
    }
</script>
@endsection
