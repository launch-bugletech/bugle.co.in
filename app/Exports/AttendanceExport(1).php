<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin');

        $attendances = Attendance::with('user')
            ->when(!$isAdmin, function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('clock_in_at')
            ->get();

        $rows = collect();

        /* ================= ROW DATA ================= */
        foreach ($attendances as $a) {
            $rows->push([
                'Employee'     => ($a->user->first_name ?? '') . ' ' . ($a->user->last_name ?? ''),
                'Clock In'     => $a->clock_in_at,
                'Clock Out'    => $a->clock_out_at,
                'Clock In IP'  => $a->clock_in_ip,
                'Clock Out IP' => $a->clock_out_ip,
            ]);
        }

        /* ================= SUMMARY (NON-ADMIN ONLY) ================= */
        if (!$isAdmin) {

            // Blank row
            $rows->push(['', '', '', '', '']);

            // 📅 Month range: 1st → Today
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfDay();

            // Working days (exclude Sundays)
            $workingDays = 0;
            $current = $startDate->copy();

            while ($current <= $endDate) {
                if (!$current->isSunday()) {
                    $workingDays++;
                }
                $current->addDay();
            }

            // Present days
            $presentDays = $attendances
                ->whereNotNull('clock_in_at')
                ->groupBy(fn ($a) => Carbon::parse($a->clock_in_at)->toDateString())
                ->count();

            $absentDays = max($workingDays - $presentDays, 0);

            // Working hours
            $workingSeconds = $attendances->sum(function ($a) {
                if ($a->clock_in_at && $a->clock_out_at) {
                    return Carbon::parse($a->clock_out_at)
                        ->diffInSeconds(Carbon::parse($a->clock_in_at));
                }
                return 0;
            });

            $rows->push([
                'Employee' => 'Total Present Days',
                'Clock In' => $presentDays,
                'Clock Out' => '',
                'Clock In IP' => '',
                'Clock Out IP' => '',
            ]);

            $rows->push([
                'Employee' => 'Total Absent Days',
                'Clock In' => $absentDays,
                'Clock Out' => '',
                'Clock In IP' => '',
                'Clock Out IP' => '',
            ]);

            $rows->push([
                'Employee' => 'Total Working Hours',
                'Clock In' => gmdate('H:i', $workingSeconds),
                'Clock Out' => '',
                'Clock In IP' => '',
                'Clock Out IP' => '',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Clock In',
            'Clock Out',
            'Clock In IP',
            'Clock Out IP',
        ];
    }
}
