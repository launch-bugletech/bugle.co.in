@extends(backendView('layouts.app'))

@section('title', ' Group List')

@section('content')
<div class="container-xxl">
	<div class="row align-items-center">
		<div class="border-0 mb-4">
			<div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
				<h3 class="fw-bold mb-0"> Group List</h3>
				@can('add_role')
				<a href="{{route('roles.create')}}" class="btn btn-dark py-2 px-5 btn-set-task w-sm-100"><i class="icofont-plus-circle me-2 fs-6"></i> Add Group</a>
				@endcan
				
			</div>
		</div>
	</div> <!-- Row end  -->
	
<div class="flash-message-container">
                         @if(session('success'))
                             <div class="flash-message">{{ session('success') }}</div>
                         @endif
                         @if (session('error'))
                         <div class="flash-message-error">{{ session('error') }}</div>
                         @endif
                     </div>
	<div class="row g-3 mb-3">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">
						<thead>
							<tr>
								<th>Id</th>
								<th>Group Title</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($roles as $key => $val)
							<tr>
								<td><strong>{{ ++$key }}</strong></td>
								<td>{{ $val->name }}</td>
								<td>
								
									<a href="{{ route('roles.show',$val->id) }}" class="btn btn-outline-secondary"><i class="icofont-eye text-warning"></i></a>
									
									@can('edit_role')
									<a href="{{ route('roles.edit',$val->id) }}" class="btn btn-outline-secondary"><i class="icofont-edit text-success"></i></a>
									@endcan
									
									@can('delete_role')
									{!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $val->id],'style'=>'display:inline']) !!}

                                                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}

                                                    {!! Form::close() !!}
									<!-- <a href="" class="btn btn-outline-secondary"><i class="icofont-ui-delete text-danger"></i></a> -->
									@endcan
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
  <!-- Button trigger modal -->
    

    <!-- Modal -->
    <div class="modal fade" id="exampleModalLive" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLiveLabel">Customer Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this customer group?</p>
                </div>
                <div class="modal-footer">
                    <button type="button"  class="btn btn-warning" id="yes">Yes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<!-- plugin css file  -->
<link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/responsive.dataTables.min.css') !!}">
<link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}">
@endpush

@push('custom_styles')
@endpush

@push('scripts')
<!-- Plugin Js -->
<script src="{!! backendAssets('dist/assets/bundles/dataTables.bundle.js') !!}"></script>
@endpush

@push('custom_scripts')
<script>
	$('#myDataTable')
		.addClass('nowrap')
		.dataTable({
			responsive: true,
			columnDefs: [{
				targets: [-1, -3],
				className: 'dt-body-right'
			}],
         order: [[0, 'desc']]
		});
	$('.deleterow').on('click', function() {
		var tablename = $(this).closest('table').DataTable();
		tablename
			.row($(this)
				.parents('tr'))
			.remove()
			.draw();

	});
</script>
<script>
   $(function(){
   
     // showing modal with effect
     $('.modal-effect').on('click', function(e){
       e.preventDefault();
   
       var effect = $(this).attr('data-effect');
       $('#exampleModalLive').addClass(effect);
       $('#exampleModalLive').modal('show');
     });
   
     // hide modal with effect
     $('#exampleModalLive').on('hidden.bs.modal', function (e) {
       $(this).removeClass (function (index, className) {
           return (className.match (/(^|\s)effect-\S+/g) || []).join(' ');
       });
     });
   });
   
   function deleteModal(id) {
      
     $("#exampleModalLive").modal('show');
        
     $('#yes').on('click', function(event){
         $("#conformation-modal").modal('hide');
         $.ajax({
             url: "{{ url('admin/customer-group/delete') }}"+'/'+id,
             type: 'GET',
             dataType: 'html',
             success:function(response) {
                location.reload();
         	   $('.flash-message').text(response.message).fadeIn().delay(2000).fadeOut();
             }
         });
     });
   }
</script>
@endpush

@push('modals')
@endpush