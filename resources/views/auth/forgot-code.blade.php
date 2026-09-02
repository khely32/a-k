@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3>Enter Verification Code</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger p-2 py-1 small">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('email'))
                <p class="text-muted small mb-3">A 6-digit code was sent to <strong>{{ session('email') }}</strong>. Check your inbox (or spam folder).</p>
                @endif

                <form action="{{ route('forgot.code.verify') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label">Verification Code</label>
                        <input type="text" name="code" id="code" class="form-control text-center" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]*" required autofocus style="font-size:1.5rem;letter-spacing:8px;font-family:monospace;">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify Code</button>
                </form>

                <hr>
                <div class="text-center">
                    <a href="{{ route('forgot.email') }}" class="small">Request a new code</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
