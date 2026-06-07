@extends('layouts.app')

@section('title', 'Router Backups')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">Router Backups</h2>
                <div class="text-muted mt-1">Backup dan restore konfigurasi router MikroTik</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        {{-- Area Selector --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.backups.index') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Pilih Area / Router</label>
                        <select name="area_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ ($selectedArea && $selectedArea->id == $area->id) ? 'selected' : '' }}>
                                    {{ $area->name }} ({{ $area->router_ip }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($selectedArea)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#backupModal">
                            <i class="ti ti-download"></i> Buat Backup
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        @if($selectedArea)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Backup — {{ $selectedArea->name }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Tipe</th>
                            <th>Ukuran</th>
                            <th>Tanggal</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                        <tr>
                            <td><code>{{ $backup->filename }}</code></td>
                            <td>
                                <span class="badge {{ $backup->type === 'binary' ? 'bg-purple-lt' : 'bg-green-lt' }}">
                                    {{ $backup->type }}
                                </span>
                            </td>
                            <td>{{ $backup->size_formatted }}</td>
                            <td>{{ $backup->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $backup->notes ?? '-' }}</td>
                            <td>
                                @if($backup->type === 'text')
                                <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="ti ti-download"></i>
                                </a>
                                @endif
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" class="d-inline"
                                    onsubmit="return confirm('Hapus backup {{ $backup->filename }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada backup untuk area ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($backups instanceof \Illuminate\Pagination\LengthAwarePaginator && $backups->hasPages())
            <div class="card-footer">
                {{ $backups->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Backup Type Modal --}}
@if($selectedArea)
<div class="modal modal-blur fade" id="backupModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Pilih tipe backup untuk router <strong>{{ $selectedArea->name }}</strong>:</p>
                    <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                        <label class="form-selectgroup-item flex-fill">
                            <input type="radio" name="type" value="text" class="form-selectgroup-input" checked>
                            <div class="form-selectgroup-label d-flex align-items-center p-3">
                                <div class="me-3"><i class="ti ti-file-text ti-lg"></i></div>
                                <div>
                                    <strong>Text Export (.rsc)</strong>
                                    <div class="text-muted small">Script text, bisa di-download & dibaca</div>
                                </div>
                            </div>
                        </label>
                        <label class="form-selectgroup-item flex-fill">
                            <input type="radio" name="type" value="binary" class="form-selectgroup-input">
                            <div class="form-selectgroup-label d-flex align-items-center p-3">
                                <div class="me-3"><i class="ti ti-file-zip ti-lg"></i></div>
                                <div>
                                    <strong>Binary Backup (.backup)</strong>
                                    <div class="text-muted small">Full restore, tersimpan di router</div>
                                </div>
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
@endif
@endsection
