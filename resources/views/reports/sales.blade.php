@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Sales Report</h1>
        <button class="btn btn-outline-secondary" onclick="window.print()">Print Report</button>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('reports.sales') }}" method="GET" class="row g-3">
                @if(in_array(Auth::user()->role, ['owner', 'admin']))
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse($sales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $sale->branch->branch_name }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($sale->items as $item)
                                <li>{{ $item->quantity }}x {{ $item->product->brand }} {{ $item->product->model }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-end">₱{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                    @php $grandTotal += $sale->total_amount; @endphp
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No sales found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Grand Total:</th>
                        <th class="text-end">₱{{ number_format($grandTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .sidebar { display: none !important; }
    main { width: 100% !important; margin: 0 !important; padding: 0 !important; }
}
</style>
@endsection
