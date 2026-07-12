import { onUnmounted, reactive } from 'vue';
import type { FirebaseApp } from 'firebase/app';
import { initializeApp } from 'firebase/app';
import type { Messaging } from 'firebase/messaging';
import { deleteToken, getMessaging, getToken, onMessage } from 'firebase/messaging';

const firebaseConfig = {
    apiKey: 'AIzaSyCqSpwOZzyQGVHAI39e2KNxEHtEQVrUpRA',
    authDomain: 'masmbull.firebaseapp.com',
    projectId: 'masmbull',
    storageBucket: 'masmbull.firebasestorage.app',
    messagingSenderId: '370089596973',
    appId: '1:370089596973:web:YOUR_WEB_APP_ID', // TODO: ganti dari Firebase Console (buat Web app)
};

const VAPID_KEY = 'BCQFBT9kOFZiXT0MQbIPEfER3X3-HiGdQz5zgaU59pxnTNd7xNAyn3Gv1yKeK4ZbSUKrYHtAapMup3MwA1snL_Y';

let firebaseApp: FirebaseApp | null = null;
let messaging: Messaging | null = null;
let onMessageUnsubscribe: (() => void) | null = null;

function getCsrfToken(): string {
    if (typeof window === 'undefined') {
        return '';
    }
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
}

function isSupported(): boolean {
    return typeof window !== 'undefined' && 'serviceWorker' in navigator && 'PushManager' in window;
}

function getFirebaseApp(): FirebaseApp {
    if (!firebaseApp) {
        firebaseApp = initializeApp(firebaseConfig);
    }
    return firebaseApp;
}

function getFirebaseMessaging(): Messaging {
    if (!messaging) {
        messaging = getMessaging(getFirebaseApp());
    }
    return messaging;
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

async function requestFcmToken(): Promise<string | null> {
    try {
        const msg = getFirebaseMessaging();
        const token = await getToken(msg, { vapidKey: VAPID_KEY });
        return token;
    } catch {
        return null;
    }
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

            if (isSubscribed) {
                await requestFcmToken();
            }
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
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                state.error = 'Izin notifikasi tidak diberikan';
                state.loading = false;
                return;
            }

            const token = await requestFcmToken();
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

            try {
                const msg = getFirebaseMessaging();
                onMessageUnsubscribe = onMessage(msg, (_payload: unknown) => {
                    // Foreground message diterima — handled silently,
                    // service worker akan menampilkan notification.
                });
            } catch {
                // onMessage gagal — tidak fatal, service worker tetap handle
            }
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
            if (onMessageUnsubscribe) {
                onMessageUnsubscribe();
                onMessageUnsubscribe = null;
            }

            if (messaging) {
                try {
                    await deleteToken(messaging);
                } catch {
                    // Token mungkin sudah invalid — lanjutkan
                }
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
    state.supported = isSupported();

    onUnmounted(() => {
        if (onMessageUnsubscribe) {
            onMessageUnsubscribe();
            onMessageUnsubscribe = null;
        }
    });

    return state;
}
