@extends(backendView('layouts.app'))

@section('title', 'Project View')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <div class="container-xxl">

        {{-- Header --}}
        <div class="row align-items-center mb-4">
            <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
                <h3 class="fw-bold mb-0">View Project Details</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin/home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.projects.index') }}">Projects</a>
                        </li>
                        <li class="breadcrumb-item active">
                            Project View
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Card --}}
        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-bordered align-middle">

                    {{-- Project Name --}}
                    <tr>
                        <th width="250">Project Name</th>
                        <td>{{ $project->name }}</td>
                    </tr>

                    {{-- Description --}}
                    <tr>
                        <th>Description</th>
                        <td>{{ $project->description ?? '—' }}</td>
                    </tr>

                    {{-- Start Date --}}
                    <tr>
                        <th>Start date</th>
                        <td> {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d-m-Y') : '-' }}
                        </td>
                    </tr>


                    <tr>
                        <th>End date</th>
                        <td>
                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d-m-Y') : '-' }}
                        </td>
                    </tr>


                    {{-- Status --}}
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($project->status == 'Active')
                                <span class="badge bg-success px-3 py-2">Active</span>

                            @elseif($project->status == 'On Hold')
                                <span class="badge bg-warning text-dark px-3 py-2">On Hold</span>

                            @elseif($project->status == 'Completed')
                                <span class="badge bg-secondary px-3 py-2">Completed</span>
                                
                            @elseif($project->status == 'Maintenance')
                                <span class="badge bg-warning px-3 py-2">Maintenance</span>

                            @else
                                <span class="badge bg-dark px-3 py-2">
                                    {{ $project->status }}
                                </span>
                            @endif
                        </td>
                    </tr>

                    {{-- Attachment --}}
                    <tr>
                        <th>Attachment</th>
                        <td>
                            @if($project->attachment)
                                <a href="{{ Storage::url($project->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                    View Attachment
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>

                    {{-- Created At --}}
                    <tr>
                        <th>Created At</th>

                        <td> {{ $project->created_at ? \Carbon\Carbon::parse($project->created_at)->format('d-m-Y') : '-' }}
                        </td>
                    </tr>

                </table>

            </div>
        </div>

    </div>
@endsection