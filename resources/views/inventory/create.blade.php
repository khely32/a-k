@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="max-width: 900px; margin: 0 auto;">

    <div class="mb-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none small text-muted" style="transition: 0.2s;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-muted)'">
            ← Return to Inventory Matrix
        </a>
    </div>

    <div class="d-flex align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <h2 class="fw-bold text-white mb-0" style="text-shadow: 0 0 10px rgba(0,229,255,0.2);">
            ⚙️ Register New Inventory Asset
        </h2>
    </div>

    <div class="card p-4">
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label text-uppercase text-muted fw-bold small tracking-wider" style="letter-spacing: 1px;">Component Identification Name</label>
                    <input type="text" name="name" class="form-control px-3 py-2" placeholder="e.g., Brembo Brake Pad Series-K" required autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-uppercase text-muted fw-bold small tracking-wider" style="letter-spacing: 1px;">SKU / Barcode Index</label>
                    <input type="text" name="sku" class="form-control px-3 py-2" placeholder="e.g., AK-BRM-001" required autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-uppercase text-muted fw-bold small tracking-wider" style="letter-spacing: 1px;">System Classification Category</label>
                    <select name="category" class="form-select px-3 py-2" required>
                        <option value="" disabled selected hidden>Select accessory division...</option>
                        <option value="Braking Systems">Braking Systems</option>
                        <option value="Engine Components">Engine Components</option>
                        <option value="Tires & Rims">Tires & Rims</option>
                        <option value="Suspension">Suspension</option>
                        <option value="Lighting & Electrical">Lighting & Electrical</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-uppercase text-muted fw-bold small tracking-wider" style="letter-spacing: 1px;">Retail Price (PHP)</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0" style="background: #111827; color: var(--cyan); border-color: rgba(0,229,255,.18); border-radius: 12px 0 0 12px;">₱</span>
                        <input type="number" step="0.01" name="price" class="form-control border-start-0" style="border-radius: 0 12px 12px 0;" placeholder="0.00" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-uppercase text-muted fw-bold small tracking-wider" style="letter-spacing: 1px;">Initial Allocation Stock</label>
                    <input type="number" name="quantity" class="form-control px-3 py-2" placeholder="0" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-uppercase text-muted fw-bold small tracking-wider" style="letter-spacing: 1px;">Alert Threshold Floor</label>
                    <input type="number" name="alert_threshold" class="form-control px-3 py-2" placeholder="5" value="5" required>
                </div>

                <div class="col-12 text-end mt-4 pt-2">
                    <button type="reset" class="btn btn-outline-danger px-4 py-2 me-2">Clear Fields</button>
                    <button type="submit" class="btn btn-primary px-5 py-2">Commit Asset to Ledger</button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection