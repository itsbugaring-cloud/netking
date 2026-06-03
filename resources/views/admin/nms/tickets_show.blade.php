@extends('layouts.app')
@section('title', $ticket->ticket_number)
@section('content')

@php
$priorityColors = ['low'=>'#22c55e','medium'=>'#f59e0b','high'=>'#ef4444','critical'=>'#1e293b'];
$statusColors = ['open'=>'#3b82f6','in_progress'=>'#f59e0b','resolved'=>'#22c55e','closed'=>'#94a3b8'];
$pc = $priorityColors[$ticket->priority] ?? '#64748b';
$sc = $statusColors[$ticket->status] ?? '#64748b';
@endphp

<div class="page-header mb-4 d-flex align-items-start justify-content-between">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0">{{ $ticket->subject }}</h4>
            <span style="padding:2px 8px;border-radius:4px;font-size:.75rem;border:1px solid {{ $sc }}33;background:{{ $sc }}15;color:{{ $sc }};">
                {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
            </span>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active">{{ $ticket->ticket_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class='bx bx-trash me-1'></i>Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    {{-- Ticket Info --}}
    <div class="col-lg-8">
        {{-- Description --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:32px;height:32px;background:#2563eb;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:.875rem;flex-shrink:0;">A</div>
                    <div>
                        <div style="font-weight:600;font-size:.875rem;color:#1e293b;">Admin</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $ticket->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
                <div style="font-size:.9375rem;line-height:1.7;color:#334155;white-space:pre-wrap;">{{ $ticket->description }}</div>
            </div>
        </div>

        {{-- Reply thread --}}
        @foreach($ticket->replies as $reply)
        <div class="card mb-2 {{ $reply->is_internal ? '' : '' }}" style="{{ $reply->is_internal ? 'border-left:3px solid #f59e0b;' : '' }}">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:28px;height:28px;background:{{ $reply->is_internal ? '#fef3c7' : '#eff6ff' }};border-radius:50%;display:flex;align-items:center;justify-content:center;color:{{ $reply->is_internal ? '#92400e' : '#1d4ed8' }};font-weight:700;font-size:.75rem;flex-shrink:0;">
                        {{ strtoupper(substr($reply->user?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <span style="font-weight:600;font-size:.8125rem;">{{ $reply->user?->name ?? 'Admin' }}</span>
                        @if($reply->is_internal)
                        <span class="ms-2" style="font-size:.7rem;background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:3px;">Internal Note</span>
                        @endif
                    </div>
                    <div class="ms-auto text-muted" style="font-size:.75rem;">{{ $reply->created_at->diffForHumans() }}</div>
                </div>
                <div style="font-size:.875rem;line-height:1.65;color:#334155;white-space:pre-wrap;">{{ $reply->message }}</div>
            </div>
        </div>
        @endforeach

        {{-- Reply form --}}
        <div class="card">
            <div class="card-header"><span class="card-title"><i class='bx bx-reply me-2' style="color:#2563eb;"></i>Reply</span></div>
            <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
                @csrf
                <div class="card-body">
                    <textarea name="message" class="form-control mb-3" rows="4" placeholder="Type your reply..." required></textarea>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="isInternal">
                                <label class="form-check-label" for="isInternal" style="font-size:.8125rem;">Internal Note</label>
                            </div>
                            <select name="status" class="form-select form-select-sm" style="max-width:160px;">
                                <option value="">Keep Status</option>
                                @foreach(['open','in_progress','resolved','closed'] as $s)
                                <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>
                                    Set to: {{ ucfirst(str_replace('_',' ',$s)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class='bx bx-send me-1'></i>Post Reply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar info --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Ticket Info</span></div>
            <div class="card-body">
                <table class="table table-sm mb-0" style="font-size:.8125rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted">Number</td>
                            <td><code>{{ $ticket->ticket_number }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Category</td>
                            <td>{{ ucfirst($ticket->category) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Priority</td>
                            <td><span style="color:{{ $pc }};font-weight:600;">● {{ ucfirst($ticket->priority) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Customer</td>
                            <td>{{ $ticket->customer?->name ?? ($ticket->contact_name ?? '—') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td>{{ $ticket->contact_phone ?? $ticket->customer?->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($ticket->resolved_at)
                        <tr>
                            <td class="text-muted">Resolved</td>
                            <td>{{ $ticket->resolved_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Assignment / Status --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Update</span></div>
            <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8125rem;">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8125rem;">Assign To</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">— Unassigned —</option>
                            @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection