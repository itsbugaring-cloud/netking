@extends('layouts.app')
@section('title', 'Tandai Bayar Cepat')

@section('styles')
<style>
  .quick-payment-page .ms-panel {
    border: 1px solid var(--border) !important;
    border-radius: 12px !important;
    background: var(--surface) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.04) !important;
  }
  .quick-payment-page .ms-panel-head {
    border-bottom: 1px solid var(--border) !important;
    border-radius: 12px 12px 0 0 !important;
    background: transparent !important;
  }
  .search-bar {
    display: flex;
    gap: .75rem;
    margin-bottom: 1.5rem;
  }
  .search-bar input {
    flex: 1;
    padding: .5rem .75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--txt);
    font-size: .875rem;
  }
  .search-bar input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 15%, transparent);
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
  .not-found-msg {
    text-align: center;
    padding: 2rem;
    color: var(--txt-3);
    font-size: .9rem;
  }
  .not-found-msg i {
    font-size: 2rem;
    display: block;
    margin-bottom: .5rem;
    opacity: .5;
  }
  .manual-list-table th,
  .manual-list-table td {
    font-size: .8rem;
    vertical-align: middle;
  }
  .manual-toolbar {
    display: flex;
    gap: .75rem;
    align-items: end;
    flex-wrap: wrap;
    margin-bottom: 1rem;
  }
  .manual-toolbar .field {
    min-width: 140px;
  }
</style>
@endsection

@section('content')
<div class="ms-page quick-payment-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-zap'></i> Bayar Cepat</div>
      <h1 class="ms-page-title">Tandai Bayar Cepat</h1>
    </div>
  </div>

  {{-- Search bar --}}
  <form method="GET" action="{{ route('admin.payments.quick') }}" class="search-bar">
    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari kode pelanggan, nama, PPPoE user, atau no. HP..." autofocus>
    <input type="hidden" name="manual_month" value="{{ $manualMonth ?? now()->month }}">
    <input type="hidden" name="manual_year" value="{{ $manualYear ?? now()->year }}">
    <button type="submit" class="ms-btn">
      <i class='bx bx-search'></i> Cari
    </button>
  </form>

  @if($errors->any())
  <div class="alert alert-danger" style="border-radius:10px;border:1px solid #f5c6cb;background:#f8d7da;color:#721c24;margin-bottom:1rem;">
    <ul class="mb-0" style="padding-left:1rem;">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  @if($search && !$customer)
    <div class="ms-panel">
      <div class="ms-panel-body">
        <div class="not-found-msg">
          <i class='bx bx-user-x'></i>
          Customer tidak ditemukan untuk pencarian "<strong>{{ $search }}</strong>"
        </div>
      </div>
    </div>
  @elseif($customer)
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
            <a href="{{ route('admin.payments.quick') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn">
              <i class='bx bx-check'></i> Tandai Bayar
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <div class="ms-panel mt-4">
    <div class="ms-panel-head">
      <span class="ms-panel-title">
        <i class='bx bx-trash me-2' style="color:#dc2626;"></i>Bulk Hapus Pembayaran Manual
      </span>
    </div>
    <div class="ms-panel-body">
      <form method="GET" action="{{ route('admin.payments.quick') }}" class="manual-toolbar">
        <div class="field">
          <label class="form-label">Bulan</label>
          <select name="manual_month" class="form-select">
            @php $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; @endphp
            @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ (int) ($manualMonth ?? now()->month) === $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
            @endfor
          </select>
        </div>
        <div class="field">
          <label class="form-label">Tahun</label>
          <select name="manual_year" class="form-select">
            @for($y = 2024; $y <= 2027; $y++)
            <option value="{{ $y }}" {{ (int) ($manualYear ?? now()->year) === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
        </div>
        <div>
          <button type="submit" class="ms-btn-secondary">Tampilkan</button>
        </div>
      </form>

      @if(($manualPayments ?? collect())->isNotEmpty())
      <form id="bulk-delete-manual-payments-global-form" action="{{ route('admin.payments.bulk-destroy') }}" method="POST" onsubmit="return confirm('Hapus semua pembayaran manual yang dipilih?');">
        @csrf
        @method('DELETE')
        <div class="d-flex justify-content-between align-items-center mb-3">
          <label class="d-flex align-items-center gap-2 mb-0" style="font-size:.8rem;color:var(--txt-3);">
            <input type="checkbox" id="select-all-global-manual-payments">
            Pilih semua
          </label>
          <button type="submit" class="btn btn-sm btn-outline-danger">Hapus yang dipilih</button>
        </div>

        <div class="table-responsive">
          <table class="table table-flat manual-list-table mb-0">
            <thead>
              <tr>
                <th style="width:40px;">#</th>
                <th>Pelanggan</th>
                <th>Area</th>
                <th>Periode</th>
                <th>Jumlah</th>
                <th>Rekening</th>
                <th>Dibuat</th>
              </tr>
            </thead>
            <tbody>
              @foreach($manualPayments as $payment)
              <tr>
                <td>
                  <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" class="global-manual-payment-checkbox" style="width:16px;height:16px;">
                </td>
                <td>
                  <div style="font-weight:600;color:var(--txt);">{{ $payment->customer?->name ?? '—' }}</div>
                  <div style="color:var(--txt-3);font-size:.74rem;">
                    {{ $payment->customer?->customer_code ?? '—' }} · {{ $payment->customer?->pppoe_user ?? '—' }}
                  </div>
                </td>
                <td>{{ $payment->customer?->area?->name ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::createFromDate($payment->periode_tahun, $payment->periode_bulan, 1)->translatedFormat('M Y') }}</td>
                <td>Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</td>
                <td>{{ $payment->rekening_tujuan }}</td>
                <td>{{ $payment->created_at?->format('d M Y H:i') ?? '—' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </form>
      @else
      <div class="not-found-msg" style="padding:1rem 0 0;">
        <i class='bx bx-check-shield'></i>
        Tidak ada pembayaran manual untuk periode ini.
      </div>
      @endif
    </div>
  </div>
</div>

<script>
  (function() {
    var selectAll = document.getElementById('select-all-global-manual-payments');
    if (!selectAll) return;

    selectAll.addEventListener('change', function() {
      document.querySelectorAll('.global-manual-payment-checkbox').forEach(function(cb) {
        cb.checked = selectAll.checked;
      });
    });
  })();
</script>
@endsection
