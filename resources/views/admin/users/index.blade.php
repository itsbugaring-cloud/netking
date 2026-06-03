@extends('layouts.app')
@section('title', 'Pengguna Admin')

@section('content')
<div class="ms-page nk-list-page users-index-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-group'></i> Kontrol Akses</div>
      <h1 class="ms-page-title">Pengguna</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.users.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Tambah Pengguna
      </a>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show"><i class='bx bx-check me-1'></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show"><i class='bx bx-x me-1'></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  <div class="ms-panel">
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="user-search" class="nk-search-input" placeholder="Cari pengguna...">
        </div>
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:.78rem;color:var(--txt-3);">Tampilkan</span>
          <select id="user-length" class="nk-length-select">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="users-table" style="min-width:1020px;">
          <thead>
            <tr>
              <th style="min-width:80px;">#</th>
              <th style="min-width:220px;">Nama</th>
              <th style="min-width:260px;">Email</th>
              <th style="min-width:180px;">Telegram</th>
              <th style="min-width:120px;">Peran</th>
              <th style="min-width:190px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $user)
            <tr>
              <td style="color:var(--txt-3);">{{ $user->id }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-sm" style="background:color-mix(in srgb,var(--blue) 10%,var(--surface));color:var(--blue);">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                  <div>
                    <div style="font-weight:600;">{{ $user->name }}</div>
                    @if($user->id === auth()->id())
                    <div style="font-size:.72rem;color:var(--blue);">Akun saat ini</div>
                    @endif
                  </div>
                </div>
              </td>
              <td style="color:var(--txt-3);">{{ $user->email }}</td>
              <td style="color:var(--txt-3);">{{ $user->telegram_username ? '@' . $user->telegram_username : '-' }}</td>
              <td>
                @if($user->role === 'admin')
                <span class="badge-status badge-inactive">Admin</span>
                @else
                <span class="badge-status badge-active">{{ ucfirst($user->role) }}</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.users.edit', $user) }}" class="nk-action-btn edit" title="Ubah">
                    <i class='bx bx-edit'></i>
                  </a>
                  @if(auth()->user()->role === 'admin')
                  <a
                    href="{{ route('admin.users.edit', $user) }}#reset-password-panel"
                    class="nk-action-btn"
                    title="Reset Password"
                    style="border:none;background:#fff7ed;color:#c2410c;"
                  >
                    <i class='bx bx-key'></i>
                  </a>
                  @endif
                  @if($user->id !== auth()->id())
                  <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline m-0" data-confirm="Hapus pengguna {{ addslashes($user->name) }}?">
                    @csrf @method('DELETE')
                    <button class="nk-action-btn delete" title="Hapus"><i class='bx bx-trash'></i></button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <div class="empty-state-icon"><i class='bx bx-group'></i></div>
                  <div class="empty-state-title">Tidak ada pengguna ditemukan</div>
                  <div class="empty-state-desc">Akun akses admin dan mitra akan terdaftar di sini.</div>
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
    var table = $('#users-table').DataTable({
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
    $('#user-search').on('input', function() { table.search(this.value).draw(); });
    $('#user-length').on('change', function() { table.page.len(+this.value).draw(); });
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });

  });
</script>
@endsection
