@extends(backendView('layouts.app'))

@section('title', 'Dashboard')

@section('content')
<div class="container-xxl">

    @php
    $user = auth()->user();
    $isAdmin = $user->role_id == 1;
    @endphp

    {{-- ================= ADMIN DASHBOARD ================= --}}
    @if($isAdmin)

    {{-- Admin Tasks Section with Google Tasks Features --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="tasks-summary-card admin-tasks-card">
                <div class="tasks-summary-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="icofont-star"></i>
                        <h6 class="mb-0">Tasks Created on <span id="adminTasksDateDisplay"></span></h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="task-stats-mini">
                            <span class="stat-badge new" id="adminNewTasksCount">0 New</span>
                            <span class="stat-badge progress" id="adminProgressTasksCount">0 In Progress</span>
                            <span class="stat-badge completed" id="adminCompletedTasksCount">0 Completed</span>
                        </div>
                        <button class="btn btn-sm btn-primary" id="addAdminTaskBtn">
                            <i class="icofont-plus"></i> Add Task
                        </button>
                    </div>
                </div>
                <div class="tasks-summary-body" id="adminTasksSummaryBody">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted mb-0 small">Loading admin tasks...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Employee Tasks Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="tasks-summary-card">
                <div class="tasks-summary-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="icofont-users"></i>
                        <h6 class="mb-0">Employee Tasks Created on <span id="tasksDateDisplay"></span></h6>
                    </div>
                    <div class="task-stats-mini">
                        <span class="stat-badge new" id="employeeNewTasksCount">0 New</span>
                        <span class="stat-badge progress" id="employeeProgressTasksCount">0 In Progress</span>
                        <span class="stat-badge completed" id="employeeCompletedTasksCount">0 Completed</span>
                    </div>
                </div>
                <div class="tasks-summary-body" id="employeeTasksSummaryBody">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted mb-0 small">Loading employee tasks...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 📅 Date-wise Attendance Section --}}
    <div class="card modern-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>📅 Daily Attendance - <span id="selectedDateDisplay">Today</span></h6>
            <div class="date-controls">
                <button class="btn btn-sm btn-outline-primary" id="prevDate">
                    <i class="icofont-rounded-left"></i> Previous
                </button>
                <input type="date" class="form-control form-control-sm d-inline-block mx-2" id="attendanceDate"
                    value="{{ date('Y-m-d') }}" style="width: auto;">
                <button class="btn btn-sm btn-outline-primary" id="nextDate">
                    Next <i class="icofont-rounded-right"></i>
                </button>
                <button class="btn btn-sm btn-primary" id="todayBtn">
                    <i class="icofont-ui-calendar"></i> Today
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Summary Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="attendance-stat present-stat">
                        <div class="stat-icon">
                            <i class="icofont-check-circled"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="presentCount">0</h4>
                            <span>Present</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="attendance-stat absent-stat">
                        <div class="stat-icon">
                            <i class="icofont-close-circled"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="absentCount">0</h4>
                            <span>Absent</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="attendance-stat late-stat">
                        <div class="stat-icon">
                            <i class="icofont-clock-time"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="lateCount">0</h4>
                            <span>Late</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="attendance-stat halfday-stat">
                        <div class="stat-icon">
                            <i class="icofont-ui-calendar"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="halfdayCount">0</h4>
                            <span>Half Day</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Attendance Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="attendanceTable">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Working Hours</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading attendance data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Task Quick Add Modal --}}
    <div class="modal fade" id="quickAddTaskModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="icofont-plus-circle"></i> Add Task
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickAddTaskForm" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quickTaskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="quickTaskDescription" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" id="quickTaskPriority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="quickTaskDueDate">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQuickTaskBtn">
                        <i class="icofont-check"></i> Add Task
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Task Edit Modal --}}
    <div class="modal fade" id="editTaskModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="icofont-edit"></i> Edit Task
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editTaskForm">
                        <input type="hidden" id="editTaskId">
                        <div class="mb-3">
                            <label class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTaskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editTaskDescription" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" id="editTaskPriority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="editTaskStatus">
                                    <option value="new">New</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editTaskDueDate">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateTaskBtn">
                        <i class="icofont-check"></i> Update Task
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= EMPLOYEE DASHBOARD ================= --}}
    @else

    {{-- Employee Stats Row --}}
    <div class="row g-4 mb-4">
        {{-- Recent Tasks Card --}}
        <div class="col-lg-8">
            <div class="card employee-card">
                <div class="employee-card-header">
                    <div class="header-content">
                        <div class="icon-wrapper tasks-icon">
                            <i class="icofont-tasks-alt"></i>
                        </div>
                        <div>
                            <h6 class="card-title-employee">My Recent Tasks</h6>
                            <p class="card-subtitle-employee">Stay on top of your work</p>
                        </div>
                    </div>
                    <a href="{{ route('tasks.board') }}" class="btn-view-tasks">
                        View All <i class="icofont-arrow-right"></i>
                    </a>
                </div>

                <div class="employee-card-body">
                    <ul class="tasks-list" id="recentTasks">
                        <li class="task-item loading">
                            <div class="task-skeleton"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Working Hours Card --}}
        <div class="col-lg-4">
            <div class="card employee-card working-hours-card">
                <div class="employee-card-header">
                    <div class="header-content">
                        <div class="icon-wrapper hours-icon">
                            <i class="icofont-clock-time"></i>
                        </div>
                        <div>
                            <h6 class="card-title-employee">Working Hours</h6>
                            <p class="card-subtitle-employee">This month</p>
                        </div>
                    </div>
                </div>

                <div class="employee-card-body text-center">
                    <div class="hours-display">
                        <div class="hours-circle">
                            <svg class="progress-ring" width="140" height="140">
                                <circle class="progress-ring-circle-bg" cx="70" cy="70" r="60"></circle>
                                <circle class="progress-ring-circle" cx="70" cy="70" r="60" id="hoursProgressCircle">
                                </circle>
                            </svg>
                            <div class="hours-text">
                                <h2 class="hours-number" id="workingHours">0</h2>
                                <span class="hours-label">hours</span>
                            </div>
                        </div>
                    </div>
                    <div class="hours-stats">
                        <div class="stat-item">
                            <span class="stat-label">Target</span>
                            <span class="stat-value" id="targetHours">0h</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-label">Remaining</span>
                            <span class="stat-value" id="remainingHours">0h</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>📅 Attendance Calendar - <span id="currentMonth"></span></h6>
            <div class="calendar-nav">
                <button class="btn btn-sm btn-primary" id="prevMonth">◀</button>
                <button class="btn btn-sm btn-primary" id="nextMonth">▶</button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="calendar-container">
                <div class="calendar-grid" id="calendarGrid"></div>
                <div class="holiday-legend mt-3">
                    <small><span class="legend-present"></span> Present</small>
                    <small><span class="legend-absent"></span> Absent</small>
                    <small><span class="legend-late"></span> Late</small>
                    <small><span class="legend-sunday"></span> Sunday</small>
                    <small><span class="legend-weekoff"></span> 2nd/4th Sat</small>
                    <small><span class="legend-holiday"></span> Holiday</small>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// =============================================
