import { reactive } from 'vue';

export interface CartItem {
    product_id: number;
    name: string;
    price: number;
    current_price: number;
    image: string | null;
    quantity: number;
}

const state = reactive<{ items: CartItem[]; loaded: boolean }>({
    items: [],
    loaded: false,
});

let syncTimer: ReturnType<typeof setTimeout> | null = null;

function scheduleSync() {
    if (syncTimer) clearTimeout(syncTimer);
    syncTimer = setTimeout(() => syncToServer(), 300);
}

function syncToServer() {
    fetch('/member/cart/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
            ),
        },
        body: JSON.stringify({
            items: state.items.map((i) => ({
                product_id: i.product_id,
                quantity: i.quantity,
            })),
        }),
    });
}

export function useCart() {
    function loadFromServer() {
        if (state.loaded) return;
        fetch('/member/cart', {
            headers: { Accept: 'application/json' },
        })
            .then((r) => r.json())
            .then((data) => {
                state.items.splice(0, state.items.length);
                for (const item of data.items) {
                    state.items.push(item);
                }
                state.loaded = true;
            })
            .catch(() => {
                state.loaded = true;
            });
    }

    function add(product: {
        id: number;
        name: string;
        price: number;
        current_price: number;
        image: string | null;
    }) {
        const existing = state.items.find((i) => i.product_id === product.id);
        if (existing) {
            existing.quantity++;
        } else {
            state.items.push({
                product_id: product.id,
                name: product.name,
                price: product.price,
                current_price: product.current_price,
                image: product.image,
                quantity: 1,
            });
        }
        scheduleSync();
    }

    function remove(productId: number) {
        const idx = state.items.findIndex((i) => i.product_id === productId);
        if (idx !== -1) state.items.splice(idx, 1);
        scheduleSync();
    }

    function updateQty(productId: number, qty: number) {
        const item = state.items.find((i) => i.product_id === productId);
        if (item) {
            item.quantity = Math.max(1, qty);
        }
        scheduleSync();
    }

    function clear() {
        state.items.splice(0, state.items.length);
        if (syncTimer) clearTimeout(syncTimer);
        syncToServer();
    }

    const totalItems = () => state.items.reduce((sum, i) => sum + i.quantity, 0);
    const totalAmount = () => state.items.reduce((sum, i) => sum + i.current_price * i.quantity, 0);

    return {
        items: state.items,
        add,
        remove,
        updateQty,
        clear,
        totalItems,
        totalAmount,
        loadFromServer,
    };
}
