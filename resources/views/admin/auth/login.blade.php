<!DOCTYPE html>
<html lang="en">
<script>
  (function () {
    try { localStorage.removeItem('nk_theme'); } catch (e) {}
    document.documentElement.setAttribute('data-theme', 'light');
  })();
</script>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Masuk</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --auth-bg: #0a0a0a;
      --auth-surface: #141414;
      --auth-surface-2: #1a1a1a;
      --auth-border: rgba(255,255,255,.08);
      --auth-text: #ffffff;
      --auth-text-2: #d4d4d8;
      --auth-text-3: #737373;
      --auth-text-4: #525252;
      --auth-primary: linear-gradient(180deg, #ff8b39 0%, #f97316 100%);
      --auth-primary-border: rgba(255,255,255,.06);
      --auth-primary-text: #fff7ed;
      --auth-primary-hover: #fb7e28;
      --auth-primary-active: #ea580c;
      --auth-shadow: 0 20px 50px rgba(0,0,0,.38);
      color-scheme: dark;
    }

    html[data-theme="light"] {
      --auth-bg: #f7f7f8;
      --auth-surface: rgba(255,255,255,.92);
      --auth-surface-2: #ffffff;
      --auth-border: #e7e7ec;
      --auth-text: #18181b;
      --auth-text-2: #3f3f46;
      --auth-text-3: #71717a;
      --auth-text-4: #a1a1aa;
      --auth-primary: linear-gradient(180deg, #111827 0%, #1f2937 100%);
      --auth-primary-border: rgba(17,24,39,.1);
      --auth-primary-text: #f8fafc;
      --auth-primary-hover: #0f172a;
      --auth-primary-active: #020617;
      --auth-shadow: 0 22px 60px rgba(16,24,40,.08);
      color-scheme: light;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: var(--auth-bg);
      color: var(--auth-text);
      -webkit-font-smoothing: antialiased;
    }

    .auth-shell {
      width: 100%;
      max-width: 418px;
      padding: 1.25rem;
    }

    .auth-brand {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      padding: 0;
      width: 226px;
      height: 108px;
      overflow: hidden;
    }

    .auth-logo-img {
      width: 100%;
      height: auto;
      max-width: none;
      transform: scale(1.24) translateY(-4px);
      transform-origin: center top;
      background: transparent;
      filter: drop-shadow(0 8px 20px rgba(0, 0, 0, .08));
    }

    html[data-theme="dark"] .auth-logo-img {
      filter:
        drop-shadow(0 1px 0 rgba(255, 255, 255, .06))
        drop-shadow(0 10px 24px rgba(0, 0, 0, .24));
    }

    .auth-logo-light { display: none; }
    html[data-theme="light"] .auth-logo-dark { display: none; }
    html[data-theme="light"] .auth-logo-light { display: block; }

    /* Subtle dot pattern */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: radial-gradient(circle, rgba(255,255,255,.03) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
    }

    html[data-theme="light"] body::before {
      background-image: radial-gradient(circle, rgba(24,24,27,.035) 1px, transparent 1px);
    }

    /* Logo area */
    .auth-logo {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 2rem;
      width: 100%;
    }
    .auth-logo img {
      width: auto;
      height: 100px;
      max-width: 100%;
      object-fit: contain;
    }


    /* Card */
    .auth-card {
      width: 100%;
      max-width: 388px;
      margin: 0 auto;
      background: linear-gradient(180deg, color-mix(in srgb, var(--auth-surface) 100%, transparent), color-mix(in srgb, var(--auth-surface) 94%, transparent));
      border: 1px solid var(--auth-border);
      border-radius: 18px;
      padding: 1.65rem 1.5rem 1.45rem;
      box-shadow: var(--auth-shadow);
      backdrop-filter: blur(10px);
    }

    .auth-title {
      font-size: 1.22rem;
      font-weight: 700;
      color: var(--auth-text);
      margin-bottom: .25rem;
      letter-spacing: -.02em;
    }
    .auth-subtitle {
      font-size: .83rem;
      color: var(--auth-text-3);
      margin-bottom: 1.2rem;
      line-height: 1.5;
    }

    /* Form */
    .form-group { margin-bottom: 1.25rem; }
    .form-label {
      font-size: .79rem;
      font-weight: 600;
      color: var(--auth-text-2);
      display: block;
      margin-bottom: .5rem;
    }
    .form-input {
      display: block;
      width: 100%;
      background: var(--auth-surface-2);
      border: 1px solid var(--auth-border);
      border-radius: 12px;
      padding: .72rem .92rem;
      font-size: .86rem;
      font-family: inherit;
      color: var(--auth-text);
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .form-input:focus {
      border-color: #1f2937;
      box-shadow: 0 0 0 3px rgba(15,23,42,.1);
    }
    html[data-theme="dark"] .form-input:focus {
      border-color: #f97316;
      box-shadow: 0 0 0 3px rgba(249,115,22,.1);
    }
    .form-input::placeholder { color: var(--auth-text-4); }

    .form-input-wrap {
      position: relative;
    }
    .pass-toggle {
      position: absolute;
      right: .75rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--auth-text-4);
      font-size: 1.1rem;
      padding: 0;
      display: flex;
    }
    .pass-toggle:hover { color: var(--auth-text-3); }

    /* Options row */
    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }
    .form-remember {
      display: flex;
      align-items: center;
      gap: .375rem;
      font-size: .74rem;
      color: var(--auth-text-3);
      cursor: pointer;
    }
    .form-remember input { accent-color: #1f2937; }
    html[data-theme="dark"] .form-remember input { accent-color: #f97316; }

    /* Submit */
    .btn-submit {
      width: 100%;
      background: var(--auth-primary);
      color: var(--auth-primary-text);
      border: 1px solid var(--auth-primary-border);
      border-radius: 12px;
      padding: .76rem .95rem;
      font-size: .9rem;
      font-weight: 700;
      letter-spacing: -.01em;
      font-family: inherit;
      cursor: pointer;
      transition: background-color .15s, transform .1s, box-shadow .15s, border-color .15s;
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,.08),
        0 10px 24px rgba(0,0,0,.16);
    }
    .btn-submit:hover {
      background: var(--auth-primary-hover);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,.08),
        0 12px 28px rgba(0,0,0,.18);
    }
    .btn-submit:active { background: var(--auth-primary-active); transform: scale(.99); }

    html[data-theme="light"] .btn-submit {
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,.08),
        0 12px 24px rgba(15,23,42,.08);
    }

    /* Footer */
    .auth-footer {
      margin-top: 1.15rem;
      text-align: center;
      font-size: .68rem;
      color: var(--auth-text-4);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .45rem;
      letter-spacing: .01em;
    }

    .auth-footer strong {
      color: var(--auth-text-2);
      font-weight: 700;
    }

    .auth-footer .auth-footer-sep {
      opacity: .45;
    }

    /* Alerts */
    .auth-alert {
      border-radius: 10px;
      font-size: .8125rem;
      padding: .75rem 1rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .auth-alert-danger { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); color: #fca5a5; }
    .auth-alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2); color: #86efac; }

    /* Animation */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .auth-card { animation: fadeIn .35s ease-out; }
    .auth-logo { animation: fadeIn .35s ease-out .05s both; }

    /* Mobile */
    @media (max-width: 480px) {
      .auth-shell { padding: .9rem; }
      .auth-card { margin: 0; padding: 1.45rem 1.1rem 1.25rem; border-radius: 16px; }
      .auth-brand {
        width: 206px;
        height: 98px;
      }
    }
  </style>
