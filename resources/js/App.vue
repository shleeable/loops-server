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
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-4 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
        >
            <div
                v-if="updateAvailable"
                class="fixed inset-x-0 bottom-4 z-50 mx-auto flex w-fit items-center gap-3 rounded-full border border-slate-200 bg-white/95 px-4 py-2.5 shadow-lg backdrop-blur dark:border-slate-800 dark:bg-slate-900/95"
            >
                <span class="text-sm text-slate-700 dark:text-slate-200">
                    A new version of Loops is available.
                </span>
                <button
                    @click="reload"
                    class="rounded-full bg-slate-900 px-3 py-1 text-sm font-medium text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                >
                    Refresh
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { onMounted, watch, inject } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import AuthModal from '@/components/AuthModal.vue'
import PageSkeleton from '@/components/Layout/PageSkeleton.vue'
import { useViteVersion } from '@/composables/useViteVersion'

const { updateAvailable, reload } = useViteVersion()
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
