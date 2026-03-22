<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";

import TopNav from "../components/TopNav.vue";
import CartToast from "../components/CartToast.vue";
import AuthModal from "../components/AuthModal.vue";
import ProductCard from "../components/ProductCard.vue";

import { useCartStore } from "../stores/cart";
import { useAuthStore } from "../stores/auth";
import { useProductStore } from "../stores/product";

/* ---------------------------- Product ---------------------------- */
const productStore = useProductStore();

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

    // Sort by prices
    if (sortOption.value === "low-high") {
        result = result.slice().sort((a, b) => a.price - b.price);
    } else if (sortOption.value === "high-low") {
        result = result.slice().sort((a, b) => b.price - a.price);
    }

    return result;
});

const displayProducts = computed(() => {
    if (auth.isAdmin) {
        return [{ id: "add-card", isAddCard: true }, ...filteredProducts.value];
    }
    return filteredProducts.value;
});

/* ---------------------------- Auth ---------------------------- */
const auth = useAuthStore();
const isAuthOpen = ref(false);
const authMode = ref("login");
console.log(auth.isAdmin);

/* ---------------------------- Cart ---------------------------- */
const cart = useCartStore();
const toastRef = ref(null);

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

/* ---------------------------- Category Filter ---------------------------- */
const router = useRouter();
const route = useRoute();

const categories = ["All", "Drinks", "Burgers", "Pizzas", "RiceMeals"];
const selectedCategory = ref("All");
const searchQuery = ref("");
const sortOption = ref("default");

function selectCategory(cat) {
    selectedCategory.value = cat;
    router.push({ name: "menu", params: { category: cat } });
}

/* ---------------------------- Admin Product Modal ---------------------------- */
const isAdminModalOpen = ref(false);
const adminSelectedItem = ref(null);
const previewImage = ref(null);
const selectedFile = ref(null);

function openAdminModal(item) {
    adminSelectedItem.value = item;
    isAdminModalOpen.value = true;
    selectedFile.value = null;
    previewImage.value = null;
}

function closeAdminModal() {
    isAdminModalOpen.value = false;
    adminSelectedItem.value = null;
}

function handleImageUpload(e) {
    const file = e.target.files[0];
    if (file) {
        selectedFile.value = file;
        previewImage.value = URL.createObjectURL(file);
    }
}

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

const saveAdminEdit = async () => {
    const formData = new FormData();
    console.log([...formData.entries()]);

    try {
        formData.append("name", adminSelectedItem.value.name);
        formData.append("description", adminSelectedItem.value.description);
        formData.append("price", adminSelectedItem.value.price);
        formData.append("category", adminSelectedItem.value.category);

        if (selectedFile.value) {
            formData.append("image", selectedFile.value);
        }

        if (adminSelectedItem.value.id) {
            await productStore.updateProduct(
                adminSelectedItem.value.id,
                formData,
            );
        } else {
            await productStore.createProduct(formData);
        }

        closeAdminModal();
    } catch (err) {
        console.error("Save failed:", err);
    }
};

function confirmDelete() {
    if (confirm("Are you sure you want to delete this product?")) {
        productStore.deleteProduct(adminSelectedItem.value.id);
        closeAdminModal();
    }
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
                <template v-if="productStore.loading">
                    <div
                        v-for="n in 8"
                        :key="n"
                        class="bg-white/10 rounded-xl overflow-hidden flex flex-col h-full animate-pulse border-white/20"
                    >
                        <!-- Image Placeholder -->
                        <div
                            class="w-full h-40 sm:h-44 md:h-52 lg:h-56 bg-black/50"
                        ></div>

                        <!-- Content Placeholder -->
                        <div class="p-3 sm:p-4 md:p-5 flex flex-col grow gap-2">
                            <!-- Name -->
                            <div
                                class="h-4 sm:h-5 md:h-6 bg-black/50 rounded w-3/4"
                            ></div>
                            <div
                                class="h-4 sm:h-5 md:h-6 bg-black/50 rounded w-1/2"
                            ></div>

                            <!-- Price -->
                            <div
                                class="h-4 w-1/3 bg-black/50 rounded mt-2"
                            ></div>

                            <!-- Add to Cart Button Placeholder -->
                            <div
                                class="mt-auto h-9 sm:h-10 rounded-full bg-black/50 w-full"
                            ></div>
                        </div>
                    </div>
                </template>

                <!-- Actual Product Cards -->
                <template v-else>
                    <ProductCard
                        v-for="item in displayProducts"
                        :key="item.id"
                        :product="item"
                        :is-add-card="item.isAddCard"
                        :is-admin="auth.isAdmin"
                        @add-to-cart="addToCart"
                        @admin-options="openAdminModal"
                        @add-product="openAddProductModal"
                    />
                </template>
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
                    class="w-full h-40 sm:h-48 md:h-56 object-cover rounded-xl mb-2 border border-white/20 text-gray-300 text-center"
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

                    <div v-if="!adminSelectedItem.id">
                        <label class="text-gray-300 text-sm sm:text-base"
                            >Category</label
                        >
                        <select
                            v-model="adminSelectedItem.category"
                            class="w-full mb-3 px-3 py-2 rounded bg-black/40 text-white border border-white/20"
                        >
                            <option
                                value=""
                                disabled
                                class="bg-black text-white"
                            >
                                Select Category
                            </option>
                            <option class="bg-black text-white">Drinks</option>
                            <option class="bg-black text-white">Burgers</option>
                            <option class="bg-black text-white">Pizzas</option>
                            <option class="bg-black text-white">
                                RiceMeals
                            </option>
                        </select>
                    </div>

                    <label class="text-gray-300 text-sm sm:text-base"
                        >Description</label
                    >
                    <textarea
                        v-model="adminSelectedItem.description"
                        rows="4"
                        class="w-full mb-3 sm:mb-4 px-3 py-2 rounded bg-white/10 text-white focus:outline-none resize-none text-sm sm:text-base"
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

                <div class="flex gap-3 mt-4">
                    <!-- Delete -->
                    <button
                        v-if="adminSelectedItem.id"
                        @click="confirmDelete"
                        class="flex-1 flex items-center justify-center px-5 py-2.5 rounded-full bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition cursor-pointer"
                    >
                        <i class="pi pi-trash text-lg"></i>
                    </button>

                    <!-- Save -->
                    <button
                        @click="saveAdminEdit"
                        class="flex-1 flex items-center justify-center px-5 py-2.5 rounded-full bg-green-600 hover:bg-green-700 text-white font-semibold transition cursor-pointer text-sm sm:text-base"
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
