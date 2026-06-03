@extends('layouts.app')
@section('title', 'Areas')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
  <div>
    <h4>Areas</h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Areas</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('admin.areas.create') }}" class="btn btn-primary btn-sm">
    <i class='bx bx-plus me-1'></i> Add Area
  </a>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title"><i class='bx bx-map-pin me-2' style="color:#2563eb;"></i>Network Coverage Areas</h5>
  </div>
  <div class="table-responsive">
    <table class="table" id="areas-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Area Name</th>
          <th>Description</th>
          <th>Customers</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($areas as $index => $area)
        <tr>
          <td style="color:#64748b; font-size:0.8125rem;">{{ $index + 1 }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="sneat-icon-box icon-box-primary" style="width:32px; height:32px; font-size:1rem;">
                <i class='bx bx-map'></i>
              </div>
              <span style="font-weight:500; color:#1e293b;">{{ $area->name }}</span>
            </div>
          </td>
          <td style="font-size:0.875rem; color:#64748b;">{{ $area->description ?? '-' }}</td>
          <td>
            <span style="background:rgba(105,108,255,0.1); color:#2563eb; font-size:0.75rem; font-weight:600; padding:3px 8px; border-radius:20px;">
              {{ $area->customers_count ?? 0 }} customers
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-sm" title="Edit" style="background:rgba(105,108,255,0.12); color:#2563eb; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0;">
                <i class='bx bx-edit'></i>
              </a>
              <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="m-0" data-confirm="Delete {{ $area->name }}?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm" title="Delete" style="background:rgba(255,61,0,0.12); color:#ff3d00; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0; border:none;">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5">
            <div class="text-center py-5" style="color:#64748b;">
              <i class='bx bx-map-pin fs-1 d-block mb-2'></i>
              <div style="font-size:0.9375rem; font-weight:500; color:#1e293b;">No areas yet</div>
              <a href="{{ route('admin.areas.create') }}" class="btn btn-primary btn-sm mt-3">
                <i class='bx bx-plus me-1'></i> Add Area
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(function() {
    $('#areas-table').DataTable({
      dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-buttons"B>f>rt<"d-flex justify-content-between align-items-center mt-3"lip>',
      buttons: [{
          extend: 'excel',
          className: 'btn btn-sm btn-outline-primary',
          text: '<i class="bx bx-spreadsheet me-1"></i>Excel'
        },
        {
          extend: 'pdf',
          className: 'btn btn-sm btn-outline-danger',
          text: '<i class="bx bx-file-blank me-1"></i>PDF'
        }
      ],
      pageLength: 25,
      order: [
        [1, 'asc']
      ],
      language: {
        search: '',
        searchPlaceholder: 'Search areas...',
        lengthMenu: 'Show _MENU_',
        info: '_START_-_END_ of _TOTAL_',
        paginate: {
          previous: '&lsaquo;',
          next: '&rsaquo;'
        }
      },
      columnDefs: [{
        orderable: false,
        targets: [4]
      }]
    });
  });
</script>
@endsection