<script setup>
import Loader from "./components/Loader.vue";
import { RouterView } from "vue-router";
import { ref, onMounted } from "vue";

const loading = ref(true);

onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 1000);
});
</script>

<template>
    <Loader v-if="loading" />
    <RouterView />
</template>
