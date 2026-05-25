<template>
    <div class="min-h-screen bg-white dark:bg-slate-950">
        <router-view v-slot="{ Component }">
            <Suspense>
                <template #default>
                    <component :is="Component" />
                </template>
                <template #fallback>
                    <PageSkeleton />
                </template>
            </Suspense>
        </router-view>
        <AuthModal v-if="isOpen" :mode="authMode" />
    </div>
</template>

<script setup>
import { onMounted, watch, inject } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import AuthModal from '@/components/AuthModal.vue'
import PageSkeleton from '@/components/Layout/PageSkeleton.vue'

const authStore = useAuthStore()

const { isOpen, authMode } = storeToRefs(authStore)

onMounted(async () => {
    if (
        localStorage.theme === 'dark' ||
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
    try {
        await authStore.hasSessionExpired()
    } catch (error) {
        console.error('Error in App setup:', error)
    }
})
</script>
