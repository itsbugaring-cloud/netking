@extends('layouts.app')

@section('title', 'Impor Pelanggan')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-upload'></i> Data Pelanggan</div>
      <h1 class="ms-page-title">Impor Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.customers.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

@if(session('success'))
<div class="alert alert-success mb-3">
  <i class='bx bx-check-circle me-1'></i>{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger mb-3">
  <i class='bx bx-error-circle me-1'></i>{{ session('error') }}
</div>
@endif

<div class="row" style="gap:1rem 0;">
  <div class="col-md-8">
    <form action="{{ route('admin.customers.import.process') }}" method="POST" enctype="multipart/form-data" id="import-form">
      @csrf
      <div class="ms-panel">
        <div class="ms-panel-head">
          <span class="ms-panel-title"><i class='bx bx-upload me-2' style="color:#2563eb;"></i>Unggah File</span>
        </div>
        <div class="ms-panel-body">
          @if($errors->any())
          <div class="alert alert-danger">
            <strong>Kesalahan impor:</strong>
            <ul class="mb-0 mt-1">
              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Pilih Area <span class="text-danger">*</span></label>
            <select name="area_id" class="form-select" required>
              <option value="">Pilih area...</option>
              @foreach($areas ?? [] as $area)
              <option value="{{ $area->id }}">{{ $area->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Mitra <span class="text-danger">*</span></label>
            <select name="partner_id" class="form-select" required>
              <option value="">Pilih mitra...</option>
              @foreach($partners ?? [] as $partner)
              <option value="{{ $partner->id }}">{{ $partner->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">File Excel / CSV <span class="text-danger">*</span></label>
            <div id="file-dropzone" style="border:2px dashed #cbd5e1; border-radius:12px; padding:2rem; text-align:center; cursor:pointer; transition: all 0.2s; background:#fafbfc;">
              <i class='bx bx-cloud-upload' style="font-size:2.5rem; color:#94a3b8;"></i>
              <div style="font-size:0.9375rem; font-weight:500; color:#475569; margin-top:0.5rem;">
                Seret & lepas file ke sini atau <span style="color:#2563eb; text-decoration:underline;">pilih file</span>
              </div>
              <div style="font-size:0.75rem; color:#94a3b8; margin-top:0.25rem;">Mendukung .xlsx, .xls, .csv</div>
              <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" required style="display:none;">
              <div id="file-info" style="display:none; margin-top:0.75rem;">
                <span class="badge" style="background:#dbeafe; color:#2563eb; padding:6px 12px; border-radius:8px; font-size:0.8125rem;">
                  <i class='bx bx-file me-1'></i><span id="file-name"></span>
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="ms-panel-foot text-end">
          <button type="submit" class="ms-btn">
            <i class='bx bx-upload me-1'></i>Impor Pelanggan
          </button>
        </div>
      </div>
    </form>
  </div>
  <div class="col-md-4">
    <div class="ms-panel">
      <div class="ms-panel-head">
        <span class="ms-panel-title"><i class='bx bx-info-circle me-2' style="color:#06b6d4;"></i>Format File</span>
      </div>
      <div class="ms-panel-body">
        <p style="font-size:0.8125rem; color:#64748b;">File Excel harus memiliki kolom berikut:</p>
        <table class="table" style="font-size:0.8125rem;">
          <thead>
            <tr>
              <th>Kolom</th>
              <th>Wajib</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>name</code></td>
              <td><span class="badge-status badge-active">Ya</span></td>
            </tr>
            <tr>
              <td><code>pppoe_user</code></td>
              <td><span class="badge-status badge-active">Ya</span></td>
            </tr>
            <tr>
              <td><code>pppoe_pass</code></td>
              <td><span class="badge-status badge-active">Ya</span></td>
            </tr>
            <tr>
              <td><code>phone</code></td>
              <td><span class="badge-status badge-inactive">Tidak</span></td>
            </tr>
            <tr>
              <td><code>address</code></td>
              <td><span class="badge-status badge-inactive">Tidak</span></td>
            </tr>
            <tr>
              <td><code>package_id</code></td>
              <td><span class="badge-status badge-inactive">Tidak</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row mt-1">
  <div class="col-12">
    <form action="{{ route('admin.customers.import-billing-start') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="ms-panel">
        <div class="ms-panel-head">
          <span class="ms-panel-title">
            <i class='bx bx-calendar-edit me-2' style="color:#0ea5e9;"></i>Update Massal Tanggal Mulai Tagihan (Existing)
          </span>
        </div>
        <div class="ms-panel-body">
          @if(session('import_billing_errors'))
          <div class="alert alert-warning">
            <strong>Catatan validasi (maks 30 baris):</strong>
            <ul class="mb-0 mt-1">
              @foreach(session('import_billing_errors') as $msg)
              <li>{{ $msg }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <div class="alert alert-info">
            Pakai file XLSX/CSV/TXT untuk update pelanggan yang sudah ada. Sistem mencocokkan berdasarkan <code>pppoe_user</code> lalu mengubah <code>billing_start_date</code>.
            Invoice lama tidak diubah otomatis dan proses ini tidak menyentuh ONT/MikroTik.
          </div>

          <div class="row g-3">
            <div class="col-lg-7">
              <label class="form-label">File XLSX/CSV Update <span class="text-danger">*</span></label>
              <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.txt" required>
              <div class="form-text">Kolom wajib: <code>pppoe_user</code> dan <code>billing_start_date</code> (alternatif: <code>tgl_aktif</code> / <code>tanggal_pasang</code>).</div>
            </div>
            <div class="col-lg-5 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" value="1" id="dry-run" name="dry_run" checked>
                <label class="form-check-label" for="dry-run">
                  Dry run dulu (preview tanpa update data)
                </label>
              </div>
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th style="width:35%;">pppoe_user</th>
                  <th style="width:35%;">billing_start_date</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>NGS-001</code></td>
                  <td><code>2025-08-05</code></td>
                  <td>Format YYYY-MM-DD</td>
                </tr>
                <tr>
                  <td><code>NPL-002</code></td>
                  <td><code>2025-09-12</code></td>
                  <td>Tanggal real pemasangan</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="ms-panel-foot text-end">
          <button type="submit" class="ms-btn">
            <i class='bx bx-save me-1'></i>Proses Update Tanggal Tagihan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
  var dropzone = document.getElementById('file-dropzone');
  var fileInput = document.getElementById('file-input');
  var fileInfo = document.getElementById('file-info');
  var fileName = document.getElementById('file-name');

  dropzone.addEventListener('click', function() {
    fileInput.click();
  });
  dropzone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropzone.style.borderColor = '#2563eb';
    dropzone.style.background = '#eff6ff';
  });
  dropzone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dropzone.style.borderColor = '#cbd5e1';
    dropzone.style.background = '#fafbfc';
  });
  dropzone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropzone.style.borderColor = '#2563eb';
    dropzone.style.background = '#eff6ff';
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      showFile(e.dataTransfer.files[0]);
    }
  });
  fileInput.addEventListener('change', function() {
    if (fileInput.files.length) showFile(fileInput.files[0]);
  });

  function showFile(f) {
    fileName.textContent = f.name + ' (' + (f.size / 1024).toFixed(1) + ' KB)';
    fileInfo.style.display = 'block';
  }
</script>
@endsection
