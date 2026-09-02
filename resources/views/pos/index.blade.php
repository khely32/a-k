@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="pos-page">

    <!-- Subtle blue/purple atmospheric blooms -->
    <div class="pos-bloom pos-bloom-1" aria-hidden="true"></div>
    <div class="pos-bloom pos-bloom-2" aria-hidden="true"></div>

    <!-- Page Header -->
    <div class="pos-header">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-cash-register me-2" style="color:var(--green);"></i>POS Transaction Desk
        </h2>
        <p class="mb-0" style="font-size:0.9rem;color:#FFFFFF;">
            <span style="color:#9CA3AF;">Cashier:</span> <span style="color:var(--accent);font-weight:700;">{{ auth()->user()->name }}</span>
            <span style="color:#9CA3AF;">&nbsp;|&nbsp;</span>
            <span style="color:#9CA3AF;">Branch:</span> <span style="color:var(--accent);font-weight:700;">{{ auth()->user()->branchLabel() }}</span>
        </p>
    </div>

    <!-- ============ THREE COLUMN COMMAND CENTER ============ -->
    <div class="pos-grid">

        <!-- COLUMN 1 :: PRODUCT FEED -->
        <section class="pos-panel pos-feed">
            <div class="pos-panel-head feed-head">
                <span class="head-icon"><i class="bi bi-search"></i></span>
                <h3>Search &amp; Select Products</h3>
            </div>

            <div class="pos-search-wrap">
                <i class="bi bi-search search-glyph"></i>
                <input type="text" id="pos-search" placeholder="Search by item name, SKU, serial, or brand..." autocomplete="off">
            </div>

            <div class="pos-filter">
                <select id="pos-category" class="neo-select">
                    <option value="all">All Categories</option>
                </select>
            </div>

            <div id="product-list" class="pos-product-list"></div>
        </section>

        <!-- RIGHT STACK :: CUSTOMER CART (top) + TRANSACTION CONSOLE (below) -->
        <div class="pos-stack">

        <!-- CUSTOMER CART -->
        <section class="pos-panel pos-cart-panel">
            <div class="pos-panel-head cart-head">
                <span class="head-icon"><i class="bi bi-cart3"></i></span>
                <h3>Customer Cart</h3>
                <span id="cart-count" class="cart-count">0</span>
            </div>

            <div class="cart-table-head">
                <span>Item / SKU</span>
                <span class="text-center">Qty</span>
                <span class="text-end">Price</span>
                <span class="text-end">Subtotal</span>
                <span></span>
            </div>

            <div id="cart-items" class="cart-items"></div>
        </section>

        <!-- TRANSACTION CONSOLE (directly below the cart) -->
        <section class="pos-panel pos-console">
            <div class="pos-panel-head console-head">
                <span class="head-icon"><i class="bi bi-credit-card-2-front"></i></span>
                <h3>Transaction Console</h3>
            </div>

            <div class="console-body">

                <!-- Totals Panel -->
                <div class="totals-panel">
                    <div class="t-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">₱0.00</span>
                    </div>
                    <div class="t-row">
                        <span>Tax (12%):</span>
                        <span id="tax-amount">₱0.00</span>
                    </div>
                    <div class="t-row t-grand">
                        <span>GRAND TOTAL:</span>
                        <span id="cart-total">₱0.00</span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="console-field">
                    <label for="payment-method">Payment Method</label>
                    <select id="payment-method" class="neo-select">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                    </select>
                </div>

                <!-- GCash QR (compact) -->
                <div id="gcash-qr-section" class="gcash-box">
                    <div style="font-size:1.8rem;">📱</div>
                    <div class="fw-bold" style="color:#39ff14;">Scan to Pay via GCash</div>
                    <div id="gcash-qr-placeholder"
                         style="width:120px;height:120px;margin:10px auto;border:2px solid rgba(0,229,255,.3);border-radius:10px;background:white;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#0f172a;">
                        <div style="font-size:1.6rem;">📱</div>
                        <div style="font-size:0.7rem;font-weight:700;">GCash QR</div>
                    </div>
                </div>

                <!-- Actions -->
                <button id="checkout-btn" class="console-btn btn-green">
                    <i class="bi bi-check2-circle"></i> Finalize Order
                </button>
                <button id="clear-cart" class="console-btn btn-red">
                    <i class="bi bi-x-circle"></i> Void Cart
                </button>

            </div>
        </section>

        </div><!-- /.pos-stack -->

    </div>
