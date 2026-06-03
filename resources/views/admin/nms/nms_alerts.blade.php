@extends('layouts.app')
@section('title', 'NMS Alert Rules')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-bell me-2' style="color:var(--orange);"></i>Alert & Event Rules</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nms.dashboard') }}">NMS</a></li>
                <li class="breadcrumb-item active">Alert Rules</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <!-- Active Alerts: Offline Devices -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm borderless">
            <div class="card-header bg-transparent pt-4 pb-2 border-bottom-0 d-flex justify-content-between align-items-center">
                <span class="card-title mb-0 d-flex align-items-center">
                    <div class="icon-wrap bg-danger-subtle text-danger me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-error-circle'></i>
                    </div>
                    Active Alerts (Offline ONTs)
                </span>
                <span class="badge bg-danger rounded-pill">{{ $offlineOnts->count() }}</span>
            </div>
            <div class="card-body px-0">
                @if($offlineOnts->isEmpty())
                <div class="text-center py-4 text-muted mx-4 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
                    <i class='bx bx-check-shield text-success fs-1 mb-2'></i>
                    <p class="mb-0 fw-medium">System is healthy. No offline devices.</p>
                </div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($offlineOnts as $ont)
                    <div class="list-group-item border-0 py-3 px-4" style="border-bottom:1px solid var(--border-color) !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">
                                    <a href="{{ route('admin.customers.show', $ont->customer_id ?? 0) }}" class="text-dark text-decoration-none">
                                        {{ $ont->customer->name ?? 'Unassigned ONT' }}
                                    </a>
                                </h6>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    <i class='bx bx-server pe-1'></i>{{ $ont->olt->name ?? 'Unknown OLT' }} / PON {{ $ont->pon_port }}
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
        <div class="card h-100 shadow-sm borderless">
            <div class="card-header bg-transparent pt-4 pb-2 border-bottom-0 d-flex justify-content-between align-items-center">
                <span class="card-title mb-0 d-flex align-items-center">
                    <div class="icon-wrap bg-warning-subtle text-warning me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-history'></i>
                    </div>
                    Recent System Events
                </span>
                <a href="{{ route('admin.nms.syslog') }}" class="btn btn-sm btn-light shadow-sm text-primary fw-semibold" style="font-size:0.75rem;">View All</a>
            </div>
            <div class="card-body p-0">
                @if($alerts->isEmpty())
                <div class="text-center py-4 text-muted mx-4 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
                    <i class='bx bx-info-circle fs-2 mb-2'></i>
                    <p class="mb-0 fw-medium">No recent alert events logged.</p>
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
                                        <span><i class='bx bx-user pe-1'></i>{{ $alert->user->name ?? 'System' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ basename(str_replace('\\', '/', $alert->subject_type)) }} #{{ $alert->subject_id }}</span>
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

@endsection
