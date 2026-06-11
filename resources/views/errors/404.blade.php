<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Halaman Tidak Ditemukan | NETKING ISP</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  :root{
    --bg:#f6f8fc;--surface:#fff;--surface-2:#f8fbff;--border:#e5e7eb;--txt:#0f172a;--txt-2:#475569;--txt-3:#64748b;--blue:#2563eb;--blue-soft:#eff6ff;--shadow:0 30px 70px rgba(15,23,42,.10);
  }
  body{font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;min-height:100vh;background:radial-gradient(circle at top left, rgba(59,130,246,.10), transparent 34%),linear-gradient(180deg,#f8fbff 0%,#f6f8fc 100%);color:var(--txt);display:flex;align-items:center;justify-content:center;padding:24px}
  .shell{width:min(100%,760px);background:linear-gradient(180deg,var(--surface) 0%,var(--surface-2) 100%);border:1px solid var(--border);border-radius:28px;box-shadow:var(--shadow);overflow:hidden}
  @keyframes nk-pop-in{from{opacity:0;transform:translateY(18px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
  @keyframes nk-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
  @keyframes nk-glow{0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.00)}50%{box-shadow:0 0 0 12px rgba(37,99,235,.05)}}
  .shell{animation:nk-pop-in .45s cubic-bezier(.2,.8,.2,1) both}
  .hero{padding:26px 28px 20px;border-bottom:1px solid var(--border)}
  .brand{font-size:.78rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--txt-3);margin-bottom:14px}
  .title-row{display:flex;align-items:flex-start;gap:18px}
  .badge{width:60px;height:60px;border-radius:20px;display:flex;align-items:center;justify-content:center;background:var(--blue-soft);color:var(--blue);font-size:1.6rem;border:1px solid #bfdbfe;flex-shrink:0;animation:nk-float 3.8s ease-in-out infinite,nk-glow 3.8s ease-in-out infinite}
  .code{font-size:4.8rem;line-height:.9;font-weight:900;color:var(--blue);letter-spacing:-.05em;animation:nk-pop-in .58s cubic-bezier(.2,.8,.2,1) .03s both}
  .title{font-size:1.6rem;font-weight:800;margin:6px 0 8px}
  .msg{font-size:.95rem;line-height:1.7;color:var(--txt-2);max-width:48ch}
  .body{padding:24px 28px 28px}
  .panel{border:1px solid var(--border);border-radius:20px;background:#fff;padding:18px 18px 16px;margin-bottom:18px}
  .panel-kicker{font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--txt-3);margin-bottom:10px}
  .panel p{font-size:.9rem;line-height:1.7;color:var(--txt-2)}
  .actions{display:flex;gap:12px;flex-wrap:wrap}
  .btn{height:42px;padding:0 18px;border-radius:14px;text-decoration:none;font-size:.87rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;transition:transform .18s ease,box-shadow .18s ease,background .18s ease;border:1px solid transparent}
  .btn-primary{background:var(--blue);color:#fff;box-shadow:0 10px 24px rgba(37,99,235,.16)}
  .btn-primary:hover{transform:translateY(-1px);background:#1d4ed8;color:#fff}
  .btn-ghost{background:#fff;color:var(--txt-2);border-color:var(--border)}
  .btn-ghost:hover{background:#f8fafc;color:var(--txt)}
  .panel{animation:nk-pop-in .5s cubic-bezier(.2,.8,.2,1) .08s both}
  .title,.msg{animation:nk-pop-in .5s cubic-bezier(.2,.8,.2,1) .12s both}
  @media (max-width:640px){.hero,.body{padding:20px}.title-row{flex-direction:column}.code{font-size:4rem}.actions{flex-direction:column}.btn{width:100%}}
</style>
</head>
<body>
  <div class="shell">
    <div class="hero">
      <div class="brand">NETKING ISP · Route Missing</div>
      <div class="title-row">
        <div class="badge">🌐</div>
        <div>
          <div class="code">404</div>
          <div class="title">Halaman yang kamu cari tidak ada</div>
          <div class="msg">Alamat ini tidak ditemukan di sistem. Bisa jadi link-nya salah, sudah dipindah, atau halaman tersebut memang sudah tidak aktif lagi.</div>
        </div>
      </div>
    </div>
    <div class="body">
      <div class="panel">
        <div class="panel-kicker">Yang Bisa Dicoba</div>
        <p>Periksa kembali URL, kembali ke halaman sebelumnya, atau buka dashboard untuk mulai lagi dari menu utama.</p>
      </div>
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Buka Dashboard</a>
        <a href="javascript:history.back()" class="btn btn-ghost">Kembali</a>
      </div>
    </div>
  </div>
</body>
</html>
