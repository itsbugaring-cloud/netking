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
      --blue: #3b82f6;
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
      /* Subtle animated background */
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      background-size: 200% 200%;
      animation: gradientBg 15s ease infinite;
    }

    @keyframes gradientBg {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
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
      /* Card entrance animation */
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
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
      position: relative;
    }

    /* Pulse animation behind the icon */
    .icon-wrapper::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      border-radius: 50%;
      background: var(--red-light);
      z-index: -1;
      animation: pulse 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
    }

    @keyframes pulse {
      0% { transform: scale(1); opacity: 0.8; }
      100% { transform: scale(1.4); opacity: 0; }
    }

    .icon-wrapper svg {
      width: 40px;
      height: 40px;
      color: var(--red);
      /* Floating animation for the icon itself */
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
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
      padding: 1.25rem;
      margin-bottom: 2rem;
      text-align: left;
      border-left: 4px solid var(--blue);
    }
    .info-box strong {
      display: block;
      color: var(--txt);
      margin-bottom: 0.4rem;
      font-size: 0.95rem;
    }
    .info-box span {
      color: var(--txt-3);
      font-size: 0.85rem;
      line-height: 1.5;
      display: block;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--txt-2);
      color: white;
      text-decoration: none;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      width: 100%;
      cursor: default;
      transition: background 0.3s ease;
    }
    .btn:hover {
      background: var(--txt);
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
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
    
    <h1>Koneksi Dihentikan Sementara</h1>
    
    <p>Akses internet Anda saat ini sedang dinonaktifkan. Hal ini umumnya disebabkan oleh tagihan yang belum terbayar atau sudah melewati tanggal jatuh tempo.</p>
    
    <div class="info-box">
      <strong>Butuh bantuan?</strong>
      <span>Lakukan pembayaran tagihan Anda, atau hubungi tim layanan kami untuk mengaktifkan kembali koneksi Anda.</span>
    </div>
    
    <div class="btn">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      Hubungi PIC Area Anda
    </div>
  </div>

</body>
</html>
