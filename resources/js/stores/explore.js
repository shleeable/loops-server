import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "~/plugins/axios";

export const useExploreStore = defineStore("explore", () => {
    const hashtags = ref([]);
    const videos = ref([]);
    const activeHashtag = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const currentVideos = computed(() => {
        if (!activeHashtag.value) return [];
        return videos.value;
    });

    const fetchHashtags = async () => {
        try {
            loading.value = true;
            error.value = null;

            const axiosInstance = axios.getAxiosInstance();
            await axiosInstance
                .get("/api/v1/explore/tags")
                .then((res) => {
                    hashtags.value = res.data;
                    activeHashtag.value = res.data[0];
                })
                .finally(async () => {
                    if (hashtags.value.length > 0) {
                        await fetchVideosByHashtag(activeHashtag.value.name);
                    }
                });
        } catch (err) {
            error.value = "Failed to fetch hashtags";
            console.error("Error fetching hashtags:", err);
        } finally {
            loading.value = false;
        }
    };

    const fetchVideosByHashtag = async (hashtagName) => {
        try {
            loading.value = true;
            error.value = null;

            const axiosInstance = axios.getAxiosInstance();
            await axiosInstance
                .get(`/api/v1/explore/tag-feed/${hashtagName}`)
                .then((res) => {
                    videos.value = res.data.data;
                });
        } catch (err) {
            error.value = "Failed to fetch videos";
            console.error("Error fetching videos:", err);
        } finally {
            loading.value = false;
        }
    };

    const setActiveHashtag = async (hashtag) => {
        if (activeHashtag.value?.id !== hashtag.id) {
            activeHashtag.value = hashtag;
            await fetchVideosByHashtag(hashtag.name);
        }
    };

    return {
        hashtags,
        videos,
        activeHashtag,
        loading,
        error,
        currentVideos,
        fetchHashtags,
        fetchVideosByHashtag,
        setActiveHashtag,
    };
});
