@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
/* ════ REPORTS PAGE — NEON DARK AESTHETIC ════ */
:root {
    --r-bg: #0D1117;
    --r-card: #161B22;
    --r-card2: #1C2128;
    --r-border: rgba(255,46,85,0.12);
    --r-accent: #FF2E55;
    --r-green: #00E676;
    --r-yellow: #FFC107;
    --r-cyan: #00E5FF;
    --r-purple: #D500F9;
    --r-text: #FFFFFF;
    --r-muted: #6B7280;
}
.rpt-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0 14px 0;
    margin-bottom: 14px;
}
.rpt-header h1 {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--r-text);
    margin: 0;
    text-shadow: 0 0 8px rgba(255, 46, 85, 0.25);
}
.rpt-header .sub {
    font-size: 0.72rem;
    color: var(--r-muted);
    margin-top: 2px;
}
.rpt-header-actions {
    display: flex;
    gap: 8px;
}
.rpt-header-actions .neon-btn {
    background: transparent;
    color: var(--r-accent);
    border: 1px solid var(--r-accent);
    border-radius: 8px;
    padding: 6px 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
}
.rpt-header-actions .neon-btn:hover {
    background: var(--r-accent);
    color: #fff;
    box-shadow: 0 0 14px rgba(255, 46, 85, 0.35);
}

