@extends('layouts.app')
@section('title', 'OLT Management')

@section('styles')
<style>
  /* Fix modal z-index (prevent workspace-shell interference) */
  #editOltModal { z-index: 1060 !important; }
  #editOltModal .modal-dialog { pointer-events: auto; }
  .modal-backdrop { z-index: 1055 !important; }
</style>
@endsection

@section('content')
<div class="ms-page nk-list-page ipam-olts-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-broadcast'></i> IPAM</div>
      <h1 class="ms-page-title">OLT Management</h1>
    </div>
    <div class="ms-page-actions">
      <button type="button" class="ms-btn-secondary" onclick="document.getElementById('import-section').classList.toggle('d-none')">
        <i class='bx bx-import'></i> Import Bookmark
      </button>
    </div>
  </div>

  {{-- Bookmark Import Form --}}
  <div id="import-section" class="ms-panel mb-3 d-none">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-import me-2'></i>Import dari Bookmark</h5>
    </div>
    <div class="p-3">
      <form action="{{ route('admin.ipam.olts.importBookmarks') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="d-flex align-items-end gap-3">
          <div class="flex-grow-1">
            <label class="form-label" style="font-size:.8rem;">File Bookmark (HTML)</label>
            <input type="file" name="file" accept=".html,.htm" class="form-control form-control-sm" required>
          </div>
          <button type="submit" class="ms-btn">
            <i class='bx bx-upload'></i> Upload & Import
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Add New OLT Form --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-plus-circle me-2'></i>Tambah OLT Baru</h5>
    </div>
    <div class="p-3">
      <form action="{{ route('admin.ipam.olts.store') }}" method="POST">
        @csrf
        <div class="d-flex align-items-end gap-3">
          <div class="flex-grow-1">
            <label class="form-label" style="font-size:.8rem;">Nama OLT</label>
            <input type="text" name="name" class="form-control form-control-sm" placeholder="Nama OLT" required value="{{ old('name') }}">
          </div>
          <div class="flex-grow-1">
            <label class="form-label" style="font-size:.8rem;">IP Address</label>
            <input type="text" name="ip_address" class="form-control form-control-sm" placeholder="192.168.1.1" required value="{{ old('ip_address') }}">
          </div>
          <button type="submit" class="ms-btn">
            <i class='bx bx-plus'></i> Tambah
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- OLT Table --}}
  <div class="ms-panel">
    <div class="ms-panel-head d-flex align-items-center justify-content-between">
      <span class="ms-panel-title"><i class='bx bx-broadcast me-2'></i>Daftar OLT</span>
    </div>

    {{-- Bulk Action Bar (matches Customers page pattern) --}}
    <form id="bulkDeleteForm" action="{{ route('admin.ipam.olts.bulkDestroy') }}" method="POST" data-confirm="Hapus semua OLT terpilih?">
      @csrf @method('DELETE')
      <div id="bulk-bar" style="display:none; margin: 0 1rem .75rem;">
        <div class="ms-panel" style="border:1px solid var(--border)!important;background:var(--surface)!important;border-radius:8px!important;box-shadow:none!important;">
          <div class="ms-panel-body d-flex align-items-center justify-content-between gap-3 py-3">
            <span class="ms-chip" id="bulk-count">0 dipilih</span>
            <div class="d-flex gap-2">
              <button type="submit" class="ms-btn-ghost">
                <i class='bx bx-trash'></i> Hapus Terpilih
              </button>
              <button type="button" class="ms-btn-secondary" onclick="bulkClear()">Batal</button>
            </div>
          </div>
        </div>
      </div>
    </form>
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="olts-search" class="nk-search-input" placeholder="Cari OLT...">
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="olts-table">
          <thead>
            <tr>
              <th style="width: 40px;" class="text-center">
                <input type="checkbox" id="selectAll" class="form-check-input">
              </th>
              <th style="width:50px;">#</th>
              <th>Nama</th>
              <th>IP Address</th>
              <th style="width:100px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($olts as $index => $olt)
            <tr>
              <td class="text-center">
                <input type="checkbox" class="form-check-input olt-checkbox" name="ids[]" value="{{ $olt->id }}" form="bulkDeleteForm">
              </td>
              <td style="color:var(--txt-3);">{{ $index + 1 }}</td>
              <td><span style="font-weight:500;">{{ $olt->name }}</span></td>
              <td><code>{{ $olt->ip_address }}</code></td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                    Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                      <button type="button" class="dropdown-item"
                        onclick="editOlt({{ $olt->id }}, '{{ addslashes($olt->name) }}', '{{ $olt->ip_address }}')">
                        <i class='bx bx-edit'></i> Edit
                      </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form action="{{ route('admin.ipam.olts.destroy', $olt) }}" method="POST" class="m-0" data-confirm="Hapus OLT {{ $olt->name }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                          <i class='bx bx-trash' style="color:var(--red);"></i> Hapus
                        </button>
                      </form>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5">
                <div class="text-center py-5" style="color:var(--txt-3);">
                  <i class='bx bx-broadcast fs-1 d-block mb-2'></i>
                  <div style="font-size:.9375rem;font-weight:500;">Belum ada OLT</div>
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

