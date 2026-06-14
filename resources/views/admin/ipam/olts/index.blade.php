@extends('layouts.app')
@section('title', 'OLT Management')

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
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="olts-search" class="nk-search-input" placeholder="Cari OLT...">
        </div>
        <form id="bulkDeleteForm" action="{{ route('admin.ipam.olts.bulkDestroy') }}" method="POST" class="d-inline-block ms-3" data-confirm="Hapus semua OLT terpilih?">
          @csrf @method('DELETE')
          <button type="submit" class="ms-btn-danger ms-btn-sm d-none" id="btnBulkDelete" style="height: 38px;">
            <i class='bx bx-trash'></i> Hapus Terpilih (<span id="bulkCount">0</span>)
          </button>
        </form>
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
              <th style="width:120px;">Aksi</th>
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
                <div class="d-flex gap-1">
                  <button type="button" class="nk-action-btn edit" title="Edit"
                    onclick="editOlt({{ $olt->id }}, '{{ addslashes($olt->name) }}', '{{ $olt->ip_address }}')">
                    <i class='bx bx-edit'></i>
                  </button>
                  <form action="{{ route('admin.ipam.olts.destroy', $olt) }}" method="POST" class="m-0" data-confirm="Hapus OLT {{ $olt->name }}?">
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

{{-- Sticky Bulk Delete Banner --}}
<div id="stickyBulkDeleteBar" class="fixed-bottom p-3 d-none shadow-lg text-center" style="background: rgba(255, 255, 255, 0.95); border-top: 1px solid var(--border); backdrop-filter: blur(5px); z-index: 1050; padding-bottom: max(1rem, env(safe-area-inset-bottom)) !important;">
  <span class="me-3 fw-bold" style="color: var(--txt-1);"><span id="stickyBulkCount">0</span> OLT Terpilih</span>
  <button type="submit" form="bulkDeleteForm" class="ms-btn-danger">
    <i class='bx bx-trash'></i> Hapus Semua Terpilih
  </button>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editOltModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editOltForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit OLT</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama OLT</label>
            <input type="text" name="name" id="edit-olt-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">IP Address</label>
            <input type="text" name="ip_address" id="edit-olt-ip" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="ms-btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="ms-btn">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(function() {
    var table = $('#olts-table').DataTable({
      dom: '<"d-flex justify-content-between align-items-center mb-3"l>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: -1,
      lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Semua"]],
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
      var checkedCount = $('.olt-checkbox:checked').length;
      if (checkedCount > 0) {
        $('#btnBulkDelete').removeClass('d-none');
        $('#bulkCount').text(checkedCount);
        $('#stickyBulkDeleteBar').removeClass('d-none');
        $('#stickyBulkCount').text(checkedCount);
      } else {
        $('#btnBulkDelete').addClass('d-none');
        $('#stickyBulkDeleteBar').addClass('d-none');
      }
      $('#selectAll').prop('checked', $('.olt-checkbox').length > 0 && checkedCount === $('.olt-checkbox').length);
    }

    $('#selectAll').on('change', function() {
      $('.olt-checkbox').prop('checked', $(this).prop('checked'));
      updateBulkDelete();
    });

    $(document).on('change', '.olt-checkbox', function() {
      updateBulkDelete();
    });
    
    // On table redraw (search/sort), update header checkbox state
    table.on('draw', function () {
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
