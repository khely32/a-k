@extends('layouts.app')

@section('content')
<style>
    .ep-card{background:#111827;border:1px solid rgba(255,255,255,.06);border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,.45);backdrop-filter:blur(12px);}
    .ep-label{display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.45rem;}
    .ep-input{
        width:100%;background:#182232;border:1px solid rgba(255,255,255,.08);color:#e2e8f0;
        font-size:.85rem;border-radius:.75rem;padding:.6rem .9rem;outline:none;
        transition:all .2s ease;
    }
    .ep-input:focus{border-color:#00E676;box-shadow:0 0 0 3px rgba(0,230,118,.15);}
    .ep-input::placeholder{color:#475569;}
    .ep-input[readonly]{background:rgba(24,34,50,.45);border:1px solid rgba(255,255,255,.05);color:#00E676;cursor:not-allowed;font-family:'Courier New',monospace;font-weight:600;}
    .ep-input.is-invalid{border-color:#EF4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.12)!important;}
    .ep-select{
        width:100%;background:#182232;border:1px solid rgba(255,255,255,.08);color:#e2e8f0;
        font-size:.85rem;border-radius:.75rem;padding:.6rem .9rem;outline:none;
        transition:all .2s ease;appearance:none;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right .75rem center;padding-right:2rem;
    }
    .ep-select:focus{border-color:#00E676;box-shadow:0 0 0 3px rgba(0,230,118,.15);}
    .ep-textarea{resize:none;min-height:90px;}
    .ep-section-icon{
        width:28px;height:28px;border-radius:.5rem;display:inline-flex;align-items:center;justify-content:center;
        background:rgba(0,230,118,.1);color:#00E676;font-size:.75rem;flex-shrink:0;
    }
    .ep-btn-cancel{
        padding:.6rem 1.4rem;font-size:.8rem;font-weight:600;color:#94a3b8;background:#1e293b;
        border:1px solid rgba(255,255,255,.08);border-radius:.75rem;text-decoration:none;
        display:inline-flex;align-items:center;gap:.4rem;transition:all .2s ease;
    }
    .ep-btn-cancel:hover{background:#334155;color:#e2e8f0;border-color:rgba(255,255,255,.15);text-decoration:none;}
    .ep-btn-save{
        padding:.6rem 1.6rem;font-size:.8rem;font-weight:700;color:#0a0d12;background:#00E676;
        border:none;border-radius:.75rem;display:inline-flex;align-items:center;gap:.4rem;
        transition:all .25s ease;cursor:pointer;
        box-shadow:0 4px 14px rgba(0,230,118,.25);
    }
    .ep-btn-save:hover{background:#00FF88;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,230,118,.35);}
    .ep-divider{border:0;border-top:1px solid rgba(255,255,255,.05);margin:1.5rem 0;}
    .color-swatch-preview{
        width:36px;height:36px;border-radius:.5rem;background:#1e293b;
        border:2px solid rgba(255,255,255,.1);flex-shrink:0;transition:background .2s ease;
    }
</style>

<div class="container-fluid py-4" style="max-width:900px;">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2" style="color:var(--text);">
                <span class="ep-section-icon"><i class="bi bi-pencil-fill"></i></span>
                Edit Product
            </h1>
            <p class="mb-0" style="color:#64748b;font-size:.78rem;">Update item attributes, pricing, and stock configuration.</p>
        </div>
        <a href="{{ route('products.index') }}" class="ep-btn-cancel">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
    <div class="mb-4 p-3 rounded-3" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill mt-1" style="color:#EF4444;font-size:.85rem;"></i>
            <div>
                <p class="fw-bold mb-1" style="color:#EF4444;font-size:.8rem;">Please fix the following errors:</p>
                <ul class="mb-0 ps-3" style="font-size:.75rem;color:#fca5a5;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- MAIN CARD --}}
    <div class="ep-card p-4 p-md-5">
        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- SECTION: Basic Information --}}
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="ep-section-icon"><i class="bi bi-tag"></i></span>
                <span class="fw-bold" style="color:var(--text);font-size:.85rem;">Basic Information</span>
            </div>

            <div class="row g-4 mb-4">
                {{-- Serial Number --}}
                <div class="col-md-6">
                    <label class="ep-label" style="color:#64748b;">Serial Number</label>
                    <div class="position-relative">
                        <input type="text" value="{{ $product->serial_number }}" readonly class="ep-input" style="padding-right:5rem;">
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 d-flex align-items-center gap-1" style="font-size:.6rem;color:#475569;">
                            <i class="bi bi-lock-fill" style="font-size:.55rem;"></i> READ-ONLY
                        </span>
                    </div>
                    <p style="color:#475569;font-size:.65rem;margin-top:.3rem;">Auto-generated branch-prefixed serial code.</p>
                </div>

                {{-- Brand --}}
                <div class="col-md-6">
                    <label class="ep-label" style="color:#94a3b8;">Brand <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="brand" class="ep-input @error('brand') is-invalid @enderror"
                           value="{{ old('brand', $product->brand) }}" required
                           placeholder="e.g., Honda, Yamaha, Kawasaki">
                </div>

                {{-- Part Name --}}
                <div class="col-md-6">
                    <label class="ep-label" style="color:#94a3b8;">Part Name / Description <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="name" class="ep-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required
                           placeholder="e.g., Front Brake Pad Set">
                </div>

                {{-- Type --}}
                <div class="col-md-6">
                    <label class="ep-label" style="color:#94a3b8;">Type / Category <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="type" id="type" class="ep-input @error('type') is-invalid @enderror"
                           value="{{ old('type', $product->type) }}" list="categoryList" required
                           placeholder="e.g., Brake, Engine, Electrical">
                    <datalist id="categoryList">
                        @php $categories = \App\Models\CategorySize::distinct()->orderBy('category')->pluck('category'); @endphp
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <hr class="ep-divider">

            {{-- SECTION: Inventory & Pricing --}}
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="ep-section-icon"><i class="bi bi-box-seam"></i></span>
                <span class="fw-bold" style="color:var(--text);font-size:.85rem;">Inventory & Pricing</span>
            </div>

            <div class="row g-4 mb-4">
                {{-- Size --}}
                <div class="col-md-4">
                    <label class="ep-label" style="color:#94a3b8;">Size</label>
                    <select name="size" id="size" class="ep-select">
                        <option value="">Select a size (optional)</option>
                    </select>
                </div>

                {{-- Price --}}
                <div class="col-md-4">
                    <label class="ep-label" style="color:#94a3b8;">Price (₱) <span style="color:#EF4444;">*</span></label>
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ms-3" style="color:#475569;font-weight:700;font-size:.85rem;">₱</span>
                        <input type="number" step="0.01" name="price" class="ep-input @error('price') is-invalid @enderror"
                               value="{{ old('price', $product->price) }}" required min="0" style="padding-left:2rem;"
                               placeholder="0.00">
                    </div>
                </div>

                {{-- Quantity --}}
                <div class="col-md-4">
                    <label class="ep-label" style="color:#94a3b8;">Stock Quantity <span style="color:#EF4444;">*</span></label>
                    <input type="number" name="quantity" class="ep-input @error('quantity') is-invalid @enderror"
                           value="{{ old('quantity', $product->quantity) }}" min="0" required
                           placeholder="0">
                </div>

                {{-- Color (conditional for paint products) --}}
                <div class="col-md-6" id="color-field-wrap" style="display:none;">
                    <label class="ep-label" style="color:#94a3b8;">
                        Color / Shade <span style="color:#64748b;">(Spray Paint)</span>
                    </label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="text" name="color" id="color" class="ep-input"
                               value="{{ old('color', $product->color) }}"
                               placeholder="e.g., Red, Gloss Black, Metallic Blue" autocomplete="off"
                               style="flex:1;">
                        <span id="color-swatch" class="color-swatch-preview"></span>
                    </div>
                    <p style="color:#475569;font-size:.65rem;margin-top:.3rem;">Appears for spray paint / aerosol product types.</p>
                </div>
            </div>

            <hr class="ep-divider">

            {{-- SECTION: Additional Details --}}
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="ep-section-icon"><i class="bi bi-card-text"></i></span>
                <span class="fw-bold" style="color:var(--text);font-size:.85rem;">Additional Details</span>
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="ep-label" style="color:#94a3b8;">Description (Optional)</label>
                <textarea name="description" class="ep-input ep-textarea"
                          rows="3" placeholder="Additional notes or product description...">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex align-items-center justify-content-end gap-3 pt-4" style="border-top:1px solid rgba(255,255,255,.05);">
                <a href="{{ route('products.index') }}" class="ep-btn-cancel">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
                <button type="submit" class="ep-btn-save">
                    <i class="bi bi-check-lg"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInput = document.getElementById('type');
    const sizeSelect = document.getElementById('size');
    const currentSize = '{{ $product->size }}';

    function loadSizes() {
        const category = typeInput.value.trim();
        sizeSelect.innerHTML = '<option value="">Loading...</option>';
        sizeSelect.disabled = true;

        if (!category) {
            sizeSelect.innerHTML = '<option value="">Select a size (optional)</option>';
            sizeSelect.disabled = false;
            return;
        }

        fetch(`/category-sizes/${encodeURIComponent(category)}`)
            .then(res => res.json())
            .then(sizes => {
                sizeSelect.innerHTML = '<option value="">Select a size (optional)</option>';
                if (sizes.length > 0) {
                    sizes.forEach(size => {
                        const opt = document.createElement('option');
                        opt.value = size;
                        opt.textContent = size;
                        if (size === currentSize) opt.selected = true;
                        sizeSelect.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = currentSize || category;
                    opt.textContent = currentSize || category + ' (custom)';
                    opt.selected = true;
                    sizeSelect.appendChild(opt);
                }
                sizeSelect.disabled = false;
            })
            .catch(() => {
                sizeSelect.innerHTML = '<option value="">Select a size (optional)</option>';
                sizeSelect.disabled = false;
            });
    }

    typeInput.addEventListener('change', loadSizes);
    typeInput.addEventListener('blur', function() {
        setTimeout(loadSizes, 200);
    });

    const colorWrap = document.getElementById('color-field-wrap');
    const colorInput = document.getElementById('color');
    const colorSwatch = document.getElementById('color-swatch');

    const COLOR_MAP = {
        red: '#ef4444', blue: '#3b82f6', black: '#111827', white: '#f8fafc',
        green: '#22c55e', yellow: '#eab308', orange: '#f97316', purple: '#a855f7',
        pink: '#ec4899', gray: '#6b7280', grey: '#6b7280', silver: '#cbd5e1',
        brown: '#92400e', gold: '#eab308', maroon: '#7f1d1d', navy: '#1e3a8a',
        chrome: '#d1d5db', 'gloss black': '#111827', 'matte black': '#1f2937',
        'metallic blue': '#2563eb'
    };

    function swatchHex(value) {
        const v = (value || '').trim().toLowerCase();
        if (!v) return '#1e293b';
        for (const key in COLOR_MAP) {
            if (v.includes(key)) return COLOR_MAP[key];
        }
        return '#1e293b';
    }

    function isPaintCategory(value) {
        return /paint|spray|aerosol/i.test(value || '');
    }

    function toggleColorField() {
        colorWrap.style.display = isPaintCategory(typeInput.value) ? '' : 'none';
    }

    colorInput.addEventListener('input', function() {
        colorSwatch.style.background = swatchHex(colorInput.value);
    });
    typeInput.addEventListener('change', toggleColorField);
    typeInput.addEventListener('input', toggleColorField);
    toggleColorField();
    colorSwatch.style.background = swatchHex(colorInput.value);

    if (typeInput.value.trim()) {
        loadSizes();
    }
});
</script>
@endsection
