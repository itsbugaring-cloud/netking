<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>500 — Server Error | NETKING ISP</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
  .wrap{text-align:center;max-width:500px;}
  .code{font-size:8rem;font-weight:900;line-height:1;background:linear-gradient(135deg,#ef4444,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
  .title{font-size:1.5rem;font-weight:700;margin:12px 0 8px;color:#f1f5f9;}
  .msg{color:#94a3b8;font-size:.9375rem;line-height:1.6;margin-bottom:32px;}
  .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}
  .btn{padding:10px 24px;border-radius:10px;font-weight:600;font-size:.875rem;text-decoration:none;transition:all .2s;}
  .btn-primary{background:#6366f1;color:#fff;}
  .btn-primary:hover{background:#4f46e5;color:#fff;}
  .btn-ghost{background:rgba(255,255,255,.08);color:#94a3b8;border:1px solid rgba(255,255,255,.1);}
  .btn-ghost:hover{background:rgba(255,255,255,.12);color:#f1f5f9;}
  .icon{font-size:4rem;margin-bottom:8px;opacity:.3;}
  .nk-logo{font-size:1rem;font-weight:800;letter-spacing:.05em;color:#6366f1;margin-bottom:40px;}
  .nk-logo span{color:#f1f5f9;}
</style>
</head>
<body>
<div class="wrap">
  <div class="nk-logo">NET<span>KING</span> ISP</div>
  <div class="icon">⚡</div>
  <div class="code">500</div>
  <div class="title">Terjadi Kesalahan Server</div>
  <div class="msg">Server mengalami masalah internal. Tim kami sudah diberitahu.<br>Coba refresh halaman atau kembali beberapa saat lagi.</div>
  <div class="actions">
    <a href="/admin/dashboard" class="btn btn-primary">← Dashboard</a>
    <a href="javascript:location.reload()" class="btn btn-ghost">Refresh</a>
  </div>
</div>
</body>
</html>
<?php /**PATH /var/www/netking.id/resources/views/errors/500.blade.php ENDPATH**/ ?>