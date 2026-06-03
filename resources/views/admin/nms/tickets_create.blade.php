@extends('layouts.app')
@section('title', 'New Ticket')
@section('content')

<div class="page-header mb-4">
    <h4>New Ticket</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Tickets</a></li>
            <li class="breadcrumb-item active">New</li>
        </ol>
    </nav>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class='bx bx-edit me-2' style="color:#2563eb;"></i>Ticket Details</span></div>
            <form method="POST" action="{{ route('admin.tickets.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                placeholder="Brief description of the issue" value="{{ old('subject') }}" required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            {{-- Quill editor container --}}
                            <div id="quill-editor" style="min-height:180px; border-radius:8px; border:1px solid #e2e8f0; font-family:'Inter',sans-serif;"></div>
                            {{-- Hidden textarea to submit Quill content --}}
                            <textarea name="description" id="description-hidden" style="display:none;">{{ old('description') }}</textarea>
                            @error('description')<div class="text-danger mt-1" style="font-size:.875rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select no-select2">
                                @foreach(['billing','technical','installation','complaint','other'] as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select no-select2">
                                @foreach(['low','medium','high','critical'] as $p)
                                <option value="{{ $p }}" {{ old('priority','medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select no-select2">
                                <option value="">— Unassigned —</option>
                                @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ old('assigned_to') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer (optional)</label>
                            <select name="customer_id" class="form-select">
                                <option value="">— Not linked —</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->pppoe_user }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Name</label>
                            <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}">
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submit-ticket">
                        <i class='bx bx-save me-1'></i>Create Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class='bx bx-info-circle me-2' style="color:#06b6d4;"></i>Tips</span></div>
            <div class="card-body" style="font-size:.875rem; color:#64748b;">
                <ul class="ps-3 mb-0" style="line-height:1.8;">
                    <li>Use <strong>Critical</strong> for internet outages affecting many customers</li>
                    <li>Use <strong>High</strong> for single customer internet down</li>
                    <li>Use <strong>Medium</strong> for billing issues or slow speeds</li>
                    <li>Use <strong>Low</strong> for general inquiries</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Init Quill editor
        var quill = new Quill('#quill-editor', {
            theme: 'snow',
            placeholder: 'Detailed description of the problem...',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['clean']
                ]
            }
        });

        // Pre-fill with old value if exists
        var oldVal = document.getElementById('description-hidden').value;
        if (oldVal) quill.root.innerHTML = oldVal;

        // On form submit, copy Quill HTML to hidden textarea
        document.getElementById('submit-ticket').addEventListener('click', function(e) {
            var html = quill.root.innerHTML;
            if (html === '<p><br></p>') html = '';
            document.getElementById('description-hidden').value = html;
        });
    });
</script>
@endsection