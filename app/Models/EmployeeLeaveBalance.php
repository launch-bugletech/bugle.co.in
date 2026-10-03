<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'leave_year_start',
        'accrued',
        'carried_forward',
        'used',
        'balance',
        'deduction_flagged',
        'deduction_days',
        'used_casual',
        'used_sick',
        'used_vacation',
        'used_wfh',
        'used_comp_off',
        'used_maternity',
    ];

    protected $casts = [
        'deduction_flagged' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────────────────────────────
    // CONSTANTS & HELPERS
    // ─────────────────────────────────────────────────────────────────

    public static function annualEntitlement(): float
    {
        return 12.0;
    }

    /**
     * April-start leave year.
     * Jan–Mar 2025 → 2024  |  Apr–Dec 2025 → 2025
     */
    public static function leaveYearStartFor(int $month, int $year): int
    {
        return $month < 4 ? $year - 1 : $year;
    }

    /**
     * Total leaves used across ALL months in the leave year cycle.
     */
    public static function totalUsedInLeaveYear(int $userId, int $leaveYearStart): float
    {
        return (float) self::where('user_id', $userId)
            ->where('leave_year_start', $leaveYearStart)
            ->sum('used');
    }

    /**
     * Remaining balance in the leave year = 12 - total used so far.
     */
    public static function yearlyBalanceRemaining(int $userId, int $leaveYearStart): float
    {
        return max(0, self::annualEntitlement() - self::totalUsedInLeaveYear($userId, $leaveYearStart));
    }

    // ─────────────────────────────────────────────────────────────────
    // FIND OR CREATE
    // ─────────────────────────────────────────────────────────────────

    /**
     * Find or create the monthly tracking row.
     *
     * - accrued        = 12 (full year entitlement, shown on the first month row only for reference)
     * - carried_forward= 0  (not used in yearly-bucket model)
     * - used           = days used THIS month
     * - balance        = 12 - total used in entire leave year (always reflects yearly bucket)
     */
    public static function findOrCreateBalance(int $userId, int $month, int $year): self
    {
        $existing = self::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing) {
            // Always recalculate balance to reflect current yearly total
            $leaveYearStart   = self::leaveYearStartFor($month, $year);
            $totalUsed        = self::totalUsedInLeaveYear($userId, $leaveYearStart);
            $existing->balance = max(0, self::annualEntitlement() - $totalUsed);
            // Note: we do NOT save here — balance is derived on read,
            // only saved when deduct/restore is called.
            return $existing;
        }

        $leaveYearStart = self::leaveYearStartFor($month, $year);
        $totalUsed      = self::totalUsedInLeaveYear($userId, $leaveYearStart);
        $balance        = max(0, self::annualEntitlement() - $totalUsed);

        return self::create([
            'user_id'           => $userId,
            'month'             => $month,
            'year'              => $year,
            'leave_year_start'  => $leaveYearStart,
            'accrued'           => self::annualEntitlement(), // 12 — informational
            'carried_forward'   => 0.0,
            'used'              => 0.0,
            'balance'           => $balance,
            'deduction_flagged' => false,
            'deduction_days'    => 0.0,
            'used_casual'       => 0.0,
            'used_sick'         => 0.0,
            'used_vacation'     => 0.0,
            'used_wfh'          => 0.0,
            'used_comp_off'     => 0.0,
            'used_maternity'    => 0.0,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // DEDUCT LEAVE
    // ─────────────────────────────────────────────────────────────────

    /**
     * Deduct $days from the yearly bucket.
     *
     * - Adds to this month's `used`
     * - Recalculates `balance` = 12 - total_used_in_year
     * - Flags LOP if total_used > 12
     */
    public static function deductLeave(
        int    $userId,
        int    $month,
        int    $year,
        float  $days,
        string $leaveType = ''
    ): self {
        $balance        = self::findOrCreateBalance($userId, $month, $year);
        $leaveYearStart = self::leaveYearStartFor($month, $year);
        $entitlement    = self::annualEntitlement();

        // Add to this month's usage
        $balance->used += $days;

        // Recalculate yearly balance AFTER this deduction
        // totalUsedInLeaveYear already includes the just-updated $balance->used
        // since we haven't saved yet, we calculate manually:
        $totalUsedOtherMonths = self::where('user_id', $userId)
            ->where('leave_year_start', $leaveYearStart)
            ->where('month', '!=', $month)
            ->where('year', '!=', $year)  // same month different year edge case
            ->sum('used');

        // Handle edge: same month/year uniqueness — just sum all except current
        $totalUsedOtherMonths = (float) self::where('user_id', $userId)
            ->where('leave_year_start', $leaveYearStart)
            ->where(function ($q) use ($month, $year) {
                $q->where('month', '!=', $month)
                  ->orWhere('year', '!=', $year);
            })
            ->sum('used');

        $totalUsedNow = $totalUsedOtherMonths + $balance->used;

        // Balance across the full year
        $balance->balance = max(0, $entitlement - $totalUsedNow);

        // LOP: days beyond 12
        $lopDays = max(0, $totalUsedNow - $entitlement);
        if ($lopDays > 0) {
            // How much LOP is attributable to this month's row?
            // Previous LOP on this row stays; we add only the new LOP from this deduction
            $prevTotalUsed = $totalUsedNow - $days;
            $prevLop       = max(0, $prevTotalUsed - $entitlement);
            $newLop        = $lopDays - $prevLop;

            $balance->deduction_flagged = true;
            $balance->deduction_days   += max(0, $newLop);
        }

        // Per-type tracking
        $typeColumn = self::leaveTypeColumn($leaveType);
        if ($typeColumn) {
            $balance->$typeColumn += $days;
        }

        $balance->save();

        // Update balance column on ALL other months in this leave year
        // so the displayed balance stays consistent everywhere
        self::refreshYearlyBalances($userId, $leaveYearStart);

        return $balance;
    }

    // ─────────────────────────────────────────────────────────────────
    // RESTORE LEAVE
    // ─────────────────────────────────────────────────────────────────

    public static function restoreLeave(
        int    $userId,
        int    $month,
        int    $year,
        float  $days,
        string $leaveType = ''
    ): self {
        $balance        = self::findOrCreateBalance($userId, $month, $year);
        $leaveYearStart = self::leaveYearStartFor($month, $year);
        $entitlement    = self::annualEntitlement();

        // Before restore: how much LOP existed?
        $totalUsedBefore = self::totalUsedInLeaveYear($userId, $leaveYearStart);
        $lopBefore       = max(0, $totalUsedBefore - $entitlement);

        // Restore usage on this month's row
        $balance->used = max(0, $balance->used - $days);

        // After restore: recalculate
        $totalUsedAfter = max(0, $totalUsedBefore - $days);
        $lopAfter       = max(0, $totalUsedAfter - $entitlement);
        $lopRestored    = $lopBefore - $lopAfter;

        // Update balance
        $balance->balance = max(0, $entitlement - $totalUsedAfter);

        // Restore LOP flags on this row
        if ($lopRestored > 0) {
            $balance->deduction_days = max(0, $balance->deduction_days - $lopRestored);
            if ($balance->deduction_days <= 0) {
                $balance->deduction_flagged = false;
                $balance->deduction_days    = 0;
            }
        }

        // Per-type tracking
        $typeColumn = self::leaveTypeColumn($leaveType);
        if ($typeColumn) {
            $balance->$typeColumn = max(0, $balance->$typeColumn - $days);
        }

        $balance->save();

        // Refresh balance column on all months in this leave year
        self::refreshYearlyBalances($userId, $leaveYearStart);

        return $balance;
    }

    // ─────────────────────────────────────────────────────────────────
    // REFRESH ALL MONTHLY BALANCE COLUMNS IN A LEAVE YEAR
    // ─────────────────────────────────────────────────────────────────

    /**
     * After any deduct/restore, update the `balance` column on every monthly
     * row in the leave year so they all reflect the same yearly remaining figure.
     */
    public static function refreshYearlyBalances(int $userId, int $leaveYearStart): void
    {
        $totalUsed   = self::totalUsedInLeaveYear($userId, $leaveYearStart);
        $remaining   = max(0, self::annualEntitlement() - $totalUsed);

        self::where('user_id', $userId)
            ->where('leave_year_start', $leaveYearStart)
            ->update(['balance' => $remaining]);
    }

    // ─────────────────────────────────────────────────────────────────
    // ANNUAL SUMMARY
    // ─────────────────────────────────────────────────────────────────

    public static function annualSummary(int $userId, int $leaveYearStart): array
    {
        $rows        = self::where('user_id', $userId)
                           ->where('leave_year_start', $leaveYearStart)
                           ->orderBy('year')->orderBy('month')
                           ->get();

        $totalUsed   = (float) $rows->sum('used');
        $totalLop    = (float) $rows->sum('deduction_days');
        $entitlement = self::annualEntitlement();
        $remaining   = max(0, $entitlement - $totalUsed);

        return [
            'leave_year_start'  => $leaveYearStart,
            'period_label'      => 'Apr ' . $leaveYearStart . ' – Mar ' . ($leaveYearStart + 1),
            'entitlement'       => $entitlement,
            'total_used'        => $totalUsed,
            'total_lop'         => $totalLop,
            'balance_remaining' => $remaining,
            'used_by_type'      => [
                'Casual Leave'     => (float) $rows->sum('used_casual'),
                'Sick Leave'       => (float) $rows->sum('used_sick'),
                'Vacation Leave'   => (float) $rows->sum('used_vacation'),
                'Work From Home'   => (float) $rows->sum('used_wfh'),
                'Compensatory Off' => (float) $rows->sum('used_comp_off'),
                'Maternity Leave'  => (float) $rows->sum('used_maternity'),
            ],
            'monthly_rows' => $rows,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────────────────────────────

    public static function leaveTypeColumn(string $leaveType): ?string
    {
        return match ($leaveType) {
            'Casual Leave'     => 'used_casual',
            'Sick Leave'       => 'used_sick',
            'Vacation Leave'   => 'used_vacation',
            'Work From Home'   => 'used_wfh',
            'Compensatory Off' => 'used_comp_off',
            'Maternity Leave'  => 'used_maternity',
            default            => null,
        };
    }
}