@extends(backendView('layouts.app'))

@section('title', 'Team Member')

@section('content')
<div class="container-xxl">

    <div class="row align-items-center">
        <div class="border-0 mb-4">
            <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
                <h3 class="fw-bold mb-0">Employee Management</h3>
                @if(auth()->user()->role_id == 1)
    <a href="{{ url('admin/employees/create') }}" class="btn btn-dark">
        <i class="icofont-plus-circle me-2"></i>Add Employee
    </a>
@endif

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="myProjectTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Are you working?</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($team_member as $val)
                    <tr>
                        <td>{{ $val->id }}</td>

                        <td>{{ $val->first_name }} {{ $val->last_name }}</td>

                        <td>{{ $val->username ?? $val->name }}</td>

                        <td>{{ $val->email }}</td>
                        <td>{{ $val->department }}</td>

                        <td>
                            <span class="badge bg-success">
                                {{ $val->role_name }}
                            </span>
                        </td>

                        {{-- STATUS TOGGLE --}}
                        <td>
    <div class="form-check form-switch">
        <input class="form-check-input toggle-status-switch"
               type="checkbox"
               id="statusSwitch{{ $val->id }}"
               data-id="{{ $val->id }}"
               {{ $val->status === 'active' ? 'checked' : '' }}>
    </div>
</td>



                        {{-- ACTIONS --}}
                        <td>
                            <a class="btn btn-outline-secondary btn-sm"
                               href="{{ url('admin/employees/view/'.$val->id) }}">
                                <i class="icofont-eye text-warning"></i>
                            </a>

                            <a class="btn btn-outline-secondary btn-sm"
                               href="{{ url('admin/employees/edit/'.$val->id) }}">
                                <i class="icofont-edit text-success"></i>
                            </a>
                              <a href="javascript:void(0)"
       class="btn btn-outline-secondary btn-sm delete-btn"
       data-id="{{ $val->id }}">
        <i class="icofont-trash text-danger"></i>
    </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>
@endsection

{{-- STYLES --}}
@push('styles')
<link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}">
@endpush
<style>
 /* Full custom toggle switch */
.form-check-input.toggle-status-switch {
    width: 50px;
    height: 25px;
    cursor: pointer;
    border-radius: 25px;
    background-color: #dc3545; /* default = inactive red */
    border: 1px solid #dc3545;
    position: relative;
    transition: all 0.3s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Circle inside the switch */
/* .form-check-input.toggle-status-switch::before {
    content: '';
    position: absolute;
    width: 21px;
    height: 21px;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    background-color: #fff;
    transition: all 0.3s;
} */

/* Active = green */
.form-check-input.toggle-status-switch:checked {
    background-color: #28a745 !important; /* green */
    border-color: #28a745 !important;
}

.form-check-input.toggle-status-switch:checked::before {
    transform: translateX(25px);
}

/* Optional: hover effect */
.form-check-input.toggle-status-switch:hover {
    opacity: 0.85;
}

</style>
{{-- SCRIPTS --}}
@push('scripts')
<script src="{!! backendAssets('dist/assets/bundles/dataTables.bundle.js') !!}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

{{-- CUSTOM JS --}}
@push('custom_scripts')
<script>
$(document).ready(function () {
    $('#myProjectTable').DataTable({
        order: [[0, 'desc']]
    });
});


$(document).on('change', '.toggle-status-switch', function () {
    let switchInput = $(this);
    let userId = switchInput.data('id');
    let isChecked = switchInput.is(':checked');
    let actionText = isChecked ? 'Activate this employee?' : 'Deactivate this employee?';

    Swal.fire({
        title: 'Are you sure?',
        text: actionText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#41d630ff',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: "{{ url('admin/employees/status') }}/" + userId,
                type: "GET",
                success: function(res) {
                    if(res.status){
                        Swal.fire('Updated!', res.message, 'success');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to update status.', 'error');
                    // revert toggle on error
                    switchInput.prop('checked', !isChecked);
                }
            });

        } else {
            // revert toggle if cancelled
            switchInput.prop('checked', !isChecked);
        }
    });
});


// DELETE EMPLOYEE WITH SWEETALERT
$(document).on('click', '.delete-btn', function () {
    let userId = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: "This employee will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ url('admin/employees/delete') }}/" + userId;
        }
    });
});


</script>
@endpush
