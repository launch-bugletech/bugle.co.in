<?php
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClockInOutDBNotification extends Notification
{
    use Queueable;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message
        ];
    }
}