// INDIAN HOLIDAYS 2026 — National + Gazetted
// =============================================
const holidays = {
    // National Holidays
    '2026-01-26': 'Republic Day',
    '2026-08-15': 'Independence Day',
    '2026-10-02': 'Gandhi Jayanti',

    // January
    '2026-01-14': 'Makar Sankranti',
    '2026-01-23': 'Netaji Jayanti',

    // February
    '2026-02-19': 'Shivaji Jayanti',
    '2026-02-26': 'Maha Shivaratri',

    // March
    '2026-03-04': 'Holi',
    '2026-03-05': 'Holi 2nd Day',
    '2026-03-21': 'Eid ul-Fitr',
    '2026-03-22': 'Eid ul-Fitr Holiday',
    '2026-03-26': 'Rama Navami',
    '2026-03-31': 'Mahavir Jayanti',

    // April
    '2026-04-03': 'Good Friday',
    '2026-04-05': 'Easter Sunday',
    '2026-04-14': 'Ambedkar Jayanti / Baisakhi',
    '2026-04-15': 'Bengali New Year',

    // May
    '2026-04-30': 'Buddha Purnima',
    '2026-05-01': 'Maharashtra Day / Labour Day',

    // June
    '2026-05-27': 'Eid ul-Adha (Bakrid)',
    '2026-05-28': 'Bakrid Holiday',
    '2026-06-26': 'Muharram',

    // August
    '2026-08-15': 'Independence Day',
    '2026-08-25': 'Raksha Bandhan',
    '2026-08-26': 'Milad-un-Nabi',

    // September
    '2026-09-04': 'Janmashtami',
    '2026-09-11': 'Ganesh Chaturthi',
    '2026-09-20': 'Ganesh Visarjan',

    // October
    '2026-10-16': 'Navratri Begins',
    '2026-10-20': 'Dussehra',
    '2026-10-28': 'Valmiki Jayanti',

    // November
    '2026-11-04': 'Karva Chauth',
    '2026-11-07': 'Dhanteras',
    '2026-11-08': 'Diwali',
    '2026-11-09': 'Govardhan Puja',
    '2026-11-10': 'Bhai Dooj',
    '2026-11-24': 'Guru Nanak Jayanti',

    // December
    '2026-12-25': 'Christmas',
    '2026-12-26': 'Christmas Holiday',
};

// =============================================
// HELPER — is this the 2nd or 4th Saturday?
// =============================================
function isWeekOffSaturday(year, month, day) {
    let satCount = 0;
    for (let d = 1; d <= day; d++) {
        if (new Date(year, month, d).getDay() === 6) {
            satCount++;
        }
    }
    return satCount === 2 || satCount === 4;
}

let currentDate = new Date();
let attendanceData = {};
let totalWorkingHours = 0;

