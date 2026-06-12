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



  <div class="ms-panel">
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="user-search" class="nk-search-input" placeholder="Cari pengguna...">
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
                <div class="dropdown">
                  <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                    Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}"><i class='bx bx-edit'></i> Edit Pengguna</a></li>
                    @if(auth()->user()->role === 'admin')
                    <li><a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}#reset-password-panel"><i class='bx bx-key' style="color:#c2410c;"></i> Reset Password</a></li>
                    @endif
                    @if($user->id !== auth()->id())
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="m-0" data-confirm="Hapus pengguna {{ addslashes($user->name) }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class='bx bx-trash' style="color:var(--red);"></i> Hapus</button>
                      </form>
                    </li>
                    @endif
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <div class="empty-state-icon"><i class='bx bx-group'></i></div>
                  <div class="empty-state-title">Tidak ada pengguna ditemukan</div>
                  <div class="empty-state-desc">Akun akses admin dan PIC akan terdaftar di sini.</div>
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
      dom: 'rt<"d-flex justify-content-between align-items-center mt-3"ilp>',
      pageLength: 25,
      autoWidth: false,
      scrollX: true,
      order: [[0, 'asc']],
      language: {
        info: 'Menampilkan <b>_START_</b> hingga <b>_END_</b> dari <b>_TOTAL_</b> hasil',
        lengthMenu: '_MENU_ per hal',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [5] }]
    });
    $('#user-search').on('input', function() { table.search(this.value).draw(); });
    
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });

  });
</script>
@endsection
