<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 — Akses Ditolak | NETKING</title>
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
  
  /* CubeLoader Styles */
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
  .perspective-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 48px;
    padding: 48px;
    min-height: 400px;
    background: transparent;
    perspective: 1200px;
  }
  .preserve-3d-loader {
    position: relative;
    width: 96px;
    height: 96px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform-style: preserve-3d;
  }
  .cube-container {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    animation: cubeSpin 8s linear infinite;
  }
  .cube-core {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
    width: 32px;
    height: 32px;
    background: #fff;
    border-radius: 50%;
    filter: blur(12px);
    box-shadow: 0 0 40px rgba(255, 255, 255, 0.8);
    animation: pulse-fast 2s ease-in-out infinite;
  }
  .side-wrapper-loader {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transform-style: preserve-3d;
  }
  .face-loader {
    width: 100%;
    height: 100%;
    position: absolute;
    animation: breathe 3s ease-in-out infinite;
    backdrop-filter: blur(2px);
  }
  
  .front-loader-face {
    background: rgba(6, 182, 212, 0.1);
    border: 2px solid #22d3ee;
    box-shadow: 0 0 15px rgba(34, 211, 238, 0.4);
  }
  .side-loader-face {
    background: rgba(168, 85, 247, 0.1);
    border: 2px solid #c084fc;
    box-shadow: 0 0 15px rgba(168, 85, 247, 0.4);
  }
  .top-loader-face {
    background: rgba(99, 102, 241, 0.1);
    border: 2px solid #818cf8;
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
  }

  .front-loader  { transform: rotateY(0deg); }
  .back-loader   { transform: rotateY(180deg); }
  .right-loader  { transform: rotateY(90deg); }
  .left-loader   { transform: rotateY(-90deg); }
  .top-loader    { transform: rotateX(90deg); }
  .bottom-loader { transform: rotateX(-90deg); }

  .floor-shadow {
    position: absolute;
    bottom: -80px;
    width: 96px;
    height: 32px;
    background: rgba(0, 0, 0, 0.4);
    filter: blur(24px);
    border-radius: 100%;
    animation: shadow-breathe 3s ease-in-out infinite;
  }

  .loading-text-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
  }
  .loading-title {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.3em;
    color: #67e8f9;
    text-transform: uppercase;
  }
  .loading-subtitle {
    font-size: 12px;
    color: #94a3b8;
  }

  @keyframes cubeSpin {
    0% { transform: rotateX(0deg) rotateY(0deg); }
    100% { transform: rotateX(360deg) rotateY(360deg); }
  }
  @keyframes breathe {
    0%, 100% { transform: translateZ(48px); opacity: 0.8; }
    50% { transform: translateZ(80px); opacity: 0.4; border-color: rgba(255,255,255,0.8); }
  }
  @keyframes pulse-fast {
    0%, 100% { transform: scale(0.8); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 1; }
  }
  @keyframes shadow-breathe {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50% { transform: scale(1.5); opacity: 0.2; }
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
  #logo,
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
    <!-- CubeLoader as loader fallback -->
    <div class="loader-container" id="spline-loader">
      <div class="perspective-container">
        <!-- 3D Scene Wrapper -->
        <div class="preserve-3d-loader">
          <!-- THE SPINNING CUBE CONTAINER -->
          <div class="cube-container">
            <!-- Internal Core -->
            <div class="cube-core"></div>
            <!-- Front -->
            <div class="side-wrapper-loader front-loader">
              <div class="face-loader front-loader-face"></div>
            </div>
            <!-- Back -->
            <div class="side-wrapper-loader back-loader">
              <div class="face-loader front-loader-face"></div>
            </div>
            <!-- Right -->
            <div class="side-wrapper-loader right-loader">
              <div class="face-loader side-loader-face"></div>
            </div>
            <!-- Left -->
            <div class="side-wrapper-loader left-loader">
              <div class="face-loader side-loader-face"></div>
            </div>
            <!-- Top -->
            <div class="side-wrapper-loader top-loader">
              <div class="face-loader top-loader-face"></div>
            </div>
            <!-- Bottom -->
            <div class="side-wrapper-loader bottom-loader">
              <div class="face-loader top-loader-face"></div>
            </div>
          </div>
          <!-- Floor Shadow -->
          <div class="floor-shadow"></div>
        </div>
        <!-- Loading Text -->
        <div class="loading-text-container">
          <h3 class="loading-title">Loading</h3>
          <p class="loading-subtitle">Preparing your experience, please wait…</p>
        </div>
      </div>
    </div>
    <canvas id="canvas3d"></canvas>
  </div>

  <!-- Overlay Text & Buttons -->
  <div class="overlay-content">
    <div class="glass-card">
      <div class="brand">NETKING</div>
      <h1 class="title">Akses Ditolak</h1>
      
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
        // Hide loader smoothly once loading is complete
        const loader = document.getElementById('spline-loader');
        if (loader) {
          loader.style.opacity = '0';
          loader.style.visibility = 'hidden';
          setTimeout(() => loader.remove(), 400);
        }

        // Set spline scene background to transparent
        const scene = spline.scene || spline._scene;
        if (scene) {
          scene.background = null;
        }
        const renderer = spline.renderer || spline._renderer;
        if (renderer) {
          renderer.setClearAlpha(0);
        }

        // Clean watermark recursively and hide the floor mesh
        const cleanupScene = () => {
          // 1. Recursive CSS injector to hide watermark inside any shadow DOM
          const injectCSS = (root) => {
            if (!root) return;
            const styleId = 'hide-spline-watermark';
            if (!root.getElementById(styleId)) {
              const style = document.createElement('style');
              style.id = styleId;
              style.textContent = `
                #logo, a[href*="spline.design"], a[href*="spline"], [class*="spline"], spline-viewer + a, .spline-container + a {
                  display: none !important;
                  opacity: 0 !important;
                  visibility: hidden !important;
                  pointer-events: none !important;
                }
              `;
              if (root.head) {
                root.head.appendChild(style);
              } else {
                root.appendChild(style);
              }
            }
            root.querySelectorAll('*').forEach(el => {
              if (el.shadowRoot) {
                injectCSS(el.shadowRoot);
              }
            });
          };
          injectCSS(document);

          // 2. Remove watermark elements from main DOM & shadow DOMs directly
          const removeElements = (root) => {
            if (!root) return;
            root.querySelectorAll('a').forEach(a => {
              if (a.href && a.href.includes('spline')) {
                a.remove();
              }
            });
            const logo = root.getElementById ? root.getElementById('logo') : null;
            if (logo) {
              logo.remove();
            }
            root.querySelectorAll('*').forEach(el => {
              if (el.shadowRoot) {
                removeElements(el.shadowRoot);
              }
            });
          };
          removeElements(document);

          // 3. Traverse Three.js scene and hide the rotated ground plane mesh named "Plane"
          if (scene) {
            scene.traverse((object) => {
              try {
                if (object.isMesh) {
                  const name = (object.name || '').toLowerCase();
                  if (name === 'plane') {
                    object.visible = false;
                    if (object.scale) {
                      object.scale.set(0, 0, 0);
                    }
                  }
                }
              } catch (e) {
                // Ignore traverse errors
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
        // Hide loader even on failure so page content is interactive
        const loader = document.getElementById('spline-loader');
        if (loader) {
          loader.style.opacity = '0';
          loader.style.visibility = 'hidden';
          setTimeout(() => loader.remove(), 400);
        }
      });
  </script>
</body>
</html>
