import { reactive } from 'vue';
import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';

function getCsrfToken(): string {
    if (typeof window === 'undefined') {
        return '';
    }
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
}

function isPushAvailable(): boolean {
    return Capacitor.isPluginAvailable('PushNotifications');
}

async function checkStatus(): Promise<boolean> {
    if (typeof window === 'undefined') {
        return false;
    }

    try {
        const response = await fetch('/member/push/status', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return false;
        }

        const data = await response.json();
        return data.subscribed === true;
    } catch {
        return false;
    }
}

async function checkPermission(): Promise<boolean> {
    try {
        const perm = await PushNotifications.checkPermissions();
        return perm.receive === 'granted';
    } catch {
        return false;
    }
}

async function requestPermission(): Promise<boolean> {
    try {
        const perm = await PushNotifications.requestPermissions();
        return perm.receive === 'granted';
    } catch {
        return false;
    }
}

function registerPush(): Promise<string> {
    return new Promise((resolve, reject) => {
        const handler = PushNotifications.addListener('registration', (data) => {
            handler.remove();
            resolve(data.value);
        });

        const errorHandler = PushNotifications.addListener('registrationError', (err) => {
            errorHandler.remove();
            handler.remove();
            reject(new Error(err.error));
        });

        PushNotifications.register();

        // Timeout 30s — pastikan ga pending selamanya
        setTimeout(() => {
            handler.remove();
            errorHandler.remove();
            reject(new Error('Timeout registrasi push notification'));
        }, 30000);
    });
}

async function postSubscribe(token: string): Promise<boolean> {
    if (typeof window === 'undefined') {
        return false;
    }

    try {
        const response = await fetch('/member/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({ token }),
        });

        return response.ok;
    } catch {
        return false;
    }
}

async function postUnsubscribe(): Promise<boolean> {
    if (typeof window === 'undefined') {
        return false;
    }

    try {
        const response = await fetch('/member/push/unsubscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
        });

        return response.ok;
    } catch {
        return false;
    }
}

const state = reactive({
    supported: false,
    subscribed: false,
    loading: false,
    error: null as string | null,

    async init() {
        state.loading = true;
        state.error = null;

        if (!state.supported) {
            state.loading = false;
            return;
        }

        try {
            const isSubscribed = await checkStatus();
            state.subscribed = isSubscribed;
        } catch (err) {
            state.error = err instanceof Error ? err.message : 'Gagal memeriksa status notifikasi';
        } finally {
            state.loading = false;
        }
    },

    async subscribe() {
        state.loading = true;
        state.error = null;

        if (!state.supported) {
            state.loading = false;
            return;
        }

        try {
            const hasPerm = await checkPermission();
            if (!hasPerm) {
                const granted = await requestPermission();
                if (!granted) {
                    state.error = 'Izin notifikasi tidak diberikan';
                    state.loading = false;
                    return;
                }
            }

            const token = await registerPush();
            if (!token) {
                state.error = 'Gagal mendapatkan token notifikasi';
                state.loading = false;
                return;
            }

            const ok = await postSubscribe(token);
            if (!ok) {
                state.error = 'Gagal mengaktifkan notifikasi';
                state.loading = false;
                return;
            }

            state.subscribed = true;
        } catch (err) {
            state.error = err instanceof Error ? err.message : 'Gagal mengaktifkan notifikasi';
        } finally {
            state.loading = false;
        }
    },

    async unsubscribe() {
        state.loading = true;
        state.error = null;

        try {
            try {
                await PushNotifications.unregister();
            } catch {
                // lanjutkan
            }

            await postUnsubscribe();
            state.subscribed = false;
        } catch (err) {
            state.error = err instanceof Error ? err.message : 'Gagal menonaktifkan notifikasi';
        } finally {
            state.loading = false;
        }
    },
});

export function useFirebase() {
    state.supported = isPushAvailable();

    return state;
}
