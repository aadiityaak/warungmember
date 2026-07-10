import { ref } from 'vue';

export function usePushNotification() {
    const supported = 'serviceWorker' in navigator && 'PushManager' in window;
    const permission = ref<NotificationPermission>('default');
    const subscribed = ref(false);

    function getCsrfToken(): string {
        return (
            (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
        );
    }

    async function checkStatus() {
        if (!supported) return;
        permission.value = Notification.permission;
        if (permission.value !== 'granted') {
            subscribed.value = false;
            return;
        }
        try {
            const registration = await navigator.serviceWorker.getRegistration();
            const sub = registration
                ? await registration.pushManager.getSubscription()
                : null;
            subscribed.value = sub !== null;
        } catch {
            subscribed.value = false;
        }
    }

    async function subscribe() {
        if (!supported) return;
        permission.value = Notification.permission;
        if (permission.value === 'denied') return;
        if (permission.value === 'default') {
            permission.value = await Notification.requestPermission();
        }
        if (permission.value !== 'granted') return;
        try {
            const registration = await navigator.serviceWorker.getRegistration();
            if (!registration) return;
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
            permission.value = 'granted';
        } catch {
            // permission denied or error
        }
    }

    async function init() {
        if (!supported) return;
        await checkStatus();
    }

    return { supported, permission, subscribed, checkStatus, init, subscribe };
}