async function fetchAttendance(year, month) {
    try {
        const response = await fetch(`/admin/attendance?year=${year}&month=${month + 1}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        attendanceData = {};
        totalWorkingHours = 0;

        if (data.success && data.attendance) {
            data.attendance.forEach(record => {
                attendanceData[record.date] = {
                    status: record.status,
                    time_in: record.clock_in_at,
                    time_out: record.clock_out_at
                };
                totalWorkingHours += Number(record.working_hours || 0);
            });
        }

        const targetHours = calculateTargetHours(year, month);

        const hoursElement = document.getElementById('workingHours');
        const targetElement = document.getElementById('targetHours');
        const progressCircle = document.getElementById('hoursProgressCircle');
        const remainingElement = document.getElementById('remainingHours');

        if (hoursElement) {
            hoursElement.textContent = totalWorkingHours.toFixed(1);

            if (targetElement) targetElement.textContent = targetHours + 'h';

            if (progressCircle) {
                const remaining = Math.max(0, targetHours - totalWorkingHours);
                const percentage = Math.min(100, (totalWorkingHours / targetHours) * 100);
                const radius = 60;
                const circumference = 2 * Math.PI * radius;
                const offset = circumference - (percentage / 100) * circumference;

                if (remainingElement) remainingElement.textContent = remaining.toFixed(1) + 'h';
                progressCircle.style.strokeDashoffset = offset;
            }
        }

    } catch (error) {
        console.error('Error fetching attendance:', error);
    }
}

// =============================================
// TARGET HOURS — excludes Sundays, holidays,
//                and 2nd/4th Saturdays
// =============================================
function calculateTargetHours(year, month) {
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    let workingDays = 0;

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayOfWeek = new Date(year, month, day).getDay();
        const isSunday = dayOfWeek === 0;
        const isWeekOff = dayOfWeek === 6 && isWeekOffSaturday(year, month, day);
        const isHoliday = !!holidays[dateStr];

        if (!isSunday && !isWeekOff && !isHoliday) {
            workingDays++;
        }
    }

    return workingDays * 8;
}

// =============================================
// RENDER CALENDAR
// =============================================
function renderCalendar(date) {
    const year = date.getFullYear();
    const month = date.getMonth();

    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    let html = '<div class="calendar-weekdays">';
    ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(d => {
        html += `<div class="weekday">${d}</div>`;
    });
    html += '</div><div class="calendar-days">';

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="calendar-day empty"></div>';
    }

    const today = new Date();
    const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayOfWeek = new Date(year, month, day).getDay();
        const isSunday = dayOfWeek === 0;
        const isSaturday = dayOfWeek === 6;
        const isWeekOff = isSaturday && isWeekOffSaturday(year, month, day);
        const isHoliday = holidays[dateStr];
        const attendance = attendanceData[dateStr];
        const isToday = isCurrentMonth && today.getDate() === day;
        const isFuture = new Date(year, month, day) > today;

        let classes = 'calendar-day';
        let displayText = day;
        let title = '';

        if (isHoliday) {
            // Holidays take top priority (can override working Saturday)
            classes += ' holiday';
            title = isHoliday;
            displayText = `<div class="day-number">${day}</div><div class="holiday-name">${isHoliday}</div>`;

        } else if (isSunday) {
            classes += ' sunday';
            title = 'Sunday';

        } else if (isWeekOff) {
            // 2nd and 4th Saturday = week off
            classes += ' week-off-saturday';
            title = 'Week Off (2nd/4th Saturday)';
            displayText = `<div class="day-number">${day}</div><div class="holiday-name">Week Off</div>`;

        } else if (attendance && !isFuture) {
            // Normal working day with attendance record
            classes += ` attendance-${attendance.status}`;
            title = attendance.status.charAt(0).toUpperCase() + attendance.status.slice(1);
            if (attendance.time_in) title += ` — In: ${attendance.time_in}`;
            if (attendance.time_out) title += `  Out: ${attendance.time_out}`;

            const statusLabels = {
                present: 'Present',
                absent: 'Absent',
                late: 'Late',
                half_day: 'Half Day'
            };
            displayText = `
                <div class="day-number">${day}</div>
                <div class="attendance-status">${statusLabels[attendance.status] ?? attendance.status}</div>
            `;

        } else if (!isFuture && !isSunday && !isHoliday && !isWeekOff) {
            // Past working day with no record = absent
            classes += ' attendance-absent';
            title = 'Absent';
            displayText = `<div class="day-number">${day}</div><div class="attendance-status">Absent</div>`;
        }

        if (isToday) classes += ' today';

        html += `<div class="${classes}" title="${title}">${displayText}</div>`;
    }

    html += '</div>';
    document.getElementById('calendarGrid').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', async function() {
    @if(!$isAdmin)
    await fetchAttendance(currentDate.getFullYear(), currentDate.getMonth());
    renderCalendar(currentDate);

    // Refresh every minute if viewing current month
    setInterval(function() {
        const now = new Date();
        if (currentDate.getFullYear() === now.getFullYear() &&
            currentDate.getMonth() === now.getMonth()) {
            renderCalendar(currentDate);
        }
    }, 60000);

    document.getElementById('prevMonth').addEventListener('click', async function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        await fetchAttendance(currentDate.getFullYear(), currentDate.getMonth());
        renderCalendar(currentDate);
    });

    document.getElementById('nextMonth').addEventListener('click', async function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        await fetchAttendance(currentDate.getFullYear(), currentDate.getMonth());
        renderCalendar(currentDate);
    });
    @endif
});
</script>

@if(!$isAdmin)
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route('
            tasks.recent ') }}')
        .then(res => res.json())
        .then(res => {
            const list = document.getElementById('recentTasks');
            list.innerHTML = '';

            if (!res.success || !res.task) {
                list.innerHTML = `
                        <li class="task-item empty">
                            <div class="empty-state">
                                <i class="icofont-tasks"></i>
                                <span>No task assigned yet</span>
                            </div>
                        </li>`;
                return;
            }

            const task = res.task;
            const statusColors = {
                'new': 'status-todo',
                'in_progress': 'status-progress',
                'completed': 'status-completed',
                'blocked': 'status-blocked'
            };
            const statusIcons = {
                'new': 'icofont-clock-time',
                'in_progress': 'icofont-spinner',
                'completed': 'icofont-check-circled',
                'blocked': 'icofont-warning'
            };
            const statusClass = statusColors[task.status] || 'status-todo';
            const statusIcon = statusIcons[task.status] || 'icofont-clock-time';

            list.innerHTML = `
                    <li class="task-item">
                        <a href="{{ route('tasks.board') }}?task=${task.id}" class="task-link">
                            <div class="task-icon ${statusClass}">
                                <i class="${statusIcon}"></i>
                            </div>
                            <div class="task-content">
                                <h6 class="task-title">${task.title}</h6>
                                <span class="task-status ${statusClass}">
                                    ${task.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                            </div>
                            <div class="task-arrow">
                                <i class="icofont-rounded-right"></i>
                            </div>
                        </a>
                    </li>
                `;
        })
        .catch(() => {
            document.getElementById('recentTasks').innerHTML = `
                    <li class="task-item error">
                        <div class="empty-state error">
                            <i class="icofont-warning"></i>
                            <span>Failed to load tasks</span>
                        </div>
                    </li>`;
        });

    function updateHoursProgress() {
        const hoursElement = document.getElementById('workingHours');
        const remainingElement = document.getElementById('remainingHours');
        const targetElement = document.getElementById('targetHours');
        const progressCircle = document.getElementById('hoursProgressCircle');

        if (hoursElement && progressCircle) {
            const currentHours = parseFloat(hoursElement.textContent) || 0;
            const targetHours = calculateTargetHours(currentDate.getFullYear(), currentDate.getMonth());
            const remaining = Math.max(0, targetHours - currentHours);
            const percentage = Math.min(100, (currentHours / targetHours) * 100);
            const radius = 60;
            const circumference = 2 * Math.PI * radius;
            const offset = circumference - (percentage / 100) * circumference;

            if (targetElement) targetElement.textContent = targetHours + 'h';
            if (remainingElement) remainingElement.textContent = remaining.toFixed(1) + 'h';

            progressCircle.style.strokeDashoffset = offset;
        }
    }

    setTimeout(updateHoursProgress, 500);
});
</script>
@endif

{{-- Admin Date-wise Attendance & Tasks Script --}}
@if($isAdmin)
<script>
let selectedDate = new Date();
let adminTasksData = [];

const quickAddModal = new bootstrap.Modal(document.getElementById('quickAddTaskModal'));
const editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));

function formatDateDisplay(date) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const compareDate = new Date(date);
    compareDate.setHours(0, 0, 0, 0);

    if (compareDate.getTime() === today.getTime()) return 'Today';

    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    if (compareDate.getTime() === yesterday.getTime()) return 'Yesterday';

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    if (compareDate.getTime() === tomorrow.getTime()) return 'Tomorrow';

    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

async function fetchDailyAttendance(date) {
    const dateStr = date.toISOString().split('T')[0];
    try {
        const response = await fetch(`/admin/attendance/daily?date=${dateStr}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        if (data.success) {
            updateAttendanceUI(data);
        } else {
            showError('Failed to load attendance data');
        }
    } catch (error) {
        console.error('Error fetching daily attendance:', error);
        showError('Error loading attendance data');
    }
    fetchDailyTasks(date);
}

async function fetchDailyTasks(date) {
    const dateStr = date.toISOString().split('T')[0];
    try {
        const response = await fetch(`/admin/tasks/daily?date=${dateStr}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        if (data.success) updateTasksUI(data);
    } catch (error) {
        console.error('Error fetching tasks:', error);
    }
}

function updateTasksUI(data) {
    document.getElementById('employeeNewTasksCount').textContent = `${data.employee_stats.new || 0} New`;
    document.getElementById('employeeProgressTasksCount').textContent =
        `${data.employee_stats.in_progress || 0} In Progress`;
    document.getElementById('employeeCompletedTasksCount').textContent =
        `${data.employee_stats.completed || 0} Completed`;

    document.getElementById('adminNewTasksCount').textContent = `${data.admin_stats.new || 0} New`;
    document.getElementById('adminProgressTasksCount').textContent = `${data.admin_stats.in_progress || 0} In Progress`;
    document.getElementById('adminCompletedTasksCount').textContent = `${data.admin_stats.completed || 0} Completed`;

    const tasksDateDisplay = document.getElementById('tasksDateDisplay');
    const adminTasksDateDisplay = document.getElementById('adminTasksDateDisplay');
    if (tasksDateDisplay) tasksDateDisplay.textContent = formatDateDisplay(selectedDate);
    if (adminTasksDateDisplay) adminTasksDateDisplay.textContent = formatDateDisplay(selectedDate);

    renderTasksSection(data.employee_tasks, 'employeeTasksSummaryBody', 'employee');
    renderAdminTasksSection(data.admin_tasks, 'adminTasksSummaryBody');
}

function renderTasksSection(tasks, bodyId, taskType) {
    const tasksBody = document.getElementById(bodyId);

    if (!tasks || tasks.length === 0) {
        tasksBody.innerHTML = `
            <div class="text-center py-4">
                <i class="icofont-tasks" style="font-size: 2.5rem; color: #ddd;"></i>
                <p class="mt-2 text-muted mb-0">No ${taskType} tasks created for this date</p>
            </div>`;
        return;
    }

    let html = '<div class="tasks-grid">';

    tasks.forEach(task => {
        const priorityClass = {
            low: 'priority-low',
            medium: 'priority-medium',
            high: 'priority-high'
        } [task.priority] || 'priority-medium';
        const statusClass = {
            new: 'status-new',
            in_progress: 'status-progress',
            completed: 'status-completed'
        } [task.status] || 'status-new';
        const statusIcon = {
            new: 'icofont-clock-time',
            in_progress: 'icofont-spinner',
            completed: 'icofont-check-circled'
        } [task.status] || 'icofont-clock-time';
        const statusLabel = {
            new: 'New',
            in_progress: 'In Progress',
            completed: 'Completed'
        } [task.status] || task.status;
        const priorityLabel = task.priority.charAt(0).toUpperCase() + task.priority.slice(1);

        html += `
            <div class="task-card ${statusClass}">
                <div class="task-card-header">
                    <div class="task-status-badge ${statusClass}">
                        <i class="${statusIcon}"></i>
                        ${statusLabel}
                    </div>
                    <span class="task-priority-badge ${priorityClass}">${priorityLabel}</span>
                </div>
                <div class="task-card-body">
                    <h6 class="task-card-title">${task.title}</h6>
                    ${task.description ? `<p class="task-card-desc">${task.description.substring(0, 100)}${task.description.length > 100 ? '...' : ''}</p>` : ''}
                    <div class="task-card-footer">
                        <div class="task-assignee">
                            <div class="assignee-avatar">
                                ${task.employee_initials || task.employee_name.charAt(0).toUpperCase()}
                            </div>
                            <span class="assignee-name">${task.employee_name}</span>
                        </div>
                        ${task.created_at ? `
                            <div class="task-created-at mt-1">
                                <small class="text-muted">
                                    <i class="icofont-clock-time"></i> Created: ${task.created_at}
                                </small>
                            </div>` : ''}
                    </div>
                </div>
            </div>`;
    });

    html += '</div>';
    tasksBody.innerHTML = html;
}

function renderAdminTasksSection(tasks, bodyId) {
    const tasksBody = document.getElementById(bodyId);

    if (!tasks || tasks.length === 0) {
        tasksBody.innerHTML = `
            <div class="text-center py-4">
                <i class="icofont-tasks" style="font-size: 2.5rem; color: #ddd;"></i>
                <p class="mt-2 text-muted mb-0">No admin tasks created for this date</p>
            </div>`;
        return;
    }

    adminTasksData = tasks;

    const grouped = {
        new: tasks.filter(t => t.status === 'new'),
        in_progress: tasks.filter(t => t.status === 'in_progress'),
        completed: tasks.filter(t => t.status === 'completed')
    };

    const statusLabels = {
        new: 'To Do',
        in_progress: 'In Progress',
        completed: 'Completed'
    };

    let html = '<div class="google-tasks-container">';

    Object.entries(grouped).forEach(([status, statusTasks]) => {
        if (statusTasks.length === 0) return;

        html += `
            <div class="task-status-group">
                <div class="task-status-header">${statusLabels[status]} (${statusTasks.length})</div>
                <div class="google-tasks-list">`;

        statusTasks.forEach(task => {
            const priorityClass = {
                low: 'priority-low',
                medium: 'priority-medium',
                high: 'priority-high'
            } [task.priority] || 'priority-medium';
            const statusClass = {
                new: 'status-new',
                in_progress: 'status-progress',
                completed: 'status-completed'
            } [task.status] || 'status-new';
            const isCompleted = task.status === 'completed';

            html += `
                <div class="google-task-item ${statusClass} ${isCompleted ? 'completed' : ''}" data-task-id="${task.id}">
                    <div class="task-checkbox-wrapper">
                        <input type="checkbox"
                               class="task-checkbox"
                               ${isCompleted ? 'checked' : ''}
                               onchange="toggleTaskCompletion(${task.id}, this.checked)">
                    </div>
                    <div class="task-content-wrapper" onclick="openTaskDetails(${task.id})">
                        <div class="task-title-row">
                            <span class="task-title-text ${isCompleted ? 'completed' : ''}">${task.title}</span>
                            <span class="task-priority-badge ${priorityClass}">${task.priority}</span>
                        </div>
                        ${task.description ? `<div class="task-description-text">${task.description.substring(0, 100)}${task.description.length > 100 ? '...' : ''}</div>` : ''}
                        <div class="task-meta">
                            <span class="task-assignee-mini">
                                <i class="icofont-user"></i> ${task.employee_name}
                            </span>
                            ${task.created_at ? `
                                <span class="task-due-date">
                                    <i class="icofont-clock-time"></i> Created: ${task.created_at}
                                </span>` : ''}
                        </div>
                    </div>
                    <div class="task-actions">
                        <button class="task-action-btn" onclick="editTask(${task.id})" title="Edit">
                            <i class="icofont-edit"></i>
                        </button>
                        <button class="task-action-btn task-action-delete" onclick="deleteTask(${task.id})" title="Delete">
                            <i class="icofont-trash"></i>
                        </button>
                    </div>
                </div>`;
        });

        html += `</div></div>`;
    });

    html += '</div>';
    tasksBody.innerHTML = html;
}

// ── Add Task ─────────────────────────────────────────────────
document.getElementById('addAdminTaskBtn').addEventListener('click', function() {
    document.getElementById('quickAddTaskForm').reset();
    document.getElementById('quickTaskDueDate').value = selectedDate.toISOString().split('T')[0];
    quickAddModal.show();
});

document.getElementById('saveQuickTaskBtn').addEventListener('click', async function() {
    const title = document.getElementById('quickTaskTitle').value.trim();
    if (!title) {
        alert('Please enter a task title');
        return;
    }

    const taskData = {
        title: title,
        description: document.getElementById('quickTaskDescription').value.trim(),
        priority: document.getElementById('quickTaskPriority').value,
        due_date: document.getElementById('quickTaskDueDate').value,
        assigned_to: {
            {
                auth() - > id()
            }
        },
    };

    try {
        const response = await fetch('{{ route("tasks.quick-add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(taskData)
        });
        const data = await response.json();
        if (data.success) {
            quickAddModal.hide();
            document.getElementById('quickAddTaskForm').reset();
            fetchDailyTasks(selectedDate);
            showToast('success', 'Task added successfully!');
        } else {
            showToast('error', data.message || 'Failed to add task');
        }
    } catch (error) {
        showToast('error', 'Error adding task');
    }
});

// ── Toggle Completion ─────────────────────────────────────────
async function toggleTaskCompletion(taskId, isCompleted) {
    const newStatus = isCompleted ? 'completed' : 'in_progress';
    try {
        const response = await fetch(`admin/tasks/${taskId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: newStatus
            })
        });
        const data = await response.json();
        if (data.success) {
            fetchDailyTasks(selectedDate);
            showToast('success', isCompleted ? 'Task completed!' : 'Task reopened');
        }
    } catch (error) {
        showToast('error', 'Error updating task');
    }
}

