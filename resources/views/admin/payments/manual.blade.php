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
  .customer-header-info .meta {
    font-size: .8rem; color: var(--txt-3); margin-top: 2px;
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
  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: .5rem .75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--txt);
    font-size: .875rem;
  }
  .form-group textarea {
    min-height: 80px;
    resize: vertical;
  }
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 15%, transparent);
  }
  .radio-group {
    display: flex;
    gap: 1rem;
    align-items: center;
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
      <h3>{{ $customer->name }}</h3>
      <div class="meta">
        {{ $customer->area->name ?? '—' }}
        · {{ $customer->package->name ?? '—' }}
        · Rp {{ number_format($customer->package_price ?? $customer->package->price ?? 0, 0, ',', '.') }}
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
      <form action="{{ route('admin.payments.manual.store', $customer) }}" method="POST">
        @csrf

        <div class="form-grid">
          {{-- Periode Bulan --}}
          <div class="form-group">
            <label for="periode_bulan">Bulan</label>
            <select name="periode_bulan" id="periode_bulan" required>
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
            <select name="periode_tahun" id="periode_tahun" required>
              @for($y = 2024; $y <= 2027; $y++)
              <option value="{{ $y }}" {{ (old('periode_tahun', now()->year) == $y) ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
          </div>

          {{-- Jumlah --}}
          <div class="form-group">
            <label for="jumlah">Jumlah (Rp)</label>
            <input type="number" name="jumlah" id="jumlah"
                   value="{{ old('jumlah', $customer->package_price ?? $customer->package->price ?? 0) }}"
                   min="0" step="1000" required>
          </div>

          {{-- Tanggal Bayar --}}
          <div class="form-group">
            <label for="tanggal_bayar">Tanggal Bayar</label>
            <input type="date" name="tanggal_bayar" id="tanggal_bayar"
                   value="{{ old('tanggal_bayar', date('Y-m-d')) }}"
                   required>
          </div>

          {{-- Rekening Tujuan --}}
          <div class="form-group">
            <label for="rekening_tujuan">Rekening</label>
            <select name="rekening_tujuan" id="rekening_tujuan" required>
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
            <textarea name="catatan" id="catatan" placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="{{ route('admin.customers.show', $customer) }}" class="ms-btn-ghost">Batal</a>
          <button type="submit" class="ms-btn">
            <i class='bx bx-check'></i> Simpan Pembayaran
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
