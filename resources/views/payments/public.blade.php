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
  </style>
</head>
<body class="d-flex flex-column">
  <div class="page page-center">
    <div class="container-xl">

      <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="avatar avatar-lg bg-primary-lt">
              <i class="ti ti-credit-card"></i>
            </span>
          </div>
          <div class="col">
            <h2 class="page-title">Pembayaran Netking</h2>
            <div class="text-secondary">Cek tagihan, lihat rekening resmi atau QRIS, lalu unggah bukti transfer tanpa login portal customer.</div>
          </div>
        </div>
      </div>

      <div class="row g-4">
      <div class="col-lg-7 col-md-7">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Cek Tagihan Pelanggan</h3>
          </div>
          <div class="card-body">
            <p class="text-secondary mb-3">Masukkan ID pelanggan untuk menampilkan tagihan aktif dan form upload bukti pembayaran.</p>

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
                  <input type="text" class="form-control" name="customer_code" value="{{ old('customer_code', $customerCode) }}" placeholder="Masukkan ID pelanggan, contoh NK000123" maxlength="32">
                </div>
                <div class="col-auto">
                  <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> Cek Tagihan</button>
                </div>
              </div>
            </form>

            <div class="row g-3 mt-3">
              <div class="col-md-4">
                <div class="p-3 rounded border">
                  <span class="avatar avatar-sm bg-primary-lt mb-2">
                    <i class="ti ti-search"></i>
                  </span>
                  <div class="fw-medium mb-1">1. Cek tagihan</div>
                  <div class="text-secondary small">Masukkan ID pelanggan untuk menampilkan semua tagihan aktif yang belum lunas.</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 rounded border">
                  <span class="avatar avatar-sm bg-primary-lt mb-2">
                    <i class="ti ti-cash"></i>
                  </span>
                  <div class="fw-medium mb-1">2. Bayar sesuai nominal</div>
                  <div class="text-secondary small">Gunakan rekening atau QRIS resmi yang tampil di halaman ini sesuai jumlah tagihan.</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 rounded border">
                  <span class="avatar avatar-sm bg-primary-lt mb-2">
                    <i class="ti ti-shield-check"></i>
                  </span>
                  <div class="fw-medium mb-1">3. Upload bukti transfer</div>
                  <div class="text-secondary small">Admin akan meninjau bukti bayar Anda sebelum tagihan dikonfirmasi lunas.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5 col-md-5">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Metode Pembayaran Resmi</h3>
          </div>
          <div class="card-body">
              @if(!empty($paymentSettings['accounts']) && count($paymentSettings['accounts']))
                <div class="list-group list-group-flush mb-3">
                  @foreach($paymentSettings['accounts'] as $account)
                    <div class="list-group-item">
                      <div class="text-uppercase text-secondary small">{{ $account['bank_name'] ?? '-' }}</div>
                      <div class="fw-bold">{{ $account['account_number'] ?? '-' }}</div>
                      <div class="text-secondary">a.n. {{ $account['account_holder'] ?? '-' }}</div>
                    </div>
                  @endforeach
                </div>
              @endif

              @if(!empty($paymentSettings['qris']))
                <div class="mb-3">
                  <h4 class="mb-2">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</h4>
                  <a href="{{ $paymentSettings['qris']['image_url'] }}" target="_blank" rel="noopener">
                    <img class="img-fluid rounded" src="{{ $paymentSettings['qris']['image_url'] }}" alt="{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}">
                  </a>
                  @if(!empty($paymentSettings['qris']['notes']))
                    <div class="text-secondary mt-2">{{ $paymentSettings['qris']['notes'] }}</div>
                  @endif
                </div>
              @endif

              @if(!empty($paymentSettings['notes']))
                <div class="alert alert-info">
                  <div class="d-flex">
                    <div>
                      <i class="ti ti-info-circle me-2"></i>
                    </div>
                    <div>{{ $paymentSettings['notes'] }}</div>
                  </div>
                </div>
              @endif
          </div>
        </div>
      </div>
      </div>

      <div class="row g-4 mt-0">
        <div class="col-lg-7 col-md-7">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Tata Cara Bayar</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">Langkahnya dibuat runtut dari atas ke bawah agar pelanggan tidak bingung.</p>
              <div class="list-group list-group-flush">
                <div class="list-group-item">
                  <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm bg-primary-lt">1</span>
                    <div>
                      <div class="fw-medium">Masukkan ID pelanggan</div>
                      <div class="text-secondary">ID pelanggan berbentuk seperti <strong>NK000123</strong>. Setelah dimasukkan, sistem akan menampilkan tagihan aktif Anda.</div>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm bg-primary-lt">2</span>
                    <div>
                      <div class="fw-medium">Pilih tagihan yang mau dibayar</div>
                      <div class="text-secondary">Kalau ada lebih dari satu tagihan aktif, pilih invoice yang ingin dibayar terlebih dahulu.</div>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm bg-primary-lt">3</span>
                    <div>
                      <div class="fw-medium">Bayar sesuai nominal</div>
                      <div class="text-secondary">Gunakan nomor rekening atau QRIS resmi yang tampil di halaman ini. Nominal harus sesuai dengan tagihan yang dipilih.</div>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm bg-primary-lt">4</span>
                    <div>
                      <div class="fw-medium">Unggah bukti transfer</div>
                      <div class="text-secondary">Setelah transfer, unggah foto bukti pembayaran agar admin bisa meninjau dan mengonfirmasi tagihan Anda.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5 col-md-5">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Pertanyaan Umum</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">Jawaban singkat untuk hal yang biasanya ditanyakan pelanggan.</p>
              <div class="list-group list-group-flush">
                <div class="list-group-item">
                  <div class="fw-medium mb-1">ID pelanggan saya tidak ketemu</div>
                  <div class="text-secondary">Pastikan formatnya benar, misalnya <strong>NK000123</strong>. Jika masih tidak ditemukan, hubungi admin Netking.</div>
                </div>
                <div class="list-group-item">
                  <div class="fw-medium mb-1">Sudah transfer tapi belum dikonfirmasi</div>
                  <div class="text-secondary">Unggah bukti pembayaran dari halaman ini. Admin akan meninjau dan mengubah status tagihan setelah bukti valid.</div>
                </div>
                <div class="list-group-item">
                  <div class="fw-medium mb-1">Bisa bayar lebih dari satu tagihan?</div>
                  <div class="text-secondary">Bisa, tetapi pilih dan unggah bukti untuk masing-masing tagihan agar pencatatannya tidak tertukar.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if($customer)
        <div class="card mt-4">
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
                          <a class="btn btn-outline-secondary" href="{{ route('payment.public', ['customerCode' => $customer->customer_code, 'invoice' => $invoice->id]) }}">Pilih Tagihan</a>
                        @else
                          <span class="btn btn-primary disabled">Tagihan Dipilih</span>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        @if($selectedInvoice)
          <div class="row g-4 mt-0">
            <div class="col-lg-7 col-md-7">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Instruksi Pembayaran Tagihan</h3>
                </div>
                <div class="card-body">
                  <p class="text-secondary mb-2">Nominal yang harus dibayar untuk tagihan yang dipilih.</p>
                  <div class="fw-medium mb-2">{{ $selectedInvoice->invoice_number }}</div>
                  <p class="text-secondary mb-3">Bayar sesuai nominal tagihan berikut: <strong>Rp {{ number_format($selectedInvoice->amount, 0, ',', '.') }}</strong>, lalu unggah bukti transfer di form sebelah.</p>
                  <div class="text-secondary small">{{ $paymentSettings['notes'] ?? 'Transfer atau bayar via QRIS sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}</div>
                </div>
              </div>
            </div>

            <div class="col-lg-5 col-md-5">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Upload Bukti Pembayaran</h3>
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
                      <textarea name="notes" class="form-control" placeholder="Contoh: transfer dari rekening BRI a.n. Andi">{{ old('notes', $selectedInvoice->payment_proof_notes) }}</textarea>
                    </div>

                    @if($selectedInvoice->payment_proof_url)
                      <div class="mb-3">
                        <a class="btn btn-outline-secondary" href="{{ $selectedInvoice->payment_proof_url }}" target="_blank" rel="noopener">Lihat Bukti yang Sudah Diunggah</a>
                      </div>
                    @endif

                    <button class="btn btn-primary w-100" type="submit">
                      <i class="ti ti-upload"></i>
                      {{ $selectedInvoice->payment_review_status === 'submitted' ? 'Ganti Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @endif
      @endif

    </div>
  </div>

  <!-- Tabler Core -->
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
