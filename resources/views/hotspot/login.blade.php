<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotspot Login - Netking</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --blue:#2563eb; --blue-d:#1d4ed8; --bg:#f8fafc; --surface:#ffffff; --border:#e2e8f0;
      --text:#1e293b; --muted:#64748b; --danger:#b42318; --ok:#15803d;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text)}
    .wrap{min-height:100vh;display:grid;place-items:center;padding:24px}
    .card{width:min(100%,420px);background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:24px 22px;box-shadow:0 10px 30px rgba(15,23,42,.08)}
    .logo{display:block;height:64px;margin:0 auto 10px auto;object-fit:contain}
    h1{margin:0 0 8px 0;font-size:1.35rem;letter-spacing:-.01em}
    .sub{margin:0 0 18px 0;font-size:.86rem;color:var(--muted);line-height:1.55}
    .info{display:grid;gap:6px;margin:0 0 16px 0;padding:10px 12px;background:#f8fafc;border:1px solid var(--border);border-radius:10px;font-size:.78rem;color:#475569}
    .err{margin:0 0 14px 0;padding:10px 12px;border:1px solid #fecaca;background:#fff1f2;color:var(--danger);border-radius:10px;font-size:.8rem}
    label{display:block;font-size:.8rem;font-weight:600;margin-bottom:6px}
    input{width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:10px;font-size:.92rem;outline:none}
    input:focus{border-color:#93c5fd;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
    .row{margin-bottom:12px}
    .btn{width:100%;border:0;background:var(--blue);color:#fff;font-weight:700;padding:12px 14px;border-radius:10px;cursor:pointer}
    .btn:hover{background:var(--blue-d)}
    .note{margin-top:14px;font-size:.76rem;color:var(--muted);text-align:center}
    .ok{color:var(--ok);font-weight:600}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <img class="logo" src="{{ asset('img/NetkingLoginBaruLight.png') }}" alt="Netking">
      <h1>Login Hotspot</h1>
      <p class="sub">Masukkan kode voucher hotspot untuk mengaktifkan koneksi internet Anda.</p>

      @if(!empty($error))
      <div class="err">Gagal login: {{ $error }}</div>
      @endif

      @if(!empty($ip) || !empty($mac))
      <div class="info">
        @if(!empty($ip))<div><strong>IP:</strong> {{ $ip }}</div>@endif
        @if(!empty($mac))<div><strong>MAC:</strong> {{ $mac }}</div>@endif
      </div>
      @endif

      <form action="{{ $linkLogin ?: '#' }}" method="POST">
        <input type="hidden" name="dst" value="{{ $linkOrig }}">
        <input type="hidden" name="popup" value="true">
        <div class="row">
          <label for="username">Kode Voucher</label>
          <input id="username" name="username" type="text" autocomplete="username" required placeholder="Contoh: ABCD1234">
        </div>
        <div class="row">
          <label for="password">Password Voucher</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Biasanya sama dengan kode voucher">
        </div>
        <button class="btn" type="submit">Masuk Hotspot</button>
      </form>

      <div class="note">
        Butuh bantuan? Hubungi admin Netking.<br>
        Status jaringan: <span class="ok">online</span>
      </div>
    </div>
  </div>
</body>
</html>

