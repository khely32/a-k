@extends('layouts.app')

@section('content')
<style>
    .hist-staff-notice{
        background:rgba(0,229,255,.06);
        border:1px solid rgba(0,229,255,.28);
        color:#a5f3fc;
        border-radius:14px;
        font-size:.9rem;
    }
    .hist-owner-notice{
        background:rgba(0,255,136,.06);
        border:1px solid rgba(0,255,136,.28);
        color:#b7ffd8;
        border-radius:14px;
        font-size:.9rem;
    }
</style>
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(strtolower(Auth::user()->role) !== 'owner')
        <div class="hist-staff-notice mb-4 p-3">
            <i class="bi bi-shield-lock me-2"></i>
            Showing only stock transfer requests for your branch
            (<strong>{{ auth()->user()->branchLabel() }}</strong>).
        </div>
    @else
        <div class="hist-owner-notice mb-4 p-3">
            <i class="bi bi-eye me-2"></i>
            Showing all stock transfers across all branches.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white">
            📜 Stock Transfer History
        </h2>
        <a href="{{ route('transfers.index') }}" class="neon-btn">
            ← Back to Transfers
        </a>
    </div>

    <div class="card shadow">
        <div class="card-header">
            All Transfer Records
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $transfer->product->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->fromBranch->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->toBranch->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No transfer history found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection