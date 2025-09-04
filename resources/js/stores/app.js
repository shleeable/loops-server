import { defineStore } from "pinia";

export const useAppStore = defineStore("app", {
    state: () => ({
        isLoginOpen: false,
    }),
    actions: {
        toggleLoginForm() {
            this.isLoginOpen = !!this.isLoginOpen;
        },
    },
    persist: true,
});
