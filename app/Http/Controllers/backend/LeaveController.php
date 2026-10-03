<?php

namespace App\Http\Controllers\backend;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\User;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveCreatedMail;
use App\Mail\LeaveStatusMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeaveRequestNotification;


class LeaveController extends BaseController
{
    // ─── Index ────────────────────────────────────────────────────────
    // LeaveController::index()
    public function index()
    {
        $pagename = 'Leave Index';

        if (auth()->user()->role_id == 1) {
            $leaves = Leave::with('user')->latest()->get();
        } else {
            $leaves = Leave::with('user')
                ->where('user_id', auth()->id())
                ->latest()
                ->get();
        }

        // Fetch current month balance for each unique user in the list
        // In LeaveController::index(), replace the $balances loop with:
        $balances = [];
        foreach ($leaves->unique('user_id') as $leave) {
            // Key by user_id+month+year so each month gets its own balance
            // We still show current month on the index — but now correctly keyed
        }

        // Actually the cleanest fix: pass balances keyed by "userId_month_year"
        $balances = [];
        foreach ($leaves as $leave) {
            $key = $leave->user_id . '_' . $leave->getLeaveMonth() . '_' . $leave->getLeaveYear();
            if (!isset($balances[$key])) {
                $balances[$key] = EmployeeLeaveBalance::findOrCreateBalance(
                    $leave->user_id,
                    $leave->getLeaveMonth(),
                    $leave->getLeaveYear()
                );
            }
        }

        return view('backend.leaves.index', compact('leaves', 'pagename', 'balances'));
    }

