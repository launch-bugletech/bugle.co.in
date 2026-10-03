<div style="text-align:center; margin-bottom: 20px;">
    <img src="{{ asset('public/Bugle_logo.png') }}" alt="Bugle Tech" style="max-width:180px;">
</div>

<h2 style="margin-bottom: 10px;"> Task Updated</h2>

<p>Hello <strong>{{ $task->employee->username }}</strong>,</p>

<p>Your assigned task has been updated. Please review the latest details below:</p>

<hr style="margin: 15px 0;">

<p>
    <strong>Task Title:</strong> {{ $task->title }} <br>
    <strong>Description:</strong> {{ $task->description ?? '—' }} <br>
    <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $task->status)) }} <br>
    <strong>Priority:</strong> {{ ucfirst($task->priority) }}
</p>

<hr style="margin: 15px 0;">

<p>If you have any questions or need clarification, please contact your manager.</p>

<p>
    Thanks,<br>
    <strong>{{ config('app.name') }}</strong>
</p>
