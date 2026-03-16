<script setup>
import { ref, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import TopNav from "../components/TopNav.vue";
import CartToast from "../components/CartToast.vue";
import { useCartStore } from "../stores/cart";
import { useAuthStore } from "../stores/auth";
import AuthModal from "../components/AuthModal.vue";

const auth = useAuthStore();
const isAuthOpen = ref(false);
const authMode = ref("login");
const cart = useCartStore();
const toastRef = ref(null);

function confirmAddToCart() {
    if (!auth.user) {
        isAuthOpen.value = true;
    } else {
        cart.addToCart(selectedItem.value, quantity.value);
        toastRef.value.showToast();
        closeModal();
    }

    showModal.value = false;
}

const router = useRouter();
const route = useRoute();

/* Categories */
const categories = ["All", "Drinks", "Burgers", "Pizzas", "Rice Meals"];

/* Menu Items */
const menuItems = [
    {
        id: 1,
        name: "Classic Burger",
        category: "Burgers",
        price: "89.00",
        image: "https://shopsuki.ph/cdn/shop/collections/Classic_Burger_1024x.jpg?v=1644488722",
    },
    {
        id: 2,
        name: "Pepperoni Pizza",
        category: "Pizzas",
        price: "180.00",
        image: "https://img.freepik.com/premium-photo/close-up-pepperoni-pizza-slices-with-sausages-mushroom-olive-bell-pepper-cheese_974629-104137.jpg",
    },
    {
        id: 3,
        name: "Fried Rice Special",
        category: "Rice Meals",
        price: "69.00",
        image: "https://www.easyrecipestotry.com/wp-content/uploads/2026/02/Vegetable-egg-fried-rice.webp",
    },
    {
        id: 4,
        name: "Iced Lemon Tea",
        category: "Drinks",
        price: "29.00",
        image: "https://shwetainthekitchen.com/wp-content/uploads/2023/07/lemon-iced-tea.jpg",
    },
];

/* Selected Category */
const selectedCategory = ref("All");

/* Filters */
const searchQuery = ref("");
const minPrice = ref("");
const maxPrice = ref("");
const sortOption = ref("default");

/* Helper: "$8.99" → 8.99 */
const parsePrice = (price) => parseFloat(price.replace("$", ""));

/* Filtered + Sorted Items */
const filteredItems = computed(() => {
    let filtered = menuItems.filter((item) => {
        const itemPrice = parsePrice(item.price);

        const matchesCategory =
            selectedCategory.value === "All" ||
            item.category === selectedCategory.value;

        const matchesSearch = item.name
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());

        const matchesMin =
            minPrice.value === "" || itemPrice >= parseFloat(minPrice.value);

        const matchesMax =
            maxPrice.value === "" || itemPrice <= parseFloat(maxPrice.value);

        return matchesCategory && matchesSearch && matchesMin && matchesMax;
    });

    /* Sorting */
    if (sortOption.value === "low-high") {
        filtered.sort((a, b) => parsePrice(a.price) - parsePrice(b.price));
    } else if (sortOption.value === "high-low") {
        filtered.sort((a, b) => parsePrice(b.price) - parsePrice(a.price));
    }

    return filtered;
});

/* Select Category */
function selectCategory(cat) {
    selectedCategory.value = cat;
    router.push({ name: "menu", params: { category: cat } });
}

const showModal = ref(false);
const selectedItem = ref(null);
const quantity = ref(1);

function addToCart(item) {
    selectedItem.value = item;
    quantity.value = 1;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}
/* Sync route */
watch(
    () => route.params.category,
    (newCategory) => {
        selectedCategory.value = newCategory || "All";
    },
    { immediate: true },
);
</script>

