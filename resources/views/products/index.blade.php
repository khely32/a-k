@extends('layouts.app')

@section('content')

@php
function productColorHex($name)
{
    $map = [
        'red' => '#ef4444', 'blue' => '#3b82f6', 'black' => '#111827', 'white' => '#f8fafc',
        'green' => '#22c55e', 'yellow' => '#eab308', 'orange' => '#f97316', 'purple' => '#a855f7',
        'pink' => '#ec4899', 'gray' => '#6b7280', 'grey' => '#6b7280', 'silver' => '#cbd5e1',
        'brown' => '#92400e', 'gold' => '#eab308', 'chrome' => '#d1d5db'
    ];
    $key = strtolower(trim((string) $name));
    foreach ($map as $word => $hex) { if (str_contains($key, $word)) return $hex; }
    return '#334155';
}
$bc = [8=>'#10B981',9=>'#06B6D4',10=>'#F59E0B',11=>'#8B5CF6'];
$ba = [];
foreach ($branches as $b) {
    $words = explode(' ', $b->branch_name);
    $a = '';
    foreach ($words as $x) { if (strtolower($x)==='branch') continue; $a .= strtoupper(substr($x,0,3)); }
    $ba[$b->id] = $a ?: strtoupper(substr($b->branch_name,0,5));
}
$ls = 0; $os = 0;
foreach ($products as $p) { foreach ($branches as $br) { $q = $p->branch_stock[$br->id] ?? 0; if ($q==0) $os++; elseif ($q<=5) $ls++; } }
@endphp

