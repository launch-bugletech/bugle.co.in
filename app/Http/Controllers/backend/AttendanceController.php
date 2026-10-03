<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Carbon\Carbon;
use App\Events\ClockInOutNotification;
use App\Notifications\ClockInOutDBNotification;
use App\Helpers\LocationHelper;
use Illuminate\Support\Facades\Cache;


class AttendanceController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ================================
       SHOW CLOCK IN / OUT PAGE
    ================================= */
    public function index()
    {
        $user = Auth::user();

        // If admin → no need to fetch clock-in clock-out
        if ($user->role === 'admin') {
            return view('backend.pages.dashboard', [
                'clockIn' => null,
                'clockOut' => null,
                'isAdmin' => true
            ]);
        }

        // Attendance for today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        return view('backend.pages.dashboard', [
            'clockIn' => $attendance->clock_in ?? null,
            'clockOut' => $attendance->clock_out ?? null,
            'isAdmin' => false
        ]);
    }

    /* ================================
       CLOCK IN
    ================================= */
    /* ================================
       CLOCK IN
    ================================= */
    public function clockIn(Request $request)
{
    $user = auth()->user();

    // 🔒 Check if already clocked in today
    $existingAttendance = Attendance::where('user_id', $user->id)
        ->whereDate('clock_in_at', now()->toDateString())
        ->first();

    // if ($existingAttendance) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'You have already clocked in today'
    //     ], 409);
    // }

    $attendance = new Attendance();
    $attendance->user_id = $user->id;
    $attendance->clock_in_at = now();

    // 📸 Save clock-in photo
    if ($request->photo) {
        try {
            $attendance->clock_in_photo = $this->storeBase64Image($request->photo, 'in');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save clock-in photo'
            ], 500);
        }
    }

    // 🌍 IP & Location
    $attendance->clock_in_ip = $request->ip();
    $attendance->clock_in_lat = $request->lat;
    $attendance->clock_in_lng = $request->lng;

    $attendance->save();

    event(new ClockInOutNotification($user->name, 'has clocked in'));

    return response()->json([
        'success' => true,
        'message' => 'Clocked in successfully'
    ]);
}


    /* ================================
       CLOCK OUT
    ================================= */
    public function clockOut(Request $request)
    {
        $user = auth()->user();

        // ✅ Get ONLY active clock-in
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->latest()
            ->first();

        // if (!$attendance) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not clocked in'
        //     ], 409);
        // }

        $attendance->clock_out_at = now();

        // 📸 Save clock-out photo
        if ($request->photo) {
            try {
                $attendance->clock_out_photo = $this->storeBase64Image($request->photo, 'out');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save clock-out photo'
                ], 500);
            }
        }

        // 🌍 IP & Location
        $attendance->clock_out_ip = $request->ip();
        $attendance->clock_out_lat = $request->lat;
        $attendance->clock_out_lng = $request->lng;

        $attendance->save();

        // 🔔 Broadcast notification
        event(new ClockInOutNotification(
            $user->name,
            'has clocked out'
        ));

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully'
        ]);
    }


    /* ================================
       SAVE BASE64 PHOTO
    ================================= */
    protected function storeBase64Image($base64Image, $type = 'photo')
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $match)) {
            throw new \Exception('Invalid image base64 format');
        }

        $extension = $match[1];
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $decoded = base64_decode($data);

        if (!$decoded) {
            throw new \Exception('Base64 decode failed');
        }

        $folder = "attendance/" . now()->format('Y-m-d');
        $filename = $folder . '/' . Str::random(12) . "_{$type}." . $extension;

        Storage::disk('public')->put($filename, $decoded);

        return $filename;  // ✔ FIXED
    }


    /* ================================
       ADMIN LIST PAGE
    ================================= */
    
    
public function adminIndex(Request $request)
{
    $pagename = 'attendance';
    $user = auth()->user();

    if ($request->ajax() && $request->has('lat')) {
        $address = LocationHelper::getAddress($request->lat, $request->lng);
        return response()->json(['address' => $address ?? 'N/A']);
    }

    $query = Attendance::with('user')->latest();

    if (!$user->hasRole('Admin')) {
        $query->where('user_id', $user->id);
    }

    $filterDate     = $request->get('date', now()->format('Y-m-d'));
    $filterMonth    = $request->get('month');
    $filterEmployee = $request->get('employee'); // ✅ get employee

    if ($filterMonth) {
        $query->whereYear('clock_in_at', substr($filterMonth, 0, 4))
              ->whereMonth('clock_in_at', substr($filterMonth, 5, 2));
        $filterDate = null;
    } else {
        $query->whereDate('clock_in_at', $filterDate);
    }

    // ✅ Apply employee filter server-side
    if ($filterEmployee) {
        $query->whereHas('user', function ($q) use ($filterEmployee) {
            $q->where('first_name', 'like', "%{$filterEmployee}%")
              ->orWhere('last_name', 'like', "%{$filterEmployee}%");
        });
    }

    $attendances = $query->get();

    return view('backend.attendance.admin_list', compact(
        'attendances', 'pagename', 'filterDate', 'filterMonth', 'filterEmployee'
    ));
}

