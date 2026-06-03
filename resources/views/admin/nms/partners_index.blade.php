@extends('layouts.app')
@section('title', 'Partners')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
  <div>
    <h4>Partners</h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Partners</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('admin.partners.create') }}" class="btn btn-primary btn-sm">
    <i class='bx bx-plus me-1'></i> Add Partner
  </a>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title"><i class='bx bx-store me-2' style="color:#2563eb;"></i>All Partners</h5>
  </div>
  <div class="table-responsive">
    <table class="table" id="partners-table">
      <thead>
        <tr>
          <th>Partner</th>
          <th>Contact</th>
          <th>Area</th>
          <th>Customers</th>
          <th>Commission</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($partners as $partner)
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-sm" style="background: hsl({{ crc32($partner->name) % 360 }}, 55%, 60%); font-size:0.75rem;">
                {{ strtoupper(substr($partner->name, 0, 1)) }}
              </div>
              <div>
                <div style="font-weight:500; color:#1e293b;">{{ $partner->name }}</div>
                <div style="font-size:0.75rem; color:#64748b;">{{ $partner->email }}</div>
              </div>
            </div>
          </td>
          <td style="font-size:0.875rem; color:#64748b;">{{ $partner->phone }}</td>
          <td style="font-size:0.875rem; color:#1e293b;">{{ $partner->area->name ?? '-' }}</td>
          <td>
            <span style="background:rgba(105,108,255,0.1); color:#2563eb; font-size:0.75rem; font-weight:600; padding:3px 8px; border-radius:20px;">
              {{ $partner->customers_count ?? 0 }}
            </span>
          </td>
          <td style="font-weight:600; color:#1e293b;">{{ $partner->commission_rate ?? 0 }}%</td>
          <td>
            @if($partner->status === 'active')
            <span class="badge-status badge-active">Active</span>
            @else
            <span class="badge-status badge-inactive">Inactive</span>
            @endif
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('admin.partners.show', $partner) }}" class="btn btn-sm" title="View" data-bs-toggle="tooltip"
                style="background:rgba(3,195,236,0.12); color:#03c3ec; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0;">
                <i class='bx bx-show'></i>
              </a>
              <a href="{{ route('admin.partners.edit', $partner) }}" class="btn btn-sm" title="Edit" data-bs-toggle="tooltip"
                style="background:rgba(105,108,255,0.12); color:#2563eb; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0;">
                <i class='bx bx-edit'></i>
              </a>
              <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST" class="m-0"
                data-confirm="Delete {{ $partner->name }}?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm" title="Delete" data-bs-toggle="tooltip"
                  style="background:rgba(255,61,0,0.12); color:#ff3d00; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0; border:none;">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7">
            <div class="text-center py-5" style="color:#64748b;">
              <i class='bx bx-store fs-1 d-block mb-2'></i>
              <div style="font-size:0.9375rem; font-weight:500; color:#1e293b;">No partners yet</div>
              <a href="{{ route('admin.partners.create') }}" class="btn btn-primary btn-sm mt-3">
                <i class='bx bx-plus me-1'></i> Add Partner
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
    $('#partners-table').DataTable({
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
        },
        {
          extend: 'csv',
          className: 'btn btn-sm btn-outline-secondary',
          text: '<i class="bx bx-data me-1"></i>CSV'
        },
        {
          extend: 'print',
          className: 'btn btn-sm btn-outline-dark',
          text: '<i class="bx bx-printer me-1"></i>Print'
        }
      ],
      pageLength: 20,
      order: [
        [0, 'asc']
      ],
      language: {
        search: '',
        searchPlaceholder: 'Search partners...',
        lengthMenu: 'Show _MENU_',
        info: '_START_-_END_ of _TOTAL_',
        paginate: {
          previous: '&lsaquo;',
          next: '&rsaquo;'
        }
      },
      columnDefs: [{
        orderable: false,
        targets: [6]
      }]
    });
  });
</script>
@endsection