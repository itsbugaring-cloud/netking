<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>500 — Gangguan Server | NETKING</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  :root{
    --bg:#f6f8fc;
    --surface:#ffffff;
    --surface-2:#f8fbff;
    --border:#e5e7eb;
    --txt:#0f172a;
    --txt-2:#475569;
    --txt-3:#64748b;
    --danger:#ef4444;
    --danger-soft:#fff4f4;
    --blue:#2563eb;
    --shadow:0 30px 70px rgba(15,23,42,.10);
  }
  body{
    font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    min-height:100vh;
    background:
      radial-gradient(circle at top left, rgba(59,130,246,.10), transparent 34%),
      radial-gradient(circle at top right, rgba(239,68,68,.08), transparent 30%),
      linear-gradient(180deg,#f8fbff 0%,#f6f8fc 100%);
    color:var(--txt);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
  }
  .shell{
    width:min(100%,760px);
    background:linear-gradient(180deg,var(--surface) 0%,var(--surface-2) 100%);
    border:1px solid var(--border);
    border-radius:28px;
    box-shadow:var(--shadow);
    overflow:hidden;
  }
  @keyframes nk-pop-in{from{opacity:0;transform:translateY(18px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
  @keyframes nk-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
  @keyframes nk-glow{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.00)}50%{box-shadow:0 0 0 12px rgba(239,68,68,.05)}}
  .shell{animation:nk-pop-in .45s cubic-bezier(.2,.8,.2,1) both}
  .hero{
    padding:26px 28px 20px;
    border-bottom:1px solid var(--border);
    background:
      radial-gradient(circle at top right, rgba(239,68,68,.10), transparent 42%),
      linear-gradient(180deg,#ffffff 0%,#fbfdff 100%);
  }
  .brand{
    font-size:.78rem;
    font-weight:800;
    letter-spacing:.16em;
    text-transform:uppercase;
    color:var(--txt-3);
    margin-bottom:14px;
  }
  .title-row{
    display:flex;
    align-items:flex-start;
    gap:18px;
  }
  .badge-wrapper{position:relative;width:68px;height:68px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-bottom:8px}
  .loader-ring{position:absolute;inset:0;border:3px solid var(--border);border-top-color:var(--danger);border-radius:50%;animation:nk-spin 1.2s linear infinite}
  .badge{width:54px;height:54px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--danger-soft);color:var(--danger);border:1px solid #fecaca;z-index:1;animation:nk-float-pulse 3s ease-in-out infinite}
  @keyframes nk-spin{100%{transform:rotate(360deg)}}
  @keyframes nk-float-pulse{
    0%,100%{transform:translateY(0) scale(1);box-shadow:0 4px 12px rgba(239,68,68,0.06)}
    50%{transform:translateY(-3px) scale(1.02);box-shadow:0 8px 20px rgba(239,68,68,0.12)}
  }
  @keyframes gear-spin {
    100% { transform: rotate(360deg); }
  }
  .gear-rotate {
    animation: gear-spin 8s linear infinite;
    transform-origin: 12px 12px;
  }
  .code{font-size:4.8rem;line-height:.9;font-weight:900;color:var(--danger);letter-spacing:-.05em;animation:nk-pop-in .58s cubic-bezier(.2,.8,.2,1) .03s both}
  .title{font-size:1.6rem;font-weight:800;margin:6px 0 8px}
  .msg{font-size:.95rem;line-height:1.7;color:var(--txt-2);max-width:48ch}
  .body{padding:24px 28px 28px}
  .panel{
    border:1px solid var(--border);
    border-radius:20px;
    background:#fff;
    padding:18px 18px 16px;
    margin-bottom:18px;
    animation:nk-pop-in .5s cubic-bezier(.2,.8,.2,1) .08s both;
  }
  .panel-kicker{
    font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--txt-3);margin-bottom:10px;
  }
  .panel p{font-size:.9rem;line-height:1.7;color:var(--txt-2)}
  .actions{display:flex;gap:12px;flex-wrap:wrap}
  .btn{
    height:42px;padding:0 18px;border-radius:14px;text-decoration:none;font-size:.87rem;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;transition:transform .18s ease,box-shadow .18s ease,background .18s ease;border:1px solid transparent;
  }
  .btn-primary{background:var(--blue);color:#fff;box-shadow:0 10px 24px rgba(37,99,235,.16)}
  .btn-primary:hover{transform:translateY(-1px);background:#1d4ed8;color:#fff}
  .btn-ghost{background:#fff;color:var(--txt-2);border-color:var(--border)}
  .btn-ghost:hover{background:#f8fafc;color:var(--txt)}
  .title,.msg{animation:nk-pop-in .5s cubic-bezier(.2,.8,.2,1) .12s both}
  @media (max-width:640px){
    .hero,.body{padding:20px}
    .title-row{flex-direction:column}
    .code{font-size:4rem}
    .actions{flex-direction:column}
    .btn{width:100%}
  }
  @media (prefers-reduced-motion: reduce){
    .shell,.badge,.code,.panel,.title,.msg{animation:none !important}
  }
</style>
</head>
<body>
  <div class="shell">
    <div class="hero">
      <div class="brand">NETKING · Server Alert</div>
      <div class="title-row">
        <div class="badge-wrapper">
          <div class="loader-ring"></div>
          <div class="badge">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="gear-svg">
              <circle cx="12" cy="12" r="3"></circle>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" class="gear-rotate"></path>
            </svg>
          </div>
        </div>
        <div>
          <div class="code">500</div>
          <div class="title">Server sedang bermasalah</div>
          <div class="msg">Permintaanmu sudah sampai, tapi sistem sedang mengalami gangguan internal. Biasanya ini terkait proses backend, koneksi perangkat, atau error sinkronisasi data.</div>
        </div>
      </div>
    </div>
    <div class="body">
      <div class="panel">
        <div class="panel-kicker">Yang Bisa Dicoba</div>
        <p>Refresh halaman, kembali ke dashboard, atau ulangi beberapa saat lagi. Kalau ini muncul saat sinkronisasi router atau paket, cek juga apakah perangkat tujuan sedang bisa dihubungi.</p>
      </div>
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Kembali ke Dashboard</a>
        <a href="javascript:location.reload()" class="btn btn-ghost">Refresh Halaman</a>
      </div>
    </div>
  </div>
</body>
</html>
