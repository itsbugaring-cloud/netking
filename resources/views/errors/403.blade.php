<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 — Akses Ditolak | NETKING</title>
<!-- Load Spline Viewer Script -->
<script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.0/build/spline-viewer.js"></script>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  :root{
    --bg:#f6f8fc;--surface:#fff;--surface-2:#fefdf8;--border:#e5e7eb;--txt:#0f172a;--txt-2:#475569;--txt-3:#64748b;--amber:#d97706;--amber-soft:rgba(217,119,6,0.06);--blue:#2563eb;--shadow:0 30px 70px rgba(15,23,42,.08);
  }
  body{font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;min-height:100vh;background:radial-gradient(circle at top, rgba(245,158,11,.08), transparent 45%),linear-gradient(180deg,#fffdf8 0%,#f6f8fc 100%);color:var(--txt);display:flex;align-items:center;justify-content:center;padding:24px}
  .shell{width:min(100%,560px);background:linear-gradient(180deg,var(--surface) 0%,var(--surface-2) 100%);border:1px solid var(--border);border-radius:32px;box-shadow:var(--shadow);overflow:hidden;animation:nk-pop-in .5s cubic-bezier(.2,.8,.2,1) both}
  .error-container{padding:40px 32px;text-align:center;display:flex;flex-direction:column;align-items:center}
  
  .brand{font-size:.8rem;font-weight:900;letter-spacing:.2em;text-transform:uppercase;color:var(--txt-3);margin-bottom:15px}
  
  /* Spline Wrapper */
  .spline-wrapper {
    width: 100%;
    height: 280px;
    margin: 15px 0 25px;
    position: relative;
    border-radius: 20px;
    background: var(--amber-soft);
    overflow: hidden;
    border: 1px dashed rgba(217, 119, 6, 0.15);
  }
  spline-viewer {
    width: 100%;
    height: 100%;
  }

  @keyframes nk-pop-in {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  .title{font-size:1.6rem;font-weight:800;margin:15px 0 8px;color:var(--txt)}
  .msg{font-size:.92rem;line-height:1.6;color:var(--txt-2);max-width:42ch;margin-bottom:28px}
  .actions{display:flex;gap:12px;justify-content:center;width:100%}
  .btn{height:42px;padding:0 24px;border-radius:14px;text-decoration:none;font-size:.87rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;transition:all .18s ease;border:1px solid transparent}
  .btn-primary{background:var(--blue);color:#fff;box-shadow:0 8px 20px rgba(37,99,235,.16)}
  .btn-primary:hover{transform:translateY(-1px);background:#1d4ed8;box-shadow:0 10px 24px rgba(37,99,235,.24)}
  .btn-ghost{background:#fff;color:var(--txt-2);border-color:var(--border)}
  .btn-ghost:hover{background:#f8fafc;color:var(--txt);border-color:#cbd5e1}
  
  @media (max-width:576px){
    .shell{border-radius:24px;width:100%}
    .error-container{padding:32px 20px}
    .actions{flex-direction:column}
    .btn{width:100%}
  }
</style>
</head>
<body>
  <div class="shell">
    <div class="error-container">
      <div class="brand">NETKING</div>
      
      <!-- Spline 3D Scene -->
      <div class="spline-wrapper">
        <!-- MASUKKAN URL SCENE SPLINE KAMU DI SINI -->
        <spline-viewer url="https://prod.spline.design/cop32OkaP0aYJb6R/scene.splinecode"></spline-viewer>
      </div>

      <h1 class="title">Akses Ditolak</h1>
      <p class="msg">Maaf, akun Anda tidak memiliki izin untuk melihat halaman ini.</p>
      
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Buka Dashboard</a>
        <a href="javascript:history.back()" class="btn btn-ghost">Kembali</a>
      </div>
    </div>
  </div>
</body>
</html>