<template>
    <TopNav />
    <CartToast ref="toastRef" message="Added to cart!" />
    <section
        class="py-24 px-6 relative bg-[url('/assets/menu-bg.jpg')] bg-cover bg-center"
    >
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black/60"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Heading -->
            <div class="text-center mb-14">
                <h2 class="text-4xl md:text-5xl font-extrabold mb-4 text-white">
                    <span
                        class="bg-linear-to-r from-red-500 via-orange-400 to-yellow-400 bg-clip-text text-transparent"
                    >
                        Our Menu
                    </span>
                </h2>
                <div
                    class="mx-auto w-24 h-1 rounded-full bg-linear-to-r from-red-500 via-orange-400 to-yellow-400"
                ></div>
            </div>

            <!-- Category Buttons -->
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-14"
            >
                <!-- Categories -->
                <div
                    class="flex flex-wrap justify-center lg:justify-start gap-4"
                >
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="selectCategory(cat)"
                        class="relative overflow-hidden px-6 py-2 rounded-full text-sm md:text-base cursor-pointer before:absolute before:inset-0 before:bg-red-600 before:scale-x-0 before:origin-left before:transition-transform before:duration-500 before:ease-out hover:before:scale-x-100"
                        :class="
                            selectedCategory === cat
                                ? 'bg-red-600 text-white before:hidden'
                                : 'bg-white/10 text-gray-200'
                        "
                    >
                        <span class="relative z-10">
                            {{ cat }}
                        </span>
                    </button>
                </div>

                <!-- Search + Sort -->
                <div
                    class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-end"
                >
                    <!-- Search -->
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search menu..."
                        class="px-4 py-2 rounded-full bg-white/10 text-white border border-transparent focus:outline-none focus:border-red-600"
                    />

                    <!-- Sort Dropdown -->
                    <select
                        v-model="sortOption"
                        class="p-2 rounded-full bg-white/10 text-white border border-transparent focus:outline-none focus:border-red-600"
                    >
                        <option value="default" class="bg-gray-900">
                            Sort by Price
                        </option>
                        <option value="low-high" class="bg-gray-900">
                            Lowest to Highest
                        </option>
                        <option value="high-low" class="bg-gray-900">
                            Highest to Lowest
                        </option>
                    </select>
                </div>
            </div>

            <!-- Menu Items Grid -->
            <div
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 sm:gap-8 lg:gap-10"
            >
                <div
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="bg-white/10 rounded-xl overflow-hidden transition duration-300 hover:scale-105 flex flex-col"
                >
                    <!-- Image -->
                    <div class="overflow-hidden">
                        <img
                            :src="item.image"
                            :alt="item.name"
                            class="w-full h-44 sm:h-52 md:h-56 object-cover transition duration-500 hover:scale-110"
                        />
                    </div>

                    <!-- Info -->
                    <div
                        class="p-4 sm:p-5 md:p-6 text-gray-200 flex flex-col grow"
                    >
                        <div
                            class="flex justify-between items-start gap-3 mb-2"
                        >
                            <h3
                                class="text-base sm:text-lg md:text-xl font-semibold leading-tight wrap-break-words"
                            >
                                {{ item.name }}
                            </h3>

                            <span
                                class="text-red-400 font-bold text-sm sm:text-base md:text-lg whitespace-nowrap"
                            >
                                {{ item.price }}
                            </span>
                        </div>

                        <p class="text-gray-300 text-sm mb-4 flex-1">
                            {{ item.description }}
                        </p>

                        <!-- Add to Cart Button -->
                        <button
                            @click="addToCart(item)"
                            class="mt-auto flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-red-600 hover:bg-red-700 text-white font-medium text-sm sm:text-base cursor-pointer"
                        >
                            <i class="pi pi-shopping-cart text-lg"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
     CONFIRM CART MODAL
========================= -->
    <div
        v-if="showModal && selectedItem"
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/70" @click="closeModal"></div>

        <!-- Modal Box -->
        <div
            class="relative bg-black/20 backdrop-blur-sm border border-white/20 rounded-xl p-8 w-[90%] max-w-md shadow-2xl animate-fade-in"
        >
            <!-- Close -->
            <button
                @click="closeModal"
                class="absolute top-2 right-3 text-gray-300 hover:text-white text-xl cursor-pointer"
            >
                ✕
            </button>

            <!-- Image -->
            <img
                :src="selectedItem.image"
                :alt="selectedItem.name"
                class="w-full h-48 object-cover rounded-2xl mb-5"
            />

            <!-- Name -->
            <h3 class="text-xl font-semibold text-white mb-2">
                {{ selectedItem.name }}
            </h3>

            <!-- Price -->
            <p class="text-red-400 font-bold mb-6">
                {{ selectedItem.price }}
            </p>

            <!-- Quantity -->
            <div class="flex items-center justify-between mb-6">
                <span class="text-gray-300">Quantity</span>

                <div class="flex items-center gap-4">
                    <button
                        @click="quantity > 1 ? quantity-- : null"
                        class="w-8 h-8 rounded bg-white/10 text-white flex items-center justify-center cursor-pointer"
                    >
                        -
                    </button>

                    <span class="text-white font-semibold text-lg">
                        {{ quantity }}
                    </span>

                    <button
                        @click="quantity++"
                        class="w-8 h-8 rounded bg-white/10 text-white flex items-center justify-center cursor-pointer"
                    >
                        +
                    </button>
                </div>
            </div>

            <!-- Confirm -->
            <button
                @click="confirmAddToCart"
                class="w-full py-3 rounded-full bg-red-600 text-white font-semibold hover:bg-red-700 cursor-pointer"
            >
                <i class="pi pi-shopping-cart text-lg"></i>
                Add to Cart
            </button>
        </div>
    </div>

    <AuthModal v-model="isAuthOpen" :mode="authMode" />
</template>
