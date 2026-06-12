<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>500 — Gangguan Server | NETKING</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body {
    font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    min-height: 100vh;
    height: 100vh;
    overflow: hidden;
    position: relative;
    background: radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 45%), linear-gradient(180deg, #f8fbff 0%, #f4f6fa 100%);
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
  
  /* Visual Loader Spinner Overlay */
  .loader-container {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f4f6fa;
    z-index: 5;
    transition: opacity 0.4s ease, visibility 0.4s ease;
  }
  .loader {
    width: 48px;
    height: 48px;
    border: 5px solid rgba(37, 99, 235, 0.15);
    border-bottom-color: #2563eb;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
  }
  @keyframes rotation {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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
    <!-- Loader Spinner equivalent to Suspense fallback -->
    <div class="loader-container" id="spline-loader">
      <span class="loader"></span>
    </div>
    <canvas id="canvas3d"></canvas>
  </div>

  <!-- Overlay Text & Buttons -->
  <div class="overlay-content">
    <div class="glass-card">
      <div class="brand">NETKING</div>
      <h1 class="title">Gangguan Server</h1>
      
      <div class="actions">
        <a href="/admin/dashboard" class="btn btn-primary">Kembali ke Dashboard</a>
        <a href="javascript:location.reload()" class="btn btn-ghost">Refresh Halaman</a>
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
        // Hide loader smoothly once loading is complete
        const loader = document.getElementById('spline-loader');
        if (loader) {
          loader.style.opacity = '0';
          loader.style.visibility = 'hidden';
          setTimeout(() => loader.remove(), 400);
        }

        // Set spline scene background to transparent
        if (spline.scene) {
          spline.scene.background = null;
        }
        if (spline.renderer) {
          spline.renderer.setClearAlpha(0);
        }

        // Periodically remove watermark elements and hide floor meshes
        const cleanupScene = () => {
          // 1. Remove any absolute-positioned watermark links in DOM
          document.querySelectorAll('a').forEach(el => {
            if (el.href && el.href.includes('spline')) {
              el.remove();
            }
          });
          document.querySelectorAll('div').forEach(el => {
            if (el.textContent && el.textContent.includes('Spline')) {
              const style = window.getComputedStyle(el);
              if (style.position === 'absolute' || style.position === 'fixed') {
                el.remove();
              }
            }
          });

          // 2. Traverse Three.js scene and hide floor meshes (safe size + keyword checks)
          if (spline.scene) {
            spline.scene.traverse((object) => {
              try {
                if (object.isMesh) {
                  const name = (object.name || '').toLowerCase();
                  const matchesKeyword = (
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
                  );

                  let isVeryLarge = false;
                  if (object.geometry && typeof object.geometry.computeBoundingBox === 'function') {
                    if (!object.geometry.boundingBox) {
                      object.geometry.computeBoundingBox();
                    }
                    const box = object.geometry.boundingBox;
                    if (box) {
                      const width = box.max.x - box.min.x;
                      const depth = box.max.z - box.min.z;
                      if (width > 50 || depth > 50) {
                        isVeryLarge = true;
                      }
                    }
                  }

                  if (matchesKeyword || isVeryLarge) {
                    object.visible = false;
                    if (object.scale) {
                      object.scale.set(0, 0, 0);
                    }
                  }
                }
              } catch (e) {
                // Ignore any Three.js traverse evaluation errors
              }
            });
          }
        };

        cleanupScene();
        // Run cleanups frequently for the first 10 seconds to ensure clean rendering
        const cleanupInterval = setInterval(cleanupScene, 100);
        setTimeout(() => clearInterval(cleanupInterval), 10000);
      })
      .catch(err => {
        console.error('Failed to load Spline scene:', err);
      });
  </script>
</body>
</html>
