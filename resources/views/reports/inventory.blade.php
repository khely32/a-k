@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Inventory Report</h1>
        <button class="btn btn-outline-secondary" onclick="window.print()">Print Report</button>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Branch</th>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $inv)
                    <tr>
                        <td>{{ $inv->branch->branch_name }}</td>
                        <td><code>{{ $inv->product->serial_number }}</code></td>
                        <td>{{ $inv->product->brand }} {{ $inv->product->model }}</td>
                        <td class="text-center">{{ $inv->quantity }}</td>
                        <td class="text-center">
                            @if($inv->quantity <= 5)
                            <span class="badge bg-danger">Critical</span>
                            @elseif($inv->quantity <= 15)
                            <span class="badge bg-warning text-dark">Warning</span>
                            @else
                            <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar { display: none !important; }
    main { width: 100% !important; margin: 0 !important; padding: 0 !important; }
}
</style>
@endsection