@push('modals')
{{-- Edit Modal --}}
<div class="modal fade" id="editOltModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;border:1px solid var(--border);overflow:hidden;box-shadow:0 10px 25px rgba(15,23,42,0.1);">
      <form id="editOltForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header" style="border-bottom:1px solid var(--border);padding:1rem 1.25rem;background:var(--surface);">
          <h5 class="modal-title mb-0" style="font-weight:800;">Edit OLT</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
          <div class="mb-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">Nama OLT</label>
            <input type="text" name="name" id="edit-olt-name" class="form-control" required>
          </div>
          <div class="mb-0">
            <label class="form-label" style="font-size:.8rem;font-weight:600;">IP Address</label>
            <input type="text" name="ip_address" id="edit-olt-ip" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:1rem 1.25rem;background:var(--bg);">
          <button type="button" class="ms-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="ms-btn">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush
@endsection

@section('scripts')
<script>
  $(function() {
    var table = $('#olts-table').DataTable({
      dom: '<"d-none"ilp>rt',
      pageLength: 25,
      autoWidth: false,
      order: [[1, 'asc']],
      language: {
        info: 'Menampilkan <b>_START_</b> hingga <b>_END_</b> dari <b>_TOTAL_</b> hasil',
        lengthMenu: '_MENU_ per hal',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [0, 4] }]
    });

    $('#olts-search').on('input', function() { table.search(this.value).draw(); });

    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });

    // Bulk Delete Logic
    function updateBulkDelete() {
      var count = $('.olt-checkbox:checked').length;
      if (count > 0) {
        $('#bulk-bar').slideDown(200);
        $('#bulk-count').text(count + ' dipilih');
      } else {
        $('#bulk-bar').slideUp(200);
      }
      $('#selectAll').prop('checked', $('.olt-checkbox').length > 0 && count === $('.olt-checkbox').length);
    }

    function bulkClear() {
      $('#selectAll').prop('checked', false);
      $('.olt-checkbox').prop('checked', false);
      updateBulkDelete();
    }

    $('#selectAll').on('change', function() {
      $('.olt-checkbox').prop('checked', $(this).prop('checked'));
      updateBulkDelete();
    });

    $(document).on('change', '.olt-checkbox', function() {
      updateBulkDelete();
    });

    table.on('draw', function() {
      updateBulkDelete();
    });
  });

  function editOlt(id, name, ip) {
    document.getElementById('editOltForm').action = '/admin/ipam/olts/' + id;
    document.getElementById('edit-olt-name').value = name;
    document.getElementById('edit-olt-ip').value = ip;
    new bootstrap.Modal(document.getElementById('editOltModal')).show();
  }
</script>
@endsection
