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
  .quick-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
  }
  .quick-toolbar-left {
    display: flex;
    flex: 1 1 320px;
    gap: .75rem;
    align-items: center;
  }
  .nk-search-wrap {
    display: flex;
    align-items: center;
    gap: .4rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: .3rem .6rem;
    min-height: 38px;
    width: 100%;
  }
  .nk-search-wrap i {
    color: var(--txt-3);
    font-size: .9rem;
  }
  .nk-search-input {
    border: none;
    outline: none;
    background: transparent;
    font-size: .78rem;
    color: var(--txt);
    width: 100%;
  }
  .nk-search-input::placeholder {
    color: var(--txt-3);
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
  .quick-payment-page .form-control,
  .quick-payment-page .form-select {
    width: 100%;
  }
  .quick-payment-page textarea.form-control {
    min-height: 80px;
    resize: vertical;
  }
  .radio-group {
    display: flex;
    flex-wrap: wrap;
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
    min-width: 180px;
  }
  .manual-toolbar .field-label {
    font-size: .76rem;
    color: var(--txt-3);
    font-weight: 500;
    margin-bottom: .35rem;
  }
  .quick-payment-page .form-select-sm + .select2-container--bootstrap-5 .select2-selection,
  .quick-payment-page .select2-container .select2-selection--single {
    min-height: 38px;
  }
  .quick-payment-page .select2-container {
    width: 100% !important;
  }
  .quick-payment-page .select2-container .select2-selection--single .select2-selection__rendered {
    white-space: normal;
    line-height: 1.35;
    padding-top: .38rem;
    padding-bottom: .38rem;
  }
  .quick-payment-page .select2-container--bootstrap-5 .select2-dropdown {
    min-width: 100% !important;
  }
  .manual-actions {
    display: flex;
    gap: .5rem;
    align-items: center;
    flex-wrap: wrap;
  }
  @media (max-width: 768px) {
    .quick-toolbar-left,
    .manual-toolbar,
    .manual-actions {
      width: 100%;
    }
    .form-grid {
      grid-template-columns: 1fr;
    }
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
  <form method="GET" action="{{ route('admin.payments.quick') }}" class="quick-toolbar">
    <div class="quick-toolbar-left">
      <div class="nk-search-wrap">
        <i class='bx bx-search'></i>
        <input type="text" name="q" class="nk-search-input" value="{{ $search ?? '' }}" placeholder="Cari kode pelanggan, nama, PPPoE user, atau no. HP..." autofocus>
      </div>
      <input type="hidden" name="manual_month" value="{{ $manualMonth ?? now()->month }}">
      <input type="hidden" name="manual_year" value="{{ $manualYear ?? now()->year }}">
    </div>
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
        <div style="margin-bottom:1rem;padding:.85rem 1rem;border:1px solid color-mix(in srgb, var(--blue) 20%, var(--border));border-radius:10px;background:color-mix(in srgb, var(--blue) 6%, var(--surface));font-size:.82rem;color:var(--txt-3);">
          Jika salah input tanggal transfer, ubah dari daftar pembayaran manual di bawah. Form ini tetap mencatat entri baru.
        </div>
        <form action="{{ route('admin.payments.manual.store', $customer) }}" method="POST">
          @csrf

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
                     value="{{ old('jumlah', $customer->package_price ?? $customer->package->price ?? 0) }}"
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
          <div class="field-label">Bulan</div>
          <select name="manual_month" class="form-select form-select-sm">
            @php $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; @endphp
            @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ (int) ($manualMonth ?? now()->month) === $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
            @endfor
          </select>
        </div>
        <div class="field">
          <div class="field-label">Tahun</div>
          <select name="manual_year" class="form-select form-select-sm">
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
      <form id="bulk-delete-manual-payments-global-form" action="{{ route('admin.payments.bulk-destroy') }}" method="POST" data-confirm="Hapus semua pembayaran manual yang dipilih?" class="d-none">
        @csrf
        @method('DELETE')
      </form>
      <form id="bulk-update-manual-payment-dates-form" action="{{ route('admin.payments.manual-dates.bulk') }}" method="POST" class="d-none">
        @csrf
        @method('PATCH')
      </form>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="d-flex align-items-center gap-2 mb-0" style="font-size:.8rem;color:var(--txt-3);">
          <input type="checkbox" id="select-all-global-manual-payments">
          Pilih semua
        </label>
        <div class="manual-actions">
          <button type="submit" form="bulk-update-manual-payment-dates-form" class="ms-btn-secondary">Simpan Semua Tanggal</button>
          <button type="submit" form="bulk-delete-manual-payments-global-form" class="ms-btn-ghost" style="color:#dc2626;border-color:color-mix(in srgb, #dc2626 24%, var(--border));">Hapus yang dipilih</button>
        </div>
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
              <th>Tgl Bayar</th>
              <th>Dibuat</th>
            </tr>
          </thead>
          <tbody>
            @foreach($manualPayments as $payment)
            <tr>
              <td>
                <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" form="bulk-delete-manual-payments-global-form" class="global-manual-payment-checkbox" style="width:16px;height:16px;">
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
              <td>
                <div class="d-flex align-items-center gap-2">
                  <input type="date" name="payment_dates[{{ $payment->id }}]" value="{{ optional($payment->approved_at)->format('Y-m-d') }}" form="bulk-update-manual-payment-dates-form" class="form-control form-control-sm" style="min-width:145px;">
                  <form action="{{ route('admin.payments.manual-date', $payment) }}" method="POST" class="m-0">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="tanggal_bayar" value="{{ optional($payment->approved_at)->format('Y-m-d') }}" class="single-date-mirror">
                    <button type="submit" class="ms-btn-secondary single-date-save" style="padding:.42rem .7rem;">Simpan</button>
                  </form>
                </div>
              </td>
              <td>{{ $payment->created_at?->format('d M Y H:i') ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
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
    if (selectAll) {
      selectAll.addEventListener('change', function() {
        document.querySelectorAll('.global-manual-payment-checkbox').forEach(function(cb) {
          cb.checked = selectAll.checked;
        });
      });
    }

    document.querySelectorAll('.single-date-save').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var wrap = btn.closest('td');
        if (!wrap) return;
        var dateInput = wrap.querySelector('input[type="date"][name^="payment_dates["]');
        var hiddenInput = wrap.querySelector('.single-date-mirror');
        if (dateInput && hiddenInput) {
          hiddenInput.value = dateInput.value;
        }
      });
    });
  })();
</script>
@endsection
