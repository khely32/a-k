@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center mt-5">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-primary text-white text-center">
                    <h3>A&K Motorcycle Parts Registration</h3>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Security Contact (Email or Phone)</label>
                            <input
                                type="text"
                                name="security_contact"
                                class="form-control"
                                value="{{ old('security_contact') }}"
                                placeholder="e.g., secondary@gmail.com or 0917xxxxxxx"
                                required>
                            <small class="text-muted">Used for password recovery verification</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assigned Branch</label>

                            <div id="branch-section" style="display:block;">

                                <select
                                    name="branch"
                                    id="branch"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select Branch
                                    </option>

                                    <option value="Moroboro Branch">
                                        Moroboro Branch
                                    </option>

                                    <option value="Poblacion Branch">
                                        Poblacion Branch
                                    </option>

                                    <option value="San Matias Branch">
                                        San Matias Branch
                                    </option>

                                    <option value="Banate Branch">
                                        Banate Branch
                                    </option>

                                </select>

                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            Register Account

                        </button>

                    </form>

                    <hr>

                    <div class="text-center">

                        <a href="{{ route('login') }}">
                            Already have an account? Login Here
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

@endsection