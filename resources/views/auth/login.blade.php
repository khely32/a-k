<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A&K Motorcycle Parts - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        :root{--red:#ef4444;--red-dk:#991b1b;--red-g:rgba(239,68,68,0.6);--bg:#07080c;--card:rgba(17,19,24,0.92)}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:#fff;min-height:100vh;overflow:hidden;display:flex;align-items:center;justify-content:center}

        .preloader{position:fixed;inset:0;background:var(--bg);z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:opacity .6s,visibility .6s}
        .preloader.hidden{opacity:0;visibility:hidden;pointer-events:none}
        .preloader-ring{width:56px;height:56px;border:3px solid rgba(239,68,68,.08);border-top-color:var(--red);border-radius:50%;animation:spin .7s linear infinite}
        .preloader-text{font-family:'Orbitron',monospace;font-size:.6rem;letter-spacing:8px;color:rgba(239,68,68,.45);margin-top:20px;animation:blink 1s step-end infinite}
        @keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

        .bg-scene{position:fixed;inset:0;background:radial-gradient(ellipse at 15% 50%,rgba(239,68,68,.07) 0%,transparent 55%),radial-gradient(ellipse at 85% 20%,rgba(239,68,68,.04) 0%,transparent 45%),radial-gradient(ellipse at 50% 100%,rgba(239,68,68,.03) 0%,transparent 40%),linear-gradient(180deg,#07080c,#0a0b10 40%,#080910);z-index:0}
        .carbon-overlay{position:fixed;inset:0;z-index:1;opacity:.025;background-image:repeating-linear-gradient(45deg,transparent,transparent 2px,rgba(255,255,255,.03) 2px,rgba(255,255,255,.03) 4px),repeating-linear-gradient(-45deg,transparent,transparent 2px,rgba(255,255,255,.03) 2px,rgba(255,255,255,.03) 4px);pointer-events:none}
        .grid-overlay{position:fixed;inset:0;z-index:1;background-image:linear-gradient(rgba(239,68,68,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(239,68,68,.02) 1px,transparent 1px);background-size:60px 60px;animation:gridScroll 25s linear infinite;pointer-events:none}
        @keyframes gridScroll{0%{background-position:0}100%{background-position:60px 60px}}

        .speed-lines{position:fixed;inset:0;z-index:1;overflow:hidden;opacity:.08;pointer-events:none}
        .speed-line{position:absolute;height:1px;background:linear-gradient(90deg,transparent,var(--red),transparent);animation:speedLine linear infinite}
        @keyframes speedLine{0%{transform:translateX(-100vw);opacity:0}10%{opacity:1}90%{opacity:1}100%{transform:translateX(100vw);opacity:0}}

        .particles{position:fixed;inset:0;z-index:1;overflow:hidden;pointer-events:none}
        .particle{position:absolute;width:2px;height:2px;background:var(--red);border-radius:50%;animation:particleFloat linear infinite;opacity:0}
        @keyframes particleFloat{0%{transform:translateY(100vh) scale(0);opacity:0}10%{opacity:.7}90%{opacity:.2}100%{transform:translateY(-10vh) scale(1);opacity:0}}

        .gear-wrap{position:fixed;z-index:1;opacity:.035;pointer-events:none}
        .gear-tl{top:-90px;left:-90px;animation:spin 35s linear infinite}
        .gear-br{bottom:-110px;right:-110px;animation:spin 28s linear infinite reverse}

        .glow-top{position:fixed;top:15%;left:50%;transform:translateX(-50%);width:500px;height:500px;background:radial-gradient(circle,rgba(239,68,68,.1),transparent 70%);filter:blur(80px);z-index:1;pointer-events:none}
        .glow-bottom{position:fixed;bottom:5%;left:10%;width:300px;height:300px;background:radial-gradient(circle,rgba(239,68,68,.06),transparent 70%);filter:blur(60px);z-index:1;pointer-events:none}

        .login-wrapper{position:relative;z-index:10;display:flex;flex-direction:column;align-items:center;animation:wrapperIn 1s ease-out .3s both}
        @keyframes wrapperIn{0%{opacity:0;transform:translateY(40px) scale(.96)}100%{opacity:1;transform:translateY(0) scale(1)}}

        .logo-area{text-align:center;margin-bottom:28px;animation:logoDrop .8s ease-out .5s both}
        @keyframes logoDrop{0%{opacity:0;transform:translateY(-25px)}100%{opacity:1;transform:translateY(0)}}
        .logo-badge{display:inline-flex;align-items:center;justify-content:center;width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--red-dk));box-shadow:0 0 40px var(--red-g),0 0 80px rgba(239,68,68,.15);animation:logoPulse 3s ease-in-out infinite;position:relative}
        .logo-badge::before{content:'';position:absolute;inset:-4px;border-radius:50%;border:2px solid rgba(239,68,68,.25);animation:ringSpin 8s linear infinite}
        .logo-badge::after{content:'';position:absolute;inset:-10px;border-radius:50%;border:1px dashed rgba(239,68,68,.1);animation:ringSpin 12s linear infinite reverse}
        @keyframes logoPulse{0%,100%{box-shadow:0 0 40px var(--red-g),0 0 80px rgba(239,68,68,.15)}50%{box-shadow:0 0 55px var(--red-g),0 0 100px rgba(239,68,68,.25)}}
        @keyframes ringSpin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
        .logo-text{font-family:'Orbitron',monospace;font-weight:900;font-size:2rem;letter-spacing:6px;margin-top:10px;background:linear-gradient(90deg,#fff,var(--red) 50%,#fff);background-size:200% 100%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:shimmer 4s ease-in-out infinite}
        @keyframes shimmer{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
        .logo-sub{font-family:'Rajdhani',sans-serif;font-size:.75rem;letter-spacing:8px;text-transform:uppercase;color:rgba(239,68,68,.5);margin-top:2px}

        .status-row{display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:22px;animation:wrapperIn .8s ease-out .7s both}
        .status-dot{width:6px;height:6px;border-radius:50%;background:var(--red);animation:dotPulse 2s ease-in-out infinite;box-shadow:0 0 6px var(--red-g)}
        @keyframes dotPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.7)}}
        .status-label{font-family:'Orbitron',monospace;font-size:.55rem;letter-spacing:3px;color:rgba(239,68,68,.45);text-transform:uppercase}

        .login-card{width:400px;background:var(--card);border:1px solid rgba(239,68,68,.12);border-radius:18px;padding:36px 32px;backdrop-filter:blur(24px);box-shadow:0 0 0 1px rgba(239,68,68,.04),0 25px 60px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.04);position:relative;overflow:hidden;animation:cardUp .7s ease-out .8s both}
        @keyframes cardUp{0%{opacity:0;transform:translateY(50px) scale(.92)}100%{opacity:1;transform:translateY(0) scale(1)}}
        .login-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--red) 30%,var(--red) 70%,transparent);animation:accentPulse 3s ease-in-out infinite}
        @keyframes accentPulse{0%,100%{opacity:.4}50%{opacity:1}}
        .login-card::after{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle at 50% 0%,rgba(239,68,68,.05),transparent 45%);pointer-events:none}

        .card-title{font-family:'Rajdhani',sans-serif;font-size:1.15rem;font-weight:700;text-align:center;color:#fff;margin-bottom:4px;letter-spacing:3px;text-transform:uppercase}
        .card-sub{text-align:center;font-size:.7rem;color:rgba(255,255,255,.3);margin-bottom:26px;letter-spacing:1px}

        .fg{margin-bottom:18px;position:relative}
        .fg label{display:block;font-family:'Rajdhani',sans-serif;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:2px;color:rgba(239,68,68,.6);margin-bottom:7px}
        .fg input{width:100%;padding:13px 44px 13px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(239,68,68,.1);border-radius:11px;color:#f1f5f9;font-size:.9rem;font-family:'Inter',sans-serif;outline:none;transition:all .3s}
        .fg input::placeholder{color:rgba(255,255,255,.2)}
        .fg input:focus{border-color:rgba(239,68,68,.5);background:rgba(239,68,68,.04);box-shadow:0 0 20px rgba(239,68,68,.08)}
        .fg .fi{position:absolute;right:14px;top:40px;color:rgba(239,68,68,.3);font-size:1.1rem;pointer-events:none;transition:color .3s}
        .fg input:focus~.fi{color:var(--red)}

        .pw-toggle{position:absolute;right:14px;top:38px;background:none;border:none;color:rgba(239,68,68,.4);cursor:pointer;font-size:1.1rem;padding:4px;transition:color .3s;z-index:2}
        .pw-toggle:hover{color:var(--red)}

        .error-alert{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:10px 14px;margin-bottom:20px;animation:shake .4s ease-out}
        .error-alert ul{list-style:none;margin:0;padding:0}
        .error-alert li{font-size:.8rem;color:#ef4444;padding:2px 0}
        @keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}

        .btn-login{width:100%;padding:15px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--red),#dc2626);color:#fff;font-family:'Rajdhani',sans-serif;font-size:1.05rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;cursor:pointer;position:relative;overflow:hidden;transition:all .3s;margin-top:8px}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(239,68,68,.4)}
        .btn-login:active{transform:translateY(0)}
        .btn-login::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);animation:btnShine 3s ease-in-out infinite}
        @keyframes btnShine{0%{left:-100%}50%,100%{left:100%}}

        .login-links{text-align:center;margin-top:20px}
        .login-links a{color:rgba(239,68,68,.5);text-decoration:none;font-size:.8rem;letter-spacing:1px;transition:color .3s}
        .login-links a:hover{color:var(--red);text-shadow:0 0 12px rgba(239,68,68,.4)}
        .login-links .div{display:inline-block;width:1px;height:12px;background:rgba(255,255,255,.1);vertical-align:middle;margin:0 12px}

        .version-tag{text-align:center;margin-top:24px;font-family:'Orbitron',monospace;font-size:.55rem;letter-spacing:4px;color:rgba(239,68,68,.18);animation:wrapperIn 1s ease-out 1.5s both}

        @media(max-width:480px){.login-card{width:92vw;padding:30px 24px}.logo-text{font-size:1.6rem;letter-spacing:4px}.logo-badge{width:65px;height:65px}}
    </style>
