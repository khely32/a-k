@extends('layouts.app')

@section('content')
<style>
    .inv-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 16px;
    }
    .inv-title-wrap { display: flex; align-items: center; gap: 14px; }
    .inv-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 230, 118, 0.1);
        border: 1px solid rgba(0, 230, 118, 0.25);
        box-shadow: 0 0 18px rgba(0, 230, 118, 0.15);
        flex-shrink: 0;
    }
    .inv-title-icon svg { width: 22px; height: 22px; color: var(--green); }
    .inv-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--green);
        letter-spacing: 0.02em;
        text-shadow: 0 0 12px rgba(0, 230, 118, 0.3);
        margin: 0;
        line-height: 1.15;
    }
    .inv-sub { font-size: 0.78rem; color: #9CA3AF; margin: 0; }
    .inv-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid rgba(0, 230, 118, 0.4);
        transition: all 0.2s ease;
        box-shadow: 0 2px 14px rgba(0, 230, 118, 0.25);
    }
    .inv-add-btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 22px rgba(0, 230, 118, 0.45), 0 0 30px rgba(0, 230, 118, 0.15);
        background: linear-gradient(135deg, #10B981, #047857);
    }
    .inv-add-btn svg { width: 16px; height: 16px; }

    .inv-filter-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        background: rgba(30, 41, 59, 0.55);
        border: 1px solid rgba(0, 230, 118, 0.12);
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 18px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
    }
    .inv-search-wrap { position: relative; flex: 1 1 220px; min-width: 200px; }
    .inv-search-wrap .bi-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748B;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .inv-search,
    .inv-select {
        background: #0D1117;
        border: 1px solid #334155;
        color: #E2E8F0;
        border-radius: 8px;
        font-size: 0.8rem;
        padding: 8px 12px 8px 34px;
        outline: none;
        transition: all 0.2s ease;
        width: 100%;
    }
    .inv-search::-webkit-input-placeholder { color: #64748B; }
    .inv-select { padding: 8px 12px; width: auto; cursor: pointer; }
    .inv-select option { background: #1E293B; color: #fff; }
    .inv-search:focus,
    .inv-select:focus {
        border-color: #10B981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        background: #0D1117;
    }

    .inv-card {
        background: #0F172A;
        border: 1px solid #1E293B;
        border-radius: 18px;
        overflow-x: auto;
        overflow-y: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
    }
    .inv-card::-webkit-scrollbar{height:8px;}
    .inv-card::-webkit-scrollbar-track{background:rgba(15,23,42,.6);border-radius:0 0 18px 18px;}
    .inv-card::-webkit-scrollbar-thumb{background:#334155;border-radius:8px;border:2px solid #0F172A;}
    .inv-card::-webkit-scrollbar-thumb:hover{background:#475569;}
    .inv-card .table {
        margin-bottom: 0;
        min-width: 720px;
        --bs-table-bg: transparent;
        --bs-table-hover-bg: transparent;
    }
    .inv-card thead th {
        background: linear-gradient(90deg, #111827, #1E293B);
        color: rgba(52, 211, 153, 0.9);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 1px solid rgba(0, 230, 118, 0.15);
        white-space: nowrap;
    }
    .inv-card tbody {
        background: #0B1120;
    }
    .inv-card tbody tr {
        background: #0B1120;
        transition: background 0.15s ease;
    }
    .inv-card tbody td {
        padding: 10px 16px;
        border-bottom: 1px solid rgba(30, 41, 59, 0.6);
        color: #F1F5F9;
        font-size: 0.82rem;
        vertical-align: middle;
        white-space: nowrap;
        transition: background 0.15s ease;
    }
    .inv-card tbody tr:last-child td { border-bottom: none; }
    .inv-card tbody tr:hover { background: rgba(30, 41, 59, 0.4); }

    .inv-id { color: #64748B; font-size: 0.75rem; font-weight: 600; }
    .serial-chip {
        font-family: 'Courier New', monospace;
        font-size: 0.76rem;
        font-weight: 600;
        color: #22D3EE;
        background: rgba(8, 51, 68, 0.6);
        border: 1px solid rgba(21, 94, 117, 0.5);
        padding: 2px 10px;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .serial-chip:hover {
        background: rgba(8, 51, 68, 0.9);
        box-shadow: 0 0 10px rgba(34, 211, 238, 0.2);
        cursor: default;
    }
    .inv-name { color: #fff; font-weight: 600; font-size: 0.85rem; }
    .inv-muted { color: #94A3B8; }

    .price-cell { text-align: right; font-weight: 700; color: #34D399; white-space: nowrap; }

    .stk-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }
    .stk-pill i { font-size: 0.55rem; }
    .stk-ok { background: rgba(16, 185, 129, 0.1); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.25); }
    .stk-lo { background: rgba(251, 191, 36, 0.1); color: #FBBF24; border: 1px solid rgba(251, 191, 36, 0.3); }
    .stk-0 { background: rgba(239, 68, 68, 0.1); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.3); }

    .act-cell { text-align: right; white-space: nowrap; }
    .act-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid transparent;
        margin-left: 4px;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .act-view { background: rgba(0, 230, 118, 0.08); color: #34D399; border-color: rgba(0, 230, 118, 0.2); }
    .act-view:hover { background: rgba(0, 230, 118, 0.2); color: #6EE7B7; box-shadow: 0 0 12px rgba(0, 230, 118, 0.3); }
    .act-edit { background: rgba(0, 229, 255, 0.08); color: var(--cyan); border-color: rgba(0, 229, 255, 0.2); }
    .act-edit:hover { background: rgba(0, 229, 255, 0.2); color: #67E8F9; box-shadow: 0 0 12px rgba(0, 229, 255, 0.3); }
    .act-del { background: rgba(239, 68, 68, 0.08); color: #EF4444; border-color: rgba(239, 68, 68, 0.2); }
    .act-del:hover { background: rgba(239, 68, 68, 0.2); color: #FCA5A5; box-shadow: 0 0 12px rgba(239, 68, 68, 0.3); }
    .act-del-form { display: inline; margin-left: 4px; }

    .inv-empty { text-align: center; padding: 48px 16px; }
    .inv-empty i { font-size: 3rem; color: #374151; }
    .inv-empty h5 { color: #94A3B8; font-weight: 700; margin-top: 14px; }
    .inv-empty p { color: #64748B; font-size: 0.82rem; }

    .detail-row { display: flex; justify-content: space-between; gap: 16px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .lbl { color: #64748B; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
    .detail-row .val { color: #E2E8F0; font-size: 0.85rem; font-weight: 600; text-align: right; }

    .inv-modal-content {
        background: #161B22;
        border: 1px solid rgba(0, 230, 118, 0.2);
        border-radius: 16px;
        box-shadow: 0 0 30px rgba(0, 230, 118, 0.1);
    }
    .inv-modal-header { border-bottom: 1px solid rgba(0, 230, 118, 0.15); padding: 16px 20px; }
    .inv-modal-title { color: var(--green); font-weight: 800; }
    .inv-modal-body { padding: 18px 20px; }
    .inv-modal-footer { border-top: 1px solid rgba(0, 230, 118, 0.15); padding: 14px 20px; }

    .inv-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px 18px;
        background: #0F172A;
        border-top: 1px solid #1E293B;
    }
    .inv-page-info { color: #94A3B8; font-size: 0.78rem; }
    .inv-page-info b { color: #E2E8F0; }
    .inv-page-controls { display: flex; align-items: center; gap: 10px; }
    .inv-page-num { color: #64748B; font-size: 0.75rem; font-weight: 600; }
    .inv-page-num b { color: #34D399; }
    .inv-page-btn {
        background: #1E293B;
        border: 1px solid #334155;
        color: #E2E8F0;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 14px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .inv-page-btn:hover:not(:disabled) { background: #334155; color: #fff; border-color: #475569; }
    .inv-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>

<div class="container-fluid py-4">

    @if(session('success'))
        <div class="mb-3 p-3" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#A7F3D0;border-radius:12px;font-size:0.82rem;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-3 p-3" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#FECDD3;border-radius:12px;font-size:0.82rem;">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="inv-title-row">
        <div class="inv-title-wrap">
            <div class="inv-title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.91 8.84 12 4 3.09 8.84"/>
                    <path d="M12 4v16"/>
                    <path d="M4.5 8.5v7.32l6.59 3.65V12L4.5 8.5z"/>
                    <path d="M19.5 8.5v7.32L12.91 19.5V12l6.59-3.5z"/>
                </svg>
            </div>
            <div>
                <h1 class="inv-title">Inventory Stocks</h1>
                <p class="inv-sub">View all motorcycle products currently stored in inventory.</p>
            </div>
        </div>

        <a href="{{ route('inventory.create') }}" class="inv-add-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Product
        </a>
    </div>

    <div class="inv-filter-bar">
        <div class="inv-search-wrap">
            <i class="bi bi-search"></i>
            <input
                type="text"
                id="inventory-search"
                class="inv-search"
                placeholder="Search product name or serial..."
                autocomplete="off">
        </div>
        <select id="category-filter" class="inv-select">
            <option value="">All Categories</option>
            @foreach($products->pluck('type')->unique()->filter()->sort()->values() as $type)
                <option value="{{ $type }}">{{ $type }}</option>
            @endforeach
        </select>
        <select id="brand-filter" class="inv-select">
            <option value="">All Brands</option>
        </select>
        <select id="color-filter" class="inv-select">
            <option value="">All Colors</option>
        </select>
    </div>

    <div class="inv-card table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Serial Number</th>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th class="text-end">Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="inventory-row"
                        data-id="{{ $product->id }}"
                        data-name="{{ strtolower($product->name) }}"
                        data-serial="{{ strtolower($product->serial_number) }}"
                        data-type="{{ strtolower($product->type ?? '') }}"
                        data-brand="{{ $product->brand ?? '' }}"
                        data-color="{{ $product->color ?? '' }}">

                        <td class="inv-id">{{ $loop->index + 1 }}</td>

                        <td>
                            @if($product->serial_number)
                                <span class="serial-chip" data-bs-toggle="tooltip" title="{{ $product->serial_number }}">{{ $product->serial_number }}</span>
                            @else
                                <span class="inv-muted">—</span>
                            @endif
                        </td>

                        <td class="inv-name">{{ $product->name }}</td>

                        <td class="inv-muted">{{ $product->brand ?? 'N/A' }}</td>

                        <td class="inv-muted">{{ $product->type ?? 'N/A' }}</td>

                        <td class="inv-muted">{{ $product->color ?: '—' }}</td>

                        <td class="inv-muted">{{ $product->size ?? '—' }}</td>

                        <td>
                            <span class="inv-muted">{{ $product->display_quantity }}</span>
                        </td>

                        <td class="price-cell">₱{{ number_format($product->price, 2) }}</td>

                        <td>
                            @if($product->display_quantity == 0)
                                <span class="stk-pill stk-0"><i class="bi bi-circle-fill"></i> Out of Stock</span>
                            @elseif($product->display_quantity <= 5)
                                <span class="stk-pill stk-lo"><i class="bi bi-circle-fill"></i> Low Stock</span>
                            @else
                                <span class="stk-pill stk-ok"><i class="bi bi-circle-fill"></i> In Stock</span>
                            @endif
                        </td>

                        <td class="act-cell">
                            <button
                                type="button"
                                class="act-btn act-view"
                                data-bs-toggle="tooltip"
                                title="View details"
                                onclick="openProductModal(
                                    '{{ addslashes($product->name) }}',
                                    '{{ $product->serial_number }}',
                                    '{{ addslashes($product->brand ?? 'N/A') }}',
                                    '{{ addslashes($product->type ?? 'N/A') }}',
                                    '{{ $product->color ?? '—' }}',
                                    '{{ $product->size ?? '—' }}',
                                    '{{ $product->display_quantity }}',
                                    '{{ number_format($product->price, 2) }}',
                                    '{{ addslashes($product->description ?? 'No description available.') }}'
                                )">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ route('products.edit', $product) }}" class="act-btn act-edit" data-bs-toggle="tooltip" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form class="act-del-form" action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product from inventory?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act-btn act-del" data-bs-toggle="tooltip" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="inv-empty">
                                <i class="bi bi-box-seam"></i>
                                <h5>No Products Found</h5>
                                <p>Add products to see them in inventory.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse

                <tr id="no-filter-results" style="display:none;">
                    <td colspan="11">
                        <div class="inv-empty">
                            <i class="bi bi-search"></i>
                            <h5>No matching products</h5>
                            <p>Try a different search or category.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="inv-pagination">
            <span class="inv-page-info">Showing <b id="invRangeStart">0</b>&ndash;<b id="invRangeEnd">0</b> of <b id="invTotal">0</b></span>
            <div class="inv-page-controls">
                <button type="button" class="inv-page-btn" id="invPrev"><i class="bi bi-chevron-left"></i> Prev</button>
                <span class="inv-page-num">Page <b id="invPageNow">1</b> / <b id="invPageCount">1</b></span>
                <button type="button" class="inv-page-btn" id="invNext">Next <i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content inv-modal-content">
            <div class="inv-modal-header d-flex justify-content-between align-items-center">
                <h5 class="inv-modal-title mb-0"><i class="bi bi-box-seam me-2"></i>Product Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="inv-modal-body" id="productModalBody"></div>
            <div class="inv-modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-secondary btn-sm" style="border-radius:8px;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openProductModal(name, serial, brand, type, color, size, qty, price, desc) {
        const rows = [
            ['Product Name', name],
            ['Serial Number', '<span class="serial-chip">' + serial + '</span>'],
            ['Brand', brand],
            ['Type', type],
            ['Color', color],
            ['Size', size],
            ['Quantity', qty],
            ['Price', '&#8369;' + price],
            ['Description', desc]
        ];
        document.getElementById('productModalBody').innerHTML =
            rows.map(function (r) {
                return '<div class="detail-row"><span class="lbl">' + r[0] + '</span><span class="val">' + r[1] + '</span></div>';
            }).join('');
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('productModal'));
        modal.show();
    }

    (function () {
        const searchInput = document.getElementById('inventory-search');
        const categorySelect = document.getElementById('category-filter');
        const brandSelect = document.getElementById('brand-filter');
        const colorSelect = document.getElementById('color-filter');
        const rows = Array.from(document.querySelectorAll('.inventory-row'));
        const noResults = document.getElementById('no-filter-results');

        const PAGE_SIZE = 10;
        let currentPage = 1;
        let filteredRows = [];
        const pageInfoStart = document.getElementById('invRangeStart');
        const pageInfoEnd = document.getElementById('invRangeEnd');
        const pageInfoTotal = document.getElementById('invTotal');
        const pageNowEl = document.getElementById('invPageNow');
        const pageCountEl = document.getElementById('invPageCount');
        const prevBtn = document.getElementById('invPrev');
        const nextBtn = document.getElementById('invNext');

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
            brandSelect.innerHTML = '<option value="">All Brands</option>';
            brands.forEach(function (b) {
                const opt = document.createElement('option');
                opt.value = b.toLowerCase().trim();
                opt.textContent = b;
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
            colorSelect.innerHTML = '<option value="">All Colors</option>';
            colors.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = c.toLowerCase().trim();
                opt.textContent = c;
                if (c.toLowerCase().trim() === current) opt.selected = true;
                colorSelect.appendChild(opt);
            });
        }

        function applyFilters() {
            const query = (searchInput.value || '').toLowerCase().trim();
            const category = (categorySelect.value || '').toLowerCase().trim();
            const brand = (brandSelect.value || '').toLowerCase().trim();
            const color = (colorSelect.value || '').toLowerCase().trim();

            filteredRows = [];
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

                if (matchesQuery && matchesCategory && matchesBrand && matchesColor) {
                    filteredRows.push(row);
                }
            });

            currentPage = 1;
            renderList();
        }

        function renderList() {
            const total = filteredRows.length;
            const pageCount = Math.max(1, Math.ceil(total / PAGE_SIZE));
            if (currentPage > pageCount) currentPage = pageCount;
            if (currentPage < 1) currentPage = 1;

            rows.forEach(function (row) {
                row.style.display = 'none';
            });

            const start = (currentPage - 1) * PAGE_SIZE;
            const end = Math.min(start + PAGE_SIZE, total);
            for (let i = start; i < end; i++) {
                filteredRows[i].style.display = '';
            }

            if (rows.length === 0) {
                noResults.style.display = 'none';
            } else {
                noResults.style.display = total === 0 ? '' : 'none';
            }

            pageInfoStart.textContent = total ? start + 1 : 0;
            pageInfoEnd.textContent = total ? end : 0;
            pageInfoTotal.textContent = total;
            pageNowEl.textContent = currentPage;
            pageCountEl.textContent = pageCount;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= pageCount;
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

        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderList();
            }
        });
        nextBtn.addEventListener('click', function () {
            const pageCount = Math.max(1, Math.ceil(filteredRows.length / PAGE_SIZE));
            if (currentPage < pageCount) {
                currentPage++;
                renderList();
            }
        });

        populateBrandOptions();
        applyFilters();

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    })();
</script>
@endsection