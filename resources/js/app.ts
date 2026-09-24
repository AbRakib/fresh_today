import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { createNotivue } from 'notivue';
import 'notivue/notification.css';
import 'notivue/notification-progress.css';
import 'notivue/animations.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case ['frontend/Welcome', 'frontend/FreshFish', 'frontend/ProductDetails'].includes(name):
                return null;
            case name.startsWith('backend/auth/'):
                return AuthLayout;
            case name === 'backend/settings/Company':
                return AppLayout;
            case name.startsWith('backend/settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    withApp: (app) => {
        app.use(
            createNotivue({
                pauseOnHover: false,
                pauseOnTouch: false,
                pauseOnTabChange: false,
                notifications: {
                    global: {
                        duration: 4000,
                    },
                },
            }),
        );
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
