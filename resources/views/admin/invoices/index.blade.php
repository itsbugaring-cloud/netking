@extends('layouts.app')
@section('title', 'Invoices')

@section('styles')
<style>
  .invoice-date-wrap {
    position: relative;
  }

  .invoice-date-wrap i {
    position: absolute;
    left: .7rem !important;
    color: var(--blue) !important;
    font-size: .95rem !important;
    pointer-events: none;
    z-index: 2;
  }

  .invoice-date-wrap .nk-date-filter-input {
    width: 186px !important;
    min-width: 186px !important;
    max-width: 186px !important;
    height: 34px !important;
    padding: .38rem .65rem .38rem 1.9rem !important;
    border: 1px solid color-mix(in srgb, var(--blue) 20%, var(--border)) !important;
    border-radius: 9px !important;
    background: var(--surface) !important;
    color: var(--txt) !important;
    box-shadow: none !important;
    font-size: .79rem !important;
    line-height: 1.2 !important;
  }

  .invoice-date-wrap .nk-date-filter-input::placeholder {
    color: var(--txt-3) !important;
  }

  .invoice-date-wrap .nk-date-filter-input:focus {
    border-color: color-mix(in srgb, var(--blue) 58%, var(--border)) !important;
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--blue) 12%, transparent) !important;
  }

  @media (max-width: 768px) {
    .invoice-date-wrap .nk-date-filter-input {
      width: 165px !important;
      min-width: 165px !important;
      max-width: 165px !important;
    }
  }
</style>
@endsection

@section('content')
<div class="ms-page nk-list-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-receipt'></i> Operasi Penagihan</div>
      <h1 class="ms-page-title">Tagihan</h1>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-file me-2'></i>Daftar Tagihan</h5>
    </div>
    <div class="ms-table-shell">
      <form method="GET" action="{{ route('admin.invoices.index') }}" class="nk-table-controls">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <div class="nk-search-wrap nk-table-search-trigger">
            <i class='bx bx-search'></i>
            <input type="text" name="search" class="nk-search-input" value="{{ request('search') }}" placeholder="Cari no invoice, pelanggan, PPPoE, mitra...">
          </div>
          <div class="position-relative d-flex align-items-center invoice-date-wrap">
            <i class='bx bx-calendar'></i>
            <input type="date" name="from_date" class="nk-search-input nk-date-filter-input" value="{{ request('from_date') }}" title="Dari tanggal">
          </div>
          <div class="position-relative d-flex align-items-center invoice-date-wrap">
            <i class='bx bx-calendar'></i>
            <input type="date" name="to_date" class="nk-search-input nk-date-filter-input" value="{{ request('to_date') }}" title="Sampai tanggal">
          </div>
          <select name="status" class="nk-length-select" style="width:150px;">
            <option value="">Semua status</option>
            <option value="unpaid" @selected(request('status') === 'unpaid')>Belum Lunas</option>
            <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:.78rem;color:var(--txt-3);">Tampilkan</span>
          <select name="per_page" class="nk-length-select">
            <option value="10" @selected(($perPage ?? 20) == 10)>10</option>
            <option value="20" @selected(($perPage ?? 20) == 20)>20</option>
            <option value="50" @selected(($perPage ?? 20) == 50)>50</option>
            <option value="100" @selected(($perPage ?? 20) == 100)>100</option>
          </select>
          <button type="submit" class="ms-btn-secondary">Terapkan</button>
          <a href="{{ route('admin.invoices.index') }}" class="ms-btn-ghost">Reset</a>
        </div>
      </form>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="invoices-table" style="min-width:1180px;">
          <thead>
            <tr>
              <th style="min-width:160px;">Tagihan</th>
              <th style="min-width:220px;">Pelanggan</th>
              <th style="min-width:180px;">Area</th>
              <th style="min-width:150px;">Jumlah</th>
              <th style="min-width:120px;">Periode</th>
              <th style="min-width:120px;">Status</th>
              <th style="min-width:100px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $invoice)
            @php
              $periodLabel = ($invoice->period_month && $invoice->period_year)
                ? \Carbon\Carbon::createFromDate($invoice->period_year, $invoice->period_month, 1)->translatedFormat('M Y')
                : optional($invoice->due_date)->translatedFormat('M Y');
            @endphp
            <tr>
              <td><code>{{ $invoice->invoice_number }}</code></td>
              <td>
                <div style="font-weight:600;">{{ $invoice->customer->name ?? '-' }}</div>
                <div style="font-size:.74rem;color:var(--txt-3);">{{ $invoice->customer->pppoe_user ?? '' }}</div>
              </td>
              <td>{{ $invoice->customer->area->name ?? '-' }}</td>
              <td style="font-weight:600;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
              <td>{{ $periodLabel ?: '-' }}</td>
              <td>
                @if($invoice->status === 'paid')
                <span class="badge-status badge-paid">Lunas</span>
                @elseif($invoice->status === 'cancelled')
                <span class="badge-status badge-cancelled">Dibatalkan</span>
                @else
                <span class="badge-status badge-unpaid">Belum Lunas</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.invoices.show', $invoice) }}" class="nk-action-btn view" title="Lihat">
                    <i class='bx bx-show'></i>
                  </a>
                  @if($invoice->status !== 'paid')
                  <form action="{{ route('admin.invoices.markAsPaid', $invoice) }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="payment_method" value="manual_transfer">
                    <button type="submit" class="nk-action-btn pay" title="Tandai Lunas">
                      <i class='bx bx-check'></i>
                    </button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Tidak ada tagihan pada filter ini.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-3 px-2 pb-2 flex-wrap gap-2">
        <div class="text-muted small">
          Menampilkan {{ $invoices->firstItem() ?? 0 }}-{{ $invoices->lastItem() ?? 0 }} dari {{ $invoices->total() }} tagihan
        </div>
        {{ $invoices->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script></script>
@endsection
