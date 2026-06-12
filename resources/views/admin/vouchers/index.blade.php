@extends('layouts.app')
@section('title', 'Voucher')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-purchase-tag'></i> Akses Prabayar</div>
      <h1 class="ms-page-title">Voucher</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.vouchers.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Buat Batch
      </a>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-barcode me-2' style="color:#2563eb;"></i>Batch Voucher</h5>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="vouchers-table" style="min-width:1100px;">
          <thead>
            <tr>
              <th style="min-width:80px;">#</th>
              <th style="min-width:260px;">Batch</th>
              <th style="min-width:110px;">Tipe</th>
              <th style="min-width:100px;">Durasi</th>
              <th style="min-width:140px;">Harga</th>
              <th style="min-width:170px;">Penggunaan</th>
              <th style="min-width:120px;">Dibuat</th>
              <th style="min-width:120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($batches as $batch)
            @php
              $pct = $batch->total > 0 ? round(($batch->used / $batch->total) * 100) : 0;
            @endphp
            <tr>
              <td>{{ $batch->id }}</td>
              <td>
                <div style="font-weight:600;color:#1e293b;">{{ $batch->name }}</div>
                <div style="font-size:.74rem;color:#64748b;">Prefix {{ $batch->prefix }} · Profil {{ $batch->profile }}</div>
              </td>
              <td>
                @if($batch->type === 'hotspot')
                <span class="badge-status badge-active">HOTSPOT</span>
                @else
                <span class="badge-status" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">PPPOE</span>
                @endif
              </td>
              <td>{{ $batch->duration_days }} hari</td>
              <td>Rp {{ number_format($batch->price, 0, ',', '.') }}</td>
              <td>
                <div class="d-flex align-items-center gap-2" style="min-width:140px;">
                  <div style="flex:1;height:6px;background:#f1f5f9;border-radius:999px;overflow:hidden;">
                    <div style="width:{{ $pct }}%;height:100%;background:#2563eb;border-radius:999px;"></div>
                  </div>
                  <span style="font-size:.73rem;color:#64748b;white-space:nowrap;">{{ $batch->used }}/{{ $batch->total }}</span>
                </div>
              </td>
              <td>{{ $batch->created_at->format('d M Y') }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                    Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('admin.vouchers.show', $batch) }}"><i class='bx bx-show'></i> Lihat Detail</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" action="{{ route('admin.vouchers.destroy', $batch) }}" class="m-0" data-confirm="Hapus batch dan semua vouchernya?">
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
                <div class="empty-state">
                  <div class="empty-state-icon"><i class='bx bx-purchase-tag'></i></div>
                  <div class="empty-state-title">Belum ada batch voucher</div>
                  <div class="empty-state-desc">Buat batch voucher hotspot atau PPPoE untuk akses prabayar.</div>
                  <a href="{{ route('admin.vouchers.create') }}" class="ms-btn">Buat Batch</a>
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
    $('#vouchers-table').DataTable({
      dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 25,
      order: [[6, 'desc']],
      language: {
        search: '',
        searchPlaceholder: 'Cari voucher...',
        lengthMenu: 'Tampilkan _MENU_',
        info: 'Menampilkan <b>_START_</b> hingga <b>_END_</b> dari <b>_TOTAL_</b> hasil',
        paginate: {
          previous: '&lsaquo;',
          next: '&rsaquo;'
        }
      },
      columnDefs: [{
        orderable: false,
        targets: [7]
      }]
    });
  });
</script>
@endsection
