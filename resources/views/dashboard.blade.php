@extends('layouts.app')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
/* ════ DASHBOARD — IMAGE_22 SPEC ════ */

/* Hero */
.hero-banner{
    background:var(--card);border:2px solid var(--cyan);border-radius:14px;
    padding:36px 24px;text-align:center;margin-bottom:20px;
    position:relative;overflow:hidden;
    box-shadow:0 0 24px rgba(0,229,255,.25),0 0 48px rgba(0,229,255,.08),inset 0 0 30px rgba(0,229,255,.04);
}
.hero-banner::before{content:'';position:absolute;top:-50%;left:-20%;width:400px;height:400px;background:radial-gradient(circle,rgba(0,229,255,.06),transparent 70%);pointer-events:none}
.hero-title{font-size:1.5rem;font-weight:900;color:var(--text);letter-spacing:2.5px;text-transform:uppercase;margin:0;line-height:1.3;text-shadow:0 0 30px rgba(0,229,255,.2)}
.hero-sub{font-size:0.78rem;color:var(--muted);margin-top:12px}
.hero-sub strong{color:var(--cyan);border-bottom:1.5px solid var(--cyan);padding-bottom:1px}

/* Card */
.dc{background:var(--card);border:1px solid var(--border2);border-radius:14px;overflow:hidden}
.dc-head{padding:12px 18px;border-bottom:1px solid var(--border2);display:flex;justify-content:space-between;align-items:center}
.dc-head h5{margin:0;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--text)}
.dc-head a{font-size:0.7rem;color:var(--cyan);text-decoration:none;font-weight:600}
.dc-head a:hover{text-decoration:underline;color:var(--cyan)}
.dc-body{padding:16px 18px}

/* KPI */
.kpi{background:var(--card);border:1px solid var(--border2);border-radius:14px;padding:18px 20px;position:relative;overflow:hidden;min-height:116px;transition:border-color .2s,box-shadow .2s}
.kpi:hover{border-color:rgba(255,255,255,.08);box-shadow:0 4px 20px rgba(0,0,0,.3)}
.kpi-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.kpi-label{font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:600}
.kpi-icon-wrap{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.92rem}
.kpi-val{font-size:1.6rem;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
.kpi-sub{font-size:0.68rem;color:var(--muted);margin-top:8px}
.kpi-sub .up{color:var(--green);font-weight:600}
.kpi-sub .wn{color:var(--yellow);font-weight:600}
.kpi-chart{position:absolute;bottom:0;left:0;right:0;height:35px}

/* Eye toggle */
.eye-toggle{cursor:pointer;user-select:none;transition:color .15s}
.eye-toggle:hover{opacity:.8}

/* Alerts split */
.alerts-split{display:flex;gap:10px}
.alert-box{flex:1;background:var(--bg);border-radius:10px;padding:12px 14px;border:1px solid var(--border2)}
.alert-box-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;margin-bottom:6px}
.alert-box-val{font-size:1.2rem;font-weight:800;line-height:1}
.alert-box-label{font-size:0.62rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-top:4px;font-weight:600}

/* Gauge */
.gauge-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;margin-top:6px}
.gauge-wrap canvas{width:90px!important;height:50px!important}
.gauge-label{font-size:0.52rem;color:var(--purple);text-transform:uppercase;letter-spacing:1.5px;font-weight:800;margin-top:2px}

/* Revenue row */
.rv-row{display:flex;align-items:center;gap:16px}
.rv-left{flex:1}
.rv-chart{width:130px;height:45px;flex-shrink:0}

/* Neon Text */
.neon-text-green{
    color:#fff;
    text-shadow:
        0 0 5px #fff,
        0 0 10px #fff,
        0 0 20px #00E676,
        0 0 40px #00E676,
        0 0 80px #00E676,
        0 0 100px #00E676,
        0 0 150px #00E5FF,
        0 0 200px #00E5FF;
}
.neon-val-green{text-shadow:0 0 8px rgba(0,230,118,.5),0 0 20px rgba(0,230,118,.2)}
.neon-val-cyan{text-shadow:0 0 8px rgba(0,229,255,.5),0 0 20px rgba(0,229,255,.2)}
.neon-val-purple{text-shadow:0 0 8px rgba(213,0,249,.5),0 0 20px rgba(213,0,249,.2)}
.neon-val-yellow{text-shadow:0 0 8px rgba(255,193,7,.5),0 0 20px rgba(255,193,7,.2)}
.neon-val-red{text-shadow:0 0 8px rgba(255,46,85,.5),0 0 20px rgba(255,46,85,.2)}

/* Top Products */
.tp-row{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04)}
.tp-row:last-child{border-bottom:none}
.tp-rank{width:22px;height:22px;border-radius:6px;background:rgba(255,46,85,.1);display:flex;align-items:center;justify-content:center;font-size:0.68rem;font-weight:700;color:var(--accent);flex-shrink:0}
.tp-rank.top{background:rgba(255,46,85,.2);color:var(--accent)}
.tp-info{flex:1;min-width:0}
.tp-name{font-size:0.8rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tp-bar-track{height:4px;background:rgba(255,46,85,.08);border-radius:2px;margin-top:4px}
.tp-bar{height:100%;border-radius:2px;transition:width .8s ease}
.tp-qty{font-size:0.78rem;font-weight:700;color:var(--text);min-width:36px;text-align:right;font-variant-numeric:tabular-nums}
.tp-val{font-size:0.75rem;font-weight:600;color:var(--green);min-width:80px;text-align:right;font-variant-numeric:tabular-nums}

/* Stock Status */
.ss-wrap{display:flex;gap:18px;align-items:center}
.ss-chart{width:140px;height:140px;position:relative;flex-shrink:0}
.ss-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
.ss-pct{font-size:1.2rem;font-weight:800;color:var(--green)}
.ss-lbl{font-size:0.58rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em}
.ss-legend{display:flex;flex-direction:column;gap:8px}
.ss-legend-item{display:flex;align-items:center;gap:7px;font-size:0.75rem;color:var(--muted)}
.ss-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0}

/* Transactions */
.tx{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04)}
.tx:last-child{border-bottom:none}
.tx-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;flex-shrink:0}
.tx-icon.sale{background:rgba(0,230,118,.1);color:var(--green)}
.tx-icon.pending{background:rgba(255,193,7,.1);color:var(--yellow)}
.tx-info{flex:1;min-width:0}
.tx-id{font-size:0.78rem;font-weight:600;color:var(--text)}
.tx-meta{font-size:0.65rem;color:var(--muted);margin-top:1px}
.tx-amt{font-weight:700;font-size:0.8rem;color:var(--green)}
.tx-status{font-size:0.58rem;font-weight:700;padding:2px 7px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px}
.tx-status.paid{background:rgba(0,230,118,.12);color:var(--green)}
.tx-status.pending{background:rgba(255,193,7,.12);color:var(--yellow)}

/* Branch Cards */
.br{background:var(--card2);border:1px solid var(--border2);border-radius:12px;padding:14px 16px;transition:.2s}
.br:hover{border-color:rgba(255,255,255,.08)}
.br.active-br{border-color:var(--accent);box-shadow:0 0 20px rgba(255,46,85,.15),inset 0 0 20px rgba(255,46,85,.05);background:rgba(255,46,85,.04)}
.br-name{font-size:0.78rem;font-weight:700;color:var(--text);margin-bottom:8px}
.br-stats{display:flex;justify-content:space-between}
.br-stat-val{font-size:0.95rem;font-weight:800;line-height:1}
.br-stat-lbl{font-size:0.58rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted);margin-top:2px;font-weight:600}

