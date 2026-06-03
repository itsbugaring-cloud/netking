@extends('layouts.app')
@section('title', 'Tickets')
@section('content')

<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4>Support Tickets</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Tickets</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary btn-sm">
        <i class='bx bx-plus me-1'></i>New Ticket
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([['Open', $stats['open'], '#ef4444', 'bx-envelope-open'], ['In Progress', $stats['in_progress'], '#f59e0b', 'bx-loader-circle'], ['Resolved', $stats['resolved'], '#22c55e', 'bx-check-circle']] as [$label, $count, $color, $icon])
    <div class="col-md-4">
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;background:{{ $color }}1a;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class='bx {{ $icon }}' style="color:{{ $color }};font-size:1.375rem;"></i>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:700;color:#1e293b;" data-countup="{{ $count }}">0</div>
                    <div class="text-muted" style="font-size:.8125rem;">{{ $label }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket / subject..." value="{{ request('search') }}" style="max-width:220px;">
            <select name="status" class="form-select form-select-sm no-select2" style="max-width:140px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['open','in_progress','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="form-select form-select-sm no-select2" style="max-width:130px;" onchange="this.form.submit()">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','critical'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-light">Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="tickets-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Customer</th>
                    <th>Assigned</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                $priorityColors = ['low'=>'#22c55e','medium'=>'#f59e0b','high'=>'#ef4444','critical'=>'#1e293b'];
                $statusColors = ['open'=>'#3b82f6','in_progress'=>'#f59e0b','resolved'=>'#22c55e','closed'=>'#94a3b8'];
                @endphp
                @forelse($tickets as $ticket)
                <tr style="cursor:pointer;" onclick="location.href='{{ route('admin.tickets.show', $ticket) }}'">
                    <td><code style="font-size:.75rem;">{{ $ticket->ticket_number }}</code></td>
                    <td>
                        <div style="font-weight:500;font-size:.875rem;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $ticket->subject }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ ucfirst($ticket->category) }}</div>
                    </td>
                    <td>
                        @php $pc = $priorityColors[$ticket->priority] ?? '#64748b'; @endphp
                        <span style="display:inline-block;width:8px;height:8px;background:{{ $pc }};border-radius:50%;margin-right:4px;"></span>
                        <span style="font-size:.8125rem;color:{{ $pc }};font-weight:600;">{{ ucfirst($ticket->priority) }}</span>
                    </td>
                    <td>
                        @php $sc = $statusColors[$ticket->status] ?? '#64748b'; @endphp
                        <span style="padding:2px 8px;border-radius:4px;font-size:.75rem;border:1px solid {{ $sc }}33;background:{{ $sc }}15;color:{{ $sc }};">
                            {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                        </span>
                    </td>
                    <td style="font-size:.8125rem;">{{ $ticket->customer?->name ?? ($ticket->contact_name ?? '—') }}</td>
                    <td style="font-size:.8125rem;">{{ $ticket->assignedTo?->name ?? '—' }}</td>
                    <td style="font-size:.8125rem;color:#64748b;">{{ $ticket->created_at->format('d M Y') }}</td>
                    <td onclick="event.stopPropagation()">
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary" style="padding:2px 8px;font-size:.75rem;">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No tickets found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('#tickets-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-buttons"B>f>rt<"d-flex justify-content-between align-items-center mt-3"lip>',
            buttons: [{
                    extend: 'excel',
                    className: 'btn btn-sm btn-outline-primary',
                    text: '<i class="bx bx-spreadsheet me-1"></i>Excel'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-outline-danger',
                    text: '<i class="bx bx-file-blank me-1"></i>PDF'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-outline-secondary',
                    text: '<i class="bx bx-data me-1"></i>CSV'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-outline-dark',
                    text: '<i class="bx bx-printer me-1"></i>Print'
                }
            ],
            pageLength: 25,
            order: [
                [6, 'desc']
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search tickets...',
                lengthMenu: 'Show _MENU_',
                info: '_START_-_END_ of _TOTAL_',
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
        document.querySelectorAll('[data-countup]').forEach(function(el) {
            var val = parseFloat(el.getAttribute('data-countup'));
            var counter = new countUp.CountUp(el, val, {
                duration: 1.5,
                useGrouping: true,
                separator: '.'
            });
            if (!counter.error) counter.start();
        });
    });
</script>
@endsection