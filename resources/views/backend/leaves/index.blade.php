@extends(backendView('layouts.app'))

@section('title', 'Leave Index')

@section('content')
    <div class="container-xxl">
        <div class="row align-items-center">
            <div class="border-0 mb-4">
                <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
                    <h3 class="fw-bold mb-0">Leave Management</h3>
                    <a href="{{ url('admin/leaves/create') }}" class="btn btn-dark">
                        <i class="icofont-plus-circle me-2"></i>Add Leave
                    </a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <table id="myProjectTable" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Leave Duration</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Used This Month</th>

                            <th>Status</th>
                            <th>Created at</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($leaves as $leave)
                            <tr>
                                {{-- Auto-incremented serial number --}}
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $leave->user->first_name }} {{ $leave->user->last_name }}</td>
                                <td>{{ $leave->leave_type }}</td>
                                <td>{{ $leave->leave_duration }}</td>
                                <td>{{ $leave->from_date }}</td>
                                <td>{{ $leave->to_date }}</td>
                                <!-- <td>{{ $leave->created_at->format('d-m-Y h:i A') }}</td>                         -->
                                {{-- In <tbody> add after the status badge td --}}
                                    <td>
    @php
        $key = $leave->user_id . '_' . $leave->getLeaveMonth() . '_' . $leave->getLeaveYear();
        $bal = $balances[$key] ?? null;

        // For yearly bucket: get total used across full leave year
        $leaveYearStart = \App\Models\EmployeeLeaveBalance::leaveYearStartFor(
            $leave->getLeaveMonth(),
            $leave->getLeaveYear()
        );
        $yearlyUsed      = \App\Models\EmployeeLeaveBalance::totalUsedInLeaveYear(
            $leave->user_id,
            $leaveYearStart
        );
        $yearlyRemaining = max(0, 12 - $yearlyUsed);
    @endphp

    @if($bal)
        <div class="d-flex flex-column" style="min-width:150px">

            {{-- Yearly bucket summary --}}
            @if(strtolower($leave->status) === 'pending')
                <span class="badge bg-warning text-dark mb-1">
                    Pending · {{ number_format($leave->getLeaveDays(), 1) }} day(s)
                </span>
                <span class="small text-muted">
                    Available: <strong>{{ number_format($yearlyRemaining, 1) }} / 12</strong>
                </span>
            @else
                <span class="badge {{ $yearlyRemaining <= 0 ? 'bg-danger' : ($yearlyRemaining <= 3 ? 'bg-warning text-dark' : 'bg-success') }} mb-1">
                    {{ number_format($yearlyUsed, 1) }} / 12 used
                </span>
                <span class="small text-muted">
                    {{ number_format($yearlyRemaining, 1) }} left this year
                </span>
            @endif

            {{-- This month's usage --}}
            @if($bal->used > 0)
                <div class="small text-muted mt-1">This month: {{ number_format($bal->used, 1) }}</div>
                <div style="font-size:0.75rem; line-height:1.8; color:#6c757d">
                    @if($bal->used_casual > 0)
                        <div><span class="badge bg-primary bg-opacity-10 text-primary">CL</span> {{ number_format($bal->used_casual, 1) }}</div>
                    @endif
                    @if($bal->used_sick > 0)
                        <div><span class="badge bg-danger bg-opacity-10 text-danger">SL</span> {{ number_format($bal->used_sick, 1) }}</div>
                    @endif
                    @if($bal->used_vacation > 0)
                        <div><span class="badge bg-success bg-opacity-10 text-success">VL</span> {{ number_format($bal->used_vacation, 1) }}</div>
                    @endif
                    @if($bal->used_wfh > 0)
                        <div><span class="badge bg-info bg-opacity-10 text-info">WFH</span> {{ number_format($bal->used_wfh, 1) }}</div>
                    @endif
                    @if($bal->used_comp_off > 0)
                        <div><span class="badge bg-warning bg-opacity-10 text-warning">CO</span> {{ number_format($bal->used_comp_off, 1) }}</div>
                    @endif
                    @if($bal->used_maternity > 0)
                        <div><span class="badge bg-secondary bg-opacity-10 text-secondary">ML</span> {{ number_format($bal->used_maternity, 1) }}</div>
                    @endif
                </div>
            @endif

            {{-- LOP warning --}}
            @if($bal->deduction_flagged)
                <span class="badge bg-danger mt-1" style="font-size:0.7rem">
                    ⚠ LOP: {{ number_format($bal->deduction_days, 1) }} day(s)
                </span>
            @endif

        </div>
    @else
        <span class="text-muted">—</span>
    @endif
</td>
                                <td>
                                    @if(strtolower($leave->status) === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif(strtolower($leave->status) === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif(strtolower($leave->status) === 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif

                                </td>
                                <td>{{ $leave->created_at->format('d-m-Y h:i A') }}</td>

                                <td>
                                    {{-- VIEW (visible to all) --}}
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ url('admin/leaves/' . $leave->id) }}"
                                        title="View">
                                        <i class="icofont-eye text-warning"></i>
                                    </a>

                                    {{-- EDIT (hide if role_id = 1) --}}
                                    @if(
                                            auth()->user()->role_id != 1 &&
                                            strtolower($leave->status) === 'pending'
                                        )
                                        <a class="btn btn-outline-secondary btn-sm"
                                            href="{{ url('admin/leaves/' . $leave->id . '/edit') }}" title="Edit">
                                            <i class="icofont-edit text-success"></i>
                                        </a>
                                    @endif

                                    {{-- APPROVE / REJECT (only admin role_id = 1) --}}
                                    @if(strtolower($leave->status) === 'pending' && auth()->user()->role_id == 1)
                                        <button type="button" class="btn btn-success btn-sm approve-btn" data-id="{{ $leave->id }}"
                                            title="Approve Leave">
                                            <i class="icofont-check-circled"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm reject-btn" data-id="{{ $leave->id }}"
                                            title="Reject Leave">
                                            <i class="icofont-close-circled"></i>
                                        </button>
                                    @endif

                                    {{-- DELETE (hide if role_id = 1) --}}
                                    @if(auth()->user()->role_id == 1)
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $leave->id }}"
                                            title="Delete">
                                            <i class="icofont-trash"></i>
                                        </button>
                                    @endif
                                    {{-- CANCEL (only employee & approved leave) --}}
                                   @if(
    (auth()->user()->role_id == 1 && strtolower($leave->status) != 'cancelled')
    ||
    (auth()->user()->role_id != 1 && 
     strtolower($leave->status) === 'approved')
)
    <button type="button"
        class="btn btn-dark btn-sm cancel-btn"
        data-id="{{ $leave->id }}"
        title="Cancel Leave">
        <i class="icofont-close-line-circled"></i>
    </button>
@endif


                                </td>

                            </tr>
                        @endforeach
                    </tbody>


                </table>
            </div>
        </div>

    </div>

    {{-- REJECT MODAL --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="leave_id" id="reject_leave_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Leave</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reject Reason</label>
                            <textarea name="reject_reason" class="form-control" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Leave</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}">
@endpush

@push('scripts')
    <script src="{!! backendAssets('dist/assets/bundles/dataTables.bundle.js') !!}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('custom_scripts')
    <script>
        $(function () {

            $('#myProjectTable').DataTable({
                order: [[0, 'asc']]
            });

            // APPROVE via POST AJAX
            $('.approve-btn').click(function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Approve Leave?',
                    text: 'Are you sure you want to approve this leave?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/leaves') }}/" + id + "/approve",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (res) {
                                Swal.fire('Approved!', 'Leave approved successfully', 'success')
                                    .then(() => { location.reload(); });
                            },
                            error: function () {
                                Swal.fire('Error!', 'Failed to approve leave', 'error');
                            }
                        });
                    }
                });
            });

            // REJECT modal
            $('.reject-btn').click(function () {
                let id = $(this).data('id');
                $('#reject_leave_id').val(id); // set hidden input
                $('#rejectForm').attr('action', "{{ url('admin/leaves') }}/" + id + "/reject");
                $('#rejectModal').modal('show');
            });

            // DELETE
            // DELETE
            $('.delete-btn').click(function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This leave will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/leaves') }}/" + id,
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                Swal.fire('Deleted!', 'Leave deleted successfully', 'success')
                                    .then(() => location.reload());
                            },
                            error: function () {
                                Swal.fire('Error!', 'Failed to delete leave', 'error');
                            }
                        });
                    }
                });
            });
            // CANCEL LEAVE
            $('.cancel-btn').click(function () {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Cancel Leave?',
                    text: 'Are you sure you want to cancel your leave?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, cancel it'
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({
                            url: "{{ url('admin/leaves') }}/" + id + "/cancelled_leave",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (res) {

                                Swal.fire(
                                    'Cancelled!',
                                    'Your leave has been cancelled successfully.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });

                            },
                            error: function () {
                                Swal.fire(
                                    'Error!',
                                    'Unable to cancel leave.',
                                    'error'
                                );
                            }
                        });

                    }
                });

            });


        });
    </script>
@endpush