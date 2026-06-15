<!doctype html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0" />
  <title>Masuk</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Boxicons -->
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    :root {
      --sneat-primary: #696cff;
      --sneat-primary-dark: #5f61e6;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #f8fafc;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0; width: 100vw; height: 100vh;
      background: 
        radial-gradient(1000px circle at 15% 30%, rgba(91,99,211,0.06), transparent 100%),
        radial-gradient(1000px circle at 85% 20%, rgba(22,163,74,0.04), transparent 100%),
        radial-gradient(1000px circle at 50% 80%, rgba(249,115,22,0.04), transparent 100%);
      z-index: -1; pointer-events: none;
    }

    .login-wrapper {
      width: 100%;
      max-width: 420px;
    }

    .login-brand {
      text-align: center;
      margin-bottom: 1.75rem;
    }

    .login-brand-logo {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      background: var(--sneat-primary);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 2rem;
      margin-bottom: 0.875rem;
    }

    .login-brand-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--sneat-primary);
      letter-spacing: -0.5px;
      margin: 0;
    }

    .login-brand-subtitle {
      font-size: 0.875rem;
      color: #a5b7cf;
      margin: 0;
    }

    .login-card {
      background: linear-gradient(145deg, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.85) 100%);
      backdrop-filter: blur(24px);
      border-radius: 1.25rem;
      box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,1);
      border: 1px solid rgba(255,255,255,0.7);
      padding: 2.5rem;
      position: relative;
    }
    
    .login-card::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(400px circle at var(--mouse-x, 0) var(--mouse-y, 0), rgba(91,99,211,0.04), transparent 40%);
      opacity: 0;
      transition: opacity 0.4s ease;
      pointer-events: none;
      z-index: 0;
      border-radius: 1.25rem;
    }

    .login-card:hover::after { opacity: 1; }

    .login-card h4 {
      font-size: 1.375rem;
      font-weight: 700;
      color: #566a7f;
      margin-bottom: 0.25rem;
    }

    .login-card p {
      color: #a5b7cf;
      font-size: 0.875rem;
      margin-bottom: 1.5rem;
    }

    .form-label {
      font-size: 0.8125rem;
      font-weight: 500;
      color: #566a7f;
      margin-bottom: 0.375rem;
    }

    .form-control {
      border-color: #dbdade;
      border-radius: 0.375rem;
      padding: 0.5rem 0.875rem;
      font-size: 0.9375rem;
      color: #566a7f;
    }

    .form-control:focus {
      border-color: var(--sneat-primary);
      box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.16);
    }

    .input-group-text {
      background: transparent;
      border-color: #dbdade;
      cursor: pointer;
      color: #a5b7cf;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--sneat-primary) 0%, var(--sneat-primary-dark) 100%);
      border: none;
      color: #fff;
      font-weight: 600;
      padding: 0.625rem 1rem;
      border-radius: 0.5rem;
      font-size: 0.9375rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 12px rgba(105, 108, 255, 0.2);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(105, 108, 255, 0.35);
      background: linear-gradient(135deg, #7478ff 0%, #5f61e6 100%);
    }

    .alert-danger {
      background: #fde7e7;
      border: none;
      border-radius: 0.5rem;
      color: #e74c3c;
      font-size: 0.875rem;
      padding: 0.75rem 1rem;
    }

    .form-check-input:checked {
      background-color: var(--sneat-primary);
      border-color: var(--sneat-primary);
    }

    .login-footer {
      text-align: center;
      margin-top: 1.25rem;
      font-size: 0.8125rem;
      color: #a5b7cf;
    }
  </style>
</head>

<body>
  <div class="login-wrapper">

    <!-- Brand -->
    <div class="login-brand">
      <div class="login-brand-logo">
        <i class='bx bx-wifi'></i>
      </div>
      <h1 class="login-brand-title">NETKING</h1>
      <p class="login-brand-subtitle">Sistem Manajemen ISP</p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
      <h4>Selamat datang kembali! 👋</h4>
      <p>Silakan masuk ke akun Anda</p>

      @if($errors->any())
      <div class="alert alert-danger mb-3">
        <i class='bx bx-error-circle me-2'></i>
        {{ $errors->first() }}
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger mb-3">
        <i class='bx bx-error-circle me-2'></i>
        {{ session('error') }}
      </div>
      @endif

      <form action="{{ route('admin.login.post') }}" method="POST" autocomplete="off">
        @csrf

        <!-- Email -->
        <div class="mb-3">
          <label class="form-label" for="email">Alamat Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="admin@netking.id"
            value="{{ old('email') }}"
            autocomplete="username"
            required />
          @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <label class="form-label" for="password">Password</label>
          </div>
          <div class="input-group">
            <input
              type="password"
              id="password"
              name="password"
              class="form-control @error('password') is-invalid @enderror"
              placeholder="············"
              autocomplete="current-password"
              required />
            <button type="button" class="input-group-text" onclick="togglePassword()" title="Lihat password" style="cursor:pointer;background:#fff;border-left:1px solid #d0d7e2;padding:0 14px;">
              <i class='bx bx-hide' id="eye-icon" style="font-size:1.1rem;color:#697a8d;"></i>
            </button>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" />
            <label class="form-check-label" for="remember" style="font-size:0.875rem; color:#697a8d;">
              Ingat saya
            </label>
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-100">
          <i class='bx bx-log-in me-2'></i> Masuk
        </button>

      </form>
    </div>

    <div class="login-footer">
      &copy; {{ date('Y') }} NETKING. Hak cipta dilindungi.
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePassword() {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'bx bx-show';
      } else {
        pwd.type = 'password';
        icon.className = 'bx bx-hide';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const card = document.querySelector('.login-card');
      if(card) {
        card.addEventListener('mousemove', function(e) {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          card.style.setProperty('--mouse-x', x + 'px');
          card.style.setProperty('--mouse-y', y + 'px');
        });
      }
    });
  </script>
</body>

</html>
