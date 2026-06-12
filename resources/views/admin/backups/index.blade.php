@extends('layouts.app')
@section('title', 'Router Backups')

@section('styles')
<style>
    .router-kanban { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .75rem; }
    .router-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: .9rem 1rem; display: flex; flex-direction: column; gap: .45rem; cursor: pointer; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    .router-card:hover { border-color: color-mix(in srgb, var(--blue) 45%, var(--border)); box-shadow: 0 4px 16px rgba(0,0,0,.08); text-decoration: none; }
    .router-card--active { border-color: var(--blue) !important; background: color-mix(in srgb, var(--blue) 6%, var(--surface)); box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 18%, transparent); }
    .router-card-name { font-size: .875rem; font-weight: 700; color: var(--txt); display: flex; align-items: center; gap: .4rem; }
    .router-card-ip { font-size: .7rem; font-family: monospace; background: color-mix(in srgb, var(--orange) 10%, var(--surface-2)); color: var(--orange); padding: .12rem .45rem; border-radius: 5px; border: 1px solid color-mix(in srgb, var(--orange) 20%, var(--border)); display: inline-block; width: fit-content; }
    .router-card-active-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--blue); flex-shrink: 0; }
    .backup-table th { font-size: .75rem; text-transform: uppercase; color: var(--txt-3); font-weight: 600; }
    .backup-table td { font-size: .8125rem; vertical-align: middle; }
    .backup-badge-binary { display: inline-flex; align-items: center; gap: .2rem; font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; font-weight: 600; background: color-mix(in srgb, var(--purple, #9b59b6) 12%, var(--surface)); color: var(--purple, #9b59b6); border: 1px solid color-mix(in srgb, var(--purple, #9b59b6) 25%, var(--border)); }
    .backup-badge-text { display: inline-flex; align-items: center; gap: .2rem; font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; font-weight: 600; background: color-mix(in srgb, var(--green) 12%, var(--surface)); color: var(--green); border: 1px solid color-mix(in srgb, var(--green) 25%, var(--border)); }

    /* Fix Modal z-index bug */
    #backupModal { z-index: 1060 !important; }
    #backupModal .modal-dialog { pointer-events: auto; }
    .modal-backdrop { z-index: 1055 !important; }
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-data'></i> MikroTik</div>
            <h1 class="ms-page-title">Router Backups</h1>
        </div>
        @if($selectedArea)
        <div class="ms-page-actions">
            <button type="button" class="ms-btn" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('backupModal')).show()">
                <i class='bx bx-download'></i> Buat Backup
            </button>
        </div>
        @endif
    </div>

    {{-- Router / Area Selector --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Pilih Router / Area</h5>
                <div class="ms-panel-subtitle">Klik kartu area untuk melihat riwayat backup</div>
            </div>
        </div>
        <div class="ms-panel-body">
            <div class="router-kanban">
                @foreach($areas as $area)
                @php $isActive = $selectedArea?->id == $area->id; @endphp
                <a href="{{ route('admin.backups.index', ['area_id' => $area->id]) }}"
                   class="router-card {{ $isActive ? 'router-card--active' : '' }}">
                    <div class="router-card-name">
                        @if($isActive)
                            <div class="router-card-active-dot"></div>
                        @else
                            <i class='bx bx-router' style="color:var(--txt-3);font-size:.95rem;flex-shrink:0;"></i>
                        @endif
                        {{ $area->name }}
                    </div>
                    <div class="router-card-ip">{{ $area->router_ip }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Backup History --}}
    @if($selectedArea)
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Riwayat Backup — {{ $selectedArea->name }}</h5>
                <div class="ms-panel-subtitle">Backup dan restore konfigurasi router MikroTik</div>
            </div>
        </div>
        <div class="ms-panel-body p-0">
            @if(count($backups) === 0)
            <div style="text-align:center;padding:3rem;color:var(--txt-3);">
                <i class='bx bx-data' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="mb-0 fw-semibold">Belum ada backup untuk area ini</p>
                <p class="mb-0 mt-1" style="font-size:.8rem;">Klik "Buat Backup" untuk membuat backup baru.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover backup-table mb-0">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Tipe</th>
                            <th>Ukuran</th>
                            <th>Tanggal</th>
                            <th>Catatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                        <tr>
                            <td><code style="font-size:.75rem;">{{ $backup->filename }}</code></td>
                            <td>
                                @if($backup->type === 'binary')
                                <span class="backup-badge-binary">binary</span>
                                @else
                                <span class="backup-badge-text">text</span>
                                @endif
                            </td>
                            <td>{{ $backup->size_formatted }}</td>
                            <td>{{ $backup->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $backup->notes ?? '-' }}</td>
                            <td class="text-end">
                                @if($backup->type === 'text')
                                <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-sm btn-outline-primary me-1" title="Download">
                                    <i class='bx bx-download'></i>
                                </a>
                                @endif
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" class="d-inline"
                                    onsubmit="return confirm('Hapus backup {{ $backup->filename }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class='bx bx-trash'></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="color:var(--txt-3);">Belum ada backup untuk area ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @if($backups instanceof \Illuminate\Pagination\LengthAwarePaginator && $backups->hasPages())
    <div style="display:flex;justify-content:center;padding:1rem 0;">
        {{ $backups->links() }}
    </div>
    @endif
    @endif
</div>
@endsection

{{-- Backup Type Modal: OUTSIDE @section('content') so it renders at <body> level, bypassing workspace-shell overflow:hidden --}}
@if($selectedArea)
@push('modals')
<div class="modal fade" id="backupModal" tabindex="-1" style="z-index:99999;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form method="POST" action="{{ route('admin.backups.store') }}" onsubmit="var btn=this.querySelector('button[type=submit]'); setTimeout(() => { btn.disabled=true; btn.innerHTML='<i class=\'bx bx-loader-alt bx-spin\'></i> Memproses...'; }, 0);">
            @csrf
            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="color:var(--txt-3);margin-bottom:.75rem;">Pilih tipe backup untuk router <strong>{{ $selectedArea->name }}</strong>:</p>
                    <div class="d-flex flex-column gap-3">
                        <label class="form-check p-3 rounded" style="border: 1px solid var(--border); cursor: pointer; display: flex; align-items: center; gap: 1rem; margin: 0;">
                            <input type="radio" name="type" value="text" class="form-check-input mt-0" checked style="width: 1.25rem; height: 1.25rem;">
                            <div>
                                <div style="font-weight: 600; color: var(--txt);"><i class='bx bx-file'></i> Text Export (.rsc)</div>
                                <div style="font-size: .78rem; color: var(--txt-3);">Script text, bisa di-download & dibaca</div>
                            </div>
                        </label>
                        <label class="form-check p-3 rounded" style="border: 1px solid var(--border); cursor: pointer; display: flex; align-items: center; gap: 1rem; margin: 0;">
                            <input type="radio" name="type" value="binary" class="form-check-input mt-0" style="width: 1.25rem; height: 1.25rem;">
                            <div>
                                <div style="font-weight: 600; color: var(--txt);"><i class='bx bx-archive'></i> Binary Backup (.backup)</div>
                                <div style="font-size: .78rem; color: var(--txt-3);">Full restore, tersimpan di router</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat Backup</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endpush
@endif