</head>
<body>

    <div class="preloader" id="preloader">
        <div class="preloader-ring"></div>
        <div class="preloader-text">INITIALIZING</div>
    </div>

    <div class="bg-scene"></div>
    <div class="carbon-overlay"></div>
    <div class="grid-overlay"></div>
    <div class="speed-lines" id="speedLines"></div>
    <div class="particles" id="particles"></div>

    <div class="gear-wrap gear-tl">
        <svg width="220" height="220" viewBox="0 0 220 220" fill="none"><path d="M110 20 L120 35 L140 25 L138 45 L160 42 L150 60 L172 65 L156 78 L175 92 L155 98 L168 118 L146 116 L150 140 L130 130 L126 155 L110 140 L94 155 L90 130 L70 140 L74 116 L52 118 L65 98 L45 92 L64 78 L48 65 L70 60 L60 42 L82 45 L80 25 L100 35 Z" stroke="#ef4444" stroke-width="1.5" fill="none"/><circle cx="110" cy="90" r="30" stroke="#ef4444" stroke-width="1.5" fill="none"/></svg>
    </div>
    <div class="gear-wrap gear-br">
        <svg width="280" height="280" viewBox="0 0 280 280" fill="none"><path d="M140 20 L152 40 L175 28 L170 52 L198 46 L185 68 L214 70 L195 86 L218 104 L195 110 L212 135 L188 132 L198 160 L174 150 L178 180 L155 165 L152 195 L140 175 L128 195 L125 165 L102 180 L106 150 L82 160 L92 132 L68 135 L85 110 L62 104 L85 86 L66 70 L95 68 L82 46 L110 52 L105 28 L128 40 Z" stroke="#ef4444" stroke-width="1.5" fill="none"/><circle cx="140" cy="110" r="38" stroke="#ef4444" stroke-width="1.5" fill="none"/></svg>
    </div>

    <div class="glow-top"></div>
    <div class="glow-bottom"></div>

    <div class="login-wrapper">
        <div class="logo-area">
            <div class="logo-badge">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none"><text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" font-family="Orbitron,monospace" font-weight="900" font-size="16" fill="#fff">A&amp;K</text></svg>
            </div>
            <div class="logo-text">A&K</div>
            <div class="logo-sub">Motorcycle Parts</div>
        </div>

        <div class="status-row">
            <span class="status-dot"></span>
            <span class="status-label">System Online</span>
        </div>

        <div class="login-card">
            <div class="card-title">System Access</div>
            <div class="card-sub">Inventory &amp; POS Management</div>

            @if ($errors->any())
                <div class="error-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li><i class="bi bi-exclamation-circle me-1"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="fg">
                    <label>Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    <i class="bi bi-envelope fi"></i>
                </div>

                <div class="fg">
                    <label>Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <button type="button" class="pw-toggle" id="toggleLoginPassword">
                        <i class="bi bi-eye" id="loginPasswordEyeIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i> LOGIN
                </button>
            </form>

            <div class="login-links">
                <a href="{{ route('forgot.email') }}">Forgot Password?</a>
                <span class="div"></span>
                <a href="{{ route('register') }}">Register</a>
            </div>
        </div>

        <div class="version-tag">A&amp;K IMS v1.0 &mdash; RACING EDITION</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => { document.getElementById('preloader').classList.add('hidden'); }, 1200);
        });

        (function() {
            const c = document.getElementById('speedLines');
            for (let i = 0; i < 20; i++) {
                const l = document.createElement('div');
                l.className = 'speed-line';
                l.style.top = Math.random()*100+'%';
                l.style.width = (Math.random()*300+100)+'px';
                l.style.animationDuration = (Math.random()*4+3)+'s';
                l.style.animationDelay = (Math.random()*5)+'s';
                c.appendChild(l);
            }
        })();

        (function() {
            const c = document.getElementById('particles');
            for (let i = 0; i < 30; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                p.style.left = Math.random()*100+'%';
                p.style.width = p.style.height = (Math.random()*3+1)+'px';
                p.style.animationDuration = (Math.random()*8+6)+'s';
                p.style.animationDelay = (Math.random()*10)+'s';
                c.appendChild(p);
            }
        })();

        document.getElementById('toggleLoginPassword').addEventListener('click', function() {
            const pw = document.getElementById('password');
            const ic = document.getElementById('loginPasswordEyeIcon');
            pw.type = pw.type === 'password' ? 'text' : 'password';
            ic.classList.toggle('bi-eye');
            ic.classList.toggle('bi-eye-slash');
        });

        @if(session('success'))
            Swal.fire({
                title: 'Registered Successfully!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Proceed to Login',
                background: '#111',
                color: '#fff'
            });
        @endif
    </script>
</body>
</html>
