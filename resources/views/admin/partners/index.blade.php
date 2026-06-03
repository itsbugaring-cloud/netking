@extends('layouts.app')
@section('title', 'Mitra')

@section('content')
<div class="ms-page nk-list-page partners-index-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-buildings'></i> Kepemilikan Pelanggan</div>
      <h1 class="ms-page-title">Mitra</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.partners.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Tambah Mitra
      </a>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-store me-2'></i>Direktori Mitra</h5>
    </div>
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="partner-search" class="nk-search-input" placeholder="Cari mitra...">
        </div>
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:.78rem;color:var(--txt-3);">Tampilkan</span>
          <select id="partner-length" class="nk-length-select">
            <option value="10">10</option>
            <option value="20" selected>20</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="partners-table" style="min-width:1240px;">
          <thead>
            <tr>
              <th style="min-width:250px;">Mitra</th>
              <th style="min-width:150px;">Kontak</th>
              <th style="min-width:180px;">Telegram</th>
              <th style="min-width:180px;">Area</th>
              <th style="min-width:110px;">Pelanggan</th>
              <th style="min-width:110px;">Komisi</th>
              <th style="min-width:130px;">Saldo Wallet</th>
              <th style="min-width:120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($partners as $partner)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-sm" style="background:hsl({{ crc32($partner->name) % 360 }},55%,60%);color:#fff;">
                    {{ strtoupper(substr($partner->name, 0, 1)) }}
                  </div>
                  <div>
                    <div style="font-weight:600;">{{ $partner->name }}</div>
                    <div style="font-size:.74rem;color:var(--txt-3);">{{ $partner->email }}</div>
                  </div>
                </div>
              </td>
              <td style="color:var(--txt-3);">{{ $partner->phone ?: '—' }}</td>
              <td style="color:var(--txt-3);">{{ $partner->telegram_username ? '@' . $partner->telegram_username : '—' }}</td>
              <td>{{ $partner->area->name ?? '—' }}</td>
              <td>
                <span class="badge-status badge-active">{{ $partner->customers_count ?? 0 }}</span>
              </td>
              <td style="font-weight:600;">{{ $partner->commission_rate ?? 0 }}%</td>
              <td style="font-weight:600;color:var(--green);">
                Rp {{ number_format($partner->wallet_balance ?? 0, 0, ',', '.') }}
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.partners.show', $partner) }}" class="nk-action-btn view" title="Lihat">
                    <i class='bx bx-show'></i>
                  </a>
                  <a href="{{ route('admin.partners.edit', $partner) }}" class="nk-action-btn edit" title="Ubah">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST" class="m-0" data-confirm="Hapus {{ $partner->name }}?">
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
              <td colspan="8">
                <div class="empty-state">
                  <div class="empty-state-icon"><i class='bx bx-buildings'></i></div>
                  <div class="empty-state-title">Belum ada mitra</div>
                  <div class="empty-state-desc">Mulai dengan membuat akun mitra pertama untuk alur kepemilikan area Anda.</div>
                  <a href="{{ route('admin.partners.create') }}" class="ms-btn">Tambah Mitra</a>
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
    var table = $('#partners-table').DataTable({
      dom: '<rt><"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 20,
      autoWidth: false,
      scrollX: true,
      order: [[0, 'asc']],
      language: {
        info: '_START_-_END_ dari _TOTAL_',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [7] }]
    });
    $('#partner-search').on('input', function() { table.search(this.value).draw(); });
    $('#partner-length').on('change', function() { table.page.len(+this.value).draw(); });
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });
  });
</script>
@endsection
