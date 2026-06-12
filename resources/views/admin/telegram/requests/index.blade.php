@extends('layouts.app')
@section('title', 'Telegram Request')

@section('styles')
<style>
  .tg-req-page .ms-panel {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    border-radius: 0 !important;
  }
  .tg-req-page .ms-panel-head {
    border-bottom: 1px solid var(--border) !important;
    border-radius: 0 !important;
    background: transparent !important;
  }
  .tg-req-page .ms-panel-body {
    padding-left: 0;
    padding-right: 0;
    background: transparent !important;
  }
  .tg-req-page .ms-table-shell {
    padding: 0;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }
  .tg-req-page .tg-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: center;
    justify-content: space-between;
    padding-top: .25rem;
    padding-bottom: .75rem;
  }
  .tg-req-page .tg-toolbar-form {
    display: flex;
    flex: 1 1 680px;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: center;
  }
  .tg-req-page .tg-toolbar-actions {
    display: flex;
    gap: .5rem;
    align-items: center;
  }
  .tg-req-page .table th,
  .tg-req-page .table td { vertical-align: middle; }
  .tg-req-page .table th {
    padding: .55rem .75rem !important;
    font-size: .73rem;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .tg-req-page .table td {
    padding: .55rem .75rem !important;
    font-size: .8125rem;
  }
  .tg-req-page code {
    background: color-mix(in srgb, var(--blue) 8%, var(--surface));
    color: color-mix(in srgb, var(--blue) 80%, var(--txt));
    border: 1px solid color-mix(in srgb, var(--blue) 18%, var(--border));
    padding: 2px 7px;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 600;
  }
  .tg-req-page .tg-user {
    display: flex;
    gap: .7rem;
    align-items: center;
  }
  .tg-req-page .tg-user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .8rem;
    color: #fff;
    font-weight: 700;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
  }
  .tg-req-page .tg-user-meta {
    min-width: 0;
  }
  .tg-req-page .tg-user-name {
    font-weight: 600;
    color: var(--txt);
  }
  .tg-req-page .tg-user-sub {
    font-size: .73rem;
    color: var(--txt-3);
    overflow-wrap: anywhere;
  }
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
  .tg-req-page .tg-empty {
    text-align: center;
    padding: 3.5rem 1rem;
    color: var(--txt-3);
  }
  .tg-req-page .tg-empty i {
    display: block;
    font-size: 2.75rem;
    color: var(--blue);
    margin-bottom: .65rem;
  }
  @media (max-width: 768px) {
    .tg-req-page .tg-toolbar-form,
    .tg-req-page .tg-toolbar-actions {
      width: 100%;
    }
    .tg-req-page .nk-search-wrap,
    .tg-req-page .ms-btn,
    .tg-req-page .ms-btn-secondary {
      width: 100%;
    }
  }
</style>
@endsection

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
      <div class="tg-toolbar">
        <form class="tg-toolbar-form" method="GET" action="{{ route('admin.telegram.requests.index') }}">
          <select name="status" class="form-select form-select-sm" style="max-width:180px;">
            <option value="">Semua status</option>
            @foreach($statuses as $s)
              <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
            @endforeach
          </select>
          <div class="tg-toolbar-actions">
            <button class="ms-btn" type="submit"><i class='bx bx-filter-alt'></i> Terapkan</button>
            <a class="ms-btn-secondary d-inline-flex justify-content-center" href="{{ route('admin.telegram.requests.index') }}">Reset</a>
          </div>
        </form>
      </div>

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
                <td>
                  <div style="font-weight:600;color:var(--txt);">{{ $it['customer_name'] ?: '-' }}</div>
                </td>
                <td><code>{{ $it['pppoe_user'] ?: '-' }}</code></td>
                <td>
                  <div class="tg-user">
                    <div class="tg-user-avatar">{{ strtoupper(substr($it['from_name'] ?: 'T', 0, 1)) }}</div>
                    <div class="tg-user-meta">
                      <div class="tg-user-name">{{ $it['from_name'] ?: '-' }}</div>
                      <div class="tg-user-sub">{{ '@' . ($it['from_username'] ?: '-') }} • {{ $it['chat_id'] ?: '-' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $it['submitted_at'] ?: '-' }}</td>
                <td>
                  <a href="{{ route('admin.telegram.requests.show', $it['ref']) }}" class="ms-btn-secondary" style="padding:.35rem .65rem;font-size:.77rem;">Detail</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8">
                  <div class="tg-empty">
                    <i class='bx bxl-telegram'></i>
                    <div style="font-size:1rem;font-weight:600;color:var(--txt);margin-bottom:.35rem;">Belum ada request dari bot</div>
                    <div>Tidak ada draft pelanggan yang menunggu diproses saat ini.</div>
                  </div>
                </td>
              </tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <div class="text-muted small">Halaman {{ $page }} dari {{ $lastPage }}</div>
        <div class="d-flex gap-1">
          @if($page > 1)
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.telegram.requests.index', ['q' => $q, 'status' => $status, 'page' => $page - 1]) }}">Prev</a>
          @endif
          @if($page < $lastPage)
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.telegram.requests.index', ['q' => $q, 'status' => $status, 'page' => $page + 1]) }}">Next</a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


