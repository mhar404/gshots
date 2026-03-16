<script setup>
import { ref } from "vue";

// Props for custom message
const props = defineProps({
    message: { type: String, default: "Item added to cart!" },
    duration: { type: Number, default: 2000 }, // milliseconds
});

const visible = ref(false);

// Show the toast
const showToast = () => {
    visible.value = true;
    setTimeout(() => {
        visible.value = false;
    }, props.duration);
};

// Expose the showToast function to parent
defineExpose({ showToast });
</script>

<template>
    <transition name="fade-slide">
        <div
            v-if="visible"
            class="fixed top-20 right-5 bg-red-600 text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50"
        >
            <i class="pi pi-check-circle text-lg"></i>
            <span>{{ message }}</span>
        </div>
    </transition>
</template>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
