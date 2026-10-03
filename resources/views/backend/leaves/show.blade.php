@extends(backendView('layouts.app'))

@section('title', 'View Leave')

@section('content')
<div class="container-xxl">

    <div class="row align-items-center mb-4">
        <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
            <h3 class="fw-bold mb-0">View Leave Details</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('admin/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/leaves') }}">Leave List</a></li>
                    <li class="breadcrumb-item active">View Leave</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered align-middle">
                
                <tr>
                    <th width="250">Employee Name</th>
                    <td>{{ $leave->user->first_name }} {{ $leave->user->last_name }}</td>
                </tr>

                <tr>
                    <th>Leave Type</th>
                    <td>{{ $leave->leave_type }}</td>
                </tr>

                <tr>
                    <th>Leave Duration Type</th>
                    <td>{{ $leave->leave_duration }}</td>
                </tr>

                <tr>
                    <th>From Date</th>
                    <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('d-m-Y') }}</td>
                </tr>

                <tr>
                    <th>To Date</th>
                    <td>{{ \Carbon\Carbon::parse($leave->to_date)->format('d-m-Y') }}</td>
                </tr>

                <tr>
                    <th>Total Days</th>
                    <td>
                        @if($leave->leave_duration == 'Half Day')
                            0.5 Day
                        @else
                            {{ \Carbon\Carbon::parse($leave->from_date)
                                ->diffInDays(\Carbon\Carbon::parse($leave->to_date)) + 1 }} Day(s)
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Reason</th>
                    <td>{{ $leave->reason ?? '—' }}</td>
                </tr>

                {{-- After the Status <tr> --}}
<tr>
    <th>Leave Balance ({{ \Carbon\Carbon::create()->month($leaveBalance->month)->format('M Y') }})</th>
    <td>
        <div class="d-flex gap-3 flex-wrap">
            <span>
                <span class="text-muted small">Accrued</span><br>
                <strong>{{ number_format($leaveBalance->accrued, 1) }}</strong>
            </span>
            <span>
                <span class="text-muted small">Carried forward</span><br>
                <strong class="text-info">{{ number_format($leaveBalance->carried_forward, 1) }}</strong>
            </span>
            <span>
                <span class="text-muted small">Used</span><br>
                <strong class="text-danger">{{ number_format($leaveBalance->used, 1) }}</strong>
            </span>
            <span>
                <span class="text-muted small">Remaining</span><br>
                <strong class="{{ $leaveBalance->balance <= 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($leaveBalance->balance, 1) }}
                </strong>
            </span>
            @if($leaveBalance->deduction_flagged)
                <span>
                    <span class="text-muted small">LOP</span><br>
                    <span class="badge bg-danger">{{ number_format($leaveBalance->deduction_days, 1) }} day(s)</span>
                </span>
            @endif
        </div>
    </td>
</tr>

                <tr>
                    <th>Status</th>
                    <td>
                        @if(strtolower($leave->status) === 'approved')
                            <span class="badge bg-success px-3 py-2">Approved</span>

                        @elseif(strtolower($leave->status) === 'rejected')
                            <span class="badge bg-danger px-3 py-2">Rejected</span>

                        @elseif(strtolower($leave->status) === 'cancelled')
                            <span class="badge bg-secondary px-3 py-2">Cancelled</span>

                        @else
                            <span class="badge bg-warning px-3 py-2">Pending</span>
                        @endif
                    </td>
                </tr>

                {{-- Reject Reason --}}
                @if(strtolower($leave->status) === 'rejected' && $leave->reject_leave_reason)
                    <tr>
                        <th>Reject Reason</th>
                        <td class="text-danger">
                            {{ $leave->reject_leave_reason }}
                        </td>
                    </tr>
                @endif

            </table>

            {{-- ACTION BUTTONS (only admin, only when pending) --}}
            @if(auth()->user()->role_id == 1 && strtolower($leave->status) === 'pending')
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="btn btn-success approve-btn" data-id="{{ $leave->id }}">
                        <i class="icofont-check-circled me-1"></i> Approve Leave
                    </button>

                    <button type="button" class="btn btn-danger reject-btn" data-id="{{ $leave->id }}">
                        <i class="icofont-close-circled me-1"></i> Reject Leave
                    </button>
                </div>
            @endif

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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('custom_scripts')
<script>
    $(function () {

        // APPROVE
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
                        data: { _token: "{{ csrf_token() }}" },
                        success: function () {
                            Swal.fire('Approved!', 'Leave approved successfully.', 'success')
                                .then(() => window.location.href = "{{ url('admin/leaves') }}");
                        },
                        error: function () {
                            Swal.fire('Error!', 'Failed to approve leave.', 'error');
                        }
                    });
                }
            });
        });

        // REJECT — open modal
        $('.reject-btn').click(function () {
            let id = $(this).data('id');
            $('#reject_leave_id').val(id);
            $('#rejectForm').attr('action', "{{ url('admin/leaves') }}/" + id + "/reject");
            $('#rejectModal').modal('show');
        });

    });
</script>
@endpush