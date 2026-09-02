@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="fw-bold mb-4">My Profile</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="background:rgba(34,197,94,0.15);color:#22c55e;border-left:4px solid #22c55e;border-radius:12px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger p-2 py-1 small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card col-md-6">
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <input type="text" class="form-control" value="{{ $user->branch ?? 'N/A' }}" disabled>
                </div>

                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
