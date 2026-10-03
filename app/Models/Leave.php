<?php

// app/Models/Leave.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'from_date',
        'leave_duration',
        'to_date',
        'reason',
        'status',
        'reject_leave_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLeaveMonth(): int
    {
        return Carbon::parse($this->from_date)->month;
    }

    public function getLeaveYear(): int
    {
        return Carbon::parse($this->from_date)->year;
    }

    /**
     * Returns 0.5 for Half Day, otherwise full calendar days.
     */
    public function getLeaveDays(): float
    {
        if ($this->leave_duration === 'Half Day') {
            return 0.5;
        }

        $from = Carbon::parse($this->from_date);
        $to   = Carbon::parse($this->to_date ?? $this->from_date);

        return (float) ($from->diffInDays($to) + 1);
    }

    /**
     * Returns the April-start leave year for this leave's from_date.
     * e.g. a leave on 2024-05-10 → 2024 (April 2024 – March 2025)
     *      a leave on 2025-02-15 → 2024 (April 2024 – March 2025)
     */
    public function getLeaveYearStart(): int
    {
        return self::leaveYearStartFor($this->from_date);
    }

    /**
     * Static helper: given any date, return the April-start year.
     */
    public static function leaveYearStartFor($date): int
    {
        $d = Carbon::parse($date);
        // Jan–Mar belong to the previous April cycle
        return $d->month < 4 ? $d->year - 1 : $d->year;
    }

    /**
     * Start date (April 1) of the leave year this leave falls in.
     */
    public static function leaveYearStartDate($date): Carbon
    {
        $yearStart = self::leaveYearStartFor($date);
        return Carbon::create($yearStart, 4, 1);
    }

    /**
     * End date (March 31) of the leave year this leave falls in.
     */
    public static function leaveYearEndDate($date): Carbon
    {
        $yearStart = self::leaveYearStartFor($date);
        return Carbon::create($yearStart + 1, 3, 31);
    }
}