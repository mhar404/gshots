<script setup>
import { ref } from "vue";

const visible = ref(false);
const message = ref("");
const type = ref("login"); // login | register | logout

const showToast = (msg, toastType = "login", duration = 2500) => {
    message.value = msg;
    type.value = toastType;
    visible.value = true;

    setTimeout(() => {
        visible.value = false;
    }, duration);
};

defineExpose({ showToast });
</script>

<template>
    <transition name="toast">
        <div
            v-if="visible"
            class="fixed top-20 right-5 px-5 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50 text-white"
            :class="{
                'bg-green-600': type === 'login',
                'bg-blue-600': type === 'register',
                'bg-gray-700': type === 'logout',
            }"
        >
            <!-- ICON -->
            <i
                class="pi text-lg"
                :class="{
                    'pi-sign-in': type === 'login',
                    'pi-user-plus': type === 'register',
                    'pi-sign-out': type === 'logout',
                }"
            ></i>

            <!-- MESSAGE -->
            <span>{{ message }}</span>
        </div>
    </transition>
</template>

<style>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
