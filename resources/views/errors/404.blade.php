<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Halaman Tidak Ditemukan | NETKING ISP</title>
<script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.3/build/spline-viewer.js"></script>
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;overflow:hidden;position:relative;}
  
  .spline-wrapper {
      position: absolute;
      inset: 0;
      z-index: 1;
      opacity: 0.8; /* Slightly dim the background */
  }

  spline-viewer {
    width: 100%;
    height: 100%;
  }

  .wrap{
    text-align:center;
    max-width:550px;
    z-index: 2;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    padding: 50px 40px;
    border-radius: 32px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.6);
    animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  @keyframes fadeUp {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
  }

  .nk-logo{font-size:1.1rem;font-weight:900;letter-spacing:.1em;color:#6366f1;margin-bottom:20px;text-transform:uppercase;}
  .nk-logo span{color:#f1f5f9;}

  .code{font-size:8rem;font-weight:900;line-height:1;background:linear-gradient(135deg,#818cf8,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:8px;filter:drop-shadow(0 10px 20px rgba(99,102,241,0.2));}
  
  .title{font-size:1.8rem;font-weight:800;margin:0 0 14px;color:#f8fafc;letter-spacing:-0.03em;}
  
  .msg{color:#cbd5e1;font-size:1rem;line-height:1.65;margin-bottom:36px;font-weight:400;}
  
  .actions{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;}
  
  .btn{padding:14px 28px;border-radius:100px;font-weight:700;font-size:.9rem;text-decoration:none;transition:all .25s ease;display:inline-flex;align-items:center;gap:8px;}
  .btn-primary{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);border:1px solid rgba(255,255,255,0.1);}
  .btn-primary:hover{transform:translateY(-3px);box-shadow: 0 12px 25px rgba(99, 102, 241, 0.4);filter:brightness(1.1);}
  .btn-ghost{background:rgba(255,255,255,.05);color:#f1f5f9;border:1px solid rgba(255,255,255,.1);}
  .btn-ghost:hover{background:rgba(255,255,255,.12);color:#fff;transform:translateY(-3px);border-color:rgba(255,255,255,.2);}
  
  @media(max-width: 480px) {
    .wrap { padding: 40px 24px; }
    .code { font-size: 6rem; }
    .title { font-size: 1.5rem; }
  }
</style>
</head>
<body>

<!-- 3D Spline Animation Robot -->
<div class="spline-wrapper">
  <spline-viewer url="https://prod.spline.design/6Wq1Q7YGyM-iab9i/scene.splinecode"></spline-viewer>
</div>

<div class="wrap">
  <div class="nk-logo">NET<span>KING</span> ISP</div>
  <div class="code">404</div>
  <div class="title">Waduh! Kesasar ya?</div>
  <div class="msg">Sepertinya halaman yang kamu cari tidak ada di sistem kami, atau mungkin sudah dihapus. Mari kembali ke jalan yang benar.</div>
  <div class="actions">
    <a href="{{ url('/admin/dashboard') }}" class="btn btn-primary">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      Ke Dashboard
    </a>
    <a href="javascript:history.back()" class="btn btn-ghost">Kembali</a>
  </div>
</div>

</body>
</html>
