<a href="{{ route('inventory.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg transition inline-block">
    Add New Stock Item
</a>
</a>
<button type="submit" onclick="toggleStockModal(true)" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
    + Add Stock Item
</button>

<div id="addStockModal" class="hidden fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 relative animate-fade-in">
        
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-bold text-gray-800">Add New Motorcycle Part/Accessory</h3>
            <button type="button" onclick="toggleStockModal(false)" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

</a>
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Item Code / Barcode</label>
                    <input type="text" name="item_code" placeholder="e.g., BRK-001" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Part / Item Name</label>
                    <input type="text" name="part_name" placeholder="e.g., Honda Wave Brake Pad" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Category</label>
                    <select name="category" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Accessories">Accessories</option>
                        <option value="Engine Parts">Engine Parts</option>
                        <option value="Tires & Rims">Tires & Rims</option>
                        <option value="Electrical">Electrical Parts</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Target Branch Location</label>
                    <select name="branch_location" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Moroboro">Brgy. Moroboro Branch</option>
                        <option value="Poblacion">Poblacion Muyco St. Branch</option>
                        <option value="San Matias">Brgy. San Matias Branch</option>
                        <option value="Banate">Bularan St. Banate Branch</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Stock Initial Qty</label>
                    <input type="number" name="stock_level" min="0" value="0" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Low-Stock Alert Qty</label>
                    <input type="number" name="alert_threshold" min="1" value="5" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Price (₱)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="flex justify-end space-x-2 border-t pt-4">
                <button type="button" onclick="toggleStockModal(false)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition">Save to Inventory</button>
            </div>
        </form>
    </div>
</div>
<div id="addStockModal" class="hidden fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 relative">
        
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-bold text-gray-800">Add New Motorcycle Part/Accessory</h3>
            <button type="button" onclick="toggleStockModal(false)" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Item Code / Barcode</label>
                    <input type="text" name="item_code" placeholder="e.g., BRK-001" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Part / Item Name</label>
                    <input type="text" name="part_name" placeholder="e.g., Honda Wave Brake Pad" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Category</label>
                    <select name="category" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Accessories">Accessories</option>
                        <option value="Engine Parts">Engine Parts</option>
                        <option value="Tires & Rims">Tires & Rims</option>
                        <option value="Electrical">Electrical Parts</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Target Branch Location</label>
                    <select name="branch_location" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Moroboro">Brgy. Moroboro Branch</option>
                        <option value="Poblacion">Poblacion Muyco St. Branch</option>
                        <option value="San Matias">Brgy. San Matias Branch</option>
                        <option value="Banate">Bularan St. Banate Branch</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Stock Initial Qty</label>
                    <input type="number" name="stock_level" min="0" value="0" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Low-Stock Alert Qty</label>
                    <input type="number" name="alert_threshold" min="1" value="5" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Price (₱)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="flex justify-end space-x-2 border-t pt-4">
                <button type="button" onclick="toggleStockModal(false)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition">Save to Inventory</button>
            </div>
        </form>
    </div>
</div>
<div class="fixed bottom-6 right-6 z-[9999]">
    <a href="/inventory/create" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold px-6 py-4 rounded-full shadow-2xl transition-all duration-200 transform hover:scale-105 cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
       <a href="{{ route('inventory.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg transition inline-block">
    Add New Stock Item
</a>
    </a>
</div>
<script>
    function toggleStockModal(show) {
        const modal = document.getElementById('addStockModal');
        if (show) {
            // This forces the popup modal to display instantly
            modal.style.setProperty('display', 'flex', 'important');
        } else {
            // This hides it completely
            modal.style.setProperty('display', 'none', 'important');
        }
    }
    
</script>


<script>
    // Handles opening and closing the registration modal smoothly
    function toggleStockModal(show) {
        const modal = document.getElementById('addStockModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
</script>