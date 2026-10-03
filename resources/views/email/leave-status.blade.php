<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .header.approved { background-color: #aff1b1ff; }
        .header.rejected { background-color: #f6a6a1ff; }
        .header.cancelled { background-color: #f4cc8fff; }

        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .details.approved { border-left: 4px solid #aff1b1ff; }
        .details.rejected { border-left: 4px solid #f6a6a1ff; }
        .details.cancelled { border-left: 4px solid #f4cc8fff; }

        .detail-row { margin: 10px 0; }

        .label {
            font-weight: bold;
            color: #555;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
            color: #fff;
        }

        .status-badge.approved { background-color: #aff1b1ff; }
        .status-badge.rejected { background-color: #f6a6a1ff; }
        .status-badge.cancelled { background-color: #f4cc8fff; }

        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="header {{ strtolower($status) }}">
        <h2>Leave Request {{ $status }}</h2>
    </div>

    <div class="content">

        {{-- Greeting --}}
        @if($status === 'Cancelled')
            <p>Hello HR Team,</p>
        @else
            <p>Hello {{ $leave->user->first_name }},</p>
        @endif

        {{-- Message --}}
        @if($status === 'Approved')
            <p>
                After review, we are pleased to inform you that your 
                <strong>{{ $leave->leave_type }}</strong> request has been approved.
                If you have any questions, please contact your manager or HR.
            </p>

        @elseif($status === 'Rejected')
            <p>
                After review, we regret to inform you that your 
                <strong>{{ $leave->leave_type }}</strong> request has been rejected.
                Please contact your manager or HR for clarification.
            </p>

        @elseif($status === 'Cancelled')
            <p>
                The following approved leave has been 
                <strong>cancelled by the employee</strong>.
                Please update the leave records accordingly.
            </p>
        @endif

        {{-- Leave Details --}}
        <div class="details {{ strtolower($status) }}">
            <h3 style="margin-top: 0;">Leave Details</h3>

            <div class="detail-row">
                <span class="label">Employee Name:</span>
                {{ $leave->user->first_name }} {{ $leave->user->last_name }}
            </div>

            <div class="detail-row">
                <span class="label">Leave Type:</span>
                {{ $leave->leave_type }}
            </div>

            <div class="detail-row">
                <span class="label">From Date:</span>
                {{ \Carbon\Carbon::parse($leave->from_date)->format('d M Y') }}
            </div>

            <div class="detail-row">
                <span class="label">To Date:</span>
                {{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}
            </div>

            <div class="detail-row">
                <span class="label">Duration:</span>
                @if($leave->leave_duration === 'Half Day')
                    0.5 Day
                @else
                    {{ \Carbon\Carbon::parse($leave->from_date)
                        ->diffInDays(\Carbon\Carbon::parse($leave->to_date)) + 1 }} day(s)
                @endif
            </div>

            @if($leave->reason)
                <div class="detail-row">
                    <span class="label">Reason:</span><br>
                    {{ $leave->reason }}
                </div>
            @endif

            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="status-badge {{ strtolower($status) }}">
                    {{ $status }}
                </span>
            </div>

            @if($status === 'Rejected' && $leave->reject_leave_reason)
                <div class="alert">
                    <strong>Rejection Reason:</strong><br>
                    {{ $leave->reject_leave_reason }}
                </div>
            @endif

        </div>

        <p style="text-align: center; margin-top: 20px;">
            <a href="{{ url('admin/leaves/' . $leave->id) }}"
               style="background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Full Details
            </a>
        </p>

    </div>

    <div class="footer">
        <p>If you have any questions, please contact HR at jobs@bugletech.com</p>
        <p>&copy; {{ date('Y') }} Bugle Technologies Private Limited. All rights reserved.</p>
    </div>

</body>
</html>
