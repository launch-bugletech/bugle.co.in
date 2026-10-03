@extends(backendView('layouts.app'))
@section('title', ' Group Edit')
@section('content')
<style type="text/css">
   ul {
   list-style-type: none;
   }
</style>
<div class="container-xxl">
   <div class="row align-items-center">
      <div class="border-0 mb-4">
         <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0"> Group Edit</h3>
            <nav aria-label="breadcrumb">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{url('admin/home')}}">Home</a></li>
                  <li class="breadcrumb-item"><a href="{{url('admin/customer-group/index')}}"> Group</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Add  Group</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Row end  -->
   <div class="row clearfix g-3">
      <div class="col-lg-12">
         <div class="card mb-3">
            <!-- <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
               <h6 class="m-0 fw-bold">Customer Group Information</h6>
               </div> -->
            <div class="card-body">
                {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
                  @csrf
                  <div class="row g-3 align-items-center">
                     <div class="col-md-6">
                        <label class="form-label">Group <span class="text-danger">*</span></label>
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        <span class="text-danger kt-form__help error name"></span>
                     </div>
                     <div class="col-md-12">
				    <label class="form-label">Permission <span class="text-danger">*</span></label>
				    <div class="row">
				        @php $module_action = ''; @endphp  
                      @foreach($permission as $value)
                        @php $name = explode("_",$value->name);
                        @endphp
                        @if($name[1] == $module_action)
                        @php $module_action = $name[1]; @endphp 
				        <div class="col-md-4">
				            <label class="checkbox-inline">
				                {{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                               {{ $value->name }}
				            </label>
				        </div>
				         @else
                       <br/>
                       @php $module_action = $name[1]; @endphp
                       <div class="col-md-4">
                        <label class="checkbox-inline">
                           {{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                                 {{ $value->name }}
                        </label>
                    </div>
                     @endif                 
                   @endforeach
				    </div>
				</div>
                  </div>
                  <button type="submit" class="btn btn-warning mt-4 text-uppercase px-5">Submit</button>
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
   <!-- Row End -->
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