<style>
    .bc-hdr{text-align:center!important;font-size:.72rem!important;letter-spacing:.04em;min-width:85px;white-space:nowrap;}
    .bc-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px;vertical-align:middle;}
    .stk{display:inline-block;padding:2px 10px;border-radius:999px;font-size:.72rem;font-weight:700;min-width:42px;text-align:center;}
    .stk-0{background:rgba(239,68,68,.12);color:#EF4444;border:1px solid rgba(239,68,68,.3);}
    .stk-lo{background:rgba(251,191,36,.12);color:#FBBF24;border:1px solid rgba(251,191,36,.3);}
    .stk-ok{background:rgba(0,230,118,.1);color:#10B981;border:1px solid rgba(0,230,118,.25);}
    #productsTable th,#productsTable td{white-space:nowrap;}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color:var(--text);">
                <i class="bi bi-grid-3x3-gap me-2" style="color:var(--cyan);"></i> Master Products View
            </h1>
            <p class="mb-0" style="color:#9CA3AF;font-size:.85rem;">
                Unified catalog showing stock across <strong style="color:var(--cyan);">{{ count($branches) }} branches</strong>
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            @if($isMainBranch)
            <!-- Neon button for Bulk Import -->
            <button type="button" class="neon-btn" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Bulk Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary d-flex align-items-center px-4">
                <i class="bi bi-plus-lg me-2"></i> Add Product
            </a>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-dismissible fade show border-0 shadow-sm" role="alert" style="background:rgba(34,197,94,0.12);color:#22c55e;border-left:4px solid #22c55e;border-radius:12px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-dismissible fade show border-0 shadow-sm mt-2" role="alert" style="background:rgba(251,191,36,0.12);color:#fbbf24;border-left:4px solid #fbbf24;border-radius:12px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {!! session('warning') !!}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-dismissible fade show border-0 shadow-sm" role="alert" style="background:rgba(239,68,68,0.12);color:#ef4444;border-left:4px solid #ef4444;border-radius:12px;">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-dismissible fade show border-0 shadow-sm" role="alert" style="background:rgba(239,68,68,0.12);color:#ef4444;border-left:4px solid #ef4444;border-radius:12px;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle-fill me-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Dashboard Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-4 border-0" style="background:var(--card);border:1px solid rgba(0,229,255,.15);border-radius:16px;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background:rgba(0,229,255,.1);width:54px;height:54px;">
                        <i class="bi bi-grid-3x3-gap fs-4" style="color:var(--cyan);"></i>
                    </div>
                    <div class="ms-3">
                        <p class="mb-0 small fw-bold" style="color:var(--text);opacity:.6;font-size:.68rem;">TOTAL PRODUCTS</p>
                        <h3 class="fw-bold mb-0" style="color:var(--text);">{{ count($products) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0" style="background:var(--card);border:1px solid rgba(251,191,36,.15);border-radius:16px;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background:rgba(251,191,36,.1);width:54px;height:54px;">
                        <i class="bi bi-exclamation-triangle fs-4" style="color:#FBBF24;"></i>
                    </div>
                    <div class="ms-3">
                        <p class="mb-0 small fw-bold" style="color:var(--text);opacity:.6;font-size:.68rem;">LOW / OUT OF STOCK</p>
                        <h3 class="fw-bold mb-0">
                            <span style="color:#FBBF24;">{{ $ls }}</span>
                            <span style="color:var(--text);opacity:.3;">/</span>
                            <span style="color:#EF4444;">{{ $os }}</span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0" style="background:var(--card);border:1px solid rgba(0,230,118,.15);border-radius:16px;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background:rgba(0,230,118,.1);width:54px;height:54px;">
                        <span class="fs-4 fw-bold" style="color:#10B981;">&#8369;</span>
                    </div>
                    <div class="ms-3">
                        <p class="mb-0 small fw-bold" style="color:var(--text);opacity:.6;font-size:.68rem;">INVENTORY VALUE</p>
                        <h3 class="fw-bold mb-0" style="color:#10B981;">&#8369;{{ number_format($totalInventoryValue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0" style="background:var(--card);border:1px solid rgba(157,78,221,.15);border-radius:16px;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background:rgba(157,78,221,.1);width:54px;height:54px;">
                        <i class="bi bi-building fs-4" style="color:#a78bfa;"></i>
                    </div>
                    <div class="ms-3">
                        <p class="mb-0 small fw-bold" style="color:var(--text);opacity:.6;font-size:.68rem;">BRANCHES</p>
                        <h3 class="fw-bold mb-0" style="color:#a78bfa;">{{ count($branches) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 mb-4" style="background:var(--card);border:1px solid rgba(239,68,68,.1);">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4 position-relative">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, brand, or serial..." style="padding-left:35px;border-radius:10px;">
                    <i class="bi bi-search position-absolute" style="left:20px;top:50%;transform:translateY(-50%);color:var(--accent);opacity:.5;"></i>
                </div>
                <div class="col-md-3">
                    <select id="typeFilter" class="form-select" style="border-radius:10px;">
                        <option value="">All Types</option>
                        @foreach($products->pluck('type')->unique()->filter()->values() as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="brandFilter" class="form-select" style="border-radius:10px;">
                        <option value="">All Brands</option>
                        @foreach($products->pluck('brand')->unique()->filter()->values() as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button id="clearFilters" class="btn w-100" style="border-radius:10px;border:1px solid rgba(239,68,68,.3);color:var(--text);">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table with Multi-Branch Stock -->
    <div class="card border-0" style="background:var(--card);border:1px solid rgba(239,68,68,.1);overflow-x:auto;">
        <div class="table-responsive" style="min-width:0;">
            <table class="table table-hover align-middle mb-0" id="productsTable">
                <thead>
                    <tr style="background:linear-gradient(90deg,#111827,#1e293b);border-bottom:2px solid rgba(0,229,255,.15);">
                        <th class="py-3 ps-4" style="color:var(--cyan);font-weight:700;font-size:.75rem;">SKU</th>
                        <th class="py-3" style="color:var(--cyan);font-weight:700;font-size:.75rem;">Part Description</th>
                        <th class="py-3" style="color:var(--cyan);font-weight:700;font-size:.75rem;">Brand</th>
                        <th class="py-3" style="color:var(--cyan);font-weight:700;font-size:.75rem;">Type</th>
                        <th class="py-3" style="color:var(--cyan);font-weight:700;font-size:.75rem;">Color</th>
                        <th class="py-3" style="color:var(--cyan);font-weight:700;font-size:.75rem;">Size</th>
                        <th class="py-3" style="color:var(--cyan);font-weight:700;font-size:.75rem;">Price</th>
                        @foreach($branches as $branch)
                        <th class="py-3 bc-hdr" style="color:{{ $bc[$branch->id] ?? '#94A3B8' }};font-weight:700;">
                            <span class="bc-dot" style="background:{{ $bc[$branch->id] ?? '#94A3B8' }};"></span>{{ $ba[$branch->id] }}
                        </th>
                        @endforeach
                        <th class="py-3" style="color:#fff;font-weight:700;font-size:.75rem;text-align:center;min-width:60px;">Total</th>
                        <th class="py-3 text-center pe-4" style="color:var(--cyan);font-weight:700;font-size:.75rem;width:90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="product-row" style="background:#fff;border-bottom:1px solid #e2e8f0;"
                        data-name="{{ strtolower($product->name) }}"
                        data-brand="{{ strtolower($product->brand ?? '') }}"
                        data-type="{{ strtolower($product->type ?? '') }}"
                        data-serial="{{ strtolower($product->serial_number) }}">
                        <td class="ps-4 fw-bold" style="font-family:monospace;color:#0f172a;font-size:.82rem;">{{ $product->serial_number }}</td>
                        <td class="fw-bold" style="color:#0f172a;font-size:.85rem;">{{ $product->name }}</td>
                        <td><span class="badge" style="background:#e2e8f0;color:#0f172a;font-weight:600;font-size:.72rem;">{{ $product->brand ?? 'N/A' }}</span></td>
                        <td><span class="badge" style="background:#e2e8f0;color:#0f172a;font-weight:600;font-size:.72rem;">{{ $product->type ?? 'Uncategorized' }}</span></td>
                        <td>
                            @if($product->color)
                            <span style="display:inline-flex;align-items:center;gap:4px;color:#0f172a;font-size:.82rem;">
                                <span style="width:12px;height:12px;border-radius:50%;background:{{ productColorHex($product->color) }};display:inline-block;border:1px solid #cbd5e1;"></span>
                                {{ $product->color }}
                            </span>
                            @else <span style="color:#94a3b8;">&mdash;</span> @endif
                        </td>
                        <td style="color:#0f172a;font-size:.82rem;">{{ $product->size ?? '&mdash;' }}</td>
                        <td class="fw-bold" style="color:#16a34a;font-size:.82rem;">&#8369;{{ number_format($product->price, 2) }}</td>
                        @foreach($branches as $branch)
                        @php $qty = $product->branch_stock[$branch->id] ?? 0; @endphp
                        <td style="text-align:center;">
                            @if($qty == 0)
                            <span class="stk stk-0">0</span>
                            @elseif($qty <= 5)
                            <span class="stk stk-lo">{{ $qty }}</span>
                            @else
                            <span class="stk stk-ok">{{ $qty }}</span>
                            @endif
                        </td>
                        @endforeach
                        <td style="text-align:center;">
                            <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:.75rem;font-weight:800;background:rgba(0,229,255,.1);color:var(--cyan);border:1px solid rgba(0,229,255,.2);">
                                {{ $product->total_stock }}
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            @if($isMainBranch)
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('products.edit', $product->id) }}" class="btn" style="background:rgba(0,229,255,.1);color:var(--cyan);border:none;border-radius:6px;padding:5px 10px;text-decoration:none;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button type="button" class="btn ms-1" style="background:rgba(239,68,68,.1);color:#EF4444;border:none;border-radius:6px;padding:5px 10px;" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 7 + count($branches) + 2 }}" class="text-center py-5" style="color:#64748b;">
                            <i class="bi bi-inboxes fs-1 d-block mb-3" style="color:#94a3b8;"></i>
                            <span class="fs-5">No parts found.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= BULK IMPORT MODAL ================= -->
@if($isMainBranch)
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 text-white" style="background:var(--card);border:1px solid rgba(239,68,68,0.2);border-radius:20px;box-shadow:0 0 30px rgba(239,68,68,0.15);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:var(--accent);"><i class="bi bi-file-earmark-spreadsheet me-2" style="color:var(--accent);"></i> Bulk Product Import</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body py-4">
                    <div class="mb-4">
                        <p class="text-muted small">Upload your spreadsheet export containing products. The system will match your headers case-insensitively and populate database rows. If an item already exists (matched by Serial Number or Name/Brand combination), its inventory count and pricing will be synchronized.</p>
                    </div>

                    <!-- Drag and Drop Dropzone -->
                    <div id="dropZone" class="text-center p-5 border-2 border-dashed rounded-3 position-relative mb-4"
                         style="border-color:rgba(239,68,68,0.3);background:rgba(239,68,68,0.02);cursor:pointer;transition:0.3s;border-radius:15px;">
                        <input type="file" name="csv_file" id="csvFileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" accept=".csv" required style="cursor:pointer;">
                        <i class="bi bi-cloud-arrow-up fs-1 mb-2 d-block" style="color:var(--accent);"></i>
                        <h5 class="fw-bold" style="color:var(--text);">Drag and drop your CSV file here</h5>
                        <p class="small mb-0" style="color:var(--text);opacity:0.6;">or click to browse local files (max 4MB)</p>
                        <div id="selectedFileInfo" class="mt-3 fw-bold d-none" style="color:var(--accent);"></div>
                    </div>

                    <!-- CSV Column Guidelines -->
                    <div class="card p-3 border-0 mb-4" style="background:rgba(239,68,68,0.03);border-radius:12px;border:1px solid rgba(239,68,68,0.1);">
                        <h6 class="fw-bold mb-2 small" style="color:var(--accent);"><i class="bi bi-info-circle me-1"></i> CSV Column Formatting Map:</h6>
                        <div class="row g-2">
                            <div class="col-md-6 small" style="color:var(--text);opacity:0.7;">
                                <div><strong style="color:var(--text);">name</strong> <span style="color:#ef4444;">*</span> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">Part Name, Description</code>)</div>
                                <div><strong style="color:var(--text);">price</strong> <span style="color:#ef4444;">*</span> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">Retail Price, Rate</code>)</div>
                                <div><strong style="color:var(--text);">quantity</strong> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">Qty, Stock, Stock Level</code>)</div>
                            </div>
                            <div class="col-md-6 small" style="color:var(--text);opacity:0.7;">
                                <div><strong style="color:var(--text);">brand</strong> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">Manufacturer, Make</code>)</div>
                                <div><strong style="color:var(--text);">type</strong> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">Category, Class</code>)</div>
                                <div><strong style="color:var(--text);">size</strong> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">Volume, Size, Capacity</code>)</div>
                                <div><strong style="color:var(--text);">serial_number</strong> (e.g. <code style="color:var(--accent);background:rgba(239,68,68,0.1);padding:1px 4px;border-radius:4px;">SKU, Code, Item Code</code>)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Live CSV Parsing Preview (Hidden by default) -->
                    <div id="csvPreviewWrapper" class="d-none">
                        <h6 class="fw-bold text-white mb-2"><i class="bi bi-eye text-info me-1"></i> Data Preview:</h6>
                        <div class="table-responsive border border-secondary rounded mb-3" style="max-height: 200px; background: rgba(0,0,0,0.2);">
                            <table class="table table-sm table-dark table-striped mb-0 text-white-50 small" id="previewTable">
                                <thead>
                                    <!-- Dynamic headers -->
                                </thead>
                                <tbody>
                                    <!-- Dynamic preview data rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm" id="downloadTemplateBtn" style="border-radius:10px;border:1px solid rgba(239,68,68,0.3);color:var(--accent);background:transparent;">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:var(--text);">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="submitImportBtn" disabled style="border-radius:10px;">
                            <i class="bi bi-upload me-1"></i> Import Now
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- ================= DELETE CONFIRMATION MODAL ================= -->
@if($isMainBranch)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 text-white" style="background:var(--card);border:1px solid rgba(239,68,68,0.2);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#ef4444;"><i class="bi bi-trash3-fill me-2"></i> Delete Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p style="color:var(--text);">Are you sure you want to permanently delete <strong id="deleteProductName" style="color:#fbbf24;">this product</strong>?</p>
                <p class="small mb-0" style="color:#ef4444;"><i class="bi bi-exclamation-triangle-fill me-1"></i> This will delete the product and all stock records across all branches. Cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:var(--text);">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4" style="border-radius:10px;">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Client-Side Search and Filter Logic, Drag-and-Drop, CSV Parsing Preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ----------------------------------------------------
    // Search and Filtering Code
    // ----------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const brandFilter = document.getElementById('brandFilter');
    const clearFilters = document.getElementById('clearFilters');
    const rows = document.querySelectorAll('.product-row');

    function filterTable() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedType = typeFilter.value.toLowerCase();
        const selectedBrand = brandFilter.value.toLowerCase();

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const brand = row.getAttribute('data-brand');
            const type = row.getAttribute('data-type');
            const serial = row.getAttribute('data-serial');

            const matchesSearch = name.includes(query) || brand.includes(query) || type.includes(query) || serial.includes(query);
            const matchesType = !selectedType || type === selectedType;
            const matchesBrand = !selectedBrand || brand === selectedBrand;

            if (matchesSearch && matchesType && matchesBrand) {
                row.classList.remove('d-none');
            } else {
                row.classList.add('d-none');
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (typeFilter) typeFilter.addEventListener('change', filterTable);
    if (brandFilter) brandFilter.addEventListener('change', filterTable);

    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            searchInput.value = '';
            typeFilter.value = '';
            brandFilter.value = '';
            filterTable();
        });
    }

    // ----------------------------------------------------
    // Delete Modal Handler
    // ----------------------------------------------------
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            
            const deleteForm = document.getElementById('deleteForm');
            const deleteProductName = document.getElementById('deleteProductName');
            
            deleteProductName.textContent = productName;
            deleteForm.action = `/products/${productId}`;
        });
    }

    // ----------------------------------------------------
    // Drag-and-Drop and CSV Live Preview
    // ----------------------------------------------------
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('csvFileInput');
    const selectedFileInfo = document.getElementById('selectedFileInfo');
    const previewWrapper = document.getElementById('csvPreviewWrapper');
    const previewTable = document.getElementById('previewTable');
    const submitBtn = document.getElementById('submitImportBtn');

    // Visual drop effect
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--accent)';
            dropZone.style.background = 'rgba(239, 68, 68, 0.08)';
            dropZone.style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.2)';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'rgba(239,68,68,0.3)';
            dropZone.style.background = 'rgba(239, 68, 68, 0.02)';
            dropZone.style.boxShadow = 'none';
        }, false);
    });

    fileInput.addEventListener('change', function(e) {
        const file = fileInput.files[0];
        if (file) {
            handleSelectedFile(file);
        }
    });

    function handleSelectedFile(file) {
        if (file.name.split('.').pop().toLowerCase() !== 'csv') {
            selectedFileInfo.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i> Invalid file type! Please select a .csv file.</span>`;
            selectedFileInfo.classList.remove('d-none');
            previewWrapper.classList.add('d-none');
            submitBtn.disabled = true;
            return;
        }

        selectedFileInfo.innerHTML = `<i class="bi bi-file-earmark-check-fill me-1"></i> File selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        selectedFileInfo.classList.remove('d-none');

        // Parse CSV for Client-Side Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            parseAndPreviewCSV(text);
        };
        reader.readAsText(file);
    }

    function parseAndPreviewCSV(text) {
        // Standard CSV parsing splits lines, handling basic double-quotes
        const lines = text.split(/\r\n|\n/);
        if (lines.length === 0) return;

        const tableHead = previewTable.querySelector('thead');
        const tableBody = previewTable.querySelector('tbody');
        tableHead.innerHTML = '';
        tableBody.innerHTML = '';

        // Extract header
        const headerRow = splitCsvRow(lines[0]);
        if (headerRow.length === 0 || headerRow[0] === "") {
            submitBtn.disabled = true;
            return;
        }

        let headerTr = document.createElement('tr');
        headerRow.forEach(col => {
            let th = document.createElement('th');
            th.textContent = col;
            headerTr.appendChild(th);
        });
        tableHead.appendChild(headerTr);

        // Preview at most 5 data rows
        let rowsAdded = 0;
        for (let i = 1; i < lines.length; i++) {
            if (!lines[i].trim()) continue;
            let cols = splitCsvRow(lines[i]);
            let tr = document.createElement('tr');
            cols.forEach(col => {
                let td = document.createElement('td');
                td.textContent = col;
                tr.appendChild(td);
            });
            tableBody.appendChild(tr);
            rowsAdded++;
            if (rowsAdded >= 5) break;
        }

        previewWrapper.classList.remove('d-none');
        submitBtn.disabled = false; // Enable submit button
    }

    function splitCsvRow(line) {
        let result = [];
        let insideQuotes = false;
        let entry = "";
        
        for (let i = 0; i < line.length; i++) {
            let char = line[i];
            if (char === '"') {
                insideQuotes = !insideQuotes;
            } else if (char === ',' && !insideQuotes) {
                result.push(entry.trim());
                entry = "";
            } else {
                entry += char;
            }
        }
        result.push(entry.trim());
        return result;
    }

    // ----------------------------------------------------
    // CSV Template Downloader
    // ----------------------------------------------------
    const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
    if (downloadTemplateBtn) {
        downloadTemplateBtn.addEventListener('click', function() {
            const csvContent = "data:text/csv;charset=utf-8," 
                + "name,brand,type,color,size,quantity,price,serial_number\n"
                + "Michelin Pilot Street Tire,Michelin,Tires,,17 inch,20,1850.00,AK-TIR001\n"
                + "NGK Iridium Spark Plug CPR9EAIX-9,NGK,Spark Plugs,,Iridium,50,480.00,AK-SPK002\n"
                + "Yamalube 4T Motor Oil 1L,Yamalube,Lubricants,,1L,15,320.00,AK-OIL003\n"
                + "Boysen Spray Paint,Bosny,Spray Paint,Red,400mL,12,180.00,AK-PNT001\n";
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "ak_motorcycle_parts_template.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
</script>
@endsection