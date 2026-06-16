@extends('layouts.app')
@section('title', 'Pengaturan')

@section('styles')
<style>
  /* ── Settings shell ─────────────────────────────────────────── */
  .sett-shell {
    display: flex;
    align-items: flex-start;
    gap: 20px;
  }

  /* ── Vertical nav sidebar ───────────────────────────────────── */
  .sett-nav {
    width: 210px;
    flex-shrink: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
    overflow: hidden;
    position: sticky;
    top: 80px;
  }
  .sett-nav-header {
    padding: 14px 16px 10px;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--txt-3);
    border-bottom: 1px solid var(--border);
  }
  .sett-nav-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 16px;
    font-size: .85rem;
    font-weight: 600;
    color: var(--txt-3);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all .16s ease;
    cursor: pointer;
  }
  .sett-nav-item i { font-size: 1rem; flex-shrink: 0; }
  .sett-nav-item:hover { color: var(--txt); background: color-mix(in srgb, var(--surface-2) 70%, transparent); }
  .sett-nav-item.is-active {
    color: var(--blue);
    background: color-mix(in srgb, var(--blue) 7%, var(--surface));
    border-left-color: var(--blue);
  }
  .sett-nav-item.is-active i { color: var(--blue); }

  /* ── Content panels ─────────────────────────────────────────── */
  .sett-content { flex: 1; min-width: 0; }
  .sett-pane { display: none; }
  .sett-pane.is-active { display: block; }

  .sett-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
    overflow: hidden;
    margin-bottom: 16px;
  }
  .sett-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--surface-2) 60%, var(--surface));
  }
  .sett-card-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
  }
  .sett-card-title { font-weight: 700; font-size: .95rem; color: var(--txt); }
  .sett-card-sub { font-size: .75rem; color: var(--txt-3); margin-top: 1px; }
  .sett-card-body { padding: 20px; }

  /* ── Form field group ───────────────────────────────────────── */
  .sett-field { margin-bottom: 14px; }
  .sett-field:last-child { margin-bottom: 0; }
  .sett-label {
    display: block;
    font-size: .8rem;
    font-weight: 600;
    color: var(--txt-2);
    margin-bottom: 5px;
  }
  .sett-hint {
    font-size: .73rem;
    color: var(--txt-3);
    margin-top: 4px;
  }

  /* ── Premium Toggle Switch ──────────────────────────────────── */
  .sett-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: color-mix(in srgb, var(--surface-2) 70%, var(--surface));
    border: 1px solid var(--border);
    margin-bottom: 10px;
    transition: border-color .2s;
  }
  .sett-toggle-row:last-child { margin-bottom: 0; }
  .sett-toggle-row:has(.nk-toggle:checked) {
    border-color: color-mix(in srgb, var(--green) 30%, var(--border));
    background: color-mix(in srgb, var(--green) 4%, var(--surface));
  }
  .sett-toggle-info .sett-toggle-title {
    font-weight: 700;
    font-size: .88rem;
    color: var(--txt);
  }
  .sett-toggle-info .sett-toggle-desc {
    font-size: .75rem;
    color: var(--txt-3);
    margin-top: 2px;
  }

  /* The actual toggle switch */
  .nk-toggle-wrap { position: relative; flex-shrink: 0; }
  .nk-toggle { position: absolute; opacity: 0; width: 0; height: 0; }
  .nk-toggle-track {
    display: block;
    width: 44px; height: 24px;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    cursor: pointer;
    position: relative;
    transition: background .25s ease, border-color .25s ease, box-shadow .25s ease;
  }
  .nk-toggle-track::after {
    content: '';
    position: absolute;
    left: 2px; top: 2px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
    transition: transform .22s cubic-bezier(.34,1.56,.64,1), background .22s;
  }
  .nk-toggle:checked + .nk-toggle-track {
    background: var(--green);
    border-color: var(--green);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--green) 20%, transparent);
  }
  .nk-toggle:checked + .nk-toggle-track::after {
    transform: translateX(20px);
  }

  /* ── Save button row ────────────────────────────────────────── */
  .sett-save-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--border);
    background: color-mix(in srgb, var(--surface-2) 50%, var(--surface));
  }

  /* ── Info sidebar card ──────────────────────────────────────── */
  .sett-info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 14px;
  }
  .sett-info-head {
    padding: 12px 16px;
    font-weight: 700;
    font-size: .85rem;
    color: var(--txt);
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--surface-2) 60%, var(--surface));
    display: flex; align-items: center; gap: 7px;
  }
  .sett-info-body { padding: 0; }
  .sett-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 16px;
    font-size: .82rem;
    border-bottom: 1px solid color-mix(in srgb, var(--border) 50%, transparent);
  }
  .sett-info-row:last-child { border-bottom: none; }
  .sett-info-label { color: var(--txt-3); font-weight: 500; }
  .sett-info-value { font-weight: 700; color: var(--txt); }

  /* ── Code block script ──────────────────────────────────────── */
  .sett-script-card {
    border-radius: 14px;
    border: 1px solid var(--border);
    background: var(--surface);
    overflow: hidden;
    margin-bottom: 16px;
  }
  .sett-script-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
  }
  .sett-script-num {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .8rem; color: #fff;
    flex-shrink: 0;
  }
  pre.sett-pre {
    margin: 0; padding: 14px 16px;
    background: #1a1a2e;
    color: #7ec8e3;
    font-size: .82rem;
    white-space: pre-wrap;
    border-radius: 0;
  }
  .sett-script-foot {
    padding: 10px 16px;
    font-size: .78rem;
    color: var(--txt-3);
    background: color-mix(in srgb, var(--surface-2) 60%, var(--surface));
    border-top: 1px solid var(--border);
  }

  /* ── Responsive ─────────────────────────────────────────────── */
  @media (max-width: 900px) {
    .sett-shell { flex-direction: column; }
    .sett-nav { width: 100%; position: static; display: flex; flex-wrap: nowrap; overflow-x: auto; border-radius: 14px; }
    .sett-nav-header { display: none; }
    .sett-nav-item { border-left: none; border-bottom: 3px solid transparent; flex: 0 0 auto; padding: 10px 14px; white-space: nowrap; }
    .sett-nav-item.is-active { border-left-color: transparent; border-bottom-color: var(--blue); }
  }
