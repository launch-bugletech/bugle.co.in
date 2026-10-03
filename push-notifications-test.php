#!/usr/bin/env php
<?php

/**
 * Quick Test Script for Push Notifications
 * 
 * Usage: php artisan tinker
 * Then copy/paste the code below
 */

// Get an admin user
$admin = \App\Models\User::where('role_id', 1)->first();

if (!$admin) {
    echo "No admin user found\n";
    exit(1);
}

echo "Testing push notification for admin: {$admin->name}\n";

// Send a test notification
$admin->notify(
    new \App\Notifications\PushNotification(
        title: 'Test Notification',
        body: 'This is a test push notification sent at ' . now()->format('H:i:s'),
        icon: '/Bugle_logo.png',
        badge: '/Bugle_logo.png',
        data: ['type' => 'test', 'timestamp' => now()->toIso8601String()]
    )
);

echo "✓ Notification sent to {$admin->name}\n";
echo "\nNext steps:\n";
echo "1. Go to /push/settings in the admin panel\n";
echo "2. Click 'Enable Notifications'\n";
echo "3. Grant browser permission\n";
echo "4. You should see the test notification\n";
