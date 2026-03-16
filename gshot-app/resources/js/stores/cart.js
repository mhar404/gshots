import { defineStore } from "pinia";

export const useCartStore = defineStore("cart", {
    state: () => ({
        items: [],
    }),

    getters: {
        cartCount: (state) =>
            state.items.reduce((total, item) => total + item.quantity, 0),

        cartTotal: (state) =>
            state.items.reduce(
                (total, item) => total + item.price * item.quantity,
                0,
            ),
    },

    actions: {
        addToCart(product, quantity = 1) {
            const existing = this.items.find(
                (item) => item.product_id === product.id,
            );

            if (existing) {
                existing.quantity += quantity;
            } else {
                this.items.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: quantity,
                });
            }
        },

        removeItem(product_id) {
            this.items = this.items.filter(
                (item) => item.product_id !== product_id,
            );
        },

        updateQuantity(product_id, quantity) {
            const item = this.items.find(
                (item) => item.product_id === product_id,
            );

            if (!item) return;

            // remove item if quantity becomes 0 or less
            if (quantity <= 0) {
                this.removeItem(product_id);
                return;
            }

            item.quantity = quantity;
        },

        clearCart() {
            this.items = [];
        },
    },
});
