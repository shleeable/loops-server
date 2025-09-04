<template>
    <div
        @click="displayPost(post)"
        @mouseenter="isHover(true)"
        @mouseleave="isHover(false)"
        class="relative brightness-90 hover:brightness-[1.1] cursor-pointer"
    >
        <div
            v-if="!isLoaded"
            class="absolute flex items-center justify-center top-0 left-0 aspect-[3/4] w-full object-cover rounded-md bg-black"
        >
            <Icon
                class="animate-spin ml-1"
                name="mingcute:loading-line"
                size="100"
                color="#FFFFFF"
            />
        </div>
        <div>
            <video
                ref="videoRef"
                muted
                loop
                class="aspect-[3/5] object-cover rounded-md"
                :src="post.media.src_url"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useHashids } from "@/composables/useHashids";

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

const route = useRoute();
const router = useRouter();
const videoRef = ref(null);
const isLoaded = ref(false);

const displayPost = (post) => {
    const { encodeHashid } = useHashids();
    const postId = encodeHashid(post.id);
    setTimeout(() => router.push(`/v/${postId}`), 300);
};

const isHover = (bool) => {
    if (!videoRef.value) return;

    if (bool) {
        videoRef.value.play();
    } else {
        videoRef.value.pause();
    }
};

onMounted(() => {
    if (videoRef.value) {
        videoRef.value.addEventListener("loadeddata", (e) => {
            if (e.target) {
                setTimeout(() => {
                    isLoaded.value = true;
                }, 200);
            }
        });
    }
});

onBeforeUnmount(() => {
    if (videoRef.value) {
        videoRef.value.pause();
        videoRef.value.currentTime = 0;
        videoRef.value.src = "";
    }
});
</script>
