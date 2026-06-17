@extends('layouts.app')
@section('title', 'Pelanggan')

@section('styles')
<style>
  /* ── Modal z-index fix ────────────────────────────────────────────────── */
  #billingStartImportModal { z-index: 1060 !important; }
  #billingStartImportModal .modal-dialog { pointer-events: auto; }
  .modal-backdrop { z-index: 1055 !important; }

  /* ── Page wrapper ─────────────────────────────────────────────────────── */
  .customers-index-page .ms-panel {
    border: none !important; box-shadow: none !important;
    background: transparent !important; border-radius: 0 !important;
  }
  .customers-index-page .ms-panel-head {
    border-bottom: 1px solid var(--border) !important;
    border-radius: 0 !important; background: transparent !important;
  }
  .customers-index-page .ms-panel-body { background: transparent !important; }
  .customers-index-page .ms-table-shell {
    padding: 0 !important; border: 0 !important;
    background: transparent !important; box-shadow: none !important;
  }
  .customers-index-page .ms-table-shell .table-responsive {
    border: 0 !important; background: transparent !important;
    min-height: 220px;
  }
  @media (min-width: 768px) {
    .customers-index-page .ms-table-shell .table-responsive { overflow: visible !important; }
  }
  .customers-index-page .ms-table-shell .dataTables_wrapper { padding: 0 !important; }

  /* ── Premium card wrapper ────────────────────────────────────────────── */
  .cust-card-shell {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 8px 32px rgba(37,99,235,.05);
  }

  /* ── Toolbar ─────────────────────────────────────────────────────────── */
  .cust-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--surface-2) 60%, var(--surface));
  }

  /* ── Segment filter tabs ─────────────────────────────────────────────── */
  .cust-seg {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 3px;
    border-radius: 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    flex-wrap: wrap;
  }
  .cust-seg-tab {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 9px;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    color: var(--txt-3);
    border: 1px solid transparent;
    transition: all .18s ease;
    white-space: nowrap;
    line-height: 1;
  }
  .cust-seg-tab:hover { color: var(--txt); background: color-mix(in srgb, var(--surface) 70%, transparent); }
  .cust-seg-tab.is-active {
    color: var(--blue);
    background: var(--surface);
    border-color: var(--border);
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }
  .cust-seg-tab .seg-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
  }
  .cust-seg-tab.is-active.seg-all   .seg-dot { background: var(--blue); }
  .cust-seg-tab.is-active.seg-active .seg-dot { background: var(--green); }
  .cust-seg-tab.is-active.seg-suspended .seg-dot { background: var(--red, #ef4444); }
  .cust-seg-tab.is-active.seg-other .seg-dot { background: var(--orange, #f97316); }

  /* ── Search input ────────────────────────────────────────────────────── */
  .cust-search-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 5px 12px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: var(--surface);
    transition: border-color .2s, box-shadow .2s;
  }
  .cust-search-wrap:focus-within {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 14%, transparent);
  }
  .cust-search-wrap i { color: var(--txt-3); font-size: 1rem; flex-shrink: 0; transition: color .2s; }
  .cust-search-wrap:focus-within i { color: var(--blue); }
  .cust-search-wrap input {
    border: none; background: transparent; color: var(--txt);
    font-size: .8125rem; outline: none; font-family: inherit;
    width: 185px; transition: width .25s ease;
  }
  .cust-search-wrap:focus-within input { width: 230px; }

  /* ── Table ───────────────────────────────────────────────────────────── */
  #customers-table thead th {
    padding: .55rem .85rem !important;
    font-size: .7rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .06em !important;
    color: var(--txt-3) !important;
    background: color-mix(in srgb, var(--surface-2) 60%, var(--surface)) !important;
    border-bottom: 1px solid var(--border) !important;
    white-space: nowrap;
  }
  #customers-table tbody td {
    padding: .7rem .85rem !important;
    font-size: .8125rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid color-mix(in srgb, var(--border) 60%, transparent) !important;
    background: transparent !important;
    transition: background .15s ease !important;
  }
  #customers-table tbody tr { position: relative; z-index: 1; }
  #customers-table tbody tr:focus-within,
  #customers-table tbody tr:hover { z-index: 10; }
  #customers-table tbody tr:hover td {
    background: color-mix(in srgb, var(--blue) 4%, var(--surface)) !important;
  }
  #customers-table tbody tr:last-child td { border-bottom: none !important; }

  /* ── Avatar ──────────────────────────────────────────────────────────── */
  .cust-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800; color: #fff;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(0,0,0,.15);
    letter-spacing: -.5px;
  }
  .cust-name-main {
    font-weight: 700;
    font-size: .9rem;
    color: var(--txt);
    line-height: 1.2;
  }
  .cust-name-sub {
    font-size: .72rem;
    color: var(--txt-3);
    margin-top: 1px;
  }

  /* ── PPPoE / ID code badge ───────────────────────────────────────────── */
  .cust-code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: color-mix(in srgb, var(--blue) 8%, var(--surface-2));
    color: color-mix(in srgb, var(--blue) 75%, var(--txt));
    border: 1px solid color-mix(in srgb, var(--blue) 18%, var(--border));
    padding: 2px 8px;
    border-radius: 7px;
    font-size: .75rem;
    font-weight: 700;
    font-family: monospace;
    letter-spacing: -.2px;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* ── Status badges ───────────────────────────────────────────────────── */
  .cust-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: .73rem;
    font-weight: 700;
    letter-spacing: .01em;
    white-space: nowrap;
  }
  .cust-badge-active {
    background: color-mix(in srgb, var(--green) 12%, var(--surface));
    color: var(--green);
    border: 1px solid color-mix(in srgb, var(--green) 25%, var(--border));
  }
  .cust-badge-suspended {
    background: color-mix(in srgb, var(--red, #ef4444) 10%, var(--surface));
    color: var(--red, #ef4444);
    border: 1px solid color-mix(in srgb, var(--red, #ef4444) 22%, var(--border));
  }
  .cust-badge-pending {
    background: color-mix(in srgb, var(--orange, #f97316) 10%, var(--surface));
    color: var(--orange, #f97316);
    border: 1px solid color-mix(in srgb, var(--orange, #f97316) 22%, var(--border));
  }
  .cust-badge-failed {
    background: color-mix(in srgb, var(--red, #ef4444) 10%, var(--surface));
    color: var(--red, #ef4444);
    border: 1px dashed color-mix(in srgb, var(--red, #ef4444) 30%, var(--border));
  }
  .cust-badge-free {
    background: color-mix(in srgb, var(--blue) 10%, var(--surface));
    color: var(--blue);
    border: 1px solid color-mix(in srgb, var(--blue) 22%, var(--border));
  }
  /* pulsing dot for active */
  .cust-pulse {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 0 0 color-mix(in srgb, var(--green) 50%, transparent);
    animation: cust-pulse-ring 1.8s infinite;
    flex-shrink: 0;
  }
  @keyframes cust-pulse-ring {
    0%   { box-shadow: 0 0 0 0 color-mix(in srgb, var(--green) 50%, transparent); }
    70%  { box-shadow: 0 0 0 6px rgba(0,200,0,0); }
    100% { box-shadow: 0 0 0 0 rgba(0,200,0,0); }
  }

  /* ── Package cell ────────────────────────────────────────────────────── */
  .cust-pkg-name { font-weight: 600; font-size: .85rem; color: var(--txt); }
  .cust-pkg-meta { font-size: .72rem; color: var(--txt-3); margin-top: 1px; }

  /* ── Action dropdown button ──────────────────────────────────────────── */
  .cust-opsi-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 30px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 600;
    background: var(--surface-2);
    border: 1px solid var(--border);
    color: var(--txt-2);
    cursor: pointer;
    transition: all .15s ease;
  }
  .cust-opsi-btn:hover {
    background: var(--surface);
    border-color: color-mix(in srgb, var(--blue) 30%, var(--border));
    color: var(--txt);
    box-shadow: 0 2px 8px rgba(37,99,235,.1);
  }
  .cust-opsi-btn i { font-size: .82rem; }

  /* dropdown item icons */
  .dropdown-menu .dropdown-item {
    display: flex; align-items: center; gap: 8px;
    font-size: .83rem; font-weight: 500;
    padding: 7px 14px;
    border-radius: 7px;
    margin: 1px 4px;
    transition: background .13s;
  }
  .dropdown-menu .dropdown-item i { font-size: .95rem; }
  .dropdown-menu { border-radius: 14px !important; border: 1px solid var(--border) !important; padding: 5px !important; }

  /* ── Bulk bar ────────────────────────────────────────────────────────── */
  #bulk-bar.ms-panel {
    border: 1px solid var(--border) !important;
    background: var(--surface) !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 12px rgba(37,99,235,.08) !important;
  }

  /* ── Pagination area ─────────────────────────────────────────────────── */
  .cust-pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px 18px;
    border-top: 1px solid var(--border);
    background: color-mix(in srgb, var(--surface-2) 60%, var(--surface));
  }
  .cust-pagination-bar .text-muted { font-size: .78rem; color: var(--txt-3); }

  /* ── Responsive ──────────────────────────────────────────────────────── */
  @media (max-width: 700px) {
    .cust-seg { width: 100%; overflow-x: auto; flex-wrap: nowrap; }
    .cust-seg-tab { flex: 0 0 auto; }
    .cust-search-wrap input { width: 140px; }
    .cust-search-wrap:focus-within input { width: 160px; }
  }
</style>
@endsection

@section('content')
@php
  $isFinance = (auth()->user()->role ?? null) === 'finance';
  $statusTabs = [
    '' => 'Semua',
    'active' => 'Aktif',
    'suspended' => 'Diisolir',
    'provisioning' => 'Proses',
    'failed' => 'Gagal',
  ];
  $visibleCustomers = collect($customers->items());
  $visibleActiveCount = $visibleCustomers->where('status', 'active')->count();
  $visibleSuspendedCount = $visibleCustomers->where('status', 'suspended')->count();
  $areaFilterName = optional($areas->firstWhere('id', (int) request('area_id')))->name;
@endphp
<div class="ms-page customers-index-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-group'></i> Daftar Pelanggan</div>
      <h1 class="ms-page-title">Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.customers.export-excel', request()->query()) }}" class="ms-btn-secondary">
        <i class='bx bx-spreadsheet'></i> Ekspor Excel
      </a>
      <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="ms-btn-secondary">
        <i class='bx bx-download'></i> Ekspor CSV
      </a>
      @if((auth()->user()->role ?? null) === 'admin')
      <button type="button" class="ms-btn-secondary" data-bs-toggle="modal" data-bs-target="#billingStartImportModal">
        <i class='bx bx-calendar-edit'></i> Update Tanggal Tagihan
      </button>
      @endif
      @unless($isFinance)
      <a href="{{ route('admin.customers.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Tambah Pelanggan
      </a>
      @endunless
    </div>
  </div>

  @if((auth()->user()->role ?? null) === 'admin' && session('import_billing_errors'))
  <div class="alert alert-warning mt-3" style="border:1px solid #f4d38f;background:#fff7e6;color:#8a5a00;border-radius:12px;">
    <div style="font-weight:700;margin-bottom:.35rem;">Sebagian baris update tanggal tagihan perlu dicek</div>
    <ul style="margin:0;padding-left:1.1rem;font-size:.85rem;">
      @foreach(session('import_billing_errors') as $line)
      <li>{{ $line }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  {{-- Bulk Action Bar --}}
  @unless($isFinance)
  <div id="bulk-bar" style="display:none; margin-bottom:.75rem;">
    <div class="ms-panel" style="border:1px solid var(--border)!important;background:var(--surface)!important;border-radius:8px!important;box-shadow:none!important;">
      <div class="ms-panel-body d-flex align-items-center justify-content-between gap-3 py-3">
        <span class="ms-chip" id="bulk-count">0 dipilih</span>
        <div class="d-flex gap-2">
          <button type="button" class="ms-btn-ghost" onclick="bulkDelete()">
            <i class='bx bx-trash'></i> Hapus Terpilih
          </button>
          <button type="button" class="ms-btn-secondary" onclick="bulkClear()">Batal</button>
        </div>
      </div>
    </div>
  </div>
  @endunless


  {{-- ═══════════════════════════════ CARD SHELL ═══════════════════════════════ --}}
  <div class="cust-card-shell">

    {{-- ── Toolbar: Filter + Search ── --}}
    <div class="cust-toolbar">

      {{-- Left: Segment Filter Tabs --}}
      <div class="cust-seg">
        @foreach($statusTabs as $value => $label)
          @php
            $isActiveTab = request('status', '') === $value;
            $tabQuery = array_merge(request()->query(), ['status' => $value === '' ? null : $value]);
            if ($value === '') unset($tabQuery['status']);
            $segClass = match($value) {
              ''           => 'seg-all',
              'active'     => 'seg-active',
              'suspended'  => 'seg-suspended',
              default      => 'seg-other',
            };
          @endphp
          <a href="{{ route('admin.customers.index', $tabQuery) }}"
             class="cust-seg-tab {{ $segClass }} {{ $isActiveTab ? 'is-active' : '' }}"
             aria-selected="{{ $isActiveTab ? 'true' : 'false' }}">
            @if($isActiveTab)<span class="seg-dot"></span>@endif
            {{ $label }}
          </a>
        @endforeach
      </div>

      {{-- Right: Area filter + Search + Per Page --}}
      <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- Area filter --}}
        @if($areas->isNotEmpty())
        <form method="GET" action="{{ route('admin.customers.index') }}" id="area-filter-form" class="d-flex align-items-center gap-2">
          <input type="hidden" name="search" value="{{ request('search') }}">
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="hidden" name="per_page" value="{{ $perPage ?? 50 }}">
          <select name="area_id" class="form-select form-select-sm" style="height:34px;font-size:.8rem;font-weight:600;border-radius:9px;" data-hide-search onchange="this.form.submit()">
            <option value="">Semua Area</option>
            @foreach($areas as $area)
            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
            @endforeach
          </select>
          @if(request('area_id'))
          <a href="{{ route('admin.customers.index') }}" style="font-size:.78rem;color:var(--txt-3);text-decoration:none;" title="Hapus filter area"><i class='bx bx-x-circle'></i></a>
          @endif
        </form>
        @endif

        {{-- Search + Submit --}}
        <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex align-items-center gap-2">
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="hidden" name="area_id" value="{{ request('area_id') }}">
          <div class="cust-search-wrap">
            <i class='bx bx-search'></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, PPPoE, HP..." autocomplete="off">
          </div>
          <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="height:34px;width:auto;min-width:75px;font-size:.8rem;font-weight:600;border-radius:9px;cursor:pointer;">
            <option value="25" @selected(($perPage ?? 50) == 25)>25</option>
            <option value="50" @selected(($perPage ?? 50) == 50)>50</option>
            <option value="100" @selected(($perPage ?? 50) == 100)>100</option>
            <option value="200" @selected(($perPage ?? 50) == 200)>200</option>
          </select>
          <button type="submit" style="height:34px;padding:0 14px;background:var(--blue);color:#fff;border:none;border-radius:9px;cursor:pointer;font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:5px;">
            <i class='bx bx-search'></i>
          </button>
        </form>
      </div>
    </div>

    {{-- ── Table ── --}}
    <div class="ms-table-shell">
      <div class="table-responsive" style="min-height:300px;">
        <table class="table table-flat mb-0" id="customers-table">
          <thead>
            <tr>
              <th style="width:38px;padding-left:18px !important;">@unless($isFinance)<input type="checkbox" id="select-all" style="accent-color:var(--blue);">@endunless</th>
              <th>Pelanggan</th>
              <th>ID Pelanggan</th>
              <th>PPPoE User</th>
              <th>Area</th>
              <th>IP Address</th>
              <th>Paket</th>
              <th style="width:120px;">Status</th>
              <th style="width:110px;">Mulai</th>
              <th style="width:80px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($customers as $customer)
            @php
              $st = $customer->status ?? 'unknown';
              if ($customer->is_free) {
                $badgeClass = 'cust-badge-free'; $badgeLabel = 'Gratis'; $badgeIcon = 'bx-gift';
              } else {
                [$badgeClass, $badgeLabel, $badgeIcon] = match($st) {
                  'active'       => ['cust-badge-active',    'Aktif',        'bxs-circle'],
                  'suspended'    => ['cust-badge-suspended', 'Diisolir',     'bx-pause-circle'],
                  'provisioning' => ['cust-badge-pending',   'Proses',       'bx-time-five'],
                  'failed'       => ['cust-badge-failed',    'Gagal',        'bx-error-circle'],
                  'pending'      => ['cust-badge-pending',   'Pending',      'bx-time'],
                  default        => ['cust-badge-pending',   ucfirst($st),   'bx-question-mark'],
                };
              }
              $avatarHue = abs(crc32($customer->name)) % 360;
            @endphp
            <tr>
              <td style="padding-left:18px !important;">@unless($isFinance)<input type="checkbox" class="row-check" value="{{ $customer->id }}" style="accent-color:var(--blue);">@endunless</td>

              {{-- Pelanggan --}}
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div class="cust-avatar" style="background: linear-gradient(135deg, hsl({{ $avatarHue }},62%,54%) 0%, hsl({{ ($avatarHue+30)%360 }},70%,44%) 100%);">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                  </div>
                  <div>
                    <div class="cust-name-main">{{ $customer->name }}</div>
                    @if($customer->phone)
                    <div class="cust-name-sub"><i class='bx bx-phone' style="font-size:.65rem;"></i> {{ $customer->phone }}</div>
                    @endif
                  </div>
                </div>
              </td>

              {{-- ID Pelanggan --}}
              <td><span class="cust-code"><i class='bx bx-hash' style="font-size:.7rem;"></i>{{ $customer->customer_code ?: '—' }}</span></td>

              {{-- PPPoE --}}
              <td><span class="cust-code"><i class='bx bx-wifi' style="font-size:.7rem;"></i>{{ $customer->pppoe_user }}</span></td>

              {{-- Area --}}
              <td>
                <div style="font-weight:600;font-size:.85rem;color:var(--txt);">{{ $customer->area->name ?? '—' }}</div>
              </td>

              {{-- IP Address --}}
              <td>
                @if($customer->remote_ip)
                <span class="cust-code" style="background:color-mix(in srgb,var(--green) 8%,var(--surface-2));color:color-mix(in srgb,var(--green) 75%,var(--txt));border-color:color-mix(in srgb,var(--green) 18%,var(--border));">
                  <i class='bx bx-globe' style="font-size:.7rem;"></i>{{ $customer->remote_ip }}
                </span>
                @else
                <span style="font-size:.8rem;color:var(--txt-3);">—</span>
                @endif
              </td>

              {{-- Paket --}}
              <td>
                @if($customer->package)
                <div class="cust-pkg-name">{{ $customer->package->name }}</div>
                <div class="cust-pkg-meta">{{ $customer->package->speed_label }} · Rp {{ number_format($customer->package->price, 0, ',', '.') }}</div>
                @else
                <span style="color:var(--txt-3);font-size:.8rem;font-style:italic;">Tanpa paket</span>
                @endif
              </td>

              {{-- Status --}}
              <td>
                <span class="cust-badge {{ $badgeClass }}">
                  @if($st === 'active' && !$customer->is_free)
                    <span class="cust-pulse"></span>
                  @else
                    <i class='bx {{ $badgeIcon }}' style="font-size:.72rem;"></i>
                  @endif
                  {{ $badgeLabel }}
                </span>
              </td>

              {{-- Mulai berlangganan --}}
              <td style="color:var(--txt-3);font-size:.8rem;white-space:nowrap;">
                {{ ($customer->billing_start_date ?? $customer->created_at)->format('d M Y') }}
              </td>

              {{-- Aksi --}}
              <td>
                <div class="dropdown">
                  <button class="cust-opsi-btn dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" data-bs-boundary="window" data-bs-strategy="fixed"
                    aria-expanded="false">
                    <i class='bx bx-dots-horizontal-rounded'></i> Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('admin.payments.manual', $customer) }}"><i class='bx bx-money' style="color:#16a34a;"></i> Tandai Bayar</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.customers.show', $customer) }}"><i class='bx bx-show' style="color:var(--blue);"></i> Lihat Detail</a></li>
                    @unless($isFinance)
                    <li><hr class="dropdown-divider" style="margin:3px 0;"></li>
                    <li><a class="dropdown-item" href="{{ route('admin.customers.edit', $customer) }}"><i class='bx bx-edit' style="color:var(--orange,#f97316);"></i> Edit Pelanggan</a></li>
                    @if($customer->status !== 'suspended')
                    <li>
                      <form action="{{ route('admin.customers.isolir', $customer) }}" method="POST" class="m-0" data-confirm="Isolir {{ $customer->name }}? IP-nya akan diblokir.">
                        @csrf
                        <button type="submit" class="dropdown-item" style="color:#dc2626;">
                          <i class='bx bx-lock-alt' style="color:#dc2626;"></i> Isolir
                        </button>
                      </form>
                    </li>
                    @else
                    <li>
                      <form action="{{ route('admin.customers.lepas-isolir', $customer) }}" method="POST" class="m-0" data-confirm="Lepas isolir {{ $customer->name }}?">
                        @csrf
                        <button type="submit" class="dropdown-item" style="color:#16a34a;">
                          <i class='bx bx-lock-open-alt' style="color:#16a34a;"></i> Lepas Isolir
                        </button>
                      </form>
                    </li>
                    @endif
                    <li>
                      <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="m-0" data-confirm="Hapus {{ $customer->name }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                          <i class='bx bx-trash' style="color:var(--red);"></i> Hapus
                        </button>
                      </form>
                    </li>
                    @endunless
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10">
                <div class="empty-state" style="padding:3rem;">
                  <div class="empty-state-icon"><i class='bx bx-group'></i></div>
                  <div class="empty-state-title">Belum ada pelanggan</div>
                  <div class="empty-state-desc">Mulai tambahkan pelanggan pertama Anda</div>
                  @unless($isFinance)
                  <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm mt-2">
                    <i class='bx bx-plus me-1'></i> Tambah Pelanggan
                  </a>
                  @endunless
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- ── Pagination Bar ── --}}
    <div class="cust-pagination-bar">
      <div class="text-muted">
        Menampilkan <strong>{{ $customers->firstItem() ?? 0 }}–{{ $customers->lastItem() ?? 0 }}</strong>
        dari <strong>{{ $customers->total() }}</strong> pelanggan
      </div>
      {{ $customers->links() }}
    </div>

  </div>{{-- /cust-card-shell --}}
</div>

@if((auth()->user()->role ?? null) === 'admin')
<div class="modal fade" id="billingStartImportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;border:1px solid var(--border);overflow:hidden;">
      <form action="{{ route('admin.customers.import-billing-start') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border);padding:1rem 1.25rem;">
          <div>
            <div style="font-size:.75rem;font-weight:700;color:var(--txt-3);text-transform:uppercase;letter-spacing:.08em;">Billing Start Date</div>
            <h5 class="modal-title mb-0" style="font-weight:800;">Bulk Update Tanggal Mulai Tagihan</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
          <div class="alert alert-info" style="border-radius:12px;">
            Gunakan file `CSV`, `TXT`, atau `XLSX` dengan header `pppoe_user` dan `billing_start_date`.
            Tanggal dipakai untuk hitung prorata bulan pertama pelanggan existing.
          </div>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.customers.import-billing-template') }}" class="ms-btn-secondary">
              <i class='bx bx-download'></i> Download Template Excel
            </a>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-weight:700;">File Update Tanggal Tagihan</label>
            <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx" required>
            <div class="form-text">Contoh isi: `NPL-064 | 2026-04-21`.</div>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="dry-run-import" name="dry_run" checked>
            <label class="form-check-label" for="dry-run-import">
              Jalankan `dry run` dulu, cek hasil tanpa mengubah data pelanggan
            </label>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:1rem 1.25rem;">
          <button type="button" class="ms-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="ms-btn">
            <i class='bx bx-upload'></i> Proses File
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif



@endsection

@section('scripts')
<script>
$(function() {
  var isFinance = @json($isFinance);
  $('#select-all').on('change', function() {
    if (isFinance) return;
    $('.row-check').prop('checked', this.checked);
    updateBulkBar();
  });

  $(document).on('change', '.row-check', function() {
    if (isFinance) return;
    updateBulkBar();
  });
});

function updateBulkBar() {
  if (@json($isFinance)) return;
  var count = $('.row-check:checked').length;
  if (count > 0) {
    $('#bulk-bar').slideDown(200);
    $('#bulk-count').text(count + ' dipilih');
  } else {
    $('#bulk-bar').slideUp(200);
  }
}

function bulkClear() {
  if (@json($isFinance)) return;
  $('#select-all').prop('checked', false);
  $('.row-check').prop('checked', false);
  updateBulkBar();
}

function bulkDelete() {
  if (@json($isFinance)) return;
  var ids = [];
  $('.row-check:checked').each(function() { ids.push($(this).val()); });
  if (!ids.length) return;
  var message = 'Hapus ' + ids.length + ' pelanggan? Data di sistem akan dihapus. Akun PPPoE di router tidak akan terpengaruh.';
  window._nkConfirm(message, function() {
      fetch('{{ route("admin.customers.bulkDelete") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ ids: ids })
      })
      .then(r => r.json())
      .then(function(data) {
        if (data.success) {
          toastr.success(data.message || 'Berhasil dihapus');
          setTimeout(() => location.reload(), 800);
        } else {
          toastr.error(data.message || 'Gagal menghapus');
        }
      })
      .catch(() => toastr.error('Kesalahan jaringan'));
  });
}



</script>
@endsection
