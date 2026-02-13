<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Category -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-600">*</span></label>
        <select x-model="form.category" name="category" required class="w-full border p-2 rounded mt-1 focus:ring focus:ring-blue-200">
            <option value="">Select Category</option>
            <option value="Medicine">Medicine</option>
            <option value="Medical Supply">Medical Supply</option>
            <option value="Equipment">Equipment</option>
        </select>
        <template x-if="errors.category">
            <p class="text-red-500 text-sm mt-1" x-text="errors.category"></p>
        </template>
    </div>

    <!-- Name -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-600">*</span></label>
        <input type="text" name="name" class="w-full border p-2 rounded mt-1" x-model="form.name" required>
        <span class="text-red-600 text-xs" x-text="errors.name"></span>
    </div>

    <!-- Quantity -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Quantity <span class="text-red-600">*</span></label>
        <input type="number" name="quantity" class="w-full border p-2 rounded mt-1" x-model="form.quantity" required>
        <span class="text-red-600 text-xs" x-text="errors.quantity"></span>
    </div>

    <!-- Unit (hidden when Equipment) -->
    <div x-show="form.category !== 'Equipment'" x-cloak>
        <label class="block text-sm font-medium text-gray-700">Unit <span class="text-red-600">*</span></label>
        <select x-model="form.unit"
                :name="form.category !== 'Equipment' ? 'unit' : null"
                class="w-full border p-2 rounded mt-1 focus:ring focus:ring-blue-200"
                :required="form.category !== 'Equipment'">
            <option value="">Select Unit</option>
            <option value="pcs">pcs (pieces)</option>
            <option value="box">box</option>
            <option value="bottle">bottle</option>
            <option value="tablet">tablet</option>
            <option value="capsule">capsule</option>
            <option value="ml">ml (milliliters)</option>
            <option value="L">L (liters)</option>
            <option value="g">g (grams)</option>
            <option value="kg">kg (kilograms)</option>
            <option value="unit">unit</option>
            <option value="pack">pack</option>
            <option value="set">set</option>
            <option value="tube">tube</option>
            <option value="vial">vial</option>
            <option value="sachet">sachet</option>
            <option value="can">can</option>
            <option value="others">Others</option>
        </select>
        <template x-if="errors.unit">
            <p class="text-red-500 text-sm mt-1" x-text="errors.unit"></p>
        </template>
    </div>

    <!-- Status (only for Equipment) -->
    <div x-show="form.category === 'Equipment'" x-cloak>
        <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-600">*</span></label>
        <select x-model="form.status"
                name="status"
                class="w-full border p-2 rounded mt-1 focus:ring focus:ring-blue-200"
                :required="form.category === 'Equipment'">
            <option value="">Select Status</option>
            <option value="working">Working</option>
            <option value="damaged">Damaged</option>
            <option value="under maintenance">Under Maintenance</option>
            <option value="lost">Lost</option>
        </select>
        <template x-if="errors.status">
            <p class="text-red-500 text-sm mt-1" x-text="errors.status"></p>
        </template>
    </div>

    <!-- Purchase Date -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Purchase Date <span class="text-red-600">*</span></label>
        <input type="date" name="purchase_date" class="w-full border p-2 rounded mt-1" x-model="form.purchase_date" required>
        <span class="text-red-600 text-xs" x-text="errors.purchase_date"></span>
    </div>

    <!-- Expiration Date -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Expiration Date <span class="text-red-600">*</span></label>
        <input type="date" name="expiration_date" class="w-full border p-2 rounded mt-1" x-model="form.expiration_date" required>
        <span class="text-red-600 text-xs" x-text="errors.expiration_date"></span>
    </div>

    <!-- Supplier -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Supplier</label>
        <input type="text" name="supplier" class="w-full border p-2 rounded mt-1" x-model="form.supplier">
        <span class="text-red-600 text-xs" x-text="errors.supplier"></span>
    </div>

    <!-- Notes -->
    <div class="col-span-1 md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" class="w-full border p-2 rounded mt-1" x-model="form.notes"></textarea>
    </div>
</div>

<!-- Alpine.js Script -->
<script>
    function inventoryForm() {
        return {
            form: {
                category: '{{ old('category', $inventory->category ?? '') }}',
                name: '{{ old('name', $inventory->name ?? '') }}',
                quantity: '{{ old('quantity', $inventory->quantity ?? '') }}',
                unit: '{{ old('unit', $inventory->unit ?? '') }}',
                status: '{{ old('status', $inventory->status ?? '') }}',
                reorder_level: '{{ old('reorder_level', $inventory->reorder_level ?? '') }}',
                supplier: '{{ old('supplier', $inventory->supplier ?? '') }}',
                purchase_date: '{{ old('purchase_date', $inventory->purchase_date ?? '') }}',
                expiration_date: '{{ old('expiration_date', $inventory->expiration_date ?? '') }}',
                notes: '{{ old('notes', $inventory->notes ?? '') }}',
            },
            errors: {},

            init() {
                this.$watch('form.category', (value) => {
                    if (value === 'Equipment') {
                        this.form.unit = '';
                    } else {
                        this.form.status = '';
                    }
                });
            },
        }
    }
</script>
