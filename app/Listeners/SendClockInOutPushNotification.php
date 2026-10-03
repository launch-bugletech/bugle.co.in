<?php

namespace App\Listeners;

use App\Events\ClockInOutNotification;
use App\Models\User;
use App\Notifications\PushNotification;

class SendClockInOutPushNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ClockInOutNotification  $event
     * @return void
     */
    public function handle(ClockInOutNotification $event)
    {
        // Get all active admins (role_id 1 = admin, role_id 3 = staff with notification access)
        $admins = User::whereIn('role_id', [1, 3])
            ->where('id', '!=', auth()->id()) // Don't notify the person who clocked in/out
            ->get();

        // Send push notification to each admin
        foreach ($admins as $admin) {
            $admin->notify(new PushNotification(
                title: 'Attendance Alert',
                body: "{$event->user} {$event->message}",
                icon: '/Bugle_logo.png',
                badge: '/Bugle_logo.png',
                data: [
                    'type' => 'attendance',
                    'user' => $event->user,
                    'message' => $event->message,
                    'timestamp' => now()->toIso8601String(),
                ]
            ));
        }
    }
}
