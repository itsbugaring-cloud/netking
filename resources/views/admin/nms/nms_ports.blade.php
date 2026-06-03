@extends('layouts.app')
@section('title', 'NMS Port Traffic')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-git-merge me-2' style="color:var(--orange);"></i>Port Traffic Overview</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nms.dashboard') }}">NMS</a></li>
                <li class="breadcrumb-item active">Ports</li>
            </ol>
        </nav>
    </div>
</div>

@foreach($olts as $olt)
<div class="card mb-4 borderless shadow-sm">
    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 d-flex align-items-center">
            <div class="icon-wrap bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                <i class='bx bx-server fs-5'></i>
            </div>
            <div>
                <span style="font-weight:700;color:var(--text-dark);">{{ $olt->name }}</span>
                <span class="d-block text-muted" style="font-size:0.75rem;font-weight:500;">Brand: {{ $olt->brand }} | IP: {{ $olt->ip_address }}</span>
            </div>
        </h5>
        <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size:0.8rem;">
            {{ $olt->onts->count() }} ONTs
        </div>
    </div>
    
    <div class="card-body px-4 pb-4">
        @if($olt->onts->isEmpty())
        <div class="text-center py-5 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
            <i class='bx bx-info-circle text-muted fs-2 mb-2'></i>
            <p class="text-muted mb-0 fw-medium">No ONTs registered on this OLT.</p>
        </div>
        @else
        <div class="table-responsive rounded-3 border" style="background:var(--bg-card);">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Port</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">ONT ID</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">SN / Mac</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Customer</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Status</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Signal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $currentPort = null; @endphp
                    @foreach($olt->onts as $ont)
                        @if($currentPort !== $ont->pon_port)
                            @if($currentPort !== null)
                                {{-- Port divider --}}
                                <tr><td colspan="6" style="padding:0;height:4px;background:var(--bg-lighter);"></td></tr>
                            @endif
                            @php $currentPort = $ont->pon_port; @endphp
                        @endif
                        
                        <tr>
                            <td class="fw-bold text-dark">
                                <span class="badge bg-light text-dark shadow-sm px-2 py-1 border">PON {{ $ont->pon_port }}</span>
                            </td>
                            <td><span class="text-muted fw-medium fs-7">#{{ $ont->onu_id }}</span></td>
                            <td>
                                <code class="bg-primary-subtle text-primary px-2 py-1 rounded" style="font-size:0.75rem;">{{ $ont->sn ?: $ont->mac_address }}</code>
                            </td>
                            <td>
                                @if($ont->customer)
                                    <span class="fw-semibold text-dark">{{ $ont->customer->name }}</span>
                                @else
                                    <span class="text-muted fst-italic fs-7">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($ont->status === 'online')
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold"><i class='bx bxs-circle me-1' style="font-size:6px;vertical-align:middle;"></i>Online</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold"><i class='bx bx-x me-1' style="vertical-align:middle;"></i>Offline</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sig = (float)$ont->rx_power;
                                    $color = 'text-muted';
                                    if($sig < 0 && $sig > -30) {
                                        $color = $sig > -25 ? 'text-success' : ($sig > -28 ? 'text-warning' : 'text-danger');
                                    }
                                @endphp
                                <span class="{{ $color }} fw-bold" style="font-size:0.8rem;">{{ $ont->rx_power ?: '—' }} dBm</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endforeach

@endsection
