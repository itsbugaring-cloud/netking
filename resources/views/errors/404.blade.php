<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Halaman Tidak Ditemukan | NETKING</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  :root{
    --bg:#f6f8fc;--surface:#fff;--surface-2:#f8fbff;--border:#e5e7eb;--txt:#0f172a;--txt-2:#475569;--txt-3:#64748b;--blue:#2563eb;--blue-soft:rgba(37,99,235,0.06);--shadow:0 30px 70px rgba(15,23,42,.08);
  }
  body{font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;min-height:100vh;background:radial-gradient(circle at top, rgba(59,130,246,.08), transparent 45%),linear-gradient(180deg,#f8fbff 0%,#f6f8fc 100%);color:var(--txt);display:flex;align-items:center;justify-content:center;padding:24px}
  .shell{width:min(100%,560px);background:linear-gradient(180deg,var(--surface) 0%,var(--surface-2) 100%);border:1px solid var(--border);border-radius:32px;box-shadow:var(--shadow);overflow:hidden;animation:nk-pop-in .5s cubic-bezier(.2,.8,.2,1) both}
  .error-container{padding:40px 32px;text-align:center;display:flex;flex-direction:column;align-items:center}
  
  .brand{font-size:.8rem;font-weight:900;letter-spacing:.2em;text-transform:uppercase;color:var(--txt-3);margin-bottom:15px}
  
  /* 3D Cube animation */
  .cube-container {
    animation: cubeBob 4s ease-in-out infinite;
    margin: 20px 0;
  }
  .cube-wrapper {
    perspective: 800px;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .cube {
    width: 80px;
    height: 80px;
    position: relative;
    transform-style: preserve-3d;
    transform: rotateX(-22deg) rotateY(45deg);
    animation: rotateCube 12s infinite linear;
  }
  .face {
    position: absolute;
    width: 80px;
    height: 80px;
    background: rgba(37, 99, 235, 0.08);
    border: 2px solid var(--blue);
    color: var(--blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    font-weight: 900;
    box-shadow: inset 0 0 15px rgba(37, 99, 235, 0.2);
    border-radius: 12px;
    backdrop-filter: blur(2px);
  }
  .face.front  { transform: rotateY(0deg) translateZ(40px); }
  .face.back   { transform: rotateY(180deg) translateZ(40px); }
  .face.right  { transform: rotateY(90deg) translateZ(40px); }
  .face.left   { transform: rotateY(-90deg) translateZ(40px); }
  .face.top    { transform: rotateX(90deg) translateZ(40px); background: rgba(37, 99, 235, 0.04); }
  .face.bottom { transform: rotateX(-90deg) translateZ(40px); background: rgba(37, 99, 235, 0.04); }

  .cube-shadow {
    width: 80px;
    height: 8px;
    background: rgba(37, 99, 235, 0.12);
    border-radius: 50%;
    margin: 5px auto 0;
    filter: blur(4px);
    animation: shadowPulse 4s ease-in-out infinite;
  }

  @keyframes rotateCube {
    0% { transform: rotateX(-22deg) rotateY(0deg); }
    100% { transform: rotateX(-22deg) rotateY(360deg); }
  }
  @keyframes cubeBob {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
  }
  @keyframes shadowPulse {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.2); opacity: 0.4; }
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
      
      <div class="cube-container">
        <div class="cube-wrapper">
          <div class="cube">
            <div class="face front">404</div>
            <div class="face back">404</div>
            <div class="face right">🔍</div>
            <div class="face left">🔍</div>
            <div class="face top"></div>
            <div class="face bottom"></div>
          </div>
        </div>
        <div class="cube-shadow"></div>
      </div>

      <h1 class="title">Halaman Tidak Ditemukan</h1>
      <p class="msg">Alamat URL yang Anda tuju tidak valid, telah dihapus, atau sedang dipindahkan.</p>
      
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Buka Dashboard</a>
        <a href="javascript:history.back()" class="btn btn-ghost">Kembali</a>
      </div>
    </div>
  </div>
</body>
</html>
