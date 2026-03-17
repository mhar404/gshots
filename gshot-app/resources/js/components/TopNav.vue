<script setup>
import { ref, onMounted, onUnmounted, computed, reactive } from "vue";
import { useAuthStore } from "@/stores/auth";
import AuthModal from "./AuthModal.vue";
import CartModal from "./CartModal.vue";
import AuthToast from "./AuthToast.vue";
import { useCartStore } from "@/stores/cart";

const toastRef = ref(null);

const signOut = () => {
    toastRef.value.showToast("Logged out successfully", "logout");
};

const cart = useCartStore();
const isCartOpen = ref(false);

const openCart = () => {
    isCartOpen.value = true;
};

const currentTime = ref("");

let timer;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const isOpen = ref(false); // mobile menu
const isScrolled = ref(false); // header scroll effect
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

// --- AUTH MODAL STATE ---
const auth = useAuthStore();
const isAuthOpen = ref(false);
const authMode = ref("login");

const openAuth = (mode = "login") => {
    authMode.value = mode;
    isAuthOpen.value = true;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    if (localStorage.getItem("token")) {
        auth.getUser();
    }
    window.addEventListener("scroll", handleScroll);
    window.addEventListener("click", handleClickOutside);
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
    window.removeEventListener("click", handleClickOutside);
    clearInterval(timer);
});
</script>

<template>
    <AuthToast ref="toastRef" />
    <nav
        :class="[
            'w-full fixed top-0 z-50 transition-all duration-300',
            isScrolled || isOpen
                ? 'bg-black/40 backdrop-blur-sm  text-white'
                : 'bg-transparent text-white',
        ]"
    >
        <div
            class="mx-auto max-w-7xl flex items-center justify-between px-6 py-2"
        >
            <!-- Logo -->
            <div class="flex items-center">
                <RouterLink :to="{ name: 'home' }" class="cursor-pointer">
                    <img
                        src="/assets/gshot-logo.png"
                        alt="G Shot"
                        class="h-14 w-auto object-contain transition-transform hover:scale-110"
                    />
                </RouterLink>
            </div>

            <!-- Desktop Links -->
            <div class="hidden md:flex space-x-8 font-sm lg:ml-22">
                <RouterLink
                    :to="{ name: 'home' }"
                    class="hover:text-red-600 transition"
                >
                    HOME
                </RouterLink>

                <RouterLink
                    :to="{ name: 'menu' }"
                    class="hover:text-red-600 transition"
                >
                    MENU
                </RouterLink>

                <RouterLink
                    :to="{ name: 'about' }"
                    class="hover:text-red-600 transition"
                >
                    ABOUT
                </RouterLink>
                <RouterLink
                    :to="{ name: 'contact' }"
                    class="hover:text-red-600 transition"
                >
                    CONTACT
                </RouterLink>
                <RouterLink
                    :to="{ name: 'test' }"
                    class="hover:text-red-600 transition"
                >
                    TEST
                </RouterLink>
            </div>

            <!-- Right Actions -->
            <div class="hidden md:flex items-center gap-8 relative">
                <div class="text-sm text-white/80 font-mono">
                    {{ currentTime }}
                </div>
                <button
                    @click="openCart()"
                    class="relative hover:text-red-600 transition cursor-pointer"
                >
                    <i
                        class="pi pi-shopping-cart"
                        style="font-size: 1.5rem"
                    ></i>

                    <span
                        v-if="cart.cartCount > 0"
                        class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"
                    >
                        {{ cart.cartCount }}
                    </span>
                </button>
                <!-- NOT LOGGED IN -->
                <button
                    v-if="!auth.user"
                    @click="openAuth('login')"
                    class="relative hover:text-red-600 transition cursor-pointer"
                >
                    <i class="pi pi-user" style="font-size: 1.5rem"></i>
                </button>

                <!-- LOGGED IN -->
                <div v-else class="relative" ref="dropdownRef">
                    <button
                        @click="isDropdownOpen = !isDropdownOpen"
                        class="flex items-center hover:text-red-600 transition cursor-pointer"
                    >
                        <i class="pi pi-user" style="font-size: 1.5rem"></i>
                    </button>

                    <!-- DROPDOWN -->
                    <div
                        v-if="isDropdownOpen"
                        class="absolute right-0 mt-3 w-56 bg-black/80 border border-white/20 rounded-xl shadow-xl overflow-hidden"
                    >
                        <!-- USER INFO HEADER -->
                        <div class="px-4 py-3">
                            <p class="text-sm font-semibold">
                                Welcome, {{ auth.user?.name }}
                            </p>
                        </div>

                        <!-- MENU ITEMS -->
                        <div class="py-2">
                            <!-- Optional Profile -->
                            <!--
                            <button
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                            >
                                Profile
                            </button>
                            -->

                            <!-- Logout -->
                            <button
                                @click="
                                    auth.logout();
                                    isDropdownOpen = false;
                                    signOut();
                                "
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-500/10 transition flex items-center gap-2 cursor-pointer"
                            >
                                <i class="pi pi-sign-out text-sm"></i>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button
                @click="isOpen = !isOpen"
                class="md:hidden focus:outline-none transition hover:text-red-600"
            >
                <i
                    v-if="!isOpen"
                    class="pi pi-bars transition-all duration-300"
                    style="font-size: 1.5rem"
                ></i>

                <i
                    v-else
                    class="pi pi-times text-2xl transition-all duration-300"
                    style="font-size: 1.5rem"
                ></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div
            v-show="isOpen"
            class="md:hidden text-white px-6 py-6 space-y-4 text-center flex flex-col"
        >
            <RouterLink
                :to="{ name: 'home' }"
                class="hover:text-red-600 transition"
            >
                HOME
            </RouterLink>

            <RouterLink
                :to="{ name: 'menu' }"
                class="hover:text-red-600 transition"
            >
                MENU
            </RouterLink>

            <RouterLink
                :to="{ name: 'about' }"
                class="hover:text-red-600 transition"
            >
                ABOUT
            </RouterLink>
            <RouterLink
                :to="{ name: 'contact' }"
                class="hover:text-red-600 transition"
            >
                CONTACT
            </RouterLink>

            <!-- Mobile Actions -->
            <div class="pt-4 border-t border-white/20 space-y-3">
                <!-- <button
                    @click="openCart()"
                    class="relative hover:text-red-600 transition cursor-pointer"
                >
                    <i
                        class="pi pi-shopping-cart"
                        style="font-size: 1.5rem"
                    ></i>

                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"
                    >
                        {{ cartCount }}
                    </span>
                </button> -->
                <button
                    @click="isSignInOpen = true"
                    class="w-full flex justify-center gap-2 items-center hover:text-red-600"
                >
                    <i class="pi pi-user" style="font-size: 1.5rem"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Sign In Modal Backdrop -->
    <AuthModal v-model="isAuthOpen" :mode="authMode" />
    <CartModal v-model="isCartOpen" />
</template>
