<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Admin');

        $query = Attendance::with('user');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // ✅ Date takes priority over month
        if ($this->request->date) {
            $query->whereDate('clock_in_at', $this->request->date);
        } elseif ($this->request->month) {
            $month = Carbon::parse($this->request->month);
            $query->whereMonth('clock_in_at', $month->month)
                  ->whereYear('clock_in_at', $month->year);
        }

        // ✅ Employee filter
        if ($this->request->employee) {
            $query->whereHas('user', function ($q) {
                $q->where('first_name', 'like', "%{$this->request->employee}%")
                  ->orWhere('last_name', 'like', "%{$this->request->employee}%");
            });
        }

        return $query->orderBy('clock_in_at')->get();
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Clock In',
            'Clock Out',
            'Duration',
            'Clock In IP',
            'Clock Out IP',
        ];
    }

    public function map($row): array
    {
        $duration = '—';
        if ($row->clock_in_at && $row->clock_out_at) {
            $d = Carbon::parse($row->clock_in_at)->diff(Carbon::parse($row->clock_out_at));
            $hours = ($d->days * 24) + $d->h;
            $duration = "{$hours}h {$d->i}m";
        }

        return [
            ($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? ''),
            $row->clock_in_at  ? Carbon::parse($row->clock_in_at)->format('d M Y, h:i A')  : 'N/A',
            $row->clock_out_at ? Carbon::parse($row->clock_out_at)->format('d M Y, h:i A') : 'Active',
            $duration,
            $row->clock_in_ip  ?? 'N/A',
            $row->clock_out_ip ?? 'N/A',
        ];
    }
}