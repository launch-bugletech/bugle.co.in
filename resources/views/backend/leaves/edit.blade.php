@extends(backendView('layouts.app'))

@section('title', 'Edit Leave')

@section('content')
<div class="container-xxl">

    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
            <h3 class="fw-bold mb-0">Edit Leave</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('admin/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/leaves') }}">Leave List</a></li>
                    <li class="breadcrumb-item active">Edit Leave</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- ── Balance summary (for the month this leave falls in) ── --}}
            @if($leaveBalance)
            @php
                $leaveYearStart = \App\Models\EmployeeLeaveBalance::leaveYearStartFor(
                    $leave->getLeaveMonth(),
                    $leave->getLeaveYear()
                );
                $yearlySummary = \App\Models\EmployeeLeaveBalance::annualSummary(
                    $leave->user_id,
                    $leaveYearStart
                );
            @endphp

            {{-- Yearly summary card --}}
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        Yearly Leave Summary
                        <small class="opacity-75 ms-1">{{ $yearlySummary['period_label'] }}</small>
                    </span>
                    <span class="badge bg-white text-primary" style="font-size:.7rem">Current Cycle</span>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="rounded p-3 text-center h-100" style="background:#f0f4ff">
                                <div class="small text-muted mb-1">Entitlement</div>
                                <div class="fw-bold fs-4 text-primary">{{ number_format($yearlySummary['entitlement'], 1) }}</div>
                                <div class="small text-muted">days / year</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rounded p-3 text-center h-100" style="background:#fff4f4">
                                <div class="small text-muted mb-1">Used so far</div>
                                <div class="fw-bold fs-4 text-danger">{{ number_format($yearlySummary['total_used'], 1) }}</div>
                                <div class="small text-muted">days taken</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rounded p-3 text-center h-100" style="background:#f0fff4">
                                <div class="small text-muted mb-1">Remaining</div>
                                <div class="fw-bold fs-4 {{ $yearlySummary['balance_remaining'] > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($yearlySummary['balance_remaining'], 1) }}
                                </div>
                                <div class="small text-muted">days left</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rounded p-3 text-center h-100"
                                style="background:{{ $yearlySummary['total_lop'] > 0 ? '#fff0f0' : '#f8f9fa' }}">
                                <div class="small text-muted mb-1">LOP</div>
                                <div class="fw-bold fs-4 {{ $yearlySummary['total_lop'] > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ number_format($yearlySummary['total_lop'], 1) }}
                                </div>
                                <div class="small {{ $yearlySummary['total_lop'] > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $yearlySummary['total_lop'] > 0 ? 'days to deduct' : 'none so far' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    @php
                        $pct    = min(100, ($yearlySummary['total_used'] / $yearlySummary['entitlement']) * 100);
                        $lopPct = $yearlySummary['total_lop'] > 0
                                    ? min(100, ($yearlySummary['total_lop'] / $yearlySummary['entitlement']) * 100)
                                    : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Annual leave consumption</span>
                            <span>{{ number_format($yearlySummary['total_used'], 1) }} / {{ number_format($yearlySummary['entitlement'], 1) }} days</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:6px">
                            <div class="progress-bar bg-success" style="width:{{ $pct - $lopPct }}%"></div>
                            @if($lopPct > 0)
                                <div class="progress-bar bg-danger" style="width:{{ $lopPct }}%"></div>
                            @endif
                        </div>
                        @if($yearlySummary['total_lop'] > 0)
                            <div class="small text-danger mt-1">⚠ {{ number_format($yearlySummary['total_lop'], 1) }} day(s) beyond the 12-leave limit — LOP applies</div>
                        @else
                            <div class="small text-success mt-1">✓ Within 12-leave entitlement — no LOP</div>
                        @endif
                    </div>

                    {{-- Used by type --}}
                    @php
                        $typeConfig = [
                            'Casual Leave'     => ['bg-primary',   'CL'],
                            'Sick Leave'       => ['bg-danger',    'SL'],
                            'Vacation Leave'   => ['bg-success',   'VL'],
                            'Work From Home'   => ['bg-info',      'WFH'],
                            'Compensatory Off' => ['bg-warning',   'CO'],
                            'Maternity Leave'  => ['bg-secondary', 'ML'],
                        ];
                    @endphp
                    <div class="small text-muted mb-2">Used by type (full year)</div>
                    <div class="row g-2">
                        @foreach($yearlySummary['used_by_type'] as $type => $val)
                            @php [$color, $abbr] = $typeConfig[$type]; @endphp
                            <div class="col-6 col-md-2">
                                <div class="rounded p-2 text-center border {{ $val > 0 ? '' : 'opacity-50' }}">
                                    <span class="badge {{ $color }} mb-1">{{ $abbr }}</span>
                                    <div class="fw-bold">{{ number_format($val, 1) }}</div>
                                    <div class="text-muted" style="font-size:.7rem">{{ $type }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Monthly balance alert --}}
            <div class="alert alert-info py-3 mb-3">
                <div class="fw-semibold mb-2 small text-info">
                    {{ \Carbon\Carbon::create()->month($leaveBalance->month)->format('F') }}
                    {{ $leaveBalance->year }} — monthly balance
                </div>
                <div class="d-flex flex-wrap gap-4">
                    <div class="text-center">
                        <div class="fw-bold fs-5">{{ number_format($leaveBalance->accrued, 1) }}</div>
                        <div class="small text-muted">Entitlement</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-5 {{ $leaveBalance->balance <= 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($leaveBalance->balance, 1) }}
                        </div>
                        <div class="small text-muted">Available</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-danger">{{ number_format($leaveBalance->used, 1) }}</div>
                        <div class="small text-muted">Used this month</div>
                    </div>
                    @if($leaveBalance->deduction_flagged)
                        <div class="text-center">
                            <div class="fw-bold fs-5 text-danger">{{ number_format($leaveBalance->deduction_days, 1) }}</div>
                            <div class="small text-danger">⚠ LOP days</div>
                        </div>
                    @endif
                </div>

                @php
                    $anyTypeUsed = $leaveBalance->used_casual > 0 || $leaveBalance->used_sick > 0
                        || $leaveBalance->used_vacation > 0 || $leaveBalance->used_wfh > 0
                        || $leaveBalance->used_comp_off > 0 || $leaveBalance->used_maternity > 0;
                @endphp
                @if($anyTypeUsed)
                    <hr class="my-2">
                    <div class="small text-muted mb-1">Used by type this month</div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($leaveBalance->used_casual > 0)
                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">CL {{ number_format($leaveBalance->used_casual, 1) }}</span>
                        @endif
                        @if($leaveBalance->used_sick > 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">SL {{ number_format($leaveBalance->used_sick, 1) }}</span>
                        @endif
                        @if($leaveBalance->used_vacation > 0)
                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">VL {{ number_format($leaveBalance->used_vacation, 1) }}</span>
                        @endif
                        @if($leaveBalance->used_wfh > 0)
                            <span class="badge bg-info bg-opacity-10 text-info px-2 py-1">WFH {{ number_format($leaveBalance->used_wfh, 1) }}</span>
                        @endif
                        @if($leaveBalance->used_comp_off > 0)
                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">CO {{ number_format($leaveBalance->used_comp_off, 1) }}</span>
                        @endif
                        @if($leaveBalance->used_maternity > 0)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">ML {{ number_format($leaveBalance->used_maternity, 1) }}</span>
                        @endif
                    </div>
                @endif
            </div>
            @endif

            <form action="{{ url('admin/leaves/'.$leave->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Employee --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Employee</label>
                    @if(auth()->user()->role_id == 1)
                        <select name="user_id" class="form-control" required>
                            <option value="">Select Employee</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ $leave->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control"
                               value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" readonly>
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    @endif
                </div>

                {{-- Leave Type --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Leave Type</label>
                    <select name="leave_type" id="leave_type"
                            class="form-control @error('leave_type') is-invalid @enderror" required>
                        <option value="">Select Leave Type</option>
                        <option value="Casual Leave"     {{ $leave->leave_type == 'Casual Leave'     ? 'selected' : '' }}>Casual Leave</option>
                        <option value="Sick Leave"       {{ $leave->leave_type == 'Sick Leave'       ? 'selected' : '' }}>Sick Leave</option>
                        <option value="Vacation Leave"   {{ $leave->leave_type == 'Vacation Leave'   ? 'selected' : '' }}>Vacation Leave</option>
                        <option value="Work From Home"   {{ $leave->leave_type == 'Work From Home'   ? 'selected' : '' }}>Work From Home</option>
                        <option value="Compensatory Off" {{ $leave->leave_type == 'Compensatory Off' ? 'selected' : '' }}>Compensatory Off</option>
                        <option value="Maternity Leave"  {{ $leave->leave_type == 'Maternity Leave'  ? 'selected' : '' }}>Maternity Leave</option>
                    </select>

                    {{-- Server-side error --}}
                    @error('leave_type')
                        <div class="invalid-feedback d-block">
                            <i class="icofont-warning-alt me-1"></i> {{ $message }}
                        </div>
                    @enderror

                    {{-- Live JS warning --}}
                    <div id="leave-type-warning" class="alert alert-warning py-2 mt-2 mb-0 d-none">
                        <i class="icofont-warning-alt me-1"></i>
                        <span id="leave-type-warning-text"></span>
                    </div>
                </div>

                {{-- Leave Duration --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Leave Duration</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="leave_duration"
                                   id="full_day" value="Full Day"
                                   {{ $leave->leave_duration == 'Full Day' ? 'checked' : '' }}>
                            <label class="form-check-label" for="full_day">Full Day</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="leave_duration"
                                   id="half_day" value="Half Day"
                                   {{ $leave->leave_duration == 'Half Day' ? 'checked' : '' }}>
                            <label class="form-check-label" for="half_day">Half Day</label>
                        </div>
                    </div>
                </div>

                {{-- From Date --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                           value="{{ $leave->from_date }}" required>
                </div>

                {{-- To Date --}}
                <div class="mb-3" id="to_date_wrapper">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control"
                           value="{{ $leave->to_date }}">
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason</label>
                    <textarea name="reason" class="form-control" rows="4">{{ $leave->reason }}</textarea>
                </div>

                <button type="submit" id="submit-btn" class="btn btn-success">
                    Update Leave
                </button>
                <a href="{{ url('admin/leaves') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>

</div>
@endsection

@push('custom_scripts')
<script>
    // The ORIGINAL leave type (so we don't warn when they keep the same type)
    const originalLeaveType = "{{ $leave->leave_type }}";

    // Monthly used-by-type for this leave's month
    // If the selected type matches originalLeaveType, we subtract its own usage
    // so the warning doesn't fire for the leave being edited
    const monthlyUsedByType = @json($monthlyUsedByType);
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const fromDate          = document.getElementById('from_date');
    const toDate            = document.getElementById('to_date');
    const toDateWrapper     = document.getElementById('to_date_wrapper');
    const halfDay           = document.getElementById('half_day');
    const fullDay           = document.getElementById('full_day');
    const leaveTypeSelect   = document.getElementById('leave_type');
    const leaveTypeWarning  = document.getElementById('leave-type-warning');
    const leaveTypeWarnText = document.getElementById('leave-type-warning-text');
    const submitBtn         = document.getElementById('submit-btn');

    // ── To Date toggle ─────────────────────────────────────────────
    function toggleToDate() {
        if (halfDay.checked) {
            toDateWrapper.style.display = 'none';
            toDate.value = '';
        } else {
            toDateWrapper.style.display = 'block';
        }
    }
    toggleToDate();
    halfDay.addEventListener('change', toggleToDate);
    fullDay.addEventListener('change', toggleToDate);

    fromDate.addEventListener('change', function () {
        toDate.min = this.value;
        if (toDate.value && toDate.value < this.value) {
            toDate.value = '';
        }
        checkLeaveTypeWarning();
    });

    // ── Leave-type warning ─────────────────────────────────────────
    function checkLeaveTypeWarning() {
        const selectedType = leaveTypeSelect.value;
        if (!selectedType) {
            hideWarning();
            return;
        }

        let usedThisMonth = parseFloat(monthlyUsedByType[selectedType] || 0);

        // If the selected type is the SAME as the original leave type,
        // subtract this leave's own usage so we don't block editing it.
        // (The balance row already includes this leave's deduction if approved.)
        if (selectedType === originalLeaveType) {
            // Don't warn — the usage we see IS this leave
            hideWarning();
            return;
        }

        if (usedThisMonth >= 1.0) {
            const dateVal   = fromDate.value;
            const monthDate = dateVal ? new Date(dateVal) : new Date();
            const monthName = monthDate.toLocaleString('default', { month: 'long', year: 'numeric' });

            leaveTypeWarnText.textContent =
                `Your ${selectedType} for ${monthName} is already used. ` +
                `You cannot apply the same leave type more than once per month.`;
            leaveTypeWarning.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50');
        } else {
            hideWarning();
        }
    }

    function hideWarning() {
        leaveTypeWarning.classList.add('d-none');
        leaveTypeWarnText.textContent = '';
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50');
    }

    leaveTypeSelect.addEventListener('change', checkLeaveTypeWarning);

    // Run on load to catch any pre-filled old() errors
    checkLeaveTypeWarning();

});
</script>
@endpush