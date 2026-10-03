@extends(backendView('layouts.app'))

@section('title', 'Team Member View')

@section('content')
<div class="container-xxl">
    <div class="row align-items-center">
        <div class="border-0 mb-4">
            <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                <h3 class="fw-bold mb-0">Employees Information</h3>
                 <nav aria-label="breadcrumb">
                         <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('admin/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url(path: 'admin/employees') }}">Employee Members</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Employee</li>
                </ol>
                    </nav>
            </div>
        </div>
    </div> <!-- Row end  -->
    <div class="row clearfix g-3">
        
        <div class="col-lg-12">
            <div class="card mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h6 class="m-0 fw-bold">Employees Information <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body">
                    <form action="{{url('admin/employees/store')}}" method="POST" id="customergroupForm">
                        @csrf
                        <div class="row g-3 align-items-center">
                            <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username"class="form-control form-control-sm" value="{{$user->username}}" disabled>
                                    <span class="text-danger kt-form__help error username"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name"class="form-control form-control-sm" value="{{$user->first_name}}" disabled>
                                    <span class="text-danger kt-form__help error first_name"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name"class="form-control form-control-sm" value="{{$user->last_name}}" disabled>
                                    <span class="text-danger kt-form__help error last_name"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Email Address</label>
                                    <input type="text" name="email" class="form-control form-control-sm" value="{{$user->email}}" disabled>
                                    <span class="text-danger kt-form__help error email"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Mobile No</label>
                                    <input type="tel" maxlength="10" name="phone"class="form-control form-control-sm" value="{{$user->phone}}" disabled>
                                    <span class="text-danger kt-form__help error phone"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Date of Join</label>
                                   <input type="text"
                                            class="form-control form-control-sm"
                                            value="{{ $user->date_of_join ? \Carbon\Carbon::parse($user->date_of_join)->format('d-m-Y') : '' }}"
                                            disabled>
                                    <span class="text-danger kt-form__help error phone"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Date of left</label>
                                    <input type="text"
       class="form-control form-control-sm"
       value="{{ $user->date_of_left ? \Carbon\Carbon::parse($user->date_of_left)->format('d-m-Y') : '' }}"
       disabled>
                                    <span class="text-danger kt-form__help error date_of_left"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="mb-2">
                                    <label class="form-label">Department</label>
                                    <input type="tel" name="department"class="form-control form-control-sm" value="{{$user->department}}" disabled>
                                    <span class="text-danger kt-form__help error department"></span>
                                 </div>
                              </div>
                              <div class="col-6">
                              <div class="mb-2">
                                 <label class="form-label"> Role <span class="text-danger">*</span></label>
                                 <select class="form-control form-category " name="role_id" disabled>
                                    <option value="">Please Select</option>
                                    @foreach ($roles as $val)
                                    @if($val->id  == $user->role_id)
                                    <option selected value="{{$val->id}}">{{ $val->name}}</option>
                                    @else
                                    <option value="{{$val->id}}">{{ $val->name}}</option>
                                    @endif
                                    @endforeach
                                 </select>
                                 <span class="text-danger kt-form__help error role_id"></span>
                              </div>
                                 <div class="w-200 text-center">
    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary"style="margin-right: -37rem;">
        ← Back
    </a>
</div>

                              </div>
                           


                        </div>
                        <!-- <button type="submit" class="btn btn-warning mt-4 text-uppercase px-5">Submit</button> -->
                    </form>
                </div>
            </div>
        </div>
    </div><!-- Row End -->
</div>
@endsection

@push('styles')
@endpush

@push('custom_styles')
@endpush

@push('scripts')
@endpush

@push('custom_scripts')
<script type="text/javascript" src="{{asset('public/validation/CustomerGroupValidation.js')}}"></script> 
@endpush

@push('modals')
@endpush