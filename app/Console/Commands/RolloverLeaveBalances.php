<?php
// app/Console/Commands/RolloverLeaveBalances.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmployeeLeaveBalance;
use Carbon\Carbon;

class RolloverLeaveBalances extends Command
{
    protected $signature   = 'leave:rollover';
    protected $description = 'Create next-month leave balance rows with carry forward for all employees';

    public function handle(): void
    {
        $now = Carbon::now();

        User::all()->each(function (User $user) use ($now) {
            EmployeeLeaveBalance::rolloverToNextMonth(
                $user->id,
                (int) $now->format('n'),
                (int) $now->format('Y')
            );
        });

        $this->info('Leave balances rolled over for ' . now()->format('F Y'));
    }
}