</div>

<style>
    .pos-page{
        position:relative;
        min-height:calc(100vh - 60px);
    }
    .pos-bloom{
        position:fixed;
        border-radius:50%;
        filter:blur(90px);
        pointer-events:none;
        z-index:0;
        opacity:.5;
    }
    .pos-bloom-1{ width:430px; height:430px; left:-150px; bottom:-130px; background:radial-gradient(circle, rgba(59,130,246,.30), transparent 70%); }
    .pos-bloom-2{ width:390px; height:390px; right:-130px; top:-110px; background:radial-gradient(circle, rgba(157,78,221,.30), transparent 70%); }

    .pos-header{
        position:relative;
        z-index:1;
        margin-bottom:22px;
        padding-bottom:14px;
        border-bottom:1px solid rgba(239,68,68,.18);
    }

    /* ---------- GRID ---------- */
    .pos-grid{
        position:relative;
        z-index:1;
        display:grid;
        grid-template-columns:minmax(340px,1.15fr) minmax(360px,1fr);
        gap:28px;
        align-items:start;
    }
    .pos-stack{
        display:flex;
        flex-direction:column;
        gap:20px;
        min-width:0;
    }
    @media (max-width:1200px){
        .pos-grid{ grid-template-columns:1fr; }
        .pos-feed{ height:auto; }
        .pos-feed .pos-product-list{ max-height:380px; }
    }

    /* ---------- PANELS ---------- */
    .pos-panel{
        display:flex;
        flex-direction:column;
        background:linear-gradient(160deg,#171a20,#0e1013);
        border-radius:22px;
        border:1px solid rgba(0,242,254,.16);
        box-shadow:14px 14px 30px rgba(0,0,0,.55), -8px -8px 22px rgba(255,255,255,.025), inset 0 1px 0 rgba(255,255,255,.05);
        overflow:hidden;
    }
    .pos-panel-head{
        display:flex;
        align-items:center;
        gap:12px;
        padding:18px 20px 14px;
        border-bottom:1px solid rgba(255,255,255,.06);
    }
    .pos-panel-head h3{
        margin:0;
        font-size:1rem;
        font-weight:800;
        letter-spacing:1px;
        text-transform:uppercase;
        color:#fff;
    }
    .head-icon{
        width:38px;
        height:38px;
        border-radius:12px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.05rem;
        transition:box-shadow .2s ease, transform .2s ease;
    }
    .head-icon:hover{
        transform:scale(1.08);
    }

    /* Feed = cyan, Cart = red, Console = green */
    .pos-feed      { border-color:rgba(0,242,254,.22); height:calc(100vh - 150px); }
    .pos-cart-panel{ border-color:rgba(239,68,68,.32); }
    .pos-console   { border-color:rgba(57,255,20,.24); }
    .feed-head .head-icon    { background:rgba(0,242,254,.13);  color:#00f2fe; }
    .feed-head .head-icon:hover    { box-shadow:0 0 14px rgba(0,242,254,.8), 0 0 30px rgba(0,242,254,.25); }
    .cart-head .head-icon    { background:rgba(239,68,68,.14); color:#ef4444; }
    .cart-head .head-icon:hover    { box-shadow:0 0 14px rgba(239,68,68,.8), 0 0 30px rgba(239,68,68,.25); }
    .console-head .head-icon { background:rgba(57,255,20,.13); color:#39ff14; }
    .console-head .head-icon:hover { box-shadow:0 0 14px rgba(57,255,20,.8), 0 0 30px rgba(57,255,20,.25); }

    .cart-count{
        margin-left:auto;
        background:linear-gradient(135deg,#ef4444,#b91c1c);
        color:#fff;
        font-size:.72rem;
        font-weight:800;
        padding:3px 10px;
        border-radius:999px;
        box-shadow:0 0 12px rgba(239,68,68,.5);
    }

    /* ---------- FEED ---------- */
    .pos-search-wrap{
        position:relative;
        padding:16px 18px 6px;
    }
    .search-glyph{
        position:absolute;
        left:34px;
        top:31px;
        color:#00f2fe;
        font-size:1.05rem;
        z-index:2;
        filter:drop-shadow(0 0 8px rgba(0,242,254,.7));
    }
    #pos-search{
        width:100%;
        background:#0b0d10;
        color:#fff;
        border:1px solid rgba(0,242,254,.25);
        border-radius:14px;
        padding:13px 14px 13px 42px;
        font-size:.95rem;
        outline:none;
        transition:border-color .2s ease, box-shadow .2s ease;
    }
    #pos-search::placeholder{ color:#9CA3AF; opacity:1; }
    #pos-search:focus{
        border-color:#00f2fe;
        box-shadow:0 0 0 3px rgba(0,242,254,.15), 0 0 18px rgba(0,242,254,.25);
    }
    .pos-filter{ padding:8px 18px 10px; }
    .neo-select{
        width:100%;
        background:#0b0d10;
        color:#dfe6f0;
        border:1px solid rgba(255,255,255,.12);
        border-radius:12px;
        padding:9px 12px;
        font-size:.85rem;
        outline:none;
        transition:border-color .2s ease, box-shadow .2s ease;
    }
    .neo-select:focus{
        border-color:rgba(0,242,254,.5);
        box-shadow:0 0 0 3px rgba(0,242,254,.12);
    }

    .pos-product-list{
        flex:1;
        overflow-y:auto;
        padding:6px 14px 18px;
        display:flex;
        flex-direction:column;
        gap:10px;
        min-height:0;
    }
    .product-row{
        display:flex;
        align-items:center;
        gap:12px;
        padding:12px;
        background:rgba(255,255,255,.02);
        border:1px solid rgba(255,255,255,.07);
        border-radius:16px;
        transition:transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .product-row:hover{
        background:rgba(0,242,254,.05);
        border-color:rgba(0,242,254,.35);
        transform:translateY(-1px);
        box-shadow:0 8px 20px rgba(0,0,0,.35);
    }
    .thumb{
        width:52px;
        height:52px;
        flex-shrink:0;
        border-radius:13px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:900;
        font-size:1.3rem;
        color:#fff;
        box-shadow:inset 0 -8px 16px rgba(0,0,0,.25), 0 4px 10px rgba(0,0,0,.3);
    }
    .p-info{ flex:1; min-width:0; }
    .p-name{
        font-weight:700;
        color:#fff;
        font-size:.92rem;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }
    .p-sku{ font-size:.72rem; color:#8b98a8; letter-spacing:.4px; }
    .p-meta{ font-size:.72rem; color:#5f6d7d; }
    .p-right{ text-align:right; flex-shrink:0; }
    .p-price{
        font-weight:800;
        color:#39ff14;
        text-shadow:0 0 12px rgba(57,255,20,.35);
        margin-bottom:6px;
        white-space:nowrap;
    }
    .add-btn{
        width:34px;
        height:34px;
        border-radius:11px;
        border:none;
        background:linear-gradient(135deg,#22c55e,#15803d);
        color:#fff;
        font-size:1.25rem;
        font-weight:900;
        line-height:1;
        cursor:pointer;
        transition:transform .18s ease, box-shadow .18s ease;
        box-shadow:0 4px 12px rgba(34,197,94,.4);
    }
    .add-btn:hover{
        transform:scale(1.12);
        box-shadow:0 6px 18px rgba(34,197,94,.6);
    }
    .add-btn.pulse{ animation:pulse .5s ease; }
    @keyframes pulse{
        0%{ transform:scale(1); }
        40%{ transform:scale(1.25); }
        100%{ transform:scale(1); }
    }

    .feed-empty{
        text-align:center;
        color:#5f6d7d;
        padding:44px 0;
    }

    /* ---------- CART ---------- */
    .cart-table-head{
        display:grid;
        grid-template-columns:1fr 86px 74px 92px 30px;
        gap:6px;
        padding:10px 16px;
        font-size:.68rem;
        letter-spacing:1px;
        text-transform:uppercase;
        color:#8b98a8;
        border-bottom:1px solid rgba(239,68,68,.15);
    }
    .cart-items{
        flex:1 1 auto;
        overflow-y:auto;
        padding:8px 12px 14px;
        min-height:0;
        max-height:300px;
    }
    .cart-row{
        display:grid;
        grid-template-columns:1fr 86px 74px 92px 30px;
        gap:6px;
        align-items:center;
        padding:12px 8px;
        border-bottom:1px solid rgba(255,255,255,.06);
    }
    .cart-row:last-child{ border-bottom:none; }
    .c-item{ min-width:0; }
    .c-name{
        font-weight:700;
        font-size:.9rem;
        color:#fff;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }
    .c-sku{ font-size:.7rem; color:#8b98a8; }
    .c-qty{ display:flex; align-items:center; justify-content:center; gap:6px; }
    .q-btn{
        width:24px;
        height:24px;
        border-radius:8px;
        border:1px solid rgba(0,242,254,.35);
        background:rgba(0,242,254,.08);
        color:#00f2fe;
        font-weight:900;
        line-height:1;
        cursor:pointer;
        transition:background .15s ease, transform .15s ease;
    }
    .q-btn:hover{ background:rgba(0,242,254,.25); transform:scale(1.1); }
    .q-val{ min-width:20px; text-align:center; font-weight:700; color:#fff; font-size:.85rem; }
    .c-price{ text-align:right; font-size:.82rem; color:#aab6c4; white-space:nowrap; }
    .c-sub{ text-align:right; font-weight:800; color:#fff; font-size:.85rem; white-space:nowrap; }
    .c-x{
        width:26px;
        height:26px;
        border-radius:9px;
        border:none;
        background:rgba(239,68,68,.12);
        color:#ef4444;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        transition:background .15s ease, transform .15s ease;
    }
    .c-x:hover{ background:rgba(239,68,68,.32); transform:scale(1.1); }
    .cart-empty{
        text-align:center;
        color:#5f6d7d;
        padding:46px 0;
    }

    /* ---------- CONSOLE ---------- */
    .console-body{
        display:flex;
        flex-direction:column;
        gap:16px;
        padding:18px;
        flex:1;
    }
    .console-field label{
        display:block;
        font-size:.72rem;
        letter-spacing:1.2px;
        text-transform:uppercase;
        color:#8b98a8;
        margin-bottom:8px;
        font-weight:700;
    }
    .console-input{
        width:100%;
        background:#0b0d10;
        color:#fff;
        border:1px solid rgba(255,255,255,.12);
        border-radius:13px;
        padding:11px 13px;
        font-size:.9rem;
        outline:none;
        transition:border-color .2s ease, box-shadow .2s ease;
    }
    .console-input::placeholder{ color:#6b7886; }
    .console-input:focus{
        border-color:rgba(0,242,254,.5);
        box-shadow:0 0 0 3px rgba(0,242,254,.12);
    }

    .totals-panel{
        background:linear-gradient(145deg,#1d2126,#15181d);
        border:1px solid rgba(57,255,20,.22);
        border-radius:18px;
        padding:18px;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.05), 0 8px 24px rgba(0,0,0,.4);
    }
    .t-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:6px 0;
        font-size:.88rem;
        color:#cfd8e3;
    }
    .t-grand{
        border-top:1px dashed rgba(57,255,20,.3);
        margin-top:8px;
        padding-top:12px;
        font-size:1.15rem;
        font-weight:800;
    }
    .t-grand span:last-child{
        font-size:1.45rem;
        color:#39ff14;
        text-shadow:0 0 16px rgba(57,255,20,.5);
    }

    .console-btn{
        width:100%;
        border:none;
        border-radius:14px;
        padding:14px;
        font-size:.95rem;
        font-weight:800;
        letter-spacing:1px;
        color:#fff;
        cursor:pointer;
        transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:8px;
    }
    .btn-green{
        background:linear-gradient(135deg,#22c55e,#16a34a);
        box-shadow:0 8px 20px rgba(34,197,94,.35);
    }
    .btn-green:hover{
        transform:translateY(-1px);
        box-shadow:0 10px 26px rgba(34,197,94,.55);
    }
    .btn-red{
        background:linear-gradient(135deg,#ef4444,#b91c1c);
        box-shadow:0 8px 20px rgba(239,68,68,.3);
    }
    .btn-red:hover{
        transform:translateY(-1px);
        box-shadow:0 10px 26px rgba(239,68,68,.5);
    }
    .console-btn:active{ transform:scale(.98); }

    .gcash-box{
        display:none;
        text-align:center;
        padding:12px;
        border:1px dashed rgba(0,242,254,.35);
        border-radius:14px;
        background:rgba(0,242,254,.04);
    }
    .gcash-box.show{ display:block; }

    /* ---------- SCROLLBARS ---------- */
    .pos-product-list::-webkit-scrollbar,
    .cart-items::-webkit-scrollbar{ width:6px; }
    .pos-product-list::-webkit-scrollbar-track,
    .cart-items::-webkit-scrollbar-track{ background:transparent; }
    .pos-product-list::-webkit-scrollbar-thumb,
    .cart-items::-webkit-scrollbar-thumb{ background:rgba(0,242,254,.25); border-radius:6px; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const TAX_RATE = 0.12;
    const $ = id => document.getElementById(id);

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const money = n => '₱' + parseFloat(n).toFixed(2);

    const THUMB_COLORS = [
        ['#f59e0b','#92400e'], ['#22c55e','#14532d'], ['#06b6d4','#164e63'],
        ['#a855f7','#581c87'], ['#f43f5e','#881337'], ['#3b82f6','#1e3a8a'],
        ['#ec4899','#831843']
    ];
    function colorFor(key){
        let h = 0;
        for (let i = 0; i < key.length; i++) h = (h * 31 + key.charCodeAt(i)) >>> 0;
        return THUMB_COLORS[h % THUMB_COLORS.length];
    }

    /* ---------------- CATEGORY FILTER ---------------- */
    function loadCategories(){
        return fetch('{{ route("pos.categories") }}')
            .then(r => r.json())
            .then(cats => {
                const sel = $('pos-category');
                sel.innerHTML = '<option value="all">All Categories</option>';
                cats.forEach(c => {
                    sel.insertAdjacentHTML('beforeend', `<option value="${esc(c)}">${esc(c)}</option>`);
                });
            });
    }

    /* ---------------- PRODUCT FEED ---------------- */
    function productRow(p){
        const [c1, c2] = colorFor(p.type || p.brand || p.part_name);
        const letter = (p.part_name || '?').charAt(0).toUpperCase();
        return `
            <div class="product-row">
                <div class="thumb" style="background:linear-gradient(135deg,${c1},${c2});">${esc(letter)}</div>
                <div class="p-info">
                    <div class="p-name">${esc(p.part_name)}</div>
                    <div class="p-sku">SKU: ${esc(p.item_code)}</div>
                    <div class="p-meta">${esc(p.brand || 'No Brand')} &middot; ${esc(p.type || 'Part')} &middot; Stock: ${p.stock_level}</div>
                </div>
                <div class="p-right">
                    <div class="p-price">${money(p.price)}</div>
                    <button class="add-btn add-cart" data-id="${p.id}" title="Add to cart">+</button>
                </div>
            </div>`;
    }

    function loadProducts(){
        const search = encodeURIComponent($('pos-search').value);
        const category = encodeURIComponent($('pos-category').value);
        fetch(`/pos/search?search=${search}&category=${category}`)
            .then(r => r.json())
            .then(products => {
                let html;
                if (products.length === 0) {
                    html = `<div class="feed-empty"><i class="bi bi-search" style="font-size:1.8rem;"></i><div style="margin-top:8px;">No products found</div></div>`;
                } else {
                    html = products.map(productRow).join('');
                }
                $('product-list').innerHTML = html;
            });
    }

    /* ---------------- CART ---------------- */
    function loadCart(){
        fetch('{{ route("pos.getCart") }}')
            .then(r => r.json())
            .then(cart => {
                const keys = Object.keys(cart);
                const totalQty = keys.reduce((s,k) => s + (cart[k].qty || 0), 0);
                $('cart-count').textContent = totalQty;

                let subtotal = 0;
                let html;
                if (keys.length === 0) {
                    html = `<div class="cart-empty"><i class="bi bi-cart-x" style="font-size:2rem;"></i><div style="margin-top:8px;">Cart is empty</div></div>`;
                } else {
                    html = keys.map(id => {
                        const it = cart[id];
                        const sub = it.qty * it.price;
                        subtotal += sub;
                        return `
                            <div class="cart-row">
                                <div class="c-item">
                                    <div class="c-name">${esc(it.name)}</div>
                                    <div class="c-sku">${esc(it.sku || 'No SKU')}</div>
                                </div>
                                <div class="c-qty">
                                    <button class="q-btn q-dec" data-id="${id}" title="Decrease">&minus;</button>
                                    <span class="q-val">${it.qty}</span>
                                    <button class="q-btn q-inc" data-id="${id}" title="Increase">+</button>
                                </div>
                                <div class="c-price">${money(it.price)}</div>
                                <div class="c-sub">${money(sub)}</div>
                                <button class="c-x remove-item" data-id="${id}" title="Remove"><i class="bi bi-x-lg"></i></button>
                            </div>`;
                    }).join('');
                }
                $('cart-items').innerHTML = html;

                const tax = subtotal * TAX_RATE;
                $('subtotal').textContent = money(subtotal);
                $('tax-amount').textContent = money(tax);
                $('cart-total').textContent = money(subtotal + tax);
            });
    }

    function setQty(id, qty){
        fetch('{{ route("pos.updateQty") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ id, qty })
        })
        .then(r => r.json())
        .then(() => loadCart());
    }

    /* ---------------- DELEGATED CLICKS ---------------- */
    document.addEventListener('click', function (e) {
        const add = e.target.closest('.add-cart');
        if (add) {
            const btn = add;
            btn.classList.remove('pulse');
            void btn.offsetWidth;
            btn.classList.add('pulse');
            fetch('{{ route("pos.addToCart") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ id: btn.dataset.id })
            })
            .then(r => r.json())
            .then(() => loadCart())
            .catch(err => { console.error('Add to cart failed:', err); });
            return;
        }

        const inc = e.target.closest('.q-inc');
        if (inc) {
            const val = parseInt(inc.closest('.cart-row').querySelector('.q-val').textContent, 10);
            setQty(inc.dataset.id, val + 1);
            return;
        }

        const dec = e.target.closest('.q-dec');
        if (dec) {
            const val = parseInt(dec.closest('.cart-row').querySelector('.q-val').textContent, 10);
            setQty(dec.dataset.id, val - 1);
            return;
        }

        const rm = e.target.closest('.remove-item');
        if (rm) {
            fetch('{{ route("pos.removeFromCart") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ id: rm.dataset.id })
            })
            .then(r => r.json())
            .then(() => loadCart());
        }
    });

    /* ---------------- PAYMENT METHOD / QR ---------------- */
    const paymentSelect = $('payment-method');
    const qrSection = $('gcash-qr-section');
    function toggleGcashQr(){
        qrSection.classList.toggle('show', paymentSelect.value === 'gcash');
    }
    paymentSelect.addEventListener('change', toggleGcashQr);
    toggleGcashQr();

    /* ---------------- SEARCH / FILTER ---------------- */
    let searchTimer;
    $('pos-search').addEventListener('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadProducts, 250);
    });
    $('pos-category').addEventListener('change', loadProducts);

    /* ---------------- ACTIONS ---------------- */
    $('clear-cart').addEventListener('click', function () {
        fetch('{{ route("pos.clearCart") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(() => loadCart());
    });

    $('checkout-btn').addEventListener('click', function () {
        const paymentMethod = paymentSelect.value;
        const customerIdEl = $('customer-id');
        const customerId = customerIdEl ? customerIdEl.value.trim() : '';
        fetch('{{ route("pos.checkout") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ payment_method: paymentMethod, customer_id: customerId })
        })
        .then(r => r.json().then(d => ({ ok: r.ok, d })))
        .then(({ ok, d }) => {
            if (ok) {
                alert(d.success);
                if (customerIdEl) customerIdEl.value = '';
                loadCart();
                loadProducts();
            } else {
                alert(d.error);
            }
        });
    });

    /* ---------------- INIT ---------------- */
    loadCategories().then(loadProducts);
    loadCart();
});
</script>
@endsection
