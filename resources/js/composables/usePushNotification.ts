import { ref } from 'vue';
import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';

export function usePushNotification() {
    const isNative = Capacitor.isNativePlatform();
    const supported = ref(isNative || ('serviceWorker' in navigator && 'PushManager' in window));
    const permission = ref<NotificationPermission | string>('default');
    const subscribed = ref(false);

    function getCsrfToken(): string {
        return (
            (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
        );
    }

    async function checkStatus() {
        if (!supported.value) return;

        if (isNative) {
            try {
                const perm = await PushNotifications.checkPermissions();
                permission.value = perm.receive;
                if (perm.receive === 'granted') {
                    subscribed.value = localStorage.getItem('fcm_subscribed') === 'true';
                }
            } catch {
                permission.value = 'denied';
            }
            return;
        }

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
        if (!supported.value) return;

        if (isNative) {
            await subscribeNative();
            return;
        }

        await subscribeWeb();
    }

    async function subscribeNative() {
        try {
            let perm = await PushNotifications.checkPermissions();

            if (perm.receive === 'prompt') {
                perm = await PushNotifications.requestPermissions();
            }

            if (perm.receive !== 'granted') {
                permission.value = perm.receive;
                return;
            }

            permission.value = 'granted';

            await PushNotifications.register();

            PushNotifications.addListener('registration', async (token) => {
                await fetch(route('member.push.fcm.subscribe'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ fcm_token: token.value }),
                });

                localStorage.setItem('fcm_subscribed', 'true');
                subscribed.value = true;
            });

            PushNotifications.addListener('registrationError', () => {
                subscribed.value = false;
            });
        } catch {
            subscribed.value = false;
        }
    }

    async function subscribeWeb() {
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
        if (!supported.value) return;
        await checkStatus();
    }

    return { supported, permission, subscribed, checkStatus, init, subscribe };
}