/* Filter Bar */
.rpt-filter {
    background: var(--r-card);
    border: 1px solid var(--r-border);
    border-radius: 14px;
    padding: 14px 20px;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-end;
    gap: 14px;
    flex-wrap: wrap;
    backdrop-filter: blur(8px);
}
.rpt-filter label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--r-muted);
    font-weight: 700;
    margin-bottom: 4px;
    display: block;
}
.rpt-filter select,
.rpt-filter input[type="date"] {
    background: #111420;
    border: 1px solid var(--r-border);
    border-radius: 8px;
    color: var(--r-text);
    font-size: 0.78rem;
    padding: 6px 12px;
    outline: none;
    height: 34px;
    font-family: 'Inter', sans-serif;
}
.rpt-filter select:focus,
.rpt-filter input[type="date"]:focus {
    border-color: var(--r-accent);
    box-shadow: 0 0 8px rgba(255, 46, 85, 0.2);
}
.rpt-filter select option {
    background: #111420;
    color: #fff;
}
.rpt-filter input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
.rpt-auto {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.72rem;
    color: var(--r-muted);
    background: rgba(0, 230, 118, 0.06);
    border: 1px solid rgba(0, 230, 118, 0.15);
    border-radius: 8px;
    padding: 6px 12px;
}
.rpt-auto .dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--r-green);
    box-shadow: 0 0 8px rgba(0, 230, 118, 0.5);
    animation: rpt-pulse 2s infinite;
}
@keyframes rpt-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* KPI Cards */
.rpt-kpi {
    background: var(--r-card);
    border: 1px solid var(--r-border);
    border-radius: 14px;
    padding: 16px 18px;
    position: relative;
    overflow: hidden;
    min-height: 100px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rpt-kpi:hover {
    border-color: rgba(255, 255, 255, 0.1);
}
.rpt-kpi::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
}
.rpt-kpi::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    pointer-events: none;
    opacity: 0.05;
}
.rpt-kpi.k-r::before { background: linear-gradient(90deg, var(--r-accent), transparent); }
.rpt-kpi.k-g::before { background: linear-gradient(90deg, var(--r-green), transparent); }
.rpt-kpi.k-y::before { background: linear-gradient(90deg, var(--r-yellow), transparent); }
.rpt-kpi.k-b::before { background: linear-gradient(90deg, var(--r-blue), transparent); }
.rpt-kpi.k-r::after { background: var(--r-accent); }
.rpt-kpi.k-g::after { background: var(--r-green); }
.rpt-kpi.k-y::after { background: var(--r-yellow); }
.rpt-kpi.k-b::after { background: var(--r-blue); }
.rpt-kpi.k-r:hover { box-shadow: 0 0 16px rgba(255, 46, 85, 0.15); }
.rpt-kpi.k-g:hover { box-shadow: 0 0 16px rgba(0, 230, 118, 0.15); }
.rpt-kpi.k-y:hover { box-shadow: 0 0 16px rgba(255, 193, 7, 0.15); }
.rpt-kpi.k-b:hover { box-shadow: 0 0 16px rgba(0, 176, 255, 0.15); }
.rpt-kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.rpt-kpi-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--r-muted);
    font-weight: 600;
}
.rpt-kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    position: relative;
}
.rpt-kpi-icon::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 14px;
    opacity: 0;
    animation: rptIconGlow 2.5s ease-in-out infinite;
    pointer-events: none;
}
@keyframes rptIconGlow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.08); }
}
.rpt-kpi.k-r .rpt-kpi-icon { background: rgba(255, 46, 85, 0.12); color: var(--r-accent); }
.rpt-kpi.k-g .rpt-kpi-icon { background: rgba(0, 230, 118, 0.12); color: var(--r-green); }
.rpt-kpi.k-y .rpt-kpi-icon { background: rgba(255, 193, 7, 0.12); color: var(--r-yellow); }
.rpt-kpi.k-b .rpt-kpi-icon { background: rgba(0, 176, 255, 0.12); color: var(--r-cyan); }
.rpt-kpi.k-r .rpt-kpi-icon::before { box-shadow: 0 0 12px rgba(255, 46, 85, 0.4), 0 0 24px rgba(255, 46, 85, 0.15); background: rgba(255, 46, 85, 0.08); }
.rpt-kpi.k-g .rpt-kpi-icon::before { box-shadow: 0 0 12px rgba(0, 230, 118, 0.4), 0 0 24px rgba(0, 230, 118, 0.15); background: rgba(0, 230, 118, 0.08); }
.rpt-kpi.k-y .rpt-kpi-icon::before { box-shadow: 0 0 12px rgba(255, 193, 7, 0.4), 0 0 24px rgba(255, 193, 7, 0.15); background: rgba(255, 193, 7, 0.08); }
.rpt-kpi.k-b .rpt-kpi-icon::before { box-shadow: 0 0 12px rgba(0, 229, 255, 0.4), 0 0 24px rgba(0, 229, 255, 0.15); background: rgba(0, 229, 255, 0.08); }
.rpt-kpi-val {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--r-text);
    line-height: 1;
}
.rpt-kpi-sub {
    font-size: 0.68rem;
    color: var(--r-muted);
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Big Value Cards */
.rpt-val-card {
    background: var(--r-card);
    border: 1px solid var(--r-border);
    border-radius: 14px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    backdrop-filter: blur(8px);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rpt-val-card:hover {
    border-color: rgba(255, 255, 255, 0.1);
    box-shadow: 0 0 20px rgba(0, 230, 118, 0.08);
}
.rpt-val-left { flex: 1; }
.rpt-val-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--r-muted);
    font-weight: 600;
    margin-bottom: 4px;
}
.rpt-val-num {
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1;
}
.rpt-val-num.green { color: var(--r-green); }
.rpt-val-sub {
    font-size: 0.68rem;
    color: var(--r-muted);
    margin-top: 6px;
}
.rpt-val-chart { width: 130px; height: 45px; flex-shrink: 0; }

/* Report Cards */
.rpt-card {
    background: var(--r-card);
    border: 1px solid var(--r-border);
    border-radius: 14px;
    overflow: hidden;
    backdrop-filter: blur(8px);
}
.rpt-card-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--r-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.rpt-card-head h5 {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--r-text);
}
.rpt-card-body { padding: 18px; }
.rpt-chart { position: relative; height: 260px; width: 100%; }

