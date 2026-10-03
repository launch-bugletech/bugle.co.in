@extends(backendView('layouts.app'))
@section('title', 'Role View')
@section('content')
<style type="text/css">
    .color-light-success
    {
        color: #dca53e !important;
    }
</style>
<div class="container-xxl">
   <div class="row align-items-center">
      <div class="border-0 mb-4">
         <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
            <h3 class="fw-bold mb-0"> View Group</h3>
            <nav aria-label="breadcrumb">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{url('admin/home')}}">Home</a></li>
                  <li class="breadcrumb-item"><a href="{{url('admin/item-list')}}">Group</a></li>
                  <li class="breadcrumb-item active" aria-current="page">View Group</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Row end  -->
   <h6 class="mb-0 fw-bold ">Group Name</h6>
   <ul class="list-group mt-3">
      <li class="list-group-item d-flex">
         <div class="cs-text flex-fill ps-2">
            <span>{{ $role->name }}</span>
         </div>
      </li>
   </ul>
<div class="customer-like mt-3">
    <h6 class="mb-0 fw-bold">Permissions</h6>
    <ul class="list-group mt-3">
        @if(!empty($rolePermissions))
            @php
                $permissionsChunks = array_chunk($rolePermissions->toArray(), 3);
                $startIndex = 1;
            @endphp
            @foreach($permissionsChunks as $chunk)
                <li class="list-group-item">
                    <div class="row">
                        @foreach($chunk as $index => $val)
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="number border-end pe-2 fw-bold">
                                        <strong class="color-light-success">{{ $startIndex }}</strong>
                                    </div>
                                    <div class="cs-text ps-2">
                                        {{ $val['name'] }}
                                    </div>
                                </div>
                            </div>
                            @php $startIndex++; @endphp <!-- Increment the starting index for the next item -->
                        @endforeach
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
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

@endpush
@push('modals')
@endpush