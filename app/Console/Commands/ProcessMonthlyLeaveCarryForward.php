<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmployeeLeaveBalance;
use Carbon\Carbon;

class ProcessMonthlyLeaveCarryForward extends Command
{
    protected $signature   = 'leaves:carry-forward {--month= : Month (1-12)} {--year= : Year}';
    protected $description = 'Credit 1 leave per employee for the given month and carry forward unused balance';

    public function handle(): void
    {
        $month = (int) ($this->option('month') ?? Carbon::now()->format('n'));
        $year  = (int) ($this->option('year')  ?? Carbon::now()->format('Y'));

        $this->info("Processing carry-forward for {$month}/{$year}...");

        $employees = User::where('status', 'active')->get(); // adjust scope as needed

        $bar = $this->output->createProgressBar($employees->count());
        $bar->start();

        foreach ($employees as $employee) {
            // getOrCreate automatically pulls previous month carry-forward
            EmployeeLeaveBalance::findOrCreateBalance($employee->id, $month, $year);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! Leave balances created/updated for all active employees.');
    }
}