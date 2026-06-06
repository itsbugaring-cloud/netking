@extends('layouts.app')
@section('title', 'IPAM Settings')

@section('content')
<div class="ms-page nk-list-page ipam-settings-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-cog'></i> IPAM</div>
      <h1 class="ms-page-title">IPAM Settings</h1>
    </div>
  </div>

  @if (session('success'))
  <div class="alert alert-success mb-3" style="border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
  </div>
  @endif
  @if (session('error'))
  <div class="alert mb-3" style="background:color-mix(in srgb,var(--red) 10%,var(--surface));border:1px solid color-mix(in srgb,var(--red) 25%,var(--border));color:var(--red);border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
  </div>
  @endif

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-cog me-2'></i>Pengaturan Koneksi MikroTik</h5>
    </div>
    <div class="p-4">
      <form action="{{ route('admin.ipam.settings.update') }}" method="POST">
        @csrf

        <div class="row g-4">
          {{-- Authentication --}}
          <div class="col-12">
            <h6 style="font-size:.85rem;font-weight:600;color:var(--txt-2);margin-bottom:.75rem;">
              <i class='bx bx-lock-alt me-1'></i> Autentikasi Default
            </h6>
          </div>
          <div class="col-md-6">
            <label class="form-label" style="font-size:.8rem;">Username MikroTik</label>
            <input type="text" name="ipam_mikrotik_username" class="form-control form-control-sm"
              value="{{ $settings['ipam.mikrotik_username'] }}" placeholder="admin">
          </div>
          <div class="col-md-6">
            <label class="form-label" style="font-size:.8rem;">Password MikroTik</label>
            <input type="password" name="ipam_mikrotik_password" class="form-control form-control-sm"
              placeholder="Kosongkan jika tidak ingin mengubah">
            <small class="text-muted" style="font-size:.7rem;">Kosongkan jika tidak ingin mengubah password saat ini.</small>
          </div>

          {{-- Connection --}}
          <div class="col-12 mt-4">
            <h6 style="font-size:.85rem;font-weight:600;color:var(--txt-2);margin-bottom:.75rem;">
              <i class='bx bx-shield me-1'></i> Koneksi & Keamanan
            </h6>
          </div>
          <div class="col-md-6">
            <div class="form-check">
              <input type="hidden" name="ipam_use_https" value="0">
              <input type="checkbox" name="ipam_use_https" value="1" class="form-check-input" id="use-https"
                {{ $settings['ipam.use_https'] ? 'checked' : '' }}>
              <label class="form-check-label" for="use-https" style="font-size:.85rem;">
                Gunakan HTTPS
              </label>
            </div>
            <small class="text-muted" style="font-size:.7rem;">Aktifkan jika RouterOS dikonfigurasi dengan SSL.</small>
          </div>
          <div class="col-md-6">
            <div class="form-check">
              <input type="hidden" name="ipam_allow_insecure_tls" value="0">
              <input type="checkbox" name="ipam_allow_insecure_tls" value="1" class="form-check-input" id="allow-insecure"
                {{ $settings['ipam.allow_insecure_tls'] ? 'checked' : '' }}>
              <label class="form-check-label" for="allow-insecure" style="font-size:.85rem;">
                Allow Insecure TLS
              </label>
            </div>
            <small class="text-muted" style="font-size:.7rem;">Izinkan self-signed certificate (tidak direkomendasikan untuk produksi).</small>
          </div>

          {{-- Performance --}}
          <div class="col-12 mt-4">
            <h6 style="font-size:.85rem;font-weight:600;color:var(--txt-2);margin-bottom:.75rem;">
              <i class='bx bx-tachometer me-1'></i> Performa
            </h6>
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">Request Timeout (detik)</label>
            <input type="number" name="ipam_request_timeout_secs" class="form-control form-control-sm"
              value="{{ $settings['ipam.request_timeout_secs'] }}" min="5" max="120" required>
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">Max Scan Concurrency</label>
            <input type="number" name="ipam_max_scan_concurrency" class="form-control form-control-sm"
              value="{{ $settings['ipam.max_scan_concurrency'] }}" min="1" max="50" required>
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">Scan Cooldown (detik)</label>
            <input type="number" name="ipam_scan_cooldown_secs" class="form-control form-control-sm"
              value="{{ $settings['ipam.scan_cooldown_secs'] }}" min="5" max="300" required>
          </div>
        </div>

        <div class="mt-4 pt-3" style="border-top:1px solid var(--border);">
          <button type="submit" class="ms-btn">
            <i class='bx bx-save'></i> Simpan Pengaturan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