    // ─── Create ───────────────────────────────────────────────────────
    public function create()
    {
        $pagename = "Create Leave";

        if (auth()->user()->role_id == 1) {
            $users = User::all();
            $allBalances = [];
            $allYearlySummaries = [];  // ← add this
            $monthlyUsedByType = [];  // ← add this

            foreach ($users as $user) {
                $balance = EmployeeLeaveBalance::findOrCreateBalance(
                    $user->id,
                    (int) now()->format('n'),
                    (int) now()->format('Y')
                );

                $allBalances[$user->id] = [
                    'month' => $balance->month,
                    'year' => $balance->year,
                    'accrued' => $balance->accrued,
                    'carried_forward' => $balance->carried_forward,
                    'used' => $balance->used,
                    'balance' => $balance->balance,
                    'deduction_flagged' => $balance->deduction_flagged,
                    'deduction_days' => $balance->deduction_days,
                    'used_casual' => $balance->used_casual,
                    'used_sick' => $balance->used_sick,
                    'used_vacation' => $balance->used_vacation,
                    'used_wfh' => $balance->used_wfh,
                    'used_comp_off' => $balance->used_comp_off,
                    'used_maternity' => $balance->used_maternity,
                ];

                // ── Yearly summary per user ───────────────────────────
                $leaveYearStart = EmployeeLeaveBalance::leaveYearStartFor(
                    (int) now()->format('n'),
                    (int) now()->format('Y')
                );
                $summary = EmployeeLeaveBalance::annualSummary($user->id, $leaveYearStart);

                $allYearlySummaries[$user->id] = [
                    'period_label' => $summary['period_label'],
                    'entitlement' => $summary['entitlement'],
                    'total_used' => $summary['total_used'],
                    'total_lop' => $summary['total_lop'],
                    'balance_remaining' => $summary['balance_remaining'],
                    'used_by_type' => $summary['used_by_type'],
                ];
            }

            $leaveBalance = null;

        } else {
            $users = User::where('id', auth()->id())->get();
            $leaveBalance = EmployeeLeaveBalance::findOrCreateBalance(
                auth()->id(),
                (int) now()->format('n'),
                (int) now()->format('Y')
            );
            $allBalances = [];
            $allYearlySummaries = [];

            // ── Pass monthly used-by-type for JS warning ──────────────
            $monthlyUsedByType = [
                'Casual Leave' => $leaveBalance->used_casual,
                'Sick Leave' => $leaveBalance->used_sick,
                'Vacation Leave' => $leaveBalance->used_vacation,
                'Work From Home' => $leaveBalance->used_wfh,
                'Compensatory Off' => $leaveBalance->used_comp_off,
                'Maternity Leave' => $leaveBalance->used_maternity,
            ];
        }

        return view('backend.leaves.create', compact(
            'pagename',
            'users',
            'leaveBalance',
            'allBalances',
            'allYearlySummaries',
            'monthlyUsedByType'   // ← add this
        ));
    }
    // ─── Store ────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|string',
            'leave_duration' => 'required|in:Full Day,Half Day',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'reason' => 'nullable|string',
        ]);

        $fromDate = Carbon::parse($request->from_date);
        $month = (int) $fromDate->format('n');
        $year = (int) $fromDate->format('Y');

        $balance = EmployeeLeaveBalance::findOrCreateBalance($request->user_id, $month, $year);
        $typeColumn = EmployeeLeaveBalance::leaveTypeColumn($request->leave_type);

        if ($typeColumn && $balance->$typeColumn >= 1.0) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'leave_type' => 'Your ' . $request->leave_type . ' for ' .
                        $fromDate->format('F Y') . ' is already used.',
                ]);
        }

        $toDate = $request->leave_duration === 'Half Day'
            ? $request->from_date
            : $request->to_date;

        $leave = Leave::create([
            'user_id' => $request->user_id,
            'leave_type' => $request->leave_type,
            'leave_duration' => $request->leave_duration,
            'from_date' => $request->from_date,
            'to_date' => $toDate,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        // ✅ Load user relation before sending mail
        $leave->load('user');

        try {
            // ✅ Fixed: jobs@ instead of dev@
            Mail::to('dev@bugletech.com')->send(new LeaveCreatedMail($leave));
        } catch (\Exception $e) {
            \Log::error('Leave create mail failed: ' . $e->getMessage());
        }

        $admins = \App\Models\User::role('Admin')->get();
Notification::send($admins, new LeaveRequestNotification($leave));



        return redirect()
            ->route('admin.leaves.index')
            ->with('success', 'Leave created successfully');
    }

    // ─── Edit ─────────────────────────────────────────────────────────
    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        $users = User::all();
        $pagename = "Edit Leave";

        // Balance for the leave's own month
        $leaveBalance = EmployeeLeaveBalance::findOrCreateBalance(
            $leave->user_id,
            $leave->getLeaveMonth(),
            $leave->getLeaveYear()
        );

        // Monthly used-by-type for JS warning
        $monthlyUsedByType = [
            'Casual Leave' => $leaveBalance->used_casual,
            'Sick Leave' => $leaveBalance->used_sick,
            'Vacation Leave' => $leaveBalance->used_vacation,
            'Work From Home' => $leaveBalance->used_wfh,
            'Compensatory Off' => $leaveBalance->used_comp_off,
            'Maternity Leave' => $leaveBalance->used_maternity,
        ];

        return view('backend.leaves.edit', compact(
            'leave',
            'users',
            'pagename',
            'leaveBalance',
            'monthlyUsedByType'
        ));
    }


    // ─── Update ───────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|string',
            'leave_duration' => 'required|in:Full Day,Half Day',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'reason' => 'nullable|string',
        ]);

        $leave = Leave::findOrFail($id);
        $fromDate = Carbon::parse($request->from_date);
        $month = (int) $fromDate->format('n');
        $year = (int) $fromDate->format('Y');

        // ── Monthly per-type limit check ──────────────────────────────
        // Skip check if leave type AND month/year haven't changed
        // (employee is just editing reason/dates within the same type)
        $typeChanged = $leave->leave_type !== $request->leave_type;
        $monthChanged = $leave->getLeaveMonth() !== $month || $leave->getLeaveYear() !== $year;

        if ($typeChanged || $monthChanged) {
            $balance = EmployeeLeaveBalance::findOrCreateBalance($request->user_id, $month, $year);
            $typeColumn = EmployeeLeaveBalance::leaveTypeColumn($request->leave_type);

            // When checking, exclude the current leave's own usage
            // (if same type, same month — the existing usage IS this leave)
            $existingUsage = 0;
            if (
                !$typeChanged &&
                !$monthChanged &&
                $leave->status === 'Approved'
            ) {
                $existingUsage = $leave->getLeaveDays();
            }

            if ($typeColumn && ($balance->$typeColumn - $existingUsage) >= 1.0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'leave_type' => 'Your ' . $request->leave_type . ' for ' .
                            $fromDate->format('F Y') . ' is already used. ' .
                            'You cannot apply the same leave type more than once per month.',
                    ]);
            }
        }

        // Restore OLD balance if leave was already Approved
        if ($leave->status === 'Approved') {
            EmployeeLeaveBalance::restoreLeave(
                $leave->user_id,
                $leave->getLeaveMonth(),
                $leave->getLeaveYear(),
                $leave->getLeaveDays(),
                $leave->leave_type
            );
        }

        $toDate = $request->leave_duration === 'Half Day'
            ? $request->from_date
            : $request->to_date;

        $leave->update([
            'user_id' => $request->user_id,
            'leave_type' => $request->leave_type,
            'leave_duration' => $request->leave_duration,
            'from_date' => $request->from_date,
            'to_date' => $toDate,
            'reason' => $request->reason,
        ]);

        return redirect()
            ->route('admin.leaves.index')
            ->with('success', 'Leave updated successfully');
    }

    // ─── Show ─────────────────────────────────────────────────────────
    public function show($id)
    {
        $leave = Leave::with('user')->findOrFail($id);
        $pagename = "View Leave";

        // Pass the current leave balance for this employee/month so it can be shown in the view
        $leaveBalance = EmployeeLeaveBalance::findOrCreateBalance(
            $leave->user_id,
            $leave->getLeaveMonth(),
            $leave->getLeaveYear()
        );

        return view('backend.leaves.show', compact('leave', 'pagename', 'leaveBalance'));
    }

    // ─── Destroy ──────────────────────────────────────────────────────
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        // Restore balance if the leave was approved
        if ($leave->status === 'Approved') {
            EmployeeLeaveBalance::restoreLeave(
                $leave->user_id,
                $leave->getLeaveMonth(),
                $leave->getLeaveYear(),
                $leave->getLeaveDays()
            );
        }

        $leave->delete();

        return redirect()
            ->route('admin.leaves.index')
            ->with('success', 'Leave deleted successfully');
    }

    // ─── Approve ──────────────────────────────────────────────────────
    /**
     * When a leave is approved:
     *  1. Deduct days from the employee's monthly balance.
     *  2. If balance goes negative, flag for salary deduction.
     *  3. Send approval mail.
     */


    public function approve($id)
    {
        // ✅ Eager-load user so $leave->user->email is never null
        $leave = Leave::with('user')->findOrFail($id);

        EmployeeLeaveBalance::deductLeave(
            $leave->user_id,
            $leave->getLeaveMonth(),
            $leave->getLeaveYear(),
            $leave->getLeaveDays(),
            $leave->leave_type
        );

        $leave->update([
            'status' => 'Approved',
            'reject_leave_reason' => null,
        ]);

        try {
            Mail::to($leave->user->email)
                ->send(new LeaveStatusMail($leave, 'Approved'));
        } catch (\Exception $e) {
            \Log::error('Leave approval mail failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
    // ─── Reject ───────────────────────────────────────────────────────
    public function reject(Request $request)
    {
        $request->validate([
            'leave_id' => 'required|exists:leaves,id',
            'reject_reason' => 'nullable|string',
        ]);

        // ✅ Eager-load user so $leave->user->email is never null
        $leave = Leave::with('user')->findOrFail($request->leave_id);

        if ($leave->status === 'Approved') {
            EmployeeLeaveBalance::restoreLeave(
                $leave->user_id,
                $leave->getLeaveMonth(),
                $leave->getLeaveYear(),
                $leave->getLeaveDays(),
                $leave->leave_type
            );
        }

        $leave->update([
            'status' => 'Rejected',
            'reject_leave_reason' => $request->reject_reason,
        ]);

        try {
            Mail::to($leave->user->email)
                ->send(new LeaveStatusMail($leave, 'Rejected'));
        } catch (\Exception $e) {
            \Log::error('Leave rejection mail failed: ' . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Leave rejected successfully');
    }


    // ─── Cancel ───────────────────────────────────────────────────────
    public function cancelled_leave($id)
    {
        // ✅ Eager-load user so $leave->user->email is never null
        $leave = Leave::with('user')->findOrFail($id);
        $user = auth()->user();

        // Admin can cancel any leave
        if ($user->role_id == 1) {

            if ($leave->status === 'Approved') {
                EmployeeLeaveBalance::restoreLeave(
                    $leave->user_id,
                    $leave->getLeaveMonth(),
                    $leave->getLeaveYear(),
                    $leave->getLeaveDays(),
                    $leave->leave_type
                );
            }

            $leave->update(['status' => 'Cancelled']);

            try {
                // ✅ Admin cancels → notify employee on their email
                Mail::to($leave->user->email)
                    ->send(new LeaveStatusMail($leave, 'Cancelled'));
            } catch (\Exception $e) {
                \Log::error('Admin cancel mail failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true]);
        }

        // Employee can cancel only their own approved leave
        if (
            $leave->user_id == $user->id &&
            strtolower($leave->status) == 'approved'
        ) {
            EmployeeLeaveBalance::restoreLeave(
                $leave->user_id,
                $leave->getLeaveMonth(),
                $leave->getLeaveYear(),
                $leave->getLeaveDays(),
                $leave->leave_type
            );

            $leave->update(['status' => 'Cancelled']);

            try {
                // ✅ Employee cancels → notify admin at jobs@
                Mail::to('jobs@bugletech.com')
                    ->send(new LeaveStatusMail($leave, 'Cancelled'));
            } catch (\Exception $e) {
                \Log::error('Employee cancel mail failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => true], 403);
    }
    // ─── Leave Balance (for admin dashboard / employee view) ──────────

    public function balance($userId)
    {
        $user = User::findOrFail($userId);
        $pagename = "Leave Balance — {$user->name}";
        $now = Carbon::now();

        // Current leave-year start (April-based)
        $currentLeaveYearStart = Leave::leaveYearStartFor($now->toDateString());

        // Show current year + previous year
        $annualSummaries = [];
        foreach ([$currentLeaveYearStart - 1, $currentLeaveYearStart] as $lys) {
            $annualSummaries[$lys] = EmployeeLeaveBalance::annualSummary($userId, $lys);
        }

        // Monthly rows for the current leave year (for the detailed breakdown table)
        $monthlyBalances = EmployeeLeaveBalance::where('user_id', $userId)
            ->where('leave_year_start', $currentLeaveYearStart)
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('backend.leaves.balance', compact(
            'user',
            'pagename',
            'annualSummaries',
            'monthlyBalances',
            'currentLeaveYearStart'
        ));
    }
}