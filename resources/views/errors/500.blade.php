<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>500 — Gangguan Server | NETKING</title>
<!-- Load Spline Viewer Script -->
<script type="module" src="https://cdn.jsdelivr.net/npm/@splinetool/viewer@1.9.0/build/spline-viewer.js"></script>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body {
    font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    min-height: 100vh;
    height: 100vh;
    overflow: hidden;
    position: relative;
    background: #f1f5f9;
  }
  .spline-container {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
  }
  spline-viewer {
    width: 100%;
    height: 100%;
    display: block;
  }
  .overlay-content {
    position: absolute;
    inset: 0;
    z-index: 10;
    pointer-events: none;
    display: flex;
    align-items: center;
    padding: 60px;
  }
  .glass-card {
    pointer-events: auto;
    width: 100%;
    max-width: 440px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px) saturate(190%);
    -webkit-backdrop-filter: blur(20px) saturate(190%);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 28px;
    padding: 40px 36px;
    box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08), 
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
    display: flex;
    flex-direction: column;
    animation: nk-pop-in .6s cubic-bezier(.34, 1.56, 0.64, 1) both;
  }
  .brand {
    font-size: .8rem;
    font-weight: 900;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 20px;
  }
  .title {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 12px;
    color: #0f172a;
    line-height: 1.25;
  }
  .msg {
    font-size: .95rem;
    line-height: 1.6;
    color: #334155;
    margin-bottom: 32px;
  }
  .actions {
    display: flex;
    gap: 12px;
    width: 100%;
  }
  .btn {
    flex: 1;
    height: 48px;
    padding: 0 24px;
    border-radius: 16px;
    text-decoration: none;
    font-size: .9rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
  }
  .btn-primary {
    background: #2563eb;
    color: #fff;
    box-shadow: 0 8px 20px rgba(37,99,235,.2);
  }
  .btn-primary:hover {
    transform: translateY(-2px);
    background: #1d4ed8;
    box-shadow: 0 12px 24px rgba(37,99,235,.3);
  }
  .btn-ghost {
    background: rgba(255, 255, 255, 0.5);
    color: #334155;
    border-color: rgba(15, 23, 42, 0.08);
  }
  .btn-ghost:hover {
    background: rgba(255, 255, 255, 0.8);
    color: #0f172a;
    border-color: rgba(15, 23, 42, 0.15);
    transform: translateY(-2px);
  }
  @keyframes nk-pop-in {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }
  @media (max-width: 768px) {
    .overlay-content {
      padding: 24px;
      align-items: flex-end;
      justify-content: center;
    }
    .glass-card {
      max-width: 100%;
      padding: 32px 24px;
    }
    .title {
      font-size: 1.7rem;
    }
    .actions {
      flex-direction: column;
    }
    .btn {
      width: 100%;
    }
  }
</style>
</head>
<body>
  <!-- Spline 3D Scene Background -->
  <div class="spline-container">
    <spline-viewer url="https://prod.spline.design/PyzDhpQ9E5f1E3MT/scene.splinecode" loading="eager"></spline-viewer>
  </div>

  <!-- Overlay Text & Buttons -->
  <div class="overlay-content">
    <div class="glass-card">
      <div class="brand">NETKING</div>
      <h1 class="title">Gangguan Server</h1>
      <p class="msg">Terjadi kesalahan internal pada server kami. Silakan coba lagi nanti.</p>
      
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Kembali ke Dashboard</a>
        <a href="javascript:location.reload()" class="btn btn-ghost">Refresh Halaman</a>
      </div>
    </div>
  </div>

  <!-- Script to Remove Spline Watermark -->
  <script>
    const hideSplineLogo = () => {
      const viewer = document.querySelector('spline-viewer');
      if (viewer && viewer.shadowRoot) {
        if (!viewer.shadowRoot.querySelector('#hide-logo-style')) {
          const style = document.createElement('style');
          style.id = 'hide-logo-style';
          style.textContent = `
            #logo, #ar, a[href*="spline.design"] {
              display: none !important;
              opacity: 0 !important;
              visibility: hidden !important;
              pointer-events: none !important;
            }
          `;
          viewer.shadowRoot.appendChild(style);
        }
      }
    };

    const interval = setInterval(hideSplineLogo, 50);
    window.addEventListener('DOMContentLoaded', hideSplineLogo);
    window.addEventListener('load', hideSplineLogo);
    setTimeout(() => clearInterval(interval), 10000);
  </script>
</body>
</html>
