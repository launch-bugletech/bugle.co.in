@extends(backendView('layouts.app'))

@section('title', 'Edit Team Member')

@section('content')
<div class="container-xxl">

    <div class="row align-items-center mb-4">
        <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
            <h3 class="fw-bold mb-0">Edit Employee</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('admin/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url(path: 'admin/employees') }}">Employee Members</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Employee</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
         <form action="{{ route('employees.update', $user->id) }}" method="POST"id="teamMemberForm">
    @csrf
    @method('PUT')

                <div class="row g-3">

                    {{-- Username --}}
                    <div class="col-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Enter username" value="{{ old('username', $user->username) }}" required>
                        <span class="text-danger error username"></span>
                    </div>

                    {{-- First Name --}}
                    <div class="col-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control form-control-sm" placeholder="Enter first name" value="{{ old('first_name', $user->first_name) }}" required>
                        <span class="text-danger error first_name"></span>
                    </div>

                    {{-- Last Name --}}
                    <div class="col-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control form-control-sm" placeholder="Enter last name" value="{{ old('last_name', $user->last_name) }}" required>
                        <span class="text-danger error last_name"></span>
                    </div>

                    {{-- Email --}}
                    <div class="col-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="Enter email" value="{{ old('email', $user->email) }}" required>
                        <span class="text-danger error email"></span>
                    </div>

                    {{-- Mobile No --}}
                    <div class="col-6">
                        <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" maxlength="10" class="form-control form-control-sm" placeholder="Enter mobile number" value="{{ old('phone', $user->phone) }}" required>
                        <span class="text-danger error phone"></span>
                    </div>

                    {{-- Role --}}
                 <div class="col-6">
    <label class="form-label">Role <span class="text-danger">*</span></label>

    <select name="role_id" class="form-select form-select-sm" required>
        <option value="">Select Role</option>

        @foreach($roles as $role)
            @if(auth()->user()->role_id == 1 || $role->id != 1)
                <option value="{{ $role->id }}"
                    {{ (old('role_id', $user->roles->first()?->id ?? '') == $role->id) ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endif
        @endforeach
    </select>

    <span class="text-danger error role_id"></span>
</div>
    

                    {{-- Password --}}
                    <div class="col-6 position-relative">
                        <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control form-control-sm password-field" placeholder="8+ characters required">
                        <span class="text-danger error password"></span>
                        <i class="bi bi-eye-slash toggle-password" style="position:absolute;top:38px;right:10px;cursor:pointer;"></i>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-6 position-relative">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control form-control-sm password-field" placeholder="Confirm password">
                        <span class="text-danger error confirm_password"></span>
                        <i class="bi bi-eye-slash toggle-password" style="position:absolute;top:38px;right:10px;cursor:pointer;"></i>
                    </div>

                    {{-- Date of Join --}}
<div class="col-6">
    <label class="form-label">Date of Join <span class="text-danger">*</span></label>
    <input type="date" name="date_of_join" class="form-control form-control-sm" 
           value="{{ old('date_of_join', $user->date_of_join ? \Carbon\Carbon::parse($user->date_of_join)->format('Y-m-d') : '') }}" required>
    <span class="text-danger error date_of_join"></span>
</div>

{{-- Date of Left --}}
 <div class="col-6">
                            <label class="form-label">Date of Left</label>
                           <input type="date" name="date_of_left"
                    min="{{ old('date_of_join', $user->date_of_join ? \Carbon\Carbon::parse($user->date_of_join)->format('Y-m-d') : '') }}"
                    class="form-control form-control-sm">

                            <span class="text-danger error date_of_left"></span>

                        </div>

                    {{-- Status --}}
                    <div class="col-6">
                        <label class="form-label">Are you working?</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-status-switch"
                                type="checkbox"
                                id="statusSwitch"
                                name="status"
                                value="active"
                                {{ old('status', $user->status) == 'active' ? 'checked' : '' }}>
                            <label for="statusSwitch" class="form-label ms-2" id="statusLabel">
                                {{ old('status', $user->status) == 'active' ? 'Active' : 'Inactive' }}
                            </label>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="col-6">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control form-control-sm" placeholder="Enter location" value="{{ old('location', $user->location) }}">
                        <span class="text-danger error location"></span>
                    </div>

                    {{-- Department --}}
                    <div class="col-6">
                        <label class="form-label">Department / Position <span class="text-danger">*</span></label>
                        <input type="text" name="department" class="form-control form-control-sm" placeholder="Enter department or position" value="{{ old('department', $user->department) }}" required>
                        <span class="text-danger error department"></span>
                    </div>

                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning px-5">Update</button>
                    <a href="{{ url('admin/employees') }}" class="btn btn-secondary px-5">Cancel</a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
/* Toggle switch sizes and colors */
.form-check-input.toggle-status-switch {
    width: 50px;
    height: 25px;
    cursor: pointer;
    transition: background-color 0.3s;
}

/* Active state green */
.form-check-input.toggle-status-switch:checked {
    background-color: #28a745;
    border-color: #28a745;
}

/* Inactive state red */
.form-check-input.toggle-status-switch:not(:checked) {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Circle inside switch */
.form-check-input.toggle-status-switch::before {
    width: 21px;
    height: 21px;
    left: 2px;
    top: 2px;
    transition: transform 0.3s;
}

.form-check-input.toggle-status-switch:checked::before {
    transform: translateX(25px);
}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush

@push('custom_scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Toggle status text
    $('#statusSwitch').change(function() {
        let isChecked = $(this).is(':checked');
        $(this).val(isChecked ? 'active' : 'inactive');
        $('#statusLabel').text(isChecked ? 'Active' : 'Inactive');
    });

    // Initialize status on page load
    let statusSwitch = $('#statusSwitch');
    let isChecked = statusSwitch.is(':checked');
    statusSwitch.val(isChecked ? 'active' : 'inactive');
    $('#statusLabel').text(isChecked ? 'Active' : 'Inactive');

    // Show/hide password
    $('.toggle-password').click(function() {
        let input = $(this).siblings('input.password-field');
        if(input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('bi-eye-slash').addClass('bi-eye');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('bi-eye').addClass('bi-eye-slash');
        }
    });
});
</script>
@endpush