</style>
@endsection

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-cog'></i> Konfigurasi Sistem</div>
      <h1 class="ms-page-title">Pengaturan</h1>
    </div>
  </div>

  <div class="sett-shell">

    {{-- ══ Vertical Nav ══ --}}
    <nav class="sett-nav" id="sett-nav">
      <div class="sett-nav-header">Menu Pengaturan</div>
      <a class="sett-nav-item is-active" data-tab="general" onclick="settSwitch('general')">
        <i class='bx bx-building'></i> Umum
      </a>
      <a class="sett-nav-item" data-tab="billing" onclick="settSwitch('billing')">
        <i class='bx bx-credit-card'></i> Tagihan
      </a>
      <a class="sett-nav-item" data-tab="notif" onclick="settSwitch('notif')">
        <i class='bx bx-bell'></i> Notifikasi
      </a>
      <a class="sett-nav-item" data-tab="telegram" onclick="settSwitch('telegram')">
        <i class='bx bxl-telegram'></i> Telegram Bot
      </a>
      <a class="sett-nav-item" data-tab="mikrotik" onclick="settSwitch('mikrotik')">
        <i class='bx bx-server'></i> Panduan MikroTik
      </a>
    </nav>

    {{-- ══ Content Area ══ --}}
    <div class="sett-content">

      {{-- ─── Pane: Umum ─── --}}
      <div class="sett-pane is-active" id="pane-general">
        <div class="sett-card">
          <div class="sett-card-head">
            <div class="sett-card-icon" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);">
              <i class='bx bx-building'></i>
            </div>
            <div>
              <div class="sett-card-title">Identitas Perusahaan</div>
              <div class="sett-card-sub">Informasi dasar organisasi Anda</div>
            </div>
          </div>
          <form id="form-general" data-group="general" data-ajax="1">
            @csrf
            <input type="hidden" name="group" value="general">
            <div class="sett-card-body">
              <div class="row g-3">
                <div class="col-md-6 sett-field">
                  <label class="sett-label">Nama Perusahaan</label>
                  <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? 'NETKING' }}">
                </div>
                <div class="col-md-6 sett-field">
                  <label class="sett-label">Email Perusahaan</label>
                  <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Zona Waktu</label>
                  <select name="timezone" class="form-select">
                    <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB, UTC+7)</option>
                    <option value="Asia/Makassar" {{ ($settings['timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA, UTC+8)</option>
                    <option value="Asia/Jayapura" {{ ($settings['timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT, UTC+9)</option>
                  </select>
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Mata Uang</label>
                  <select name="currency" class="form-select">
                    <option value="IDR" {{ ($settings['currency'] ?? '') == 'IDR' ? 'selected' : '' }}>IDR — Rupiah Indonesia (Rp)</option>
                    <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD — Dolar Amerika ($)</option>
                  </select>
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Bahasa</label>
                  <select name="language" class="form-select">
                    <option value="id" {{ ($settings['language'] ?? '') == 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                    <option value="en" {{ ($settings['language'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="sett-save-row">
              <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Pengaturan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- ─── Pane: Tagihan ─── --}}
      <div class="sett-pane" id="pane-billing">
        <div class="sett-card">
          <div class="sett-card-head">
            <div class="sett-card-icon" style="background:color-mix(in srgb,var(--green) 12%,var(--surface));color:var(--green);">
              <i class='bx bx-credit-card'></i>
            </div>
            <div>
              <div class="sett-card-title">Konfigurasi Tagihan</div>
              <div class="sett-card-sub">Jadwal dan denda keterlambatan</div>
            </div>
          </div>
          <form id="form-billing" data-group="billing" data-ajax="1">
            @csrf
            <input type="hidden" name="group" value="billing">
            <div class="sett-card-body">
              <div class="row g-3">
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Hari Tagihan</label>
                  <select name="billing_day" class="form-select">
                    @for($i = 1; $i <= 28; $i++)
                    <option value="{{ $i }}" {{ ($settings['billing_day'] ?? '1') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                  </select>
                  <div class="sett-hint">Tanggal jatuh tempo setiap bulan.</div>
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Denda Keterlambatan (%)</label>
                  <input type="number" name="late_fee_percent" class="form-control" value="{{ $settings['late_fee_percent'] ?? '5' }}" min="0" max="100">
                  <div class="sett-hint">Persentase yang ditambahkan setelah masa tenggang.</div>
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Masa Tenggang (hari)</label>
                  <input type="number" name="grace_period_days" class="form-control" value="{{ $settings['grace_period_days'] ?? '7' }}" min="0" max="30">
                  <div class="sett-hint">Hari sebelum ditandai jatuh tempo.</div>
                </div>
              </div>

              <hr class="my-3" style="border-color:var(--border);">
              <div style="font-weight:700;font-size:.88rem;color:var(--txt);margin-bottom:12px;">Rekening Pembayaran</div>
              <div class="row g-3">
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Nama Bank 1</label>
                  <input type="text" name="payment_bank_1_name" class="form-control" value="{{ $settings['payment_bank_1_name'] ?? 'BRI' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">No. Rekening Bank 1</label>
                  <input type="text" name="payment_bank_1_number" class="form-control" value="{{ $settings['payment_bank_1_number'] ?? '159601000592564' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Pemilik Rekening Bank 1</label>
                  <input type="text" name="payment_bank_1_holder" class="form-control" value="{{ $settings['payment_bank_1_holder'] ?? 'Deni Firmansyah' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Nama Bank 2</label>
                  <input type="text" name="payment_bank_2_name" class="form-control" value="{{ $settings['payment_bank_2_name'] ?? 'BNI' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">No. Rekening Bank 2</label>
                  <input type="text" name="payment_bank_2_number" class="form-control" value="{{ $settings['payment_bank_2_number'] ?? '0320906963' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Pemilik Rekening Bank 2</label>
                  <input type="text" name="payment_bank_2_holder" class="form-control" value="{{ $settings['payment_bank_2_holder'] ?? 'Deni Firmansyah' }}">
                </div>
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Label QRIS</label>
                  <input type="text" name="payment_qris_label" class="form-control" value="{{ $settings['payment_qris_label'] ?? 'QRIS NETKING' }}">
                </div>
                <div class="col-md-8 sett-field">
                  <label class="sett-label">URL Gambar QRIS</label>
                  <input type="text" name="payment_qris_image_url" class="form-control" value="{{ $settings['payment_qris_image_url'] ?? url('/img/payments/QRIS-NETKING.jpg') }}">
                  <div class="sett-hint">Gunakan URL publik agar bisa tampil di aplikasi pelanggan.</div>
                </div>
                <div class="col-12 sett-field">
                  <label class="sett-label">Catatan QRIS</label>
                  <textarea name="payment_qris_notes" class="form-control" rows="2">{{ $settings['payment_qris_notes'] ?? 'Scan QRIS resmi NETKING, bayar sesuai nominal tagihan, lalu upload bukti pembayaran agar admin dapat memverifikasi pembayaran Anda.' }}</textarea>
                </div>
                <div class="col-12 sett-field">
                  <label class="sett-label">Catatan Pembayaran Manual</label>
                  <textarea name="manual_payment_notes" class="form-control" rows="3">{{ $settings['manual_payment_notes'] ?? 'Transfer sesuai nominal tagihan, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}</textarea>
                </div>
              </div>
            </div>
            <div class="sett-save-row">
              <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Tagihan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- ─── Pane: Notifikasi ─── --}}
      <div class="sett-pane" id="pane-notif">
        <div class="sett-card">
          <div class="sett-card-head">
            <div class="sett-card-icon" style="background:color-mix(in srgb,var(--orange,#f97316) 12%,var(--surface));color:var(--orange,#f97316);">
              <i class='bx bx-bell'></i>
            </div>
            <div>
              <div class="sett-card-title">Notifikasi Sistem</div>
              <div class="sett-card-sub">Kelola saluran pengiriman notifikasi</div>
            </div>
          </div>
          <form id="form-notifications" data-group="notifications" data-ajax="1">
            @csrf
            <input type="hidden" name="group" value="notifications">
            <div class="sett-card-body">
              <div class="sett-toggle-row">
                <div class="sett-toggle-info">
                  <div class="sett-toggle-title"><i class='bx bx-envelope' style="font-size:.9rem;margin-right:5px;color:var(--blue);"></i>Notifikasi Email</div>
                  <div class="sett-toggle-desc">Peringatan pembayaran dan notifikasi sistem via email</div>
                </div>
                <label class="nk-toggle-wrap">
                  <input class="nk-toggle" type="checkbox" name="notif_email" value="1" {{ ($settings['notif_email'] ?? '1') == '1' ? 'checked' : '' }}>
                  <span class="nk-toggle-track"></span>
                </label>
              </div>
              <div class="sett-toggle-row">
                <div class="sett-toggle-info">
                  <div class="sett-toggle-title"><i class='bx bx-mobile' style="font-size:.9rem;margin-right:5px;color:var(--blue);"></i>Notifikasi SMS</div>
                  <div class="sett-toggle-desc">OTP dan peringatan kritis via SMS</div>
                </div>
                <label class="nk-toggle-wrap">
                  <input class="nk-toggle" type="checkbox" name="notif_sms" value="1" {{ ($settings['notif_sms'] ?? '0') == '1' ? 'checked' : '' }}>
                  <span class="nk-toggle-track"></span>
                </label>
              </div>
            </div>
            <div class="sett-save-row">
              <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Notifikasi</button>
            </div>
          </form>
        </div>
      </div>

      {{-- ─── Pane: Telegram ─── --}}
      <div class="sett-pane" id="pane-telegram">
        <div class="sett-card">
          <div class="sett-card-head">
            <div class="sett-card-icon" style="background:color-mix(in srgb,#29b6f6 12%,var(--surface));color:#29b6f6;">
              <i class='bx bxl-telegram'></i>
            </div>
            <div>
              <div class="sett-card-title">Telegram Bot — MikroTik</div>
              <div class="sett-card-sub">Konfigurasi bot untuk isolir & manajemen router</div>
            </div>
          </div>
          <form id="form-telegram" data-group="telegram_bot" data-ajax="1">
            @csrf
            <input type="hidden" name="group" value="telegram_bot">
            <div class="sett-card-body">
              <div class="row g-3">
                <div class="col-md-4 sett-field">
                  <label class="sett-label">Mode Bot</label>
                  <select name="telegram_config_mode" class="form-select">
                    <option value="test" {{ ($settings['telegram_config_mode'] ?? 'test') === 'test' ? 'selected' : '' }}>TEST</option>
                    <option value="live" {{ ($settings['telegram_config_mode'] ?? '') === 'live' ? 'selected' : '' }}>LIVE</option>
                  </select>
                  <div class="sett-hint">Gunakan TEST untuk uji coba, LIVE untuk produksi.</div>
                </div>
                <div class="col-md-8 sett-field">
                  <label class="sett-label">Allowed Chat IDs</label>
                  <input type="text" name="telegram_config_allowed_ids" class="form-control" value="{{ $settings['telegram_config_allowed_ids'] ?? '' }}" placeholder="Contoh: 299890939,123456789">
                  <div class="sett-hint">Pisahkan dengan koma. Kosongkan untuk mengizinkan semua.</div>
                </div>
                <div class="col-md-12 sett-field">
                  <label class="sett-label">Bot Token (MikroTik)</label>
                  <input type="password" name="telegram_config_bot_token" class="form-control" placeholder="Isi token baru jika ingin mengganti">
                  <div class="sett-hint">Token tersimpan: <strong>{{ $telegram['masked_token'] ?? '-' }}</strong></div>
                </div>
                <div class="col-md-6 sett-field">
                  <label class="sett-label">Bot Secret Webhook</label>
                  <input type="text" name="telegram_config_bot_secret" class="form-control" value="{{ $settings['telegram_config_bot_secret'] ?? '' }}" placeholder="Secret path webhook">
                </div>
                <div class="col-md-6 sett-field">
                  <label class="sett-label">Admin Chat ID (Fallback)</label>
                  <input type="text" name="telegram_config_admin_chat_id" class="form-control" value="{{ $settings['telegram_config_admin_chat_id'] ?? '' }}" placeholder="Contoh: 299890939">
                </div>
                <div class="col-12 sett-field">
                  <div class="sett-toggle-row" style="margin-bottom:0;">
                    <div class="sett-toggle-info">
                      <div class="sett-toggle-title">Webhook URL Aktif</div>
                      <div class="sett-toggle-desc" id="telegram-webhook-preview" style="font-family:monospace;font-size:.78rem;">{{ $telegram['webhook_url'] ?? '-' }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <hr class="my-3" style="border-color:var(--border);">
              <div style="font-weight:700;font-size:.88rem;color:var(--green);margin-bottom:12px;"><i class='bx bx-money' style="margin-right:5px;"></i>Bot Keuangan (Notifikasi Pembayaran)</div>
              <div class="row g-3">
                <div class="col-md-12 sett-field">
                  <label class="sett-label">Bot Token (Keuangan)</label>
                  <input type="password" name="telegram_finance_bot_token" class="form-control" placeholder="Isi token bot telegram untuk keuangan">
                  <div class="sett-hint">Token tersimpan: <strong>{{ empty($settings['telegram_finance_bot_token']) ? '-' : 'Terisi (Disembunyikan untuk keamanan)' }}</strong></div>
                </div>
                <div class="col-md-12 sett-field">
                  <label class="sett-label">Chat ID Penerima Notifikasi</label>
                  <input type="text" name="telegram_finance_chat_id" class="form-control" value="{{ $settings['telegram_finance_chat_id'] ?? '' }}" placeholder="Contoh: 123456789,987654321">
                  <div class="sett-hint">Pisahkan dengan koma untuk banyak admin keuangan.</div>
                </div>
              </div>
            </div>
            <div class="sett-save-row">
              <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Telegram</button>
              <button type="button" class="ms-btn-secondary" id="btn-telegram-test"><i class='bx bx-check-shield'></i> Tes Token</button>
              <button type="button" class="ms-btn-ghost" id="btn-telegram-webhook"><i class='bx bx-link'></i> Set Webhook</button>
            </div>
          </form>
        </div>
      </div>

      {{-- ─── Pane: Panduan MikroTik ─── --}}
      <div class="sett-pane" id="pane-mikrotik">
        <div class="sett-card" style="margin-bottom:16px;">
          <div class="sett-card-head">
            <div class="sett-card-icon" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);">
              <i class='bx bx-terminal'></i>
            </div>
            <div>
              <div class="sett-card-title">Script MikroTik Otomatis</div>
              <div class="sett-card-sub">Copy-paste ke New Terminal di Winbox untuk integrasi penuh</div>
            </div>
            <div class="ms-auto" style="margin-left:auto;">
              <div class="sett-field" style="margin-bottom:0;">
                <label class="sett-label">IP Server / VPS:</label>
                <input type="text" id="vpsIpInput" class="form-control form-control-sm" value="{{ request()->getHost() }}" style="max-width:220px;font-weight:700;" oninput="updateScriptIps(this.value)">
              </div>
            </div>
          </div>
        </div>

        {{-- Script 1: Isolir --}}
        <div class="sett-script-card">
          <div class="sett-script-head" style="background:color-mix(in srgb,var(--red) 5%,var(--surface-2));">
            <div class="d-flex align-items-center gap-2">
              <div class="sett-script-num" style="background:var(--red);">1</div>
              <div>
                <div style="font-weight:700;font-size:.88rem;color:var(--txt);">Script Isolir (Redirect Pelanggan Nunggak)</div>
                <div style="font-size:.73rem;color:var(--txt-3);">Memblokir internet & menampilkan halaman peringatan.</div>
              </div>
            </div>
            <button type="button" class="ms-btn-secondary" onclick="copyScript('script-isolir', this)" style="flex-shrink:0;">
              <i class='bx bx-copy'></i> Copy
            </button>
          </div>
          <pre class="sett-pre" id="script-isolir"><code><span style="color:#6a9955;"># 1. Pelanggan Isolir tetap bisa akses Server Netking</span>
/ip firewall filter
add action=accept chain=forward comment="BYPASS SERVER NETKING" dst-address=<span class="vps-ip-display" style="color:#ce9178;">{{ request()->getHost() }}</span> src-address-list=isolir

<span style="color:#6a9955;"># 2. Redirect port 80 ke Landing Page Isolir Netking</span>
/ip firewall nat
add action=dst-nat chain=dstnat comment="REDIRECT ISOLIR KE NETKING" dst-port=80 protocol=tcp src-address-list=isolir to-addresses=<span class="vps-ip-display" style="color:#ce9178;">{{ request()->getHost() }}</span> to-ports=80

<span style="color:#6a9955;"># 3. Blokir sisa trafik untuk pelanggan Isolir</span>
/ip firewall filter
add action=drop chain=forward comment="DROP KONEKSI ISOLIR" src-address-list=isolir</code></pre>
          <div class="sett-script-foot" style="color:var(--orange,#f97316);">
            <i class='bx bx-info-circle'></i> <strong>Penting:</strong> Pastikan rule "REDIRECT" dan "DROP" ada di urutan <strong>paling atas</strong> Filter Rules & NAT.
            <div style="color:var(--red);font-weight:600;margin-top:4px;"><i class='bx bx-error'></i> Jika menggunakan sistem Redirect Webpage, fitur <u>Disable PPPoE Secret</u> <strong>TIDAK BOLEH DIGUNAKAN</strong>.</div>
          </div>
        </div>

        {{-- Script 1B --}}
        <div class="sett-script-card">
          <div class="sett-script-head" style="background:color-mix(in srgb,var(--orange,#f97316) 5%,var(--surface-2));">
            <div class="d-flex align-items-center gap-2">
              <div class="sett-script-num" style="background:var(--orange,#f97316);">1B</div>
              <div>
                <div style="font-weight:700;font-size:.88rem;color:var(--txt);">Script Isolir Alternatif (Block Total)</div>
                <div style="font-size:.73rem;color:var(--txt-3);">Hanya 1 baris. Memutus akses internet tanpa halaman peringatan.</div>
              </div>
            </div>
            <button type="button" class="ms-btn-secondary" onclick="copyScript('script-isolir-simple', this)" style="flex-shrink:0;">
              <i class='bx bx-copy'></i> Copy
            </button>
          </div>
          <pre class="sett-pre" id="script-isolir-simple"><code><span style="color:#6a9955;"># Block Total akses internet untuk pelanggan Isolir</span>
/ip firewall filter
add action=drop chain=forward comment="BLOCK TOTAL KONEKSI ISOLIR" src-address-list=isolir</code></pre>
          <div class="sett-script-foot">
            <i class='bx bx-bulb' style="color:var(--yellow,#eab308);"></i> <strong>Catatan:</strong> Pilih salah satu saja (1 atau 1B). Posisikan di urutan paling atas Filter Rules.
          </div>
        </div>

        {{-- Script 2: Auto-Kick --}}
        <div class="sett-script-card">
          <div class="sett-script-head" style="background:color-mix(in srgb,var(--green) 5%,var(--surface-2));">
            <div class="d-flex align-items-center gap-2">
              <div class="sett-script-num" style="background:var(--green);">2</div>
              <div>
                <div style="font-weight:700;font-size:.88rem;color:var(--txt);">Script Auto-Kick PPPoE (Opsional)</div>
                <div style="font-size:.73rem;color:var(--txt-3);">Otomatis memutuskan sesi aktif saat profil diganti ke Isolir.</div>
              </div>
            </div>
            <button type="button" class="ms-btn-secondary" onclick="copyScript('script-kick', this)" style="flex-shrink:0;">
              <i class='bx bx-copy'></i> Copy
            </button>
          </div>
          <pre class="sett-pre" id="script-kick"><code><span style="color:#6a9955;"># Tambahkan di tab "On Up" pada Profile PPPoE Isolir Akang</span>
:delay 2s;
/ppp active remove [find name=$user];</code></pre>
          <div class="sett-script-foot">
            <i class='bx bx-bulb' style="color:var(--yellow,#eab308);"></i> Catatan: Sistem Netking sudah otomatis mengeksekusi "Kick" via API, script ini hanya sebagai backup tambahan di router.
          </div>
        </div>
      </div>

    </div>{{-- /sett-content --}}

    {{-- ══ Sidebar Info ══ --}}
    <div style="width:220px;flex-shrink:0;position:sticky;top:80px;">
      <div class="sett-info-card">
        <div class="sett-info-head"><i class='bx bx-info-circle' style="color:var(--blue);"></i> Info Sistem</div>
        <div class="sett-info-body">
          <div class="sett-info-row"><span class="sett-info-label">Versi</span><span class="sett-info-value">1.0.0</span></div>
          <div class="sett-info-row"><span class="sett-info-label">Lingkungan</span><span class="sett-info-value"><span class="badge-status badge-active">{{ app()->environment() }}</span></span></div>
          <div class="sett-info-row"><span class="sett-info-label">PHP</span><span class="sett-info-value">{{ phpversion() }}</span></div>
          <div class="sett-info-row"><span class="sett-info-label">Laravel</span><span class="sett-info-value">{{ app()->version() }}</span></div>
          <div class="sett-info-row"><span class="sett-info-label">Database</span><span class="sett-info-value">{{ config('database.default') }}</span></div>
        </div>
      </div>
      <div class="sett-info-card">
        <div class="sett-info-head"><i class='bx bx-zap' style="color:var(--orange,#f97316);"></i> Aksi Cepat</div>
        <div class="sett-card-body d-grid gap-2">
          <button class="ms-btn-secondary" onclick="clearCache()"><i class='bx bx-refresh'></i> Bersihkan Cache</button>
          <button class="ms-btn-ghost" onclick="testEmail()"><i class='bx bx-mail-send'></i> Uji Email</button>
        </div>
      </div>
    </div>

  </div>{{-- /sett-shell --}}
</div>
@endsection

@section('scripts')
<script>
  // ── Vertical tab switcher ────────────────────────────────────────────────
  function settSwitch(tab) {
    document.querySelectorAll('.sett-pane').forEach(function(p) { p.classList.remove('is-active'); });
    document.querySelectorAll('.sett-nav-item').forEach(function(n) { n.classList.remove('is-active'); });
    var pane = document.getElementById('pane-' + tab);
    if (pane) pane.classList.add('is-active');
    var navItem = document.querySelector('[data-tab="' + tab + '"]');
    if (navItem) navItem.classList.add('is-active');
  }

  // ── Script tools ─────────────────────────────────────────────────────────
  function updateScriptIps(ip) {
    if (!ip) ip = 'IP_SERVER_VPS';
    document.querySelectorAll('.vps-ip-display').forEach(function(el) { el.textContent = ip; });
  }

  function copyScript(id, btn) {
    var text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text).then(function() {
      var orig = btn.innerHTML;
      btn.innerHTML = "<i class='bx bx-check'></i> Tersalin!";
      btn.style.background = 'var(--green)';
      btn.style.color = '#fff';
      setTimeout(function() {
        btn.innerHTML = orig;
        btn.style.background = '';
        btn.style.color = '';
      }, 2000);
    });
  }

  // ── AJAX form submit ─────────────────────────────────────────────────────
  $(function() {
    $('form[data-ajax]').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('button[type="submit"]');
      var origHtml = $btn.html();
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
        success: function(res) { window.nkShowToast && nkShowToast('success', res.message || 'Pengaturan tersimpan'); },
        error: function(xhr) { window.nkShowToast && nkShowToast('error', 'Gagal menyimpan: ' + (xhr.responseJSON?.message || 'Kesalahan server')); },
        complete: function() { $btn.prop('disabled', false).html(origHtml); }
      });
    });

    // Telegram test token
    $('#btn-telegram-test').on('click', function() {
      var $btn = $(this); var html = $btn.html();
      $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Testing...');
      $.ajax({
        url: '{{ route("admin.settings.telegram.test-token") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          var name = res?.bot?.name || '-';
          window.nkShowToast && nkShowToast('success', (res.message || 'Token valid') + ' (' + name + ')');
        },
        error: function(xhr) { window.nkShowToast && nkShowToast('error', xhr.responseJSON?.message || 'Gagal tes token'); },
        complete: function() { $btn.prop('disabled', false).html(html); }
      });
    });

    // Telegram set webhook
    $('#btn-telegram-webhook').on('click', function() {
      var $btn = $(this); var html = $btn.html();
      $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Mengatur...');
      $.ajax({
        url: '{{ route("admin.settings.telegram.set-webhook") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          $('#telegram-webhook-preview').text(res.webhook_url || '-');
          window.nkShowToast && nkShowToast('success', res.message || 'Webhook berhasil diset');
        },
        error: function(xhr) { window.nkShowToast && nkShowToast('error', xhr.responseJSON?.message || 'Gagal set webhook'); },
        complete: function() { $btn.prop('disabled', false).html(html); }
      });
    });
  });

  function clearCache() {
    $.post('{{ route("admin.settings.update") }}', { _token: '{{ csrf_token() }}', group: 'cache', action: 'clear' })
      .done(function() { window.nkShowToast && nkShowToast('success', 'Cache berhasil dibersihkan'); });
  }

  function testEmail() {
    window.nkShowToast && nkShowToast('info', 'Fitur uji email segera hadir');
  }
</script>
@endsection
