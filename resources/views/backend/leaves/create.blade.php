@extends(backendView('layouts.app'))

@section('title', 'Create Leave')

@section('content')
    <div class="container-xxl">

        {{-- Header --}}
        <div class="row align-items-center mb-4">
            <div class="card-header py-3 bg-transparent d-flex justify-content-between border-bottom">
                <h3 class="fw-bold mb-0">Create Leave</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('admin/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('admin/leaves') }}">Leave List</a></li>
                        <li class="breadcrumb-item active">Create Leave</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ url('admin/leaves') }}" method="POST">
                    @csrf

                    {{-- Employee selector --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee</label>

                        @if(auth()->user()->role_id == 1)
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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

                    {{-- ════════════════════════════════════════════════════════════
                    EMPLOYEE VIEW — Yearly + Monthly balance panels
                    ════════════════════════════════════════════════════════════ --}}
                    @if(auth()->user()->role_id != 1 && $leaveBalance)

                        @php
                            $leaveYearStart = \App\Models\EmployeeLeaveBalance::leaveYearStartFor(
                                (int) now()->format('n'),
                                (int) now()->format('Y')
                            );
                            $yearlySummary = \App\Models\EmployeeLeaveBalance::annualSummary(
                                auth()->id(),
                                $leaveYearStart
                            );
                            $anyTypeUsed = $leaveBalance->used_casual > 0
                                || $leaveBalance->used_sick > 0
                                || $leaveBalance->used_vacation > 0
                                || $leaveBalance->used_wfh > 0
                                || $leaveBalance->used_comp_off > 0
                                || $leaveBalance->used_maternity > 0;

                            // Monthly used per type — for JS warning
                            $monthlyUsedByType = [
                                'Casual Leave'     => $leaveBalance->used_casual,
                                'Sick Leave'       => $leaveBalance->used_sick,
                                'Vacation Leave'   => $leaveBalance->used_vacation,
                                'Work From Home'   => $leaveBalance->used_wfh,
                                'Compensatory Off' => $leaveBalance->used_comp_off,
                                'Maternity Leave'  => $leaveBalance->used_maternity,
                            ];
                        @endphp

                        {{-- ── Yearly summary ─────────────────────────────── --}}
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
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Annual leave consumption</span>
                                        <span>{{ number_format($yearlySummary['total_used'], 1) }} / {{ number_format($yearlySummary['entitlement'], 1) }} days</span>
                                    </div>
                                    @php
                                        $pct    = min(100, ($yearlySummary['total_used'] / $yearlySummary['entitlement']) * 100);
                                        $lopPct = $yearlySummary['total_lop'] > 0
                                                    ? min(100, ($yearlySummary['total_lop'] / $yearlySummary['entitlement']) * 100)
                                                    : 0;
                                    @endphp
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

                        {{-- ── Monthly balance ──────────────────────────────── --}}
                        <div class="alert alert-info py-3 mb-3">
                            <div class="fw-semibold mb-2 small text-info">
                                This month — {{ \Carbon\Carbon::create()->month($leaveBalance->month)->format('F') }} {{ $leaveBalance->year }}
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
                    {{-- end employee view --}}


                    {{-- ════════════════════════════════════════════════════════════
                    ADMIN VIEW — dynamic panels
                    ════════════════════════════════════════════════════════════ --}}
                    @if(auth()->user()->role_id == 1)

                        <div id="admin-yearly-panel" class="card border-primary mb-3" style="display:none">
                            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">
                                    Yearly Leave Summary
                                    <small class="opacity-75 ms-1" id="yr-period"></small>
                                </span>
                                <span class="badge bg-white text-primary" style="font-size:.7rem">Current Cycle</span>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="rounded p-3 text-center h-100" style="background:#f0f4ff">
                                            <div class="small text-muted mb-1">Entitlement</div>
                                            <div class="fw-bold fs-4 text-primary" id="yr-entitlement">—</div>
                                            <div class="small text-muted">days / year</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="rounded p-3 text-center h-100" style="background:#fff4f4">
                                            <div class="small text-muted mb-1">Used so far</div>
                                            <div class="fw-bold fs-4 text-danger" id="yr-used">—</div>
                                            <div class="small text-muted">days taken</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="rounded p-3 text-center h-100" style="background:#f0fff4">
                                            <div class="small text-muted mb-1">Remaining</div>
                                            <div class="fw-bold fs-4" id="yr-remaining">—</div>
                                            <div class="small text-muted">days left</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="rounded p-3 text-center h-100" id="yr-lop-box" style="background:#f8f9fa">
                                            <div class="small text-muted mb-1">LOP</div>
                                            <div class="fw-bold fs-4 text-muted" id="yr-lop">0.0</div>
                                            <div class="small text-muted" id="yr-lop-label">none so far</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Annual leave consumption</span>
                                        <span id="yr-progress-label">— / 12.0 days</span>
                                    </div>
                                    <div class="progress" style="height:10px;border-radius:6px">
                                        <div class="progress-bar bg-success" id="yr-bar-normal" style="width:0%"></div>
                                        <div class="progress-bar bg-danger"  id="yr-bar-lop"    style="width:0%"></div>
                                    </div>
                                    <div class="small mt-1" id="yr-progress-note"></div>
                                </div>
                                <div class="small text-muted mb-2">Used by type (full year)</div>
                                <div class="row g-2" id="yr-types"></div>
                            </div>
                        </div>

                        <div id="admin-monthly-panel" class="alert alert-info py-3 mb-3" style="display:none">
                            <div class="fw-semibold mb-2 small text-info" id="bal-period"></div>
                            <div class="d-flex flex-wrap gap-4 mb-1">
                                <div class="text-center">
                                    <div class="fw-bold fs-5" id="bal-accrued">—</div>
                                    <div class="small text-muted">Entitlement</div>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold fs-5" id="bal-available">—</div>
                                    <div class="small text-muted">Available</div>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold fs-5 text-danger" id="bal-used">—</div>
                                    <div class="small text-muted">Used this month</div>
                                </div>
                                <div class="text-center" id="bal-lop-wrap" style="display:none">
                                    <div class="fw-bold fs-5 text-danger" id="bal-lop">—</div>
                                    <div class="small text-danger">⚠ LOP days</div>
                                </div>
                            </div>
                            <hr class="my-2" id="bal-type-hr" style="display:none">
                            <div class="small text-muted mb-1" id="bal-type-label" style="display:none">Used by type this month</div>
                            <div class="d-flex flex-wrap gap-2" id="bal-types"></div>
                        </div>

                    @endif
                    {{-- end admin view --}}


                    {{-- ── Leave Type ── with server-side error + live JS warning --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Leave Type</label>
                        <select name="leave_type" id="leave_type"
                                class="form-control @error('leave_type') is-invalid @enderror" required>
                            <option value="">Select Leave Type</option>
                            <option value="Casual Leave"     {{ old('leave_type') == 'Casual Leave'     ? 'selected' : '' }}>Casual Leave</option>
                            <option value="Sick Leave"       {{ old('leave_type') == 'Sick Leave'       ? 'selected' : '' }}>Sick Leave</option>
                            <option value="Vacation Leave"   {{ old('leave_type') == 'Vacation Leave'   ? 'selected' : '' }}>Vacation Leave</option>
                            <option value="Work From Home"   {{ old('leave_type') == 'Work From Home'   ? 'selected' : '' }}>Work From Home</option>
                            <option value="Compensatory Off" {{ old('leave_type') == 'Compensatory Off' ? 'selected' : '' }}>Compensatory Off</option>
                            <option value="Maternity Leave"  {{ old('leave_type') == 'Maternity Leave'  ? 'selected' : '' }}>Maternity Leave</option>
                        </select>

                        {{-- Server-side validation error --}}
                        @error('leave_type')
                            <div class="invalid-feedback d-block">
                                <i class="icofont-warning-alt me-1"></i> {{ $message }}
                            </div>
                        @enderror

                        {{-- Live JS warning (shown before submit) --}}
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
                                <input class="form-check-input" type="radio" name="leave_duration" id="full_day"
                                    value="Full Day" {{ old('leave_duration', 'Full Day') == 'Full Day' ? 'checked' : '' }}>
                                <label class="form-check-label" for="full_day">Full Day</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="leave_duration" id="half_day"
                                    value="Half Day" {{ old('leave_duration') == 'Half Day' ? 'checked' : '' }}>
                                <label class="form-check-label" for="half_day">Half Day</label>
                            </div>
                        </div>
                    </div>

                    {{-- From Date --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control"
                            value="{{ old('from_date') }}" required>
                    </div>

                    {{-- To Date --}}
                    <div class="mb-3" id="to_date_wrapper">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ old('to_date') }}">
                    </div>

                    {{-- Days preview --}}
                    <div class="mb-3" id="days-preview" style="display:none">
                        <div class="alert alert-secondary py-2 mb-0">
                            Applying for <strong id="days-count">—</strong> day(s)
                            <span id="lop-warning" class="text-danger ms-2" style="display:none">
                                — exceeds your available balance, LOP may apply
                            </span>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Reason</label>
                        <textarea name="reason" class="form-control" rows="4">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" id="submit-btn" class="btn btn-dark px-4">
                        <i class="icofont-check-circled me-1"></i> Submit Leave
                    </button>
                    <a href="{{ url('admin/leaves') }}" class="btn btn-outline-secondary ms-2">Cancel</a>

                </form>
            </div>
        </div>

    </div>
@endsection

@push('custom_scripts')
<script>
    const allBalances        = @json($allBalances);
    const allYearlySummaries = @json($allYearlySummaries);

    // Monthly used-by-type for the current user (employee view)
    // For admin this is populated dynamically from allBalances
    const employeeMonthlyUsed = @json($monthlyUsedByType ?? []);
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const fromDateInput      = document.getElementById('from_date');
    const toDateInput        = document.getElementById('to_date');
    const toDateWrapper      = document.getElementById('to_date_wrapper');
    const halfDayRadio       = document.getElementById('half_day');
    const fullDayRadio       = document.getElementById('full_day');
    const daysPreview        = document.getElementById('days-preview');
    const daysCount          = document.getElementById('days-count');
    const lopWarning         = document.getElementById('lop-warning');
    const leaveTypeSelect    = document.getElementById('leave_type');
    const leaveTypeWarning   = document.getElementById('leave-type-warning');
    const leaveTypeWarnText  = document.getElementById('leave-type-warning-text');
    const submitBtn          = document.getElementById('submit-btn');

    const today = new Date().toISOString().split('T')[0];
    fromDateInput.min = today;
    toDateInput.min   = today;

    // ── Toggle To Date ─────────────────────────────────────────────
    function toggleToDate() {
        if (halfDayRadio.checked) {
            toDateWrapper.style.display = 'none';
            toDateInput.value = '';
        } else {
            toDateWrapper.style.display = 'block';
        }
        updateDaysPreview();
    }
    halfDayRadio.addEventListener('change', toggleToDate);
    fullDayRadio.addEventListener('change', toggleToDate);
    toggleToDate();

    fromDateInput.addEventListener('change', function () {
        toDateInput.min = this.value;
        if (toDateInput.value && toDateInput.value < this.value) {
            toDateInput.value = '';
        }
        updateDaysPreview();
        checkLeaveTypeWarning();
    });
    toDateInput.addEventListener('change', updateDaysPreview);

    // ── Days preview ───────────────────────────────────────────────
    function updateDaysPreview() {
        let days = 0;
        if (halfDayRadio.checked) {
            days = 0.5;
        } else if (fromDateInput.value) {
            const from = new Date(fromDateInput.value);
            const to   = toDateInput.value ? new Date(toDateInput.value) : from;
            const diff = Math.round((to - from) / 86400000) + 1;
            days = diff > 0 ? diff : 0;
        }

        if (days > 0) {
            daysPreview.style.display = 'block';
            daysCount.textContent = days % 1 === 0 ? days : days.toFixed(1);
            const avail = getAvailableBalance();
            lopWarning.style.display = (avail !== null && days > avail) ? 'inline' : 'none';
        } else {
            daysPreview.style.display = 'none';
        }
    }

    function getAvailableBalance() {
        @if(auth()->user()->role_id != 1 && $leaveBalance)
            return {{ $leaveBalance->balance }};
        @elseif(auth()->user()->role_id == 1)
            const uid = document.getElementById('user_id')?.value;
            if (uid && allBalances[uid] !== undefined) {
                return parseFloat(allBalances[uid].balance || 0);
            }
            return null;
        @else
            return null;
        @endif
    }

    // ── Monthly leave-type used map ────────────────────────────────
    // Returns the used amount for the selected leave type in the
    // month of the currently selected from_date.
    function getMonthlyUsedForType(leaveType) {
        @if(auth()->user()->role_id == 1)
            const uid = document.getElementById('user_id')?.value;
            if (!uid || !allBalances[uid]) return 0;
            const b = allBalances[uid];
            const map = {
                'Casual Leave'     : parseFloat(b.used_casual    || 0),
                'Sick Leave'       : parseFloat(b.used_sick      || 0),
                'Vacation Leave'   : parseFloat(b.used_vacation  || 0),
                'Work From Home'   : parseFloat(b.used_wfh       || 0),
                'Compensatory Off' : parseFloat(b.used_comp_off  || 0),
                'Maternity Leave'  : parseFloat(b.used_maternity || 0),
            };
            return map[leaveType] || 0;
        @else
            const map = @json($monthlyUsedByType ?? []);
            return parseFloat(map[leaveType] || 0);
        @endif
    }

    // ── Leave-type warning logic ───────────────────────────────────
    function checkLeaveTypeWarning() {
        const leaveType = leaveTypeSelect.value;

        if (!leaveType) {
            hideLeaveTypeWarning();
            return;
        }

        const usedThisMonth = getMonthlyUsedForType(leaveType);

        if (usedThisMonth >= 1.0) {
            // Get month name from from_date or current month
            const dateVal   = fromDateInput.value;
            const monthDate = dateVal ? new Date(dateVal) : new Date();
            const monthName = monthDate.toLocaleString('default', { month: 'long', year: 'numeric' });

            leaveTypeWarnText.textContent =
                `Your ${leaveType} for ${monthName} is already used. ` +
                `You cannot apply the same leave type more than once per month.`;
            leaveTypeWarning.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50');
        } else {
            hideLeaveTypeWarning();
        }
    }

    function hideLeaveTypeWarning() {
        leaveTypeWarning.classList.add('d-none');
        leaveTypeWarnText.textContent = '';
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50');
    }

    leaveTypeSelect.addEventListener('change', checkLeaveTypeWarning);

    // Run on load in case old() pre-filled the type
    checkLeaveTypeWarning();


    // ══════════════════════════════════════════════════════════════
    // ADMIN — dynamic panels
    // ══════════════════════════════════════════════════════════════
    @if(auth()->user()->role_id == 1)

    const userSelect    = document.getElementById('user_id');
    const monthlyPanel  = document.getElementById('admin-monthly-panel');
    const yearlyPanel   = document.getElementById('admin-yearly-panel');

    const balPeriod     = document.getElementById('bal-period');
    const balAccrued    = document.getElementById('bal-accrued');
    const balAvailable  = document.getElementById('bal-available');
    const balUsed       = document.getElementById('bal-used');
    const balLopWrap    = document.getElementById('bal-lop-wrap');
    const balLop        = document.getElementById('bal-lop');
    const balTypes      = document.getElementById('bal-types');
    const balTypeHr     = document.getElementById('bal-type-hr');
    const balTypeLabel  = document.getElementById('bal-type-label');

    const yrPeriod        = document.getElementById('yr-period');
    const yrEntitlement   = document.getElementById('yr-entitlement');
    const yrUsed          = document.getElementById('yr-used');
    const yrRemaining     = document.getElementById('yr-remaining');
    const yrLop           = document.getElementById('yr-lop');
    const yrLopLabel      = document.getElementById('yr-lop-label');
    const yrLopBox        = document.getElementById('yr-lop-box');
    const yrBarNormal     = document.getElementById('yr-bar-normal');
    const yrBarLop        = document.getElementById('yr-bar-lop');
    const yrProgressLabel = document.getElementById('yr-progress-label');
    const yrProgressNote  = document.getElementById('yr-progress-note');
    const yrTypes         = document.getElementById('yr-types');

    const ENTITLEMENT = 12.0;
    const monthNames  = ['January','February','March','April','May','June',
                         'July','August','September','October','November','December'];

    const monthTypeConfig = {
        used_casual    : { label: 'CL',  cls: 'bg-primary bg-opacity-10 text-primary'   },
        used_sick      : { label: 'SL',  cls: 'bg-danger bg-opacity-10 text-danger'     },
        used_vacation  : { label: 'VL',  cls: 'bg-success bg-opacity-10 text-success'   },
        used_wfh       : { label: 'WFH', cls: 'bg-info bg-opacity-10 text-info'         },
        used_comp_off  : { label: 'CO',  cls: 'bg-warning bg-opacity-10 text-warning'   },
        used_maternity : { label: 'ML',  cls: 'bg-secondary bg-opacity-10 text-secondary'},
    };

    const yearTypeConfig = [
        { key: 'Casual Leave',     color: 'bg-primary',   abbr: 'CL'  },
        { key: 'Sick Leave',       color: 'bg-danger',    abbr: 'SL'  },
        { key: 'Vacation Leave',   color: 'bg-success',   abbr: 'VL'  },
        { key: 'Work From Home',   color: 'bg-info',      abbr: 'WFH' },
        { key: 'Compensatory Off', color: 'bg-warning',   abbr: 'CO'  },
        { key: 'Maternity Leave',  color: 'bg-secondary', abbr: 'ML'  },
    ];

    function fmt(n) { return parseFloat(n || 0).toFixed(1); }

    function populateMonthly(b) {
        const monthName = monthNames[(parseInt(b.month) || new Date().getMonth() + 1) - 1];
        balPeriod.textContent    = `This month — ${monthName} ${b.year || new Date().getFullYear()}`;
        balAccrued.textContent   = fmt(b.accrued);

        const avail = parseFloat(b.balance || 0);
        balAvailable.textContent = fmt(avail);
        balAvailable.className   = 'fw-bold fs-5 ' + (avail <= 0 ? 'text-danger' : 'text-success');
        balUsed.textContent      = fmt(b.used);

        if (b.deduction_flagged && parseFloat(b.deduction_days) > 0) {
            balLop.textContent       = fmt(b.deduction_days);
            balLopWrap.style.display = 'block';
        } else {
            balLopWrap.style.display = 'none';
        }

        balTypes.innerHTML = '';
        let anyType = false;
        for (const [col, cfg] of Object.entries(monthTypeConfig)) {
            const val = parseFloat(b[col] || 0);
            if (val > 0) {
                anyType = true;
                const span = document.createElement('span');
                span.className   = `badge ${cfg.cls} px-2 py-1`;
                span.textContent = `${cfg.label} ${val.toFixed(1)}`;
                balTypes.appendChild(span);
            }
        }
        balTypeHr.style.display    = anyType ? 'block' : 'none';
        balTypeLabel.style.display = anyType ? 'block' : 'none';
        monthlyPanel.style.display = 'block';
    }

    function populateYearly(uid) {
        if (!allYearlySummaries[uid]) {
            yearlyPanel.style.display = 'none';
            return;
        }
        const s          = allYearlySummaries[uid];
        const totalUsed  = parseFloat(s.total_used  || 0);
        const totalLop   = parseFloat(s.total_lop   || 0);
        const remaining  = parseFloat(s.balance_remaining || 0);

        yrPeriod.textContent      = s.period_label;
        yrEntitlement.textContent = fmt(s.entitlement);
        yrUsed.textContent        = fmt(totalUsed);
        yrRemaining.textContent   = fmt(remaining);
        yrRemaining.className     = 'fw-bold fs-4 ' + (remaining > 0 ? 'text-success' : 'text-danger');

        yrLop.textContent = fmt(totalLop);
        if (totalLop > 0) {
            yrLop.className           = 'fw-bold fs-4 text-danger';
            yrLopLabel.textContent    = 'days to deduct';
            yrLopLabel.className      = 'small text-danger';
            yrLopBox.style.background = '#fff0f0';
        } else {
            yrLop.className           = 'fw-bold fs-4 text-muted';
            yrLopLabel.textContent    = 'none so far';
            yrLopLabel.className      = 'small text-muted';
            yrLopBox.style.background = '#f8f9fa';
        }

        const pct    = Math.min(100, (totalUsed / ENTITLEMENT) * 100);
        const lopPct = totalLop > 0 ? Math.min(100, (totalLop / ENTITLEMENT) * 100) : 0;
        yrBarNormal.style.width     = (pct - lopPct) + '%';
        yrBarLop.style.width        = lopPct + '%';
        yrProgressLabel.textContent = `${fmt(totalUsed)} / ${fmt(ENTITLEMENT)} days`;
        yrProgressNote.textContent  = totalLop > 0
            ? `⚠ ${fmt(totalLop)} day(s) beyond the 12-leave limit — LOP applies`
            : '✓ Within 12-leave entitlement — no LOP';
        yrProgressNote.className    = 'small mt-1 ' + (totalLop > 0 ? 'text-danger' : 'text-success');

        yrTypes.innerHTML = '';
        for (const tc of yearTypeConfig) {
            const val = parseFloat((s.used_by_type && s.used_by_type[tc.key]) || 0);
            const div = document.createElement('div');
            div.className = 'col-6 col-md-2';
            div.innerHTML = `
                <div class="rounded p-2 text-center border ${val > 0 ? '' : 'opacity-50'}">
                    <span class="badge ${tc.color} mb-1">${tc.abbr}</span>
                    <div class="fw-bold">${val.toFixed(1)}</div>
                    <div class="text-muted" style="font-size:.7rem">${tc.key}</div>
                </div>`;
            yrTypes.appendChild(div);
        }

        yearlyPanel.style.display = 'block';
    }

    userSelect.addEventListener('change', function () {
        const uid = this.value;
        if (uid) {
            if (allBalances[uid])  populateMonthly(allBalances[uid]);
            else monthlyPanel.style.display = 'none';
            populateYearly(uid);
        } else {
            monthlyPanel.style.display = 'none';
            yearlyPanel.style.display  = 'none';
        }
        // Re-check leave type warning when employee changes
        checkLeaveTypeWarning();
        updateDaysPreview();
    });

    if (userSelect.value) {
        userSelect.dispatchEvent(new Event('change'));
    }

    @endif

});
</script>
@endpush