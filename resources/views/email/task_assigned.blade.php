<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Task Assigned</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#333;">

    {{-- Logo --}}
    <div style="text-align:center; margin-bottom: 20px;">
        <img src="{{ asset('public/Bugle_logo.png') }}" alt="Bugle Tech" style="max-width:180px;">
    </div>

    <h2 style="margin-bottom: 10px;">New Task Assigned</h2>

    <p>Hello <strong>{{ $task->employee->username }}</strong>,</p>

    <p>You have been assigned a new task. Please find the details below:</p>

    <hr style="margin: 15px 0;">

    <p>
        <strong>Task Title:</strong> {{ $task->title }} <br>
        <strong>Description:</strong> {{ $task->description ?? '—' }} <br>
        <strong>Priority:</strong> {{ ucfirst($task->priority) }} <br>
        <strong>Due Date:</strong>
        {{ $task->due_date
            ? \Carbon\Carbon::parse($task->due_date)->format('d M Y')
            : 'N/A' }}
    </p>

    <hr style="margin: 15px 0;">

    <p>Please make sure to complete the task within the given timeline.</p>

    <p>
        Thanks,<br>
        <strong>{{ config('app.name') }}</strong>
    </p>

</body>
</html>
