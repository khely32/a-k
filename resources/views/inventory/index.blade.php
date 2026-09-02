@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <h2 class="fw-bold text-white mb-1">
                📦 Inventory Stocks
            </h2>
            <p class="mb-0" style="color:#9CA3AF;">
                View all motorcycle products currently stored in inventory.
            </p>
        </div>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <input
                type="text"
                id="inventory-search"
                class="form-control"
                placeholder="Search product name or serial..."
                autocomplete="off"
                style="width:230px;background-color:#111;color:#fff;border:1px solid rgba(239,68,68,.2);">
            <select id="category-filter" class="form-select" style="width:auto;background-color:#111;color:#fff;border:1px solid rgba(239,68,68,.2);">
                <option value="" style="background:#1a1a1a;color:#fff;">All Categories</option>
                @foreach($products->pluck('type')->unique()->filter()->sort()->values() as $type)
                    <option value="{{ $type }}" style="background:#1a1a1a;color:#fff;">{{ $type }}</option>
                @endforeach
            </select>
            <select id="brand-filter" class="form-select" style="width:auto;background-color:#111;color:#fff;border:1px solid rgba(239,68,68,.2);">
                <option value="" style="background:#1a1a1a;color:#fff;">All Brands</option>
            </select>
            <select id="color-filter" class="form-select" style="width:auto;background-color:#111;color:#fff;border:1px solid rgba(239,68,68,.2);">
                <option value="" style="background:#1a1a1a;color:#fff;">All Colors</option>
            </select>
            <a href="{{ route('inventory.create') }}" class="btn btn-primary px-4 py-2">
                ➕ Add Product
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Serial Number</th>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($products as $product)

                            <tr class="inventory-row"
                                data-name="{{ strtolower($product->name) }}"
                                data-serial="{{ strtolower($product->serial_number) }}"
                                data-type="{{ strtolower($product->type ?? '') }}"
                                data-brand="{{ $product->brand ?? '' }}"
                                data-color="{{ $product->color ?? '' }}">

                                <td>{{ $product->id }}</td>

                                <td>
                                    <code class="text-info">{{ $product->serial_number }}</code>
                                </td>

                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>

                                <td>
                                    {{ $product->brand ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $product->type ?? 'N/A' }}
                                </td>

                                <td>
                                    @if($product->color)
                                        {{ $product->color }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    {{ $product->size ?? '—' }}
                                </td>

                                <td>
                                    {{ $product->display_quantity }}
                                </td>

                                <td>
                                    ₱{{ number_format($product->price, 2) }}
                                </td>

                                <td>

                                    @if($product->display_quantity == 0)

                                        <span class="badge bg-danger">
                                            Out of Stock
                                        </span>

                                    @elseif($product->display_quantity <= 5)

                                        <span class="badge bg-warning text-dark">
                                            Low Stock
                                        </span>

                                    @else

                                        <span class="badge bg-success">
                                            In Stock
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="10" class="text-center py-5">

                                    <h5>No Products Found</h5>

                                    <p class="text-muted mb-0">
                                        Add products to see them in inventory.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                        <tr id="no-filter-results" style="display:none;">
                            <td colspan="10" class="text-center py-5">
                                <h5>No matching products</h5>
                                <p class="text-muted mb-0">Try a different search or category.</p>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    (function () {
        const searchInput = document.getElementById('inventory-search');
        const categorySelect = document.getElementById('category-filter');
        const brandSelect = document.getElementById('brand-filter');
        const colorSelect = document.getElementById('color-filter');
        const rows = Array.from(document.querySelectorAll('.inventory-row'));
        const noResults = document.getElementById('no-filter-results');

        function optionList(items) {
            const map = {};
            items.forEach(function (item) {
                const key = item.key;
                if (key && !(key in map)) map[key] = item.display;
            });
            return Object.keys(map).sort().map(function (k) { return map[k]; });
        }

        function populateBrandOptions() {
            const category = (categorySelect.value || '').toLowerCase().trim();
            const brands = optionList(rows
                .filter(function (r) {
                    const t = (r.dataset.type || '').toLowerCase().trim();
                    return category === '' || t === category;
                })
                .map(function (r) {
                    return { key: (r.dataset.brand || '').toLowerCase().trim(), display: r.dataset.brand || '' };
                }));
            const current = (brandSelect.value || '').toLowerCase().trim();
            brandSelect.innerHTML = '<option value="" style="background:#1a1a1a;color:#fff;">All Brands</option>';
            brands.forEach(function (b) {
                const opt = document.createElement('option');
                opt.value = b.toLowerCase().trim();
                opt.textContent = b;
                opt.style.background = '#1a1a1a';
                opt.style.color = '#fff';
                if (b.toLowerCase().trim() === current) opt.selected = true;
                brandSelect.appendChild(opt);
            });
            populateColorOptions();
        }

        function populateColorOptions() {
            const category = (categorySelect.value || '').toLowerCase().trim();
            const brand = (brandSelect.value || '').toLowerCase().trim();
            const colors = optionList(rows
                .filter(function (r) {
                    const t = (r.dataset.type || '').toLowerCase().trim();
                    const b = (r.dataset.brand || '').toLowerCase().trim();
                    const c = (r.dataset.color || '').toLowerCase().trim();
                    if (!c) return false;
                    return (category === '' || t === category) && (brand === '' || b === brand);
                })
                .map(function (r) {
                    return { key: (r.dataset.color || '').toLowerCase().trim(), display: r.dataset.color || '' };
                }));
            const current = (colorSelect.value || '').toLowerCase().trim();
            colorSelect.innerHTML = '<option value="" style="background:#1a1a1a;color:#fff;">All Colors</option>';
            colors.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = c.toLowerCase().trim();
                opt.textContent = c;
                opt.style.background = '#1a1a1a';
                opt.style.color = '#fff';
                if (c.toLowerCase().trim() === current) opt.selected = true;
                colorSelect.appendChild(opt);
            });
        }

        function applyFilters() {
            const query = (searchInput.value || '').toLowerCase().trim();
            const category = (categorySelect.value || '').toLowerCase().trim();
            const brand = (brandSelect.value || '').toLowerCase().trim();
            const color = (colorSelect.value || '').toLowerCase().trim();
            let visible = 0;

            rows.forEach(function (row) {
                const name = row.dataset.name || '';
                const serial = row.dataset.serial || '';
                const type = (row.dataset.type || '').toLowerCase().trim();
                const rowBrand = (row.dataset.brand || '').toLowerCase().trim();
                const rowColor = (row.dataset.color || '').toLowerCase().trim();

                const matchesQuery = query === '' || name.indexOf(query) !== -1 || serial.indexOf(query) !== -1;
                const matchesCategory = category === '' || type === category;
                const matchesBrand = brand === '' || rowBrand === brand;
                const matchesColor = color === '' || rowColor === color;

                const show = matchesQuery && matchesCategory && matchesBrand && matchesColor;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (rows.length === 0) {
                noResults.style.display = 'none';
                return;
            }

            noResults.style.display = visible === 0 ? '' : 'none';
        }

        searchInput.addEventListener('input', applyFilters);
        categorySelect.addEventListener('change', function () {
            populateBrandOptions();
            applyFilters();
        });
        brandSelect.addEventListener('change', function () {
            populateColorOptions();
            applyFilters();
        });
        colorSelect.addEventListener('change', applyFilters);

        populateBrandOptions();
    })();
</script>
@endsection