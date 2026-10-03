<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Leave Request</title>
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
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
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
            border-left: 4px solid #4CAF50;
        }
        .detail-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
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
    <div class="header">
        <h2>New Leave Request Submitted</h2>
    </div>
    
    <div class="content">
        <p>Hello Admin,</p>
        <p>A new leave request has been submitted and requires your attention.</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="label">Employee:</span> 
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
                {{ \Carbon\Carbon::parse($leave->from_date)->diffInDays(\Carbon\Carbon::parse($leave->to_date)) + 1 }} day(s)
            </div>
            
            @if($leave->reason)
            <div class="detail-row">
                <span class="label">Reason:</span><br>
                {{ $leave->reason }}
            </div>
            @endif
            
            <div class="detail-row">
                <span class="label">Status:</span> 
                <span style="color: #ff9800; font-weight: bold;">{{ $leave->status }}</span>
            </div>
        </div>
        
        <p>Please review and take appropriate action on this leave request.</p>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="{{ url('admin/leaves/' . $leave->id) }}" 
               style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Leave Request
            </a>
        </p>
    </div>
    
    <div class="footer">
        <!--<p>This is an automated email from Bugle Tech Leave Management System.</p>-->
        <p>&copy; {{ date('Y') }} Bugle Technologies Private Limited. All rights reserved.</p>
    </div>
</body>
</html>