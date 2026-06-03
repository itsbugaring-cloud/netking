@extends('admin.layout.app')
@section('title', 'Telegram Request')

@push('styles')
<style>
  .tg-req-page .ms-table-shell { padding: 0; }
  .tg-req-page .table th,
  .tg-req-page .table td { vertical-align: middle; }
  .tg-req-status {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .2rem .55rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    font-size: .73rem;
    font-weight: 600;
    line-height: 1;
    color: var(--txt);
    background: var(--surface);
    text-transform: lowercase;
  }
  .tg-req-status::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: #94a3b8;
  }
  .tg-req-status.st-online::before { background: #16a34a; }
  .tg-req-status.st-diterima::before { background: #2563eb; }
  .tg-req-status.st-menunggu_push_olt::before { background: #f59e0b; }
  .tg-req-status.st-menunggu_pppoe_up::before { background: #f97316; }
  .tg-req-status.st-rejected::before { background: #ef4444; }
  .tg-req-status.st-failed_mikrotik::before { background: #dc2626; }
</style>
@endpush

@section('content')
<div class="ms-page tg-req-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bxl-telegram'></i> Automasi Bot</div>
      <h1 class="ms-page-title">Telegram Request</h1>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <span class="ms-panel-title">
        <i class='bx bx-list-ul me-2' style="color:var(--blue);"></i>Antrian Request
        <span class="ms-2 ms-kpi-chip"><strong>{{ $total }}</strong> total</span>
      </span>
    </div>

    <div class="ms-panel-body">
      <form class="row g-2 mb-3" method="GET" action="{{ route('admin.telegram.requests.index') }}">
        <div class="col-lg-5">
          <div class="nk-search-wrap">
            <i class='bx bx-search'></i>
            <input type="text" name="q" class="nk-search-input" value="{{ $q }}" placeholder="">
          </div>
        </div>
        <div class="col-lg-3">
          <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            @foreach($statuses as $s)
              <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-2">
          <button class="ms-btn w-100" type="submit"><i class='bx bx-filter-alt'></i> Filter</button>
        </div>
        <div class="col-lg-2">
          <a class="ms-btn-secondary w-100 d-inline-flex justify-content-center" href="{{ route('admin.telegram.requests.index') }}">Reset</a>
        </div>
      </form>

      <div class="ms-table-shell">
        <div class="table-responsive">
          <table class="table table-flat mb-0">
            <thead>
              <tr>
                <th>Ref</th>
                <th>Status</th>
                <th>Area</th>
                <th>Pelanggan</th>
                <th>PPPoE</th>
                <th>Dibuat Oleh</th>
                <th>Waktu</th>
                <th style="width:100px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
            @forelse($items as $it)
              <tr>
                <td><code>{{ $it['ref'] }}</code></td>
                <td>
                  <span class="tg-req-status st-{{ $it['status'] }}">{{ $it['status'] }}</span>
                </td>
                <td>{{ $it['area_name'] ?: '-' }}</td>
                <td>{{ $it['customer_name'] ?: '-' }}</td>
                <td><code>{{ $it['pppoe_user'] ?: '-' }}</code></td>
                <td>
                  <div>{{ $it['from_name'] ?: '-' }}</div>
                  <small class="text-muted">{{ '@' . ($it['from_username'] ?: '-') }} • {{ $it['chat_id'] ?: '-' }}</small>
                </td>
                <td>{{ $it['submitted_at'] ?: '-' }}</td>
                <td>
                  <a href="{{ route('admin.telegram.requests.show', $it['ref']) }}" class="ms-btn-secondary" style="padding:.35rem .65rem;font-size:.77rem;">Detail</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted py-4">Belum ada request.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">Halaman {{ $page }} dari {{ $lastPage }}</div>
        <div class="d-flex gap-2">
          @if($page > 1)
            <a class="ms-btn-secondary" href="{{ route('admin.telegram.requests.index', ['q' => $q, 'status' => $status, 'page' => $page - 1]) }}">Prev</a>
          @endif
          @if($page < $lastPage)
            <a class="ms-btn-secondary" href="{{ route('admin.telegram.requests.index', ['q' => $q, 'status' => $status, 'page' => $page + 1]) }}">Next</a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
