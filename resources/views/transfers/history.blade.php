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

    /* ---------- Dark themed records card ---------- */
    .hist-card{
        background:rgba(30,41,59,.88);
        backdrop-filter:blur(14px);
        border:1px solid rgba(0,229,255,.2);
        border-radius:20px;
        box-shadow:0 0 20px rgba(0,229,255,.08), 0 0 40px rgba(124,58,237,.08);
        overflow:hidden;
    }
    .hist-card-header{
        color:#e2e8f0;
        font-weight:700;
        font-size:1.05rem;
        letter-spacing:.5px;
        padding:18px 22px;
        background:rgba(15,23,42,.35);
        border-bottom:1px solid rgba(148,163,184,.18);
    }
    .hist-card-body{ padding:0; }

    .hist-table{
        width:100%;
        margin:0;
        border-collapse:separate;
        border-spacing:0;
        color:#cbd5e1;
    }
    .hist-table thead{
        background:linear-gradient(90deg,#111827,#1e293b);
    }
    .hist-table thead th{
        color:var(--cyan);
        font-size:.75rem;
        font-weight:600;
        letter-spacing:.08em;
        text-transform:uppercase;
        padding:14px 16px;
        border-bottom:1px solid rgba(0,229,255,.2);
        white-space:nowrap;
    }
    .hist-table thead th.text-right{ text-align:right; }
    .hist-table tbody td{
        padding:14px 16px;
        font-size:.875rem;
        color:#cbd5e1;
        border-bottom:1px solid rgba(148,163,184,.1);
    }
    .hist-table tbody tr{ transition:background .25s ease; }
    .hist-table tbody tr:hover{ background:rgba(0,229,255,.06); }
    .hist-table tbody tr:last-child td{ border-bottom:none; }
    .hist-table .hist-product{ color:#f1f5f9; font-weight:500; }
    .hist-table .hist-date{ color:#94a3b8; }
    .hist-table .hist-qty{
        color:var(--green);
        font-weight:700;
        text-align:right;
    }
    .hist-empty{ color:#94a3b8; }
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

    <div class="hist-card">
        <div class="hist-card-header">
            All Transfer Records
        </div>
        <div class="hist-card-body">
            <div class="table-responsive">
                <table class="hist-table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th class="text-right">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td class="hist-date">{{ $transfer->created_at->format('M d, Y h:i A') }}</td>
                            <td class="hist-product">{{ $transfer->product->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->fromBranch->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->toBranch->name ?? 'N/A' }}</td>
                            <td class="hist-qty">{{ $transfer->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 hist-empty">
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