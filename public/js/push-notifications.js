/**
 * Akuntha Push Notification Manager
 * Handles service worker registration and push notification subscription
 */

class PushNotificationManager {
    constructor(options = {}) {
        this.swPath = options.swPath || '/service-worker.js';
        this.serverUrl = options.serverUrl || '/api/push-notifications';
        this.subscribed = false;
        this.registration = null;
        this.subscription = null;
    }

    /**
     * Check if browser supports push notifications
     */
    isSupported() {
        return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    }

    /**
     * Initialize push notifications
     */
    async init() {
        if (!this.isSupported()) {
            console.warn('Push notifications are not supported in this browser');
            return false;
        }

        try {
            // Register service worker
            this.registration = await navigator.serviceWorker.register(this.swPath);
            console.log('Service Worker registered:', this.registration);

            // Check existing subscription
            const existingSubscription = await this.registration.pushManager.getSubscription();
            if (existingSubscription) {
                this.subscription = existingSubscription;
                this.subscribed = true;
                console.log('Existing push subscription found');
            }

            return true;
        } catch (error) {
            console.error('Failed to initialize push notifications:', error);
            return false;
        }
    }

    /**
     * Request notification permission from user
     */
    async requestPermission() {
        if (!this.isSupported()) {
            console.warn('Push notifications are not supported');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        } catch (error) {
            console.error('Failed to request notification permission:', error);
            return false;
        }
    }

    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        if (!this.isSupported()) {
            alert('Push notifications are not supported in your browser');
            return false;
        }

        try {
            // Request permission first
            const hasPermission = await this.requestPermission();
            if (!hasPermission) {
                console.log('Notification permission denied');
                return false;
            }

            // Subscribe to push
            const subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(
                    document.querySelector('meta[name="vapid-public-key"]')?.content || 
                    'BMf6Vy_vUP6tBVglMLGqLsXxR76lLh13lhZDzrxNT2u9n0cSwFaLqCyWl6QfPPtq2QfBVpFhHn9e-SZTWhRZvFE='
                )
            });

            // Send subscription to server
            const response = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    publicKey: this.arrayBufferToBase64(subscription.getKey('p256dh')),
                    authToken: this.arrayBufferToBase64(subscription.getKey('auth')),
                }),
            });

            const data = await response.json();

            if (data.success) {
                this.subscription = subscription;
                this.subscribed = true;
                console.log('Successfully subscribed to push notifications');
                this.showNotification('Notifications Enabled', 'You will now receive push notifications');
                return true;
            } else {
                console.error('Server subscription failed:', data.message);
                return false;
            }
        } catch (error) {
            console.error('Failed to subscribe to push notifications:', error);
            return false;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        if (!this.registration) {
            return false;
        }

        try {
            const subscription = await this.registration.pushManager.getSubscription();
            
            if (subscription) {
                // Notify server
                await fetch('/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint,
                    }),
                });

                // Unsubscribe from push
                await subscription.unsubscribe();
                this.subscription = null;
                this.subscribed = false;
                console.log('Successfully unsubscribed from push notifications');
                return true;
            }
        } catch (error) {
            console.error('Failed to unsubscribe from push notifications:', error);
            return false;
        }
    }

    /**
     * Check if currently subscribed
     */
    isSubscribed() {
        return this.subscribed;
    }

    /**
     * Show a local notification
     */
    showNotification(title, options = {}) {
        if (!this.registration) {
            return;
        }

        const defaultOptions = {
            icon: '/Bugle_logo.png',
            badge: '/Bugle_logo.png',
            tag: 'akuntha-notification',
            requireInteraction: false,
            ...options,
        };

        this.registration.showNotification(title, defaultOptions);
    }

    /**
     * Convert VAPID key from Base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    /**
     * Convert ArrayBuffer to Base64
     */
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    /**
     * Get all active subscriptions for current user
     */
    async getSubscriptions() {
        try {
            const response = await fetch('/push/subscriptions', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            return await response.json();
        } catch (error) {
            console.error('Failed to get subscriptions:', error);
            return null;
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', async function() {
    window.pushNotificationManager = new PushNotificationManager();
    await window.pushNotificationManager.init();

    // Auto-subscribe if notification permission is already granted
    if (Notification.permission === 'granted' && !window.pushNotificationManager.isSubscribed()) {
        console.log('Auto-subscribing to push notifications');
        await window.pushNotificationManager.subscribe();
    }
});
