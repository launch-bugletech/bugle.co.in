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

{{-- 🔥 Gradient KPI Cards --}}
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card gradient-card blue">
            <div class="card-body">
                <h6>Total Clients</h6>
                <h3>128</h3>
                <i class="icofont-users-alt-5"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card gradient-card green">
            <div class="card-body">
                <h6>Active Projects</h6>
                <h3>42</h3>
                <i class="icofont-briefcase"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card gradient-card orange">
            <div class="card-body">
                <h6>Pending Payments</h6>
                <h3>₹3.2L</h3>
                <i class="icofont-wallet"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card gradient-card purple">
            <div class="card-body">
                <h6>This Month Revenue</h6>
                <h3>₹8.9L</h3>
                <i class="icofont-chart-growth"></i>
            </div>
        </div>
    </div>
</div>

{{-- 📊 Analytics Section --}}
<div class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card modern-card">
            <div class="card-header">
                <h6>Team Status Today</h6>
            </div>
            <div class="card-body">
                <div class="status-row">
                    <span>Present</span>
                    <span class="badge bg-success">18</span>
                </div>
                <div class="status-row">
                    <span>Absent</span>
                    <span class="badge bg-danger">4</span>
                </div>
                <div class="status-row">
                    <span>Late</span>
                    <span class="badge bg-warning">2</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🚀 Recent Projects (Agency Style) --}}
<div class="card modern-card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h6>Recent Projects</h6>
        <a href="#" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Progress</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Bugle Website Revamp</td>
                    <td>Bugle Pvt Ltd</td>
                    <td>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:80%"></div>
                        </div>
                    </td>
                    <td>20 Feb 2026</td>
                </tr>
                <tr>
                    <td>Zoffness college prep</td>
                    <td>Ben Zoffness</td>
                    <td>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-warning" style="width:55%"></div>
                        </div>
                    </td>
                    <td>05 Mar 2026</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ================= EMPLOYEE DASHBOARD ================= --}}
@else

<div class="row g-4 mb-4">
    <!--<div class="col-md-4">-->
    <!--    <div class="card modern-card text-center">-->
    <!--        <h6>My Tasks</h6>-->
    <!--        <h2 class="text-primary">12</h2>-->
    <!--    </div>-->
    <!--</div>-->

    <!--<div class="col-md-4">-->
    <!--    <div class="card modern-card text-center">-->
    <!--        <h6>Completed</h6>-->
    <!--        <h2 class="text-success">7</h2>-->
    <!--    </div>-->
    <!--</div>-->

    <div class="col-md-4">
    <div class="card modern-card text-center">
        <h6>Working Hours</h6>
        <h2 class="text-warning" id="workingHours">0</h2>
    </div>
</div>
</div>

<!--<div class="card modern-card mb-4">-->
<!--    <div class="card-header">-->
<!--        <h6>My Projects</h6>-->
<!--    </div>-->
<!--    <div class="card-body">-->
<!--        <p><strong>Bugle Landing Page</strong></p>-->
<!--        <div class="progress" style="height:8px;">-->
<!--            <div class="progress-bar bg-info" style="width:65%"></div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

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
                <small><span class="legend-holiday"></span> Holiday</small>
            </div>
        </div>
    </div>
</div>

@endif
</div>
@endsection

@push('scripts')
<script>
// Indian Holidays 2026 (Gazetted Holidays - National/Federal)
const holidays = {
    '2026-01-26': 'Republic Day',
    '2026-03-04': 'Holi',
    '2026-03-21': 'Ramzan Id',
    '2026-03-26': 'Rama Navami',
    '2026-03-31': 'Mahavir Jayanti',
    '2026-04-03': 'Good Friday',
    '2026-05-01': 'Buddha Purnima',
    '2026-05-27': 'Bakrid',
    '2026-06-26': 'Muharram',
    '2026-08-15': 'Independence Day',
    '2026-08-26': 'Milad un-Nabi',
    '2026-09-04': 'Janmashtami',
    '2026-10-02': 'Gandhi Jayanti',
    '2026-10-20': 'Dussehra',
    '2026-11-08': 'Diwali',
    '2026-11-24': 'Guru Nanak Jayanti',
    '2026-12-25': 'Christmas'
};

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

                // 🔥 SUM working hours
                totalWorkingHours += Number(record.working_hours || 0);
            });
        }

        // Update UI
        document.getElementById('workingHours').textContent =
            totalWorkingHours.toFixed(1);

    } catch (error) {
        console.error('Error fetching attendance:', error);
    }
}


