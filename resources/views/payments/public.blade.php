<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pembayaran Netking</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  <!-- CSS files -->
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }
    body {
      font-feature-settings: "cv03", "cv04", "cv11";
      background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f0f9ff 100%);
      min-height: 100vh;
    }

    /* Payment Method Selection */
    .payment-method-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .5rem;
    }
    @media (max-width: 576px) {
      .payment-method-grid { grid-template-columns: 1fr; }
    }
    .payment-method-option input[type="radio"] { display: none; }
    .payment-method-card {
      display: flex;
      flex-direction: column;
      padding: .75rem 1rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      cursor: pointer;
      transition: all .15s ease;
      background: #fff;
    }
    .payment-method-card:hover {
      border-color: #93c5fd;
      background: #f0f9ff;
    }
    .payment-method-option input[type="radio"]:checked + .payment-method-card {
      border-color: #2563eb;
      background: #eff6ff;
      box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .payment-method-name {
      font-weight: 700;
      font-size: .875rem;
      color: #1e293b;
    }
    .payment-method-detail {
      font-size: .75rem;
      color: #64748b;
      font-family: monospace;
    }
    .payment-method-holder {
      font-size: .7rem;
      color: #94a3b8;
    }

    .pay-header {
      background: linear-gradient(135deg, #0ea5e9, #2563eb 60%, #4f46e5);
      border-radius: 1rem;
      padding: 2rem;
      color: #fff;
      margin-bottom: 1.5rem;
    }
    .pay-header h2 { margin: 0; font-weight: 700; }
    .pay-header p { margin: 0.5rem 0 0; opacity: 0.85; }
    .bento-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .bento-grid .bento-main {
      grid-column: 1;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .bento-grid .bento-side {
      grid-column: 2;
      grid-row: 1 / -1;
      position: sticky;
      top: 1rem;
      align-self: start;
    }
    .card {
      border: none;
      box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.04);
      transition: box-shadow .2s;
    }
    .card:hover {
      box-shadow: 0 4px 16px rgba(0,0,0,.08);
    }
    .accordion-button:not(.collapsed) {
      background: #eef2ff;
    }
    /* Drop zone */
    #dropZone.drag-over {
      border-color: #2563eb !important;
      background: #eef2ff;
    }
    /* Footer */
    .pay-footer {
      text-align: center;
      padding: 2rem 0 1rem;
      color: #94a3b8;
      font-size: 0.8rem;
    }
    /* Confetti */
    .confetti-piece {
      position: fixed;
      width: 10px;
      height: 10px;
      top: -10px;
      opacity: 0;
      animation: confetti-fall 3s ease-out forwards;
      z-index: 9999;
      pointer-events: none;
    }
    @keyframes confetti-fall {
      0% { opacity: 1; top: -10px; transform: rotate(0deg); }
      100% { opacity: 0; top: 100vh; transform: rotate(720deg); }
    }
    @media (max-width: 767.98px) {
      .bento-grid {
        grid-template-columns: 1fr;
      }
      .bento-grid .bento-side {
        grid-column: 1;
        grid-row: auto;
      }
      .pay-header {
        border-radius: 0.75rem;
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body class="d-flex flex-column">
  <div class="page">
    <div class="page-wrapper">
      <div class="page-body">
        <div class="container-xl py-4">

      {{-- Hero Header --}}
      <div class="pay-header">
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-lg" style="background: rgba(255,255,255,.15); backdrop-filter: blur(4px);">
            <i class="ti ti-credit-card" style="color: #fff;"></i>
          </span>
          <div>
            <h2>Pembayaran Netking</h2>
            <p>Bayar tagihan internet Anda dengan mudah dan cepat.</p>
          </div>
        </div>
      </div>

      {{-- Bento Grid Layout --}}
      <div class="bento-grid">

        {{-- Main Column --}}
        <div class="bento-main">

          {{-- Search Card --}}
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Pembayaran Pelanggan</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">Masukkan ID pelanggan untuk melakukan pembayaran.</p>

              @if(session('success'))
                <div class="alert alert-success">
                  <div class="d-flex align-items-center">
                    <i class="ti ti-circle-check me-2"></i>
                    <div>{{ session('success') }}</div>
                  </div>
                </div>
              @endif
              @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
              @endif
              @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
              @endif

              <form method="GET" action="{{ route('payment.public.root') }}">
                <div class="row g-2">
                  <div class="col">
                    <input type="text" class="form-control" name="customer_code" value="{{ old('customer_code', $customerCode) }}" placeholder="Masukkan ID pelanggan" maxlength="32">
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-primary" type="submit" id="btnCekTagihan"><i class="ti ti-search"></i> Cek</button>
                  </div>
                </div>
              </form>

              <div class="row g-3 mt-3">
                <div class="col-md-4">
                  <div class="p-3 rounded border">
                    <span class="avatar avatar-sm bg-primary-lt mb-2"><i class="ti ti-search"></i></span>
                    <div class="fw-medium mb-1">1. Cek pelanggan</div>
                    <div class="text-secondary small">Ketik ID pelanggan lalu klik Cek.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="p-3 rounded border">
                    <span class="avatar avatar-sm bg-primary-lt mb-2"><i class="ti ti-cash"></i></span>
                    <div class="fw-medium mb-1">2. Bayar</div>
                    <div class="text-secondary small">Transfer ke rekening atau scan QRIS di samping.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="p-3 rounded border">
                    <span class="avatar avatar-sm bg-primary-lt mb-2"><i class="ti ti-shield-check"></i></span>
                    <div class="fw-medium mb-1">3. Upload bukti</div>
                    <div class="text-secondary small">Kirim foto bukti transfer untuk dikonfirmasi admin.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Customer Info & Upload Form --}}
          @if($customer)
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Data Pelanggan</h3>
            </div>
            <div class="card-body">
              <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                  <div class="border rounded p-3">
                    <div class="text-uppercase text-secondary small">Pelanggan</div>
                    <div class="fw-bold">{{ $customer->name }}</div>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="border rounded p-3">
                    <div class="text-uppercase text-secondary small">ID Pelanggan</div>
                    <div class="fw-bold">{{ $customer->customer_code }}</div>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="border rounded p-3">
                    <div class="text-uppercase text-secondary small">Area</div>
                    <div class="fw-bold">{{ $customer->area->name ?? '-' }}</div>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="border rounded p-3">
                    <div class="text-uppercase text-secondary small">Paket</div>
                    <div class="fw-bold">{{ $customer->package->name ?? '-' }}</div>
                  </div>
                </div>
              </div>

              @if($customer->is_free)
              <div class="alert alert-success mb-4">
                <div class="d-flex align-items-center">
                  <i class="ti ti-gift me-2"></i>
                  <div>
                    <strong>Pelanggan Gratis</strong> — Anda tidak memiliki tagihan. Terima kasih!
                  </div>
                </div>
              </div>
              @else
              <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                  <i class="ti ti-info-circle me-2"></i>
                  <div>
                    Tagihan bulanan: <strong>Rp {{ number_format($customer->package_price, 0, ',', '.') }}</strong>
                    — Periode: <strong>{{ now()->translatedFormat('F Y') }}</strong>
                  </div>
                </div>
              </div>

              @if(!session('success'))
              {{-- Upload Form --}}
              <h4 class="mb-3">Upload Bukti Pembayaran</h4>
              <form method="POST" action="{{ route('payment.public.submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="customer_code" value="{{ $customer->customer_code }}">

                <div class="mb-3">
                  <label class="form-label fw-semibold">Rekening Tujuan</label>
                  <div class="payment-method-grid">
                    @foreach($paymentSettings['accounts'] as $account)
                    <label class="payment-method-option">
                      <input type="radio" name="rekening_tujuan" value="{{ $account['bank_name'] }}" {{ old('rekening_tujuan') === $account['bank_name'] ? 'checked' : '' }} required>
                      <div class="payment-method-card">
                        <span class="payment-method-name">{{ $account['bank_name'] }}</span>
                        <span class="payment-method-detail">{{ $account['account_number'] }}</span>
                        <span class="payment-method-holder">a.n. {{ $account['account_holder'] }}</span>
                      </div>
                    </label>
                    @endforeach
                    @if(!empty($paymentSettings['qris']))
                    <label class="payment-method-option">
                      <input type="radio" name="rekening_tujuan" value="QRIS" {{ old('rekening_tujuan') === 'QRIS' ? 'checked' : '' }} required>
                      <div class="payment-method-card">
                        <span class="payment-method-name">QRIS</span>
                        <span class="payment-method-detail">Scan QR Code</span>
                        <span class="payment-method-holder">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</span>
                      </div>
                    </label>
                    @endif
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Foto Bukti Transfer</label>
                  <div id="dropZone" class="border border-2 border-dashed rounded p-4 text-center position-relative" style="cursor: pointer; transition: all .2s;">
                    <input type="file" name="payment_proof" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                    <div id="dropPlaceholder">
                      <i class="ti ti-cloud-upload fs-1 text-primary"></i>
                      <div class="fw-medium mt-2">Seret file ke sini atau klik untuk memilih</div>
                      <div class="text-secondary small mt-1">JPG, PNG, atau WEBP. Maks 5 MB.</div>
                    </div>
                    <div id="filePreview" class="d-none">
                      <img id="previewImg" class="rounded shadow-sm" style="max-height: 150px; max-width: 100%;" alt="Preview">
                      <div id="fileName" class="text-secondary small mt-2"></div>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Catatan <span class="text-secondary">(opsional)</span></label>
                  <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: transfer dari rekening BRI a.n. Andi">{{ old('catatan') }}</textarea>
                </div>

                <button class="btn btn-primary w-100" type="submit">
                  <i class="ti ti-upload me-1"></i> Kirim Bukti Pembayaran
                </button>
              </form>
              @endif
              @endif {{-- end @if($customer->is_free) @else --}}
            </div>
          </div>
          @endif {{-- end @if($customer) --}}

        </div>

        {{-- Sidebar Column --}}
        <div class="bento-side">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-center">
                <span class="avatar avatar-sm bg-green-lt me-2"><i class="ti ti-building-bank"></i></span>
                <h3 class="card-title mb-0">Metode Pembayaran</h3>
              </div>
            </div>
            <div class="card-body">

              <div class="text-secondary small mb-3">Pilih rekening tujuan:</div>

              {{-- Accordion Payment Methods --}}
              <div class="accordion" id="paymentAccordion">

                @if(!empty($paymentSettings['accounts']) && count($paymentSettings['accounts']))
                  @foreach($paymentSettings['accounts'] as $idx => $account)
                    @php
                      $bankSlug = strtolower($account['bank_name'] ?? 'bank');
                      $logoExt = $bankSlug === 'bni' ? 'svg' : 'png';
                    @endphp
                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#bank-{{ $idx }}">
                          <div class="d-flex align-items-center gap-2" style="min-height: 32px;">
                            <img src="{{ asset('img/banks/' . $bankSlug . '.' . $logoExt) }}" alt="{{ $account['bank_name'] }}" style="height: 28px; width: auto; object-fit: contain;" onerror="this.outerHTML='<span class=fw-bold>{{ $account['bank_name'] ?? '' }}</span>'">
                          </div>
                        </button>
                      </h2>
                      <div id="bank-{{ $idx }}" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                        <div class="accordion-body text-center py-3">
                          <div class="font-monospace fw-bold fs-2 text-primary mb-1">{{ $account['account_number'] ?? '-' }}</div>
                          <div class="text-secondary small mb-2">a.n. <strong>{{ $account['account_holder'] ?? '-' }}</strong></div>
                          <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyToClipboard('{{ $account['account_number'] ?? '' }}')">
                            <i class="ti ti-copy me-1"></i>Salin
                          </button>
                        </div>
                      </div>
                    </div>
                  @endforeach
                @endif

                {{-- QRIS --}}
                @if(!empty($paymentSettings['qris']))
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed py-3 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#qris-panel">
                        <div class="d-flex align-items-center gap-2" style="min-height: 32px;">
                          <img src="{{ asset('img/banks/qris.png') }}" alt="QRIS" style="height: 28px; width: auto; object-fit: contain;" onerror="this.outerHTML='<span class=fw-bold>QRIS</span>'">
                        </div>
                      </button>
                    </h2>
                    <div id="qris-panel" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                      <div class="accordion-body text-center py-3">
                        <a href="{{ $paymentSettings['qris']['image_url'] }}" target="_blank" rel="noopener">
                          <img class="rounded shadow-sm" src="{{ $paymentSettings['qris']['image_url'] }}" alt="{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}" style="max-width: 160px; width: 100%;">
                        </a>
                        <div class="fw-medium small mt-2">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</div>
                        @if(!empty($paymentSettings['qris']['notes']))
                          <div class="text-secondary small mt-1">{{ $paymentSettings['qris']['notes'] }}</div>
                        @endif
                      </div>
                    </div>
                  </div>
                @endif

              </div>

              {{-- Payment Note --}}
              @if(!empty($paymentSettings['notes']))
                <div class="alert alert-info mb-0 mt-3">
                  <div class="d-flex align-items-start">
                    <i class="ti ti-info-circle me-2 mt-1"></i>
                    <div class="small">{{ $paymentSettings['notes'] }}</div>
                  </div>
                </div>
              @endif

            </div>
          </div>
        </div>

      </div>
      {{-- End Bento Grid --}}

      {{-- Footer --}}
      <div class="pay-footer">
        &copy; {{ date('Y') }} Netking &bull; Powered by Netking
      </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="copyToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="ti ti-check me-1"></i> Nomor rekening berhasil disalin!</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <!-- Tabler Core -->
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
  <script>
    // Copy to clipboard with toast
    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        const toast = new bootstrap.Toast(document.getElementById('copyToast'), { delay: 2500 });
        toast.show();
      });
    }

    // Loading state on search form submit
    document.querySelector('form[action]').addEventListener('submit', function() {
      const btn = document.getElementById('btnCekTagihan');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mencari...';
    });

    // Auto-format customer code: uppercase + NK prefix
    const codeInput = document.querySelector('input[name="customer_code"]');
    if (codeInput) {
      codeInput.addEventListener('input', function() {
        let val = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (/^\d+$/.test(val) && val.length > 0) {
          val = 'NK' + val.padStart(6, '0');
        }
        this.value = val;
      });
    }

    // Drag & drop + preview
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    if (dropZone && fileInput) {
      ['dragenter','dragover'].forEach(e => {
        dropZone.addEventListener(e, function(ev) { ev.preventDefault(); dropZone.classList.add('drag-over'); });
      });
      ['dragleave','drop'].forEach(e => {
        dropZone.addEventListener(e, function(ev) { ev.preventDefault(); dropZone.classList.remove('drag-over'); });
      });
      dropZone.addEventListener('drop', function(ev) {
        if (ev.dataTransfer.files.length) {
          fileInput.files = ev.dataTransfer.files;
          showPreview(ev.dataTransfer.files[0]);
        }
      });
      fileInput.addEventListener('change', function() {
        if (this.files.length) showPreview(this.files[0]);
      });
      function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewImg').src = e.target.result;
          document.getElementById('fileName').textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';
          document.getElementById('dropPlaceholder').classList.add('d-none');
          document.getElementById('filePreview').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
      }
    }

    // Confetti on successful upload
    @if(session('success'))
    (function() {
      const colors = ['#2563eb','#0ea5e9','#16a34a','#eab308','#ef4444','#8b5cf6'];
      for (let i = 0; i < 60; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left = Math.random() * 100 + 'vw';
        piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        piece.style.animationDelay = Math.random() * 1.5 + 's';
        piece.style.animationDuration = (2 + Math.random() * 2) + 's';
        document.body.appendChild(piece);
        setTimeout(() => piece.remove(), 5000);
      }
    })();
    @endif
  </script>
</body>
</html>
