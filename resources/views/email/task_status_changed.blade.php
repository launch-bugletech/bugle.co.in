<h2 style="margin-bottom: 10px;">🔔 Task Status Updated</h2>

<p>Hello Admin,</p>

<p>An employee has updated the status of a task. Please find the details below:</p>

<hr style="margin: 15px 0;">

<p>
    <strong>Task Title:</strong> {{ $task->title }} <br>
    <strong>Description:</strong> {{ $task->description ?? '—' }} <br>
    <strong>Employee:</strong> {{ $task->employee->username }} <br>
    <strong>New Status:</strong> {{ ucfirst(str_replace('_',' ', $task->status)) }}
</p>

<hr style="margin: 15px 0;">

<p>
This update was made by the assigned employee. No action is required unless follow-up is needed.
</p>

<p>
Regards,<br>
<strong>{{ config('app.name') }}</strong>
</p>
