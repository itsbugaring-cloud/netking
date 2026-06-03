@extends('layouts.app')
@section('title', 'Customers')

@section('content')

<div class="page-title-box d-flex align-items-center justify-content-between">
  <div>
    <h4>Customers</h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Customers</li>
      </ol>
    </nav>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('admin.customers.index', ['export' => 'csv']) }}" class="btn btn-outline-primary btn-sm">
      <i class='bx bx-download me-1'></i> Export CSV
    </a>
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
      <i class='bx bx-plus me-1'></i> Add Customer
    </a>
  </div>
</div>

<!-- Bulk Action Bar (hidden by default) -->
<div class="card mb-3" id="bulk-bar" style="display:none;">
  <div class="card-body d-flex align-items-center justify-content-between py-2 px-3">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-status badge-provisioning" style="font-size:.75rem;" id="bulk-count">0 selected</span>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
        <i class='bx bx-trash me-1'></i> Delete Selected
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkClear()">Cancel</button>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title">All Customers</h5>
    <div class="d-flex gap-2 flex-wrap">
      @if($areas->isNotEmpty())
      <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex gap-2">
        <select name="area_id" class="form-select form-select-sm no-select2" style="min-width:160px;" onchange="this.form.submit()">
          <option value="">All Areas</option>
          @foreach($areas as $area)
          <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
          @endforeach
        </select>
        <select name="status" class="form-select form-select-sm no-select2" style="min-width:120px;" onchange="this.form.submit()">
          <option value="">All Status</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
          <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
          <option value="provisioning" {{ request('status') == 'provisioning' ? 'selected' : '' }}>Provisioning</option>
          <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
        @if(request('area_id') || request('status'))
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        @endif
      </form>
      @endif
    </div>
  </div>

  <div class="table-responsive">
    <table class="table" id="customers-table">
      <thead>
        <tr>
          <th style="width:40px;"><input type="checkbox" id="select-all" style="accent-color:#2563eb;"></th>
          <th>Customer</th>
          <th>PPPoE User</th>
          <th>Partner / Area</th>
          <th>Package</th>
          <th>Status</th>
          <th>Joined</th>
          <th style="width:100px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($customers as $customer)
        <tr>
          <td><input type="checkbox" class="row-check" value="{{ $customer->id }}" style="accent-color:#2563eb;"></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-sm" style="background: hsl({{ crc32($customer->name) % 360 }}, 55%, 60%); font-size:0.75rem;">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
              </div>
              <div>
                <div style="font-weight:500; color:#1e293b;">{{ $customer->name }}</div>
                <div style="font-size:0.75rem; color:#64748b;">{{ $customer->phone }}</div>
              </div>
            </div>
          </td>
          <td><code style="background:#f5f5f9; padding:2px 6px; border-radius:4px; font-size:0.8125rem; color:#2563eb;">{{ $customer->pppoe_user }}</code></td>
          <td>
            <div style="font-size:0.875rem; color:#1e293b;">{{ $customer->partner->name ?? '-' }}</div>
            <div style="font-size:0.75rem; color:#64748b;">{{ $customer->area->name ?? '-' }}</div>
          </td>
          <td>
            @if($customer->package)
            <div style="font-size:0.8125rem; font-weight:600; color:#1e293b;">{{ $customer->package->name }}</div>
            <div style="font-size:0.75rem; color:#64748b;">{{ $customer->package->speed_label }} · Rp {{ number_format($customer->package->price, 0, ',', '.') }}</div>
            @else
            <span style="color:#94a3b8; font-size:0.8125rem;">No package</span>
            @endif
          </td>
          <td>
            @if($customer->status === 'active')
            <span class="badge-status badge-active">Active</span>
            @elseif($customer->status === 'pending')
            <span class="badge-status badge-pending">Pending</span>
            @else
            <span class="badge-status badge-inactive">Inactive</span>
            @endif
          </td>
          <td style="font-size:0.8125rem; color:#64748b;">{{ $customer->created_at->format('d M Y') }}</td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm" title="View" style="background:rgba(3,195,236,0.12); color:#03c3ec; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0;">
                <i class='bx bx-show'></i>
              </a>
              <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm" title="Edit" style="background:rgba(105,108,255,0.12); color:#2563eb; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0;">
                <i class='bx bx-edit'></i>
              </a>
              <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="m-0" data-confirm="Delete {{ $customer->name }}?">
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
          <td colspan="8">
            <div class="empty-state">
              <div class="empty-state-icon"><i class='bx bx-group'></i></div>
              <div class="empty-state-title">Belum ada pelanggan</div>
              <div class="empty-state-desc">Mulai tambahkan pelanggan pertama Anda</div>
              <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
                <i class='bx bx-plus me-1'></i> Tambah Customer
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($customers->hasPages())
  <div class="card-footer" style="border-top: 1px solid #dbdade; padding: 0.75rem 1.5rem;">
    {{ $customers->links() }}
  </div>
  @endif
</div>

@endsection

@section('scripts')
<script>
  $(function() {
    var table = $('#customers-table').DataTable({
      dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>><rt<"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 20,
      order: [
        [6, 'desc']
      ],
      language: {
        search: '',
        searchPlaceholder: 'Search customers...',
        lengthMenu: 'Show _MENU_',
        info: 'Showing _START_-_END_ of _TOTAL_',
        paginate: {
          previous: '‹',
          next: '›'
        }
      },
      columnDefs: [{
        orderable: false,
        targets: [0, 7]
      }]
    });

    // Select All checkbox
    $('#select-all').on('change', function() {
      var checked = this.checked;
      table.rows({
        search: 'applied'
      }).nodes().each(function(row) {
        $(row).find('.row-check').prop('checked', checked);
      });
      updateBulkBar();
    });

    // Individual checkbox
    $(document).on('change', '.row-check', function() {
      updateBulkBar();
    });
  });

  function updateBulkBar() {
    var count = $('.row-check:checked').length;
    if (count > 0) {
      $('#bulk-bar').slideDown(200);
      $('#bulk-count').text(count + ' selected');
    } else {
      $('#bulk-bar').slideUp(200);
    }
  }

  function bulkClear() {
    $('#select-all').prop('checked', false);
    $('.row-check').prop('checked', false);
    updateBulkBar();
  }

  function bulkDelete() {
    var ids = [];
    $('.row-check:checked').each(function() {
      ids.push($(this).val());
    });
    if (!ids.length) return;
    Swal.fire({
      title: 'Delete ' + ids.length + ' customers?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'Yes, delete all!',
      cancelButtonText: 'Cancel'
    }).then(function(result) {
      if (result.isConfirmed) {
        fetch('{{ route("admin.customers.bulkDelete") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({
              ids: ids
            })
          })
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (data.success) {
              toastr.success(data.message || 'Deleted successfully');
              setTimeout(function() {
                location.reload();
              }, 800);
            } else {
              toastr.error(data.message || 'Failed to delete');
            }
          })
          .catch(function() {
            toastr.error('Network error');
          });
      }
    });
  }
</script>
@endsection