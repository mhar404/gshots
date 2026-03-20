<script setup>
import { onMounted } from "vue";
import { useCartStore } from "@/stores/cart";

const cart = useCartStore();

const props = defineProps({
    modelValue: Boolean,
});

const emit = defineEmits(["update:modelValue"]);

const closeModal = () => {
    emit("update:modelValue", false);
};

const handleCheckout = async () => {
    console.log(
        "Products in cart:",
        cart.items.map((item) => item.product.name),
    );
    console.log(
        "Quantity:",
        cart.items.map((item) => item.quantity),
    );
    console.log("Total price:", cart.cartTotal);
    // await cart.clearCart();
    closeModal();
};

onMounted(async () => {
    if (localStorage.getItem("token")) {
        await cart.fetchCart(); // now authorized
    }
});
</script>

<template>
    <div
        v-if="modelValue && cart.items.length > 0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
    >
        <div
            class="bg-black/20 backdrop-blur-sm border border-white/20 rounded-xl w-full max-w-lg p-6 text-white relative"
        >
            <!-- Close -->
            <button
                @click="closeModal"
                class="absolute top-3 right-4 hover:text-red-500 cursor-pointer"
            >
                <i class="pi pi-times text-xl"></i>
            </button>

            <!-- Title -->
            <h2 class="text-2xl font-bold mb-6">Your Cart</h2>

            <!-- CART ITEMS -->
            <div class="space-y-4">
                <div
                    v-for="item in cart.items"
                    :key="item.product_id"
                    class="flex justify-between items-center border-b border-white/10 pb-2"
                >
                    <div>
                        <p class="font-semibold">{{ item.product.name }}</p>
                        <p class="text-sm text-gray-400">
                            ₱{{ item.product.price }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Decrease quantity -->
                        <button
                            :disabled="item.quantity === 1"
                            @click="
                                cart.updateQuantity(
                                    item.product_id,
                                    item.quantity - 1,
                                )
                            "
                            class="w-8 h-8 rounded bg-white/10 flex items-center justify-center disabled:opacity-40"
                        >
                            -
                        </button>

                        <span>{{ item.quantity }}</span>

                        <!-- Increase quantity -->
                        <button
                            @click="
                                cart.updateQuantity(
                                    item.product_id,
                                    item.quantity + 1,
                                )
                            "
                            class="w-8 h-8 rounded bg-white/10 flex items-center justify-center"
                        >
                            +
                        </button>

                        <!-- Remove item -->
                        <button
                            @click="cart.removeItem(item.product_id)"
                            class="ml-2 text-red-500 hover:text-red-600 transition"
                            title="Remove item"
                        >
                            <i class="pi pi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TOTAL + CHECKOUT -->
            <div class="flex justify-between mt-6 text-lg font-bold">
                <span>Total</span>
                <span>₱{{ cart.cartTotal }}</span>
            </div>

            <button
                @click="handleCheckout"
                class="w-full mt-6 bg-red-600 hover:bg-red-700 py-3 rounded-lg font-semibold transition cursor-pointer"
            >
                Checkout
            </button>
        </div>
    </div>
</template>
