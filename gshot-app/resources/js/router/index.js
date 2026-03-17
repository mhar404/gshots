import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
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
    ],
    scrollBehavior(to, from, savedPosition) {
        // If navigating back/forward, use saved scroll position
        if (savedPosition) {
            return savedPosition;
        }
        // Always scroll to top for new navigation, with smooth behavior
        return { top: 0, behavior: "smooth" };
    },
});

export default router;
