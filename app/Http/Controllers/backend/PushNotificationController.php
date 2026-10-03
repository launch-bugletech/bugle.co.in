<?php

namespace App\Http\Controllers\backend;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;

class PushNotificationController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'publicKey' => 'required|string',
            'authToken' => 'required|string',
        ]);

        $user = Auth::user();

        // Check if subscription already exists for this endpoint
        $subscription = PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $request->endpoint)
            ->first();

        if ($subscription) {
            // Update existing subscription
            $subscription->update([
                'public_key' => $request->publicKey,
                'auth_token' => $request->authToken,
                'user_agent' => $request->header('User-Agent'),
                'is_active' => true,
            ]);
        } else {
            // Create new subscription
            $subscription = PushSubscription::create([
                'user_id' => $user->id,
                'endpoint' => $request->endpoint,
                'public_key' => $request->publicKey,
                'auth_token' => $request->authToken,
                'user_agent' => $request->header('User-Agent'),
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to push notifications',
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $user = Auth::user();

        PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unsubscribed from push notifications',
        ]);
    }

    /**
     * Get user's push subscriptions
     */
    public function getSubscriptions(Request $request)
    {
        $user = Auth::user();

        $subscriptions = PushSubscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->select('id', 'endpoint', 'is_active', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
            'count' => $subscriptions->count(),
        ]);
    }

    /**
     * Test push notification
     */
    public function test(Request $request)
    {
        $user = Auth::user();

        // Only admins can test
        if ($user->role !== 'admin' && $user->role_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'subscription_id' => 'required|exists:push_subscriptions,id',
        ]);

        $subscription = PushSubscription::findOrFail($request->subscription_id);

        // Send test notification
        $subscription->user->notify(
            new \App\Notifications\PushNotification(
                'Test Notification',
                'This is a test push notification from Akuntha',
                '/Bugle_logo.png',
                '/Bugle_logo.png',
                ['type' => 'test']
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
        ]);
    }

    /**
     * Show push notification settings page
     */
    public function settings()
    {
        return view('backend.pages.push-notification-settings');
    }
}
