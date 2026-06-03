@extends('layouts.app')
@section('title', 'NMS Syslog')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-file-blank me-2' style="color:var(--orange);"></i>System Activity Log</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nms.dashboard') }}">NMS</a></li>
                <li class="breadcrumb-item active">Syslog</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-sm borderless">
    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 d-flex align-items-center">
            <div class="icon-wrap bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                <i class='bx bx-list-ul fs-5'></i>
            </div>
            Event History
        </h5>
    </div>
    <div class="card-body px-4 pb-4">
        @if($logs->isEmpty())
        <div class="text-center py-5 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
            <i class='bx bx-info-circle text-muted fs-2 mb-2'></i>
            <p class="text-muted mb-0 fw-medium">No system activity logs found.</p>
        </div>
        @else
        <div class="table-responsive rounded-3 border" style="background:var(--bg-card);">
            <table class="table table-hover mb-0 align-middle" id="syslog-table">
                <thead class="bg-light">
                    <tr>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Time</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">User</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Action</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Description</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;letter-spacing:0.5px;">Target</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td class="text-muted fw-medium" style="font-size:0.8rem;white-space:nowrap;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            @if($log->user)
                            <div class="d-flex align-items-center gap-2">
                                <span class="bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:24px;height:24px;font-size:0.7rem;">
                                    {{ substr($log->user->name, 0, 1) }}
                                </span>
                                <span class="fw-semibold text-dark fs-7">{{ $log->user->name }}</span>
                            </div>
                            @else
                            <span class="badge bg-light text-muted border px-2 py-1"><i class='bx bx-bot me-1'></i>System</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $color = 'secondary';
                                if(str_contains($log->action, 'create') || str_contains($log->action, 'provision')) $color = 'success';
                                elseif(str_contains($log->action, 'update') || str_contains($log->action, 'change')) $color = 'primary';
                                elseif(str_contains($log->action, 'delete') || str_contains($log->action, 'fail') || str_contains($log->action, 'suspend')) $color = 'danger';
                            @endphp
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} px-2 py-1 rounded-pill fw-semibold shadow-sm" style="font-size:0.7rem;">
                                {{ str_replace('_', ' ', strtoupper($log->action)) }}
                            </span>
                        </td>
                        <td class="text-dark fw-medium" style="font-size:0.85rem;">{{ $log->description }}</td>
                        <td class="text-muted" style="font-size:0.75rem;">
                            {{ basename(str_replace('\\', '/', $log->subject_type)) }} <code class="bg-light px-1 rounded shadow-sm">#{{ $log->subject_id }}</code>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    if ($.fn.DataTable) {
        $('#syslog-table').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            language: { search: 'Filter Logs:' }
        });
    }
});
</script>
@endsection