/* Promo */
.promo{background:linear-gradient(135deg,#1A0010,#0D1117);border:1px solid rgba(255,46,85,.15);border-radius:14px;padding:22px;position:relative;overflow:hidden;min-height:180px;display:flex;flex-direction:column;justify-content:center}
.promo::before{content:'';position:absolute;top:-40%;right:-20%;width:250px;height:250px;background:radial-gradient(circle,rgba(255,46,85,.1),transparent 70%);pointer-events:none}
.promo::after{content:'';position:absolute;bottom:-30px;right:-20px;width:200px;height:160px;background:radial-gradient(ellipse,rgba(255,46,85,.08),transparent 60%);pointer-events:none}
.promo-brand{font-size:0.95rem;font-weight:900;color:var(--accent);margin-bottom:4px;position:relative;z-index:1;text-shadow:0 0 14px rgba(255,46,85,.35)}
.promo-tag{font-size:0.78rem;color:var(--muted);position:relative;z-index:1;line-height:1.5}
.promo-tag strong{color:var(--text);display:block;font-size:0.9rem;margin-bottom:2px}
.promo-badge{display:inline-block;background:var(--accent);color:#fff;font-size:0.5rem;font-weight:700;padding:2px 8px;border-radius:4px;margin-top:8px;letter-spacing:0.5px}
.promo-bike{position:absolute;bottom:10px;right:10px;width:140px;height:100px;opacity:.15;pointer-events:none;z-index:0}

.mb16{margin-bottom:16px}

@media print{
    .sidebar,.no-print{display:none!important}
    .content-wrapper{margin-left:0!important;width:100%!important;padding:10px!important}
    .hero-banner{border-color:#ddd;box-shadow:none}
    .dc,.kpi,.promo,.br,.hero-banner{background:#fff!important;border:1px solid #ddd!important;color:#000!important}
    .neon-text-green{color:#000!important;text-shadow:none!important}
    .neon-val-green,.neon-val-cyan,.neon-val-purple,.neon-val-yellow,.neon-val-red{color:#000!important;text-shadow:none!important}
    .dc-head h5,.kpi-label,.tp-name,.tx-id,.br-name,.hero-title{color:#000!important}
    .tp-val,.tx-amt,.kpi-val,.ss-pct{color:#000!important}
}
</style>

<!-- ═══ HERO ═══ -->
<div class="hero-banner">
    <h1 class="hero-title">A AND K MOTORCYCLE PARTS<br>AND ACCESSORIES</h1>
    <div class="hero-sub">Welcome back, <strong>{{ auth()->user()->name ?? 'User' }}</strong> | Real-time Operations Management Center</div>
</div>

<!-- ═══ KPI ROW ═══ -->
<div class="row g-3 mb16">
    <!-- Card 1: Total Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Total Revenue</span>
                <div class="kpi-icon-wrap eye-toggle" onclick="toggleRevenue()" title="Toggle visibility">
                    <i class="bi bi-eye" id="eyeIcon" style="color:var(--green)"></i>
                </div>
            </div>
            <div class="kpi-val neon-val-green" style="color:var(--green)" id="kpi-revenue" data-raw="₱{{ number_format($stats['total_sales'], 2) }}">₱***.**</div>
            <div class="kpi-sub"><span class="up"><i class="bi bi-arrow-up-short"></i>Live total</span> &mdash; Your branch revenue summary</div>
            <div class="kpi-chart"><canvas id="revKpiChart"></canvas></div>
        </div>
    </div>
    <!-- Card 2: Active Stock Items -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Active Stock Items</span>
                <div class="kpi-icon-wrap" style="background:rgba(0,229,255,.1)"><i class="bi bi-box-seam" style="color:var(--cyan)"></i></div>
            </div>
            <div class="kpi-val neon-val-cyan" style="color:var(--cyan)" id="kpi-inventory">{{ number_format($stats['total_inventory']) }}</div>
            <div class="kpi-sub"><span class="up" style="color:var(--cyan)"><i class="bi bi-arrow-up-short"></i>Units available</span> &mdash; Unique items registered in database</div>
            <div class="kpi-chart"><canvas id="cyanKpiChart"></canvas></div>
        </div>
    </div>
    <!-- Card 3: Stock Alerts -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi" style="padding:12px 16px">
            <div class="kpi-top" style="margin-bottom:8px">
                <span class="kpi-label">Stock Alerts (All Branches)</span>
                <div class="kpi-icon-wrap" style="background:rgba(255,193,7,.1)"><i class="bi bi-exclamation-triangle" style="color:var(--yellow)"></i></div>
            </div>
            <div class="alerts-split">
                <div class="alert-box">
                    <div class="alert-box-icon" style="background:rgba(255,193,7,.1)"><i class="bi bi-exclamation-triangle" style="color:var(--yellow)"></i></div>
                    <div class="alert-box-val neon-val-yellow" style="color:var(--yellow)" id="kpi-lowstock">{{ $stats['low_stock'] }}</div>
                    <div class="alert-box-label">Low Stock</div>
                </div>
                <div class="alert-box">
                    <div class="alert-box-icon" style="background:rgba(255,46,85,.1)"><i class="bi bi-x-circle" style="color:var(--accent)"></i></div>
                    <div class="alert-box-val neon-val-red" style="color:var(--accent)">{{ $stats['out_of_stock'] }}</div>
                    <div class="alert-box-label">Out of Stock</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card 4: Total Transfers -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Total Transfers</span>
                <div class="kpi-icon-wrap" style="background:rgba(213,0,249,.1)"><i class="bi bi-arrow-left-right" style="color:var(--purple)"></i></div>
            </div>
            <div class="kpi-val neon-val-purple" style="color:var(--purple)" id="kpi-pending">{{ $stats['total_transfers'] }}</div>
            <div class="kpi-sub">@if($stats['total_transfers'] > 0)<span class="wn"><i class="bi bi-arrow-up-short"></i>Awaiting approval</span>@else<span class="up" style="color:var(--purple)"><i class="bi bi-check-circle"></i>No pending</span>@endif</div>
            <div class="gauge-wrap"><canvas id="gaugeChart"></canvas><div class="gauge-label">TRANSFERS</div></div>
        </div>
    </div>
</div>

<!-- ═══ INVENTORY VALUE ═══ -->
<div class="row g-3 mb16">
    <div class="col-12">
        <div class="dc">
            <div class="dc-body">
                <div class="rv-row">
                    <div class="rv-left">
                        <div class="kpi-label" style="margin-bottom:4px"><i class="bi bi-database" style="margin-right:3px;color:var(--cyan)"></i> Total Inventory Value</div>
                        <div class="kpi-val neon-val-cyan" style="color:var(--green);font-size:1.3rem" id="kpi-invvalue">₱{{ number_format($stats['inventory_value'], 2) }}</div>
                        <div class="kpi-sub" style="margin-top:4px"><span class="up">Quantity &times; Unit Price</span></div>
                    </div>
                    <div class="rv-chart"><canvas id="invChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ TOP PRODUCTS + STOCK STATUS + TRANSACTIONS ═══ -->
<div class="row g-3 mb16">
    <!-- Insights: Top Products -->
    <div class="col-xl-5">
        <div class="dc" style="height:100%">
            <div class="dc-head"><h5>Insights: Top Products</h5><a href="#">View All</a></div>
            <div class="dc-body" style="padding:8px 18px">
                @forelse($topProducts as $i => $item)
                    @php
                        $name = $item->product->name ?? 'N/A';
                        $brand = $item->product->brand ?? '-';
                        $qty = (int) $item->quantity;
                        $price = (float) ($item->product->price ?? 0);
                        $value = $qty * $price;
                        $maxQty = $topProducts->max('quantity') ?: 1;
                        $pct = round(($qty / $maxQty) * 100);
                    @endphp
                    <div class="tp-row">
                        <div class="tp-rank {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</div>
                        <div class="tp-info">
                            <div class="tp-name" title="{{ $name }}">{{ $name }}</div>
                            <div class="tp-bar-track"><div class="tp-bar" style="width:{{ $pct }}%;background:var(--accent)"></div></div>
                        </div>
                        <div class="tp-qty">{{ number_format($qty) }}</div>
                        <div class="tp-val">₱{{ number_format($value, 2) }}</div>
                    </div>
                @empty
                    <div style="text-align:center;padding:24px;color:var(--muted);font-size:0.8rem">No inventory data found</div>
                @endforelse
            </div>
        </div>
    </div>
    <!-- Insights: Stock Status -->
    <div class="col-xl-3">
        <div class="dc" style="height:100%">
            <div class="dc-head"><h5>Insights: Stock Status Overview</h5></div>
            <div class="dc-body">
                <div class="ss-wrap">
                    <div class="ss-chart">
                        <canvas id="stockDonut"></canvas>
                        <div class="ss-center">
                            <div class="ss-pct" id="stock-pct">100%</div>
                            <div class="ss-lbl">Well Stocked</div>
                        </div>
                    </div>
                    <div class="ss-legend">
                        <div class="ss-legend-item"><span class="ss-dot" style="background:var(--green)"></span> Well Stocked (<span id="l-instock">{{ $stats['active_products'] - $stats['low_stock'] - $stats['out_of_stock'] }}</span>)</div>
                        <div class="ss-legend-item"><span class="ss-dot" style="background:var(--yellow)"></span> Low Stock (<span id="l-low">{{ $stats['low_stock'] }}</span>)</div>
                        <div class="ss-legend-item"><span class="ss-dot" style="background:var(--accent)"></span> Out of Stock (<span id="l-out">{{ $stats['out_of_stock'] }}</span>)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Recent Transactions -->
    <div class="col-xl-4">
        <div class="dc" style="height:100%">
            <div class="dc-head"><h5>Recent Transactions</h5><a href="{{ route('reports.transaction-history') }}">View All</a></div>
            <div class="dc-body" style="padding:6px 18px;max-height:260px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(255,46,85,.06) transparent">
                @forelse($recentSales as $sale)
                    @php $isPaid = $sale->status === 'completed' || $sale->total_amount > 0; @endphp
                    <div class="tx">
                        <div class="tx-icon {{ $isPaid ? 'sale' : 'pending' }}">
                            <i class="bi {{ $isPaid ? 'bi-cart-check' : 'bi-hourglass-split' }}"></i>
                        </div>
                        <div class="tx-info">
                            <div class="tx-id">INV-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="tx-meta">{{ $sale->created_at->format('d M Y') }} &bull; {{ $sale->created_at->format('h:i A') }}</div>
                        </div>
                        <div class="tx-status {{ $isPaid ? 'paid' : 'pending' }}">{{ $isPaid ? 'Paid' : 'Pending' }}</div>
                    </div>
                @empty
                    <div style="text-align:center;padding:28px;color:var(--muted);font-size:0.78rem">No recent transactions</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ═══ BRANCH SUMMARY + PROMO ═══ -->
<div class="row g-3">
    <div class="col-xl-8">
        <div class="dc" style="height:100%">
            <div class="dc-head"><h5>Branch Summary</h5></div>
            <div class="dc-body">
                <div class="row g-3">
                    @foreach($branchSummary as $br)
                        <div class="col-xl-3 col-md-6">
                            <div class="br {{ $br->id == $user->branch_id ? 'active-br' : '' }}">
                                <div class="br-name">{{ $br->id == 8 ? 'Main Branch' : $br->branch_name }}</div>
                                <div class="br-stats">
                                    <div><div class="br-stat-val" style="color:var(--cyan)">{{ $br->product_count }}</div><div class="br-stat-lbl">Products</div></div>
                                    <div><div class="br-stat-val" style="color:var(--green)">{{ number_format($br->total_qty) }}</div><div class="br-stat-lbl">Inventory</div></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="promo" style="height:100%">
            <svg class="promo-bike" viewBox="0 0 200 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M30 100 Q40 60 80 55 Q100 50 120 55 L150 70 Q160 75 155 85 L140 95 Q135 98 125 95 L95 85 Q85 80 80 85 L70 100" stroke="#FF2E55" stroke-width="1.5" fill="rgba(255,46,85,0.05)"/>
                <circle cx="50" cy="100" r="18" stroke="#FF2E55" stroke-width="1.5" fill="none"/>
                <circle cx="50" cy="100" r="8" stroke="#FF2E55" stroke-width="1" fill="none" opacity=".5"/>
                <circle cx="140" cy="95" r="18" stroke="#FF2E55" stroke-width="1.5" fill="none"/>
                <circle cx="140" cy="95" r="8" stroke="#FF2E55" stroke-width="1" fill="none" opacity=".5"/>
                <path d="M80 55 L90 40 Q92 38 95 40 L100 50" stroke="#FF2E55" stroke-width="1.2" fill="none"/>
                <path d="M120 55 L115 42 Q113 38 110 40 L105 48" stroke="#FF2E55" stroke-width="1.2" fill="none"/>
                <path d="M150 70 L165 65 Q168 64 170 66 L172 70" stroke="#FF2E55" stroke-width="1" fill="none" opacity=".7"/>
                <ellipse cx="100" cy="75" rx="40" ry="15" fill="rgba(255,46,85,0.03)"/>
            </svg>
            <div class="promo-brand">A&K PARTS</div>
            <div class="promo-tag">
                <strong>Quality Parts.<br>Reliable Performance.</strong>
                Management Center &mdash; Trusted motorcycle parts &amp; accessories for every rider.
                <span class="promo-badge">v1.0</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var R='#FF2E55',G='#00E676',Y='#FFC107',P='#D500F9',C='#00E5FF',M='#FF007F';

    /* Revenue KPI sparkline */
    var rc=document.getElementById('revKpiChart');
    if(rc) new Chart(rc.getContext('2d'),{type:'line',data:{labels:['M','T','W','T','F','S','S'],datasets:[{data:[0,0,0,0,0,0,{{ $stats['total_sales'] }}],borderColor:G,backgroundColor:'rgba(0,230,118,0.08)',fill:true,tension:.4,pointRadius:0,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{display:false},y:{display:false}}}});

    /* Cyan KPI sparkline */
    var cc=document.getElementById('cyanKpiChart');
    if(cc) new Chart(cc.getContext('2d'),{type:'line',data:{labels:['M','T','W','T','F','S','S'],datasets:[{data:[0,0,0,0,0,0,{{ $stats['total_inventory'] }}],borderColor:C,backgroundColor:'rgba(0,229,255,0.08)',fill:true,tension:.4,pointRadius:0,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{display:false},y:{display:false}}}});

    /* Gauge */
    var gc=document.getElementById('gaugeChart');
    if(gc) new Chart(gc.getContext('2d'),{type:'doughnut',data:{labels:['Pending','Done'],datasets:[{data:[{{ $stats['total_transfers'] }},Math.max(10-{{ $stats['total_transfers'] }},1)],backgroundColor:[P,'rgba(255,255,255,0.04)'],borderWidth:0}]},options:{responsive:false,cutout:'78%',rotation:-90,circumference:180,plugins:{legend:{display:false},tooltip:{enabled:false}}}});

    /* Inv sparkline */
    var ic=document.getElementById('invChart');
    if(ic) new Chart(ic.getContext('2d'),{type:'line',data:{labels:['M','T','W','T','F','S','S'],datasets:[{data:[0,0,0,0,0,0,{{ $stats['inventory_value'] }}],borderColor:C,backgroundColor:'rgba(0,229,255,0.06)',fill:true,tension:.4,pointRadius:0,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{display:false},y:{display:false}}}});

    /* Stock Donut */
    var inst={{ $stats['active_products'] - $stats['low_stock'] - $stats['out_of_stock'] }};
    var low={{ $stats['low_stock'] }};
    var oos={{ $stats['out_of_stock'] }};
    var tot=inst+low+oos;
    document.getElementById('stock-pct').textContent=(tot>0?Math.round(inst/tot*100):100)+'%';
    var sc=document.getElementById('stockDonut');
    if(sc) new Chart(sc.getContext('2d'),{type:'doughnut',data:{labels:['Well Stocked','Low Stock','Out of Stock'],datasets:[{data:[inst,low,oos],backgroundColor:[G,Y,R],borderWidth:0,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,cutout:'72%',plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.label+': '+c.raw}}}}}});

    /* Live polling */
    setInterval(function(){
        fetch('{{ route("dashboard") }}').then(function(r){return r.text()}).then(function(h){
            var d=new DOMParser().parseFromString(h,'text/html');
            ['kpi-inventory','kpi-lowstock','kpi-pending','kpi-invvalue'].forEach(function(id){
                var el=document.getElementById(id),nd=d.getElementById(id);
                if(el&&nd) el.textContent=nd.textContent;
            });
            var revEl=document.getElementById('kpi-revenue');
            var nd=d.getElementById('kpi-revenue');
            if(revEl&&nd) revEl.dataset.raw=nd.dataset.raw;
        }).catch(function(){});
    },10000);
});

function toggleRevenue(){
    var el=document.getElementById('kpi-revenue');
    var icon=document.getElementById('eyeIcon');
    if(el.dataset.masked==='true'){
        el.textContent=el.dataset.raw;
        el.dataset.masked='false';
        icon.className='bi bi-eye';
    }else{
        el.textContent='₱***.**';
        el.dataset.masked='true';
        icon.className='bi bi-eye-slash';
    }
}
</script>
@endsection
