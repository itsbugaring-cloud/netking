<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Koneksi Terisolir - Netking</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg: #f8fafc;
      --surface: #ffffff;
      --txt: #1e293b;
      --txt-2: #475569;
      --txt-3: #64748b;
      --border: #e2e8f0;
      --red: #ef4444;
      --red-light: #fee2e2;
      --green: #22c55e;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--txt);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }
    .isolir-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2.5rem;
      max-width: 480px;
      width: 100%;
      text-align: center;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
    }
    .icon-wrapper {
      width: 80px;
      height: 80px;
      background: var(--red-light);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
    }
    .icon-wrapper svg {
      width: 40px;
      height: 40px;
      color: var(--red);
    }
    h1 {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
      letter-spacing: -0.025em;
    }
    p {
      color: var(--txt-2);
      line-height: 1.6;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
    }
    .info-box {
      background: #f1f5f9;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 2rem;
      text-align: left;
    }
    .info-box strong {
      display: block;
      color: var(--txt);
      margin-bottom: 0.25rem;
      font-size: 0.9rem;
    }
    .info-box span {
      color: var(--txt-3);
      font-size: 0.85rem;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--green);
      color: white;
      text-decoration: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      width: 100%;
      transition: opacity 0.2s;
    }
    .btn:hover {
      opacity: 0.9;
    }
    .btn svg {
      width: 20px;
      height: 20px;
      margin-right: 0.5rem;
    }
  </style>
</head>
<body>

  <div class="isolir-card">
    <div class="icon-wrapper">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
    
    <h1>Koneksi Dihentikan Sementara</h1>
    
    <p>Mohon maaf, akses internet Anda sedang diisolir. Hal ini biasanya terjadi karena ada tagihan yang belum diselesaikan atau melewati tanggal jatuh tempo.</p>
    
    <div class="info-box">
      <strong>Butuh bantuan?</strong>
      <span>Silakan lakukan pembayaran tagihan atau hubungi layanan pelanggan kami untuk membuka kembali akses internet Anda secara otomatis.</span>
    </div>
    
    <div class="btn" style="background: var(--txt-2); cursor: default; justify-content: center;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      Silakan Hubungi PIC Area Anda
    </div>
  </div>

</body>
</html>
