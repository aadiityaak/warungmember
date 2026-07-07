import { reactive } from 'vue';

export interface CartItem {
    product_id: number;
    name: string;
    price: number;
    current_price: number;
    image: string | null;
    quantity: number;
}

const state = reactive<{ items: CartItem[] }>({
    items: [],
});

export function useCart() {
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
    }

    function remove(productId: number) {
        const idx = state.items.findIndex((i) => i.product_id === productId);
        if (idx !== -1) state.items.splice(idx, 1);
    }

    function updateQty(productId: number, qty: number) {
        const item = state.items.find((i) => i.product_id === productId);
        if (item) {
            item.quantity = Math.max(1, qty);
        }
    }

    function clear() {
        state.items.splice(0, state.items.length);
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
    };
}
