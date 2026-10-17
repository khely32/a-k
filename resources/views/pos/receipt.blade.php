@php
    $subtotal = $sale->items->sum(fn ($item) => $item->subtotal);
    $grandTotal = round((float) $sale->total_amount, 2);
    $vat = round($grandTotal - $subtotal, 2);
    $cashier = \App\Support\DesignatedCashier::forBranch($sale->branch) ?? ($sale->user->name ?? 'N/A');
    $branchName = $sale->branch->branch_name ?? 'A&K Parts';
    $branchAddress = $sale->branch->location ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }} &middot; A&K PARTS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --paper:#ffffff;
            --ink:#111318;
            --ink-soft:#4b5563;
            --ink-faint:#9ca3af;
            --brand-green:#059669;
            --brand-green-bright:#00e676;
            --brand-red:#ff2e55;
            --line:#e5e7eb;
        }
        *{box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        html,body{margin:0;padding:0}
        body{
            font-family:'Inter',system-ui,-apple-system,sans-serif;
            background:#0d1117;
            color:var(--ink);
            min-height:100vh;
            display:flex;
            align-items:flex-start;
            justify-content:center;
            padding:84px 16px 40px;
        }

        /* ---------- on-screen toolbar (never printed) ---------- */
        .receipt-toolbar{
            position:fixed;
            top:0;left:0;right:0;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding:14px 22px;
            background:rgba(13,17,23,.82);
            backdrop-filter:blur(14px);
            border-bottom:1px solid rgba(0,230,118,.18);
            z-index:50;
        }
        .receipt-toolbar .tb-brand{
            display:flex;align-items:center;gap:9px;
            font-weight:800;letter-spacing:1px;color:var(--brand-green-bright);
            text-shadow:0 0 14px rgba(0,230,118,.35);
            font-size:.9rem;
        }
        .receipt-toolbar .tb-brand svg{width:24px;height:24px}
        .tb-actions{display:flex;align-items:center;gap:10px}
        .tb-btn{
            display:inline-flex;align-items:center;gap:8px;
            font-family:inherit;font-size:.82rem;font-weight:700;
            padding:9px 18px;border-radius:10px;cursor:pointer;text-decoration:none;
            border:none;transition:transform .15s ease,box-shadow .15s ease,background .15s ease;
        }
        .tb-btn:active{transform:scale(.97)}
        .tb-print{
            background:linear-gradient(135deg,#22c55e,#16a34a);
            color:#fff;box-shadow:0 8px 20px rgba(34,197,94,.35);
        }
        .tb-print:hover{transform:translateY(-1px);box-shadow:0 10px 26px rgba(34,197,94,.55)}
        .tb-back{
            background:rgba(255,255,255,.06);
            color:#dfe6f0;border:1px solid rgba(255,255,255,.14);
        }
        .tb-back:hover{background:rgba(255,46,85,.16);border-color:rgba(255,46,85,.5);color:#fff}

        /* ---------- receipt paper ---------- */
        .receipt{
            width:360px;
            max-width:100%;
            background:var(--paper);
            border-radius:16px;
            padding:26px 24px 22px;
            box-shadow:0 24px 60px rgba(0,0,0,.6), 0 0 0 1px rgba(0,230,118,.12), 0 0 44px rgba(0,230,118,.08);
            position:relative;
            overflow:hidden;
        }
        .receipt::before{
            content:"";
            position:absolute;top:0;left:0;right:0;height:5px;
            background:linear-gradient(90deg,var(--brand-red),var(--brand-green-bright));
        }

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
        .r-branch{
            margin-top:12px;font-size:.8rem;font-weight:700;color:var(--ink);
        }
        .r-loc{font-size:.7rem;color:var(--ink-soft);margin-top:2px}

        .r-rule{
            border:0;border-top:1px dashed var(--line);
            margin:16px 0;
        }
        .r-rule.solid{border-top-style:solid}

        .r-meta{display:flex;flex-direction:column;gap:6px;font-size:.75rem}
        .r-meta .row{display:flex;justify-content:space-between;gap:12px}
        .r-meta .k{color:var(--ink-faint);font-weight:600;text-transform:uppercase;letter-spacing:.6px;font-size:.65rem}
        .r-meta .v{color:var(--ink);font-weight:600;text-align:right}
        .r-meta .v.mono{font-family:'JetBrains Mono',monospace}

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
        .ri-name{font-weight:700;line-height:1.25;font-family:'JetBrains Mono',monospace;font-size:.72rem}
        .ri-sku{color:var(--ink-faint);font-size:.62rem;margin-top:2px;letter-spacing:.3px}
        .ri-qty{font-family:'JetBrains Mono',monospace;color:var(--ink-soft);text-align:right;white-space:nowrap}
        .ri-price,.ri-sub{font-family:'JetBrains Mono',monospace;white-space:nowrap}

        .r-totals{margin-top:14px}
        .r-totals .row{
            display:flex;justify-content:space-between;align-items:center;
            font-size:.78rem;color:var(--ink-soft);padding:4px 0;
        }
        .r-totals .row .amt{font-family:'JetBrains Mono',monospace;color:var(--ink);font-weight:600}
        .r-totals .grand{
            margin-top:8px;padding-top:12px;border-top:2px solid var(--ink);
            font-size:.95rem;font-weight:800;color:var(--ink);text-transform:uppercase;letter-spacing:.5px;
        }
        .r-totals .grand .amt{
            font-size:1.22rem;font-weight:800;color:var(--brand-green);
        }

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

        @media print{
            @page{size:80mm auto;margin:4mm}
            body{background:#fff;padding:0;display:block;min-height:0}
            .receipt-toolbar{display:none!important}
            .receipt{
                width:auto;max-width:none;border-radius:0;padding:0;margin:0;
                box-shadow:none;overflow:visible;
            }
            .receipt::before{display:none}
            .r-name{color:#000}
        }
    </style>
</head>
<body>

    <div class="receipt-toolbar">
        <span class="tb-brand">
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="11" stroke="#00E676" stroke-width="2"/>
                <circle cx="18" cy="18" r="3.5" stroke="#00E676" stroke-width="2"/>
                <path d="M18 5V9M18 27V31M5 18H9M27 18H31" stroke="#00E676" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="18" cy="18" r="1.5" fill="#00E676"/>
                <path d="M30 26l10 10" stroke="#00E676" stroke-width="2" stroke-linecap="round"/>
                <path d="M28 24c-3.2-.2-6 .9-6 4.1s2.8 4.3 6 4.1c3.2.2 6-.9 6-4.1 0-2.3-1.4-3.4-3.2-3.8z" stroke="#00E676" stroke-width="1.6" fill="none"/>
            </svg>
            A&K PARTS
        </span>
        <span class="tb-actions">
            <a href="{{ route('pos.index') }}" class="tb-btn tb-back">New Sale</a>
            <button type="button" class="tb-btn tb-print" onclick="window.print()">Print Receipt</button>
        </span>
    </div>

    <div class="receipt">
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

    <script>
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
