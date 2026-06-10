@extends('layouts.app')
@section('title', 'Area')

@section('content')
<div class="ms-page nk-list-page areas-index-page">
  <div class="ms-page-head">
    <div>
      <h1 class="ms-page-title">Area Jaringan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.areas.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Tambah Area
      </a>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-map-pin me-2'></i>Daftar Area</h5>
    </div>
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="areas-search" class="nk-search-input" placeholder="Cari area...">
        </div>
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:.78rem;color:var(--txt-3);">Tampilkan</span>
          <select id="areas-length" class="form-select form-select-sm">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="areas-table">
          <thead>
            <tr>
              <th style="width:50px;">#</th>
              <th>Nama Area</th>
              <th>MikroTik Identity</th>
              <th>Router IP</th>
              <th>VLAN ID</th>
              <th style="width:120px;">Pelanggan</th>
              <th style="width:90px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($areas as $index => $area)
            <tr>
              <td style="color:var(--txt-3);">{{ $index + 1 }}</td>
              <td><span style="font-weight:500;">{{ $area->name }}</span></td>
              <td>
                @if($area->router_identity)
                <span style="font-weight:600;font-size:.8rem;color:var(--orange,#f97316);">{{ $area->router_identity }}</span>
                @else
                <span style="color:var(--txt-3);font-size:.75rem;">—</span>
                @endif
              </td>
              <td style="color:var(--txt-3);"><code>{{ $area->router_ip }}</code></td>
              <td>
                @if($area->vlan_id)
                <span style="font-weight:600;font-size:.8rem;color:var(--blue);">{{ $area->vlan_id }}</span>
                @else
                <span style="color:var(--txt-3);font-size:.75rem;">—</span>
                @endif
              </td>
              <td>
                <span style="background:color-mix(in srgb,var(--blue) 10%,var(--surface));color:var(--blue);font-size:.75rem;font-weight:600;padding:3px 8px;border-radius:20px;">
                  {{ $area->customers_count ?? 0 }} pelanggan
                </span>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.areas.edit', $area) }}" class="nk-action-btn edit" title="Ubah">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="m-0" data-confirm="Hapus {{ $area->name }}?">
                    @csrf @method('DELETE')
                    <button type="submit" class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7">
                <div class="text-center py-5" style="color:var(--txt-3);">
                  <i class='bx bx-map-pin fs-1 d-block mb-2'></i>
                  <div style="font-size:.9375rem;font-weight:500;">Belum ada area</div>
                  <a href="{{ route('admin.areas.create') }}" class="ms-btn mt-3">
                    <i class='bx bx-plus'></i> Tambah Area
                  </a>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(function() {
    var table = $('#areas-table').DataTable({
      dom: '<rt><"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 25,
      autoWidth: false,
      scrollX: true,
      order: [[1, 'asc']],
      language: {
        info: '_START_-_END_ dari _TOTAL_',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [5] }]
    });
    $('#areas-search').on('input', function() { table.search(this.value).draw(); });
    $('#areas-length').on('change', function() { table.page.len(+this.value).draw(); });
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });
  });
</script>
@endsection
