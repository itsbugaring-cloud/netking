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
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-mikrotik"><i class='bx bx-server'></i> Panduan MikroTik</a></li>
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
                    <div class="form-text">Tanggal dalam sebulan untuk batas jatuh tempo pembayaran.</div>
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
                    <div class="form-text mb-2">Akun pembayaran untuk aplikasi pelanggan dan halaman pembayaran.</div>
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
                    <textarea name="payment_qris_notes" class="form-control" rows="2">{{ $settings['payment_qris_notes'] ?? 'Scan QRIS resmi NETKING, bayar sesuai nominal tagihan, lalu upload bukti pembayaran agar admin dapat memverifikasi pembayaran Anda.' }}</textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Catatan Pembayaran Manual</label>
                    <textarea name="manual_payment_notes" class="form-control" rows="3">{{ $settings['manual_payment_notes'] ?? 'Transfer sesuai nominal tagihan, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}</textarea>
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
                      <div style="font-size:.78rem;color:#64748b;">Peringatan pembayaran dan notifikasi sistem</div>
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
                  <div class="col-12">
                    <h6 class="fw-bold mb-2 pb-2 border-bottom text-primary"><i class='bx bx-server'></i> Bot MikroTik (Config & Isolir)</h6>
                  </div>
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
                    <label class="form-label">Bot Token (MikroTik)</label>
                    <input type="password" name="telegram_config_bot_token" class="form-control" placeholder="Isi token baru jika ingin mengganti">
                    <div class="form-text">Token tersimpan: <strong>{{ $telegram['masked_token'] ?? '-' }}</strong></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Bot Secret Webhook</label>
                    <input type="text" name="telegram_config_bot_secret" class="form-control" value="{{ $settings['telegram_config_bot_secret'] ?? '' }}" placeholder="Secret path webhook">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Admin Chat ID (Fallback)</label>
                    <input type="text" name="telegram_config_admin_chat_id" class="form-control" value="{{ $settings['telegram_config_admin_chat_id'] ?? '' }}" placeholder="Contoh: 299890939">
                  </div>
                  <div class="col-12 mb-3">
                    <div class="p-3 rounded" style="background:var(--nk-bg-subtle);">
                      <div class="fw-semibold mb-1">Webhook URL Aktif</div>
                      <div class="small text-muted" id="telegram-webhook-preview">{{ $telegram['webhook_url'] ?? '-' }}</div>
                    </div>
                  </div>

                  <div class="col-12 mt-4">
                    <h6 class="fw-bold mb-2 pb-2 border-bottom text-success"><i class='bx bx-money'></i> Bot Keuangan (Notifikasi Pembayaran)</h6>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Bot Token (Keuangan)</label>
                    <input type="password" name="telegram_finance_bot_token" class="form-control" placeholder="Isi token bot telegram untuk keuangan">
                    <div class="form-text">Token tersimpan: <strong>{{ empty($settings['telegram_finance_bot_token']) ? '-' : 'Terisi (Disembunyikan untuk keamanan)' }}</strong></div>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Chat ID Penerima Notifikasi (Admin Keuangan)</label>
                    <input type="text" name="telegram_finance_chat_id" class="form-control" value="{{ $settings['telegram_finance_chat_id'] ?? '' }}" placeholder="Contoh: 123456789,987654321">
                    <div class="form-text">Anda bisa memasukkan banyak Chat ID sekaligus, pisahkan dengan tanda koma (,). Notifikasi upload struk akan dikirim ke semua ID tersebut.</div>
                  </div>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-2">
                  <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Telegram</button>
                  <button type="button" class="ms-btn-secondary" id="btn-telegram-test"><i class='bx bx-check-shield'></i> Tes Token</button>
                  <button type="button" class="ms-btn-ghost" id="btn-telegram-webhook"><i class='bx bx-link'></i> Set Webhook</button>
                </div>
              </form>
            </div>            
            
            <!-- Tab Panduan MikroTik -->
            <div class="tab-pane fade" id="tab-mikrotik">
              <div class="text-center mb-4">
                <i class='bx bx-terminal' style="font-size: 3rem; color: var(--blue); margin-bottom: 0.5rem;"></i>
                <h4 style="font-weight: 700; color: var(--txt);">Script MikroTik Otomatis</h4>
                <p style="color: var(--txt-3); max-width: 500px; margin: 0 auto; font-size: 0.9rem;">
                  Tinggal *Copy-Paste* script di bawah ini ke <strong>New Terminal</strong> di Winbox Akang untuk mengatur semua keperluan integrasi Netking.
                </p>
              </div>

              <!-- Script 1: Isolir -->
              <div class="card mb-4" style="border-radius: 12px; border: 1px solid var(--border); background: var(--surface); overflow: hidden;">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: color-mix(in srgb, var(--red) 5%, var(--surface-2)); border-bottom: 1px solid var(--border); padding: 1rem 1.25rem;">
                  <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; background: var(--red); color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">1</div>
                    <div>
                      <h6 class="mb-0" style="font-weight: 700; color: var(--txt);">Script Isolir (Redirect Pelanggan Nunggak)</h6>
                      <div style="font-size: 0.75rem; color: var(--txt-3);">Memblokir internet pelanggan belum bayar & memunculkan halaman peringatan.</div>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyScript('script-isolir', this)">
                    <i class='bx bx-copy'></i> Copy Script
                  </button>
                </div>
                
                <div style="padding: 1rem 1.25rem; background: var(--surface-2); border-bottom: 1px solid var(--border);">
                  <label style="font-size: 0.8rem; font-weight: 600; color: var(--txt-3); display: block; margin-bottom: 0.4rem;">IP Server / VPS Netking Anda:</label>
                  <input type="text" id="vpsIpInput" class="form-control form-control-sm" value="{{ request()->getHost() }}" style="max-width: 300px; font-weight: 600;" oninput="updateScriptIps(this.value)">
                  <div style="font-size: 0.7rem; color: var(--txt-3); margin-top: 0.3rem;"><i class='bx bx-info-circle'></i> Sesuaikan IP ini dengan IP Publik VPS Akang. Script di bawah akan otomatis mengikuti IP yang diketik.</div>
                </div>

                <div class="card-body p-0">
                  <pre id="script-isolir" class="m-0 p-3" style="background: #1e1e1e; color: #569cd6; font-size: 0.85rem; border-radius: 0; white-space: pre-wrap;"><code><span style="color:#6a9955;"># 1. Pastikan Pelanggan Isolir tetap bisa akses Server Netking</span>
/ip firewall filter
add action=accept chain=forward comment="BYPASS SERVER NETKING" dst-address=<span style="color:#ce9178;" class="vps-ip-display">{{ request()->getHost() }}</span> src-address-list=isolir

<span style="color:#6a9955;"># 2. Redirect port 80 (HTTP) ke Landing Page Isolir Netking</span>
/ip firewall nat
add action=dst-nat chain=dstnat comment="REDIRECT ISOLIR KE NETKING" dst-port=80 protocol=tcp src-address-list=isolir to-addresses=<span style="color:#ce9178;" class="vps-ip-display">{{ request()->getHost() }}</span> to-ports=80

<span style="color:#6a9955;"># 3. Blokir sisa trafik (HTTPS, Game, Sosmed) untuk pelanggan Isolir</span>
/ip firewall filter
add action=drop chain=forward comment="DROP KONEKSI ISOLIR" src-address-list=isolir</code></pre>
                </div>
                <div class="card-footer" style="background: var(--surface-2); padding: 0.75rem 1.25rem; font-size: 0.8rem; color: var(--orange);">
                  <div class="mb-2"><i class='bx bx-info-circle'></i> <strong>Penting:</strong> Setelah di-paste, buka <strong>IP > Firewall > Filter Rules</strong> & <strong>NAT</strong>, lalu pastikan rule "REDIRECT" dan "DROP" ini berada di urutan <strong>paling atas</strong> agar tidak tertimpa rule yang lain.</div>
                  <div style="color: var(--red); font-weight: 600;"><i class='bx bx-error'></i> Catatan Isolir Webpage: Jika Akang menggunakan sistem "Redirect Webpage" seperti script di atas, fitur <span style="text-decoration: underline;">Disable PPPoE (Disable Secret)</span> di Netking <strong>TIDAK BOLEH DIGUNAKAN</strong>. Karena jika rahasia (secret) pelanggan didisable, router pelanggan tidak akan bisa terkoneksi ke MikroTik, sehingga mustahil menampilkan halaman isolir.</div>
                </div>
              </div>

              <!-- Script 2: WhatsApp Bot -->
              <div class="card mb-4" style="border-radius: 12px; border: 1px solid var(--border); background: var(--surface); overflow: hidden;">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: color-mix(in srgb, var(--green) 5%, var(--surface-2)); border-bottom: 1px solid var(--border); padding: 1rem 1.25rem;">
                  <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; background: var(--green); color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                    <div>
                      <h6 class="mb-0" style="font-weight: 700; color: var(--txt);">Script Auto-Kick PPPoE (Opsional)</h6>
                      <div style="font-size: 0.75rem; color: var(--txt-3);">Otomatis memutuskan sesi aktif saat profil diganti ke Isolir.</div>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyScript('script-kick', this)">
                    <i class='bx bx-copy'></i> Copy Script
                  </button>
                </div>
                <div class="card-body p-0">
                  <pre id="script-kick" class="m-0 p-3" style="background: #1e1e1e; color: #569cd6; font-size: 0.85rem; border-radius: 0;"><code><span style="color:#6a9955;"># Tambahkan script ini di tab "On Up" pada Profile PPPoE Isolir Akang</span>
:delay 2s;
/ppp active remove [find name=$user];</code></pre>
                </div>
                <div class="card-footer" style="background: var(--surface-2); padding: 0.75rem 1.25rem; font-size: 0.8rem; color: var(--txt-3);">
                  <i class='bx bx-bulb' style="color:var(--yellow);"></i> Catatan: Sistem Netking sebenarnya sudah otomatis mengeksekusi "Kick" via API saat isolir, jadi script ini hanya sebagai *backup* tambahan di router.
                </div>
              </div>
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
  function updateScriptIps(ip) {
    if(!ip) ip = 'IP_SERVER_VPS';
    document.querySelectorAll('.vps-ip-display').forEach(function(el) {
      el.textContent = ip;
    });
  }

  function copyScript(id, btn) {
    var text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text).then(function() {
      var originalHtml = btn.innerHTML;
      btn.innerHTML = "<i class='bx bx-check'></i> Tersalin!";
      btn.classList.replace('btn-outline-primary', 'btn-success');
      btn.style.color = 'white';
      setTimeout(function() {
        btn.innerHTML = originalHtml;
        btn.classList.replace('btn-success', 'btn-outline-primary');
        btn.style.color = '';
      }, 2000);
    });
  }

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