// ── Edit Task ─────────────────────────────────────────────────
function editTask(taskId) {
    const task = adminTasksData.find(t => t.id === taskId);
    if (!task) return;
    document.getElementById('editTaskId').value = task.id;
    document.getElementById('editTaskTitle').value = task.title;
    document.getElementById('editTaskDescription').value = task.description || '';
    document.getElementById('editTaskPriority').value = task.priority;
    document.getElementById('editTaskStatus').value = task.status;
    document.getElementById('editTaskDueDate').value = task.due_date || '';
    editTaskModal.show();
}

document.getElementById('updateTaskBtn').addEventListener('click', async function() {
    const taskId = document.getElementById('editTaskId').value;
    const title = document.getElementById('editTaskTitle').value.trim();
    if (!title) {
        alert('Please enter a task title');
        return;
    }

    const taskData = {
        title: title,
        description: document.getElementById('editTaskDescription').value.trim(),
        priority: document.getElementById('editTaskPriority').value,
        status: document.getElementById('editTaskStatus').value,
        due_date: document.getElementById('editTaskDueDate').value,
        _token: '{{ csrf_token() }}'
    };

    try {
        const response = await fetch(`/admin/tasks/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(taskData)
        });
        const data = await response.json();
        if (data.success) {
            editTaskModal.hide();
            fetchDailyTasks(selectedDate);
            showToast('success', 'Task updated successfully!');
        } else {
            showToast('error', data.message || 'Failed to update task');
        }
    } catch (error) {
        showToast('error', 'Error updating task');
    }
});

// ── Delete Task ───────────────────────────────────────────────
async function deleteTask(taskId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        try {
            const response = await fetch(`admin/tasks/${taskId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Task deleted successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchDailyTasks(selectedDate);
            } else {
                Swal.fire('Error!', data.message || 'Failed to delete task.', 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong while deleting the task.', 'error');
        }
    });
}

function openTaskDetails(taskId) {
    editTask(taskId);
}

// ── Toast ─────────────────────────────────────────────────────
function showToast(type, message) {
    if (type === 'success') console.log('✓ ' + message);
    else console.error('✗ ' + message);
}

// ── Attendance UI ─────────────────────────────────────────────
function updateAttendanceUI(data) {
    document.getElementById('presentCount').textContent = data.stats.present || 0;
    document.getElementById('absentCount').textContent = data.stats.absent || 0;
    document.getElementById('lateCount').textContent = data.stats.late || 0;
    document.getElementById('halfdayCount').textContent = data.stats.half_day || 0;

    const tbody = document.getElementById('attendanceTableBody');

    if (!data.attendance || data.attendance.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="icofont-users-alt-4" style="font-size: 3rem; color: #ddd;"></i>
                    <p class="mt-2 text-muted">No attendance records for this date</p>
                </td>
            </tr>`;
        return;
    }

    let html = '';
    data.attendance.forEach((record, index) => {
        const statusBadges = {
            'present': '<span class="badge bg-success">Present</span>',
            'absent': '<span class="badge bg-danger">Absent</span>',
            'late': '<span class="badge bg-warning text-dark">Late</span>',
            'half_day': '<span class="badge bg-info">Half Day</span>'
        };

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2">
                            ${record.employee_initials || record.employee_name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-semibold">${record.employee_name}</div>
                            <small class="text-muted">${record.employee_email || ''}</small>
                        </div>
                    </div>
                </td>
                <td>${statusBadges[record.status] || record.status}</td>
                <td>${record.clock_in_at  ? `<span class="time-badge"><i class="icofont-login"></i> ${record.clock_in_at}</span>`  : '<span class="text-muted">—</span>'}</td>
                <td>${record.clock_out_at ? `<span class="time-badge"><i class="icofont-logout"></i> ${record.clock_out_at}</span>` : '<span class="text-muted">—</span>'}</td>
                <td>${record.working_hours ? `<span class="hours-badge"><i class="icofont-clock-time"></i> ${parseFloat(record.working_hours).toFixed(1)}h</span>` : '<span class="text-muted">—</span>'}</td>
                <td><small class="text-muted">${record.notes || '—'}</small></td>
            </tr>`;
    });

    tbody.innerHTML = html;
}

function showError(message) {
    document.getElementById('attendanceTableBody').innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5">
                <i class="icofont-warning text-danger" style="font-size: 3rem;"></i>
                <p class="mt-2 text-danger">${message}</p>
            </td>
        </tr>`;
}

// ── Date Controls ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('attendanceDate');
    const prevBtn = document.getElementById('prevDate');
    const nextBtn = document.getElementById('nextDate');
    const todayBtn = document.getElementById('todayBtn');
    const dateDisplay = document.getElementById('selectedDateDisplay');

    fetchDailyAttendance(selectedDate);
    dateDisplay.textContent = formatDateDisplay(selectedDate);

    dateInput.addEventListener('change', function() {
        selectedDate = new Date(this.value + 'T00:00:00');
        dateDisplay.textContent = formatDateDisplay(selectedDate);
        fetchDailyAttendance(selectedDate);
    });

    prevBtn.addEventListener('click', function() {
        selectedDate.setDate(selectedDate.getDate() - 1);
        dateInput.value = selectedDate.toISOString().split('T')[0];
        dateDisplay.textContent = formatDateDisplay(selectedDate);
        fetchDailyAttendance(selectedDate);
    });

    nextBtn.addEventListener('click', function() {
        selectedDate.setDate(selectedDate.getDate() + 1);
        dateInput.value = selectedDate.toISOString().split('T')[0];
        dateDisplay.textContent = formatDateDisplay(selectedDate);
        fetchDailyAttendance(selectedDate);
    });

    todayBtn.addEventListener('click', function() {
        selectedDate = new Date();
        dateInput.value = selectedDate.toISOString().split('T')[0];
        dateDisplay.textContent = formatDateDisplay(selectedDate);
        fetchDailyAttendance(selectedDate);
    });
});
</script>
@endif
@endpush

<style>
.gradient-card {
    border: none;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.gradient-card h3 {
    margin: 0;
    font-weight: 700;
}

.gradient-card i {
    position: absolute;
    right: 20px;
    bottom: 15px;
    font-size: 45px;
    opacity: 0.3;
}

.gradient-card.blue {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.gradient-card.green {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
}

.gradient-card.orange {
    background: linear-gradient(135deg, #fa709a, #fee140);
}

.gradient-card.purple {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.modern-card {
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
    border-radius: 14px;
}

/* ============================================
   ADMIN DATE-WISE ATTENDANCE STYLES
   ============================================ */

.date-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.date-controls input[type="date"] {
    max-width: 180px;
}

.attendance-stat {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
}

.attendance-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.attendance-stat .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.present-stat .stat-icon {
    background: linear-gradient(135deg, #10b981, #059669);
}

.absent-stat .stat-icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.late-stat .stat-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.halfday-stat .stat-icon {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.attendance-stat .stat-info h4 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
}

.attendance-stat .stat-info span {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.time-badge,
.hours-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: #f3f4f6;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.time-badge i,
.hours-badge i {
    font-size: 1rem;
    color: #6b7280;
}

/* ============================================
   TASKS SUMMARY STYLES
   ============================================ */

.tasks-summary-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.tasks-summary-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.tasks-summary-header h6 {
    color: #1f2937;
    font-weight: 700;
    margin: 0;
}

.tasks-summary-header i {
    color: #667eea;
    font-size: 1.25rem;
}

.tasks-summary-body {
    padding: 1.5rem;
}

.task-stats-mini {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.stat-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.stat-badge.new {
    background: #e0e7ff;
    color: #4f46e5;
}

.stat-badge.progress {
    background: #dbeafe;
    color: #3b82f6;
    height: 2rem;
}

.stat-badge.completed {
    background: #d1fae5;
    color: #10b981;
}

.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.task-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1rem;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.task-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #cbd5e1;
}

.task-card.status-new::before {
    background: #4f46e5;
}

.task-card.status-progress::before {
    background: #3b82f6;
}

.task-card.status-completed::before {
    background: #10b981;
}

.task-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 0.5rem;
}

.task-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.task-status-badge.status-new {
    background: #e0e7ff;
    color: #4f46e5;
}

.task-status-badge.status-progress {
    background: #dbeafe;
    color: #3b82f6;
}

.task-status-badge.status-completed {
    background: #d1fae5;
    color: #10b981;
}

.task-status-badge i {
    font-size: 0.875rem;
}

.admin-tasks-card .tasks-summary-header {
    background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
}

.admin-tasks-card .tasks-summary-header i {
    color: #dc2626;
}

.task-priority-badge {
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.task-priority-badge.priority-low {
    background: #f3f4f6;
    color: #6b7280;
}

.task-priority-badge.priority-medium {
    background: #fef3c7;
    color: #d97706;
}

.task-priority-badge.priority-high {
    background: #fed7aa;
    color: #c2410c;
}

.task-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.task-card-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    line-height: 1.4;
}

.task-card-desc {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.task-card-footer {
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.task-assignee {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.assignee-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
    flex-shrink: 0;
}

.assignee-name {
    font-size: 0.8rem;
    color: #374151;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ============================================
   GOOGLE TASKS-LIKE STYLES
   ============================================ */

.google-tasks-container {
    padding: 0;
}

.task-status-group {
    margin-bottom: 1.5rem;
}

.task-status-group:last-child {
    margin-bottom: 0;
}

.task-status-header {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    padding: 0.5rem 0.75rem;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.google-tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.google-task-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
    animation: slideIn 0.3s ease-out;
}

.google-task-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    border-color: #d1d5db;
}

.google-task-item.completed {
    opacity: 0.7;
    background: #f9fafb;
}

.task-checkbox-wrapper {
    padding-top: 0.125rem;
    flex-shrink: 0;
}

.task-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #10b981;
}

.task-content-wrapper {
    flex: 1;
    min-width: 0;
    cursor: pointer;
}

.task-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.task-title-text {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1f2937;
    flex: 1;
    line-height: 1.4;
}

.task-title-text.completed {
    text-decoration: line-through;
    color: #9ca3af;
}

.task-description-text {
    font-size: 0.85rem;
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 0.5rem;
}

.task-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.task-assignee-mini,
.task-due-date {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.task-assignee-mini i,
.task-due-date i {
    font-size: 0.875rem;
}

.task-actions {
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.google-task-item:hover .task-actions {
    opacity: 1;
}

.task-action-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;
}

.task-action-btn:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.task-action-btn.task-action-delete:hover {
    background: #fee2e2;
    color: #dc2626;
}

.task-action-btn i {
    font-size: 1rem;
}

.google-task-item .task-priority-badge {
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    flex-shrink: 0;
}

.modal-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 2px solid #e9ecef;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #1f2937;
    font-weight: 700;
}

.modal-title i {
    color: #667eea;
}

.form-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, .15);
}

/* ============================================
   EMPLOYEE DASHBOARD STYLES
   ============================================ */

.employee-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
}

.employee-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
    transform: translateY(-2px);
}

.employee-card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
}

.tasks-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hours-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.card-title-employee {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.card-subtitle-employee {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.btn-view-tasks {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(102, 126, 234, .3);
}

.btn-view-tasks:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, .4);
    color: white;
}

.btn-view-tasks i {
    transition: transform 0.3s;
}

.btn-view-tasks:hover i {
    transform: translateX(3px);
}

.employee-card-body {
    padding: 0;
}

.tasks-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.task-item {
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.task-item:last-child {
    border-bottom: none;
}

.task-item:hover {
    background: #f9fafb;
}

.task-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.task-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.task-icon.status-todo {
    background: #e0e7ff;
    color: #4f46e5;
}

.task-icon.status-progress {
    background: #dbeafe;
    color: #3b82f6;
}

.task-icon.status-completed {
    background: #d1fae5;
    color: #10b981;
}

.task-icon.status-blocked {
    background: #fee2e2;
    color: #ef4444;
}

.task-content {
    flex: 1;
    min-width: 0;
}

.task-title {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-status {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

.task-status.status-todo {
    background: #e0e7ff;
    color: #4f46e5;
}

.task-status.status-progress {
    background: #dbeafe;
    color: #3b82f6;
}

.task-status.status-completed {
    background: #d1fae5;
    color: #10b981;
}

.task-status.status-blocked {
    background: #fee2e2;
    color: #ef4444;
}

.task-arrow {
    color: #9ca3af;
    font-size: 1.25rem;
    transition: all 0.2s;
}

.task-link:hover .task-arrow {
    color: #667eea;
    transform: translateX(4px);
}

.task-item.empty,
.task-item.loading,
.task-item.error {
    padding: 3rem 1.5rem;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 3rem;
    opacity: 0.5;
}

.empty-state span {
    font-size: 0.875rem;
    font-weight: 500;
}

.empty-state.error {
    color: #ef4444;
}

.task-skeleton {
    height: 60px;
    background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 8px;
    margin: 1.5rem;
}

/* Working Hours */
.working-hours-card {
    height: 100%;
}

.hours-display {
    padding: 2rem 0 1.5rem 0;
}

.hours-circle {
    position: relative;
    display: inline-block;
}

.progress-ring {
    transform: rotate(-90deg);
}

.progress-ring-circle-bg {
    fill: none;
    stroke: #e5e7eb;
    stroke-width: 8;
}

.progress-ring-circle {
    fill: none;
    stroke: url(#gradient);
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 377;
    stroke-dashoffset: 377;
    transition: stroke-dashoffset 1s ease-in-out;
}

.hours-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.hours-number {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

.hours-label {
    display: block;
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.hours-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    padding: 1.5rem;
    margin-top: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.stat-divider {
    width: 1px;
    height: 40px;
    background: #e5e7eb;
}

/* ============================================
   CALENDAR STYLES
   ============================================ */

.calendar-container {
    font-size: 13px;
}

.calendar-nav {
    display: flex;
    gap: 8px;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
    margin-bottom: 10px;
}

.weekday {
    text-align: center;
    font-weight: 600;
    padding: 10px 4px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 13px;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #e9ecef;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 45px;
    padding: 4px;
}

.calendar-day .day-number {
    font-weight: 600;
    margin-bottom: 2px;
}

.calendar-day .holiday-name {
    font-size: 9px;
    text-align: center;
    line-height: 1.1;
    font-weight: 500;
}

.calendar-day .attendance-status {
    font-size: 10px;
    font-weight: 700;
    margin-top: 2px;
}

.calendar-day:hover:not(.empty) {
    background: #f8f9fa;
    transform: scale(1.05);
}

.calendar-day.empty {
    border: none;
    background: transparent;
}

.calendar-day.today {
    border: 2px solid #0d6efd;
    box-shadow: 0 0 8px rgba(13, 110, 253, .3);
}

/* Day type colours */
.calendar-day.sunday {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
    font-weight: 600;
}

.calendar-day.holiday {
    background: #f288e2ff;
    border-color: #f288e2ff;
    color: #600252ff;
    font-weight: 600;
}

/* ✅ 2nd / 4th Saturday — week off */
.calendar-day.week-off-saturday {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0369a1;
    font-weight: 600;
}

/* Attendance colours */
.calendar-day.attendance-present {
    background: #d1e7dd;
    border-color: #badbcc;
    color: #0f5132;
    font-weight: 600;
}

.calendar-day.attendance-absent {
    background: #f8d7da;
    border-color: #f5c6cb;
    color: #842029;
    font-weight: 600;
}

.calendar-day.attendance-late {
    background: #efb47dff;
    border-color: #efb47dff;
    color: #664d03;
    font-weight: 600;
}

.calendar-day.attendance-half_day {
    background: #cfe2ff;
    border-color: #b6d4fe;
    color: #084298;
    font-weight: 600;
}

/* Legend */
.holiday-legend {
    display: flex;
    gap: 12px;
    justify-content: center;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
}

.holiday-legend small {
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend-sunday,
.legend-holiday,
.legend-weekoff,
.legend-present,
.legend-absent,
.legend-late {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 3px;
}

.legend-present {
    background: #d1e7dd;
    border: 1px solid #badbcc;
}

.legend-absent {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
}

.legend-late {
    background: #efb47dff;
    border: 1px solid #efb47dff;
}

.legend-sunday {
    background: #fff3cd;
    border: 1px solid #ffc107;
}

.legend-weekoff {
    background: #e0f2fe;
    border: 1px solid #7dd3fc;
}

.legend-holiday {
    background: #f288e2ff;
    border: 1px solid #f288e2ff;
}

/* ============================================
   RESPONSIVE
   ============================================ */

@media (max-width: 992px) {
    .working-hours-card {
        margin-top: 0;
    }

    .date-controls {
        flex-wrap: wrap;
    }

    .tasks-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .employee-card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .btn-view-tasks {
        width: 100%;
        justify-content: center;
    }

    .hours-display {
        padding: 1.5rem 0 1rem 0;
    }

    .hours-number {
        font-size: 2rem;
    }

    .date-controls input[type="date"] {
        max-width: 100%;
        width: 100%;
    }

    .tasks-summary-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .task-stats-mini {
        width: 100%;
        justify-content: space-between;
    }

    .tasks-grid {
        grid-template-columns: 1fr;
    }

    .task-actions {
        opacity: 1;
    }
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- SVG Gradient for Progress Circle -->
<svg width="0" height="0" style="position: absolute;">
    <defs>
        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
        </linearGradient>
    </defs>
</svg>