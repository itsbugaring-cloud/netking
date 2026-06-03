@extends('layouts.app')

@section('title', 'Import Customers')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
  <div>
    <h4>Import Customers</h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
        <li class="breadcrumb-item active">Import</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class='bx bx-arrow-back me-1'></i>Back
  </a>
</div>

<div class="row" style="gap:1.25rem 0;">
  <div class="col-md-8">
    <form action="{{ route('admin.customers.import.process') }}" method="POST" enctype="multipart/form-data" id="import-form">
      @csrf
      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class='bx bx-upload me-2' style="color:#2563eb;"></i>Upload File</span>
        </div>
        <div class="card-body">
          @if($errors->any())
          <div class="alert alert-danger">
            <strong>Import errors:</strong>
            <ul class="mb-0 mt-1">
              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Select Area <span class="text-danger">*</span></label>
            <select name="area_id" class="form-select" required>
              <option value="">Choose area...</option>
              @foreach($areas ?? [] as $area)
              <option value="{{ $area->id }}">{{ $area->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Partner <span class="text-danger">*</span></label>
            <select name="partner_id" class="form-select" required>
              <option value="">Choose partner...</option>
              @foreach($partners ?? [] as $partner)
              <option value="{{ $partner->id }}">{{ $partner->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Excel / CSV File <span class="text-danger">*</span></label>
            <div id="file-dropzone" style="border:2px dashed #cbd5e1; border-radius:12px; padding:2rem; text-align:center; cursor:pointer; transition: all 0.2s; background:#fafbfc;">
              <i class='bx bx-cloud-upload' style="font-size:2.5rem; color:#94a3b8;"></i>
              <div style="font-size:0.9375rem; font-weight:500; color:#475569; margin-top:0.5rem;">
                Drag & drop file here or <span style="color:#2563eb; text-decoration:underline;">browse</span>
              </div>
              <div style="font-size:0.75rem; color:#94a3b8; margin-top:0.25rem;">Supports .xlsx, .xls, .csv</div>
              <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" required style="display:none;">
              <div id="file-info" style="display:none; margin-top:0.75rem;">
                <span class="badge" style="background:#dbeafe; color:#2563eb; padding:6px 12px; border-radius:8px; font-size:0.8125rem;">
                  <i class='bx bx-file me-1'></i><span id="file-name"></span>
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary">
            <i class='bx bx-upload me-1'></i>Import Customers
          </button>
        </div>
      </div>
    </form>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class='bx bx-info-circle me-2' style="color:#06b6d4;"></i>File Format</span>
      </div>
      <div class="card-body">
        <p style="font-size:0.8125rem; color:#64748b;">The Excel file must have the following columns:</p>
        <table class="table" style="font-size:0.8125rem;">
          <thead>
            <tr>
              <th>Column</th>
              <th>Required</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>name</code></td>
              <td><span class="badge-status badge-active">Yes</span></td>
            </tr>
            <tr>
              <td><code>pppoe_user</code></td>
              <td><span class="badge-status badge-active">Yes</span></td>
            </tr>
            <tr>
              <td><code>pppoe_pass</code></td>
              <td><span class="badge-status badge-active">Yes</span></td>
            </tr>
            <tr>
              <td><code>phone</code></td>
              <td><span class="badge-status badge-inactive">No</span></td>
            </tr>
            <tr>
              <td><code>address</code></td>
              <td><span class="badge-status badge-inactive">No</span></td>
            </tr>
            <tr>
              <td><code>package_id</code></td>
              <td><span class="badge-status badge-inactive">No</span></td>
            </tr>
          </tbody>
        </table>
      </div>
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