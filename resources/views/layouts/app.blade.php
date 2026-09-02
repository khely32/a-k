<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'A&K Motorcycle Parts') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0D1117;
    --sidebar:#0A0D12;
    --card:#161B22;
    --card2:#1C2128;
    --accent:#FF2E55;
    --accent2:#DC143C;
    --red:#FF2E55;
    --crimson:#FF2E55;
    --green:#00E676;
    --cyan:#00E5FF;
    --yellow:#FFC107;
    --purple:#D500F9;
    --magenta:#FF007F;
    --text:#FFFFFF;
    --muted:#6B7280;
    --border:rgba(255,46,85,0.12);
    --border2:rgba(255,255,255,0.06);
}
*{box-sizing:border-box}
body{
    background:var(--bg);
    color:var(--text);
    min-height:100vh;
    font-family:'Inter',system-ui,-apple-system,sans-serif;
    margin:0;
    display:flex;
    -webkit-font-smoothing:antialiased;
}

.sidebar{
    width:240px;
    height:100vh;
    background:var(--sidebar);
    border-right:1px solid var(--border2);
    position:fixed;
    left:0;top:0;
    z-index:1000;
    display:flex;
    flex-direction:column;
    overflow:hidden;
}
.sidebar-scroll{flex:1;overflow-y:auto;padding:20px 0;scrollbar-width:thin;scrollbar-color:rgba(255,46,85,.08) transparent}
.sidebar-scroll::-webkit-scrollbar{width:3px}
.sidebar-scroll::-webkit-scrollbar-thumb{background:rgba(255,46,85,.15);border-radius:3px}

.sidebar-brand{padding:0 18px;margin-bottom:6px;display:flex;align-items:center;gap:10px}
.sidebar-brand svg{width:34px;height:34px;flex-shrink:0}
.sidebar-brand-text{display:flex;flex-direction:column}
.sidebar-brand-name{font-size:1.05rem;font-weight:800;color:var(--green);letter-spacing:1px;line-height:1.1;text-shadow:0 0 14px rgba(0,230,118,.35)}
.sidebar-brand-sub{font-size:0.52rem;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;font-weight:600;margin-top:1px}

.sidebar-branch{
    display:inline-flex;align-items:center;gap:5px;
    font-size:0.58rem;color:var(--cyan);font-weight:700;text-transform:uppercase;letter-spacing:1.2px;
    padding:4px 10px;margin:4px 18px 12px;
    border:1px solid rgba(0,229,255,.3);border-radius:999px;
    background:rgba(0,229,255,.06);
    box-shadow:0 0 10px rgba(0,229,255,.1);
}
.sidebar-branch i{font-size:0.65rem}

