@extends('layouts.app')
@section('title', 'Pelanggan')

@section('styles')
<style>
  /* ── Fix modal z-index (prevent workspace-shell interference) ────────── */
  #billingStartImportModal { z-index: 1060 !important; }
  #billingStartImportModal .modal-dialog { pointer-events: auto; }
  .modal-backdrop { z-index: 1055 !important; }

  /* ── Panel transparent (sama seperti ONT inventory) ─────────────────── */
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
    min-height: 220px; /* space for dropdown on 1 row */
  }
  @media (min-width: 768px) {
    .customers-index-page .ms-table-shell .table-responsive {
      overflow: visible !important;
    }
  }
  .customers-index-page .ms-table-shell .dataTables_wrapper { padding: 0 !important; }

  /* ── Filter tabs ─────────────────────────────────────────────────────── */
  .cust-filter-tabs {
    display: inline-flex;
    align-items: center;
    gap: .2rem;
    padding: .35rem;
    border-radius: 999px;
    background: var(--surface);
    border: 1px solid var(--border);
    box-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 8px 18px rgba(37, 99, 235, .08);
    flex-wrap: wrap;
  }
  .cust-filter-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 84px;
    height: 34px;
    padding: 0 .9rem;
    font-size: .78rem;
    font-weight: 600;
    border-radius: 999px;
    text-decoration: none;
    background: transparent;
    color: var(--txt-3);
    transition: color .18s ease, background .18s ease, transform .18s ease;
    white-space: nowrap;
    line-height: 1;
  }
  .cust-filter-tab:hover { color: var(--txt); transform: translateY(-1px); }
  .cust-filter-tab.active {
    color: var(--blue);
    background: color-mix(in srgb, var(--blue) 10%, var(--surface));
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--blue) 18%, var(--border));
  }

  /* ── Table cell sizing (sama seperti ONT inventory) ─────────────────── */
  #customers-table td { padding: .45rem .75rem !important; }
  #customers-table th { padding: .5rem .75rem !important; font-size: .73rem; text-transform: uppercase; letter-spacing: .4px; }
  #customers-table td { font-size: .8125rem; }
  
  /* Fix z-index stacking context for dropdowns inside table rows */
  #customers-table tbody tr { position: relative; z-index: 1; }
  #customers-table tbody tr:focus-within,
  #customers-table tbody tr:hover { z-index: 10; }

  /* ── Action buttons ─────────────────────────────────────────────────── */
  .cust-action-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 6px; cursor: pointer;
    text-decoration: none; transition: opacity .12s;
    border: 1px solid transparent;
  }
  .cust-action-btn i { font-size: .9rem; }
  .cust-action-btn.view {
    color: var(--blue);
    background: color-mix(in srgb, var(--blue) 10%, var(--surface));
    border-color: color-mix(in srgb, var(--blue) 22%, var(--border));
  }
  .cust-action-btn.edit {
    color: var(--orange, #f97316);
    background: color-mix(in srgb, var(--orange, #f97316) 10%, var(--surface));
    border-color: color-mix(in srgb, var(--orange, #f97316) 22%, var(--border));
  }
  .cust-action-btn.delete {
    color: var(--red);
    background: color-mix(in srgb, var(--red) 10%, var(--surface));
    border-color: color-mix(in srgb, var(--red) 22%, var(--border));
  }
  .cust-action-btn:hover { opacity: .75; }
  .cust-action-btn[data-tooltip] {
    position: relative;
  }
  .cust-action-btn[data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 10px);
    transform: translateX(-50%) scale(.92);
    transform-origin: bottom center;
    padding: .38rem .55rem;
    border-radius: 8px;
    background: #1f2937;
    color: #f8fafc;
    font-size: .72rem;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .2);
    transition: opacity .18s ease, transform .18s ease;
    z-index: 20;
  }
  .cust-action-btn[data-tooltip]::before {
    content: "";
    position: absolute;
    left: 50%;
    bottom: calc(100% + 4px);
    width: 9px;
    height: 9px;
    background: #1f2937;
    transform: translateX(-50%) rotate(45deg) scale(.92);
    opacity: 0;
    transition: opacity .18s ease, transform .18s ease;
    pointer-events: none;
    z-index: 19;
  }
  .cust-action-btn[data-tooltip]:hover::after,
  .cust-action-btn[data-tooltip]:hover::before,
  .cust-action-btn[data-tooltip]:focus-visible::after,
  .cust-action-btn[data-tooltip]:focus-visible::before {
    opacity: 1;
    transform: translateX(-50%) scale(1);
  }

  /* ── Code badge PPPoE ───────────────────────────────────────────────── */
  #customers-table code {
    background: color-mix(in srgb, var(--blue) 8%, var(--surface));
    color: color-mix(in srgb, var(--blue) 80%, var(--txt));
    border: 1px solid color-mix(in srgb, var(--blue) 18%, var(--border));
    padding: 2px 7px; border-radius: 6px; font-size: .78rem; font-weight: 600;
  }

  /* ── DataTables pagination ───────────────────────────────────────────── */
  #customers-table_wrapper input[type="search"],
  #customers-table_wrapper select {
    border-radius: 6px !important; border: 1px solid var(--border) !important;
    background: var(--surface) !important; color: var(--txt) !important;
    font-size: .8125rem !important;
  }

  .cust-status-toolbar {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
    padding: .15rem 0 1rem;
  }
  .cust-status-note {
    font-size:.76rem;
    color:var(--txt-3);
    font-weight:500;
  }

  /* ── Bulk bar border ─────────────────────────────────────────────────── */
  #bulk-bar.ms-panel {
    border: 1px solid var(--border) !important;
    background: var(--surface) !important;
    border-radius: 8px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.05) !important;
  }

  @media (max-width: 700px) {
    .cust-filter-tabs {
      width: 100%;
      justify-content: flex-start;
      overflow-x: auto;
      flex-wrap: nowrap;
    }
    .cust-filter-tab {
      flex: 0 0 auto;
      min-width: 78px;
    }
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


  <div class="ms-panel" style="overflow: visible !important;">
    {{-- Panel Head --}}
    <div class="ms-panel-head d-flex justify-content-between align-items-center">
      <span class="ms-panel-title">
        <i class='bx bx-group me-2' style="color:var(--blue);"></i>Data Pelanggan
        <span class="ms-2 ms-kpi-chip"><strong>{{ $customers->total() }}</strong> total</span>
      </span>
      {{-- Area filter --}}
      @if($areas->isNotEmpty())
      <form method="GET" action="{{ route('admin.customers.index') }}" id="area-filter-form" class="d-flex align-items-center gap-2">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="per_page" value="{{ $perPage ?? 50 }}">
        <select name="area_id" class="form-select form-select-sm" data-hide-search onchange="this.form.submit()">
          <option value="">Semua Area</option>
          @foreach($areas as $area)
          <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
          @endforeach
        </select>
        @if(request('area_id'))
        <a href="{{ route('admin.customers.index') }}" style="font-size:.78rem;color:var(--txt-3);text-decoration:none;" title="Hapus filter area">
          <i class='bx bx-x'></i>
        </a>
        @endif
      </form>
      @endif
    </div>

    {{-- Toolbar: Server-side search/filter --}}
    <div class="ms-panel-body" style="padding: 12px 16px; border-bottom: 1px solid var(--border);">
      <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;">
        
        {{-- Left: Status Filter Tabs --}}
        <div style="display:flex; align-items:center; gap:3px; background:var(--surface-2); padding:3px; border-radius:10px; border:1px solid var(--border);">
          @foreach($statusTabs as $value => $label)
            @php
              $isActiveTab = request('status', '') === $value;
              $tabQuery = array_merge(request()->query(), ['status' => $value === '' ? null : $value]);
              if ($value === '') unset($tabQuery['status']);
            @endphp
            <a href="{{ route('admin.customers.index', $tabQuery) }}"
               style="padding:5px 14px; border-radius:7px; font-size:0.8125rem; font-weight:600; white-space:nowrap; text-decoration:none; transition:all .15s; {{ $isActiveTab ? 'background:var(--surface); color:var(--blue); box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid var(--border);' : 'color:var(--txt-3); border:1px solid transparent;' }}"
               aria-selected="{{ $isActiveTab ? 'true' : 'false' }}">
              {{ $label }}
            </a>
          @endforeach
        </div>

        {{-- Right: Search + Per Page + Submit --}}
        <form method="GET" action="{{ route('admin.customers.index') }}" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
          <input type="hidden" name="status" value="{{ request('status') }}">
          <div style="display:flex; align-items:center; gap:6px; background:var(--surface-2); border:1px solid var(--border); border-radius:8px; padding:5px 10px;">
            <i class='bx bx-search' style="color:var(--txt-3); font-size:1rem;"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelanggan..." 
                   style="border:none; outline:none; background:transparent; font-size:0.8125rem; color:var(--txt); width:190px;">
          </div>
          <select name="per_page" onchange="this.form.submit()"
                  style="height:34px; width:65px; padding:0 6px; font-size:0.8125rem; font-weight:600; border:1px solid var(--border); border-radius:8px; background:var(--surface); color:var(--txt); outline:none; cursor:pointer;">
            <option value="25" @selected(($perPage ?? 50) == 25)>25</option>
            <option value="50" @selected(($perPage ?? 50) == 50)>50</option>
            <option value="100" @selected(($perPage ?? 50) == 100)>100</option>
            <option value="200" @selected(($perPage ?? 50) == 200)>200</option>
          </select>
          <button type="submit" style="height:34px; padding:0 12px; background:var(--blue); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:0.8125rem; font-weight:600; display:flex; align-items:center; gap:4px;">
            <i class='bx bx-search'></i>
          </button>
        </form>

      </div>
    </div>


    {{-- Table --}}
    <div class="ms-table-shell">
      <div class="table-responsive mt-2">
        <table class="table table-flat mb-0" id="customers-table">
          <thead>
            <tr>
              <th style="width:38px;">@unless($isFinance)<input type="checkbox" id="select-all" style="accent-color:var(--blue);">@endunless</th>
              <th>Pelanggan</th>
              <th>ID Pelanggan</th>
              <th>PPPoE User</th>
              <th>Area</th>
              <th>No. HP</th>
              <th>Paket</th>
              <th style="width:110px;">Status</th>
              <th style="width:110px;">Berlangganan</th>
              <th style="width:90px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($customers as $customer)
            @php
              $statusMap = [
                'active'       => ['label' => 'Aktif',        'class' => 'badge-active'],
                'suspended'    => ['label' => 'Diisolir',     'class' => 'badge-inactive'],
                'provisioning' => ['label' => 'Dalam Proses', 'class' => 'badge-pending'],
                'failed'       => ['label' => 'Gagal',        'class' => 'badge-danger'],
                'pending'      => ['label' => 'Pending',      'class' => 'badge-pending'],
              ];
              $s = $statusMap[$customer->status] ?? ['label' => ucfirst($customer->status), 'class' => 'badge-inactive'];
              if ($customer->is_free) {
                $s = ['label' => 'Gratis', 'class' => 'badge-pending'];
              }
            @endphp
            <tr>
              <td>@unless($isFinance)<input type="checkbox" class="row-check" value="{{ $customer->id }}" style="accent-color:var(--blue);">@endunless</td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div style="flex-shrink:0;width:40px;height:40px;border-radius:12px;background:hsl({{ crc32($customer->name) % 360 }},50%,58%);font-size:1.05rem;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                  </div>
                  <div>
                    <div style="font-weight:700;font-size: .95rem; color:var(--txt);">{{ $customer->name }}</div>
                  </div>
                </div>
              </td>
              <td><code style="font-size:.75rem;">{{ $customer->customer_code ?: '—' }}</code></td>
              <td><code>{{ $customer->pppoe_user }}</code></td>
              <td>
                <div style="font-weight:600;color:var(--txt);">{{ $customer->area->name ?? '—' }}</div>
              </td>
              <td style="font-size:.8125rem;color:var(--txt-3);">{{ $customer->phone ?: '—' }}</td>
              <td>
                @if($customer->package)
                <div style="font-weight:600;color:var(--txt);">{{ $customer->package->name }}</div>
                <div style="font-size:.73rem;color:var(--txt-3);">{{ $customer->package->speed_label }} · Rp {{ number_format($customer->package->price, 0, ',', '.') }}</div>
                @else
                <span style="color:var(--txt-3);font-size:.8rem;">Tidak ada paket</span>
                @endif
              </td>
              <td>
                <span class="badge-status {{ $s['class'] }}">
                  @if($customer->status === 'active')<i class='bx bxs-circle bx-flashing' style="font-size:.4rem;margin-right:3px;vertical-align:middle;"></i>@endif
                  {{ $s['label'] }}
                </span>
              </td>
              <td style="color:var(--txt-3);white-space:nowrap;">{{ ($customer->billing_start_date ?? $customer->created_at)->format('d M Y') }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="window" data-bs-strategy="fixed" aria-expanded="false" style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                    Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('admin.payments.manual', $customer) }}"><i class='bx bx-money' style="color:#16a34a;"></i> Tandai Bayar</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.customers.show', $customer) }}"><i class='bx bx-show'></i> Lihat Detail</a></li>
                    @unless($isFinance)
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('admin.customers.edit', $customer) }}"><i class='bx bx-edit'></i> Edit Pelanggan</a></li>
                    <li>
                      <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="m-0" data-confirm="Hapus {{ $customer->name }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class='bx bx-trash' style="color:var(--red);"></i> Hapus</button>
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
                <div class="empty-state">
                  <div class="empty-state-icon"><i class='bx bx-group'></i></div>
                  <div class="empty-state-title">Belum ada pelanggan</div>
                  <div class="empty-state-desc">Mulai tambahkan pelanggan pertama Anda</div>
                  @unless($isFinance)
                  <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
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
    <div class="ms-panel-body pt-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
          Menampilkan {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} dari {{ $customers->total() }} pelanggan
        </div>
        {{ $customers->links() }}
      </div>
    </div>
  </div>
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
