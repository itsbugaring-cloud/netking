<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Halaman Tidak Ditemukan | NETKING</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body {
    font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    min-height: 100vh;
    height: 100vh;
    overflow: hidden;
    position: relative;
    background: #f4f6fa;
  }
  .spline-container {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
  }
  #canvas3d {
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
    margin-bottom: 32px;
    color: #0f172a;
    line-height: 1.25;
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
      margin-bottom: 24px;
    }
    .actions {
      flex-direction: column;
    }
    .btn {
      width: 100%;
    }
  }

  /* Global Watermark Hiding */
  a[href*="spline.design"],
  a[href*="spline"],
  .spline-container + a,
  body > a {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }
</style>
</head>
<body>
  <!-- Spline 3D Scene Background Canvas -->
  <div class="spline-container">
    <canvas id="canvas3d"></canvas>
  </div>

  <!-- Overlay Text & Buttons -->
  <div class="overlay-content">
    <div class="glass-card">
      <div class="brand">NETKING</div>
      <h1 class="title">Halaman Tidak Ditemukan</h1>
      
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Buka Dashboard</a>
        <a href="javascript:history.back()" class="btn btn-ghost">Kembali</a>
      </div>
    </div>
  </div>

  <!-- Load Spline Runtime from jsDelivr and Initialize Scene -->
  <script type="module">
    import { Application } from 'https://cdn.jsdelivr.net/npm/@splinetool/runtime@1.12.96/build/runtime.js';

    const canvas = document.getElementById('canvas3d');
    const spline = new Application(canvas);
    
    spline.load('https://prod.spline.design/PyzDhpQ9E5f1E3MT/scene.splinecode')
      .then(() => {
        // Repeatedly hide floor meshes to override any internal updates
        const hideFloor = () => {
          if (spline.scene) {
            spline.scene.traverse((object) => {
              if (object.isMesh) {
                const name = (object.name || '').toLowerCase();
                if (
                  name.includes('floor') ||
                  name.includes('ground') ||
                  name.includes('base') ||
                  name.includes('table') ||
                  name.includes('plane') ||
                  name.includes('grid') ||
                  name.includes('platform') ||
                  name.includes('desk') ||
                  name.includes('stage') ||
                  name.includes('rect') ||
                  name.includes('tri') ||
                  name.includes('poly') ||
                  name.includes('shape') ||
                  name.includes('extrusion') ||
                  name.includes('bg') ||
                  name.includes('backdrop') ||
                  name.includes('shadow')
                ) {
                  object.visible = false;
                  if (object.scale) {
                    object.scale.set(0, 0, 0);
                  }
                }
              }
            });
          }
        };

        hideFloor();
        // Run repeatedly for the first 5 seconds to ensure it stays hidden
        const floorInterval = setInterval(hideFloor, 100);
        setTimeout(() => clearInterval(floorInterval), 5000);
      })
      .catch(err => {
        console.error('Failed to load Spline scene:', err);
      });
  </script>
</body>
</html>
