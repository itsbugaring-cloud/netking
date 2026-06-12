@extends('layouts.app')
@section('title', 'Laporan Data Pelanggan')

@section('content')
<div class="ms-page nk-list-page">
  <div class="ms-page-head">
    <div>
      <h1 class="ms-page-title">Laporan Data Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.reports.export-billing', request()->query()) }}" class="btn btn-sm btn-success" style="border-radius:8px;font-weight:600;">
        <i class='bx bx-download me-1'></i> Export CSV
      </a>
      <a href="{{ route('admin.customers.export-excel', request()->query()) }}" class="btn btn-sm btn-primary" style="border-radius:8px;font-weight:600;">
        <i class='bx bx-spreadsheet me-1'></i> Ekspor Excel
      </a>
    </div>
  </div>

  {{-- Stats --}}
  <div class="stat-grid mb-4">
    <div class="stat-card">
      <div><div class="stat-label">Total Pelanggan</div><div class="stat-value">{{ number_format($stats['total']) }}</div></div>
      <div class="stat-icon si-blue"><i class='bx bx-group'></i></div>
    </div>
    <div class="stat-card">
      <div><div class="stat-label">Aktif</div><div class="stat-value">{{ number_format($stats['active']) }}</div></div>
      <div class="stat-icon si-green"><i class='bx bx-wifi'></i></div>
    </div>
    <div class="stat-card">
      <div><div class="stat-label">Diisolir</div><div class="stat-value">{{ number_format($stats['suspended']) }}</div></div>
      <div class="stat-icon" style="background:color-mix(in srgb,var(--red,#ef4444) 10%,var(--surface));color:var(--red,#ef4444);"><i class='bx bx-block'></i></div>
    </div>
    <div class="stat-card">
      <div><div class="stat-label">Pembayaran Pending</div><div class="stat-value">{{ number_format($stats['pending_payments']) }}</div></div>
      <div class="stat-icon" style="background:color-mix(in srgb,var(--orange,#f59e0b) 10%,var(--surface));color:var(--orange,#f59e0b);"><i class='bx bx-time-five'></i></div>
    </div>
  </div>

  {{-- Filter --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-body" style="padding:14px 18px;">
      <form method="GET" action="{{ route('admin.reports.billing') }}" class="d-flex flex-wrap gap-2 align-items-end">
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Area</label>
          <select name="area_id" class="form-select form-select-sm" data-hide-search style="min-width:150px;border-radius:8px;">
            <option value="">Semua Area</option>
            @foreach($areas as $area)
              <option value="{{ $area->id }}" {{ request('area_id')==$area->id?'selected':'' }}>{{ $area->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Status Pelanggan</label>
          <select name="status" class="form-select form-select-sm" data-hide-search style="min-width:150px;border-radius:8px;">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
            <option value="suspended" {{ request('status')=='suspended'?'selected':'' }}>Diisolir</option>
            <option value="provisioning" {{ request('status')=='provisioning'?'selected':'' }}>Dalam Proses</option>
            <option value="failed" {{ request('status')=='failed'?'selected':'' }}>Gagal</option>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Status Tagihan</label>
          <select name="payment_status" class="form-select form-select-sm" data-hide-search style="min-width:150px;border-radius:8px;">
            <option value="">Semua</option>
            <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>Ada Pending</option>
            <option value="approved" {{ request('payment_status')=='approved'?'selected':'' }}>Sudah Bayar</option>
          </select>
        </div>
        <div style="display:flex;gap:6px;align-items:flex-end;">
          <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;height:31px;">
            <i class='bx bx-filter-alt'></i> Filter
          </button>
          @if(request()->hasAny(['area_id','status','payment_status']))
          <a href="{{ route('admin.reports.billing') }}" class="btn btn-sm ms-btn-secondary" style="border-radius:8px;height:31px;">Reset</a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-group me-2'></i>Data Pelanggan ({{ $customers->total() }} total)</h5>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>ID Pelanggan</th>
              <th>Area</th>
              <th>Paket</th>
              <th>Status</th>
              <th>Total Bayar</th>
              <th>Tunggakan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($customers as $i => $c)
            <tr>
              <td style="color:var(--txt-3);font-size:.8rem;">{{ $customers->firstItem() + $i }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:hsl({{ crc32($c->name) % 360 }},50%,58%);font-size:.95rem;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">{{ strtoupper(substr($c->name,0,1)) }}</div>
                  <div>
                    <a href="{{ route('admin.customers.show', $c) }}" style="font-weight:700;font-size:.9rem;color:var(--txt);text-decoration:none;">{{ $c->name }}</a>
                    <div style="font-size:.72rem;color:var(--txt-3);">{{ $c->phone ?? '-' }}</div>
                  </div>
                </div>
              </td>
              <td><code style="font-size:.75rem;">{{ $c->customer_code ?? '—' }}</code></td>
              <td style="font-size:.8125rem;">{{ $c->area->name ?? '-' }}</td>
              <td style="font-size:.8125rem;">{{ $c->package->name ?? '-' }}</td>
              <td>
                @php
                  $sm = ['active'=>['Aktif','badge-active'],'suspended'=>['Diisolir','badge-inactive'],'provisioning'=>['Dalam Proses','badge-pending'],'failed'=>['Gagal','badge-danger'],'pending'=>['Pending','badge-pending']];
                  [$sl,$sc] = $sm[$c->status] ?? [ucfirst($c->status),'badge-inactive'];
                @endphp
                <span class="badge-status {{ $sc }}">{{ $sl }}</span>
              </td>
              <td style="font-weight:600;color:var(--green);font-size:.8125rem;">
                Rp {{ number_format($c->paid_total ?? 0, 0, ',', '.') }}
              </td>
              <td>
                @if(($c->unpaid_total ?? 0) > 0)
                  <span style="font-weight:700;color:var(--red);font-size:.8125rem;">
                    Rp {{ number_format($c->unpaid_total, 0, ',', '.') }}
                  </span>
                @else
                  <span style="color:var(--txt-3);font-size:.8rem;">—</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4" style="color:var(--txt-3);">Tidak ada data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">{{ $customers->links() }}</div>
</div>
@endsection
