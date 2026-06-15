<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Koneksi Terisolir - Netking</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --primary: #FF3B30;
      --primary-dark: #D70015;
      --surface-light: rgba(255, 255, 255, 0.85);
      --txt-main: #1c1c1e;
      --txt-muted: #636366;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Outfit', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      color: var(--txt-main);
      overflow: hidden;
      background: #0f172a;
    }

    /* Animated Background */
    .bg-animation {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      z-index: -1;
      background: radial-gradient(circle at 15% 50%, rgba(255, 59, 48, 0.15), transparent 40%),
                  radial-gradient(circle at 85% 30%, rgba(255, 149, 0, 0.15), transparent 40%);
      animation: floatBg 15s ease-in-out infinite alternate;
    }

    @keyframes floatBg {
      0% { transform: scale(1); }
      100% { transform: scale(1.1) translate(20px, 10px); }
    }

    /* Glass Card */
    .isolir-card {
      background: var(--surface-light);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255, 255, 255, 0.5);
      border-radius: 24px;
      padding: 3rem 2.5rem;
      max-width: 500px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      transform: translateY(20px);
      opacity: 0;
      animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes slideUp {
      to { transform: translateY(0); opacity: 1; }
    }

    /* Icon Animation */
    .icon-wrapper {
      width: 90px;
      height: 90px;
      background: linear-gradient(135deg, #FF3B30, #FF9500);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 2rem;
      box-shadow: 0 10px 20px rgba(255, 59, 48, 0.3);
      position: relative;
    }

    .icon-wrapper::after {
      content: '';
      position: absolute;
      top: -10px; left: -10px; right: -10px; bottom: -10px;
      border: 2px solid rgba(255, 59, 48, 0.2);
      border-radius: 50%;
      animation: pulseIcon 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulseIcon {
      0% { transform: scale(0.8); opacity: 1; }
      100% { transform: scale(1.3); opacity: 0; }
    }

    .icon-wrapper svg {
      width: 44px;
      height: 44px;
      color: white;
    }

    /* Typography */
    h1 {
      font-size: 1.8rem;
      font-weight: 800;
      margin-bottom: 1rem;
      background: linear-gradient(135deg, #1c1c1e, #636366);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -0.03em;
    }

    p.description {
      color: var(--txt-muted);
      line-height: 1.7;
      margin-bottom: 2rem;
      font-size: 1.05rem;
      font-weight: 400;
    }

    /* Diagnostics Box */
    .diagnostic-box {
      background: rgba(0,0,0,0.03);
      border: 1px solid rgba(0,0,0,0.05);
      border-radius: 16px;
      padding: 1.25rem;
      margin-bottom: 2rem;
      display: flex;
      flex-direction: column;
      gap: 8px;
      text-align: left;
    }

    .diagnostic-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.9rem;
    }

    .diagnostic-label {
      color: var(--txt-muted);
      font-weight: 600;
    }

    .diagnostic-value {
      font-family: monospace;
      font-weight: 700;
      color: var(--primary);
      background: rgba(255, 59, 48, 0.1);
      padding: 4px 8px;
      border-radius: 6px;
    }

    /* Buttons */
    .btn-group {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      padding: 1rem 1.5rem;
      border-radius: 14px;
      font-weight: 700;
      font-size: 1rem;
      width: 100%;
      transition: all 0.3s ease;
      cursor: pointer;
      border: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, #1c1c1e, #3a3a3c);
      color: white;
      box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 20px rgba(0,0,0,0.15);
    }

    .btn-secondary {
      background: rgba(0,0,0,0.05);
      color: var(--txt-main);
    }

    .btn-secondary:hover {
      background: rgba(0,0,0,0.1);
    }

    .btn svg {
      width: 20px;
      height: 20px;
      margin-right: 8px;
    }
  </style>
</head>
<body>

  <div class="bg-animation"></div>

  <div class="isolir-card">
    <div class="icon-wrapper">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
    </div>
    
    <h1>Akses Internet Terhenti</h1>
    
    <p class="description">Mohon maaf, koneksi internet Anda saat ini sedang dialihkan. Hal ini umumnya terjadi karena tagihan bulan ini belum diselesaikan.</p>
    
    <div class="diagnostic-box">
      <div class="diagnostic-item">
        <span class="diagnostic-label">Status Jaringan:</span>
        <span class="diagnostic-value" style="color: #FF9500; background: rgba(255, 149, 0, 0.1);">ISOLATED</span>
      </div>
      <div class="diagnostic-item">
        <span class="diagnostic-label">IP Address Anda:</span>
        <span class="diagnostic-value">{{ request()->ip() }}</span>
      </div>
    </div>
    
    <div class="btn-group">
      <!-- Arahkan ke Client Portal untuk bayar online kalau Netking punya -->
      <a href="{{ url('/') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Bayar Tagihan Online
      </a>

      <!-- Tidak redirect WA otomatis, tapi jadi tombol info -->
      <button onclick="alert('Silakan hubungi teknisi atau PIC Area yang biasa menangani jaringan di wilayah Anda, dan sebutkan IP Address Anda: {{ request()->ip() }}');" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M5.636 18.364a9 9 0 010-12.728M15.536 8.464a5 5 0 010 7.072M8.464 15.536a5 5 0 010-7.072M12 12h.01" />
        </svg>
        Hubungi PIC Area Anda
      </button>
    </div>
  </div>

</body>
</html>
