@extends('layouts.app')

@section('content')
<style>
    .tx-card {
        background: rgba(30, 41, 59, 0.88);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.08), 0 0 40px rgba(153, 27, 27, 0.08);
    }
    .tx-card thead th {
        color: var(--accent);
        font-size: 0.82rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(239, 68, 68, 0.2);
        background: linear-gradient(90deg, #111827, #1e293b);
    }
    .filter-input {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: white;
        padding: 8px 16px;
        border-radius: 10px;
    }
    .filter-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
    }
    .filter-input option { background: #0f172a; color: white; }
    .filter-input::-webkit-calendar-picker-indicator {
        filter: invert(1) sepia(1) saturate(3) hue-rotate(320deg);
        opacity: 1;
        cursor: pointer;
    }

    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.4);
        padding: 5px 16px;
        border-radius: 50px;
        color: var(--accent);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        animation: pulse-badge 2s infinite;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: var(--accent);
        border-radius: 50%;
        animation: blink 1s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.2; }
    }
    @keyframes pulse-badge {
        0%, 100% { box-shadow: 0 0 5px rgba(239, 68, 68, 0.3); }
        50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.6); }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h1 class="h3 mb-0" style="color: var(--accent); text-shadow: 0 0 8px var(--accent), 0 0 20px var(--accent);">
            Transaction History
        </h1>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="live-badge">
                <span class="live-dot"></span>
                <span id="last-updated">LIVE</span>
            </span>
            <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Filters (branch selector is owner-only; staff see only their branch's data) -->
    <div class="tx-card mb-4 p-3">
        <div class="row g-3 align-items-end">
            @if(Auth::user()->role === 'owner')
            <div class="col-md-3">
                <label class="form-label text-muted small">Branch</label>
                <select id="branch-filter" name="branch_id" class="form-select filter-input">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <label class="form-label text-muted small">From Date</label>
                <input type="date" id="start-date" name="start_date" class="form-control filter-input" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">To Date</label>
                <input type="date" id="end-date" name="end_date" class="form-control filter-input" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="button" id="reset-filters" class="btn btn-outline-secondary">Reset</button>
            </div>
        </div>
    </div>

    <div class="tx-card table-responsive">
        <table class="table table-dark table-borderless align-middle mb-0">
            <thead>
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Date & Time</th>
                    <th class="px-4 py-3">Branch</th>
                    <th class="px-4 py-3">Cashier</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Payment</th>
                    <th class="px-4 py-3 text-end">Total</th>
                </tr>
            </thead>
            <tbody id="tx-tbody">
                @php $grandTotal = 0; @endphp
                @forelse($sales as $sale)
                    @php $grandTotal += $sale->total_amount; @endphp
                    <tr>
                        <td class="px-4 py-3 text-cyan-300 fw-bold">#{{ $sale->id }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $sale->created_at->format('M d, Y') }}</div>
                            <small class="text-secondary">{{ $sale->created_at->format('h:i A') }}</small>
                        </td>
                        <td class="px-4 py-3">{{ $sale->branch->branch_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $sale->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <ul class="list-unstyled mb-0 small">
                                @foreach($sale->items as $item)
                                    <li>{{ $item->quantity }}x {{ $item->product->name ?? 'Unknown' }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-uppercase small">{{ $sale->payment_method ?? 'cash' }}</span>
                        </td>
                        <td class="px-4 py-3 text-end text-success fw-semibold">
                            ₱{{ number_format($sale->total_amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-5">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot id="tx-tfoot">
                <tr>
                    <th colspan="6" class="px-4 py-3 text-end text-white">Grand Total:</th>
                    <th class="px-4 py-3 text-end text-success fw-bold">₱{{ number_format($grandTotal, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchFilter = document.getElementById('branch-filter');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const resetBtn = document.getElementById('reset-filters');
    const tbody = document.getElementById('tx-tbody');
    const tfoot = document.getElementById('tx-tfoot');
    const lastUpdated = document.getElementById('last-updated');

    function currentQuery() {
        const params = new URLSearchParams();
        if (branchFilter && branchFilter.value) params.set('branch_id', branchFilter.value);
        if (startDate && startDate.value) params.set('start_date', startDate.value);
        if (endDate && endDate.value) params.set('end_date', endDate.value);
        return params.toString();
    }

    function fetchTransactions() {
        const base = '{{ route("reports.transaction-data") }}';
        const url = base + (currentQuery() ? '?' + currentQuery() : '');

        fetch(url)
            .then(res => res.json())
            .then(data => {
                renderRows(data.sales, data.grand_total);
                lastUpdated.textContent = 'Updated ' + new Date().toLocaleTimeString();
            })
            .catch(err => console.error('Polling error:', err));
    }

    function renderRows(sales, grandTotal) {
        if (!sales.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-5">No transactions found.</td></tr>';
            tfoot.innerHTML = '<tr><th colspan="6" class="px-4 py-3 text-end text-white">Grand Total:</th>' +
                '<th class="px-4 py-3 text-end text-success fw-bold">\u20B10.00</th></tr>';
            return;
        }

        let rows = '';
        sales.forEach(function (sale) {
            let items = '';
            sale.items.forEach(function (item) {
                items += '<li>' + item.quantity + 'x ' + escapeHtml(item.name) + '</li>';
            });
            if (!items) items = '<li class="text-secondary">\u2014</li>';

            rows += '<tr>' +
                '<td class="px-4 py-3 text-cyan-300 fw-bold">#' + sale.id + '</td>' +
                '<td class="px-4 py-3"><div>' + escapeHtml(sale.created_at) + '</div>' +
                    '<small class="text-secondary">' + escapeHtml(sale.created_time) + '</small></td>' +
                '<td class="px-4 py-3">' + escapeHtml(sale.branch) + '</td>' +
                '<td class="px-4 py-3">' + escapeHtml(sale.cashier) + '</td>' +
                '<td class="px-4 py-3"><ul class="list-unstyled mb-0 small">' + items + '</ul></td>' +
                '<td class="px-4 py-3"><span class="text-uppercase small">' + escapeHtml(sale.payment_method) + '</span></td>' +
                '<td class="px-4 py-3 text-end text-success fw-semibold">\u20B1' + parseFloat(sale.total).toFixed(2) + '</td>' +
            '</tr>';
        });

        tbody.innerHTML = rows;
        tfoot.innerHTML = '<tr>' +
            '<th colspan="6" class="px-4 py-3 text-end text-white">Grand Total:</th>' +
            '<th class="px-4 py-3 text-end text-success fw-bold">\u20B1' + parseFloat(grandTotal).toFixed(2) + '</th>' +
        '</tr>';
    }

    function escapeHtml(str) {
        if (str == null) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function resetFilters() {
        if (branchFilter) branchFilter.value = '';
        if (startDate) startDate.value = '';
        if (endDate) endDate.value = '';
        fetchTransactions();
    }

    if (branchFilter) branchFilter.addEventListener('change', fetchTransactions);
    if (startDate) startDate.addEventListener('change', fetchTransactions);
    if (endDate) endDate.addEventListener('change', fetchTransactions);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);

    setInterval(fetchTransactions, 5000);
    fetchTransactions();
});
</script>
@endsection
