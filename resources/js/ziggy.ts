// The @routes Blade directive already defines Ziggy config and sets window.route (UMD).
// We only keep the type declaration. Do NOT re-import the ESM route — it can't access
// the `const Ziggy` from the classic script scope, causing "Cannot read properties of
// undefined (reading '<route_name>')".
interface ZiggyRouter {
    current(name?: string, params?: unknown): boolean | string | undefined;
    has(name: string): boolean;
    params: Record<string, unknown>;
}

declare global {
    interface Window {
        route(name?: string, params?: string | number | Record<string, unknown> | (string | number)[], absolute?: boolean, config?: unknown): string | ZiggyRouter;
    }
}

export {}