</head>
<body>

  <div class="auth-shell">
    <div style="text-align: center; margin-bottom: 0.75rem;">
      <div class="auth-brand">
        <img src="/img/NetkingLoginBaruDark.png" alt="Netking" class="auth-logo-img auth-logo-dark">
        <img src="/img/NetkingLoginBaruLight.png" alt="Netking" class="auth-logo-img auth-logo-light">
      </div>
    </div>

    <div class="auth-card">
    <div class="auth-title">Masuk ke akun Anda</div>
    <div class="auth-subtitle">Masukkan data di bawah untuk mengakses akun Anda</div>

    <form action="{{ route('admin.login.post') }}" method="POST" autocomplete="off">
      @csrf

      @if($errors->any())
      <div class="auth-alert auth-alert-danger">
        <i class='bx bx-error-circle' style="font-size:1.1rem;flex-shrink:0;"></i>
        <div>@foreach($errors->all() as $err){{ $err }} @endforeach</div>
      </div>
      @endif
      @if(session('status'))
      <div class="auth-alert auth-alert-success">
        <i class='bx bx-check-circle' style="font-size:1.1rem;flex-shrink:0;"></i> {{ session('status') }}
      </div>
      @endif

      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-input"
          value="{{ old('email') }}" placeholder="Masukkan email" autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="form-input-wrap">
          <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" style="padding-right:2.5rem;">
          <button type="button" class="pass-toggle" onclick="nkTogglePass()" tabindex="-1">
            <i class='bx bx-hide' id="pass-ico"></i>
          </button>
        </div>
      </div>

      <div class="form-options">
        <label class="form-remember">
          <input type="checkbox" name="remember"> Ingat saya
        </label>
      </div>

      <button type="submit" class="btn-submit">Masuk</button>
    </form>

    <div class="auth-footer">
      <span>&copy; {{ date('Y') }}</span>
      <strong>NETKING</strong>
      <span class="auth-footer-sep">•</span>
      <span>v2.0</span>
    </div>
  </div>
  </div>

  <script>
    function nkTogglePass() {
      var inp = document.getElementById('password');
      var ico = document.getElementById('pass-ico');
      if (inp.type === 'password') { inp.type = 'text'; ico.className = 'bx bx-show'; }
      else { inp.type = 'password'; ico.className = 'bx bx-hide'; }
    }
  </script>
</body>
</html>
