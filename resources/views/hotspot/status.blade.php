<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status Hotspot - Netking</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --blue:#2563eb; --bg:#f8fafc; --surface:#ffffff; --border:#e2e8f0;
      --text:#1e293b; --muted:#64748b; --ok:#15803d;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text)}
    .wrap{min-height:100vh;display:grid;place-items:center;padding:24px}
    .card{width:min(100%,500px);background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:24px 22px;box-shadow:0 10px 30px rgba(15,23,42,.08)}
    h1{margin:0 0 6px 0;font-size:1.38rem;letter-spacing:-.01em}
    .sub{margin:0 0 14px 0;font-size:.86rem;color:var(--muted);line-height:1.55}
    .ok{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border-radius:999px;background:#f0fdf4;border:1px solid #bbf7d0;color:var(--ok);font-size:.8rem;font-weight:700}
    .dot{width:8px;height:8px;border-radius:50%;background:#22c55e}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}
    .item{padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .k{font-size:.74rem;color:var(--muted)}
    .v{margin-top:2px;font-size:.9rem;font-weight:700;color:var(--text)}
    .actions{display:flex;gap:10px;margin-top:16px;flex-wrap:wrap}
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 14px;border-radius:10px;border:0;background:var(--blue);color:#fff;font-weight:700;text-decoration:none;cursor:pointer}
    .btn-ghost{background:#fff;color:var(--text);border:1px solid var(--border)}
    @media(max-width:520px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Status Koneksi Hotspot</h1>
      <p class="sub">Koneksi Anda aktif. Informasi sesi ditampilkan di bawah ini.</p>
      <div class="ok"><span class="dot"></span> Terhubung</div>

      <div class="grid">
        <div class="item"><div class="k">Username</div><div class="v">{{ $username ?: '-' }}</div></div>
        <div class="item"><div class="k">IP</div><div class="v">{{ $ip ?: '-' }}</div></div>
        <div class="item"><div class="k">MAC</div><div class="v">{{ $mac ?: '-' }}</div></div>
        <div class="item"><div class="k">Uptime</div><div class="v">{{ $uptime ?: '-' }}</div></div>
        <div class="item"><div class="k">Download</div><div class="v">{{ $bytesIn ?: '-' }}</div></div>
        <div class="item"><div class="k">Upload</div><div class="v">{{ $bytesOut ?: '-' }}</div></div>
      </div>

      <div class="actions">
        <a class="btn-ghost btn" href="{{ url('/') }}">Kembali ke Website</a>
        @if(!empty($linkLogout))
        <a class="btn" href="{{ $linkLogout }}">Putuskan Koneksi</a>
        @endif
      </div>
    </div>
  </div>
</body>
</html>

