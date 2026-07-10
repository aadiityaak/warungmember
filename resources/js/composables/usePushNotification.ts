import { ref } from 'vue';

export function usePushNotification() {
    const subscribed = ref(false);
    const supported = 'serviceWorker' in navigator && 'PushManager' in window;

    function getCsrfToken(): string {
        return (
            (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
        );
    }

    async function subscribe() {
        if (!supported) return;
        try {
            const registration = await navigator.serviceWorker.ready;
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                const res = await fetch(route('member.push.vapid-key'));
                const { publicKey } = await res.json();
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: publicKey,
                });
            }

            const subJson = subscription.toJSON();
            await fetch(route('member.push.subscribe'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(subJson),
            });

            subscribed.value = true;
        } catch {
            // permission denied or error
        }
    }

    async function init() {
        if (!supported) return;
        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                await subscribe();
            }
        } catch {
            // ignore
        }
    }

    return { supported, subscribed, init, subscribe };
}
