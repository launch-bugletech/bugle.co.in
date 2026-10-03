import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY, // or just your key string
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

window.Echo.private('admin-notifications')
.listen('.clock.in.out', (e) => {
        console.log('New Notification:', e.user + ' ' + e.message);

        // Add notification to bell dropdown
        let dropdown = document.querySelector('#notificationDropdown');
        let li = document.createElement('li');
        li.innerHTML = `<a href="#" class="dropdown-item">${e.user} ${e.message}</a>`;
        dropdown.prepend(li);

        // Optional: increment notification count
        let countBadge = document.querySelector('#notificationCount');
        countBadge.innerText = parseInt(countBadge.innerText || 0) + 1;
    });
