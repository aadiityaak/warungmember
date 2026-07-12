import { ref, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export function useNtfy() {
    const supported = ref(typeof EventSource !== 'undefined');
    const subscribed = ref(false);
    const loading = ref(true);
    const error = ref('');

    let eventSource: EventSource | null = null;
    let topic = '';
    let server = '';

    const BASE = window.location.origin || '';

    function getCsrfToken(): string {
        return (
            (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
        );
    }

    async function checkStatus() {
        if (!supported.value) {
            loading.value = false;
            return;
        }

        try {
            const url = `${BASE}/member/push/status`;
            console.log('[useNtfy] checkStatus — fetching', url);
            const res = await Promise.race([
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                }),
                new Promise<never>((_, reject) =>
                    setTimeout(() => reject(new Error('timeout')), 5000)
                ),
            ]);
            console.log('[useNtfy] checkStatus — response', res.status, res.ok);
            const data = await res.json();
            console.log('[useNtfy] checkStatus — data', data);
            subscribed.value = Boolean(data.subscribed);
            topic = data.topic ?? '';
            server = data.server ?? '';
            console.log('[useNtfy] checkStatus — after set, subscribed =', subscribed.value, 'topic =', topic, 'server =', server);

            if (subscribed.value && topic && server) {
                connectSse(data.server, data.topic);
            }
        } catch (e) {
            console.error('[useNtfy] checkStatus — error', e);
            subscribed.value = false;
        } finally {
            loading.value = false;
        }
    }

    async function subscribe() {
        if (!supported.value) return;

        // Request notification permission — browser or Capacitor (Android WebView)
        const capacitor = (window as any).Capacitor;
        if (capacitor?.Plugins?.LocalNotifications) {
            const perm = await capacitor.Plugins.LocalNotifications.checkPermissions();
            if (perm.display === 'prompt') {
                await capacitor.Plugins.LocalNotifications.requestPermissions();
            }
        } else if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
        }

        try {
            const url = `${BASE}/member/push/subscribe`;
            console.log('[useNtfy] subscribe — fetching', url);
            const res = await Promise.race([
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                }),
                new Promise<never>((_, reject) =>
                    setTimeout(() => reject(new Error('timeout')), 5000)
                ),
            ]);
            console.log('[useNtfy] subscribe — response', res.status, res.ok);
            const data = await res.json();
            console.log('[useNtfy] subscribe — data', data);

            if (data.success) {
                subscribed.value = true;
                topic = data.topic;
                server = data.server;
                connectSse(data.server, data.topic);
            }
        } catch (e) {
            console.error('[useNtfy] subscribe — error', e);
            error.value = 'Gagal mengaktifkan notifikasi';
        }
    }

    async function unsubscribe() {
        console.log('[useNtfy] unsubscribe — called');
        try {
            const url = `${BASE}/member/push/unsubscribe`;
            await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            console.log('[useNtfy] unsubscribe — fetch done');
        } catch (e) {
            console.error('[useNtfy] unsubscribe — fetch error', e);
        }

        disconnectSse();
        subscribed.value = false;
        topic = '';
        server = '';
        console.log('[useNtfy] unsubscribe — state reset, subscribed =', subscribed.value);
    }

    function connectSse(srv: string, tpc: string) {
        disconnectSse();

        const url = `${srv.replace(/\/$/, '')}/${tpc}/sse`;
        eventSource = new EventSource(url);

        eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                handleNotification(data);
            } catch {
                // ignore non-JSON events
            }
        };

        eventSource.onerror = () => {
            // EventSource auto-reconnects
        };
    }

    function disconnectSse() {
        if (eventSource) {
            eventSource.close();
            eventSource = null;
        }
    }

    function handleNotification(data: Record<string, any>) {
        const title = data.title ?? 'WarungMember';
        const body = data.message ?? '';
        const clickUrl = data.click ?? '/member/notifications';

        // Capacitor native notification (Android WebView)
        const capacitor = (window as any).Capacitor;
        if (capacitor?.Plugins?.LocalNotifications) {
            capacitor.Plugins.LocalNotifications.schedule({
                notifications: [{
                    title,
                    body,
                    id: Date.now(),
                    smallIcon: 'ic_stat_icon',
                    largeIcon: 'ic_launcher_round',
                    actionTypeId: 'OPEN_PAGE',
                    extra: { url: clickUrl },
                }],
            }).catch(() => {});
        } else if ('Notification' in window && Notification.permission === 'granted') {
            // Fallback: browser notification
            new Notification(title, {
                body,
                icon: '/pwa-icons/pwa-192x192.png',
                tag: 'warungmember-notification',
            });
        }

        // Refresh notification list if on notifications page
        if (window.location.pathname.includes('/member/notifications')) {
            router.reload({ only: ['notifications', 'unreadNotifications'] });
        }
    }

    async function init() {
        console.log('[useNtfy] init — starting');
        await checkStatus();
    }

    onUnmounted(() => {
        disconnectSse();
    });

    return { supported, subscribed, loading, error, checkStatus, init, subscribe, unsubscribe };
}