/* Top Products (horizontal bars) */
.tp-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.tp-row:last-child { border-bottom: none; }
.tp-rank {
    width: 24px;
    height: 24px;
    border-radius: 7px;
    background: rgba(255, 255, 255, 0.04);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--r-muted);
    flex-shrink: 0;
}
.tp-rank.top3 {
    background: rgba(255, 46, 85, 0.12);
    color: var(--r-accent);
}
.tp-info { flex: 1; min-width: 0; }
.tp-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--r-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tp-brand {
    font-size: 0.65rem;
    color: var(--r-muted);
    margin-top: 1px;
}
.tp-bar-track {
    height: 4px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 2px;
    margin-top: 5px;
}
.tp-bar {
    height: 100%;
    border-radius: 2px;
    transition: width 0.8s ease;
}
.tp-qty {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--r-text);
    min-width: 32px;
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.tp-val {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--r-green);
    min-width: 72px;
    text-align: right;
    font-variant-numeric: tabular-nums;
}

/* Table */
.rpt-card .table { margin: 0; }
.rpt-card .table thead th {
    background: linear-gradient(90deg, var(--r-card), var(--r-card2));
    color: var(--r-muted);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: 1px solid var(--r-border);
    font-weight: 600;
}
.rpt-card .table td {
    border-color: rgba(255, 255, 255, 0.04);
    vertical-align: middle;
    font-size: 0.82rem;
    color: var(--r-text);
}
.rpt-card .table tbody tr { transition: background 0.15s; }
.rpt-card .table tbody tr:hover { background: rgba(255, 255, 255, 0.03); }

