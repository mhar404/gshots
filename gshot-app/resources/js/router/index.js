import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const routes = [
    {
        path: "/",
        name: "home",
        component: () => import("@/pages/Home.vue"),
    },
    {
        path: "/menu/:category?",
        name: "menu",
        component: () => import("@/pages/Menu.vue"),
    },
    {
        path: "/about",
        name: "about",
        component: () => import("@/pages/About.vue"),
    },
    {
        path: "/contact",
        name: "contact",
        component: () => import("@/pages/Contact.vue"),
    },
    {
        path: "/test",
        name: "test",
        component: () => import("@/pages/Test.vue"),
    },
    {
        path: "/checkout",
        name: "checkout",
        component: () => import("@/pages/Checkout.vue"),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        return { top: 0, behavior: "smooth" };
    },
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    if (to.meta.requiresAuth && !authStore.user) {
        return next({ name: "home" });
    }

    next();
});

export default router;
