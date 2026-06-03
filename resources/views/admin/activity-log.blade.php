@extends('layouts.app')
@section('title', 'Log Aktivitas')

@section('content')

<a href="{{ route('admin.dashboard') }}" class="btn-back mb-3">
    <i class='bx bx-arrow-back'></i> Kembali ke Dasbor
</a>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0 fw-bold"><i class='bx bx-history me-2' style="color:var(--blue);"></i>Log Aktivitas</h5>
            <span class="ms-2" style="font-size:.8rem;color:var(--txt-3);">{{ $logs->total() }} entries</span>
        </div>

        <div class="table-responsive">
            <table class="table table-flat mb-0">
                <thead>
                    <tr>
                        <th style="width:160px;">Waktu</th>
                        <th style="width:120px;">Pengguna</th>
                        <th style="width:100px;">Aksi</th>
                        <th>Deskripsi</th>
                        <th style="width:120px;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="font-size:.8rem;white-space:nowrap;color:var(--txt-2);">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </td>
                        <td>
                            <span style="font-size:.8rem;font-weight:500;">{{ $log->user->name ?? 'System' }}</span>
                        </td>
                        <td>
                            @php
                                $actionColor = match($log->action) {
                                    'created' => 'green',
                                    'updated' => 'blue',
                                    'deleted' => 'red',
                                    'synced'  => 'orange',
                                    'login'   => 'cyan',
                                    default   => 'gray',
                                };
                            @endphp
                            <span class="badge-status" style="background:rgba(var(--{{ $actionColor }}-rgb, 100,100,100),.1);color:var(--{{ $actionColor }}, #64748b);font-size:.7rem;">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td style="font-size:.8rem;">{{ $log->description }}</td>
                        <td style="font-size:.75rem;color:var(--txt-3);">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4" style="color:var(--txt-3);">
                            <i class='bx bx-notepad d-block mb-1' style="font-size:2rem;"></i>
                            Belum ada aktivitas yang tercatat
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
