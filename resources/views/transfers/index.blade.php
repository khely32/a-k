@extends('layouts.app')

@section('content')
<style>
    :root{
        --cyan:#00e5ff;
        --purple:#7c3aed;
        --green:#00ff88;
        --orange:#ffb300;
        --red:#ff4d6d;
        --bg:#0f172a;
        --card:#1e293b;
        --border:rgba(0,229,255,.2);
    }

    .glass-card{
        background:rgba(30,41,59,.88);
        backdrop-filter:blur(14px);
        border:1px solid var(--border);
        border-radius:20px;
        box-shadow:
            0 0 20px rgba(0,229,255,.08),
            0 0 40px rgba(124,58,237,.08);
    }

    .title-glow{
        color:var(--cyan);
        text-shadow:
            0 0 8px var(--cyan),
            0 0 20px var(--cyan);
    }

    .neon-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        padding:10px 18px;
        border-radius:12px;
        font-weight:600;
        letter-spacing:.5px;
        transition:.3s ease;
        border:2px solid;
        text-transform:uppercase;
        background: transparent;
        cursor: pointer;
    }

    .neon-btn:hover{
        transform:translateY(-2px);
    }

    .neon-cyan{
        color:var(--cyan);
        border-color:var(--cyan);
        box-shadow:0 0 12px rgba(0,229,255,.4);
    }

    .neon-cyan:hover{
        background:var(--cyan);
        color:#000;
        box-shadow:0 0 25px var(--cyan);
    }

    .neon-green{
        color:var(--green);
        border-color:var(--green);
        box-shadow:0 0 12px rgba(0,255,136,.4);
    }

    .neon-green:hover{
        background:var(--green);
        color:#000;
        box-shadow:0 0 25px var(--green);
    }

    .neon-red{
        color:var(--red);
        border-color:var(--red);
        box-shadow:0 0 12px rgba(255,77,109,.4);
    }

    .neon-red:hover{
        background:var(--red);
        color:#fff;
        box-shadow:0 0 25px var(--red);
    }

    .neon-purple{
        color:var(--purple);
        border-color:var(--purple);
        box-shadow:0 0 12px rgba(124,58,237,.4);
    }

    .neon-purple:hover{
        background:var(--purple);
        color:#fff;
        box-shadow:0 0 25px var(--purple);
    }

    .neon-btn-sm{
        padding:5px 12px;
        font-size:.75rem;
        border-radius:8px;
        gap:4px;
        letter-spacing:.3px;
    }

    table{
        border-collapse:separate;
        border-spacing:0;
    }

    thead{
        background:linear-gradient(90deg,#111827,#1e293b);
    }

    thead th{
        color:var(--cyan);
        font-size:.82rem;
        letter-spacing:.08em;
        text-transform:uppercase;
        border-bottom:1px solid rgba(0,229,255,.2);
    }

    tbody tr{
        transition:.25s ease;
    }

    tbody tr:hover{
        background:rgba(0,229,255,.06);
    }

    .status{
        padding:6px 12px;
        border-radius:999px;
        font-size:.75rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.08em;
        display: inline-block;
    }

    .status-pending{
        color:var(--orange);
        border:1px solid var(--orange);
        box-shadow:0 0 10px rgba(255,179,0,.3);
    }

    .status-approved{
        color:var(--green);
        border:1px solid var(--green);
        box-shadow:0 0 10px rgba(0,255,136,.3);
    }

    .status-rejected{
        color:var(--red);
        border:1px solid var(--red);
        box-shadow:0 0 10px rgba(255,77,109,.3);
    }

    .status-received{
        color:var(--cyan);
        border:1px solid var(--cyan);
        box-shadow:0 0 10px rgba(0,229,255,.3);
    }

    .alert-success{
        background:rgba(0,255,136,.08);
        border:1px solid rgba(0,255,136,.4);
        color:#b7ffd8;
        border-radius:14px;
    }

    .alert-error{
        background:rgba(255,77,109,.08);
        border:1px solid rgba(255,77,109,.4);
        color:#ffb7c5;
        border-radius:14px;
    }

    .empty-state{
        color:#94a3b8;
    }

    .staff-notice{
        background:rgba(0,229,255,.06);
        border:1px solid rgba(0,229,255,.28);
        color:#a5f3fc;
        border-radius:14px;
        font-size:.9rem;
    }

    .owner-notice{
        background:rgba(0,255,136,.06);
        border:1px solid rgba(0,255,136,.28);
        color:#b7ffd8;
        border-radius:14px;
        font-size:.9rem;
    }
</style>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 title-glow">Stock Transfers</h1>
        <a href="{{ route('transfers.create') }}" class="neon-btn neon-cyan">
            <i class="bi bi-plus-lg me-1"></i>
            @if(strtolower(Auth::user()->role) === 'owner')
                New Stock Transfer
            @else
                Request Stock Transfer
            @endif
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success mb-4 p-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error mb-4 p-3">
            {{ session('error') }}
        </div>
    @endif

    @if(strtolower(Auth::user()->role) !== 'owner')
        <div class="staff-notice mb-4 p-3">
            <i class="bi bi-shield-lock me-2"></i>
            Showing only stock transfer requests involving your branch
            (<strong>{{ auth()->user()->branchLabel() }}</strong>) as source or destination.
        </div>
    @else
        <div class="owner-notice mb-4 p-3">
            <i class="bi bi-eye me-2"></i>
            Showing all stock transfers across all branches.
        </div>
    @endif

    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-striped table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transfers as $transfer)
                    @php
                        $status = strtolower($transfer->status);
                        $userBranchId = (int) auth()->user()->branch_id;
                        $isFromBranch = $userBranchId === (int) $transfer->from_branch_id;
                    @endphp
                    <tr>
                        <td class="text-cyan-300 fw-bold">#{{ $transfer->id }}</td>
                        <td>{{ $transfer->fromBranch->branch_name ?? '-' }}</td>
                        <td>{{ $transfer->toBranch->branch_name ?? '-' }}</td>
                        <td class="fw-medium">{{ $transfer->product->name ?? '-' }}</td>
                        <td class="fw-semibold text-cyan-400">{{ $transfer->quantity }}</td>
                        <td>
                            <span class="status
                                @if($status === 'pending') status-pending
                                @elseif($status === 'approved') status-approved
                                @elseif($status === 'received') status-received
                                @else status-rejected
                                @endif">
                                {{ ucfirst(strtolower($transfer->status)) }}
                            </span>
                        </td>
                        <td>{{ $transfer->created_at->format('M d, Y') }}</td>
                        <td class="text-center">
                            @if($status === 'pending')

                                @if($isFromBranch)

                                    <form action="{{ route('transfers.approve', $transfer->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Approve this transfer?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="neon-btn neon-green neon-btn-sm">Approve</button>
                                    </form>

                                    <form action="{{ route('transfers.reject', $transfer->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Decline this transfer?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="neon-btn neon-red neon-btn-sm">Decline</button>
                                    </form>

                                @else
                                    <span class="text-secondary small fst-italic fw-medium">Awaiting {{ $transfer->fromBranch->branch_name ?? 'Source Branch' }} Approval</span>
                                @endif

                            @elseif($status === 'approved')

                                <span class="text-success small fw-semibold">Approved</span>

                            @elseif($status === 'received')

                                <span class="text-cyan small fw-semibold">Received</span>

                            @elseif($status === 'rejected')

                                <span class="text-secondary small fst-italic fw-medium">Rejected</span>

                            @else
                                <span class="text-secondary small fst-italic fw-medium">Processed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-5">
                            No transfer requests found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
