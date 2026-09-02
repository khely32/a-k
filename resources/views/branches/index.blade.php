@extends('layouts.app')

@section('content')
<style>
    :root{
        --cyan:#00e5ff;
        --purple:#9d4edd;
        --green:#00ff88;
        --pink:#ff007f;
        --bg:#0a0b10;
        --card-bg:linear-gradient(135deg, rgba(20,24,33,0.85), rgba(10,11,16,0.95));
        --text-muted:#8b9bb4;
    }
    .cyber-card{
        background:var(--card-bg);
        border-radius:16px;
        border:1px solid rgba(255,255,255,0.06);
        padding:28px 24px;
        height:100%;
        transition:all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow:0 12px 40px rgba(0,0,0,0.6);
    }
    .cyber-card:hover{
        transform:translateY(-6px);
    }
    .branch-header{
        background:linear-gradient(90deg, #111827, #1e293b);
        border-bottom:1px solid rgba(0,229,255,0.2);
    }
    .branch-header th{
        color:var(--cyan);
        font-size:.82rem;
        letter-spacing:.08em;
        text-transform:uppercase;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color:var(--cyan);text-shadow:0 0 8px var(--cyan);">
                <i class="bi bi-diagram-3-fill me-2"></i> Network Branch Offices
            </h1>
            <p class="text-muted mb-0">Manage all branch locations, monitor performance, and oversee operations.</p>
        </div>
        <a href="{{ route('branches.create') }}" class="btn" style="background:rgba(0,229,255,0.1);color:var(--cyan);border:1px solid rgba(0,229,255,0.3);border-radius:8px;padding:10px 24px;text-decoration:none;font-weight:600;">
            <i class="bi bi-plus-lg me-1"></i> Add Branch
        </a>
    </div>

    @if(session('success'))
        <div class="alert mb-4 p-3" style="background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.4);color:#b7ffd8;border-radius:14px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert mb-4 p-3" style="background:rgba(255,77,109,0.08);border:1px solid rgba(255,77,109,0.4);color:#ffb7c5;border-radius:14px;">
            {{ session('error') }}
        </div>
    @endif

    @if($branches->count() > 0)
    <div class="row g-4 mb-4">
        @foreach($branches as $branch)
        <div class="col-xl-4 col-md-6">
            <div class="cyber-card" style="border-color:rgba(157,78,221,0.15);padding:24px;">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.05);">
                    <div style="width:50px;height:50px;border-radius:14px;background:rgba(157,78,221,0.15);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
                        @if($loop->first) 🏢 @else 🏪 @endif
                    </div>
                    <div style="flex:1;">
                        <div class="fw-bold" style="color:#fff;font-size:1.1rem;">{{ $branch->branch_name }}</div>
                        <div style="color:var(--text-muted);font-size:0.85rem;">
                            <i class="bi bi-geo-alt me-1"></i> {{ $branch->address ?? $branch->location ?? 'No address' }}
                        </div>
                    </div>
                    <div>
                        <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.7rem;font-weight:700;text-transform:uppercase;{{ $branch->is_active ? 'background:rgba(0,230,118,0.12);color:#10B981;border:1px solid rgba(0,230,118,0.3);' : 'background:rgba(239,68,68,0.12);color:#EF4444;border:1px solid rgba(239,68,68,0.3);' }}">
                            {{ $branch->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                </div>

                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="fw-bold" style="color:var(--cyan);font-size:1.3rem;">{{ $branch->products_count }}</div>
                        <div style="color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;">Products</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold" style="color:var(--green);font-size:1.3rem;">₱{{ $branch->revenue }}</div>
                        <div style="color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;">Revenue</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold" style="color:var(--pink);font-size:1.3rem;">{{ $branch->users_count }}</div>
                        <div style="color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;">Staff</div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2" style="border-top:1px solid rgba(255,255,255,0.05);padding-top:12px;">
                    <div style="flex:1;font-size:0.8rem;color:var(--text-muted);">
                        <span style="color:#fbbf24;">⚠️ {{ $branch->low_stock }}</span> Low · 
                        <span style="color:#ef4444;">🚫 {{ $branch->out_of_stock }}</span> Out
                    </div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">
                        Stock: <span style="color:#fff;font-weight:600;">{{ $branch->total_stock }}</span>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm flex-fill" style="background:rgba(0,229,255,0.08);color:var(--cyan);border:1px solid rgba(0,229,255,0.2);border-radius:8px;text-decoration:none;">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete this branch?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm w-100" style="background:rgba(255,77,109,0.08);color:#ff4d6d;border:1px solid rgba(255,77,109,0.2);border-radius:8px;">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="cyber-card" style="overflow:hidden;padding:0;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background:transparent;">
                <thead class="branch-header">
                    <tr>
                        <th class="py-3 ps-4">Branch</th>
                        <th class="py-3">Location</th>
                        <th class="py-3 text-center">Products</th>
                        <th class="py-3 text-center">Staff</th>
                        <th class="py-3 text-end">Revenue</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.03);">
                        <td class="ps-4 fw-bold" style="color:#fff;">{{ $branch->branch_name }}</td>
                        <td style="color:var(--text-muted);">{{ $branch->address ?? $branch->location ?? '—' }}</td>
                        <td class="text-center fw-bold" style="color:var(--cyan);">{{ $branch->products_count }}</td>
                        <td class="text-center" style="color:var(--pink);">{{ $branch->users_count }}</td>
                        <td class="text-end fw-bold" style="color:var(--green);">₱{{ $branch->revenue }}</td>
                        <td class="text-center">
                            <span style="display:inline-block;padding:3px 12px;border-radius:999px;font-size:0.7rem;font-weight:700;text-transform:uppercase;{{ $branch->is_active ? 'background:rgba(0,230,118,0.12);color:#10B981;border:1px solid rgba(0,230,118,0.3);' : 'background:rgba(239,68,68,0.12);color:#EF4444;border:1px solid rgba(239,68,68,0.3);' }}">
                                {{ $branch->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm" style="background:rgba(0,229,255,0.1);color:var(--cyan);border:none;border-radius:6px;padding:4px 10px;text-decoration:none;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this branch?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background:rgba(255,77,109,0.1);color:#ff4d6d;border:none;border-radius:6px;padding:4px 10px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-5" style="color:var(--text-muted);">
        <div style="font-size:4rem;margin-bottom:16px;opacity:0.3;">🏢</div>
        <h4 class="fw-bold mb-2" style="color:#fff;">No Branches Yet</h4>
        <p>Create your first branch to start managing locations.</p>
        <a href="{{ route('branches.create') }}" class="btn" style="background:rgba(0,229,255,0.1);color:var(--cyan);border:1px solid rgba(0,229,255,0.3);border-radius:8px;padding:10px 24px;">
            <i class="bi bi-plus-lg me-1"></i> Add Branch
        </a>
    </div>
    @endif
</div>
@endsection