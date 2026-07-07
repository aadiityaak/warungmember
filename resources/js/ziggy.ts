import { route as routeFn } from 'ziggy-js';

declare global {
    interface Window {
        route: typeof routeFn;
    }
}

window.route = routeFn;

// Export for direct import in <script setup> when needed
export { routeFn as route };
