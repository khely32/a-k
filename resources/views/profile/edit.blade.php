@extends('layouts.app')

@section('content')
<style>
    .profile-title {
        color: var(--green);
        text-shadow: 0 0 10px rgba(0, 230, 118, 0.3);
        font-size: 1.25rem;
        font-weight: 800;
    }
    .profile-card {
        background: #0F172A;
        border: 1px solid #1E293B;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }
    .profile-card .form-label {
        color: #CBD5E1;
        font-weight: 500;
        font-size: 0.8rem;
    }
    .profile-card .form-control {
        background: #1E293B;
        border: 1px solid #475569;
        color: #FFFFFF;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.88rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .profile-card .form-control:focus {
        border-color: #10B981;
        background: #1E293B;
        color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .profile-card .form-control:read-only,
    .profile-card .form-control[disabled] {
        background: rgba(30, 41, 59, 0.5);
        border-color: rgba(51, 65, 85, 0.6);
        color: #94A3B8;
        cursor: not-allowed;
    }
    .profile-save {
        background: #059669;
        border: none;
        border-radius: 10px;
        color: #FFFFFF;
        font-weight: 600;
        padding: 10px 26px;
        transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
    }
    .profile-save:hover {
        background: #10B981;
        color: #FFFFFF;
        box-shadow: 0 4px 18px rgba(5, 150, 105, 0.4);
        transform: translateY(-1px);
    }
</style>

<div class="container-fluid">
    <h2 class="profile-title mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="background:rgba(34,197,94,0.15);color:#22c55e;border-left:4px solid #22c55e;border-radius:12px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger p-2 py-1 small" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#FECDD3;border-radius:10px;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card profile-card col-md-6 border-0">
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    @if(strtolower(auth()->user()->role) === 'owner')
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @else
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" disabled>
                        <input type="hidden" name="name" value="{{ $user->name }}">
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    @if(strtolower(auth()->user()->role) === 'owner')
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @else
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" disabled>
                        <input type="hidden" name="email" value="{{ $user->email }}">
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <input type="text" class="form-control" value="{{ $user->branchDisplayName() }}" disabled>
                </div>

                <button type="submit" class="btn profile-save px-4">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
