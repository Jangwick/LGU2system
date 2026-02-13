@extends('layouts.admin')

@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Inventory</h2>
        <button
            onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-inventory' }))"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            + Add Item
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

    <!-- Search and Category Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <input type="text" id="search-input" placeholder="Search.."
                class="w-full sm:w-56 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                oninput="filterInventory()">

            <select id="category-filter" onchange="filterInventory()"
                class="w-full sm:w-32 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                <option value="">Categories</option>
                <option value="Medicine">Medicine</option>
                <option value="Medical Supply">Medical Supply</option>
                <option value="Equipment">Equipment</option>
                <option value="Others">Others</option>
            </select>

            <a href="{{ route('admin.inventory.export.csv') }}"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                Export CSV
            </a>

        </div>
    </div>

    <table class="w-full border text-sm text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Category</th>
                <th class="p-2 border">Quantity</th>
                @if($items->contains(fn($item) => $item->category === 'Equipment'))
                    <th class="p-2 border">Status</th>
                @endif
                <th class="p-2 border">Purchase Date</th>
                <th class="p-2 border">Expiration</th>
                <th class="p-2 border">Actions</th>
            </tr>
        </thead>
        <tbody id="inventory-table-body">
            @foreach($items as $item)
                <tr>
                    <td class="p-2 border">{{ ucfirst($item->name) }}</td>
                    <td class="p-2 border">{{ $item->category }}</td>
                    <td class="p-2 border">
                        @if($item->category === 'Equipment')
                            x{{ $item->quantity }}
                        @else
                            {{ $item->quantity }} {{ $item->unit }}
                            @if(in_array($item->category, ['Medicine', 'Medical Supply']) && $item->isLowStock())
                                <span class="text-red-600 text-xs ml-2">(Low Stock)</span>
                            @endif
                        @endif
                    </td>

                    @if($items->contains(fn($i) => $i->category === 'Equipment'))
                        @if($item->category === 'Equipment')
                            <td class="p-2 border">{{ ucfirst($item->status) }}</td>
                        @else
                            <td class="p-2 border text-gray-400 italic">N/A</td>
                        @endif
                    @endif

                    <td class="p-2 border">{{ $item->purchase_date }}</td>
                    <td class="p-2 border">{{ $item->expiration_date ?? 'N/A' }}</td>
                    <td class="p-2 border">
                        <button
                            onclick="openEditModal({{ json_encode($item) }})"
                            class="text-blue-600 hover:underline">Edit</button>

                        <form action="{{ route('admin.inventory.archive', $item->id) }}" method="POST" class="inline ml-2">
                            @csrf @method('PATCH')
                            <button type="submit" onclick="return confirm('Archive this item?')"
                                class="text-yellow-600 hover:underline">Archive</button>
                        </form>    

                        <form action="{{ route('admin.inventory.destroy', $item->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')"
                                class="text-red-600 hover:underline ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- CREATE MODAL --}}
<x-modal name="create-inventory" maxWidth="2xl">
    @if ($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-inventory' }));
            });
        </script>
    @endif

    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Add Inventory Item</h2>
        <form method="POST" action="{{ route('admin.inventory.store') }}" id="createInventoryForm"
            x-data="inventoryForm({
                name: '',
                category: '',
                quantity: '',
                reorder_level: '',
                unit: '',
                status: '',
                supplier: '',
                purchase_date: '',
                expiration_date: '',
                notes: ''
            })">
            @csrf
            @include('admin.inventory.form')
            <div class="mt-4 text-right">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</x-modal>



{{-- EDIT MODAL --}}
<x-modal name="edit-inventory" maxWidth="2xl">
    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Edit Inventory Item</h2>
        <form method="POST" id="editInventoryForm"
            x-data="inventoryForm()" @submit.prevent="handleSubmit($el)">
            @csrf @method('PUT')
            @include('admin.inventory.form')
            <div x-show="errors.general" class="text-red-600 mb-2 text-sm" x-text="errors.general"></div>

            <div class="mt-4 text-right">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update</button>
            </div>
        </form>

    </div>
</x-modal>

<script>
    function openEditModal(item) {
      window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-inventory' }));

      const form = document.getElementById('editInventoryForm');
      form.action = `/admin/inventory/${item.id}`;

      const formatDate = (dateStr) => {
          if (!dateStr) return '';
          const date = new Date(dateStr);
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          return `${year}-${month}-${day}`;
      };

      const alpine = Alpine.$data(form);

      alpine.form = {
          name: item.name ?? '',
          category: item.category ?? '',
          quantity: item.quantity ?? '',
          unit: item.unit ?? '',
          status: item.status ?? '',
          supplier: item.supplier ?? '',
          purchase_date: formatDate(item.purchase_date),
          expiration_date: formatDate(item.expiration_date),
          notes: item.notes ?? ''
      };

      alpine.originalForm = JSON.parse(JSON.stringify(alpine.form));
      alpine.errors = {};
    }

    window.addEventListener('modal-closed', function (e) {
        if (e.detail === 'create-inventory') {
            const form = document.getElementById('createInventoryForm');
            if (form) {
                const alpine = Alpine.$data(form);
                if (alpine) {
                    alpine.resetForm();
                }
            }
        }
    });

    function inventoryForm() {
        return {
            form: {
                name: '',
                category: '',
                quantity: '',
                reorder_level: '',
                unit: '',
                status: '',
                supplier: '',
                purchase_date: '',
                expiration_date: '',
                notes: ''
            },
            originalForm: {},
            errors: {},
            resetForm() {
                this.form = {
                    name: '',
                    category: '',
                    quantity: '',
                    reorder_level: '',
                    unit: '',
                    status: '',
                    supplier: '',
                    purchase_date: '',
                    expiration_date: '',
                    notes: ''
                };
                this.errors = {};
            },
            handleSubmit(formEl) {
                formEl.submit();
            }
        }
    }
    
    let debounceTimeout;

    function filterInventory() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const search = document.getElementById('search-input').value.trim().toLowerCase();
            const selectedCategory = document.getElementById('category-filter').value.toLowerCase();
            const tbody = document.getElementById('inventory-table-body');
            const rows = tbody.querySelectorAll("tr");
            let hasResults = false;

            rows.forEach(row => {
                const name = row.children[0]?.textContent.toLowerCase() || "";
                const category = row.children[1]?.textContent.toLowerCase() || "";
                const supplier = row.children[4]?.textContent.toLowerCase() || "";

                const matchesSearch =
                    name.includes(search) ||
                    category.includes(search) ||
                    supplier.includes(search);

                const matchesCategory = !selectedCategory || category === selectedCategory;

                const shouldShow = matchesSearch && matchesCategory;
                row.style.display = shouldShow ? "" : "none";

                if (shouldShow) hasResults = true;
            });

            // Optionally show a "No results" row (if you want)
            if (!hasResults && !document.getElementById('no-results')) {
                const tr = document.createElement('tr');
                tr.id = 'no-results';
                tr.innerHTML = `<td colspan="8" class="text-center p-4 text-sm text-gray-500">No matching items found.</td>`;
                tbody.appendChild(tr);
            } else if (hasResults) {
                const noRow = document.getElementById('no-results');
                if (noRow) noRow.remove();
            }
        }, 200);
    }
</script>
@endsection
