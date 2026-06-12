@extends('layouts.app')
@section('title', 'Laporan Pembayaran')

@section('content')
<div class="ms-page nk-list-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-money'></i> Keuangan</div>
      <h1 class="ms-page-title">Laporan Pembayaran Bulanan</h1>
    </div>
  </div>

  {{-- Filter Bar --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-body">
      <form method="GET" action="{{ route('admin.reports.payments') }}" class="d-flex flex-wrap gap-2 align-items-end">
        <div>
          <label class="form-label mb-1" style="font-size:.75rem;">Bulan</label>
          <select name="month" class="form-select form-select-sm" style="width:130px;">
            @php
              $bulanNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            @endphp
            @for($i = 1; $i <= 12; $i++)
              <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ $bulanNames[$i-1] }}</option>
            @endfor
          </select>
        </div>
        <div>
          <label class="form-label mb-1" style="font-size:.75rem;">Tahun</label>
          <select name="year" class="form-select form-select-sm" style="width:100px;">
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label mb-1" style="font-size:.75rem;">Area</label>
          <select name="area_id" class="form-select form-select-sm" style="width:160px;">
            <option value="">Semua Area</option>
            @foreach($areas as $area)
              <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <button type="submit" class="ms-btn-primary btn-sm"><i class='bx bx-filter-alt'></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Summary Cards --}}
  <div class="stat-grid">
    <div class="stat-card">
      <div>
        <div class="stat-label">Total Pembayaran</div>
        <div class="stat-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
      </div>
      <div class="stat-icon si-blue"><i class='bx bx-wallet'></i></div>
    </div>
    <div class="stat-card">
      <div>
        <div class="stat-label">Jumlah Transaksi</div>
        <div class="stat-value">{{ number_format($totalCount) }}</div>
      </div>
      <div class="stat-icon si-green"><i class='bx bx-receipt'></i></div>
    </div>
    @foreach($rekeningBreakdown as $rek)
    <div class="stat-card">
      <div>
        <div class="stat-label">{{ $rek->rekening_tujuan ?: 'Lainnya' }}</div>
        <div class="stat-value">Rp {{ number_format($rek->total, 0, ',', '.') }}</div>
        <div style="font-size:.7rem;color:#64748b;">{{ $rek->count }} transaksi</div>
      </div>
      <div class="stat-icon si-orange"><i class='bx bx-credit-card'></i></div>
    </div>
    @endforeach
  </div>

  {{-- Payment Detail Table --}}
  <div class="ms-panel mt-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title">
        <i class='bx bx-list-ul me-2' style="color:#2563eb;"></i>
        Detail Pembayaran — {{ $bulanNames[$month - 1] }} {{ $year }}
      </h5>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th style="width:40px;">No</th>
              <th>Nama Customer</th>
              <th>Area</th>
              <th>Periode</th>
              <th class="text-end">Jumlah (Rp)</th>
              <th>Rekening</th>
              <th>Tanggal Bayar</th>
              <th>Approved by</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $idx => $pmt)
            <tr>
              <td>{{ $payments->firstItem() + $idx }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:hsl({{ crc32($pmt->customer?->name ?? 'x') % 360 }},50%,58%);font-size:.95rem;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">{{ strtoupper(substr($pmt->customer?->name ?? '?', 0, 1)) }}</div>
                  <div style="font-weight:700;font-size:.875rem;color:var(--txt);">{{ $pmt->customer?->name ?? '-' }}</div>
                </div>
              </td>
              <td>{{ $pmt->customer?->area?->name ?? '-' }}</td>
              <td>{{ sprintf('%02d/%04d', $pmt->periode_bulan, $pmt->periode_tahun) }}</td>
              <td class="text-end">{{ number_format($pmt->jumlah, 0, ',', '.') }}</td>
              <td>{{ $pmt->rekening_tujuan ?: '-' }}</td>
              <td>{{ $pmt->approved_at?->format('d M Y') ?? '-' }}</td>
              <td>{{ $pmt->approvedBy?->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4" style="color:#94a3b8;">
                <i class='bx bx-info-circle'></i> Tidak ada data pembayaran untuk periode ini.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($payments->hasPages())
    <div class="ms-panel-body">
      {{ $payments->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
