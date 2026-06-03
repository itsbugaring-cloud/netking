@extends('layouts.app')
@section('title', 'Vouchers')
@section('content')

<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4>Vouchers</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Vouchers</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary btn-sm">
        <i class='bx bx-plus me-1'></i>Generate Vouchers
    </a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class='bx bx-purchase-tag me-2' style="color:#2563eb;"></i>Voucher Batches</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="vouchers-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Batch Name</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Progress</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                <tr>
                    <td class="text-muted" style="font-size:.8125rem;">{{ $batch->id }}</td>
                    <td>
                        <div style="font-weight:500;font-size:.875rem;">{{ $batch->name }}</div>
                        <div class="text-muted" style="font-size:.75rem;">Prefix: {{ $batch->prefix }} · Profile: {{ $batch->profile }}</div>
                    </td>
                    <td>
                        <span class="badge-status {{ $batch->type === 'hotspot' ? 'badge-active' : '' }}" style="{{ $batch->type === 'pppoe' ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' : '' }}padding:2px 8px;border-radius:4px;font-size:.75rem;">
                            {{ strtoupper($batch->type) }}
                        </span>
                    </td>
                    <td style="font-size:.8125rem;">{{ $batch->duration_days }}d</td>
                    <td style="font-size:.8125rem;">Rp {{ number_format($batch->price, 0, ',', '.') }}</td>
                    <td style="min-width:120px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="flex-fill" style="height:6px;background:#f1f5f9;border-radius:9px;overflow:hidden;">
                                @php $pct = $batch->total > 0 ? round(($batch->used / $batch->total) * 100) : 0; @endphp
                                <div style="width:{{ $pct }}%;height:100%;background:#2563eb;border-radius:9px;transition:width .3s;"></div>
                            </div>
                            <span style="font-size:.75rem;color:#64748b;white-space:nowrap;">{{ $batch->used }}/{{ $batch->total }}</span>
                        </div>
                    </td>
                    <td style="font-size:.8125rem;color:#64748b;">{{ $batch->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.vouchers.show', $batch) }}" class="btn btn-sm btn-outline-primary" style="padding:2px 8px;font-size:.75rem;"><i class='bx bx-list-ul'></i></a>
                            <form method="POST" action="{{ route('admin.vouchers.destroy', $batch) }}" class="d-inline" data-confirm="Delete batch and all its vouchers?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:2px 8px;font-size:.75rem;">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No voucher batches yet. <a href="{{ route('admin.vouchers.create') }}">Generate now →</a></td>
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
        $('#vouchers-table').DataTable({
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
                }
            ],
            pageLength: 25,
            order: [
                [6, 'desc']
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search vouchers...',
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
    });
</script>
@endsection