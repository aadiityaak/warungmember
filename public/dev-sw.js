// Dev-only minimal service worker — no precaching, no Workbox.
// Only handles push notifications so the feature can be tested in development.

self.addEventListener('push', (event) => {
    let data = { title: 'WarungMember', body: '' };
    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch {
            // ignore
        }
    }
    const options = {
        body: data.body,
        icon: data.icon || '/pwa-icons/pwa-192x192.png',
        badge: data.badge || '/pwa-icons/pwa-192x192.png',
        data: { url: data.url || '/member/notifications' },
    };
    event.waitUntil(self.registration.showNotification(data.title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/member/notifications';
    event.waitUntil(
        clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clients) => {
                for (const client of clients) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        client.navigate(url);
                        return client.focus();
                    }
                }
                return self.clients.openWindow(url);
            })
    );
});
