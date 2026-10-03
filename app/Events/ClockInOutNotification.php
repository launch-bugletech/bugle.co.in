<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClockInOutNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $user;
    public $message;

    public function __construct($username, $message)
    {
        $this->user = $username;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('admin-notifications');
    }

    // 🔥 VERY IMPORTANT
    public function broadcastAs()
    {
        return 'clock.in.out';
    }
}
