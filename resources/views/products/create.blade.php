@extends('layouts.app')

@section('content')
<style>
    .add-page{flex:1;display:flex;align-items:center;justify-content:center;min-height:calc(100vh - 40px);padding:32px 0;}
    .add-product-card{width:100%;max-width:760px;margin:0 auto;background:#131B26;border:1px solid #1E293B;border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.02);}
    .add-product-body{padding:28px;}
    .add-product-header{display:flex;align-items:center;justify-content:space-between;padding:20px 28px;border-bottom:1px solid #1E293B;}
    .add-product-title{color:#fff;font-size:1.5rem;font-weight:700;letter-spacing:.01em;margin:0;}
    .add-product-icon{color:#10B981;font-size:1.35rem;}
    .form-label{color:#94A3B8;font-size:.875rem;font-weight:500;margin-bottom:.4rem;}
    .add-product-body .form-control,.add-product-body .form-select{background:#1D283A;border:1px solid #334155;color:#fff;border-radius:8px;}
    .add-product-body .form-control:focus,.add-product-body .form-select:focus{background:#1D283A;border-color:#059669;color:#fff;box-shadow:0 0 0 3px rgba(5,150,105,.22);}
    .add-product-body .form-control::placeholder,.add-product-body .form-select::placeholder{color:#64748B;opacity:1;}
    .add-product-body .form-control:disabled,.add-product-body .form-select:disabled{background:rgba(29,40,58,.6);color:#94A3B8;border-color:#334155;}
    .add-product-body .form-select option{background:#1D283A;color:#fff;}
    .add-product-body .text-muted{color:#94A3B8!important;}
    .invalid-feedback{color:#f87171;}
    .btn-add-cancel{background:#334155;border:1px solid #475569;color:#E2E8F0;border-radius:8px;padding:8px 20px;font-weight:600;}
    .btn-add-cancel:hover{background:#475569;color:#fff;border-color:#64748B;}
    .btn-add-save{background:#059669;border:none;color:#fff;border-radius:8px;padding:8px 24px;font-weight:600;transition:.2s;}
    .btn-add-save:hover{background:#047857;color:#fff;box-shadow:0 0 18px rgba(5,150,105,.4);}
    .add-product-footer{display:flex;justify-content:flex-end;gap:12px;padding:16px 28px 24px;border-top:1px solid #1E293B;}
</style>

<div class="add-page">
        <div class="add-product-card">
            <div class="add-product-header">
                <h1 class="add-product-title"><i class="bi bi-box-seam-fill me-2 add-product-icon"></i> Add New Product</h1>
                <a href="{{ route('products.index') }}" class="btn btn-add-cancel btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
            </div>

            <div class="add-product-body">
                <form action="{{ route('products.store') }}" method="POST" id="addProductForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Part Name / Item Description</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., Engine Oil, Brake Pad" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand') }}" placeholder="e.g., Honda, Yamaha" required>
                            @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type / Category</label>
                            <input type="text" name="type" id="type" class="form-control @error('type') is-invalid @enderror" value="{{ old('type') }}" placeholder="e.g., Accessories, Lubricants" list="categoryList" required>
                            <datalist id="categoryList">
                                @php $categories = \App\Models\CategorySize::distinct()->orderBy('category')->pluck('category'); @endphp
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="size" class="form-label">Size</label>
                            <input type="text" name="size" id="size" class="form-control" value="{{ old('size') }}" placeholder="Type a size, e.g., 1L, 400mL, 17 inch" list="sizeList" autocomplete="off">
                            <datalist id="sizeList"></datalist>
                            <small class="text-muted">Type your desired size or pick a suggested one for the category.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color / Shade <span class="text-muted">(Spray Paint)</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}" placeholder="e.g., Red, Gloss Black, Metallic Blue" autocomplete="off">
                                <span id="color-swatch" style="width:30px;height:30px;border-radius:6px;background:#334155;border:1px solid rgba(255,255,255,0.25);flex-shrink:0;display:inline-block;"></span>
                            </div>
                            <small class="text-muted">Use for spray paint / aerosol products</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Stock Quantity Level</label>
                            <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 0) }}" min="0" required>
                            @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea name="description" id="description" class="form-control" rows="2" placeholder="Additional details about the product">{{ old('description') }}</textarea>
                    </div>

                    <div class="add-product-footer">
                        <a href="{{ route('products.index') }}" class="btn btn-add-cancel">Cancel</a>
                        <button type="submit" class="btn btn-add-save"><i class="bi bi-check-lg me-1"></i> Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInput = document.getElementById('type');
    const sizeInput = document.getElementById('size');
    const sizeList = document.getElementById('sizeList');

    function loadSizes() {
        const category = typeInput.value.trim();
        sizeList.innerHTML = '';
        if (!category) return;

        fetch(`/category-sizes/${encodeURIComponent(category)}`)
            .then(res => res.json())
            .then(sizes => {
                sizeList.innerHTML = '';
                sizes.forEach(size => {
                    const opt = document.createElement('option');
                    opt.value = size;
                    sizeList.appendChild(opt);
                });
            })
            .catch(() => {});
    }

    typeInput.addEventListener('change', loadSizes);
    typeInput.addEventListener('blur', function() {
        setTimeout(loadSizes, 200);
    });

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
        if (!v) return '#334155';
        for (const key in COLOR_MAP) {
            if (v.includes(key)) return COLOR_MAP[key];
        }
        return '#334155';
    }

    colorInput.addEventListener('input', function() {
        colorSwatch.style.background = swatchHex(colorInput.value);
    });
});
</script>
@endsection