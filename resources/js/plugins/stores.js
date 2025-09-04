import { useAuthStore } from "~/stores/auth";
import { useProfileStore } from "~/stores/profile";
import { useAppStore } from "~/stores/app";
import { useExploreStore } from "~/stores/explore";
import { useVideoStore } from "~/stores/video";

export default {
    install: (app) => {
        const authStore = useAuthStore();
        const profileStore = useProfileStore();
        const appStore = useAppStore();
        const exploreStore = useExploreStore();
        const videoStore = useVideoStore();

        app.provide("authStore", authStore);
        app.provide("profileStore", profileStore);
        app.provide("appStore", appStore);
        app.provide("exploreStore", exploreStore);
        app.provide("videoStore", videoStore);

        app.config.globalProperties.$authStore = authStore;
        app.config.globalProperties.$profileStore = profileStore;
        app.config.globalProperties.$appStore = appStore;
        app.config.globalProperties.$exploreStore = exploreStore;
        app.config.globalProperties.$videoStore = videoStore;
    },
};
