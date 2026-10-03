<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\User;

class LeaveRequestNotification extends Notification
{
    public function __construct(public $leave) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        // Fix: use user_id, not leave id
        $username = User::find($this->leave->user_id)?->username ?? 'Unknown User';


        return [
            'message'  => "{$username} requested leave from {$this->leave->from_date} to {$this->leave->to_date}",
            'leave_id' => $this->leave->id,
            'type'     => 'leave',
        ];
    }
}