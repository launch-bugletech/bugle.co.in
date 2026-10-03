<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        h2, h3 {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<h2>Attendance Report</h2>

<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Clock In IP</th>
            <th>Clock Out IP</th>

            {{-- Admin-only columns --}}
            @if($isAdmin)
                <th>Present Days</th>
                <th>Absent Days</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @forelse($attendances as $a)
            <tr>
                <td>
                    {{ $a->user->first_name ?? '' }}
                    {{ $a->user->last_name ?? '' }}
                </td>
                <td>{{ $a->clock_in_at }}</td>
                <td>{{ $a->clock_out_at }}</td>
                <td>{{ $a->clock_in_ip }}</td>
                <td>{{ $a->clock_out_ip }}</td>

                {{-- Admin-only summary per employee --}}
                @if($isAdmin)
                    <td>
                        {{ $employeeSummary[$a->user_id]['present'] ?? 0 }}
                    </td>
                    <td>
                        {{ $employeeSummary[$a->user_id]['absent'] ?? 0 }}
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $isAdmin ? 7 : 5 }}" style="text-align:center;">
                    No attendance records found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ================= NON-ADMIN SUMMARY ================= --}}
@if(!$isAdmin && $userSummary)
    <br><br>
    <h3>Attendance Summary</h3>

    <table style="width:50%">
        <tr>
            <th>Total Present Days</th>
            <td>{{ $userSummary['present'] }}</td>
        </tr>
        <tr>
            <th>Total Absent Days</th>
            <td>{{ $userSummary['absent'] }}</td>
        </tr>
        <tr>
            <th>Total Working Hours</th>
            <td>{{ $userSummary['hours'] }}</td>
        </tr>
    </table>
@endif

</body>
</html>
