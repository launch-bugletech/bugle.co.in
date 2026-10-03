@extends(backendView('layouts.app'))

@section('title', 'Create Project')

@section('content')
<div class="container-xxl">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
            <h3 class="fw-bold mb-0">Create Project</h3>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('admin/home') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.projects.index') }}">Projects</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Create Project
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Project Details</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.projects.store') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                {{-- Project Name --}}
                <div class="mb-3">
                    <label class="form-label">Project Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              class="form-control"
                              rows="4">{{ old('description') }}</textarea>
                </div>

                {{-- Start Date --}}
                <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date"
                           name="start_date"
                           class="form-control"
                           value="{{ old('start_date') }}">
                </div>

                {{-- End Date --}}
                <div class="mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date"
                           name="end_date"
                           class="form-control"
                           value="{{ old('end_date') }}">
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="Active" {{ old('status')=='Active'?'selected':'' }}>Active</option>
                        <option value="On Hold" {{ old('status')=='On Hold'?'selected':'' }}>On Hold</option>
                        <option value="Completed" {{ old('status')=='Completed'?'selected':'' }}>Completed</option>
                        <option value="Maintenance" {{ old('status')=='Maintenance'?'selected':'' }}>Maintenance</option>
                    </select>
                </div>

                {{-- Attachment --}}
                <div class="mb-3">
                    <label class="form-label">Attachment</label>
                    <input type="file"
                           name="attachment"
                           class="form-control">
                    <small class="text-muted">
                        Allowed: pdf, doc, xls, png, jpg
                    </small>
                </div>

                <button type="submit" class="btn btn-dark">
                    Save Project
                </button>

                <a href="{{ route('admin.projects.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>
        </div>
    </div>

</div>
@endsection


@push('custom_scripts')
<script>

</script>
@endpush
