<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\PushSubscription;
use Exception;

class PushChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // Check if notification has toPush method
        if (!method_exists($notification, 'toPush')) {
            return;
        }

        $pushData = $notification->toPush($notifiable);

        // Get all active push subscriptions for this user
        $subscriptions = PushSubscription::where('user_id', $notifiable->id)
            ->where('is_active', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $this->sendPushNotification($subscription, $pushData);
            } catch (Exception $e) {
                // Log error but don't stop processing other subscriptions
                \Log::error('Push notification failed for subscription ' . $subscription->id . ': ' . $e->getMessage());
                
                // Mark subscription as inactive if it fails (e.g., endpoint expired)
                if (strpos($e->getMessage(), '410') !== false) {
                    $subscription->update(['is_active' => false]);
                }
            }
        }
    }

    protected function sendPushNotification(PushSubscription $subscription, array $pushData)
    {
        // For Web Push Protocol (requires web-push library)
        // This is a simplified implementation using cURL
        
        $endpoint = $subscription->endpoint;
        $publicKey = $subscription->public_key;
        $authToken = $subscription->auth_token;

        // Prepare the payload
        $payload = json_encode($pushData);

        // Make HTTP POST request to the push service
        $headers = [
            'Content-Type: application/octet-stream',
            'Content-Length: ' . strlen($payload),
            'TTL: 24' // Time to live in seconds
        ];

        // If you're using a web-push library, you'd use that instead
        // For now, this shows the basic structure
        // In production, install matthiasmullie/minify-web-push
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception('Push notification failed with status ' . $httpCode);
        }

        return true;
    }
}
