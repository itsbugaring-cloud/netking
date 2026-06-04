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
    }
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
    }
    @media (max-width: 767.98px) {
      .bento-grid {
        grid-template-columns: 1fr;
      }
      .bento-grid .bento-side {
        grid-column: 1;
        grid-row: auto;
      }
    }
  </style>
</head>
<body class="d-flex flex-column">
  <div class="page">
    <div class="page-wrapper">
      <div class="page-body">
        <div class="container-xl py-4">

      {{-- Page Header --}}
      <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="avatar avatar-lg bg-primary-lt">
              <i class="ti ti-credit-card"></i>
            </span>
          </div>
          <div class="col">
            <h2 class="page-title">Pembayaran Netking</h2>
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
              <h3 class="card-title">Cek Tagihan Pelanggan</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">Masukkan ID pelanggan untuk melihat tagihan.</p>

              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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
                    <input type="text" class="form-control" name="customer_code" value="{{ old('customer_code', $customerCode) }}" placeholder="Masukkan ID pelanggan, contoh NK000568" maxlength="32">
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> Cek Tagihan</button>
                  </div>
                </div>
              </form>

              <div class="row g-3 mt-3">
                <div class="col-md-4">
                  <div class="p-3 rounded border">
                    <span class="avatar avatar-sm bg-primary-lt mb-2"><i class="ti ti-search"></i></span>
                    <div class="fw-medium mb-1">1. Cek tagihan</div>
                    <div class="text-secondary small">Ketik ID pelanggan lalu klik Cek Tagihan.</div>
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

          {{-- Customer Data & Invoice List --}}
          @if($customer)
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Data Pelanggan & Tagihan Aktif</h3>
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

              @if($invoices->isEmpty())
                <div class="alert alert-success">Tidak ada tagihan aktif untuk ID pelanggan ini.</div>
              @else
                @if($invoices->count() > 1)
                  <div class="alert alert-warning">Ada lebih dari satu tagihan aktif. Pilih tagihan yang ingin dibayar.</div>
                @endif

                <div class="list-group list-group-flush">
                  @foreach($invoices as $invoice)
                    @php
                      $isSelected = $selectedInvoice && $selectedInvoice->id === $invoice->id;
                      $badgeClass = $invoice->payment_review_status === 'submitted'
                        ? 'bg-yellow-lt'
                        : ($invoice->due_date->isPast() ? 'bg-orange-lt' : 'bg-blue-lt');
                      $statusLabel = $invoice->payment_review_status === 'submitted'
                        ? 'Menunggu Review'
                        : ($invoice->due_date->isPast() ? 'Jatuh Tempo' : 'Belum Lunas');
                    @endphp
                    <div class="list-group-item py-3">
                      <div class="row align-items-center">
                        <div class="col">
                          <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                          <div class="text-secondary small">
                            Jatuh tempo {{ $invoice->due_date->format('d M Y') }}
                            @if($invoice->is_prorated)
                              <span class="badge bg-muted-lt ms-1">Prorata</span>
                            @endif
                          </div>
                          @if($invoice->payment_review_status === 'rejected' && $invoice->payment_reject_reason)
                            <div class="text-danger small mt-1">Bukti bayar ditolak: {{ $invoice->payment_reject_reason }}</div>
                          @endif
                        </div>
                        <div class="col-auto d-flex align-items-center gap-2 flex-wrap">
                          <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                          <span class="fs-3 fw-bold">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                          @if(!$isSelected)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('payment.public', ['customerCode' => $customer->customer_code, 'invoice' => $invoice->id]) }}">Pilih</a>
                          @else
                            <span class="btn btn-primary btn-sm disabled">Dipilih</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
          @endif

          {{-- Upload Form (when invoice selected) --}}
          @if($customer && $selectedInvoice)
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Upload Bukti Pembayaran</h3>
              <div class="card-subtitle">{{ $selectedInvoice->invoice_number }} — Rp {{ number_format($selectedInvoice->amount, 0, ',', '.') }}</div>
            </div>
            <div class="card-body">
              @if($selectedInvoice->payment_review_status === 'submitted')
                <div class="alert alert-warning mb-3">Bukti pembayaran untuk tagihan ini sudah pernah dikirim dan sedang menunggu review. Jika perlu, Anda bisa ganti file bukti di bawah.</div>
              @endif
              <form method="POST" action="{{ route('payment.public.submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="customer_code" value="{{ $customer->customer_code }}">
                <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">

                <div class="mb-3">
                  <label class="form-label">Metode Pembayaran</label>
                  <select name="payment_method" class="form-select" required>
                    <option value="">Pilih metode</option>
                    <option value="transfer_bank" @selected(old('payment_method', $selectedInvoice->payment_method) === 'transfer_bank')>Transfer Bank</option>
                    <option value="qris" @selected(old('payment_method', $selectedInvoice->payment_method) === 'qris')>QRIS</option>
                    <option value="cash" @selected(old('payment_method', $selectedInvoice->payment_method) === 'cash')>Cash</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Foto Bukti Transfer</label>
                  <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                  <div class="form-hint mt-1">Format JPG, PNG, atau WEBP. Maksimal 5 MB.</div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Catatan</label>
                  <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: transfer dari rekening BRI a.n. Andi">{{ old('notes', $selectedInvoice->payment_proof_notes) }}</textarea>
                </div>

                @if($selectedInvoice->payment_proof_url)
                  <div class="mb-3">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ $selectedInvoice->payment_proof_url }}" target="_blank" rel="noopener"><i class="ti ti-eye me-1"></i>Lihat Bukti Sebelumnya</a>
                  </div>
                @endif

                <button class="btn btn-primary w-100" type="submit">
                  <i class="ti ti-upload me-1"></i>
                  {{ $selectedInvoice->payment_review_status === 'submitted' ? 'Ganti Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}
                </button>
              </form>
            </div>
          </div>
          @endif

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

              {{-- Custom Accordion Payment Methods --}}
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

        </div>
      </div>
    </div>
  </div>

  <!-- Tabler Core -->
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
  <script>
    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Nomor rekening berhasil disalin!');
      });
    }
  </script>
</body>
</html>
