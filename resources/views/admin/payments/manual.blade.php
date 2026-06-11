@extends('layouts.app')
@section('title', 'Pembayaran Manual - ' . $customer->name)

@section('styles')
<style>
  .manual-payment-page .ms-panel {
    border: 1px solid var(--border) !important;
    border-radius: 12px !important;
    background: var(--surface) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.04) !important;
  }
  .manual-payment-page .ms-panel-head {
    border-bottom: 1px solid var(--border) !important;
    border-radius: 12px 12px 0 0 !important;
    background: transparent !important;
  }
  .customer-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: color-mix(in srgb, var(--blue) 5%, var(--surface));
    border: 1px solid color-mix(in srgb, var(--blue) 15%, var(--border));
    border-radius: 12px;
    margin-bottom: 1.5rem;
  }
  .customer-header-avatar {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 700; color: #fff;
  }
  .customer-header-info h3 {
    margin: 0; font-size: 1rem; font-weight: 700; color: var(--txt);
  }
  .customer-name-row {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
  }
  .customer-header-info .meta {
    font-size: .8rem; color: var(--txt-3); margin-top: 2px;
  }
  .free-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .2rem .55rem;
    border-radius: 999px;
    background: color-mix(in srgb, #10b981 14%, var(--surface));
    border: 1px solid color-mix(in srgb, #10b981 30%, var(--border));
    color: #047857;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .free-warning {
    margin-bottom: 1rem;
    padding: .9rem 1rem;
    border: 1px solid color-mix(in srgb, #10b981 30%, var(--border));
    border-radius: 10px;
    background: color-mix(in srgb, #10b981 8%, var(--surface));
    color: #065f46;
    font-size: .82rem;
  }
  .free-warning strong {
    color: #047857;
  }
  .disabled-form-shell {
    opacity: .65;
    pointer-events: none;
  }
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  .form-grid .full-width {
    grid-column: 1 / -1;
  }
  .form-group label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: var(--txt-3);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: .35rem;
  }
  .manual-payment-page .form-control,
  .manual-payment-page .form-select {
    width: 100%;
  }
  .manual-payment-page textarea.form-control {
    min-height: 80px;
    resize: vertical;
  }
  .radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
  }
  .manual-payment-page .form-select-sm + .select2-container--bootstrap-5 .select2-selection,
  .manual-payment-page .select2-container .select2-selection--single {
    min-height: 38px;
  }
  .manual-payment-page .select2-container {
    width: 100% !important;
  }
  .manual-payment-page .select2-container .select2-selection--single .select2-selection__rendered {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  @media (max-width: 768px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
  }
  .radio-group label {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .875rem;
    font-weight: 500;
    color: var(--txt);
    text-transform: none;
    letter-spacing: 0;
    cursor: pointer;
  }
  .radio-group input[type="radio"] {
    width: auto;
    accent-color: var(--blue);
  }
</style>
@endsection

@section('content')
<div class="ms-page manual-payment-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-wallet'></i> Pembayaran Manual</div>
      <h1 class="ms-page-title">Catat Pembayaran Manual</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.customers.show', $customer) }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  @if($errors->any())
  <div class="alert alert-danger" style="border-radius:10px;border:1px solid #f5c6cb;background:#f8d7da;color:#721c24;margin-bottom:1rem;">
    <ul class="mb-0" style="padding-left:1rem;">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  {{-- Customer info header --}}
  <div class="customer-header">
    <div class="customer-header-avatar" style="background:hsl({{ crc32($customer->name) % 360 }},50%,58%);">
      {{ strtoupper(substr($customer->name, 0, 1)) }}
    </div>
    <div class="customer-header-info">
      <div class="customer-name-row">
        <h3>{{ $customer->name }}</h3>
        @if($customer->is_free)
        <span class="free-badge"><i class='bx bx-gift'></i> Gratis</span>
        @endif
      </div>
      <div class="meta">
        {{ $customer->area->name ?? '—' }}
        · {{ $customer->package->name ?? '—' }}
        · {{ $customer->is_free ? 'Tidak ditagih' : 'Rp ' . number_format($customer->package_price ?? $customer->package->price ?? 0, 0, ',', '.') }}
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <span class="ms-panel-title">
        <i class='bx bx-edit me-2' style="color:var(--blue);"></i>Form Pembayaran
      </span>
    </div>
    <div class="ms-panel-body">
      @if($customer->is_free)
      <div class="free-warning">
        <strong>Pelanggan ini gratis.</strong> Tidak perlu catat pembayaran manual untuk pelanggan ini.
      </div>
      @endif
      <div style="margin-bottom:1rem;padding:.85rem 1rem;border:1px solid color-mix(in srgb, var(--blue) 20%, var(--border));border-radius:10px;background:color-mix(in srgb, var(--blue) 6%, var(--surface));font-size:.82rem;color:var(--txt-3);">
        Jika salah input tanggal transfer, ubah dari daftar pembayaran manual. Form ini tetap mencatat entri baru.
      </div>
      <form action="{{ route('admin.payments.manual.store', $customer) }}" method="POST">
        @csrf
        <fieldset @disabled($customer->is_free) class="{{ $customer->is_free ? 'disabled-form-shell' : '' }}">
        <div class="form-grid">
          {{-- Periode Bulan --}}
          <div class="form-group">
            <label for="periode_bulan">Bulan</label>
            <select name="periode_bulan" id="periode_bulan" class="form-select form-select-sm" required>
              @php
                $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
              @endphp
              @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" {{ (old('periode_bulan', now()->month) == $m) ? 'selected' : '' }}>{{ $months[$m] }}</option>
              @endfor
            </select>
          </div>

          {{-- Periode Tahun --}}
          <div class="form-group">
            <label for="periode_tahun">Tahun</label>
            <select name="periode_tahun" id="periode_tahun" class="form-select form-select-sm" required>
              @for($y = 2024; $y <= 2027; $y++)
              <option value="{{ $y }}" {{ (old('periode_tahun', now()->year) == $y) ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
          </div>

          {{-- Jumlah --}}
          <div class="form-group">
            <label for="jumlah">Jumlah (Rp)</label>
            <input type="number" name="jumlah" id="jumlah" class="form-control"
                   value="{{ old('jumlah', $customer->is_free ? 0 : ($customer->package_price ?? $customer->package->price ?? 0)) }}"
                   min="0" step="1000" required>
          </div>

          {{-- Tanggal Bayar --}}
          <div class="form-group">
            <label for="tanggal_bayar">Tanggal Bayar</label>
            <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control"
                   value="{{ old('tanggal_bayar', date('Y-m-d')) }}"
                   required>
          </div>

          {{-- Rekening Tujuan --}}
          <div class="form-group">
            <label for="rekening_tujuan">Rekening</label>
            <select name="rekening_tujuan" id="rekening_tujuan" class="form-select form-select-sm" required>
              <option value="">Pilih rekening...</option>
              <option value="BRI" {{ old('rekening_tujuan') == 'BRI' ? 'selected' : '' }}>BRI</option>
              <option value="BNI" {{ old('rekening_tujuan') == 'BNI' ? 'selected' : '' }}>BNI</option>
              <option value="Mandiri" {{ old('rekening_tujuan') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
              <option value="BCA" {{ old('rekening_tujuan') == 'BCA' ? 'selected' : '' }}>BCA</option>
              <option value="QRIS" {{ old('rekening_tujuan') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
              <option value="Cash" {{ old('rekening_tujuan') == 'Cash' ? 'selected' : '' }}>Cash</option>
            </select>
          </div>

          {{-- Metode --}}
          <div class="form-group full-width">
            <label>Metode Pembayaran</label>
            <div class="radio-group">
              <label>
                <input type="radio" name="metode" value="transfer" {{ old('metode', 'transfer') == 'transfer' ? 'checked' : '' }}>
                Transfer
              </label>
              <label>
                <input type="radio" name="metode" value="cash" {{ old('metode') == 'cash' ? 'checked' : '' }}>
                Cash
              </label>
            </div>
          </div>

          {{-- Catatan --}}
          <div class="form-group full-width">
            <label for="catatan">Catatan (opsional)</label>
            <textarea name="catatan" id="catatan" class="form-control" placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
          </div>
        </div>
        </fieldset>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="{{ route('admin.customers.show', $customer) }}" class="ms-btn-ghost">Batal</a>
          <button type="submit" class="ms-btn" @disabled($customer->is_free)>
            <i class='bx {{ $customer->is_free ? 'bx-block' : 'bx-check' }}'></i> {{ $customer->is_free ? 'Pelanggan Gratis' : 'Simpan Pembayaran' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
