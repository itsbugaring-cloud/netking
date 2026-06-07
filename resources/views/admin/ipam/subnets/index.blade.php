@extends('layouts.app')
@section('title', 'Subnet Management')

@section('content')
<div class="ms-page nk-list-page ipam-subnets-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-sitemap'></i> IPAM</div>
      <h1 class="ms-page-title">Subnet Management</h1>
    </div>
    <div class="ms-page-actions">
      <button type="button" class="ms-btn" onclick="document.getElementById('add-subnet-section').classList.toggle('d-none')">
        <i class='bx bx-plus'></i> Tambah Subnet
      </button>
    </div>
  </div>

  {{-- Add Subnet Form --}}
  <div id="add-subnet-section" class="ms-panel mb-3 d-none">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-plus-circle me-2'></i>Tambah Subnet Baru</h5>
    </div>
    <div class="p-3">
      <form action="{{ route('admin.ipam.subnets.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem;">Network Address</label>
            <input type="text" name="network_address" class="form-control form-control-sm" placeholder="192.168.1.0" required value="{{ old('network_address') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label" style="font-size:.8rem;">Prefix Length</label>
            <input type="number" name="prefix_length" class="form-control form-control-sm" placeholder="24" min="1" max="32" required value="{{ old('prefix_length') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem;">Nama</label>
            <input type="text" name="name" class="form-control form-control-sm" placeholder="LAN Office" value="{{ old('name') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">Deskripsi</label>
            <input type="text" name="description" class="form-control form-control-sm" placeholder="Deskripsi subnet" value="{{ old('description') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label" style="font-size:.8rem;">VLAN ID</label>
            <input type="text" name="vlan_id" class="form-control form-control-sm" placeholder="100" value="{{ old('vlan_id') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem;">Lokasi</label>
            <input type="text" name="location" class="form-control form-control-sm" placeholder="Gedung A" value="{{ old('location') }}">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="ms-btn w-100">
              <i class='bx bx-plus'></i> Tambah
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Subnet Table --}}
  <div class="ms-panel">
    <div class="ms-panel-head d-flex align-items-center justify-content-between">
      <span class="ms-panel-title"><i class='bx bx-sitemap me-2'></i>Daftar Subnet</span>
    </div>
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="subnets-search" class="nk-search-input" placeholder="Cari subnet...">
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="subnets-table">
          <thead>
            <tr>
              <th style="min-width:160px;">Network</th>
              <th style="min-width:140px;">Nama</th>
              <th style="min-width:120px;">Lokasi</th>
              <th style="min-width:80px;">VLAN</th>
              <th style="min-width:200px;">Utilization</th>
              <th style="width:120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($subnets as $item)
            @php
              $subnet = $item['subnet'];
              $percentage = $item['percentage'];
              $used = $item['used'];
              $total = $item['total'];
              $available = $item['available'];
              $barColor = $percentage > 80 ? 'var(--red)' : ($percentage > 50 ? 'var(--orange, #f97316)' : 'var(--green)');
            @endphp
            <tr>
              <td>
                <span style="font-weight:600;font-size:.85rem;">{{ $subnet->network_address }}/{{ $subnet->prefix_length }}</span>
              </td>
              <td>{{ $subnet->name ?? '-' }}</td>
              <td style="color:var(--txt-3);">{{ $subnet->location ?? '-' }}</td>
              <td>
                @if($subnet->vlan_id)
                <span style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);font-size:.7rem;font-weight:600;padding:2px 6px;border-radius:4px;">
                  {{ $subnet->vlan_id }}
                </span>
                @else
                  -
                @endif
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="flex:1;height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                    <div style="width:{{ $percentage }}%;height:100%;background:{{ $barColor }};border-radius:3px;"></div>
                  </div>
                  <span style="font-size:.7rem;font-weight:600;min-width:70px;color:var(--txt-3);">
                    {{ $used }}/{{ $total }} ({{ round($percentage) }}%)
                  </span>
                </div>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <button type="button" class="nk-action-btn edit" title="Edit"
                    onclick="editSubnet({{ $subnet->id }}, '{{ $subnet->network_address }}', {{ $subnet->prefix_length }}, '{{ addslashes($subnet->name ?? '') }}', '{{ addslashes($subnet->description ?? '') }}', '{{ addslashes($subnet->vlan_id ?? '') }}', '{{ addslashes($subnet->location ?? '') }}')">
                    <i class='bx bx-edit'></i>
                  </button>
                  <form action="{{ route('admin.ipam.subnets.destroy', $subnet) }}" method="POST" class="m-0" data-confirm="Hapus subnet {{ $subnet->network_address }}/{{ $subnet->prefix_length }}?">
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
              <td colspan="6">
                <div class="text-center py-5" style="color:var(--txt-3);">
                  <i class='bx bx-sitemap fs-1 d-block mb-2'></i>
                  <div style="font-size:.9375rem;font-weight:500;">Belum ada subnet</div>
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

{{-- Edit Modal --}}
<div class="modal fade" id="editSubnetModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editSubnetForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Subnet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-8">
              <label class="form-label">Network Address</label>
              <input type="text" name="network_address" id="edit-subnet-network" class="form-control" required>
            </div>
            <div class="col-4">
              <label class="form-label">Prefix</label>
              <input type="number" name="prefix_length" id="edit-subnet-prefix" class="form-control" min="1" max="32" required>
            </div>
            <div class="col-12">
              <label class="form-label">Nama</label>
              <input type="text" name="name" id="edit-subnet-name" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" id="edit-subnet-description" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">VLAN ID</label>
              <input type="text" name="vlan_id" id="edit-subnet-vlan" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">Lokasi</label>
              <input type="text" name="location" id="edit-subnet-location" class="form-control">
            </div>
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
    var table = $('#subnets-table').DataTable({
      dom: '<rt><"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 25,
      autoWidth: false,
      scrollX: true,
      order: [[0, 'asc']],
      language: {
        info: '_START_-_END_ dari _TOTAL_',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [5] }]
    });
    $('#subnets-search').on('input', function() { table.search(this.value).draw(); });
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });
  });

  function editSubnet(id, network, prefix, name, description, vlan, location) {
    document.getElementById('editSubnetForm').action = '/admin/ipam/subnets/' + id;
    document.getElementById('edit-subnet-network').value = network;
    document.getElementById('edit-subnet-prefix').value = prefix;
    document.getElementById('edit-subnet-name').value = name;
    document.getElementById('edit-subnet-description').value = description;
    document.getElementById('edit-subnet-vlan').value = vlan;
    document.getElementById('edit-subnet-location').value = location;
    new bootstrap.Modal(document.getElementById('editSubnetModal')).show();
  }
</script>
@endsection
