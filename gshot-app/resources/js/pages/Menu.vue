<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import TopNav from "../components/TopNav.vue";
import CartToast from "../components/CartToast.vue";
import { useCartStore } from "../stores/cart";
import { useAuthStore } from "../stores/auth";
import { useProductStore } from "../stores/product";
import AuthModal from "../components/AuthModal.vue";

// Product
const productStore = useProductStore();

//auth
const auth = useAuthStore();
const isAuthOpen = ref(false);
const authMode = ref("login");
console.log(auth.isAdmin);

//cart
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

/* Categories */
const router = useRouter();
const route = useRoute();
const categories = ["All", "Drinks", "Burgers", "Pizzas", "RiceMeals"];

// Category filter
const selectedCategory = ref("All");
const searchQuery = ref("");
const sortOption = ref("default");

function selectCategory(cat) {
    selectedCategory.value = cat;
    router.push({ name: "menu", params: { category: cat } });
}

const filteredProducts = computed(() => {
    let result = productStore.products;

    // Filter by category
    if (selectedCategory.value !== "All") {
        result = result.filter(
            (item) =>
                item.category.toLowerCase() ===
                selectedCategory.value.toLowerCase(),
        );
    }

    // Filter by search
    if (searchQuery.value) {
        result = result.filter((item) =>
            item.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    }

    // Sort by price
    if (sortOption.value === "low-high") {
        result = result.slice().sort((a, b) => a.price - b.price);
    } else if (sortOption.value === "high-low") {
        result = result.slice().sort((a, b) => b.price - a.price);
    }

    return result;
});

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

const isAdminModalOpen = ref(false);
const adminSelectedItem = ref(null);

function openAdminModal(item) {
    adminSelectedItem.value = item;
    isAdminModalOpen.value = true;
}

function closeAdminModal() {
    isAdminModalOpen.value = false;
    adminSelectedItem.value = null;
}

const previewImage = ref(null);

function handleImageUpload(e) {
    const file = e.target.files[0];
    if (file) {
        previewImage.value = URL.createObjectURL(file);
    }
}

const displayProducts = computed(() => {
    if (auth.isAdmin) {
        return [{ id: "add-card", isAddCard: true }, ...filteredProducts.value];
    }
    return filteredProducts.value;
});

function openAddProductModal() {
    adminSelectedItem.value = {
        name: "",
        description: "",
        price: "",
        image: "",
        category: "",
    };
    previewImage.value = null;
    isAdminModalOpen.value = true;
}

watch(
    () => route.params.category,
    (newCategory) => {
        selectedCategory.value = newCategory || "All";
    },
    { immediate: true },
);

onMounted(async () => {
    await productStore.fetchProducts();
});
</script>

<template>
    <TopNav />
    <CartToast ref="toastRef" message="Added to cart!" />
    <section
        class="py-24 px-6 relative bg-[url('/assets/menu-bg.jpg')] bg-cover bg-center min-h-screen"
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
                    v-for="item in displayProducts"
                    :key="item.id"
                    class="bg-white/10 rounded-xl overflow-hidden transition duration-300 hover:scale-105 flex flex-col"
                >
                    <!-- ✅ ADD PRODUCT CARD -->
                    <div
                        v-if="item.isAddCard"
                        @click="openAddProductModal"
                        class="flex flex-col items-center justify-center h-full cursor-pointer border-2 border-dashed border-white/30 hover:border-red-500 text-white rounded-xl"
                    >
                        <div class="text-5xl mb-2">＋</div>
                        <p class="text-sm sm:text-base font-semibold">
                            Add Product
                        </p>
                    </div>

                    <!-- ✅ NORMAL PRODUCT CARD -->
                    <template v-else>
                        <div class="overflow-hidden relative">
                            <img
                                :src="item.image"
                                :alt="item.name"
                                class="w-full h-44 sm:h-52 md:h-56 object-cover transition duration-500 hover:scale-110"
                            />
                            <button
                                v-if="auth.isAdmin"
                                @click="openAdminModal(item)"
                                class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 text-xl cursor-pointer"
                            >
                                ⋮
                            </button>
                        </div>

                        <div
                            class="p-4 sm:p-5 md:p-6 text-gray-200 flex flex-col grow"
                        >
                            <div
                                class="flex justify-between items-start gap-3 mb-2"
                            >
                                <h3
                                    class="text-base sm:text-lg md:text-xl font-semibold"
                                >
                                    {{ item.name }}
                                </h3>

                                <span class="text-red-400 font-bold">
                                    ₱{{ item.price }}
                                </span>
                            </div>

                            <button
                                @click="addToCart(item)"
                                class="mt-auto flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-red-600 hover:bg-red-700 text-white font-medium cursor-pointer"
                            >
                                <i class="pi pi-shopping-cart text-lg"></i>
                                Add to Cart
                            </button>
                        </div>
                    </template>
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
            <h3 class="text-xl font-semibold text-white">
                {{ selectedItem.name }}
            </h3>

            <p class="text-gray-300 mb-3">
                {{ selectedItem.description }}
            </p>

            <!-- Price -->
            <p class="text-red-400 font-bold mb-6">₱{{ selectedItem.price }}</p>

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

    <!-- admin modal -->
    <div
        v-if="isAdminModalOpen && adminSelectedItem"
        class="fixed inset-0 z-50 flex items-center justify-center px-3 sm:px-6"
    >
        <!-- Overlay -->
        <div
            class="absolute inset-0 bg-black/70"
            @click="closeAdminModal"
        ></div>

        <!-- Modal Box -->
        <div
            class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-black/20 backdrop-blur-sm border border-white/20 rounded-xl p-4 sm:p-6 md:p-8 shadow-2xl animate-fade-in flex flex-col md:flex-row gap-4 sm:gap-6"
        >
            <!-- Close -->
            <button
                @click="closeAdminModal"
                class="absolute top-2 right-3 text-gray-300 hover:text-white text-xl cursor-pointer z-10"
            >
                ✕
            </button>

            <!-- Image Preview + Upload -->
            <div class="flex-1 flex flex-col">
                <label class="text-gray-300 mb-1 text-sm sm:text-base"
                    >Image</label
                >

                <img
                    :src="previewImage || adminSelectedItem.image"
                    alt="Product Image"
                    class="w-full h-40 sm:h-48 md:h-56 object-cover rounded-xl mb-2 border border-white/20"
                />

                <input
                    type="file"
                    accept="image/*"
                    @change="handleImageUpload"
                    class="w-full text-xs sm:text-sm text-white file:bg-red-600 file:text-white file:px-3 file:py-1.5 sm:file:px-4 sm:file:py-2 file:rounded-full file:border-0 cursor-pointer"
                />
            </div>

            <!-- Form Fields -->
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <label class="text-gray-300 text-sm sm:text-base"
                        >Name</label
                    >
                    <input
                        v-model="adminSelectedItem.name"
                        class="w-full mb-3 sm:mb-4 px-3 py-2 rounded bg-white/10 text-white focus:outline-none text-sm sm:text-base"
                    />

                    <label class="text-gray-300 text-sm sm:text-base"
                        >Description</label
                    >
                    <textarea
                        v-model="adminSelectedItem.description"
                        rows="4"
                        class="w-full mb-3 sm:mb-4 px-3 py-2 rounded bg-white/10 text-white focus:outline-none resize-none text-sm sm:text-base"
                        placeholder="Enter product description..."
                    ></textarea>

                    <label class="text-gray-300 text-sm sm:text-base"
                        >Price</label
                    >
                    <input
                        v-model="adminSelectedItem.price"
                        type="number"
                        class="w-full mb-3 sm:mb-4 px-3 py-2 rounded bg-white/10 text-white focus:outline-none text-sm sm:text-base"
                    />
                </div>

                <div class="flex justify-between items-center mt-4">
                    <!-- Delete -->
                    <button
                        v-if="adminSelectedItem.id"
                        @click="confirmDelete"
                        class="flex px-5 py-2.5 items-center justify-center rounded-full bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition cursor-pointer"
                    >
                        <i class="pi pi-trash text-lg"></i>
                    </button>

                    <!-- Save -->
                    <button
                        @click="saveAdminEdit"
                        class="px-5 py-2.5 rounded-full bg-green-600 hover:bg-green-700 text-white font-semibold transition cursor-pointer text-sm sm:text-base"
                    >
                        {{
                            adminSelectedItem.id
                                ? "Save Changes"
                                : "Add Product"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <AuthModal v-model="isAuthOpen" :mode="authMode" />
</template>
