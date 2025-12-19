<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm px-4"
    >
        <div
            class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl overflow-hidden"
        >
            <div class="h-24 bg-gradient-to-br from-rose-500 via-red-600 to-red-700"></div>

            <div class="px-6 pb-6 -mt-16 text-center">
                <div class="relative inline-block">
                    <img
                        :src="avatar"
                        alt="Profile avatar"
                        class="w-24 h-24 rounded-full border-2 border-white dark:border-gray-900 shadow-lg"
                    />
                </div>

                <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                    Hey {{ username }}!
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">You're already logged in.</p>

                <div class="mt-6 space-y-2">
                    <AnimatedButton @click="authStore.logout()" class="w-full" size="lg">
                        <div class="flex justify-center items-center gap-2">
                            <i class="bx bx-log-out text-lg"></i>
                            Log out
                        </div>
                    </AnimatedButton>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '~/stores/auth'
import AnimatedButton from '~/components/AnimatedButton.vue'

const authStore = useAuthStore()

const username = computed(() => authStore.user?.username)
const avatar = computed(() => authStore.user?.avatar)
</script>
