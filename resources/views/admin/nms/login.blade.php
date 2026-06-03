<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
      background: #f8fafc;
      -webkit-font-smoothing: antialiased;
    }

    /* ======= LEFT PANEL (Brand) ======= */
    .auth-left {
      flex: 0 0 45%;
      background: #1e293b;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 3rem;
    }

    .auth-left-content {
      position: relative;
      z-index: 1;
      text-align: center;
      max-width: 360px;
    }

    .auth-left-icon {
      width: 72px;
      height: 72px;
      background: #2563eb;
      border-radius: 18px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 32px;
      margin-bottom: 1.5rem;
    }

    .auth-left-brand {
      font-size: 2rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.5px;
      margin-bottom: .5rem;
    }

    .auth-left-sub {
      font-size: .9375rem;
      color: #94a3b8;
      line-height: 1.6;
      margin-bottom: 2rem;
    }

    .auth-left-features {
      text-align: left;
      display: flex;
      flex-direction: column;
      gap: .75rem;
    }

    .auth-feature {
      display: flex;
      align-items: center;
      gap: .75rem;
      color: #cbd5e1;
      font-size: .8125rem;
    }

    .auth-feature i {
      width: 32px;
      height: 32px;
      background: rgba(37, 99, 235, .2);
      color: #60a5fa;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .auth-version {
      position: absolute;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      font-size: .6875rem;
      color: #475569;
      z-index: 1;
    }

    /* ======= RIGHT PANEL (Form) ======= */
    .auth-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .auth-form-wrap {
      width: 100%;
      max-width: 380px;
    }

    .auth-welcome {
      font-size: 1.375rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: .375rem;
    }

    .auth-welcome-sub {
      font-size: .875rem;
      color: #64748b;
      margin-bottom: 1.75rem;
    }

    .auth-label {
      font-size: .75rem;
      font-weight: 600;
      color: #475569;
      margin-bottom: .375rem;
      display: block;
    }

    .auth-input-wrap {
      position: relative;
      margin-bottom: 1rem;
    }

    .auth-input-wrap i.input-icon {
      position: absolute;
      left: .75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 1rem;
      pointer-events: none;
    }

    .auth-input {
      display: block;
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: .625rem .75rem .625rem 2.5rem;
      font-size: .8125rem;
      font-family: inherit;
      color: #1e293b;
      background: #fff;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }

    .auth-input:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .auth-input::placeholder {
      color: #cbd5e1;
    }

    .auth-pass-toggle {
      position: absolute;
      right: .75rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #94a3b8;
      font-size: 1rem;
      padding: 0;
      display: flex;
      align-items: center;
    }

    .auth-pass-toggle:hover {
      color: #64748b;
    }

    .auth-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }

    .auth-remember {
      display: flex;
      align-items: center;
      gap: .375rem;
      font-size: .75rem;
      color: #64748b;
      cursor: pointer;
    }

    .auth-remember input {
      accent-color: #2563eb;
    }

    .auth-submit {
      width: 100%;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: .6875rem;
      font-size: .875rem;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      transition: background .15s, transform .1s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
    }

    .auth-submit:hover {
      background: #1d4ed8;
    }

    .auth-submit:active {
      background: #1e40af;
      transform: scale(.99);
    }

    .auth-powered {
      margin-top: 2rem;
      font-size: .6875rem;
      color: #cbd5e1;
      text-align: center;
    }

    .auth-alert {
      border-radius: 10px;
      font-size: .75rem;
      padding: .625rem .875rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .auth-alert-danger {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
    }

    .auth-alert-success {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 768px) {
      body {
        flex-direction: column;
      }

      .auth-left {
        flex: 0 0 auto;
        padding: 2rem 1.5rem;
      }

      .auth-left-features {
        display: none;
      }

      .auth-version {
        display: none;
      }

      .auth-right {
        padding: 1.5rem;
      }
    }

    /* ======= ANIMATION ======= */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(15px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .auth-form-wrap {
      animation: fadeInUp .4s ease-out;
    }

    .auth-left-content {
      animation: fadeInUp .5s ease-out .1s both;
    }
  </style>
</head>

<body>
  <!-- LEFT PANEL -->
  <div class="auth-left">
    <div class="auth-left-content">
      <div class="auth-left-icon"><i class='bx bx-wifi'></i></div>
      <div class="auth-left-brand">NETKING</div>
      <div class="auth-left-sub">Platform manajemen ISP yang memudahkan monitoring pelanggan, billing, dan jaringan dalam satu dashboard.</div>
      <div class="auth-left-features">
        <div class="auth-feature">
          <i class='bx bx-user'></i>
          <span>Kelola pelanggan & partner</span>
        </div>
        <div class="auth-feature">
          <i class='bx bx-receipt'></i>
          <span>Billing & invoice otomatis</span>
        </div>
        <div class="auth-feature">
          <i class='bx bx-chip'></i>
          <span>Monitoring ONT & jaringan</span>
        </div>
        <div class="auth-feature">
          <i class='bx bx-bar-chart-alt-2'></i>
          <span>Dashboard & analytics real-time</span>
        </div>
      </div>
    </div>
    <div class="auth-version">v2.0 — © 2026 NETKING</div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="auth-welcome">Welcome back 👋</div>
      <div class="auth-welcome-sub">Silakan masuk ke akun admin Anda</div>

      <form action="{{ route('admin.login.post') }}" method="POST" autocomplete="off">
        @csrf

        @if($errors->any())
        <div class="auth-alert auth-alert-danger">
          <i class='bx bx-error-circle' style="font-size:1.1rem;"></i>
          <div>@foreach($errors->all() as $err){{ $err }} @endforeach</div>
        </div>
        @endif
        @if(session('status'))
        <div class="auth-alert auth-alert-success">
          <i class='bx bx-check-circle' style="font-size:1.1rem;"></i> {{ session('status') }}
        </div>
        @endif

        <div>
          <label class="auth-label" for="email">Email</label>
          <div class="auth-input-wrap">
            <i class='bx bx-envelope input-icon'></i>
            <input type="email" id="email" name="email" class="auth-input"
              value="{{ old('email') }}" placeholder="admin@erka51.com" autofocus>
          </div>
        </div>

        <div>
          <label class="auth-label" for="password">Password</label>
          <div class="auth-input-wrap">
            <i class='bx bx-lock-alt input-icon'></i>
            <input type="password" id="password" name="password"
              class="auth-input" placeholder="••••••••" style="padding-right:2.5rem;">
            <button type="button" class="auth-pass-toggle" onclick="nkTogglePass()" tabindex="-1">
              <i class='bx bx-hide' id="pass-ico"></i>
            </button>
          </div>
        </div>

        <div class="auth-options">
          <label class="auth-remember">
            <input type="checkbox" name="remember"> Remember me
          </label>
        </div>

        <button type="submit" class="auth-submit">
          <i class='bx bx-log-in'></i> Sign In
        </button>
      </form>

      <div class="auth-powered">Powered by NETKING</div>
    </div>
  </div>

  <script>
    function nkTogglePass() {
      var inp = document.getElementById('password');
      var ico = document.getElementById('pass-ico');
      if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bx bx-show';
      } else {
        inp.type = 'password';
        ico.className = 'bx bx-hide';
      }
    }
  </script>
</body>

</html>