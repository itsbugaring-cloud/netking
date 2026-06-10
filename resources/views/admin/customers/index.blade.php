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
  }
  .customers-index-page .ms-table-shell .dataTables_wrapper { padding: 0 !important; }

  /* ── Filter tabs ─────────────────────────────────────────────────────── */
  .cust-filter-tabs {
    display: inline-flex; align-items: center; gap: 1px;
  }
  .cust-filter-tab {
    display: inline-flex; align-items: center;
    padding: .25rem .75rem; font-size: .78rem; font-weight: 500;
    border-radius: 6px; border: none;
    background: transparent; color: var(--txt-3);
    cursor: pointer; transition: color .12s, background .12s;
    white-space: nowrap; line-height: 1.5;
  }
  .cust-filter-tab:hover { color: var(--txt); background: var(--surface-2); }
  .cust-filter-tab.active { color: var(--txt); font-weight: 600; background: var(--surface-2); }

  /* ── Table cell sizing (sama seperti ONT inventory) ─────────────────── */
  #customers-table td { padding: .45rem .75rem !important; }
  #customers-table th { padding: .5rem .75rem !important; font-size: .73rem; text-transform: uppercase; letter-spacing: .4px; }
  #customers-table td { font-size: .8125rem; }

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

  /* ── Bulk bar border ─────────────────────────────────────────────────── */
  #bulk-bar.ms-panel {
    border: 1px solid var(--border) !important;
    background: var(--surface) !important;
    border-radius: 8px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.05) !important;
  }
</style>
@endsection

@section('content')
@php
  $isFinance = (auth()->user()->role ?? null) === 'finance';
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

  <div class="ms-panel">
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
        <select name="area_id" class="form-select form-select-sm" onchange="this.form.submit()">
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
    <div class="ms-panel-body pt-0 pb-0">
      <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex gap-3 flex-wrap align-items-center">
          <div class="nk-search-wrap nk-table-search-trigger">
            <i class='bx bx-search'></i>
            <input type="text" name="search" class="nk-search-input" value="{{ request('search') }}" placeholder="Cari nama, PPPoE, no HP, alamat...">
          </div>

          <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="suspended" @selected(request('status') === 'suspended')>Diisolir</option>
            <option value="provisioning" @selected(request('status') === 'provisioning')>Dalam Proses</option>
            <option value="failed" @selected(request('status') === 'failed')>Gagal</option>
          </select>
        </div>

        <div class="d-flex align-items-center gap-2">
          <span style="font-size:.76rem;color:var(--txt-3);font-weight:500;">Tampilkan</span>
          <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="25" @selected(($perPage ?? 50) == 25)>25</option>
            <option value="50" @selected(($perPage ?? 50) == 50)>50</option>
            <option value="100" @selected(($perPage ?? 50) == 100)>100</option>
            <option value="200" @selected(($perPage ?? 50) == 200)>200</option>
          </select>
          <button type="submit" class="ms-btn-secondary">Terapkan</button>
          <a href="{{ route('admin.customers.index') }}" class="ms-btn-ghost">Reset</a>
        </div>
      </form>
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
            @endphp
            <tr>
              <td>@unless($isFinance)<input type="checkbox" class="row-check" value="{{ $customer->id }}" style="accent-color:var(--blue);">@endunless</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="flex-shrink:0;width:34px;height:34px;border-radius:10px;background:hsl({{ crc32($customer->name) % 360 }},50%,58%);font-size:.76rem;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                  </div>
                  <div>
                    <div style="font-weight:600;color:var(--txt);">{{ $customer->name }}</div>
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
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.customers.show', $customer) }}" class="cust-action-btn view" title="Lihat Detail">
                    <i class='bx bx-show'></i>
                  </a>
                  @unless($isFinance)
                  <a href="{{ route('admin.customers.edit', $customer) }}" class="cust-action-btn edit" title="Edit">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="m-0" data-confirm="Hapus {{ $customer->name }}?">
                    @csrf @method('DELETE')
                    <button type="submit" class="cust-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                  @endunless
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
  Swal.fire({
    title: 'Hapus ' + ids.length + ' pelanggan?',
    text: 'Data akan dihapus dari sistem. Akun PPPoE di router tidak akan terpengaruh.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then(function(result) {
    if (result.isConfirmed) {
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
    }
  });
}
</script>
@endsection
