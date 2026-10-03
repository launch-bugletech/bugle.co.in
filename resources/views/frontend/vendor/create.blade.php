@extends(backendView('layouts.app'))

@section('title', 'Add Vendor')

@section('content')


<div class="container-xxl">
	<div class="row align-items-center">
		<div class="border-0 mb-4">
			<div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
				<h3 class="fw-bold mb-0">New Drafts</h3>
			</div>
		</div>
	</div> <!-- Row end  -->
	<div class="row clearfix g-3">
		
		<div class="col-lg-12">
			<div class="card mb-3">
				<div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
					<h6 class="m-0 fw-bold">Basic Information <span class="text-danger">*</span></h6>
				</div>
				<div class="card-body">
					<form action="" method="POST" id="custdraftForm" enctype="multipart/form-data">
						@csrf 
						<div class="row g-3 align-items-center">
							<div class="col-md-4">
								<label class="form-label">Company Name <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="company_name"   placeholder="please enter company name">
								<span class="text-danger kt-form__help error company_name"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">Address <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="address" placeholder="please enter address">
								<span class="text-danger kt-form__help error address"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">City <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="city" placeholder="please enter city">
								<span class="text-danger kt-form__help error city"></span>
							</div>
						</div>
                        <div class="row g-3 align-items-center">
							<div class="col-md-4">
								<label class="form-label">Mobile Number <span class="text-danger">*</span></label>
								<input type="number" class="form-control" name="mobile_number"   placeholder="please enter mobile_number">
								<span class="text-danger kt-form__help error mobile_number"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">Email <span class="text-danger">*</span></label>
								<input type="email" class="form-control" name="email" placeholder="please enter email">
								<span class="text-danger kt-form__help error email"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">Contact Person</label>
								<input type="text" class="form-control" name="contact_person" placeholder="please enter contact_person">
								<span class="text-danger kt-form__help error contact_person"></span>
							</div>
						</div>
                        <div class="row g-3 align-items-center">
							<div class="col-md-4">
								<label class="form-label">Country <span class="text-danger">*</span></label>
								<input type="number" class="form-control" name="country"   placeholder="please enter country">
								<span class="text-danger kt-form__help error country"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">State <span class="text-danger">*</span></label>
								<input type="email" class="form-control" name="state" placeholder="please enter state">
								<span class="text-danger kt-form__help error state"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">Company Overview</label>
								<input type="textarea" class="form-control" name="company-overview" placeholder="Writer your company-overview">
								<span class="text-danger kt-form__help error company-overview"></span>
							</div>
						</div>
                        <div class="row g-3 align-items-center">
							<div class="col-md-4">
								<label class="form-label">Services Offered <span class="text-danger">*</span></label>
                                <select class="form-control" name="state">
                                        <option value="">Please Select</option>
                                        <option value="Solar Panel Installation">Solar Panel Installation</option>
                                        <option value="Solar Panel Cleaning">Solar Panel Cleaning</option>
                                    </select>
                                    <span class="text-danger kt-form__help error services_offered"></span>
							</div>
							<div class="col-md-4">
								<label class="form-label">Website</label>
								<input type="email" class="form-control" name="website" placeholder="please enter website">
								<span class="text-danger kt-form__help error state"></span>
							</div>
						
						</div>
                        <div class="row dimension">
                                <div class="col-2">
                                    <div class="mb-2">
                                        <label class="form-label">Social</label>
                                        <input type="text" name="social[]" class="form-control form-control-sm item_length" placeholder="Social Links" onclick="item_lengthClicked(this)">
                                        <span class="text-danger social_error"></span>
                                        <span class="text-danger kt-form__help error social"></span>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-warning btn-circle btn-xl add_button"  title="Add field" style="margin-top:25px; margin-left:10px">+ </button>
                                    </div>
                                </div>
                                <div id="dimension_card"></div>
                         </div>
						<!-- <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" placeholder="please enter address">
                                    <span class="text-danger kt-form__help error address"></span>
                            </div>
                            <div class="col-md-4">
                                    <label class="form-label">Contact No</label>
                                    <input type="text" class="form-control" name="contact_no" placeholder="please enter contact">
                                    <span class="text-danger kt-form__help error contact_no"></span>
                            </div>
                            <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <select class="form-control" name="state">
                                        <option value="">Please Select</option>
                                        @foreach($state as $val)
                                        <option value="{{ $val->state_id}}">{{$val->state_name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger kt-form__help error state"></span>
                            </div>
                            <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" placeholder="please enter city">
                                    <span class="text-danger kt-form__help error city"></span>
                            </div>
						
                            <div class="col-md-4">
                                    <label class="form-label">Zip Code</label>
                                    <input type="text" class="form-control" name="zip_code" placeholder="please eneter zip code">
                                    <span class="text-danger kt-form__help error zip_code"></span>
                            </div>
                            <div class="col-md-4">
                            <label class="form-label">Upload Pro Kitchen (Optional)</label>
                            <input type="file" class="form-control" name="pro_kitchen_pdf" id="pro_kitchen_pdf" onClick="proPdfClicked()">
                            <p style="margin-top: 10px; text-align: center;">(PDF/Excel File)</p>
                            <span class="text-danger" id="pro_kitchen_pdf_error"></span>
                            </div>
					    </div> -->
						<button type="submit" class="btn btn-warning mt-4 text-uppercase px-5">Next</button>
					</form>
				</div>
			</div>
		</div>
	</div><!-- Row End -->
</div>




@endsection

@push('styles')
<!-- plugin css file  -->
<link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/responsive.dataTables.min.css') !!}">
<link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}">
@endpush

@push('custom_styles')
<style>
    /* White buttons with black border */
    .btn-white-border {
        background-color: white;
		border-top: 1px solid gray;
        border-bottom: 1px solid black;
        color: black;
    }
    .btn-light:focus
    {
      background-color: #eca72f!important;
    }
</style>
@endpush

@push('scripts')
<!-- Plugin Js -->
<script src="{!! backendAssets('dist/assets/bundles/apexcharts.bundle.js') !!}"></script>
<script src="{!! backendAssets('dist/assets/bundles/dataTables.bundle.js') !!}"></script>
<script src="{!! backendAssets('dist/assets/js/page/index.js') !!}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1Jr7axGGkwvHRnNfoOzoVRFV3yOPHJEU&callback=myMap"></script>
@endpush

@push('custom_scripts')
<script>
    $(document).ready(function() {
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('#dimension_card'); //Input field wrapper

        var x = 1;
        var z = 1;
        var y = 2; //Initial field counter is 1
        var count = 1;

        //Once add button is clicked
        $('.add_button').click(function() {
             $('.social_error').html('');
           
            z++;

            if (!validateDimension()) {
                return;
            }

            //  if(variation_name == "")
            //   {
            //     $('.variation_error').html("Please Enter variation Name");
            //     console.log("error");
            //   }else{
            var fieldHTML = `
                        <div class="row dimension ">
                            <div class="col-2">
                                <div class="mb-2">
                                    <label class="form-label">Social</label>
                                    <input type="text" name="social[]" class="form-control form-control-sm item_length" placeholder="Social Links" onclick="item_lengthClicked(this)">
                                    <span class="text-danger social"></span>
                                    <span class="text-danger kt-form__help error social"></span>
                                </div>
					        </div>
                        </div>
                                   `;
//class="btn btn-lg btn-block btn-danger lift text-uppercase remove_button"


            count++;

            //Check maximum number of input fields
            if (x < maxField) {
                x++; //Increment field counter
                y++;
                $(wrapper).append(fieldHTML); //Add field html
            }
            // }
        });
        item_lengthClicked = function(self) {
            $(self).siblings('.item_length_error').html("");
        }
       
      

        $(wrapper).on('click', '.remove_button', function(e) {
            e.preventDefault();
            $(this).parent('div').parent('div').parent('div').remove(); //Remove field html
            y = 1;

            x--; //Decrement field counter
        });
    });
</script>


@endpush