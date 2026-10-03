<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $icon;
    protected $badge;
    protected $data;

    public function __construct($title, $body, $icon = null, $badge = null, $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->icon = $icon ?? '/Bugle_logo.png';
        $this->badge = $badge ?? '/Bugle_logo.png';
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database', 'push'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => 'push_notification',
            'data' => $this->data,
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'options' => [
                'tag' => 'akuntha-notification',
                'requireInteraction' => true,
                'data' => $this->data,
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }
}
