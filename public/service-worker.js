// Service Worker for handling push notifications
self.addEventListener('push', function(event) {
    console.log('Push notification received:', event);

    const options = {
        icon: '/Bugle_logo.png',
        badge: '/Bugle_logo.png',
        tag: 'akuntha-notification',
        requireInteraction: true,
        actions: [
            {
                action: 'open',
                title: 'Open'
            },
            {
                action: 'close',
                title: 'Close'
            }
        ]
    };

    let title = 'Akuntha Notification';
    let body = 'You have a new notification';

    if (event.data) {
        try {
            const data = event.data.json();
            title = data.title || title;
            body = data.body || body;
            Object.assign(options, data.options || {});
        } catch (e) {
            body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(title, {
            body: body,
            ...options
        })
    );
});

// Handle notification click
self.addEventListener('notificationclick', function(event) {
    console.log('Notification clicked:', event);

    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    // Open or focus the main window
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(function(clientList) {
            // Check if there's already a window open
            for (let i = 0; i < clientList.length; i++) {
                if (clientList[i].url === '/' && 'focus' in clientList[i]) {
                    return clientList[i].focus();
                }
            }
            // If no window is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow('/');
            }
        })
    );
});

// Handle notification close
self.addEventListener('notificationclose', function(event) {
    console.log('Notification closed:', event);
});
