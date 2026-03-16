import { defineStore } from "pinia";
import api from "@/lib/axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        errors: {},
        loading: false,
    }),

    actions: {
        async authenticate(apiRoute, formData) {
            try {
                const res = await api.post(`/${apiRoute}`, formData);
                this.user = res.data.user;
                localStorage.setItem("token", res.data.token);
                console.log(res.data);
                this.errors = {};
            } catch (error) {
                this.errors = error.response?.data?.errors || {};
                console.log(error.response.data);
            }
        },

        async logout() {
            try {
                const res = await api.post("/logout");
                console.log(res.data);
            } catch (error) {
                console.error(error);
            }

            this.user = null;
            this.token = null;
            this.errors = {};
            localStorage.removeItem("token");
        },

        async getUser() {
            try {
                const res = await api.get("/user");
                this.user = res.data;
                console.log(res.data);
            } catch (error) {
                this.user = null;
                this.token = null;
                localStorage.removeItem("token");
                console.error(error);
            }
        },
    },
});
