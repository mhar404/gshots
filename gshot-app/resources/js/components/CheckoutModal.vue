<script setup>
import { ref } from "vue";
import { useCartStore } from "@/stores/cart";

const cart = useCartStore();

const props = defineProps({
    modelValue: Boolean,
});

const emit = defineEmits(["update:modelValue"]);

// User details
const userName = ref("");
const userPhone = ref(""); // New field for phone
const userAddress = ref("");
const paymentMethod = ref("cash");

const closeModal = () => emit("update:modelValue", false);

// Get geolocation and reverse geocode
const getLocation = () => {
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
                );
                const data = await res.json();
                userAddress.value = data.display_name || `${lat}, ${lng}`;
            } catch (err) {
                console.error("Error fetching address:", err);
                userAddress.value = `${lat}, ${lng}`;
            }
        },
        (error) => {
            alert(error.message);
        },
    );
};

// Checkout handler
const confirmCheckout = async () => {
    console.log("User:", userName.value);
    console.log("Address:", userAddress.value);
    console.log("Payment:", paymentMethod.value);
    console.log(
        "Cart Items:",
        cart.items.map((i) => i.product.name + " x" + i.quantity),
    );
    console.log("Total:", cart.cartTotal);

    // Optional: call API to process checkout
    // await api.post('/checkout', { ... })

    closeModal();
};
</script>

<template>
    <div
        v-if="modelValue && cart.items.length > 0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 overflow-y-auto"
    >
        <!-- Modal Container -->
        <div
            class="bg-black/20 backdrop-blur-sm border border-white/20 rounded-xl w-full max-w-5xl p-6 text-white relative flex flex-col md:flex-row gap-6 max-h-[90vh] overflow-hidden"
        >
            <!-- Close Button -->
            <button
                @click="closeModal"
                class="absolute top-3 right-4 hover:text-red-500 cursor-pointer z-50"
            >
                <i class="pi pi-times text-xl"></i>
                <span class="sr-only">Close Modal</span>
            </button>

            <!-- LEFT: Orders -->
            <div
                class="flex-1 border-b md:border-b-0 md:border-r border-white/20 pr-0 md:pr-4 flex flex-col overflow-hidden"
            >
                <h2 class="text-2xl font-bold mb-4">Your Orders</h2>

                <!-- Scrollable Cart Items -->
                <div
                    class="space-y-4 overflow-y-auto max-h-64 sm:max-h-72 md:max-h-[calc(90vh-180px)] pr-2"
                >
                    <div
                        v-for="item in cart.items"
                        :key="item.product_id"
                        class="flex justify-between items-center border-b border-white/10 p-2"
                    >
                        <div>
                            <p class="font-semibold">{{ item.product.name }}</p>
                            <p class="text-sm text-gray-400">
                                ₱{{ item.product.price }} x
                                {{ item.quantity }}
                            </p>
                        </div>
                        <div class="font-semibold">
                            ₱{{ item.product.price * item.quantity }}
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div
                    class="flex justify-between font-bold text-lg mt-4 border-t border-white/20 pt-3"
                >
                    <span>Total</span>
                    <span>₱{{ cart.cartTotal }}</span>
                </div>
            </div>

            <!-- RIGHT: User Details -->
            <form
                class="flex-1 flex flex-col gap-4 overflow-y-auto max-h-[calc(90vh-80px)]"
                @submit.prevent="confirmCheckout"
            >
                <h2 class="text-2xl font-bold mb-2">Your Details</h2>

                <label class="font-semibold">Full Name</label>
                <input
                    v-model="userName"
                    type="text"
                    placeholder="Enter your full name"
                    class="w-full p-2 rounded bg-white/10 focus:outline-none"
                    required
                />

                <label class="font-semibold">Phone Number</label>
                <input
                    v-model="userPhone"
                    type="tel"
                    placeholder="Enter your phone number"
                    class="w-full p-2 rounded bg-white/10 focus:outline-none"
                    required
                />

                <label class="font-semibold">Address / Location</label>
                <textarea
                    v-model="userAddress"
                    placeholder="Enter your address or use your location"
                    class="w-full p-2 rounded bg-white/10 focus:outline-none"
                    required
                ></textarea>

                <!-- Elegant icon button -->
                <button
                    type="button"
                    @click="getLocation"
                    class="flex items-center gap-2 bg-linear-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-full shadow-md hover:shadow-lg transition-all duration-300"
                >
                    <i class="pi pi-map-marker text-xl text-white"></i>
                    <span>Use My Location</span>
                </button>

                <label class="font-semibold">Mode of Payment</label>
                <select
                    v-model="paymentMethod"
                    class="w-full p-2 rounded bg-white/10 focus:outline-none"
                    required
                >
                    <option value="cash">Cash</option>
                    <option value="gcash">Gcash</option>
                    <option value="card">Credit/Debit Card</option>
                </select>

                <!-- Sticky Confirm Checkout -->
                <button
                    type="submit"
                    class="mt-auto w-full bg-green-600 hover:bg-green-700 py-3 rounded-lg font-semibold transition cursor-pointer"
                >
                    Confirm Checkout
                </button>
            </form>
        </div>
    </div>
</template>
