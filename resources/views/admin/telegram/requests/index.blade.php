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
    gap: .4rem;
    padding: .35rem .75rem;
    border-radius: 6px;
    font-size: .75rem;
    font-weight: 600;
    line-height: 1;
    text-transform: capitalize;
  }
  .tg-req-status::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: currentColor;
    box-shadow: 0 0 6px currentColor;
  }
  .tg-req-status.st-online { background: color-mix(in srgb, #10b981 12%, var(--surface)); color: #047857; border: 1px solid color-mix(in srgb, #10b981 30%, var(--border)); }
  .tg-req-status.st-diterima { background: color-mix(in srgb, #3b82f6 12%, var(--surface)); color: #1d4ed8; border: 1px solid color-mix(in srgb, #3b82f6 30%, var(--border)); }
  .tg-req-status.st-menunggu_push_olt { background: color-mix(in srgb, #f59e0b 12%, var(--surface)); color: #b45309; border: 1px solid color-mix(in srgb, #f59e0b 30%, var(--border)); }
  .tg-req-status.st-menunggu_pppoe_up { background: color-mix(in srgb, #f97316 12%, var(--surface)); color: #c2410c; border: 1px solid color-mix(in srgb, #f97316 30%, var(--border)); }
  .tg-req-status.st-rejected { background: color-mix(in srgb, #ef4444 12%, var(--surface)); color: #b91c1c; border: 1px solid color-mix(in srgb, #ef4444 30%, var(--border)); }
  .tg-req-status.st-failed_mikrotik { background: color-mix(in srgb, #dc2626 12%, var(--surface)); color: #991b1b; border: 1px solid color-mix(in srgb, #dc2626 30%, var(--border)); }
  
  .tg-empty {
    text-align: center;
    padding: 3.5rem 1rem;
    color: var(--txt-3);
  }
  .tg-empty i {
    display: block;
    font-size: 3rem;
    color: var(--blue);
    margin-bottom: .85rem;
    opacity: 0.5;
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

    <div class="ms-panel-body" style="padding-top: 1rem;">
      <div class="tg-toolbar d-flex justify-content-between align-items-center mb-4" style="background: var(--surface-2); padding: 1rem 1.25rem; border-radius: 12px; border: 1px solid var(--border);">
        <form class="tg-toolbar-form m-0 d-flex gap-3 align-items-center w-100" method="GET" action="{{ route('admin.telegram.requests.index') }}">
          <div class="d-flex align-items-center gap-2 flex-grow-1">
            <i class='bx bx-filter-alt text-muted' style="font-size: 1.2rem;"></i>
            <select name="status" class="form-select form-select-sm border-0 bg-transparent shadow-none" style="max-width: 200px; font-weight: 600; color: var(--txt); font-size: .85rem; cursor: pointer;" onchange="this.form.submit()">
              <option value="">-- Tampilkan Semua Status --</option>
              @foreach($statuses as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
              @endforeach
            </select>
          </div>
          @if($status || $q)
          <div class="tg-toolbar-actions">
            <a class="ms-btn-ghost" href="{{ route('admin.telegram.requests.index') }}" style="color: var(--red);">
                <i class='bx bx-x'></i> Clear Filter
            </a>
          </div>
          @endif
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
              <tr style="transition: background 0.2s;" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
                <td>
                    @php
                        $shortRef = explode('-', $it['ref']);
                        $shortRef = count($shortRef) > 1 ? $shortRef[1] : $it['ref'];
                    @endphp
                    <code style="font-size: .7rem; padding: 4px 8px; border-radius: 4px; background: color-mix(in srgb, var(--blue) 5%, var(--surface)); color: var(--blue); border: 1px solid color-mix(in srgb, var(--blue) 15%, var(--border));">
                        #{{ substr($shortRef, 0, 8) }}
                    </code>
                </td>
                <td>
                  <span class="tg-req-status st-{{ $it['status'] }}">{{ str_replace('_', ' ', $it['status']) }}</span>
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
        <div class="text-muted small">Menampilkan {{ $items->firstItem() ?? 0 }} sampai {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data</div>
        <div>
          {{ $items->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


