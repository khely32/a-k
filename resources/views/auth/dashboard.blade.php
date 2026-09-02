@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow border-0">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <h2 class="fw-bold">Welcome, {{ $user->name }}</h2>

                            <p class="text-muted mb-0">
                                Role: {{ strtoupper($user->role) }}
                            </p>

                            <p class="text-muted">
                                Branch: {{ $user->branch }}
                            </p>
                        </div>

                        <div>
                            <a href="{{ route('reports.index') }}"
                               class="btn btn-primary">
                                📊 Reports
                            </a>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    @if($user->role == 'owner')

    <div class="row g-3">

        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Branches</h6>
                    <h2>{{ $stats['total_branches'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Products</h6>
                    <h2>{{ $stats['total_products'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning">
                <div class="card-body text-center">
                    <h6>Low Stock Items</h6>
                    <h2>{{ $stats['low_stock'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Pending Transfers</h6>
                    <h2>{{ $stats['pending_transfers'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

    </div>

    @else

    <div class="row g-3">

        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5>Your Branch</h5>
                    <h3>{{ $user->branch }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-warning">
                <div class="card-body text-center">
                    <h5>Low Stock Alerts</h5>
                    <h3>{{ $stats['low_stock'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

    </div>

    @endif

</div>

@endsection