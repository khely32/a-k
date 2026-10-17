@php
    $subtotal = $sale->items->sum(fn ($item) => $item->subtotal);
    $grandTotal = round((float) $sale->total_amount, 2);
    $vat = round($grandTotal - $subtotal, 2);
    $cashier = \App\Support\DesignatedCashier::forBranch($sale->branch) ?? ($sale->user->name ?? 'N/A');
    $branchName = $sale->branch->branch_name ?? 'A&K Parts';
    $branchAddress = $sale->branch->location ?? '';
@endphp
<style>
    .receipt{
        --paper:#ffffff;
        --ink:#111318;
        --ink-soft:#4b5563;
        --ink-faint:#9ca3af;
        --brand-green:#059669;
        --brand-green-bright:#00e676;
        --brand-red:#ff2e55;
        --line:#e5e7eb;
        width:360px;
        max-width:100%;
        margin:0 auto;
        background:var(--paper);
        color:var(--ink);
        border-radius:16px;
        padding:26px 24px 22px;
        box-shadow:0 24px 60px rgba(0,0,0,.6), 0 0 0 1px rgba(0,230,118,.12), 0 0 44px rgba(0,230,118,.08);
        position:relative;
        overflow:hidden;
        font-family:'Inter',system-ui,-apple-system,sans-serif;
        text-align:left;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
    .receipt::before{
        content:"";
        position:absolute;top:0;left:0;right:0;height:5px;
        background:linear-gradient(90deg,var(--brand-red),var(--brand-green-bright));
    }
    .receipt *{box-sizing:border-box}

    .r-brand{text-align:center;padding-top:6px}
    .r-logo{width:52px;height:52px;margin:0 auto 8px;display:block}
    .r-name{
        font-size:1.18rem;font-weight:900;letter-spacing:2.5px;
        color:var(--brand-green);margin:0;
    }
    .r-tagline{
        font-size:.6rem;font-weight:700;letter-spacing:2.5px;
        text-transform:uppercase;color:var(--ink-faint);margin:3px 0 0;
    }
    .r-branch{margin-top:12px;font-size:.8rem;font-weight:700;color:var(--ink)}
    .r-loc{font-size:.7rem;color:var(--ink-soft);margin-top:2px}

    .r-rule{border:0;border-top:1px dashed var(--line);margin:16px 0}

    .r-meta{display:flex;flex-direction:column;gap:6px;font-size:.75rem}
    .r-meta .row{display:flex;justify-content:space-between;gap:12px}
    .r-meta .k{color:var(--ink-faint);font-weight:600;text-transform:uppercase;letter-spacing:.6px;font-size:.65rem}
    .r-meta .v{color:var(--ink);font-weight:600;text-align:right}
    .r-meta .v.mono{font-family:'JetBrains Mono',ui-monospace,monospace}

    .r-items{width:100%;border-collapse:collapse;font-size:.74rem}
    .r-items thead th{
        text-align:left;color:var(--ink-faint);font-weight:700;
        text-transform:uppercase;letter-spacing:.6px;font-size:.6rem;
        padding:0 0 8px;border-bottom:1px solid var(--line);
    }
    .r-items thead th.num,
    .r-items td.num{text-align:right;font-variant-numeric:tabular-nums}
    .r-items tbody td{padding:8px 0;border-bottom:1px dashed var(--line);vertical-align:top;color:var(--ink)}
    .r-items tbody tr:last-child td{border-bottom:none}
    .ri-name{font-weight:700;line-height:1.25;font-family:'JetBrains Mono',ui-monospace,monospace;font-size:.72rem}
    .ri-sku{color:var(--ink-faint);font-size:.62rem;margin-top:2px;letter-spacing:.3px}
    .ri-qty{font-family:'JetBrains Mono',ui-monospace,monospace;color:var(--ink-soft);text-align:right;white-space:nowrap}
    .ri-price,.ri-sub{font-family:'JetBrains Mono',ui-monospace,monospace;white-space:nowrap}

    .r-totals{margin-top:14px}
    .r-totals .row{
        display:flex;justify-content:space-between;align-items:center;
        font-size:.78rem;color:var(--ink-soft);padding:4px 0;
    }
    .r-totals .row .amt{font-family:'JetBrains Mono',ui-monospace,monospace;color:var(--ink);font-weight:600}
    .r-totals .grand{
        margin-top:8px;padding-top:12px;border-top:2px solid var(--ink);
        font-size:.95rem;font-weight:800;color:var(--ink);text-transform:uppercase;letter-spacing:.5px;
    }
    .r-totals .grand .amt{font-size:1.22rem;font-weight:800;color:var(--brand-green)}

    .r-pay{
        display:flex;justify-content:space-between;align-items:center;
        margin-top:14px;padding:9px 12px;border-radius:10px;
        background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.22);
        font-size:.72rem;
    }
    .r-pay .k{color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.6px;font-size:.62rem}
    .r-pay .v{color:var(--brand-green);font-weight:800;text-transform:uppercase;letter-spacing:.6px}

    .r-foot{text-align:center;margin-top:18px}
    .r-foot .thanks{font-size:.82rem;font-weight:800;color:var(--ink);letter-spacing:.4px}
    .r-foot .note{font-size:.64rem;color:var(--ink-faint);margin-top:5px;line-height:1.5}
    .r-foot .stamp{
        display:inline-block;margin-top:12px;padding:3px 12px;border-radius:999px;
        font-size:.56rem;font-weight:800;letter-spacing:1.6px;text-transform:uppercase;
        color:var(--brand-green);border:1px dashed rgba(5,150,105,.5);
    }

    /* Print only the receipt card, whatever page it lives on. */
    @media print{
        @page{size:80mm auto;margin:4mm}
        body *{visibility:hidden}
        #receipt-modal-content,
        #receipt-modal-content *{visibility:visible}
        #receipt-modal-content{
            position:absolute;
            left:0;
            top:0;
            width:100%;
            box-shadow:none !important;
        }
        .receipt{border-radius:0;padding:0}
        .receipt::before{display:none}
        .r-name{color:#000}
    }
