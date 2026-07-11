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
            const res = await Promise.race([
                fetch(route('member.push.status'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                }),
                new Promise<never>((_, reject) =>
                    setTimeout(() => reject(new Error('timeout')), 3000)
                ),
            ]);
            const data = await res.json();
            subscribed.value = data.subscribed;
            topic = data.topic ?? '';
            server = data.server ?? '';

            if (data.subscribed && data.topic && data.server) {
                connectSse(data.server, data.topic);
            }
        } catch {
            subscribed.value = false;
        } finally {
            loading.value = false;
        }
    }

    async function subscribe() {
        if (!supported.value) return;

        // Request notification permission first
        if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
        }

        try {
            const res = await Promise.race([
                fetch(route('member.push.subscribe'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }),
                new Promise<never>((_, reject) =>
                    setTimeout(() => reject(new Error('timeout')), 3000)
                ),
            ]);
            const data = await res.json();

            if (data.success) {
                subscribed.value = true;
                topic = data.topic;
                server = data.server;
                connectSse(data.server, data.topic);
            }
        } catch {
            error.value = 'Gagal mengaktifkan notifikasi';
        }
    }

    async function unsubscribe() {
        try {
            await fetch(route('member.push.unsubscribe'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        } catch {
            // ignore
        }

        disconnectSse();
        subscribed.value = false;
        topic = '';
        server = '';
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

        if ('Notification' in window && Notification.permission === 'granted') {
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
        await checkStatus();
    }

    onUnmounted(() => {
        disconnectSse();
    });

    return { supported, subscribed, loading, error, checkStatus, init, subscribe, unsubscribe };
}
