<script setup>
import { ref } from "vue";
import { useCartStore } from "@/stores/cart";
import TopNav from "@/components/TopNav.vue";

const cart = useCartStore();

// Form fields
const userName = ref("");
const userPhone = ref("");
const userAddress = ref("");
const paymentMethod = ref("");

// Get location (reverse geocode)
const getLocation = () => {
    navigator.geolocation.getCurrentPosition(async (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;

        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
            );
            const data = await res.json();
            userAddress.value = data.display_name || `${lat}, ${lng}`;
        } catch {
            userAddress.value = `${lat}, ${lng}`;
        }
    });
};

// Submit
const confirmCheckout = () => {
    const items = cart.items.map((item) => ({
        name: item.product.name,
        quantity: item.quantity,
    }));

    const payload = {
        name: userName.value,
        phone: userPhone.value,
        payment: paymentMethod.value,
        address: userAddress.value,
        items: items,
        total: cart.cartTotal,
    };

    console.log(payload);
};
</script>

<template>
    <TopNav />
    <div
        class="relative min-h-screen bg-[url('/assets/menu-bg.jpg')] bg-cover bg-center pt-28 pb-10 px-4 sm:px-6 lg:px-8"
    >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>

        <!-- Content -->
        <div
            class="relative z-10 max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 text-white"
        >
            <!-- LEFT: Orders -->
            <div
                class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-5 sm:p-6 flex flex-col shadow-lg"
            >
                <h2 class="text-xl sm:text-2xl font-semibold mb-4">
                    Your Orders
                </h2>

                <!-- Items -->
                <div
                    class="space-y-4 overflow-y-auto max-h-[55vh] sm:max-h-[60vh] pr-2"
                >
                    <div
                        v-for="item in cart.items"
                        :key="item.product_id"
                        class="flex justify-between items-center border-b border-white/10 pb-3"
                    >
                        <div>
                            <p
                                class="font-medium text-white/90 text-sm sm:text-base"
                            >
                                {{ item.product.name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                ₱{{ item.product.price }} × {{ item.quantity }}
                            </p>
                        </div>

                        <div
                            class="font-semibold text-white/90 text-sm sm:text-base"
                        >
                            ₱{{ item.product.price * item.quantity }}
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div
                    class="mt-5 border-t border-white/10 pt-4 flex justify-between items-center text-base sm:text-lg font-semibold sticky bottom-0"
                >
                    <span class="text-white/70">Total</span>
                    <span class="text-red-500 text-lg sm:text-xl">
                        ₱{{ cart.cartTotal }}
                    </span>
                </div>
            </div>

            <!-- RIGHT: Checkout -->
            <form
                @submit.prevent="confirmCheckout"
                class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-5 sm:p-6 flex flex-col gap-5 shadow-lg"
            >
                <h2 class="text-xl sm:text-2xl font-semibold">
                    Checkout Details
                </h2>

                <!-- Name -->
                <div>
                    <label class="text-xs text-gray-400">Full Name</label>
                    <input
                        v-model="userName"
                        type="text"
                        placeholder="Enter your name"
                        class="w-full mt-2 px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none text-sm sm:text-base"
                        required
                    />
                </div>

                <!-- Phone -->
                <div>
                    <label class="text-xs text-gray-400">Phone Number</label>
                    <input
                        v-model="userPhone"
                        type="tel"
                        placeholder="09XXXXXXXXX"
                        class="w-full mt-2 px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none text-sm sm:text-base"
                        required
                    />
                </div>

                <!-- Address -->
                <div>
                    <label class="text-xs text-gray-400">Address</label>
                    <textarea
                        v-model="userAddress"
                        placeholder="Enter address or use location"
                        class="w-full mt-2 px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none resize-none h-24 text-sm sm:text-base"
                        required
                    ></textarea>

                    <p class="text-xs text-gray-400 mt-1">
                        Auto-filled from your location. Feel free to edit if
                        needed.
                    </p>
                </div>

                <!-- Location Button -->
                <button
                    type="button"
                    @click="getLocation"
                    class="flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 py-2 px-4 rounded-full font-medium shadow-md hover:shadow-lg transition text-sm sm:text-base"
                >
                    <i class="pi pi-map-marker"></i>
                    <span>Use My Location</span>
                </button>

                <!-- Payment -->
                <div>
                    <label class="text-xs text-gray-400">Mode of Payment</label>
                    <select
                        v-model="paymentMethod"
                        class="w-full mt-2 px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none text-sm sm:text-base"
                        required
                    >
                        <option
                            disabled
                            value=""
                            class="bg-black text-gray-400"
                        >
                            Select Mode of Payment
                        </option>

                        <option value="cash" class="bg-black text-white">
                            Cash
                        </option>
                        <option value="gcash" class="bg-black text-white">
                            Gcash
                        </option>
                        <option value="card" class="bg-black text-white">
                            Card
                        </option>
                    </select>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="mt-4 w-full bg-red-600 hover:bg-red-700 py-3 rounded-xl font-semibold shadow-md hover:shadow-lg transition text-sm sm:text-base"
                >
                    Confirm Checkout
                </button>
            </form>
        </div>
    </div>
</template>
