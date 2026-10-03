@extends(backendView('layouts.app'))

@section('title', 'Edit Profile')

@section('content')
<div class="container-xxl">
	<div class="row g-3 mb-3">
	
		<div class="col-xl-12 col-lg-12">
			<div class="card mb-3">
			
				<div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
					<h6 class="mb-0 fw-bold ">My Account</h6>
					<div class="flash-message-container" style="margin-top:-50px">
                         @if(session('success'))
                             <div class="flash-message">{{ session('success') }}</div>
                         @endif
                         @if (session('error'))
                         <div class="flash-message-error">{{ session('error') }}</div>
                         @endif
                 </div>
				</div>
				
				<div class="card-body">
					<form action="{{url('admin/update_profile')}}" method="POST" id="customergroupForm" enctype="multipart/form-data">
                    @method('PATCH')
						@csrf
						
					<div class="row g-3 align-items-center">

							<div class="row g-3 align-items-center" style="margin-top:30px">

							<div class="col-6">
								<div class="mb-2">
									<label class="form-label">User Name</label>
									<input type="text" name="username"  value="{{$user->username}}" class="form-control form-control-sm" >
									<span class="text-danger kt-form__help error username"></span>
								</div>
							</div>
							
                            <div class="col-6">
								<div class="mb-2">
									<label class="form-label">First Name</label>
									<input type="text" name="first_name"  value="{{$user->first_name}}" class="form-control form-control-sm">
									<span class="text-danger kt-form__help error first_name"></span>
								</div>
							</div>
                            
                           
                            <div class="col-6">
								<div class="mb-2">
									<label class="form-label">Last Name</label>
									<input type="text" name="last_name"  value="{{$user->last_name}}"class="form-control form-control-sm">
									<span class="text-danger kt-form__help error last_name"></span>
								</div>
							</div>
                            <div class="col-6">
								<div class="mb-2">
									<label class="form-label">Email</label>
                               		<input type="text" name="email"  value="{{$user->email}}"class="form-control form-control-sm">
									<span class="text-danger kt-form__help error email"></span>
								</div>
							</div>
                            <div class="col-6">
								<div class="mb-2">
									<label class="form-label">Phone</label>
                               		<input type="text" name="phone"  value="{{$user->phone}}"class="form-control form-control-sm">
									<span class="text-danger kt-form__help error phone"></span>
								</div>
							</div>
                            <div class="col-6">
    <div class="mb-2">
        <label class="form-label">Date of join</label>
        <input type="text"
               name="date_of_join"
               value="{{ $user->date_of_join ? \Carbon\Carbon::parse($user->date_of_join)->format('d-m-Y') : '' }}"
               class="form-control form-control-sm">
        <span class="text-danger kt-form__help error date_of_join"></span>
    </div>
</div>

<div class="col-6">
    <div class="mb-2">
        <label class="form-label">Date of left</label>
        <input type="text"
               name="date_of_left"
               value="{{ $user->date_of_left ? \Carbon\Carbon::parse($user->date_of_left)->format('d-m-Y') : '' }}"
               class="form-control form-control-sm">
        <span class="text-danger kt-form__help error date_of_left"></span>
    </div>
</div>

                            <div class="col-6">
								<div class="mb-2">
									<label class="form-label">Department</label>
                               		<input type="text" name="department"  value="{{$user->department}}"class="form-control form-control-sm">
									<span class="text-danger kt-form__help error department"></span>
								</div>
							</div>
                            <div class="col-6">
								<div class="mb-2">
									<label class="form-label">Location</label>
                               		<input type="text" name="location"  value="{{$user->location}}"class="form-control form-control-sm">
									<span class="text-danger kt-form__help error location"></span>
								</div>
							</div>
							
						</div>
						</div>
									<div class="mt-4">
    <button type="button"
            id="editBtn"
            class="btn btn-secondary text-uppercase px-5 me-2">
        Edit
    </button>

    <button type="submit"
            id="saveBtn"
            class="btn btn-primary text-uppercase px-5 d-none">
        Save
    </button>
</div>
					</form>
				</div>
			</div>
	
		</div>
	</div><!-- Row end  -->

</div>
@endsection
@push('scripts')
<script>
    document.getElementById('editBtn').addEventListener('click', function () {

        // Enable all fields
        document.querySelectorAll('.editable-field').forEach(function (el) {
            el.removeAttribute('readonly');
        });

        // Toggle buttons
        document.getElementById('editBtn').classList.add('d-none');
        document.getElementById('saveBtn').classList.remove('d-none');
    });
</script>
@endpush