/* ════ BRANCH ANALYTICS ════ */
.ba-section{margin-bottom:24px}
.ba-restricted{
    background:rgba(100,116,139,0.08);
    border:1px solid rgba(100,116,139,0.2);
    border-radius:14px;
    padding:28px 32px;
    display:flex;
    align-items:center;
    gap:18px;
}
.ba-restricted-icon{
    width:52px;height:52px;border-radius:12px;
    background:rgba(100,116,139,0.12);
    display:flex;align-items:center;justify-content:center;
    font-size:1.5rem;color:#94A3B8;flex-shrink:0;
}
.ba-restricted h6{margin:0;color:#94A3B8;font-size:0.85rem;font-weight:700}
.ba-restricted p{margin:4px 0 0;color:#64748B;font-size:0.75rem}
.ba-header{
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:16px;
}
.ba-header h4{
    margin:0;font-size:0.95rem;font-weight:800;color:var(--r-text);
    text-shadow:0 0 10px rgba(0,230,118,0.2);
    display:flex;align-items:center;gap:8px;
}
.ba-header h4 i{color:var(--r-green);font-size:1.1rem}
.ba-header .ba-badge{
    font-size:0.6rem;text-transform:uppercase;letter-spacing:0.08em;
    font-weight:700;padding:4px 12px;border-radius:999px;
    background:rgba(0,230,118,0.1);color:var(--r-green);
    border:1px solid rgba(0,230,118,0.2);
}

/* Summary Table */
.ba-table-wrap{
    background:var(--r-card);border:1px solid var(--r-border);
    border-radius:14px;overflow:hidden;margin-bottom:16px;
}
.ba-table{width:100%;border-collapse:collapse}
.ba-table thead th{
    background:linear-gradient(90deg,var(--r-card),var(--r-card2));
    color:var(--r-muted);font-size:0.62rem;text-transform:uppercase;
    letter-spacing:0.06em;padding:12px 16px;border-bottom:1px solid var(--r-border);
    font-weight:600;text-align:left;
}
.ba-table thead th:first-child{padding-left:20px}
.ba-table thead th:last-child{padding-right:20px}
.ba-table tbody td{
    padding:12px 16px;font-size:0.78rem;color:var(--r-text);
    border-bottom:1px solid rgba(255,255,255,0.03);vertical-align:middle;
}
.ba-table tbody td:first-child{padding-left:20px}
.ba-table tbody td:last-child{padding-right:20px}
.ba-table tbody tr:hover{background:rgba(255,255,255,0.02)}
.ba-table tbody tr:last-child td{border-bottom:none}
.ba-branch-dot{
    width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:8px;
}
.ba-rate{font-weight:700}
.ba-rate.high{color:#10B981}
.ba-rate.mid{color:#F59E0B}
.ba-rate.low{color:#EF4444}
.ba-growth{font-weight:600;font-size:0.72rem}
.ba-growth.pos{color:#10B981}
.ba-growth.neg{color:#EF4444}
.ba-delta{font-weight:600;font-size:0.72rem;color:#94A3B8}

/* Chart Grid */
.ba-charts{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.ba-chart-card{
    background:var(--r-card);border:1px solid var(--r-border);
    border-radius:14px;overflow:hidden;
}
.ba-chart-head{
    padding:12px 18px;border-bottom:1px solid var(--r-border);
    display:flex;justify-content:space-between;align-items:center;
}
.ba-chart-head h5{
    margin:0;font-size:0.72rem;font-weight:700;text-transform:uppercase;
    letter-spacing:0.05em;color:var(--r-text);
    display:flex;align-items:center;gap:6px;
}
.ba-chart-head h5 i{font-size:0.85rem}
.ba-chart-body{padding:18px;position:relative;height:300px}

@media(max-width:991px){
    .ba-charts{grid-template-columns:1fr}
    .ba-table-wrap{overflow-x:auto}
}
@media print{
    .rpt-header-actions,.rpt-filter,.rpt-auto,.no-print{display:none!important}
    .content-wrapper{margin-left:0!important;width:100%!important;padding:0!important}
    .rpt-card,.rpt-kpi,.rpt-val-card{
        background:#fff!important;border:1px solid #ddd!important;
        color:#000!important;box-shadow:none!important;
    }
    .rpt-card .table{color:#000!important}
    .rpt-card .table thead th{background:#f3f4f6!important;color:#000!important;border-bottom:2px solid #000!important}
    .rpt-card .table td{color:#000!important;border-color:#ccc!important}
    .rpt-kpi-val,.rpt-val-num{color:#000!important}
    .rpt-kpi-label,.rpt-val-label{color:#555!important}
    .ba-section{display:none!important}
}
</style>

<div class="rpt-header">
    <div>
        <h1>Reports Dashboard</h1>
        <div class="sub">Analyze sales, inventory, and branch performance</div>
    </div>
    <div class="rpt-header-actions no-print">
        <button type="button" class="neon-btn" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <a href="{{ route('dashboard') }}" class="neon-btn"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>
</div>

<!-- Filter Bar -->
<div class="rpt-filter no-print">
    <div style="flex:1;max-width:220px">
        <label>Branch</label>
        <select id="filter-branch" class="form-select">
            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }} style="font-weight:700;color:var(--r-cyan)">Overall / All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->id == 8 ? 'Main Branch' : $branch->branch_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;max-width:180px">
        <label>Start Date</label>
        <input type="date" id="filter-start" value="{{ $startDate ?? '' }}" class="form-control">
    </div>
    <div style="flex:1;max-width:180px">
        <label>End Date</label>
        <input type="date" id="filter-end" value="{{ $endDate ?? '' }}" class="form-control">
    </div>
    <div class="rpt-auto"><span class="dot"></span> Auto-refreshing</div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="rpt-kpi k-r">
            <div class="rpt-kpi-top">
                <span class="rpt-kpi-label">Total Products</span>
                <div class="rpt-kpi-icon"><i class="bi bi-box-seam"></i></div>
            </div>
            <div class="rpt-kpi-val" id="kpi-products">{{ $totalProducts }}</div>
            <div class="rpt-kpi-sub">Active items in inventory</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="rpt-kpi k-g">
            <div class="rpt-kpi-top">
                <span class="rpt-kpi-label">Total Inventory</span>
                <div class="rpt-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
            <div class="rpt-kpi-val" id="kpi-inventory">{{ $totalInventory }}</div>
            <div class="rpt-kpi-sub">Units across all categories</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="rpt-kpi k-y">
            <div class="rpt-kpi-top">
                <span class="rpt-kpi-label">Low Stock Items</span>
                <div class="rpt-kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
            <div class="rpt-kpi-val" id="kpi-lowstock">{{ $lowStockProducts }}</div>
            <div class="rpt-kpi-sub">Requires attention</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="rpt-kpi k-b">
            <div class="rpt-kpi-top">
                <span class="rpt-kpi-label">Pending Transfers</span>
                <div class="rpt-kpi-icon"><i class="bi bi-arrow-left-right"></i></div>
            </div>
            <div class="rpt-kpi-val" id="kpi-pending">{{ $pendingTransfers }}</div>
            <div class="rpt-kpi-sub">Awaiting approval</div>
        </div>
    </div>
</div>

<!-- Revenue + Inventory Value with sparklines -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="rpt-val-card">
            <div class="rpt-val-left">
                <div class="rpt-val-label"><i class="bi bi-currency-dollar" style="margin-right:3px"></i> Total Revenue</div>
                <div class="rpt-val-num green" id="kpi-revenue">₱{{ number_format($totalRevenue, 2) }}</div>
                <div class="rpt-val-sub">Live transaction summary</div>
            </div>
            <div class="rpt-val-chart"><canvas id="revSparkline"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="rpt-val-card">
            <div class="rpt-val-left">
                <div class="rpt-val-label"><i class="bi bi-database" style="margin-right:3px"></i> Total Inventory Value</div>
                <div class="rpt-val-num green" id="kpi-invvalue">₱{{ number_format($inventoryValue, 2) }}</div>
                <div class="rpt-val-sub">Quantity &times; Unit Price</div>
            </div>
            <div class="rpt-val-chart"><canvas id="invSparkline"></canvas></div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="rpt-card">
            <div class="rpt-card-head"><h5><i class="bi bi-bar-chart" style="margin-right:5px;color:var(--r-accent)"></i> Top Products</h5></div>
            <div class="rpt-card-body"><div class="rpt-chart"><canvas id="lineChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="rpt-card">
            <div class="rpt-card-head"><h5><i class="bi bi-pie-chart" style="margin-right:5px;color:var(--r-green)"></i> Stock Status</h5></div>
            <div class="rpt-card-body"><div class="rpt-chart"><canvas id="pieChart"></canvas></div></div>
        </div>
    </div>
</div>

<!-- ═══ Branch Analytics Section ═══ -->
<div class="ba-section" id="ba-section">
    @if($isMainBranch)
        <div class="ba-header no-print">
            <h4><i class="bi bi-bar-chart-line"></i> Multi-Branch Sales Analytics</h4>
            <span class="ba-badge">Main Branch Exclusive</span>
        </div>

        <!-- Summary Table -->
        <div class="ba-table-wrap" id="ba-table-wrap">
            <table class="ba-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th class="text-end">Target</th>
                        <th class="text-end">Avg Revenue</th>
                        <th class="text-end">Completion %</th>
                        <th class="text-end">Delta</th>
                        <th class="text-end">Growth</th>
                    </tr>
                </thead>
                <tbody id="ba-table-body">
                    <tr><td colspan="6" class="text-center py-4" style="color:var(--r-muted)">Loading analytics...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Dual Chart Grid -->
        <div class="ba-charts">
            <div class="ba-chart-card">
                <div class="ba-chart-head">
                    <h5><i class="bi bi-graph-up" style="color:#10B981"></i> Sales Performance</h5>
                </div>
                <div class="ba-chart-body"><canvas id="baPerformanceChart"></canvas></div>
            </div>
            <div class="ba-chart-card">
                <div class="ba-chart-head">
                    <h5><i class="bi bi-bar-chart-alt" style="color:#06B6D4"></i> Branch Comparison</h5>
                </div>
                <div class="ba-chart-body"><canvas id="baComparisonChart"></canvas></div>
            </div>
        </div>
    @else
        <div class="ba-restricted">
            <div class="ba-restricted-icon"><i class="bi bi-shield-lock"></i></div>
            <div>
                <h6>Cross-branch analytics are restricted to Main Branch management.</h6>
                <p>Contact the Main Branch administrator for cross-branch performance reports.</p>
            </div>
        </div>
    @endif
</div>

<!-- Top Inventory Products Table -->
<div class="rpt-card">
    <div class="rpt-card-head">
        <h5><i class="bi bi-trophy" style="margin-right:5px;color:var(--r-accent)"></i> Top Inventory Products</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-borderless align-middle">
            <thead>
                <tr>
                    <th class="px-3 py-3">Product</th>
                    <th class="px-3 py-3">Brand</th>
                    <th class="px-3 py-3 text-end">Qty</th>
                    <th class="px-3 py-3 text-end">Value</th>
                </tr>
            </thead>
            <tbody id="top-products-body">
                @forelse($topProducts as $item)
                    @php
                        $product = $item->product ?? null;
                        if (!$product && isset($item->product_id)) {
                            $product = \App\Models\Product::find($item->product_id);
                        }
                        $qty = (int) ($item->quantity ?? $item->total_qty ?? 0);
                    @endphp
                    <tr>
                        <td class="px-3 py-2 fw-semibold">{{ $product->name ?? 'N/A' }}</td>
                        <td class="px-3 py-2" style="color:var(--r-muted)">{{ $product->brand ?? '-' }}</td>
                        <td class="px-3 py-2 text-end">{{ number_format($qty) }}</td>
                        <td class="px-3 py-2 text-end" style="color:var(--r-green)">₱{{ number_format(($product->price ?? 0) * $qty, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4" style="color:var(--r-muted)">No inventory data found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var R = '#FF2E55', G = '#00E676', Y = '#FFC107', B = '#00E5FF', P = '#D500F9';
    var fontColor = '#8A8F9E';
    var lineChart, pieChart, revSpark, invSpark;

    function makeSparkline(canvasId, color) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        return new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
                datasets: [{ data: [0, 0, 0, 0, 0, 0, 0], borderColor: color, backgroundColor: color.replace(')', ',0.08)').replace('rgb', 'rgba'), fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
        });
    }

    function initCharts(lineLabels, lineData, pieData) {
        lineChart = new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [{
                    label: 'Quantity',
                    data: lineData,
                    borderColor: R,
                    backgroundColor: 'rgba(255, 46, 85, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: R,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: fontColor } },
                    x: { grid: { display: false }, ticks: { color: fontColor, maxRotation: 45 } }
                }
            }
        });

        pieChart = new Chart(document.getElementById('pieChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: pieData,
                    backgroundColor: [G, Y, R],
                    borderWidth: 0,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '72%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: fontColor, padding: 12, usePointStyle: true } }
                }
            }
        });

        revSpark = makeSparkline('revSparkline', 'rgb(0, 230, 118)');
        invSpark = makeSparkline('invSparkline', 'rgb(0, 230, 118)');
    }

    function updateCharts(lineLabels, lineData, pieData) {
        if (lineChart) {
            lineChart.data.labels = lineLabels;
            lineChart.data.datasets[0].data = lineData;
            lineChart.update();
        }
        if (pieChart) {
            pieChart.data.datasets[0].data = pieData;
            pieChart.update();
        }
    }

    function buildProductsTable(products) {
        var tbody = document.getElementById('top-products-body');
        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4" style="color:var(--r-muted)">No inventory data found</td></tr>';
            return;
        }
        tbody.innerHTML = products.map(function (p) {
            var val = (parseFloat(p.price) * parseInt(p.quantity)).toFixed(2);
            return '<tr>' +
                '<td class="px-3 py-2 fw-semibold">' + escapeHtml(p.product_name) + '</td>' +
                '<td class="px-3 py-2" style="color:var(--r-muted)">' + escapeHtml(p.brand || '-') + '</td>' +
                '<td class="px-3 py-2 text-end">' + p.quantity + '</td>' +
                '<td class="px-3 py-2 text-end" style="color:var(--r-green)">₱' + Number(val).toLocaleString('en', {minimumFractionDigits:2}) + '</td>' +
            '</tr>';
        }).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function applyData(d) {
        document.getElementById('kpi-products').textContent = d.totalProducts;
        document.getElementById('kpi-inventory').textContent = d.totalInventory;
        document.getElementById('kpi-lowstock').textContent = d.lowStockProducts;
        document.getElementById('kpi-pending').textContent = d.pendingTransfers;
        document.getElementById('kpi-revenue').textContent = '₱' + Number(d.totalRevenue).toLocaleString('en', {minimumFractionDigits:2});
        document.getElementById('kpi-invvalue').textContent = '₱' + Number(d.inventoryValue).toLocaleString('en', {minimumFractionDigits:2});

        if (revSpark) {
            var arr = [0,0,0,0,0,0,d.totalRevenue];
            revSpark.data.datasets[0].data = arr;
            revSpark.update();
        }
        if (invSpark) {
            var arr2 = [0,0,0,0,0,0,d.inventoryValue];
            invSpark.data.datasets[0].data = arr2;
            invSpark.update();
        }

        var lineLabels = (d.topProducts || []).map(function (p) { return (p.product_name || 'N/A').substring(0, 14); });
        var lineData = (d.topProducts || []).map(function (p) { return p.quantity; });
        var pieData = [d.inStock || 0, d.lowStock || 0, d.outOfStock || 0];

        if (lineChart) {
            updateCharts(lineLabels, lineData, pieData);
        } else {
            initCharts(lineLabels, lineData, pieData);
        }
        buildProductsTable(d.topProducts);
    }

    function fetchData() {
        var branchId = document.getElementById('filter-branch').value;
        var startDate = document.getElementById('filter-start').value;
        var endDate = document.getElementById('filter-end').value;

        var params = new URLSearchParams();
        params.set('branch_id', branchId);
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);

        fetch('{{ route("reports.data") }}?' + params.toString())
            .then(function (res) { return res.json(); })
            .then(function (d) { applyData(d); })
            .catch(function (err) { console.error('Report poll error:', err); });
    }

    document.getElementById('filter-branch').addEventListener('change', fetchData);
    document.getElementById('filter-start').addEventListener('change', fetchData);
    document.getElementById('filter-end').addEventListener('change', fetchData);

    setInterval(fetchData, 5000);

    /* ═══ Branch Analytics ═══ */
    @if($isMainBranch)
    var baPerformanceChart = null, baComparisonChart = null;
    var baFont = '#8A8F9E';

    function fetchBranchAnalytics() {
        fetch('{{ route("reports.branch-analytics") }}?branch_id=all')
            .then(function(r){ return r.json(); })
            .then(function(d){ renderBranchAnalytics(d); })
            .catch(function(e){ console.error('Branch analytics error:', e); });
    }

    function renderBranchAnalytics(d) {
        var months = d.months || [];
        var branches = d.branches || {};
        var comparison = d.comparison || [];

        renderBaTable(comparison);
        renderBaPerformance(months, branches);
        renderBaComparison(comparison);
    }

    function renderBaTable(rows) {
        var tbody = document.getElementById('ba-table-body');
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--r-muted)">No branch data</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function(r) {
            var rateClass = r.rate >= 80 ? 'high' : r.rate >= 50 ? 'mid' : 'low';
            var growthClass = r.growth >= 0 ? 'pos' : 'neg';
            var growthIcon = r.growth >= 0 ? '&#9650;' : '&#9660;';
            return '<tr>' +
                '<td><span class="ba-branch-dot" style="background:' + r.color + '"></span>' + escapeHtml(r.branch) + '</td>' +
                '<td class="text-end" style="color:var(--r-muted)">&#8369;' + formatNum(r.target) + '</td>' +
                '<td class="text-end fw-semibold">&#8369;' + formatNum(r.revenue) + '</td>' +
                '<td class="text-end"><span class="ba-rate ' + rateClass + '">' + r.rate.toFixed(1) + '%</span></td>' +
                '<td class="text-end"><span class="ba-delta">' + (r.target > r.revenue ? '&#8369;' + formatNum(r.target - r.revenue) : '&#8212;') + '</span></td>' +
                '<td class="text-end"><span class="ba-growth ' + growthClass + '">' + growthIcon + ' ' + Math.abs(r.growth).toFixed(1) + '%</span></td>' +
            '</tr>';
        }).join('');
    }

    function renderBaPerformance(months, branches) {
        var ctx = document.getElementById('baPerformanceChart');
        if (!ctx) return;

        var branchIds = Object.keys(branches);
        var barDatasets = [];
        var lineDatasets = [];

        branchIds.forEach(function(bid) {
            var b = branches[bid];
            barDatasets.push({
                label: b.branch_name + ' (Revenue)',
                data: b.monthly,
                backgroundColor: hexToRgba(b.color, 0.7),
                borderColor: b.color,
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.7,
                categoryPercentage: 0.8,
                yAxisID: 'y',
            });
            lineDatasets.push({
                label: b.branch_name + ' (Rate %)',
                data: b.monthly.map(function() { return b.completion_rate; }),
                borderColor: b.color,
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 3],
                pointRadius: 0,
                tension: 0.3,
                yAxisID: 'y1',
            });
        });

        if (baPerformanceChart) baPerformanceChart.destroy();
        baPerformanceChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: barDatasets.concat(lineDatasets),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: baFont, padding: 10, usePointStyle: true, font: { size: 10 } }
                    }
                },
                scales: {
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: baFont, callback: function(v) { return '&#8369;' + formatNumShort(v); } },
                        title: { display: true, text: 'Revenue (₱)', color: baFont, font: { size: 10 } }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        max: 120,
                        grid: { drawOnChartArea: false },
                        ticks: { color: baFont, callback: function(v) { return v + '%'; } },
                        title: { display: true, text: 'Completion %', color: baFont, font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: baFont, font: { size: 10 } }
                    }
                }
            }
        });
    }

    function renderBaComparison(comparison) {
        var ctx = document.getElementById('baComparisonChart');
        if (!ctx) return;

        var labels = comparison.map(function(c){ return c.branch; });
        var revenues = comparison.map(function(c){ return c.revenue; });
        var colors = comparison.map(function(c){ return c.color; });

        if (baComparisonChart) baComparisonChart.destroy();
        baComparisonChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Revenue',
                    data: revenues,
                    backgroundColor: colors.map(function(c){ return hexToRgba(c, 0.7); }),
                    borderColor: colors,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return '&#8369;' + formatNum(ctx.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: baFont, callback: function(v) { return '&#8369;' + formatNumShort(v); } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#FFFFFF', font: { weight: '600', size: 11 } }
                    }
                }
            }
        });
    }

    function hexToRgba(hex, alpha) {
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function formatNum(n) {
        return Number(n).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatNumShort(n) {
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
        return n.toFixed(0);
    }

    fetchBranchAnalytics();
    setInterval(fetchBranchAnalytics, 15000);
    @endif
});
</script>
@endsection
