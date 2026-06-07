@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')

<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-cog'></i> Konfigurasi Sistem</div>
      <h1 class="ms-page-title">Pengaturan</h1>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-xl-8">
      <div class="ms-panel">
        <div class="ms-panel-body p-0">
          <ul class="nav nav-tabs px-4 pt-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-general">Umum</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-billing">Tagihan</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-notif">Notifikasi</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-telegram">Telegram Bot</a></li>
          </ul>

          <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="tab-general">
              <form id="form-general" data-group="general">
                @csrf
                <input type="hidden" name="group" value="general">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Nama Perusahaan</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? 'NETKING' }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email Perusahaan</label>
                    <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Zona Waktu</label>
                    <select name="timezone" class="form-select">
                      <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB, UTC+7)</option>
                      <option value="Asia/Makassar" {{ ($settings['timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA, UTC+8)</option>
                      <option value="Asia/Jayapura" {{ ($settings['timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT, UTC+9)</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Mata Uang</label>
                    <select name="currency" class="form-select">
                      <option value="IDR" {{ ($settings['currency'] ?? '') == 'IDR' ? 'selected' : '' }}>IDR — Rupiah Indonesia (Rp)</option>
                      <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD — Dolar Amerika ($)</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Bahasa</label>
                    <select name="language" class="form-select">
                      <option value="id" {{ ($settings['language'] ?? '') == 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                      <option value="en" {{ ($settings['language'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                    </select>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Umum</button>
                </div>
              </form>
            </div>

            <div class="tab-pane fade" id="tab-billing">
              <form id="form-billing" data-group="billing">
                @csrf
                <input type="hidden" name="group" value="billing">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Hari Tagihan</label>
                    <select name="billing_day" class="form-select">
                      @for($i = 1; $i <= 28; $i++)
                      <option value="{{ $i }}" {{ ($settings['billing_day'] ?? '1') == $i ? 'selected' : '' }}>{{ $i }}</option>
                      @endfor
                    </select>
                    <div class="form-text">Tanggal dalam sebulan untuk pembuatan invoice otomatis.</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Denda Keterlambatan (%)</label>
                    <input type="number" name="late_fee_percent" class="form-control" value="{{ $settings['late_fee_percent'] ?? '5' }}" min="0" max="100">
                    <div class="form-text">Persentase yang ditambahkan setelah masa tenggang.</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Masa Tenggang (hari)</label>
                    <input type="number" name="grace_period_days" class="form-control" value="{{ $settings['grace_period_days'] ?? '7' }}" min="0" max="30">
                    <div class="form-text">Hari sebelum ditandai sebagai jatuh tempo.</div>
                  </div>
                  <div class="col-12">
                    <hr class="my-1">
                    <div class="form-text mb-2">Akun pembayaran manual untuk aplikasi pelanggan dan halaman invoice.</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Nama Bank 1</label>
                    <input type="text" name="payment_bank_1_name" class="form-control" value="{{ $settings['payment_bank_1_name'] ?? 'BRI' }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Nomor Rekening Bank 1</label>
                    <input type="text" name="payment_bank_1_number" class="form-control" value="{{ $settings['payment_bank_1_number'] ?? '159601000592564' }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Pemilik Rekening Bank 1</label>
                    <input type="text" name="payment_bank_1_holder" class="form-control" value="{{ $settings['payment_bank_1_holder'] ?? 'Deni Firmansyah' }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Nama Bank 2</label>
                    <input type="text" name="payment_bank_2_name" class="form-control" value="{{ $settings['payment_bank_2_name'] ?? 'BNI' }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Nomor Rekening Bank 2</label>
                    <input type="text" name="payment_bank_2_number" class="form-control" value="{{ $settings['payment_bank_2_number'] ?? '0320906963' }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Pemilik Rekening Bank 2</label>
                    <input type="text" name="payment_bank_2_holder" class="form-control" value="{{ $settings['payment_bank_2_holder'] ?? 'Deni Firmansyah' }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Label QRIS</label>
                    <input type="text" name="payment_qris_label" class="form-control" value="{{ $settings['payment_qris_label'] ?? 'QRIS NETKING' }}">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">URL Gambar QRIS</label>
                    <input type="text" name="payment_qris_image_url" class="form-control" value="{{ $settings['payment_qris_image_url'] ?? url('/img/payments/QRIS-NETKING.jpg') }}">
                    <div class="form-text">Gunakan URL publik agar bisa tampil di aplikasi pelanggan.</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Catatan QRIS</label>
                    <textarea name="payment_qris_notes" class="form-control" rows="2">{{ $settings['payment_qris_notes'] ?? 'Scan QRIS resmi NETKING, bayar sesuai nominal invoice, lalu upload bukti pembayaran agar admin dapat memverifikasi pembayaran Anda.' }}</textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Catatan Pembayaran Manual</label>
                    <textarea name="manual_payment_notes" class="form-control" rows="3">{{ $settings['manual_payment_notes'] ?? 'Transfer sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}</textarea>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Tagihan</button>
                </div>
              </form>
            </div>

            <div class="tab-pane fade" id="tab-notif">
              <form id="form-notifications" data-group="notifications">
                @csrf
                <input type="hidden" name="group" value="notifications">
                <div class="d-flex flex-column gap-3">
                  <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background:var(--nk-bg-subtle);">
                    <div>
                      <div style="font-weight:600;">Notifikasi Email</div>
                      <div style="font-size:.78rem;color:#64748b;">Peringatan invoice dan notifikasi sistem</div>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="notif_email" value="1" {{ ($settings['notif_email'] ?? '1') == '1' ? 'checked' : '' }}>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background:var(--nk-bg-subtle);">
                    <div>
                      <div style="font-weight:600;">Notifikasi SMS</div>
                      <div style="font-size:.78rem;color:#64748b;">OTP dan peringatan kritis</div>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="notif_sms" value="1" {{ ($settings['notif_sms'] ?? '0') == '1' ? 'checked' : '' }}>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Notifikasi</button>
                </div>
              </form>
            </div>

            <div class="tab-pane fade" id="tab-telegram">
              <form id="form-telegram" data-group="telegram_bot">
                @csrf
                <input type="hidden" name="group" value="telegram_bot">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Mode Bot</label>
                    <select name="telegram_config_mode" class="form-select">
                      <option value="test" {{ ($settings['telegram_config_mode'] ?? 'test') === 'test' ? 'selected' : '' }}>TEST</option>
                      <option value="live" {{ ($settings['telegram_config_mode'] ?? '') === 'live' ? 'selected' : '' }}>LIVE</option>
                    </select>
                    <div class="form-text">Gunakan mode TEST untuk uji coba, LIVE untuk produksi.</div>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">Allowed Chat IDs</label>
                    <input type="text" name="telegram_config_allowed_ids" class="form-control" value="{{ $settings['telegram_config_allowed_ids'] ?? '' }}" placeholder="Contoh: 299890939,123456789">
                    <div class="form-text">Pisahkan dengan koma. Kosongkan untuk mengizinkan semua chat ID.</div>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Bot Token</label>
                    <input type="password" name="telegram_config_bot_token" class="form-control" placeholder="Isi token baru jika ingin mengganti">
                    <div class="form-text">Token tersimpan: <strong>{{ $telegram['masked_token'] ?? '-' }}</strong></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Bot Secret Webhook</label>
                    <input type="text" name="telegram_config_bot_secret" class="form-control" value="{{ $settings['telegram_config_bot_secret'] ?? '' }}" placeholder="Secret path webhook">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Admin Chat ID</label>
                    <input type="text" name="telegram_config_admin_chat_id" class="form-control" value="{{ $settings['telegram_config_admin_chat_id'] ?? '' }}" placeholder="Contoh: 299890939">
                  </div>
                  <div class="col-12">
                    <div class="p-3 rounded" style="background:var(--nk-bg-subtle);">
                      <div class="fw-semibold mb-1">Webhook URL Aktif</div>
                      <div class="small text-muted" id="telegram-webhook-preview">{{ $telegram['webhook_url'] ?? '-' }}</div>
                    </div>
                  </div>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-2">
                  <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Telegram</button>
                  <button type="button" class="ms-btn-secondary" id="btn-telegram-test"><i class='bx bx-check-shield'></i> Tes Token</button>
                  <button type="button" class="ms-btn-ghost" id="btn-telegram-webhook"><i class='bx bx-link'></i> Set Webhook</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-info-circle me-2' style="color:#2563eb;"></i>Info Sistem</h5>
        </div>
        <div class="ms-table-shell">
          <table class="table table-sm mb-0">
            <tbody>
              <tr><td class="text-muted">Versi</td><td class="text-end fw-bold">1.0.0</td></tr>
              <tr><td class="text-muted">Lingkungan</td><td class="text-end"><span class="badge-status badge-active">{{ app()->environment() }}</span></td></tr>
              <tr><td class="text-muted">PHP</td><td class="text-end">{{ phpversion() }}</td></tr>
              <tr><td class="text-muted">Laravel</td><td class="text-end">{{ app()->version() }}</td></tr>
              <tr><td class="text-muted">Database</td><td class="text-end">{{ config('database.default') }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="ms-panel mt-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-shield me-2' style="color:#2563eb;"></i>Aksi Cepat</h5>
        </div>
        <div class="ms-panel-body d-grid gap-2">
          <button class="ms-btn-secondary" onclick="clearCache()"><i class='bx bx-refresh'></i> Bersihkan Cache</button>
          <button class="ms-btn-ghost" onclick="testEmail()"><i class='bx bx-mail-send'></i> Uji Email</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(function() {
    $('form[id^="form-"]').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('button[type="submit"]');
      var originalBtnHtml = $btn.html();
      $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Menyimpan...');

      $form.find('input[type="checkbox"]').each(function() {
        if (!this.checked) {
          if (!$form.find('input[type="hidden"][name="' + this.name + '"]').length) {
            $form.append('<input type="hidden" name="' + this.name + '" value="0">');
          }
        }
      });

      $.ajax({
        url: '{{ route("admin.settings.update") }}',
        method: 'POST',
        data: $form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
          toastr.success(res.message || 'Pengaturan tersimpan');
        },
        error: function(xhr) {
          toastr.error('Gagal menyimpan: ' + (xhr.responseJSON?.message || 'Kesalahan server'));
        },
        complete: function() {
          $btn.prop('disabled', false).html(originalBtnHtml);
        }
      });
    });

    $('#btn-telegram-test').on('click', function() {
      var $btn = $(this);
      var html = $btn.html();
      $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Testing...');

      $.ajax({
        url: '{{ route("admin.settings.telegram.test-token") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          var botName = res?.bot?.name || '-';
          var botUser = res?.bot?.username || '-';
          toastr.success((res.message || 'Token valid') + ' (' + botName + ' @' + botUser + ')');
        },
        error: function(xhr) {
          toastr.error(xhr.responseJSON?.message || 'Gagal tes token Telegram');
        },
        complete: function() {
          $btn.prop('disabled', false).html(html);
        }
      });
    });

    $('#btn-telegram-webhook').on('click', function() {
      var $btn = $(this);
      var html = $btn.html();
      $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Mengatur...');

      $.ajax({
        url: '{{ route("admin.settings.telegram.set-webhook") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          $('#telegram-webhook-preview').text(res.webhook_url || '-');
          toastr.success(res.message || 'Webhook berhasil diset');
        },
        error: function(xhr) {
          toastr.error(xhr.responseJSON?.message || 'Gagal set webhook');
        },
        complete: function() {
          $btn.prop('disabled', false).html(html);
        }
      });
    });
  });

  function clearCache() {
    $.post('{{ route("admin.settings.update") }}', {
      _token: '{{ csrf_token() }}',
      group: 'cache',
      action: 'clear'
    }).done(function() {
      toastr.success('Cache berhasil dibersihkan');
    });
  }

  function testEmail() {
    toastr.info('Fitur uji email segera hadir');
  }
</script>
@endsection