</style>

<div class="receipt" id="receipt-modal-content">
    <div class="r-brand">
        <svg class="r-logo" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="18" cy="18" r="11" stroke="#059669" stroke-width="2"/>
            <circle cx="18" cy="18" r="3.5" stroke="#059669" stroke-width="2"/>
            <path d="M18 5V9M18 27V31M5 18H9M27 18H31" stroke="#059669" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="18" cy="18" r="1.5" fill="#059669"/>
            <path d="M30 26l10 10" stroke="#059669" stroke-width="2" stroke-linecap="round"/>
            <path d="M28 24c-3.2-.2-6 .9-6 4.1s2.8 4.3 6 4.1c3.2.2 6-.9 6-4.1 0-2.3-1.4-3.4-3.2-3.8z" stroke="#059669" stroke-width="1.6" fill="none"/>
        </svg>
        <h1 class="r-name">A&K PARTS</h1>
        <p class="r-tagline">Motorcycle Parts</p>
        <div class="r-branch">{{ $branchName }}</div>
        @if($branchAddress)
            <div class="r-loc">{{ $branchAddress }}</div>
        @endif
    </div>

    <hr class="r-rule">

    <div class="r-meta">
        <div class="row">
            <span class="k">Receipt No.</span>
            <span class="v mono">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row">
            <span class="k">Date</span>
            <span class="v">{{ $sale->created_at->format('M d, Y h:i A') }}</span>
        </div>
        <div class="row">
            <span class="k">Cashier</span>
            <span class="v">{{ $cashier }}</span>
        </div>
        <div class="row">
            <span class="k">Customer</span>
            <span class="v">{{ $sale->customer_id ?: 'Walk-in' }}</span>
        </div>
    </div>

    <hr class="r-rule">

    <table class="r-items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Price</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->items as $item)
                <tr>
                    <td>
                        <div class="ri-name">{{ $item->product->name ?? 'Unknown item' }}</div>
                        @if($item->product && $item->product->serial_number)
                            <div class="ri-sku">{{ $item->product->serial_number }}</div>
                        @endif
                    </td>
                    <td class="ri-qty">{{ $item->quantity }}</td>
                    <td class="ri-price num">₱{{ number_format((float) $item->price, 2) }}</td>
                    <td class="ri-sub num">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:var(--ink-faint);padding:12px 0;">No items recorded</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="r-totals">
        <div class="row">
            <span>Subtotal</span>
            <span class="amt">₱{{ number_format($subtotal, 2) }}</span>
        </div>
        <div class="row">
            <span>VAT (12%)</span>
            <span class="amt">₱{{ number_format($vat, 2) }}</span>
        </div>
        <div class="row grand">
            <span>Grand Total</span>
            <span class="amt">₱{{ number_format($grandTotal, 2) }}</span>
        </div>
    </div>

    <div class="r-pay">
        <span class="k">Payment Method</span>
        <span class="v">{{ $sale->payment_method ?? 'cash' }}</span>
    </div>

    <div class="r-foot">
        <div class="thanks">Thank you for your business!</div>
        <div class="note">
            This document serves as your official sales receipt.<br>
            Please keep it for warranty and returns.
        </div>
        <span class="stamp">A&K PARTS &middot; SYSTEM GENERATED</span>
    </div>
</div>
