@php
    $cashier = \App\Support\DesignatedCashier::forBranch($sale->branch) ?? ($sale->user->name ?? 'N/A');
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
            --brand-green-bright:#00e676;
            --brand-red:#ff2e55;
        }
        html,body{margin:0;padding:0}
        body{
            font-family:'Inter',system-ui,-apple-system,sans-serif;
            background:#0d1117;
            min-height:100vh;
            display:flex;
            align-items:flex-start;
            justify-content:center;
            padding:84px 16px 40px;
        }

        /* on-screen toolbar (never printed) */
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

        @media print{
            @page{size:80mm auto;margin:4mm}
            body{background:#fff;padding:0;display:block;min-height:0}
            .receipt-toolbar{display:none!important}
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

    @include('pos.partials.receipt-card', ['sale' => $sale])

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
