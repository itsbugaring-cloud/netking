@extends('layouts.app')
@section('title', 'Riwayat Transaksi Inventaris')

@section('content')
<div class="ms-page nk-list-page inv-history-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-history'></i> Inventaris</div>
      <h1 class="ms-page-title">Riwayat Transaksi</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.dashboard') }}" class="ms-btn-secondary">
        <i class='bx bx-tachometer'></i> Dasbor
      </a>
    </div>
  </div>



  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Log Semua Aktivitas</h5>
        <div class="ms-panel-subtitle">Riwayat mutasi, pemotongan kabel, dan perubahan inventori</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> {{ $logs->total() }} entri</span>
      </div>
    </div>

    <div class="ms-toolbar">
      <div class="ms-toolbar-left">
        <form method="GET" action="{{ route('admin.inventory.history.index') }}" class="ms-filter-form flex-wrap">
          <select name="tipe" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            @foreach($tipe_options as $val => $label)
              <option value="{{ $val }}" {{ request('tipe') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua User</option>
            @foreach($user_list as $u)
              <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
          </select>
          <select name="referensi_tabel" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Referensi</option>
            <option value="inv_units" {{ request('referensi_tabel') === 'inv_units' ? 'selected' : '' }}>Unit SN</option>
            <option value="inv_kabels" {{ request('referensi_tabel') === 'inv_kabels' ? 'selected' : '' }}>Kabel</option>
            <option value="inv_qty_stocks" {{ request('referensi_tabel') === 'inv_qty_stocks' ? 'selected' : '' }}>Qty</option>
          </select>
          <input type="date" name="date_from" value="{{ request('date_from') }}"
                 class="form-control form-control-sm" style="max-width:140px">
          <input type="date" name="date_to" value="{{ request('date_to') }}"
                 class="form-control form-control-sm" style="max-width:140px">
          <button type="submit" class="ms-btn-secondary ms-btn-sm"><i class='bx bx-search'></i></button>
          @if(request()->anyFilled(['tipe','user_id','referensi_tabel','date_from','date_to']))
          <a href="{{ route('admin.inventory.history.index') }}" class="ms-btn-ghost ms-btn-sm">Reset</a>
          @endif
        </form>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Tipe</th>
              <th>Referensi</th>
              <th>Dari</th>
              <th>Ke</th>
              <th class="text-end">Qty</th>
              <th>Pelaku</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            <tr>
              <td style="white-space:nowrap;color:var(--txt-3)">
                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
              </td>
              <td>
                @php $tipe = $log->tipe ?? ''; @endphp
                @if($tipe === 'masuk_baru')
                  <span class="badge-status badge-active">Masuk</span>
                @elseif($tipe === 'mutasi')
                  <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Mutasi</span>
                @elseif($tipe === 'potong_kabel')
                  <span class="badge-status badge-pending">Potong Kabel</span>
                @elseif($tipe === 'pasang')
                  <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Pasang</span>
                @elseif($tipe === 'retur')
                  <span class="badge-status badge-inactive">Retur</span>
                @elseif($tipe === 'barang_keluar')
                  <span class="badge-status badge-inactive">Keluar</span>
                @elseif($tipe === 'penyesuaian')
                  <span class="badge-status badge-inactive">Penyesuaian</span>
                @else
                  <span class="badge-status badge-inactive">{{ $tipe }}</span>
                @endif
              </td>
              <td>
                @if($log->referensi_tabel && $log->referensi_id)
                  <code>{{ str_replace('inv_', '', $log->referensi_tabel) }}#{{ $log->referensi_id }}</code>
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </td>
              <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $log->dari ?? '-' }}
              </td>
              <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $log->ke ?? '-' }}
              </td>
              <td class="text-end">{{ $log->qty ? number_format($log->qty, 1) : '-' }}</td>
              <td>{{ $log->user->name ?? $log->pelaku ?? '-' }}</td>
              <td style="color:var(--txt-3);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $log->keterangan ?? '-' }}
              </td>
            </tr>
            @empty
            <tr><td colspan="8">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-history'></i></div>
                <div class="empty-state-title">Belum ada histori</div>
                <div class="empty-state-desc">Aktivitas inventori akan muncul di sini</div>
              </div>
            </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($logs->hasPages())
    <div class="ms-panel-footer">{{ $logs->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
@endsection
