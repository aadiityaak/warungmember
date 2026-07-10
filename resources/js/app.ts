import { createInertiaApp } from '@inertiajs/vue3';
import { createSSRApp, h } from 'vue';
import './ziggy';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/build/sw.js', { scope: '/build/' }).catch(() => {
        // SW registration failed — non-blocking
    });
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'admin/orders/Receipt':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    setup({ el, App, props, plugin }) {
        const app = createSSRApp({ render: () => h(App, props) });
        app.use(plugin);
        app.config.globalProperties.route = window.route;
        app.mount(el);
        return app;
    },
});

initializeFlashToast();
