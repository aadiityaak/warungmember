/// <reference lib="webworker" />
import { clientsClaim } from 'workbox-core';
import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { NetworkFirst } from 'workbox-strategies';

declare const self: ServiceWorkerGlobalScope;

clientsClaim();
precacheAndRoute(self.__WB_MANIFEST);

// Runtime caching for API routes
registerRoute(
    ({ url }) => url.pathname.startsWith('/api/'),
    new NetworkFirst({
        cacheName: 'api-cache',
        networkTimeoutSeconds: 5,
    })
);

// Push event — received when server sends a push notification
self.addEventListener('push', (event) => {
    let data: { title: string; body: string; icon?: string; badge?: string; url?: string } = {
        title: 'WarungMember',
        body: '',
    };
    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch {
            // ignore
        }
    }
    const options: NotificationOptions = {
        body: data.body,
        icon: data.icon || '/pwa-icons/pwa-192x192.png',
        badge: data.badge || '/pwa-icons/pwa-192x192.png',
        data: { url: data.url || '/member/notifications' },
    };
    event.waitUntil(self.registration.showNotification(data.title, options));
});

// Notification click — open the app at the target URL
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
