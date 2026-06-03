@extends('layouts.app')
@section('title', 'Admin Users')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4>Admin Users</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class='bx bx-plus me-1'></i> Add User
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class='bx bx-check me-1'></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><i class='bx bx-x me-1'></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                        <span class="badge bg-primary ms-1">You</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-info' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                            <i class='bx bx-edit'></i> Edit
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                            data-confirm="Delete user {{ addslashes($user->name) }}?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class='bx bx-trash'></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No users found</td>
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
        $('#users-table').DataTable({
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
                [0, 'asc']
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search users...',
                lengthMenu: 'Show _MENU_',
                info: '_START_-_END_ of _TOTAL_',
                paginate: {
                    previous: '&lsaquo;',
                    next: '&rsaquo;'
                }
            },
            columnDefs: [{
                orderable: false,
                targets: [4]
            }]
        });
    });
</script>
@endsection