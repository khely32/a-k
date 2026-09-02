@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h2 mb-4">Add New Product</h1>

    <div class="card shadow col-md-8">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
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
                        <select name="size" id="size" class="form-select">
                            <option value="">Select a size (optional)</option>
                        </select>
                    </div>
                </div>

                <div class="row" id="color-field-wrap" style="display:none;">
                    <div class="col-md-6 mb-3">
                        <label for="color" class="form-label">Color / Shade <span class="text-muted">(Spray Paint)</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}" placeholder="e.g., Red, Gloss Black, Metallic Blue" autocomplete="off">
                            <span id="color-swatch" style="width:30px;height:30px;border-radius:6px;background:#334155;border:1px solid rgba(255,255,255,0.25);flex-shrink:0;display:inline-block;"></span>
                        </div>
                        <small class="text-muted">Applies to spray paint / aerosol products</small>
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

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInput = document.getElementById('type');
    const sizeSelect = document.getElementById('size');

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
                        sizeSelect.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = category;
                    opt.textContent = category + ' (custom)';
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
        if (!v) return '#334155';
        for (const key in COLOR_MAP) {
            if (v.includes(key)) return COLOR_MAP[key];
        }
        return '#334155';
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
});
</script>
@endsection