//     public function adminIndex()
// {
//     $pagename = 'attendance';
//     $user = auth()->user();

//     if ($user->hasRole('Admin')) {
//         $attendances = Attendance::with('user')
//             ->latest()
//             ->paginate(20);
//     } else {
//         $attendances = Attendance::with('user')
//             ->where('user_id', $user->id)
//             ->latest()
//             ->paginate(20);
//     }

//     // ✅ Attach address (ONLY ONCE per record)
//     $attendances->getCollection()->transform(function ($attendance) {

//         // Clock-in address
//         if ($attendance->clock_in_lat && $attendance->clock_in_lng) {
//             $attendance->clock_in_address = LocationHelper::getAddress(
//                 $attendance->clock_in_lat,
//                 $attendance->clock_in_lng
//             );
//         } else {
//             $attendance->clock_in_address = null;
//         }

//         // Clock-out address
//         if ($attendance->clock_out_lat && $attendance->clock_out_lng) {
//             $attendance->clock_out_address = LocationHelper::getAddress(
//                 $attendance->clock_out_lat,
//                 $attendance->clock_out_lng
//             );
//         } else {
//             $attendance->clock_out_address = null;
//         }

//         return $attendance;
//     });

//     return view('backend.attendance.admin_list', compact('attendances', 'pagename'));
// }

    
    
   public function exportPdf(Request $request)
{
    $user = auth()->user();
    $isAdmin = $user->hasRole('Admin');

    $query = Attendance::with('user');

    if (!$isAdmin) {
        $query->where('user_id', $user->id);
    }

    // ✅ Date takes priority over month
    if ($request->date) {
        $query->whereDate('clock_in_at', $request->date);
    } elseif ($request->month) {
        $month = Carbon::parse($request->month);
        $query->whereMonth('clock_in_at', $month->month)
              ->whereYear('clock_in_at', $month->year);
    }

    // ✅ Employee filter
    if ($request->employee) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('first_name', 'like', "%{$request->employee}%")
              ->orWhere('last_name', 'like', "%{$request->employee}%");
        });
    }

    $attendances = $query->orderBy('clock_in_at')->get();

    // summary logic stays same...
    $employeeSummary = [];
    $userSummary = null;

    if ($isAdmin) {
        $employeeSummary = $attendances->groupBy('user_id')->map(function ($records) {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfDay();
            $workingDays = 0;
            $current = $startDate->copy();
            while ($current <= $endDate) {
                if (!$current->isSunday()) $workingDays++;
                $current->addDay();
            }
            $presentDays = $records
                ->whereNotNull('clock_in_at')
                ->groupBy(fn($a) => Carbon::parse($a->clock_in_at)->toDateString())
                ->count();
            return [
                'present' => $presentDays,
                'absent' => max($workingDays - $presentDays, 0),
            ];
        });
    } else {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfDay();
        $workingDays = 0;
        $current = $startDate->copy();
        while ($current <= $endDate) {
            if (!$current->isSunday()) $workingDays++;
            $current->addDay();
        }
        $presentDays = $attendances
            ->groupBy(fn($a) => Carbon::parse($a->clock_in_at)->toDateString())
            ->count();
        $workingSeconds = $attendances->sum(function ($a) {
            if ($a->clock_in_at && $a->clock_out_at) {
                return Carbon::parse($a->clock_out_at)->diffInSeconds(Carbon::parse($a->clock_in_at));
            }
            return 0;
        });
        $userSummary = [
            'present' => $presentDays,
            'absent' => max($workingDays - $presentDays, 0),
            'hours' => gmdate('H:i', $workingSeconds),
        ];
    }

    $pdf = Pdf::loadView(
        'backend.pages.export.attendance_pdf',
        compact('attendances', 'isAdmin', 'employeeSummary', 'userSummary')
    )->setPaper('a4', 'landscape');

    return $pdf->download('attendance_report.pdf');
}



    // public function exportExcel()
    // {
    //     return Excel::download(new AttendanceExport, 'attendance_report.xlsx');
    // }

