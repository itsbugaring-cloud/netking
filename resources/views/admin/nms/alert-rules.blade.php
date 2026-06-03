@extends('layouts.app')
@section('title', 'NMS Alert Rules')

@section('styles')
<style>
  .alert-rules-page .icon-wrap.bg-danger-subtle,
  .alert-rules-page .bg-danger-subtle {
    background: color-mix(in srgb, var(--nk-danger) 12%, var(--surface)) !important;
    color: color-mix(in srgb, var(--nk-danger) 82%, var(--txt)) !important;
  }

  .alert-rules-page .icon-wrap.bg-warning-subtle,
  .alert-rules-page .bg-warning-subtle {
    background: color-mix(in srgb, var(--nk-warning) 12%, var(--surface)) !important;
    color: color-mix(in srgb, var(--nk-warning) 82%, var(--txt)) !important;
  }

  .alert-rules-page .text-dark {
    color: var(--txt) !important;
  }

  .alert-rules-page .text-muted {
    color: var(--txt-3) !important;
  }

  .alert-rules-page [style*="background:var(--bg-lighter)"] {
    background: var(--surface-2) !important;
    border-color: var(--border) !important;
  }

  .alert-rules-page .list-group-item {
    background: transparent !important;
    color: var(--txt) !important;
  }
</style>
@endsection

@section('content')
<div class="ms-page alert-rules-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-bell'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Aturan Peringatan</h1>
        </div>
    </div>

<div class="row g-3">
    <!-- Active Alerts: Offline Devices -->
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title mb-0 d-flex align-items-center">
                    <div class="icon-wrap bg-danger-subtle text-danger me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-error-circle'></i>
                    </div>
                    Peringatan Aktif (ONT Offline)
                </span>
                <span class="ms-chip">{{ $offlineOnts->count() }}</span>
            </div>
            <div class="ms-panel-body p-0">
                @if($offlineOnts->isEmpty())
                <div class="text-center py-4 text-muted mx-4 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
          <i class='bx bx-check-circle text-success fs-1 mb-2'></i>
                    <p class="mb-0 fw-medium">Sistem sehat. Tidak ada perangkat offline.</p>
                </div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($offlineOnts as $ont)
                    <div class="list-group-item border-0 py-3 px-4" style="border-bottom:1px solid var(--border-color) !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">
                                    @if($ont->customer_id && $ont->customer)
                                      <a href="{{ route('admin.customers.show', $ont->customer_id) }}" class="text-dark text-decoration-none">
                                          {{ $ont->customer->name }}
                                      </a>
                                    @else
                                      <span class="text-dark">ONT Belum Ditetapkan</span>
                                    @endif
                                </h6>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    <i class='bx bx-server pe-1'></i>{{ $ont->olt->name ?? 'OLT Tidak Diketahui' }} / PON {{ $ont->pon_port }}
                                </div>
                                <div class="mt-1">
                                    <code class="bg-light px-2 py-1 rounded text-dark border shadow-sm" style="font-size:0.75rem;">{{ $ont->sn ?: $ont->mac_address }}</code>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold shadow-sm mb-2" style="font-size:0.7rem;">
                                    Offline
                                </span>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    {{ $ont->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent System Events -->
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title mb-0 d-flex align-items-center">
                    <div class="icon-wrap bg-warning-subtle text-warning me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-history'></i>
                    </div>
                    Kejadian Sistem Terbaru
                </span>
                <a href="{{ route('admin.nms.syslog') }}" class="ms-btn-secondary">Lihat Semua</a>
            </div>
            <div class="ms-panel-body p-0">
                @if($alerts->isEmpty())
                <div class="text-center py-4 text-muted mx-4 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
                    <i class='bx bx-info-circle fs-2 mb-2'></i>
                    <p class="mb-0 fw-medium">Tidak ada kejadian peringatan terbaru yang tercatat.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            @foreach($alerts as $alert)
                            @php
                                $icon = 'bx-info-circle';
                                $color = 'primary';
                                if(str_contains($alert->action, 'failed') || str_contains($alert->action, 'deleted')) { $icon = 'bx-error'; $color = 'danger'; }
                                elseif(str_contains($alert->action, 'suspended')) { $icon = 'bx-pause-circle'; $color = 'warning'; }
                                elseif(str_contains($alert->action, 'provisioned')) { $icon = 'bx-check-circle'; $color = 'success'; }
                            @endphp
                            <tr>
                                <td class="ps-4" style="width:50px;">
                                    <div class="bg-{{ $color }}-subtle text-{{ $color }} rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:32px;height:32px;">
                                        <i class="bx {{ $icon }} fs-6"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size:0.85rem;">{{ $alert->description }}</div>
                                    <div class="text-muted mt-1 d-flex align-items-center gap-2" style="font-size:0.75rem;">
                                        <span><i class='bx bx-user pe-1'></i>{{ $alert->user->name ?? 'Sistem' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $alert->subject_type ? basename(str_replace('\\', '/', $alert->subject_type)).' #'.$alert->subject_id : '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4 text-muted fw-medium" style="font-size:0.75rem;white-space:nowrap;">
                                    {{ $alert->created_at->format('M d, H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

@endsection
