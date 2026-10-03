@extends(backendView('layouts.app'))

@section('title', 'Push Notification Settings')

@section('content')
<div class="container-xxl">
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Push Notification Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h6>Browser Notifications</h6>
                            <p class="text-muted">Receive instant notifications on your computer</p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 3)
                                <button id="enable-push-btn" class="btn btn-primary btn-sm" onclick="enablePushNotifications()">
                                    <i class="icofont-bell"></i> Enable Notifications
                                </button>
                                <button id="disable-push-btn" class="btn btn-secondary btn-sm d-none" onclick="disablePushNotifications()">
                                    <i class="icofont-bell-alt"></i> Disable Notifications
                                </button>
                            @endif
                        </div>
                    </div>

                    <div id="notification-status" class="alert alert-info d-none" role="alert">
                        <strong id="status-message"></strong>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <h6>Active Subscriptions</h6>
                            <div id="subscriptions-list" class="mt-3">
                                <div class="text-muted">Loading subscriptions...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function enablePushNotifications() {
    try {
        const success = await window.pushNotificationManager.subscribe();
        
        if (success) {
            document.getElementById('enable-push-btn').classList.add('d-none');
            document.getElementById('disable-push-btn').classList.remove('d-none');
            
            const status = document.getElementById('notification-status');
            status.classList.remove('d-none');
            status.classList.remove('alert-danger');
            status.classList.add('alert-success');
            document.getElementById('status-message').textContent = '✓ Push notifications enabled successfully!';
            
            loadSubscriptions();
        } else {
            showStatus('Failed to enable push notifications. Please check your browser settings.', 'danger');
        }
    } catch (error) {
        console.error('Error enabling push notifications:', error);
        showStatus('Error: ' + error.message, 'danger');
    }
}

async function disablePushNotifications() {
    try {
        const success = await window.pushNotificationManager.unsubscribe();
        
        if (success) {
            document.getElementById('enable-push-btn').classList.remove('d-none');
            document.getElementById('disable-push-btn').classList.add('d-none');
            
            const status = document.getElementById('notification-status');
            status.classList.remove('d-none');
            status.classList.remove('alert-success');
            status.classList.add('alert-warning');
            document.getElementById('status-message').textContent = '⊘ Push notifications disabled.';
            
            loadSubscriptions();
        } else {
            showStatus('Failed to disable push notifications.', 'danger');
        }
    } catch (error) {
        console.error('Error disabling push notifications:', error);
        showStatus('Error: ' + error.message, 'danger');
    }
}

async function loadSubscriptions() {
    try {
        const response = await window.pushNotificationManager.getSubscriptions();
        
        if (response && response.subscriptions) {
            const list = document.getElementById('subscriptions-list');
            
            if (response.subscriptions.length === 0) {
                list.innerHTML = '<div class="text-muted">No active subscriptions</div>';
                return;
            }
            
            let html = '<table class="table table-sm"><thead><tr><th>Device</th><th>Subscribed</th><th>Action</th></tr></thead><tbody>';
            
            response.subscriptions.forEach((sub, index) => {
                const date = new Date(sub.created_at).toLocaleDateString();
                const device = sub.user_agent ? sub.user_agent.substring(0, 50) + '...' : 'Unknown Device';
                html += `
                    <tr>
                        <td><small>${device}</small></td>
                        <td><small>${date}</small></td>
                        <td>
                            <button class="btn btn-xs btn-warning" onclick="testNotification(${sub.id})">
                                <i class="icofont-send"></i> Test
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            list.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading subscriptions:', error);
    }
}

async function testNotification(subscriptionId) {
    try {
        const response = await fetch('/push/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                subscription_id: subscriptionId,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            showStatus('Test notification sent!', 'success');
        } else {
            showStatus('Failed to send test notification: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error sending test notification:', error);
        showStatus('Error: ' + error.message, 'danger');
    }
}

function showStatus(message, type = 'info') {
    const status = document.getElementById('notification-status');
    status.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
    status.classList.add('alert-' + type);
    document.getElementById('status-message').textContent = message;
}

document.addEventListener('DOMContentLoaded', function() {
    // Check current subscription status
    if (window.pushNotificationManager && window.pushNotificationManager.isSubscribed()) {
        document.getElementById('enable-push-btn').classList.add('d-none');
        document.getElementById('disable-push-btn').classList.remove('d-none');
    }
    
    loadSubscriptions();
});
</script>
@endsection