public function exportExcel(Request $request)
{
    return Excel::download(new AttendanceExport($request), 'attendance_report.xlsx');
}


    protected function getAttendanceSummary($userId = null)
    {
        $query = Attendance::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $records = $query->get();

        $presentDays = $records
            ->whereNotNull('clock_in_at')
            ->groupBy(fn($a) => Carbon::parse($a->clock_in_at)->toDateString())
            ->count();

        $totalDays = $records
            ->groupBy(fn($a) => Carbon::parse($a->created_at)->toDateString())
            ->count();

        $absentDays = max($totalDays - $presentDays, 0);

        $workingSeconds = $records->sum(function ($a) {
            if ($a->clock_in_at && $a->clock_out_at) {
                return Carbon::parse($a->clock_out_at)
                    ->diffInSeconds(Carbon::parse($a->clock_in_at));
            }
            return 0;
        });

        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'working_hours' => gmdate('H:i', $workingSeconds),
        ];
    }

    // monthly report by user id
    public function employeeMonthlyAttendance(Request $request, $userId)
    {
        $pagename = 'attendance';

        $month = $request->get('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::with('user')
            ->where('user_id', $userId)
            ->whereBetween('clock_in_at', [$start, $end])
            ->orderBy('clock_in_at')
            ->get();

        $user = $attendances->first()?->user;

        /* ================= SUMMARY ================= */
        $workingDays = 0;
        $current = $start->copy();

        while ($current <= $end) {
            if (!$current->isSunday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        $presentDays = $attendances
            ->groupBy(fn($a) => Carbon::parse($a->clock_in_at)->toDateString())
            ->count();

        $workingSeconds = $attendances->sum(function ($a) {
            if ($a->clock_in_at && $a->clock_out_at) {
                return Carbon::parse($a->clock_out_at)
                    ->diffInSeconds(Carbon::parse($a->clock_in_at));
            }
            return 0;
        });

        $summary = [
            'present' => $presentDays,
            'absent' => max($workingDays - $presentDays, 0),
            'hours' => gmdate('H:i', $workingSeconds),
        ];

        return view(
            'backend.attendance.employee_monthly',
            compact('attendances', 'user', 'month', 'summary', 'pagename') // ✅ PASS IT
        );
    }



    public function getAttendanceCalendar(Request $request)
    {

        if (!$request->ajax()) {
            abort(403);
        }

        // dd(688789);
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $userId = auth()->id();

        // Get first and last day of the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Fetch attendance records for the user
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('clock_in_at', '>=', $startDate)
            ->whereDate('clock_in_at', '<=', $endDate)
            ->get()
            ->map(function ($record) {
                // Extract date from clock_in_at
                $date = Carbon::parse($record->clock_in_at)->format('Y-m-d');

                // Calculate working hours if both clock in and out exist
                $workingHours = 0;
                if ($record->clock_in_at && $record->clock_out_at) {
                    $clockIn = Carbon::parse($record->clock_in_at);
                    $clockOut = Carbon::parse($record->clock_out_at);
                    $workingHours = $clockOut->diffInHours($clockIn, true);
                }

                // Determine status based on working hours
                $status = 'absent';
                if ($workingHours >= 8) {
                    $status = 'present';
                } elseif ($workingHours >= 4) {
                    $status = 'half_day';
                } elseif ($workingHours > 0) {
                    $status = 'late';
                }


                return [
                    'date' => $date,
                    'status' => $status,
                    'clock_in_at' => $record->clock_in_at ? Carbon::parse($record->clock_in_at)->format('H:i') : null,
                    'clock_out_at' => $record->clock_out_at ? Carbon::parse($record->clock_out_at)->format('H:i') : null,
                    'working_hours' => round($workingHours, 2),
                ];

            });

        return response()->json([
            'success' => true,
            'attendance' => $attendance
        ]);
    }
    
    
    public function getDailyAttendance(Request $request)
    {
    $date = $request->input('date', Carbon::today()->format('Y-m-d'));
    
    try {
        // Parse the date
        $selectedDate = Carbon::parse($date);
        
        // Get all employees (excluding admins)
        $employees = User::where('role_id', '!=', 1)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        
        // Get attendance records for this date
        $attendanceRecords = Attendance::whereDate('clock_in_at', $selectedDate)
            ->with('user')
            ->get()
            ->keyBy('user_id');
        
        // Initialize stats
        $stats = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'half_day' => 0,
        ];
        
        $attendanceData = [];
        
        foreach ($employees as $employee) {
            $attendance = $attendanceRecords->get($employee->id);
            
            $fullName = trim($employee->first_name . ' ' . $employee->last_name);
            $initials = strtoupper(
                (substr($employee->first_name, 0, 1) ?? '') . 
                (substr($employee->last_name, 0, 1) ?? '')
            );
            
            if ($attendance) {
                // ✅ HAS ATTENDANCE
                $clockInTime = Carbon::parse($attendance->clock_in_at);
                $workStartTime = Carbon::parse($attendance->clock_in_at)->setTime(10, 30, 0);
                
                if ($attendance->clock_out_at) {
                    $clockOutTime = Carbon::parse($attendance->clock_out_at);
                    $workingHours = $clockOutTime->diffInHours($clockInTime, true);
                    
                    if ($workingHours < 4) {
                        $status = 'half_day';
                    } elseif ($clockInTime->gt($workStartTime)) {
                        $status = 'late';
                    } else {
                        $status = 'present';
                    }
                } else {
                    // Still working
                    if ($clockInTime->gt($workStartTime)) {
                        $status = 'late';
                    } else {
                        $status = 'present';
                    }
                }
                
                // Calculate working hours
                $calculatedWorkingHours = null;
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $clockIn = Carbon::parse($attendance->clock_in_at);
                    $clockOut = Carbon::parse($attendance->clock_out_at);
                    $calculatedWorkingHours = round($clockOut->diffInMinutes($clockIn) / 60, 1);
                }
                
                $stats[$status] = ($stats[$status] ?? 0) + 1;
                
                $attendanceData[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $fullName,
                    'employee_initials' => $initials,
                    'employee_email' => $employee->email,
                    'status' => $status,
                    'clock_in_at' => $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('h:i A') : null,
                    'clock_out_at' => $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('h:i A') : null,
                    'working_hours' => $calculatedWorkingHours,
                    'notes' => $attendance->clock_in_location ?? null,
                ];
                
            } else {
                // ❌ NO ATTENDANCE
                
                $isPast = $selectedDate->lt(Carbon::today());
                $isSunday = $selectedDate->dayOfWeek === 0;

                $holidays = [
                    '2026-01-26', '2026-03-04', '2026-03-21', '2026-03-26',
                    '2026-03-31', '2026-04-03', '2026-05-01', '2026-05-27',
                    '2026-06-26', '2026-08-15', '2026-08-26', '2026-09-04',
                    '2026-10-02', '2026-10-20', '2026-11-08', '2026-11-24',
                    '2026-12-25'
                ];

                $isHoliday = in_array($selectedDate->format('Y-m-d'), $holidays);

                // 👉 NEW LOGIC (11 AM ABSENT RULE)
                $currentTime = Carbon::now();
                $cutoffTime = Carbon::today()->setTime(11, 0, 0);

                $isToday = $selectedDate->isSameDay(Carbon::today());
                $isAfterCutoff = $currentTime->gte($cutoffTime);

                if (
                    (!$isSunday && !$isHoliday) &&
                    (
                        $isPast || ($isToday && $isAfterCutoff)
                    )
                ) {
                    $stats['absent']++;

                    $attendanceData[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $fullName,
                        'employee_initials' => $initials,
                        'employee_email' => $employee->email,
                        'status' => 'absent',
                        'clock_in_at' => null,
                        'clock_out_at' => null,
                        'working_hours' => null,
                        'notes' => 'No attendance record',
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'date' => $selectedDate->format('Y-m-d'),
            'stats' => $stats,
            'attendance' => $attendanceData,
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching attendance data: ' . $e->getMessage(),
        ], 500);
    }
}


    // public function getDailyAttendance(Request $request)
    // {
    //     $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        
    //     try {
    //         // Parse the date
    //         $selectedDate = Carbon::parse($date);
            
    //         // Get all employees (excluding admins, role_id = 1)
    //         $employees = User::where('role_id', '!=', 1)
    //             ->where('status', 'active')
    //             ->orderBy('first_name')
    //             ->orderBy('last_name')
    //             ->get();
            
    //         // Get attendance records for this date (using clock_in_at)
    //         $attendanceRecords = Attendance::whereDate('clock_in_at', $selectedDate)
    //             ->with('user')
    //             ->get()
    //             ->keyBy('user_id');
            
    //         // Initialize stats
    //         $stats = [
    //             'present' => 0,
    //             'absent' => 0,
    //             'late' => 0,
    //             'half_day' => 0,
    //         ];
            
    //         $attendanceData = [];
            
    //         // Process each employee
    //         foreach ($employees as $employee) {
    //             $attendance = $attendanceRecords->get($employee->id);
                
    //             // Get full name and initials
    //             $fullName = trim($employee->first_name . ' ' . $employee->last_name);
    //             $initials = strtoupper(
    //                 (substr($employee->first_name, 0, 1) ?? '') . 
    //                 (substr($employee->last_name, 0, 1) ?? '')
    //             );
                
    //             if ($attendance) {
    //                 // Employee has attendance record
    //                 // Calculate status based on clock_in_at time
    //                 $clockInTime = Carbon::parse($attendance->clock_in_at);
    //                 $workStartTime = Carbon::parse($attendance->clock_in_at)->setTime(10, 30, 0); 
    //                 // Determine status
    //                 if ($attendance->clock_out_at) {
    //                     $clockOutTime = Carbon::parse($attendance->clock_out_at);
    //                     $workingHours = $clockOutTime->diffInHours($clockInTime, true);
                        
    //                     // Check if it's a half day (less than 4 hours)
    //                     if ($workingHours < 4) {
    //                         $status = 'half_day';
    //                     } elseif ($clockInTime->gt($workStartTime)) {
    //                         $status = 'late';
    //                     } else {
    //                         $status = 'present';
    //                     }
    //                 } else {
    //                     // Still clocked in (not clocked out yet)
    //                     if ($clockInTime->gt($workStartTime)) {
    //                         $status = 'late';
    //                     } else {
    //                         $status = 'present';
    //                     }
    //                     $workingHours = null;
    //                 }
                    
    //                 // Calculate working hours if both clock in and out exist
    //                 $calculatedWorkingHours = null;
    //                 if ($attendance->clock_in_at && $attendance->clock_out_at) {
    //                     $clockIn = Carbon::parse($attendance->clock_in_at);
    //                     $clockOut = Carbon::parse($attendance->clock_out_at);
    //                     $calculatedWorkingHours = round($clockOut->diffInMinutes($clockIn) / 60, 1);
    //                 }
                    
    //                 $stats[$status] = ($stats[$status] ?? 0) + 1;
                    
    //                 $attendanceData[] = [
    //                     'employee_id' => $employee->id,
    //                     'employee_name' => $fullName,
    //                     'employee_initials' => $initials,
    //                     'employee_email' => $employee->email,
    //                     'status' => $status,
    //                     'clock_in_at' => $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('h:i A') : null,
    //                     'clock_out_at' => $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('h:i A') : null,
    //                     'working_hours' => $calculatedWorkingHours,
    //                     'notes' => $attendance->clock_in_location ?? null,
    //                 ];
    //             } else {
    //                 // Check if the date is in the past and it's a working day
    //                 $isPast = $selectedDate->lt(Carbon::today());
    //                 $isSunday = $selectedDate->dayOfWeek === 0;
                    
    //                 // List of Indian holidays for 2026
    //                 $holidays = [
    //                     '2026-01-26', '2026-03-04', '2026-03-21', '2026-03-26',
    //                     '2026-03-31', '2026-04-03', '2026-05-01', '2026-05-27',
    //                     '2026-06-26', '2026-08-15', '2026-08-26', '2026-09-04',
    //                     '2026-10-02', '2026-10-20', '2026-11-08', '2026-11-24',
    //                     '2026-12-25'
    //                 ];
                    
    //                 $isHoliday = in_array($selectedDate->format('Y-m-d'), $holidays);
                    
    //                 // Only mark as absent if it's a past working day
    //                 if ($isPast && !$isSunday && !$isHoliday) {
    //                     $stats['absent']++;
                        
    //                     $attendanceData[] = [
    //                         'employee_id' => $employee->id,
    //                         'employee_name' => $fullName,
    //                         'employee_initials' => $initials,
    //                         'employee_email' => $employee->email,
    //                         'status' => 'absent',
    //                         'clock_in_at' => null,
    //                         'clock_out_at' => null,
    //                         'working_hours' => null,
    //                         'notes' => 'No attendance record',
    //                     ];
    //                 }
    //             }
    //         }
            
    //         return response()->json([
    //             'success' => true,
    //             'date' => $selectedDate->format('Y-m-d'),
    //             'stats' => $stats,
    //             'attendance' => $attendanceData,
    //         ]);
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error fetching attendance data: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    
    
}