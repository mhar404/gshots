<script setup>
import { onMounted } from "vue";
import { useProductStore } from "../stores/product";

const productStore = useProductStore();

onMounted(() => {
    productStore.fetchProducts();
});
</script>

<template>
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
