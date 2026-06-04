<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pembayaran Netking</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg-a: #eff7ff;
      --bg-b: #f7fbff;
      --surface: rgba(255,255,255,.92);
      --surface-2: rgba(247,250,255,.96);
      --line: rgba(148,163,184,.22);
      --text: #0f172a;
      --muted: #64748b;
      --blue: #2563eb;
      --cyan: #0284c7;
      --green: #16a34a;
      --red: #dc2626;
      --amber: #d97706;
      --shadow: 0 24px 70px rgba(37,99,235,.14);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      color: var(--text);
      background:
        radial-gradient(circle at top left, rgba(14,165,233,.22), transparent 34%),
        radial-gradient(circle at top right, rgba(37,99,235,.18), transparent 26%),
        linear-gradient(180deg, var(--bg-a), var(--bg-b));
      min-height: 100vh;
    }
    .pay-shell {
      width: min(1120px, calc(100% - 32px));
      margin: 0 auto;
      padding: 28px 0 54px;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .pay-hero {
      position: relative;
      overflow: hidden;
      border-radius: 28px;
      padding: 28px;
      background: linear-gradient(135deg, #0ea5e9, #2563eb 58%, #4338ca);
      color: #fff;
      box-shadow: var(--shadow);
    }
    .pay-hero::after {
      content: '';
      position: absolute;
      inset: auto -60px -70px auto;
      width: 220px;
      height: 220px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      filter: blur(10px);
    }
    .pay-hero-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      position: relative;
      z-index: 1;
    }
    .pay-brand {
      display: flex;
      gap: 14px;
      align-items: center;
    }
    .pay-brand-icon {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      background: rgba(255,255,255,.16);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.7rem;
      backdrop-filter: blur(8px);
    }
    .pay-brand h1 {
      margin: 0;
      font-size: clamp(1.6rem, 2vw, 2.1rem);
      line-height: 1.05;
      letter-spacing: -.04em;
    }
    .pay-brand p,
    .pay-hero-note {
      margin: 0;
      color: rgba(255,255,255,.8);
    }
    .pay-hero-metrics {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 18px;
      position: relative;
      z-index: 1;
    }
    .pay-metric {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.16);
      border: 1px solid rgba(255,255,255,.18);
      color: #fff;
      font-size: .8rem;
      font-weight: 600;
      backdrop-filter: blur(8px);
    }
    .pay-layout {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .pay-top-grid,
    .pay-guide-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr);
      gap: 18px;
      align-items: start;
    }
    .pay-card {
      background: var(--surface);
      border: 1px solid rgba(255,255,255,.55);
      border-radius: 24px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
      overflow: hidden;
    }
    .pay-card-head {
      padding: 20px 22px 14px;
      border-bottom: 1px solid var(--line);
    }
    .pay-card-title {
      margin: 0;
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: -.02em;
    }
    .pay-card-sub {
      margin-top: 6px;
      color: var(--muted);
      font-size: .88rem;
    }
    .pay-card-body {
      padding: 20px 22px 22px;
    }
    .pay-highlight-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      margin-top: 18px;
    }
    .pay-highlight {
      background: var(--surface-2);
      border: 1px solid var(--line);
      border-radius: 18px;
      padding: 16px;
    }
    .pay-highlight-icon {
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(37,99,235,.08);
      color: var(--blue);
      font-size: 1.2rem;
      margin-bottom: 10px;
    }
    .pay-highlight-title {
      font-size: .9rem;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .pay-form {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .pay-input {
      flex: 1 1 300px;
      min-height: 52px;
      border-radius: 14px;
      border: 1px solid var(--line);
      background: #fff;
      padding: 0 16px;
      font: inherit;
      color: var(--text);
      outline: none;
    }
    .pay-input:focus,
    .pay-select:focus,
    .pay-textarea:focus {
      border-color: rgba(37,99,235,.45);
      box-shadow: 0 0 0 4px rgba(37,99,235,.1);
    }
    .pay-btn {
      min-height: 52px;
      border: 0;
      border-radius: 14px;
      padding: 0 18px;
      font: inherit;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
    }
    .pay-btn-primary {
      color: #fff;
      background: linear-gradient(135deg, #0284c7, #2563eb);
    }
    .pay-btn-secondary {
      color: var(--text);
      background: var(--surface-2);
      border: 1px solid var(--line);
    }
    .pay-alert {
      border-radius: 16px;
      padding: 14px 16px;
      font-size: .92rem;
      margin-bottom: 14px;
    }
    .pay-alert-success { background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; }
    .pay-alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .pay-alert-warning { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
    .pay-customer {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 18px;
    }
    .pay-kpi {
      padding: 14px 16px;
      border-radius: 18px;
      background: var(--surface-2);
      border: 1px solid var(--line);
    }
    .pay-kpi-label {
      color: var(--muted);
      font-size: .75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    .pay-kpi-value {
      margin-top: 8px;
      font-weight: 700;
      font-size: .95rem;
      overflow-wrap: anywhere;
    }
    .pay-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 999px;
      padding: 6px 10px;
      font-size: .75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .04em;
    }
    .st-paid { background: #ecfdf3; color: #166534; }
    .st-unpaid { background: #eff6ff; color: #1d4ed8; }
    .st-overdue { background: #fff7ed; color: #c2410c; }
    .st-review { background: #fff7ed; color: #9a3412; }
    .pay-invoices {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 18px;
    }
    .pay-invoice {
      border: 1px solid var(--line);
      border-radius: 18px;
      background: var(--surface-2);
      padding: 16px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
    }
    .pay-invoice-title {
      margin: 0;
      font-size: .96rem;
      font-weight: 700;
    }
    .pay-invoice-sub {
      color: var(--muted);
      font-size: .82rem;
      margin-top: 4px;
    }
    .pay-price {
      font-size: 1.12rem;
      font-weight: 800;
      letter-spacing: -.03em;
    }
    .pay-panel {
      display: grid;
      gap: 12px;
    }
    .pay-method-card,
    .pay-upload {
      border: 1px solid var(--line);
      border-radius: 18px;
      padding: 16px;
      background: var(--surface-2);
    }
    .pay-method-grid {
      display: grid;
      gap: 14px;
    }
    .pay-bank-list {
      display: grid;
      gap: 10px;
    }
    .pay-bank {
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 14px;
      background: #fff;
    }
    .pay-bank-name {
      color: var(--muted);
      font-size: .74rem;
      text-transform: uppercase;
      letter-spacing: .05em;
      font-weight: 700;
    }
    .pay-bank-number {
      margin-top: 6px;
      font-weight: 800;
      font-size: 1.04rem;
      letter-spacing: .03em;
    }
    .pay-qris {
      display: block;
      width: 100%;
      border-radius: 16px;
      border: 1px solid var(--line);
    }
    .pay-select,
    .pay-file,
    .pay-textarea {
      width: 100%;
      min-height: 48px;
      border-radius: 14px;
      border: 1px solid var(--line);
      background: #fff;
      padding: 0 14px;
      font: inherit;
      color: var(--text);
      outline: none;
    }
    .pay-file { padding: 10px 14px; }
    .pay-textarea {
      min-height: 100px;
      padding: 12px 14px;
      resize: vertical;
    }
    .pay-label {
      display: block;
      font-size: .82rem;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .pay-help {
      color: var(--muted);
      font-size: .79rem;
      line-height: 1.55;
    }
    .pay-mini-note {
      border-radius: 16px;
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      color: #1d4ed8;
      padding: 14px 16px;
      font-size: .83rem;
      line-height: 1.6;
    }
    .pay-steps {
      display: grid;
      gap: 12px;
    }
    .pay-step {
      display: grid;
      grid-template-columns: 38px 1fr;
      gap: 12px;
      align-items: start;
    }
    .pay-step-no {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      background: rgba(37,99,235,.1);
      color: var(--blue);
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .pay-faq {
      display: grid;
      gap: 12px;
    }
    .pay-faq-item {
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 14px 16px;
      background: var(--surface-2);
    }
    .pay-faq-q {
      font-size: .87rem;
      font-weight: 700;
      margin-bottom: 6px;
    }
    @media (max-width: 960px) {
      .pay-top-grid,
      .pay-guide-grid { grid-template-columns: 1fr; }
      .pay-customer { grid-template-columns: 1fr 1fr; }
      .pay-highlight-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .pay-shell { width: min(100% - 20px, 1120px); padding-top: 14px; }
      .pay-hero { padding: 20px; border-radius: 22px; }
      .pay-hero-top { flex-direction: column; }
      .pay-customer { grid-template-columns: 1fr; }
      .pay-card-body, .pay-card-head { padding-left: 16px; padding-right: 16px; }
      .pay-form { flex-direction: column; }
      .pay-input, .pay-btn { width: 100%; }
    }
  </style>
</head>
<body>
  <div class="pay-shell">
    <section class="pay-hero">
      <div class="pay-hero-top">
        <div class="pay-brand">
          <div class="pay-brand-icon"><i class='bx bx-credit-card-front'></i></div>
          <div>
            <h1>Pembayaran Netking</h1>
            <p>Cek tagihan, lihat rekening resmi atau QRIS, lalu unggah bukti transfer tanpa login portal customer.</p>
          </div>
        </div>
        <div class="pay-hero-note">Cukup siapkan <strong>ID pelanggan</strong> Anda.</div>
      </div>
      <div class="pay-hero-metrics">
        <div class="pay-metric"><i class='bx bx-id-card'></i> Tanpa login portal</div>
        <div class="pay-metric"><i class='bx bx-qr-scan'></i> Rekening & QRIS resmi</div>
        <div class="pay-metric"><i class='bx bx-cloud-upload'></i> Upload bukti langsung</div>
      </div>
    </section>

    <div class="pay-layout">
      <section class="pay-top-grid">
        <main class="pay-card">
          <div class="pay-card-head">
            <h2 class="pay-card-title">Cek Tagihan Pelanggan</h2>
            <div class="pay-card-sub">Masukkan ID pelanggan untuk menampilkan tagihan aktif dan form upload bukti pembayaran.</div>
          </div>
          <div class="pay-card-body">
            @if(session('success'))
              <div class="pay-alert pay-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
              <div class="pay-alert pay-alert-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
              <div class="pay-alert pay-alert-error">{{ $errors->first() }}</div>
            @endif

            <form class="pay-form" method="GET" action="{{ route('payment.public.root') }}">
              <input type="text" class="pay-input" name="customer_code" value="{{ old('customer_code', $customerCode) }}" placeholder="Masukkan ID pelanggan, contoh NK000123">
              <button class="pay-btn pay-btn-primary" type="submit"><i class='bx bx-search'></i> Cek Tagihan</button>
            </form>

            <div class="pay-highlight-grid">
              <div class="pay-highlight">
                <div class="pay-highlight-icon"><i class='bx bx-search-alt'></i></div>
                <div class="pay-highlight-title">1. Cek tagihan</div>
                <div class="pay-help">Masukkan ID pelanggan untuk menampilkan semua tagihan aktif yang belum lunas.</div>
              </div>
              <div class="pay-highlight">
                <div class="pay-highlight-icon"><i class='bx bx-credit-card'></i></div>
                <div class="pay-highlight-title">2. Bayar sesuai nominal</div>
                <div class="pay-help">Gunakan rekening atau QRIS resmi yang tampil di halaman ini sesuai jumlah tagihan.</div>
              </div>
              <div class="pay-highlight">
                <div class="pay-highlight-icon"><i class='bx bx-check-shield'></i></div>
                <div class="pay-highlight-title">3. Upload bukti transfer</div>
                <div class="pay-help">Admin akan meninjau bukti bayar Anda sebelum tagihan dikonfirmasi lunas.</div>
              </div>
            </div>
          </div>
        </main>

        <section class="pay-card">
          <div class="pay-card-head">
            <h2 class="pay-card-title">Metode Pembayaran Resmi</h2>
            <div class="pay-card-sub">Rekening dan QRIS resmi Netking tampil langsung dari awal.</div>
          </div>
          <div class="pay-card-body">
            <div class="pay-method-grid">
              @if(!empty($paymentSettings['accounts']) && count($paymentSettings['accounts']))
                <div class="pay-bank-list">
                  @foreach($paymentSettings['accounts'] as $account)
                    <div class="pay-bank">
                      <div class="pay-bank-name">{{ $account['bank_name'] ?? '-' }}</div>
                      <div class="pay-bank-number">{{ $account['account_number'] ?? '-' }}</div>
                      <div class="pay-help">a.n. {{ $account['account_holder'] ?? '-' }}</div>
                    </div>
                  @endforeach
                </div>
              @endif

              @if(!empty($paymentSettings['qris']))
                <div>
                  <div class="pay-bank-name" style="margin-bottom:8px;">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</div>
                  <a href="{{ $paymentSettings['qris']['image_url'] }}" target="_blank" rel="noopener">
                    <img class="pay-qris" src="{{ $paymentSettings['qris']['image_url'] }}" alt="QRIS NETKING">
                  </a>
                  @if(!empty($paymentSettings['qris']['notes']))
                    <div class="pay-help" style="margin-top:10px;">{{ $paymentSettings['qris']['notes'] }}</div>
                  @endif
                </div>
              @endif

              <div class="pay-mini-note">
                Pastikan nominal transfer sesuai tagihan yang dipilih. Setelah transfer, unggah bukti pembayaran di halaman ini agar admin dapat meninjau tagihan Anda.
              </div>
            </div>
          </div>
        </section>
      </section>

      <section class="pay-guide-grid">
        <section class="pay-card">
          <div class="pay-card-head">
            <h2 class="pay-card-title">Tata Cara Bayar</h2>
            <div class="pay-card-sub">Langkahnya dibuat runtut dari atas ke bawah agar pelanggan tidak bingung.</div>
          </div>
          <div class="pay-card-body">
            <div class="pay-steps">
              <div class="pay-step">
                <div class="pay-step-no">1</div>
                <div>
                  <div class="pay-card-title" style="font-size:.9rem;">Masukkan ID pelanggan</div>
                  <div class="pay-help">ID pelanggan berbentuk seperti <strong>NK000123</strong>. Setelah dimasukkan, sistem akan menampilkan tagihan aktif Anda.</div>
                </div>
              </div>
              <div class="pay-step">
                <div class="pay-step-no">2</div>
                <div>
                  <div class="pay-card-title" style="font-size:.9rem;">Pilih tagihan yang mau dibayar</div>
                  <div class="pay-help">Kalau ada lebih dari satu tagihan aktif, pilih invoice yang ingin dibayar terlebih dahulu.</div>
                </div>
              </div>
              <div class="pay-step">
                <div class="pay-step-no">3</div>
                <div>
                  <div class="pay-card-title" style="font-size:.9rem;">Bayar sesuai nominal</div>
                  <div class="pay-help">Gunakan nomor rekening atau QRIS resmi yang tampil di halaman ini. Nominal harus sesuai dengan tagihan yang dipilih.</div>
                </div>
              </div>
              <div class="pay-step">
                <div class="pay-step-no">4</div>
                <div>
                  <div class="pay-card-title" style="font-size:.9rem;">Unggah bukti transfer</div>
                  <div class="pay-help">Setelah transfer, unggah foto bukti pembayaran agar admin bisa meninjau dan mengonfirmasi tagihan Anda.</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="pay-card">
          <div class="pay-card-head">
            <h2 class="pay-card-title">Pertanyaan Umum</h2>
            <div class="pay-card-sub">Jawaban singkat untuk hal yang biasanya ditanyakan pelanggan.</div>
          </div>
          <div class="pay-card-body">
            <div class="pay-faq">
              <div class="pay-faq-item">
                <div class="pay-faq-q">ID pelanggan saya tidak ketemu</div>
                <div class="pay-help">Pastikan formatnya benar, misalnya <strong>NK000123</strong>. Jika masih tidak ditemukan, hubungi admin Netking.</div>
              </div>
              <div class="pay-faq-item">
                <div class="pay-faq-q">Sudah transfer tapi belum dikonfirmasi</div>
                <div class="pay-help">Unggah bukti pembayaran dari halaman ini. Admin akan meninjau dan mengubah status tagihan setelah bukti valid.</div>
              </div>
              <div class="pay-faq-item">
                <div class="pay-faq-q">Bisa bayar lebih dari satu tagihan?</div>
                <div class="pay-help">Bisa, tetapi pilih dan unggah bukti untuk masing-masing tagihan agar pencatatannya tidak tertukar.</div>
              </div>
            </div>
          </div>
        </section>
      </section>

      @if($customer)
        <section class="pay-card">
          <div class="pay-card-head">
            <h2 class="pay-card-title">Data Pelanggan & Tagihan Aktif</h2>
            <div class="pay-card-sub">Setelah ID pelanggan dicek, semua tagihan aktif akan tampil di bagian ini.</div>
          </div>
          <div class="pay-card-body">
            <div class="pay-customer">
              <div class="pay-kpi">
                <div class="pay-kpi-label">Pelanggan</div>
                <div class="pay-kpi-value">{{ $customer->name }}</div>
              </div>
              <div class="pay-kpi">
                <div class="pay-kpi-label">ID Pelanggan</div>
                <div class="pay-kpi-value">{{ $customer->customer_code }}</div>
              </div>
              <div class="pay-kpi">
                <div class="pay-kpi-label">Area</div>
                <div class="pay-kpi-value">{{ $customer->area->name ?? '-' }}</div>
              </div>
              <div class="pay-kpi">
                <div class="pay-kpi-label">Paket</div>
                <div class="pay-kpi-value">{{ $customer->package->name ?? '-' }}</div>
              </div>
            </div>

            @if($invoices->isEmpty())
              <div class="pay-alert pay-alert-success">Tidak ada tagihan aktif untuk ID pelanggan ini.</div>
            @else
              @if($invoices->count() > 1)
                <div class="pay-alert pay-alert-warning">Ada lebih dari satu tagihan aktif. Pilih tagihan yang ingin dibayar.</div>
              @endif

              <div class="pay-invoices">
                @foreach($invoices as $invoice)
                  @php
                    $isSelected = $selectedInvoice && $selectedInvoice->id === $invoice->id;
                    $statusClass = $invoice->payment_review_status === 'submitted'
                      ? 'st-review'
                      : ($invoice->due_date->isPast() ? 'st-overdue' : 'st-unpaid');
                    $statusLabel = $invoice->payment_review_status === 'submitted'
                      ? 'Menunggu Review'
                      : ($invoice->due_date->isPast() ? 'Jatuh Tempo' : 'Belum Lunas');
                  @endphp
                  <div class="pay-invoice">
                    <div>
                      <p class="pay-invoice-title">{{ $invoice->invoice_number }}</p>
                      <div class="pay-invoice-sub">
                        Jatuh tempo {{ $invoice->due_date->format('d M Y') }}
                        @if($invoice->is_prorated)
                          • Tagihan Prorata
                        @endif
                        @if($invoice->payment_review_status === 'rejected' && $invoice->payment_reject_reason)
                          • Bukti bayar ditolak: {{ $invoice->payment_reject_reason }}
                        @endif
                      </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                      <span class="pay-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                      <div class="pay-price">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</div>
                      @if(!$isSelected)
                        <a class="pay-btn pay-btn-secondary" href="{{ route('payment.public', ['customerCode' => $customer->customer_code, 'invoice' => $invoice->id]) }}">Pilih Tagihan</a>
                      @else
                        <span class="pay-btn pay-btn-primary" style="pointer-events:none;opacity:.88;">Tagihan Dipilih</span>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </section>

        @if($selectedInvoice)
          <section class="pay-top-grid">
            <section class="pay-card">
              <div class="pay-card-head">
                <h2 class="pay-card-title">Instruksi Pembayaran Tagihan</h2>
                <div class="pay-card-sub">Nominal yang harus dibayar untuk tagihan yang dipilih.</div>
              </div>
              <div class="pay-card-body">
                <div class="pay-method-card">
                  <div class="pay-card-title" style="margin-bottom:12px;">{{ $selectedInvoice->invoice_number }}</div>
                  <div class="pay-help" style="margin-bottom:16px;">Bayar sesuai nominal tagihan berikut: <strong>Rp {{ number_format($selectedInvoice->amount, 0, ',', '.') }}</strong>, lalu unggah bukti transfer di form sebelah.</div>
                  <div class="pay-mini-note">{{ $paymentSettings['notes'] ?? 'Transfer atau bayar via QRIS sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}</div>
                </div>
              </div>
            </section>

            <section class="pay-card">
              <div class="pay-card-head">
                <h2 class="pay-card-title">Upload Bukti Pembayaran</h2>
                <div class="pay-card-sub">Setelah transfer, kirim bukti pembayaran agar admin dapat melakukan review.</div>
              </div>
              <div class="pay-card-body">
                <div class="pay-upload">
                  @if($selectedInvoice->payment_review_status === 'submitted')
                    <div class="pay-alert pay-alert-warning">Bukti pembayaran untuk tagihan ini sudah pernah dikirim dan sedang menunggu review. Jika perlu, Anda bisa ganti file bukti di bawah.</div>
                  @endif
                  <form method="POST" action="{{ route('payment.public.submit') }}" enctype="multipart/form-data" style="display:grid;gap:14px;">
                    @csrf
                    <input type="hidden" name="customer_code" value="{{ $customer->customer_code }}">
                    <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">

                    <div>
                      <label class="pay-label">Metode Pembayaran</label>
                      <select name="payment_method" class="pay-select" required>
                        <option value="">Pilih metode</option>
                        <option value="transfer_bank" @selected(old('payment_method', $selectedInvoice->payment_method) === 'transfer_bank')>Transfer Bank</option>
                        <option value="qris" @selected(old('payment_method', $selectedInvoice->payment_method) === 'qris')>QRIS</option>
                        <option value="cash" @selected(old('payment_method', $selectedInvoice->payment_method) === 'cash')>Cash</option>
                      </select>
                    </div>

                    <div>
                      <label class="pay-label">Foto Bukti Transfer</label>
                      <input type="file" name="payment_proof" class="pay-file" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                      <div class="pay-help" style="margin-top:8px;">Format JPG, PNG, atau WEBP. Maksimal 5 MB.</div>
                    </div>

                    <div>
                      <label class="pay-label">Catatan</label>
                      <textarea name="notes" class="pay-textarea" placeholder="Contoh: transfer dari rekening BRI a.n. Andi">{{ old('notes', $selectedInvoice->payment_proof_notes) }}</textarea>
                    </div>

                    @if($selectedInvoice->payment_proof_url)
                      <div>
                        <a class="pay-btn pay-btn-secondary" href="{{ $selectedInvoice->payment_proof_url }}" target="_blank" rel="noopener">Lihat Bukti yang Sudah Diunggah</a>
                      </div>
                    @endif

                    <button class="pay-btn pay-btn-primary" type="submit">
                      <i class='bx bx-upload'></i>
                      {{ $selectedInvoice->payment_review_status === 'submitted' ? 'Ganti Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}
                    </button>
                  </form>
                </div>
              </div>
            </section>
          </section>
        @endif
      @endif
    </div>
  </div>
</body>
</html>
