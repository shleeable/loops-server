<template>
    <div
        class="bg-white dark:bg-slate-900 rounded-lg shadow flex flex-col h-full md:max-h-[calc(100%-320px)]"
    >
        <div class="flex-1 overflow-y-auto p-3">
            <div v-if="isLoading && !comments.length" class="p-4 text-center">
                <Spinner />
            </div>
            <div v-else-if="error" class="p-4 text-center text-red-500">
                {{ error?.message || "Error loading comments" }}
            </div>

            <div v-else>
                <div
                    v-if="!comments.length"
                    class="p-4 text-center text-gray-500 dark:text-slate-400"
                >
                    <div
                        v-if="!canComment"
                        class="flex flex-col items-center space-y-2"
                    >
                        <i class="bx bx-comment-x text-[36px]"></i>
                        <span>Comments are disabled for this video</span>
                    </div>
                    <div v-else class="flex flex-col items-center space-y-2">
                        <i class="bx bx-comment-dots text-2xl"></i>
                        <span>No comments yet</span>
                    </div>
                </div>
                <div v-else class="">
                    <CommentItem
                        v-for="comment in comments"
                        :key="comment.id"
                        :comment="comment"
                        :videoId="videoId"
                    />

                    <div v-if="hasMore" class="p-4 text-center">
                        <button
                            @click="loadMore"
                            class="text-sm font-medium text-[#F02C56] hover:text-[#F02C56]/70 cursor-pointer"
                            :disabled="isLoading"
                        >
                            Load more
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments input section - only show if authenticated AND comments are enabled -->
        <div
            v-if="authStore.authenticated && canComment"
            class="p-4 border-t border-gray-200 dark:border-slate-800 flex-shrink-0"
        >
            <div
                class="relative flex items-center border border-gray-300 rounded-lg bg-gray-50 dark:bg-slate-800 dark:border-slate-700 overflow-hidden"
            >
                <input
                    v-model="newComment"
                    class="w-full p-3 bg-transparent outline-none dark:text-slate-50 dark:bg-slate-800 border-0"
                    placeholder="Add comment..."
                    @keydown.enter.prevent="handleAddComment"
                />

                <div class="flex items-center px-3">
                    <EmojiPicker
                        v-model="selectedEmoji"
                        @select="onEmojiSelect"
                    >
                        <template #trigger="{ toggle }">
                            <button @click="toggle">😊</button>
                        </template>
                    </EmojiPicker>

                    <button
                        @click="handleAddComment"
                        class="ml-2 text-sm text-gray-400 hover:text-red-500 cursor-pointer font-bold"
                        :disabled="isSubmitting || !newComment.trim()"
                    >
                        Post
                    </button>
                </div>
            </div>
        </div>

        <div
            v-else-if="!canComment"
            class="p-4 border-t border-gray-200 dark:border-slate-800 flex-shrink-0"
        >
            <div class="text-center text-gray-500 dark:text-slate-400 text-sm">
                Comments have been disabled
            </div>
        </div>

        <div
            v-else-if="!authStore.authenticated && canComment"
            class="p-4 border-t border-gray-200 dark:border-slate-800 flex-shrink-0"
        >
            <div class="text-center text-gray-500 dark:text-slate-400 text-sm">
                Sign in to leave a comment
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, inject } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useCommentStore } from "@/stores/comments";
import EmojiPicker from "@/components/Form/EmojiPicker.vue";
import CommentItem from "./CommentItem.vue";
import Spinner from "../Spinner.vue";

const authStore = useAuthStore();
const appStore = inject("appStore");
const videoStore = inject("videoStore");
const commentStore = useCommentStore();
const newComment = ref("");
const isSubmitting = ref(false);
const selectedEmoji = ref("");
const error = ref(null);

// Computed properties to get store state
const currentVideo = computed(() => videoStore.currentVideo);
const videoId = computed(() => currentVideo.value?.id);
const comments = computed(() =>
    videoId.value ? commentStore.getComments(videoId.value) : [],
);
const isLoading = computed(() =>
    videoId.value ? commentStore.isLoading(videoId.value) : false,
);
const hasMore = computed(() =>
    videoId.value ? commentStore.hasMore(videoId.value) : false,
);

// Check if comments are enabled for this video
const canComment = computed(() => {
    return currentVideo.value?.permissions?.can_comment !== false;
});

const onEmojiSelect = (emoji) => {
    newComment.value += emoji.native;
};

// Modified handleAddComment
const handleAddComment = async () => {
    if (
        !newComment.value.trim() ||
        !videoId.value ||
        isSubmitting.value ||
        !canComment.value
    )
        return;

    try {
        isSubmitting.value = true;
        await commentStore.addComment(videoId.value, newComment.value);
        await videoStore.incrementCommentCount();
        newComment.value = "";
    } catch (err) {
        error.value = err;
    } finally {
        isSubmitting.value = false;
    }
};

// Watch currentVideo and fetch comments if they're enabled
watch(
    currentVideo,
    async (newVideo, oldVideo) => {
        if (!newVideo || newVideo.id === oldVideo?.id) return;

        console.log(
            "Video changed, checking if comments are enabled for videoId:",
            newVideo.id,
        );

        // Only fetch comments if they're enabled for this video
        if (newVideo.permissions?.can_comment !== false) {
            try {
                error.value = null;
                await commentStore.fetchComments(newVideo.id, true);
            } catch (err) {
                console.error("Error fetching comments:", err);
                error.value = err;
            }
        }
    },
    { immediate: true },
);

const loadMore = async () => {
    if (
        !videoId.value ||
        !hasMore.value ||
        isLoading.value ||
        !canComment.value
    )
        return;

    console.log("Loading more comments for videoId:", videoId.value);
    try {
        await commentStore.fetchComments(videoId.value, false);
    } catch (err) {
        console.error("Error loading more comments:", err);
        error.value = err;
    }
};
</script>