.sidebar-nav{padding:0 10px}
.nav-section{font-size:0.56rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);font-weight:700;padding:10px 10px 5px;margin-top:2px}
.nav-link{
    display:flex;align-items:center;gap:10px;
    padding:9px 12px;margin:1px 0;border-radius:8px;
    color:#5C6070;text-decoration:none;font-size:0.82rem;font-weight:500;transition:all .15s ease;
}
.nav-link i{width:18px;text-align:center;font-size:0.92rem;flex-shrink:0}
.nav-link:hover{color:var(--text);background:rgba(0,230,118,.06)}
.nav-link.active{
    background:linear-gradient(135deg,#059669,#047857);
    color:#fff;font-weight:600;
    border-radius:999px;
    box-shadow:0 2px 20px rgba(0,230,118,.35),0 0 40px rgba(0,230,118,.12);
}
.nav-badge{
    margin-left:auto;font-size:0.56rem;font-weight:700;padding:1px 6px;border-radius:999px;min-width:18px;text-align:center;line-height:1.4;
}
.nav-badge.red{background:var(--accent);color:#fff}
.nav-badge.yellow{background:var(--yellow);color:#000}

.sidebar-footer{padding:14px 18px;border-top:1px solid var(--border2)}
.btn-logout{
    width:100%;padding:8px 0;
    background:transparent;border:1.5px solid var(--accent);border-radius:8px;
    color:var(--accent);font-size:0.78rem;font-weight:600;cursor:pointer;transition:.15s;
    font-family:inherit;
}
.btn-logout:hover{background:var(--accent);color:#fff;box-shadow:0 0 16px rgba(255,46,85,.35)}

.content-wrapper{
    margin-left:240px;
    padding:20px 24px;
    width:calc(100% - 240px);
    min-height:100vh;
}

.table{color:var(--text)}
.table thead th{color:var(--muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;border-color:var(--border2)}
.table td,.table th{border-color:var(--border2)}
.form-control,.form-select{background:#1E293B;color:#FFFFFF;border:1px solid #475569}
.form-control:focus,.form-select:focus{background:#1E293B;color:#FFFFFF;border-color:var(--green);box-shadow:0 0 0 3px rgba(0,230,118,.15)}
.form-control::placeholder{color:#64748B}
.form-control:read-only,.form-control[disabled]{background:rgba(30,41,59,.6);color:#94A3B8;border-color:#334155}
.form-select option{background:#1E293B;color:#FFFFFF}
.btn{border-radius:8px;font-weight:600;font-size:0.82rem}
.btn-primary{background:#059669;border:none;color:#fff}
.btn-primary:hover{background:#047857}
.btn-secondary{background:#475569;border:1px solid #576574;color:#fff}
.btn-secondary:hover{background:#576574;color:#fff}
.neon-btn{background:transparent;color:var(--green)!important;border:1px solid var(--green);border-radius:8px;padding:7px 16px;font-weight:600;text-decoration:none;transition:.2s;display:inline-flex;align-items:center;gap:6px;font-size:0.8rem}
.neon-btn:hover{background:var(--green);color:#000!important;box-shadow:0 0 16px rgba(0,230,118,.35)}

@media print{
    .sidebar,.no-print{display:none!important}
    .content-wrapper{margin-left:0!important;width:100%!important;padding:10px!important}
    body{background:#fff!important;color:#000!important}
}
</style>
</head>
<body>
@auth
<aside class="sidebar">
    <div class="sidebar-scroll">
        <div class="sidebar-brand">
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="11" stroke="#00E676" stroke-width="2"/>
                <circle cx="18" cy="18" r="3.5" stroke="#00E676" stroke-width="2"/>
                <path d="M18 5V9M18 27V31M5 18H9M27 18H31" stroke="#00E676" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="18" cy="18" r="1.5" fill="#00E676"/>
                <path d="M30 26l10 10" stroke="#00E676" stroke-width="2" stroke-linecap="round"/>
                <path d="M28 24c-3.2-.2-6 .9-6 4.1s2.8 4.3 6 4.1c3.2.2 6-.9 6-4.1 0-2.3-1.4-3.4-3.2-3.8z" stroke="#00E676" stroke-width="1.6" fill="none"/>
            </svg>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">A&K PARTS</span>
                <span class="sidebar-brand-sub">Motorcycle Parts</span>
            </div>
        </div>

        <div class="sidebar-branch"><i class="bi bi-building"></i> {{ auth()->user()->branchLabel() }}</div>

        <div class="sidebar-nav">
            <div class="nav-section">Main</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Inventory
                @php $sa = ($badges['low_stock'] ?? 0) + ($badges['out_of_stock'] ?? 0); @endphp
                @if($sa > 0)<span class="nav-badge yellow">{{ $sa }}</span>@endif
            </a>
            <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-terminal"></i> POS
            </a>
            <a href="{{ route('transfers.index') }}" class="nav-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Stock Transfers
                @if(($badges['pending_transfers'] ?? 0) > 0)<span class="nav-badge red">{{ $badges['pending_transfers'] }}</span>@endif
            </a>

            <div class="nav-section">Analytics</div>
            @if(Auth::user()->role === 'owner')
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
            @endif
            <a href="{{ route('reports.transaction-history') }}" class="nav-link {{ request()->routeIs('reports.transaction-history') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Transaction History
            </a>

            <div class="nav-section">Management</div>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Products
            </a>
            @if(Auth::user()->role === 'owner')
            <a href="{{ route('monitor.index') }}" class="nav-link {{ request()->routeIs('monitor.*') ? 'active' : '' }}">
                <i class="bi bi-activity"></i> Live Monitor
            </a>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Branches
            </a>
            @endif

            <div class="nav-section">Account</div>
            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> Profile
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-left me-1"></i> Logout</button>
        </form>
    </div>
</aside>
@endauth

<div class="content-wrapper">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@if(Auth::check() && Auth::user()->role !== 'owner')
<script>
(function(){
    const POLL_INTERVAL = 8000;
    const url = '{{ route("api.branch-status") }}';
    let kicked = false;

    function checkBranch(){
        if(kicked) return;
        fetch(url, {
            headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
            credentials:'same-origin'
        })
        .then(r => {
            if(r.status === 403 || r.status === 419){
                doKick(); return null;
            }
            return r.json();
        })
        .then(data => {
            if(data && data.active === false) doKick();
        })
        .catch(() => {});
    }

    function doKick(){
        if(kicked) return;
        kicked = true;
        // Force POST logout to destroy session server-side
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("logout") }}';
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

    setInterval(checkBranch, POLL_INTERVAL);
})();
</script>
@endif

@yield('scripts')
</body>
</html>
