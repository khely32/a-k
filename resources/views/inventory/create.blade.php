@extends('layouts.app')

@section('content')
<style>
    .inv-add-wrap{width:100%;padding:0;}
    .inv-add-card{width:100%;max-width:1100px;margin:0 auto;background:#131B26;border:1px solid #1E293B;border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.02);}
    .inv-add-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:20px 28px;border-bottom:1px solid #1E293B;}
    .inv-add-title{color:#fff;font-size:1.4rem;font-weight:700;margin:0;}
    .inv-add-icon{color:#10B981;font-size:1.3rem;}
    .inv-add-body{padding:28px;}
    .inv-add-back{background:#1E293B;border:1px solid #334155;color:#94A3B8;border-radius:8px;padding:7px 14px;font-size:.8rem;font-weight:600;text-decoration:none;transition:.15s;}
    .inv-add-back:hover{background:#334155;color:#fff;border-color:#475569;text-decoration:none;}
    .inv-add-label{display:block;color:#94A3B8;font-size:.82rem;font-weight:600;margin-bottom:.4rem;}
    .inv-add-body .form-control,.inv-add-body .form-select{background:#1D283A;border:1px solid #334155;color:#fff;border-radius:8px;}
    .inv-add-body .form-control:focus,.inv-add-body .form-select:focus{background:#1D283A;border-color:#059669;color:#fff;box-shadow:0 0 0 3px rgba(5,150,105,.22);}
    .inv-add-body .form-control::placeholder{color:#64748B;opacity:1;}
    .inv-add-body .form-select option{background:#1D283A;color:#fff;}
    .inv-add-body .form-control:disabled,.inv-add-body .form-select:disabled{background:rgba(29,40,58,.6);color:#94A3B8;border-color:#334155;}
    .inv-add-body .text-muted{color:#94A3B8!important;}
    .inv-add-footer{display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;padding:16px 28px 24px;border-top:1px solid #1E293B;}
    .inv-add-clear{background:transparent;border:1px solid rgba(239,68,68,.4);color:#F87171;border-radius:8px;padding:10px 20px;font-weight:600;font-size:.82rem;transition:.15s;}
    .inv-add-clear:hover{background:rgba(239,68,68,.15);color:#FCA5A5;border-color:rgba(239,68,68,.6);}
    .inv-add-save{background:#059669;border:none;color:#fff;border-radius:8px;padding:10px 26px;font-weight:600;font-size:.82rem;transition:.2s;}
    .inv-add-save:hover{background:#047857;color:#fff;box-shadow:0 0 18px rgba(5,150,105,.4);}
</style>

<div class="inv-add-wrap">
    <div class="inv-add-card">
        <div class="inv-add-header">
            <h2 class="inv-add-title"><i class="bi bi-box-seam-fill me-2 inv-add-icon"></i> Register New Inventory Asset</h2>
            <a href="{{ route('inventory.index') }}" class="inv-add-back"><i class="bi bi-arrow-left me-1"></i> Return to Inventory</a>
        </div>

        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="inv-add-body">
                <div class="row g-4">

                    <div class="col-md-6">
                        <label class="inv-add-label">Part Name / Item Description <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g., Engine Oil, Brake Pad" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="inv-add-label">Brand <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror"
                               value="{{ old('brand') }}" placeholder="e.g., Honda, Yamaha" required>
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="inv-add-label">Type / Category <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="type" class="form-control @error('type') is-invalid @enderror"
                               value="{{ old('type') }}" placeholder="e.g., Accessories, Lubricants" list="categoryList" required>
                        <datalist id="categoryList">
                            @php $categories = \App\Models\CategorySize::distinct()->orderBy('category')->pluck('category'); @endphp
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="inv-add-label">Size <span class="text-muted">(e.g., volume/viscosity/dimensions)</span></label>
                        <input type="text" name="size" id="size" class="form-control @error('size') is-invalid @enderror"
                               value="{{ old('size') }}" placeholder="e.g., 1L, 400mL, 17 inch" list="sizeList" autocomplete="off">
                        <datalist id="sizeList"></datalist>
                        @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="inv-add-label">Color / Shade <span class="text-muted">(Spray Paint)</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="color" id="color" class="form-control"
                                   value="{{ old('color') }}" placeholder="e.g., Red, Gloss Black" autocomplete="off">
                            <span id="color-swatch" style="width:32px;height:32px;border-radius:6px;background:#334155;border:1px solid rgba(255,255,255,0.2);flex-shrink:0;"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="inv-add-label">Stock Quantity Level <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity', 0) }}" min="0" placeholder="Estimated initial stock" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="inv-add-label">Price (₱) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price') }}" placeholder="Estimated retail price" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="inv-add-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Additional details about the product">{{ old('description') }}</textarea>
                    </div>

                </div>
            </div>

            <div class="inv-add-footer">
                <a href="{{ route('inventory.index') }}" class="inv-add-clear">Cancel</a>
                <button type="submit" class="inv-add-save"><i class="bi bi-plus-lg me-1"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInput = document.querySelector('[name="type"]');
    const sizeInput = document.getElementById('size');
    const sizeList  = document.getElementById('sizeList');

    function loadSizes() {
        const category = typeInput.value.trim();
        sizeList.innerHTML = '';
        if (!category) return;
        fetch('/category-sizes/' + encodeURIComponent(category))
            .then(function(r){ return r.json(); })
            .then(function(sizes){
                sizeList.innerHTML = '';
                sizes.forEach(function(s){
                    var opt = document.createElement('option');
                    opt.value = s;
                    sizeList.appendChild(opt);
                });
            })
            .catch(function(){});
    }

    typeInput.addEventListener('change', loadSizes);
    typeInput.addEventListener('blur', function(){ setTimeout(loadSizes, 200); });

    var colorInput  = document.getElementById('color');
    var colorSwatch = document.getElementById('color-swatch');
    var COLOR_MAP = {
        red:'#ef4444',blue:'#3b82f6',black:'#111827',white:'#f8fafc',
        green:'#22c55e',yellow:'#eab308',orange:'#f97316',purple:'#a855f7',
        pink:'#ec4899',gray:'#6b7280',grey:'#6b7280',silver:'#cbd5e1',
        brown:'#92400e',gold:'#eab308',maroon:'#7f1d1d',navy:'#1e3a8a',
        chrome:'#d1d5db','gloss black':'#111827','matte black':'#1f2937',
        'metallic blue':'#2563eb'
    };
    function swatchHex(v){
        var s = (v||'').trim().toLowerCase();
        if (!s) return '#334155';
        for (var k in COLOR_MAP) { if (s.indexOf(k)!==-1) return COLOR_MAP[k]; }
        return '#334155';
    }
    colorInput.addEventListener('input', function(){
        colorSwatch.style.background = swatchHex(colorInput.value);
    });
});
</script>
@endsection