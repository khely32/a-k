@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --bg-cyber: #0a0b10;
        --neon-cyan: #00f2fe;
        --neon-pink: #ff007f;
        --neon-purple: #9d4edd;
        --neon-green: #39ff14;
        --neon-orange: #ff8c00;
        --neon-red: #ff1744;
        --text-muted: #8b9bb4;
        --card-bg: linear-gradient(135deg, rgba(20, 24, 33, 0.85), rgba(10, 11, 16, 0.95));
    }

    body {
        background-color: var(--bg-cyber) !important;
    }

    .header-wrapper {
        text-align: center;
        margin-bottom: 2rem;
        padding-top: 1rem;
    }

    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 0, 127, 0.15);
        border: 1px solid var(--neon-pink);
        padding: 6px 18px;
        border-radius: 50px;
        color: var(--neon-pink);
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        animation: pulse-badge 2s infinite;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: var(--neon-pink);
        border-radius: 50%;
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.2; }
    }

    @keyframes pulse-badge {
        0%, 100% { box-shadow: 0 0 5px rgba(255, 0, 127, 0.3); }
        50% { box-shadow: 0 0 20px rgba(255, 0, 127, 0.6); }
    }

    .branch-card {
        background: var(--card-bg);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 24px;
        margin-bottom: 24px;
        transition: all 0.3s ease;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
    }

    .branch-card:hover {
        border-color: var(--neon-cyan);
        box-shadow: 0 0 25px rgba(0, 242, 254, 0.15);
        transform: translateY(-2px);
    }

    .branch-name {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        text-shadow: 0 0 10px rgba(0, 242, 254, 0.3);
    }

    .branch-address {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .summary-stat {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .stat-low { background: rgba(255, 140, 0, 0.15); color: var(--neon-orange); border: 1px solid rgba(255, 140, 0, 0.3); }
    .stat-out { background: rgba(255, 23, 68, 0.15); color: var(--neon-red); border: 1px solid rgba(255, 23, 68, 0.3); }
    .stat-ok { background: rgba(57, 255, 20, 0.1); color: var(--neon-green); border: 1px solid rgba(57, 255, 20, 0.2); }

    .stock-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 12px;
    }

    .stock-table th {
        color: var(--neon-cyan);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 10px 12px;
        border-bottom: 1px solid rgba(0, 242, 254, 0.15);
        font-weight: 700;
    }

    .stock-table td {
        padding: 8px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 0.9rem;
    }

    .stock-table tr:hover td {
        background: rgba(0, 242, 254, 0.03);
    }

    .qty-badge {
        display: inline-block;
        padding: 2px 12px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .qty-ok { background: rgba(57, 255, 20, 0.15); color: var(--neon-green); }
    .qty-low { background: rgba(255, 140, 0, 0.2); color: var(--neon-orange); animation: pulse-orange 1.5s infinite; }
    .qty-out { background: rgba(255, 23, 68, 0.2); color: var(--neon-red); animation: pulse-red 1.5s infinite; }

    @keyframes pulse-orange {
        0%, 100% { box-shadow: 0 0 5px rgba(255, 140, 0, 0.2); }
        50% { box-shadow: 0 0 15px rgba(255, 140, 0, 0.5); }
    }

    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 5px rgba(255, 23, 68, 0.2); }
        50% { box-shadow: 0 0 15px rgba(255, 23, 68, 0.5); }
    }

    .low-stock-row td { background: rgba(255, 140, 0, 0.05) !important; }
    .out-of-stock-row td { background: rgba(255, 23, 68, 0.05) !important; }

    .card-actions {
        display: flex;
        gap: 18px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .btn-cyber-neon {
        background: transparent;
        color: var(--neon-cyan);
        border: 2px solid var(--neon-cyan);
        padding: 12px 28px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .btn-cyber-neon:hover {
        background: var(--neon-cyan);
        color: #010206;
        box-shadow: 0 0 25px var(--neon-cyan), 0 0 45px rgba(0, 242, 254, 0.4);
        transform: scale(1.02);
    }

    .last-updated {
        color: var(--text-muted);
        font-size: 0.8rem;
        text-align: center;
        margin-top: 20px;
    }

    .filter-bar {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 24px;
    }

    .filter-bar input {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(0, 242, 254, 0.2);
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        min-width: 260px;
        font-size: 0.9rem;
    }

    .filter-bar input:focus {
        outline: none;
        border-color: var(--neon-cyan);
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
    }

    .filter-bar select {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(0, 242, 254, 0.2);
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .filter-bar select:focus {
        outline: none;
        border-color: var(--neon-cyan);
    }

    .filter-bar select option {
        background: #0f172a;
        color: white;
    }
</style>

<div class="container py-4">
    <div class="header-wrapper">
        <div class="live-badge">
            <span class="live-dot"></span>
            LIVE MONITORING
        </div>
        <h1 style="font-size: 2rem; font-weight: 900; color: white; margin-top: 12px; letter-spacing: 2px;">
            Real-Time Branch Stock Monitor
        </h1>
        <p style="color: var(--text-muted);">
            <i class="fas fa-store"></i> {{ $branches->count() }} Branches &middot;
                <i class="fas fa-boxes"></i> <span id="total-items-count">{{ $totalProducts }}</span> Products
        </p>
        <div class="filter-bar">
            <input type="text" id="search-input" placeholder="Search product name, brand, or serial..." />
            <select id="branch-filter">
                <option value="all">All Branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <select id="stock-filter">
                <option value="all">All Stock Status</option>
                <option value="low_stock">Low Stock (&le;5)</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="in_stock">In Stock</option>
            </select>
            <select id="category-filter">
                <option value="all">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div id="branch-cards-container">
        @foreach ($branches as $branch)
            @php
                $branchInventories = $branch->inventories->map(function ($inv) {
                    return (object) [
                        'product_id'    => $inv->product_id,
                        'product_name'  => $inv->product->name ?? 'Unknown',
                        'serial_number' => $inv->product->serial_number ?? '',
                        'brand'         => $inv->product->brand ?? '',
                        'category'      => $inv->product->type ?? '',
                        'quantity'      => (int) $inv->quantity,
                        'price'         => $inv->product->price ?? 0,
                        'status'        => $inv->quantity <= 0 ? 'out_of_stock' : ($inv->quantity <= 5 ? 'low_stock' : 'in_stock'),
                    ];
                })->sortBy('product_name');
                $lowCount = $branchInventories->where('status', 'low_stock')->count();
                $outCount = $branchInventories->where('status', 'out_of_stock')->count();
            @endphp
            <div class="branch-card" data-branch-id="{{ $branch->id }}">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <div class="branch-name" data-branch-name="{{ $branch->name }}">
                            <i class="fas fa-store" style="color: var(--neon-cyan); margin-right: 8px;"></i>
                            {{ $branch->name }}
                        </div>
                        <div class="branch-address">{{ $branch->address }}</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="summary-stat stat-ok">
                            <i class="fas fa-box"></i> <span class="branch-total-items">{{ $branchInventories->count() }}</span> items
                        </span>
                        <span class="summary-stat stat-low" style="{{ $lowCount === 0 ? 'display:none' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i> <span class="branch-low-count">{{ $lowCount }}</span> low
                        </span>
                        <span class="summary-stat stat-out" style="{{ $outCount === 0 ? 'display:none' : '' }}">
                            <i class="fas fa-times-circle"></i> <span class="branch-out-count">{{ $outCount }}</span> out
                        </span>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="stock-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Serial</th>
                                <th style="text-align:center;">Quantity</th>
                                <th style="text-align:right;">Price</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branchInventories as $item)
                                <tr class="{{ $item->status === 'out_of_stock' ? 'out-of-stock-row' : ($item->status === 'low_stock' ? 'low-stock-row' : '') }}">
                                    <td><strong>{{ $item->product_name }}</strong></td>
                                    <td style="color: var(--text-muted);">{{ $item->category }}</td>
                                    <td style="color: var(--text-muted);">{{ $item->brand }}</td>
                                    <td style="color: var(--text-muted); font-size:0.8rem;">{{ $item->serial_number }}</td>
                                    <td style="text-align:center;">
                                        <span class="qty-badge {{ $item->status === 'out_of_stock' ? 'qty-out' : ($item->status === 'low_stock' ? 'qty-low' : 'qty-ok') }}">
                                            {{ $item->quantity }}
                                        </span>
                                    </td>
                                    <td style="text-align:right; color: var(--neon-green);">
                                        ₱{{ number_format($item->price, 2) }}
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($item->status === 'out_of_stock')
                                            <span style="color: var(--neon-red);"><i class="fas fa-times-circle"></i> Out</span>
                                        @elseif ($item->status === 'low_stock')
                                            <span style="color: var(--neon-orange);"><i class="fas fa-exclamation-triangle"></i> Low</span>
                                        @else
                                            <span style="color: var(--neon-green);"><i class="fas fa-check-circle"></i> In Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; color: var(--text-muted); padding: 30px;">
                                        <i class="fas fa-box-open"></i> No inventory recorded for this branch.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <div class="branch-card" id="stock-summary-panel" style="border-color: rgba(0, 242, 254, 0.25);">
        <div style="text-align:center; margin-bottom: 16px;">
            <span style="color: var(--neon-cyan); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;">
                <i class="fas fa-clipboard-list"></i> STOCK SUMMARY
            </span>
        </div>
        @php
            $summaryInitial = [
                'total' => 0,
                'in'    => 0,
                'low'   => 0,
                'out'   => 0,
            ];
            foreach ($branches as $branch) {
                foreach ($branch->inventories as $inv) {
                    $qty = (int) $inv->quantity;
                    $summaryInitial['total']++;
                    if ($qty <= 0) { $summaryInitial['out']++; }
                    elseif ($qty <= 5) { $summaryInitial['low']++; }
                    else { $summaryInitial['in']++; }
                }
            }
        @endphp
        <div class="d-flex justify-content-center flex-wrap" style="gap: 14px;">
            <span class="summary-stat stat-ok" style="font-size: 1rem; padding: 8px 22px;">
                <i class="fas fa-box"></i> <span id="summary-total-items">{{ $summaryInitial['total'] }}</span> Total Items
            </span>
            <span class="summary-stat stat-ok" style="font-size: 1rem; padding: 8px 22px;">
                <i class="fas fa-check-circle"></i> <span id="summary-in-stock">{{ $summaryInitial['in'] }}</span> In Stock
            </span>
            <span class="summary-stat stat-low" style="font-size: 1rem; padding: 8px 22px;">
                <i class="fas fa-exclamation-triangle"></i> <span id="summary-low-stock">{{ $summaryInitial['low'] }}</span> Low Stock
            </span>
            <span class="summary-stat stat-out" style="font-size: 1rem; padding: 8px 22px;">
                <i class="fas fa-times-circle"></i> <span id="summary-out-stock">{{ $summaryInitial['out'] }}</span> Out of Stock
            </span>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
        <div class="last-updated">
            <i class="fas fa-sync-alt" id="sync-icon"></i>
            Last updated: <span id="last-updated-time">just now</span>
            <span style="margin: 0 8px;">|</span>
            Auto-refreshes every <strong>5s</strong>
        </div>
        <div class="card-actions">
            <a href="{{ route('transfers.index') }}" class="btn-cyber-neon" style="--neon-cyan: var(--neon-purple);">
                <i class="fas fa-exchange-alt"></i> Stock Transfers
            </a>
            <a href="{{ route('dashboard') }}" class="btn-cyber-neon" style="--neon-cyan: var(--neon-green);">
                <i class="fas fa-chart-bar"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const branchFilter = document.getElementById('branch-filter');
    const stockFilter = document.getElementById('stock-filter');
    const categoryFilter = document.getElementById('category-filter');
    const container = document.getElementById('branch-cards-container');
    const lastUpdated = document.getElementById('last-updated-time');
    const syncIcon = document.getElementById('sync-icon');

    let polling = true;

    function fetchStockData() {
        syncIcon.className = 'fas fa-sync-alt fa-spin';

        fetch('{{ route("monitor.stock-data") }}')
            .then(res => res.json())
            .then(data => {
                updateDashboard(data);
                syncCategories(data);
                lastUpdated.textContent = new Date().toLocaleTimeString();
                syncIcon.className = 'fas fa-sync-alt';
            })
            .catch(err => {
                console.error('Polling error:', err);
                syncIcon.className = 'fas fa-sync-alt';
            });
    }

    function updateDashboard(data) {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const branchVal = branchFilter.value;
        const stockVal = stockFilter.value;
        const categoryVal = categoryFilter.value;

        container.innerHTML = '';

        let summaryTotal = 0;
        let summaryIn = 0;
        let summaryLow = 0;
        let summaryOut = 0;

        data.branches.forEach(branch => {
            if (branchVal !== 'all' && String(branch.branch_id) !== branchVal) return;

            let filteredItems = branch.inventories;

            if (stockVal !== 'all') {
                filteredItems = filteredItems.filter(item => item.status === stockVal);
            }

            if (categoryVal !== 'all') {
                filteredItems = filteredItems.filter(item => item.category === categoryVal);
            }

            if (searchTerm) {
                filteredItems = filteredItems.filter(item =>
                    item.product_name.toLowerCase().includes(searchTerm) ||
                    item.brand.toLowerCase().includes(searchTerm) ||
                    item.serial_number.toLowerCase().includes(searchTerm)
                );
            }

            summaryTotal += filteredItems.length;
            summaryIn += filteredItems.filter(i => i.status === 'in_stock').length;
            summaryLow += filteredItems.filter(i => i.status === 'low_stock').length;
            summaryOut += filteredItems.filter(i => i.status === 'out_of_stock').length;

            const lowCount = filteredItems.filter(i => i.status === 'low_stock').length;
            const outCount = filteredItems.filter(i => i.status === 'out_of_stock').length;

            const card = document.createElement('div');
            card.className = 'branch-card';
            card.dataset.branchId = branch.branch_id;

            let rows = '';
            filteredItems.forEach(item => {
                const rowClass = item.status === 'out_of_stock' ? 'out-of-stock-row' : (item.status === 'low_stock' ? 'low-stock-row' : '');
                const qtyClass = item.status === 'out_of_stock' ? 'qty-out' : (item.status === 'low_stock' ? 'qty-low' : 'qty-ok');
                let statusHtml = '';
                if (item.status === 'out_of_stock') statusHtml = '<span style="color: var(--neon-red);"><i class="fas fa-times-circle"></i> Out</span>';
                else if (item.status === 'low_stock') statusHtml = '<span style="color: var(--neon-orange);"><i class="fas fa-exclamation-triangle"></i> Low</span>';
                else statusHtml = '<span style="color: var(--neon-green);"><i class="fas fa-check-circle"></i> In Stock</span>';

                rows += `<tr class="${rowClass}">
                    <td><strong>${escapeHtml(item.product_name)}</strong></td>
                    <td style="color: var(--text-muted);">${escapeHtml(item.category)}</td>
                    <td style="color: var(--text-muted);">${escapeHtml(item.brand)}</td>
                    <td style="color: var(--text-muted); font-size:0.8rem;">${escapeHtml(item.serial_number)}</td>
                    <td style="text-align:center;"><span class="qty-badge ${qtyClass}">${item.quantity}</span></td>
                    <td style="text-align:right; color: var(--neon-green);">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td style="text-align:center;">${statusHtml}</td>
                </tr>`;
            });

            if (!rows) {
                rows = `<tr><td colspan="7" style="text-align:center; color: var(--text-muted); padding: 30px;">
                    <i class="fas fa-box-open"></i> No items match your filters.
                </td></tr>`;
            }

            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <div class="branch-name"><i class="fas fa-store" style="color: var(--neon-cyan); margin-right: 8px;"></i> ${escapeHtml(branch.branch_name)}</div>
                        <div class="branch-address">${escapeHtml(branch.branch_address)}</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="summary-stat stat-ok"><i class="fas fa-box"></i> ${filteredItems.length} items</span>
                        <span class="summary-stat stat-low" style="${lowCount === 0 ? 'display:none' : ''}"><i class="fas fa-exclamation-triangle"></i> ${lowCount} low</span>
                        <span class="summary-stat stat-out" style="${outCount === 0 ? 'display:none' : ''}"><i class="fas fa-times-circle"></i> ${outCount} out</span>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="stock-table">
                        <thead><tr>
                            <th>Product</th><th>Category</th><th>Brand</th><th>Serial</th>
                            <th style="text-align:center;">Quantity</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:center;">Status</th>
                        </tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;

            container.appendChild(card);
        });

        document.getElementById('summary-total-items').textContent = summaryTotal;
        document.getElementById('summary-in-stock').textContent = summaryIn;
        document.getElementById('summary-low-stock').textContent = summaryLow;
        document.getElementById('summary-out-stock').textContent = summaryOut;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function syncCategories(data) {
        const existing = new Set(Array.from(categoryFilter.options).map(o => o.value));
        const fresh = new Set();
        data.branches.forEach(branch => {
            (branch.inventories || []).forEach(item => {
                if (item.category) fresh.add(item.category);
            });
        });
        fresh.forEach(cat => {
            if (!existing.has(cat)) {
                const opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                categoryFilter.appendChild(opt);
                existing.add(cat);
            }
        });
    }

    searchInput.addEventListener('input', fetchStockData);
    branchFilter.addEventListener('change', fetchStockData);
    stockFilter.addEventListener('change', fetchStockData);
    categoryFilter.addEventListener('change', fetchStockData);

    setInterval(fetchStockData, 5000);

    fetchStockData();
});
</script>
@endsection
