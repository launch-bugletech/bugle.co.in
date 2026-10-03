@extends(backendView('layouts.app'))

@section('title', 'Projects')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Str;
    @endphp

    <div class="container-xxl">

        {{-- Header --}}
        <div class="row align-items-center">
            <div class="border-0 mb-4">
                <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
                    <h3 class="fw-bold mb-0">Project Management</h3>

                    <a href="{{ route('admin.projects.create') }}" class="btn btn-dark">
                        <i class="icofont-plus-circle me-2"></i> Add Project
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card">
            <div class="card-body">
                <table id="myProjectTable" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>Description</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Attachment</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($projects as $project)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>{{ $project->name }}</td>

                                            <td>{{ Str::limit($project->description, 50) }}</td>

                                            <td>
                                                {{ $project->start_date
                            ? \Carbon\Carbon::parse($project->start_date)->format('d-m-Y')
                            : '-' }}
                                            </td>

                                            <td>
                                                {{ $project->end_date
                            ? \Carbon\Carbon::parse($project->end_date)->format('d-m-Y')
                            : '-' }}
                                            </td>


                                            {{-- Attachment --}}
                                           <td>
                                            @if($project->attachment)
                                                <a href="{{ asset('storage/app/public/' . $project->attachment) }}" target="_blank"
                                                    class="btn btn-sm btn-info">
                                                    View
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                            {{-- Status --}}
                                            <td>
                                                @if($project->status == 'Active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($project->status == 'On Hold')
                                                    <span class="badge bg-warning text-dark">On Hold</span>
                                                @elseif($project->status == 'Completed')
                                                    <span class="badge bg-secondary">Completed</span>
                                                @elseif($project->status == 'Maintenance')
                                                    <span class="badge bg-warning">Maintenance</span>
                                                @else
                                                    <span class="badge bg-dark">{{ $project->status }}</span>
                                                @endif
                                            </td>

                                            {{-- Actions --}}
                                            <td>
                                                <a class="btn btn-outline-secondary btn-sm"
                                                    href="{{ route('admin.projects.edit', $project->id) }}" title="Edit">
                                                    <i class="icofont-edit text-success"></i>
                                                </a>
                                                  {{-- View --}}
                                                <a class="btn btn-outline-info btn-sm"
                                                href="{{ route('admin.projects.show', $project->id) }}"
                                                title="View">
                                                    <i class="icofont-eye text-primary"></i>
                                                </a>

                                                <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $project->id }}"
                                                    title="Delete">
                                                    <i class="icofont-trash"></i>
                                                </button>
                                            </td>


                                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection


{{-- Styles --}}
@push('styles')
    <link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}">
@endpush


{{-- Scripts --}}
@push('scripts')
    <script src="{!! backendAssets('dist/assets/bundles/dataTables.bundle.js') !!}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush


{{-- Custom Script --}}
@push('custom_scripts')
    <script>
        $(function () {

            $('#myProjectTable').DataTable({
                order: [[0, 'asc']]
            });

            $('.delete-btn').click(function () {

                let id = $(this).data('id');
                let deleteUrl = "{{ route('admin.projects.destroy', ':id') }}";
                deleteUrl = deleteUrl.replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This project will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete'
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: "DELETE"
                            },
                            success: function () {
                                Swal.fire('Deleted!', 'Project deleted successfully', 'success')
                                    .then(() => location.reload());
                            },
                            error: function () {
                                Swal.fire('Error!', 'Failed to delete project', 'error');
                            }
                        });

                    }

                });

            });

        });
    </script>
@endpush