<template>
    <StudioLayout>
        <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-950">
            <StudioPosts api-base="/api/v1/studio/posts" />
        </div>
    </StudioLayout>
</template>

<script setup>
import StudioPosts from '@/components/Studio/StudioPosts.vue'
</script>
