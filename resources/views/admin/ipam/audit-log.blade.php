@extends('layouts.app')
@section('title', 'IPAM Audit Log')

@section('content')
<div class="ms-page nk-list-page ipam-audit-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-history'></i> IPAM</div>
      <h1 class="ms-page-title">IPAM Audit Log</h1>
    </div>
  </div>

  @if (session('success'))
  <div class="alert alert-success mb-3" style="border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
  </div>
  @endif
  @if (session('error'))
  <div class="alert mb-3" style="background:color-mix(in srgb,var(--red) 10%,var(--surface));border:1px solid color-mix(in srgb,var(--red) 25%,var(--border));color:var(--red);border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
  </div>
  @endif

  <div class="ms-panel">
    <div class="ms-panel-head d-flex align-items-center justify-content-between">
      <span class="ms-panel-title"><i class='bx bx-history me-2'></i>Log Aktivitas</span>
      <span style="font-size:.78rem;color:var(--txt-3);">{{ $logs->total() }} entri</span>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th style="min-width:150px;">Waktu</th>
              <th style="min-width:120px;">Actor</th>
              <th style="min-width:100px;">Action</th>
              <th style="min-width:120px;">Target</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            <tr>
              <td style="font-size:.8rem;color:var(--txt-3);">
                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') : '-' }}
              </td>
              <td><span style="font-weight:500;">{{ $log->actor }}</span></td>
              <td>
                @php
                  $actionColors = [
                    'create' => 'var(--green)',
                    'update' => 'var(--blue)',
                    'delete' => 'var(--red)',
                    'scan' => 'var(--orange, #f97316)',
                    'import' => '#a855f7',
                    'map' => 'var(--blue)',
                    'auto_map' => 'var(--blue)',
                  ];
                  $color = $actionColors[$log->action] ?? 'var(--txt-3)';
                @endphp
                <span style="background:color-mix(in srgb,{{ $color }} 12%,var(--surface));color:{{ $color }};font-size:.7rem;font-weight:600;padding:2px 8px;border-radius:4px;">
                  {{ $log->action }}
                </span>
              </td>
              <td style="font-size:.8rem;">
                {{ $log->target_type }}{{ $log->target_id ? " #{$log->target_id}" : '' }}
              </td>
              <td style="font-size:.8rem;color:var(--txt-3);">{{ $log->detail }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="5">
                <div class="text-center py-5" style="color:var(--txt-3);">
                  <i class='bx bx-history fs-1 d-block mb-2'></i>
                  <div style="font-size:.9375rem;font-weight:500;">Belum ada log aktivitas</div>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($logs->hasPages())
    <div class="p-3 d-flex justify-content-center">
      {{ $logs->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
