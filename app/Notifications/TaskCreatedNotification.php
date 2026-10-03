<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\User;

class TaskCreatedNotification extends Notification
{
    public function __construct(public $task) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        // Fix: assigned_to is a JSON array of user IDs, not the task id
        $assignedIds = is_array($this->task->assigned_to)
            ? $this->task->assigned_to
            : json_decode($this->task->assigned_to ?? '[]', true);

        $creator = User::find($assignedIds[0] ?? null);
        $username = $creator?->username ?? 'Unknown User';

        return [
            'message' => "{$username} added a new task: {$this->task->title}",
            'task_id' => $this->task->id,
            'type'    => 'task',
        ];
    }
}