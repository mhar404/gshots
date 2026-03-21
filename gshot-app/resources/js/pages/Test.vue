<script setup>
import { onMounted, ref } from "vue";
import { useProductStore } from "../stores/product";

const productStore = useProductStore();

onMounted(() => {
    productStore.fetchProducts();
});

const address = ref("");

function getLocation() {
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
            );
            const data = await res.json();

            address.value = data.display_name;
        },
        (error) => {
            alert(error.message);
        },
    );
}
</script>

<template>
    <div class="w-full max-w-md mx-auto space-y-3">
        <!-- Label -->
        <label class="text-sm font-medium text-gray-700">
            Delivery Address
        </label>

        <!-- Textarea -->
        <div class="relative">
            <textarea
                v-model="address"
                rows="3"
                placeholder="Enter your full address"
                class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none p-3 pr-12 text-sm resize-none"
            ></textarea>

            <!-- Location Button inside -->
            <button
                @click="getLocation"
                class="absolute top-2 right-2 flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-blue-500 text-white rounded-lg hover:bg-blue-600 active:scale-95 transition"
            >
                📍 Use
            </button>
        </div>

        <!-- Helper text -->
        <p class="text-xs text-gray-500">
            You can manually edit your address after using your location.
        </p>
    </div>
    <div>
        <h1>Test Page</h1>

        <ul class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <li v-for="item in productStore.products" :key="item.id">
                <img
                    :src="item.image"
                    :alt="item.name"
                    class="w-full h-48 object-cover rounded"
                />
                <h2>{{ item.name }}</h2>
                <p>{{ item.description }}</p>
                <p>${{ item.price }}</p>
            </li>
        </ul>
    </div>
</template>
