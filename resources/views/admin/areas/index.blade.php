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
        </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="areas-table">
          <thead>
            <tr>
              <th style="width:50px;">#</th>
              <th>Nama Area</th>
              <th>MikroTik Identity</th>
              <th>Router IP</th>
              <th>VLAN PPPoE</th>
              <th>VLAN MGMT</th>
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
                @if($area->vlan_pppoe)
                <span style="font-weight:600;font-size:.8rem;color:var(--blue);">{{ $area->vlan_pppoe }}</span>
                @else
                <span style="color:var(--txt-3);font-size:.75rem;">—</span>
                @endif
              </td>
              <td>
                @if($area->vlan_mgmt)
                <span style="font-weight:600;font-size:.8rem;color:var(--blue);">{{ $area->vlan_mgmt }}</span>
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
                <div class="dropdown">
                  <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                    Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('admin.areas.edit', $area) }}"><i class='bx bx-edit'></i> Edit Area</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="m-0" data-confirm="Hapus {{ $area->name }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class='bx bx-trash' style="color:var(--red);"></i> Hapus</button>
                      </form>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8">
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
      dom: '<"d-none"ilp>rt',
      pageLength: 25,
      autoWidth: false,
      scrollX: true,
      order: [[1, 'asc']],
      language: {
        info: 'Menampilkan <b>_START_</b> hingga <b>_END_</b> dari <b>_TOTAL_</b> hasil',
        lengthMenu: '_MENU_ per hal',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [6] }]
    });
    $('#areas-search').on('input', function() { table.search(this.value).draw(); });
    
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });
  });
</script>
@endsection

