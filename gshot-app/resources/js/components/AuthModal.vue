<script setup>
import { ref, reactive, watch } from "vue";
import { useAuthStore } from "@/stores/auth";
import AuthToast from "./AuthToast.vue";

const props = defineProps({
    modelValue: Boolean, // v-model binding for open/close
    mode: { type: String, default: "login" }, // login or register
});

const emit = defineEmits(["update:modelValue"]);
const toastRef = ref(null);
const auth = useAuthStore();
const authMode = ref(props.mode);

const formData = reactive({
    name: "",
    email: "",
    password: "",
});

watch(
    () => props.mode,
    (newMode) => {
        authMode.value = newMode;
        clearInputField();
        auth.errors = {};
    },
);

const submitForm = async () => {
    const route = authMode.value === "login" ? "login" : "register";
    await auth.authenticate(route, formData);

    if (auth.user) {
        const type = authMode.value === "login" ? "login" : "register";

        const message =
            authMode.value === "login"
                ? "Login successful!"
                : "Account created successfully!";

        toastRef.value.showToast(message, type);

        closeModal();
    }
};

const clearInputField = () => {
    formData.name = "";
    formData.email = "";
    formData.password = "";
};

const closeModal = () => {
    emit("update:modelValue", false);
    clearInputField();
    auth.errors = {};
};
</script>

<template>
    <AuthToast ref="toastRef" />
    <div
        v-show="modelValue"
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
        @click.self="closeModal"
    >
        <div
            class="bg-black/20 backdrop-blur-sm border border-white/20 rounded-xl w-full max-w-md p-6 relative shadow-lg"
        >
            <!-- Close Button -->
            <button
                @click="closeModal"
                class="absolute top-3 right-3 text-gray-500 hover:text-red-600 cursor-pointer"
            >
                <i class="pi pi-times text-xl"></i>
            </button>

            <!-- Header -->
            <h2
                class="text-2xl font-bold text-center mb-4 text-gray-800 dark:text-white"
            >
                {{ authMode === "login" ? "Sign In" : "Register" }}
            </h2>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-5">
                <div v-if="authMode === 'register'" class="relative">
                    <input
                        v-model="formData.name"
                        type="text"
                        placeholder=" "
                        required
                        class="peer w-full px-4 pt-5 pb-2 bg-black/40 border border-white/20 rounded-xl text-white focus:border-red-500 focus:ring-2 focus:ring-red-600/40 outline-none transition"
                    />
                    <label
                        class="absolute left-4 top-2 text-gray-400 text-sm transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-2 peer-focus:text-sm peer-focus:text-red-500"
                    >
                        Full Name
                    </label>
                </div>

                <div class="relative">
                    <input
                        v-model="formData.email"
                        type="email"
                        placeholder=" "
                        required
                        class="peer w-full px-4 pt-5 pb-2 bg-black/40 border border-white/20 rounded-xl text-white focus:border-red-500 focus:ring-2 focus:ring-red-600/40 outline-none transition"
                    />
                    <label
                        class="absolute left-4 top-2 text-gray-400 text-sm transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-2 peer-focus:text-sm peer-focus:text-red-500"
                    >
                        Email Address
                    </label>
                    <p
                        v-if="auth.errors.email"
                        class="text-red-600 text-sm mt-1"
                    >
                        {{ auth.errors.email[0] }}
                    </p>
                </div>

                <div class="relative">
                    <input
                        v-model="formData.password"
                        type="password"
                        placeholder=" "
                        required
                        class="peer w-full px-4 pt-5 pb-2 bg-black/40 border border-white/20 rounded-xl text-white focus:border-red-500 focus:ring-2 focus:ring-red-600/40 outline-none transition"
                    />
                    <label
                        class="absolute left-4 top-2 text-gray-400 text-sm transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-2 peer-focus:text-sm peer-focus:text-red-500"
                    >
                        Password
                    </label>
                    <p
                        v-if="auth.errors.password"
                        class="text-red-600 text-sm mt-1"
                    >
                        {{ auth.errors.password[0] }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 rounded-xl font-semibold text-white bg-red-600 hover:bg-red-700 cursor-pointer"
                >
                    {{ authMode === "login" ? "Sign In" : "Register" }}
                </button>
            </form>

            <p
                class="text-sm text-center mt-3 text-gray-500 dark:text-gray-300"
            >
                {{
                    authMode === "login"
                        ? "Don't have an account?"
                        : "Already have an account?"
                }}
                <button
                    @click="
                        authMode = authMode === 'login' ? 'register' : 'login';
                        auth.errors = {};
                        clearInputField();
                    "
                    class="text-red-600 hover:underline cursor-pointer"
                >
                    {{ authMode === "login" ? "Register" : "Sign In" }}
                </button>
            </p>
        </div>
    </div>
</template>
