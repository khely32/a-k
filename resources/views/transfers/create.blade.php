@extends('layouts.app')

@section('content')
<style>
    .create-title {
        color: var(--green);
        text-shadow: 0 0 10px rgba(0, 230, 118, 0.3);
        font-size: 1.3rem;
        font-weight: 800;
    }
    .create-card {
        background: var(--card);
        border: 1px solid rgba(0, 230, 118, 0.12);
        border-radius: 14px;
        overflow: hidden;
    }
    .create-card-header {
        background: linear-gradient(135deg, #0D1117 0%, #161B22 100%);
        border-bottom: 1px solid rgba(0, 230, 118, 0.12);
        padding: 16px 24px;
    }
    .create-card-header h6 {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text);
    }
    .create-card-body {
        padding: 28px 24px;
    }
    .create-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94A3B8;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
    }
    .create-input {
        background: #1E293B;
        border: 1px solid #475569;
        border-radius: 10px;
        color: #FFFFFF;
        font-size: 0.88rem;
        padding: 10px 14px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .create-input:focus {
        border-color: var(--green);
        box-shadow: 0 0 0 3px rgba(0, 230, 118, 0.15);
    }
    .create-input::placeholder {
        color: #64748B;
    }
    .create-input:read-only,
    .create-input[disabled] {
        background: rgba(30, 41, 59, 0.6);
        border-color: #334155;
        color: #94A3B8;
        cursor: not-allowed;
    }
    .create-select {
        background: #1E293B;
        border: 1px solid #475569;
        border-radius: 10px;
        color: #FFFFFF;
        font-size: 0.88rem;
        padding: 10px 14px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: 'Inter', sans-serif;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }
    .create-select:focus {
        border-color: var(--green);
        box-shadow: 0 0 0 3px rgba(0, 230, 118, 0.15);
    }
    .create-select option {
        background: #1E293B;
        color: #FFFFFF;
    }
    .create-btn-submit {
        background: #059669;
        border: none;
        border-radius: 10px;
        color: #FFFFFF;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 10px 28px;
        cursor: pointer;
        transition: background 0.2s, box-shadow 0.2s;
        font-family: 'Inter', sans-serif;
        letter-spacing: 0.3px;
    }
    .create-btn-submit:hover {
        background: #047857;
        box-shadow: 0 4px 20px rgba(5, 150, 105, 0.35);
    }
    .create-btn-cancel {
        background: #475569;
        border: 1px solid #576574;
        border-radius: 10px;
        color: #FFFFFF;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 10px 28px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .create-btn-cancel:hover {
        background: #576574;
        color: #FFFFFF;
    }
    .stock-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .stock-in { background: rgba(0, 230, 118, 0.12); color: #00E676; border: 1px solid rgba(0, 230, 118, 0.3); }
    .stock-low { background: rgba(255, 193, 7, 0.12); color: #FFC107; border: 1px solid rgba(255, 193, 7, 0.3); }
    .stock-out { background: rgba(255, 46, 85, 0.12); color: #FF2E55; border: 1px solid rgba(255, 46, 85, 0.3); }
    .search-result {
        background: rgba(30, 41, 59, 0.95);
        border: 1px solid rgba(0, 230, 118, 0.15);
        border-radius: 12px;
        padding: 12px 16px;
        margin-top: 8px;
    }
    .search-result .list-group-item {
        background: rgba(30, 41, 59, 0.9);
        color: var(--text);
        border: 1px solid rgba(0, 230, 118, 0.1);
    }
    .search-result .list-group-item:hover {
        background: rgba(0, 230, 118, 0.06);
        border-color: rgba(0, 230, 118, 0.2);
    }
    .alert-success {
        background: rgba(0, 230, 118, 0.08);
        border: 1px solid rgba(0, 230, 118, 0.3);
        color: #A7F3D0;
        border-radius: 10px;
    }
    .alert-danger {
        background: rgba(255, 46, 85, 0.08);
        border: 1px solid rgba(255, 46, 85, 0.3);
        color: #FECDD3;
        border-radius: 10px;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-md-12">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="create-title">
                    @if(strtolower(auth()->user()->role) == 'owner')
                        New Stock Transfer
                    @else
                        Request Stock Transfer
                    @endif
                </h1>
            </div>

            @if(session('success'))
                <div class="alert-success mb-4 p-3">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-danger mb-4 p-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="create-card">
                <div class="create-card-header">
                    <h6><i class="bi bi-arrow-left-right me-2" style="color:var(--green)"></i>Transfer Details</h6>
                </div>

                <div class="create-card-body">
                    <form action="{{ route('transfers.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">

                            @if(strtolower(auth()->user()->role) == 'owner')

                                <div class="col-md-6 mb-3">
                                    <label class="create-label">Source Branch</label>
                                    <select name="from_branch_id" id="from_branch_id" class="create-select" required>
                                        <option value="">-- Select Source Branch --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="create-label">Destination Branch</label>
                                    <select name="to_branch_id" class="create-select" required>
                                        <option value="">-- Select Destination Branch --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            @else

                                <div class="col-md-6 mb-3">
                                    <label class="create-label">Request Stock From</label>
                                    <select name="from_branch_id" id="from_branch_id" class="create-select" required>
                                        <option value="">-- Select Source Branch --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="create-label">Deliver To</label>
                                    <input type="text" class="create-input" value="{{ optional($destinationBranch)->branch_name }}" readonly disabled>
                                    <input type="hidden" name="to_branch_id" value="{{ optional($destinationBranch)->id }}">
                                </div>

                            @endif

                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label class="create-label">Search Product</label>
                                <input type="text" id="product_search" class="create-input" placeholder="Type product name, brand, or serial number..." autocomplete="off">
                                <input type="hidden" name="product_id" id="product_id">
                                <div id="product_results" class="mt-2" style="display:none;"></div>
                            </div>

                            <div class="col-md-12">
                                <div id="stock_info" style="display:none;" class="search-result">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong id="stock_product_name" style="color:#FFFFFF"></strong>
                                            <span id="stock_badge" class="stock-badge ms-2"></span>
                                        </div>
                                        <div class="text-end small" style="color:#94A3B8">
                                            Qty Available: <strong id="stock_qty" style="color:#FFFFFF">0</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="create-label">Quantity</label>
                                <input type="number" name="quantity" class="create-input" min="1" value="{{ old('quantity') }}" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('transfers.index') }}" class="create-btn-cancel">
                                <i class="bi bi-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="create-btn-submit">
                                <i class="bi bi-send me-1"></i> Submit Request
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
let selectedProductId = null;
let searchTimeout = null;
const allProducts = @json($products);

const productSearch = document.getElementById('product_search');
const productResults = document.getElementById('product_results');
const productIdInput = document.getElementById('product_id');
const stockInfo = document.getElementById('stock_info');
const stockProductName = document.getElementById('stock_product_name');
const stockBadge = document.getElementById('stock_badge');
const stockQty = document.getElementById('stock_qty');
const fromBranchSelect = document.getElementById('from_branch_id');

productSearch.addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const q = this.value.trim().toLowerCase();
    if (q.length < 1) {
        productResults.style.display = 'none';
        productResults.innerHTML = '';
        productIdInput.value = '';
        stockInfo.style.display = 'none';
        return;
    }
    searchTimeout = setTimeout(() => {
        const matches = allProducts.filter(p =>
            p.name.toLowerCase().includes(q) ||
            (p.brand && p.brand.toLowerCase().includes(q)) ||
            (p.serial_number && p.serial_number.toLowerCase().includes(q))
        ).slice(0, 10);

        if (matches.length === 0) {
            productResults.innerHTML = '<div class="p-2" style="color:#94A3B8;font-size:0.82rem">No products found.</div>';
            productResults.style.display = 'block';
            productIdInput.value = '';
            stockInfo.style.display = 'none';
            return;
        }

        let html = '<div class="list-group" style="background:transparent;">';
        matches.forEach(p => {
            html += `<a href="#" class="list-group-item list-group-item-action" data-id="${p.id}" data-name="${p.name}">`;
            html += `<div class="fw-semibold">${p.name}</div>`;
            html += `<small style="color:#94A3B8">${p.brand || ''} ${p.serial_number ? '#'+p.serial_number : ''}</small>`;
            html += '</a>';
        });
        html += '</div>';
        productResults.innerHTML = html;
        productResults.style.display = 'block';

        productResults.querySelectorAll('a').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                selectedProductId = this.dataset.id;
                productIdInput.value = selectedProductId;
                productSearch.value = this.dataset.name;
                productResults.style.display = 'none';
                checkStock();
            });
        });
    }, 300);
});

document.addEventListener('click', function (e) {
    if (!productResults.contains(e.target) && e.target !== productSearch) {
        productResults.style.display = 'none';
    }
});

if (fromBranchSelect) {
    fromBranchSelect.addEventListener('change', function () {
        checkStock();
    });
}

function checkStock() {
    const branchId = fromBranchSelect?.value;
    if (!branchId || !selectedProductId) {
        if (stockInfo) stockInfo.style.display = 'none';
        return;
    }

    fetch(`/transfers/check-stock?source_branch_id=${branchId}&product_id=${selectedProductId}`)
        .then(r => r.json())
        .then(data => {
            const product = allProducts.find(p => p.id == selectedProductId);
            stockProductName.textContent = product ? product.name : 'Unknown';

            stockBadge.textContent = data.label;
            stockBadge.className = 'stock-badge ms-2';
            if (data.status === 'in_stock') stockBadge.classList.add('stock-in');
            else if (data.status === 'low_stock') stockBadge.classList.add('stock-low');
            else stockBadge.classList.add('stock-out');

            stockQty.textContent = data.quantity;
            stockInfo.style.display = 'block';
        })
        .catch(() => {
            if (stockInfo) stockInfo.style.display = 'none';
        });
}
</script>
@endsection
