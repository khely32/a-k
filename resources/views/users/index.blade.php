@extends('layouts.app')
@section('content')
<style>
    .user-card {
        background: rgba(30, 41, 59, 0.88);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(0, 230, 118, 0.12);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0, 230, 118, 0.05);
    }
    .user-card thead th {
        color: var(--green);
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(0, 230, 118, 0.12);
        background: linear-gradient(90deg, #111827, #1e293b);
        font-weight: 700;
    }
    .branch-header-bar {
        background: linear-gradient(135deg, #0D1117 0%, #161B22 100%);
        border: 1px solid rgba(0, 230, 118, 0.12);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .branch-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--green);
        text-shadow: 0 0 10px rgba(0, 230, 118, 0.2);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .branch-title i { font-size: 1.2rem; }

    .badge-active {
        background: rgba(0, 230, 118, 0.1);
        color: #10B981;
        border: 1px solid rgba(0, 230, 118, 0.3);
        padding: 3px 12px;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-disabled {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 3px 12px;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .toggle-switch {
        position: relative;
        width: 44px;
        height: 24px;
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.3s;
        border: none;
        outline: none;
        flex-shrink: 0;
    }
    .toggle-switch.on { background: #10B981; }
    .toggle-switch.off { background: #475569; }
    .toggle-switch .toggle-knob {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        transition: transform 0.25s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .toggle-switch.on .toggle-knob { transform: translateX(20px); }

    .role-badge {
        padding: 3px 12px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: inline-block;
    }
    .role-owner { background: rgba(124, 58, 237, 0.2); color: #a78bfa; border: 1px solid rgba(124, 58, 237, 0.4); }
    .role-admin { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
    .role-manager { background: rgba(0, 230, 118, 0.12); color: #10B981; border: 1px solid rgba(0, 230, 118, 0.3); }
    .role-staff { background: rgba(255, 193, 7, 0.12); color: #FFC107; border: 1px solid rgba(255, 193, 7, 0.3); }
    .role-cashier { background: rgba(0, 229, 255, 0.12); color: #00E5FF; border: 1px solid rgba(0, 229, 255, 0.3); }

    .pw-masked {
        font-family: 'Courier New', monospace;
        font-size: 0.82rem;
        color: #64748B;
        letter-spacing: 2px;
    }
    .pw-reset-wrap { display: flex; flex-direction: column; gap: 4px; }
    .pw-input-row { display: flex; align-items: center; gap: 6px; }
    .pw-input {
        background: #0D1117;
        border: 1px solid #475569;
        color: #fff;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 0.78rem;
        width: 170px;
        outline: none;
        font-family: 'Courier New', monospace;
        transition: border-color 0.2s;
    }
    .pw-input:focus { border-color: var(--cyan); box-shadow: 0 0 6px rgba(0, 229, 255, 0.2); }
    .pw-eye {
        background: rgba(0, 229, 255, 0.08);
        border: 1px solid rgba(0, 229, 255, 0.2);
        color: var(--cyan);
        border-radius: 6px;
        padding: 5px 8px;
        cursor: pointer;
        font-size: 0.82rem;
        transition: all 0.2s;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pw-eye:hover { background: rgba(0, 229, 255, 0.18); border-color: var(--cyan); box-shadow: 0 0 8px rgba(0, 229, 255, 0.2); }
    .pw-eye.revealed { background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.3); color: #EF4444; }
    .pw-eye.revealed:hover { background: rgba(239, 68, 68, 0.18); box-shadow: 0 0 8px rgba(239, 68, 68, 0.2); }
    .pw-hint {
        font-size: 0.65rem;
        color: #64748B;
        font-style: italic;
    }
    .pw-display { display: flex; align-items: center; gap: 10px; }
    .pw-plaintext {
        font-family: 'Courier New', monospace;
        font-size: 0.82rem;
        color: var(--cyan);
        background: rgba(0, 229, 255, 0.06);
        border: 1px solid rgba(0, 229, 255, 0.15);
        padding: 2px 10px;
        border-radius: 4px;
        letter-spacing: 0.5px;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .btn-sm-action {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-reset-pw { background: rgba(0, 229, 255, 0.1); color: var(--cyan); }
    .btn-reset-pw:hover { background: rgba(0, 229, 255, 0.2); }
    .btn-save-pw { background: rgba(0, 230, 118, 0.12); color: #10B981; }
    .btn-save-pw:hover { background: rgba(0, 230, 118, 0.22); }
    .btn-cancel-pw { background: rgba(100,116,139,0.15); color: #94A3B8; }
    .btn-cancel-pw:hover { background: rgba(100,116,139,0.25); }

    .email-cell { display: flex; align-items: center; gap: 8px; }
    .email-text { font-size: 0.82rem; color: #94A3B8; min-width: 100px; }
    .email-edit-wrap { display: flex; align-items: center; gap: 6px; }
    .email-edit-input {
        background: #0D1117;
        border: 1px solid var(--cyan);
        color: #fff;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.78rem;
        width: 200px;
        outline: none;
    }
    .email-edit-input:focus { box-shadow: 0 0 8px rgba(0, 229, 255, 0.25); }
    .btn-edit-email { background: rgba(0, 229, 255, 0.1); color: var(--cyan); border: none; border-radius: 5px; padding: 3px 7px; cursor: pointer; font-size: 0.75rem; transition: all 0.15s; }
    .btn-edit-email:hover { background: rgba(0, 229, 255, 0.2); }
    .btn-save-email { background: rgba(0, 230, 118, 0.12); color: #10B981; border: none; border-radius: 5px; padding: 3px 7px; cursor: pointer; font-size: 0.75rem; transition: all 0.15s; }
    .btn-save-email:hover { background: rgba(0, 230, 118, 0.22); }
    .btn-cancel-email { background: rgba(100,116,139,0.15); color: #94A3B8; border: none; border-radius: 5px; padding: 3px 7px; cursor: pointer; font-size: 0.75rem; transition: all 0.15s; }
    .btn-cancel-email:hover { background: rgba(100,116,139,0.25); }

    .pw-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        z-index: 9999;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s;
        pointer-events: none;
    }
    .pw-toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
    .pw-toast.success { background: rgba(0,230,118,0.15); color: #10B981; border: 1px solid rgba(0,230,118,0.3); }
    .pw-toast.error { background: rgba(239,68,68,0.15); color: #EF4444; border: 1px solid rgba(239,68,68,0.3); }

    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .owner-only { display: none; }
    body.is-owner .owner-only { display: table-cell; }
    body.is-owner .owner-only-flex { display: flex; }
</style>

@if($isOwner)
<script>document.body.classList.add('is-owner');</script>
@endif

<div class="container-fluid py-4">
    @if(session('success'))
        <div class="mb-3 p-3" style="background:rgba(0,230,118,0.08);border:1px solid rgba(0,230,118,0.3);color:#A7F3D0;border-radius:12px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-3 p-3" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);color:#FECDD3;border-radius:12px;">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="font-size:1.2rem;font-weight:800;color:var(--green);text-shadow:0 0 10px rgba(0,230,118,0.2);">
                <i class="bi bi-people-fill me-2"></i> Users Management
            </h1>
            <p style="font-size:0.75rem;color:#6B7280;margin:0;">
                @if($isOwner) Owner view — full access to passwords, credentials, and branch controls
                @else Staff view — limited to your branch
                @endif
            </p>
        </div>
        @if($isOwner)
        <a href="{{ route('users.create') }}" class="btn btn-sm" style="background:#059669;color:#fff;border-radius:8px;padding:8px 18px;text-decoration:none;font-weight:600;font-size:0.82rem;">
            <i class="bi bi-plus-lg me-1"></i> Add User
        </a>
        @endif
    </div>

    @forelse($grouped as $branchName => $branchUsers)
    @php
        $firstUser = $branchUsers->first();
        $branchId = $firstUser ? $firstUser->branch_id : null;
        $branchObj = $branchId ? \App\Models\Branch::find($branchId) : null;
        $branchIsActive = $branchObj ? $branchObj->is_active : true;
    @endphp

    <div class="branch-header-bar">
        <div class="branch-title">
            <i class="bi bi-building"></i>
            {{ $branchName }}
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <span id="badge-{{ $branchId }}" class="{{ $branchIsActive ? 'badge-active' : 'badge-disabled' }}">
                {{ $branchIsActive ? 'Active' : 'Disabled' }}
            </span>
            @if($isOwner && $branchId !== 8)
            <button
                class="toggle-switch {{ $branchIsActive ? 'on' : 'off' }}"
                id="toggle-{{ $branchId }}"
                onclick="toggleBranch({{ $branchId }})"
                title="{{ $branchIsActive ? 'Disable this branch' : 'Enable this branch' }}"
            >
                <div class="toggle-knob"></div>
            </button>
            @endif
        </div>
    </div>

    <div class="user-card table-responsive mb-4">
        <table class="table table-dark table-borderless align-middle mb-0">
            <thead>
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Username</th>
                    <th class="owner-only px-4 py-3">Password</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="owner-only px-4 py-3" style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchUsers as $user)
                <tr id="row-{{ $user->id }}">
                    <td class="px-4 py-3 fw-semibold" style="color:#fff;">{{ $user->name }}</td>
                    <td class="px-4 py-3" style="color:#94A3B8;">
                        <div class="email-cell" id="email-display-{{ $user->id }}">
                            <span class="email-text" id="email-text-{{ $user->id }}">{{ $user->email }}</span>
                            @if($isOwner)
                            <button class="btn-edit-email" onclick="showEmailEdit({{ $user->id }})" title="Edit username">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                        </div>
                        @if($isOwner)
                        <div class="email-edit-wrap" id="email-edit-{{ $user->id }}" style="display:none;">
                            <input type="email" class="email-edit-input" id="email-input-{{ $user->id }}" value="{{ $user->email }}">
                            <button class="btn-save-email" onclick="saveEmail({{ $user->id }})" title="Save">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn-cancel-email" onclick="cancelEmailEdit({{ $user->id }})" title="Cancel">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        @endif
                    </td>
                    <td class="owner-only px-4 py-3">
                        <div class="pw-display">
                            <span class="pw-masked" id="pw-dots-{{ $user->id }}">&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;</span>
                            <span class="pw-plaintext" id="pw-text-{{ $user->id }}" style="display:none;"></span>
                            <button
                                type="button"
                                class="pw-eye"
                                id="pw-eye-{{ $user->id }}"
                                onclick="toggleRevealPassword({{ $user->id }})"
                                title="Reveal password"
                            >
                                <i class="bi bi-eye" id="pw-eye-icon-{{ $user->id }}"></i>
                            </button>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="role-badge
                            @if(strtolower($user->role) === 'owner') role-owner
                            @elseif(strtolower($user->role) === 'admin') role-admin
                            @elseif(strtolower($user->role) === 'manager') role-manager
                            @elseif(strtolower($user->role) === 'staff') role-staff
                            @else role-cashier
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="owner-only px-4 py-3" style="text-align:right;">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="text-center py-5" style="color:#6B7280;">
        <i class="bi bi-people" style="font-size:3rem;opacity:0.3;"></i>
        <h5 class="mt-3 fw-bold" style="color:#94A3B8;">No users found.</h5>
    </div>
    @endforelse
</div>

<div class="pw-toast" id="pwToast"></div>

<script>
function showToast(msg, type) {
    var t = document.getElementById('pwToast');
    t.textContent = msg;
    t.className = 'pw-toast ' + type + ' show';
    setTimeout(function(){ t.className = 'pw-toast'; }, 2500);
}

var pwRevealed = {};
var pwTimers = {};

function toggleRevealPassword(userId) {
    if (pwRevealed[userId]) {
        hidePassword(userId);
        return;
    }

    var btn = document.getElementById('pw-eye-' + userId);
    var icon = document.getElementById('pw-eye-icon-' + userId);
    btn.style.pointerEvents = 'none';
    icon.className = 'bi bi-arrow-repeat';
    icon.style.animation = 'spin 0.8s linear infinite';

    fetch('/users/' + userId + '/reveal-password', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        btn.style.pointerEvents = '';
        icon.style.animation = '';

        if (d.password && d.password !== '(no password stored)') {
            document.getElementById('pw-dots-' + userId).style.display = 'none';
            var textEl = document.getElementById('pw-text-' + userId);
            textEl.textContent = d.password;
            textEl.style.display = 'inline';

            icon.className = 'bi bi-eye-slash';
            btn.classList.add('revealed');
            btn.title = 'Hide password';
            pwRevealed[userId] = true;

            if (pwTimers[userId]) clearTimeout(pwTimers[userId]);
            pwTimers[userId] = setTimeout(function(){ hidePassword(userId); }, 8000);
        } else {
            icon.className = 'bi bi-eye';
            showToast(d.password || 'No password stored.', 'error');
        }
    })
    .catch(function(){
        btn.style.pointerEvents = '';
        icon.style.animation = '';
        icon.className = 'bi bi-eye';
        showToast('Failed to fetch password.', 'error');
    });
}

function hidePassword(userId) {
    if (pwTimers[userId]) clearTimeout(pwTimers[userId]);
    document.getElementById('pw-dots-' + userId).style.display = '';
    var textEl = document.getElementById('pw-text-' + userId);
    textEl.style.display = 'none';
    textEl.textContent = '';

    var icon = document.getElementById('pw-eye-icon-' + userId);
    var btn = document.getElementById('pw-eye-' + userId);
    icon.className = 'bi bi-eye';
    btn.classList.remove('revealed');
    btn.title = 'Reveal password';
    pwRevealed[userId] = false;
}

function toggleBranch(branchId) {
    fetch('/branches/' + branchId + '/toggle', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var btn = document.getElementById('toggle-' + branchId);
            var badge = document.getElementById('badge-' + branchId);
            if (d.is_active) {
                btn.className = 'toggle-switch on';
                badge.className = 'badge-active';
                badge.textContent = 'Active';
            } else {
                btn.className = 'toggle-switch off';
                badge.className = 'badge-disabled';
                badge.textContent = 'Disabled';
            }
            showToast(d.message, d.is_active ? 'success' : 'error');
        }
    })
    .catch(function(){ showToast('Failed to toggle branch.', 'error'); });
}

function showEmailEdit(userId) {
    document.getElementById('email-display-' + userId).style.display = 'none';
    document.getElementById('email-edit-' + userId).style.display = 'flex';
    var input = document.getElementById('email-input-' + userId);
    input.focus();
    input.select();
}

function cancelEmailEdit(userId) {
    document.getElementById('email-display-' + userId).style.display = 'flex';
    document.getElementById('email-edit-' + userId).style.display = 'none';
}

function saveEmail(userId) {
    var newEmail = document.getElementById('email-input-' + userId).value.trim();
    if (!newEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
        showToast('Please enter a valid email address.', 'error');
        return;
    }
    fetch('/users/' + userId + '/update-username', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: newEmail })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            document.getElementById('email-text-' + userId).textContent = d.new_email;
            cancelEmailEdit(userId);
            showToast('Username updated successfully.', 'success');
        } else {
            showToast(d.message || 'Failed to update username.', 'error');
        }
    })
    .catch(function(){ showToast('Failed to update username.', 'error'); });
}
</script>
@endsection