function renderCalendar(date) {
    const year = date.getFullYear();
    const month = date.getMonth();
    
    // Update month display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day and total days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    let html = '<div class="calendar-weekdays">';
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdays.forEach(day => {
        html += `<div class="weekday">${day}</div>`;
    });
    html += '</div><div class="calendar-days">';
    
    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="calendar-day empty"></div>';
    }
    
    const today = new Date();
    const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;
    
    // Days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayOfWeek = new Date(year, month, day).getDay();
        const isSunday = dayOfWeek === 0;
        const isHoliday = holidays[currentDateStr];
        const attendance = attendanceData[currentDateStr];
        const isToday = isCurrentMonth && today.getDate() === day;
        const isFutureDate = new Date(year, month, day) > today;
        
        let classes = 'calendar-day';
        let displayText = day;
        let title = '';
        
        // Priority: Holiday > Sunday > Attendance Status
        if (isHoliday) {
            classes += ' holiday';
            title = isHoliday;
            displayText = `<div class="day-number">${day}</div><div class="holiday-name">${isHoliday}</div>`;
        } else if (isSunday) {
            classes += ' sunday';
            title = 'Sunday';
        } else if (attendance && !isFutureDate) {
            // Show attendance status
            classes += ` attendance-${attendance.status}`;
            title = `${attendance.status.charAt(0).toUpperCase() + attendance.status.slice(1)}`;
            if (attendance.time_in) {
                title += ` - In: ${attendance.time_in}`;
            }
            if (attendance.time_out) {
                title += ` Out: ${attendance.time_out}`;
            }
            const statusLabels = {
    present: 'Present',
    absent: 'Absent',
    late: 'Late',
    half_day: 'Half Day'
};

displayText = `
    <div class="day-number">${day}</div>
    <div class="attendance-status">
        ${statusLabels[attendance.status] ?? attendance.status}
    </div>
`;
        } else if (!isFutureDate && !isSunday && !isHoliday) {
            // Past date with no attendance record = absent
            classes += ' attendance-absent';
            title = 'Absent';
            displayText = `<div class="day-number">${day}</div><div class="attendance-status">Absent</div>`;
        }
        
        if (isToday) {
            classes += ' today';
        }
        
        html += `<div class="${classes}" title="${title}">${displayText}</div>`;
    }
    
    html += '</div>';
    document.getElementById('calendarGrid').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', async function() {
    @if(!$isAdmin)
    // Fetch initial attendance data
    await fetchAttendance(currentDate.getFullYear(), currentDate.getMonth());
    renderCalendar(currentDate);
    
    // Update calendar every minute if viewing current month
    setInterval(function() {
        const now = new Date();
        if (currentDate.getFullYear() === now.getFullYear() && 
            currentDate.getMonth() === now.getMonth()) {
            renderCalendar(currentDate);
        }
    }, 60000); // Update every minute
    
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
@endpush

<style>
.gradient-card {
    border: none;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.gradient-card h3 { margin: 0; font-weight: 700; }
.gradient-card i {
    position: absolute;
    right: 20px;
    bottom: 15px;
    font-size: 45px;
    opacity: 0.3;
}
.gradient-card.blue { background: linear-gradient(135deg,#4facfe,#00f2fe); }
.gradient-card.green { background: linear-gradient(135deg,#43e97b,#38f9d7); }
.gradient-card.orange { background: linear-gradient(135deg,#fa709a,#fee140); }
.gradient-card.purple { background: linear-gradient(135deg,#667eea,#764ba2); }

.modern-card {
    border: none;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
    border-radius: 14px;
}

.status-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 15px;
}

/* Calendar Styles */
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

.calendar-day .work-hours {
    font-size: 8px;
    font-weight: 600;
    margin-top: 1px;
    color: inherit;
    opacity: 0.9;
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
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.3);
}

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

/* Attendance Status Colors */
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

.legend-sunday, .legend-holiday, .legend-present, .legend-absent, .legend-late {
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

.legend-holiday {
    background: #f288e2ff;
    border: 1px solid #f288e2ff;
}
</style>