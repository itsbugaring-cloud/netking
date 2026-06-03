@extends('layouts.app')
@section('title', 'NMS Topology')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-network-chart'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Topologi Jaringan</h1>
        </div>
    </div>

<div class="row g-3">
    <!-- OLT Network Map -->
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title d-flex align-items-center">
                    <div class="icon-wrap bg-primary-subtle text-primary me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-server'></i>
                    </div>
                    Jaringan Fiber OLT
                </span>
                <span class="ms-chip">{{ $olts->count() }} Headend</span>
            </div>
            <div class="ms-panel-body position-relative">
                <div class="topology-container" style="position:relative;padding-left:1.5rem;border-left:2px solid var(--border-color);">
                    @foreach($olts as $olt)
                    <div class="topology-node mb-4 position-relative">
                        <!-- Connector line to left border -->
                        <div style="position:absolute;left:-24px;top:24px;width:16px;height:2px;background:var(--border-color);"></div>
                        
                        <!-- OLT Node -->
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary text-white rounded-3 shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;border:3px solid #fff;">
                                <i class='bx bx-server fs-3'></i>
                            </div>
                            <div class="bg-white rounded-3 border shadow-sm p-3 w-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">{{ $olt->name }}</h6>
                                        <div class="text-muted" style="font-size:0.75rem;">IP: <code class="px-1 rounded bg-light border">{{ $olt->ip_address }}</code></div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill fw-semibold shadow-sm" style="font-size:0.7rem;">
                                        {{ $olt->onts_count }} ONTs
                                    </span>
                                </div>
                                
                                <!-- PON Ports Summary (mock visualization) -->
                                <div class="d-flex gap-1 mt-3">
                                    @for($i=1; $i<=8; $i++)
                                    <div class="rounded-1" style="flex:1;height:4px;background:{{ $olt->onts_count > ($i*10) ? 'var(--primary)' : 'var(--bg-lighter)' }};" title="PON {{ $i }}"></div>
                                    @endfor
                                </div>
                                <div class="text-end mt-1 text-muted" style="font-size:0.7rem;">Beban Port PON</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($olts->isEmpty())
                        <div class="text-center py-4 text-muted"><p class="mb-0 fw-medium">Tidak ada OLT yang dipetakan.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Area Distribution Map -->
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title d-flex align-items-center">
                    <div class="icon-wrap bg-success-subtle text-success me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-wifi'></i>
                    </div>
                    Distribusi Area
                </span>
                <span class="ms-chip">{{ $areas->count() }} Area</span>
            </div>
            <div class="ms-panel-body position-relative">
                <div class="topology-container" style="position:relative;padding-left:1.5rem;border-left:2px solid var(--border-color);">
                    @foreach($areas as $area)
                    <div class="topology-node mb-4 position-relative">
                        <!-- Connector line to left border -->
                        <div style="position:absolute;left:-24px;top:24px;width:16px;height:2px;background:var(--border-color);"></div>
                        
                        <!-- Area Node -->
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-success text-white rounded-3 shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;border:3px solid #fff;">
                                <i class='bx bx-map fs-3'></i>
                            </div>
                            <div class="bg-white rounded-3 border shadow-sm p-3 w-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">{{ $area->name }}</h6>
                                        <div class="text-muted" style="font-size:0.75rem;">Router: <code class="px-1 rounded bg-light border">{{ $area->router_ip ?: 'Belum diatur' }}</code></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold shadow-sm" style="font-size:0.7rem;">
                                                <i class='bx bx-user me-1'></i>{{ $area->customers_count }} Pelanggan
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill fw-semibold shadow-sm" style="font-size:0.7rem;">
                                                <i class='bx bx-box me-1'></i>{{ $area->odps_count }} ODPs
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($area->odps_count > 0)
                                <div class="d-flex align-items-center gap-2 mt-3 text-muted" style="font-size:0.75rem;font-weight:500;">
                                    <i class='bx bx-network-chart text-success'></i>
                                    <span>Jaringan mencakup {{ $area->odps_count }} titik distribusi</span>
                                </div>
                                @else
                                <div class="d-flex align-items-center gap-2 mt-3 text-muted" style="font-size:0.75rem;">
                                    <i class='bx bx-info-circle'></i>
                                    <span>Tidak ada titik distribusi yang ditentukan</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($areas->isEmpty())
                        <div class="text-center py-4 text-muted"><p class="mb-0 fw-medium">Tidak ada Area yang dipetakan.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection
