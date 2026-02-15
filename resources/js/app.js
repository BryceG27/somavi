import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

createInertiaApp({
    title: (title) => (title ? `${title} - Casa Kalsa` : 'Casa Kalsa'),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(PrimeVue, { 
            ripple: true, 
            theme: {
                preset: Aura,
                options: {
                    prefix: 'p',
                    darkModeSelector: '.my-app-dark',
                    cssLayer: false
                }
            },
        });

        app.mount(el);
    },
    progress: {
        color: '#c65b3c',
    },
});
