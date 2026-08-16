<template>
    <BlankLayout>
        <div class="w-full h-screen grid grid-cols-1 lg:grid-cols-2">
            <AuthBrandPanel
                :heading="$t('auth.brandHeadingLogin')"
                :subheading="$t('auth.brandSubheadingLogin')"
            />

            <div class="flex items-center justify-center bg-white px-6 py-16 dark:bg-gray-950">
                <div class="w-full max-w-sm">
                    <div class="mb-10 flex flex-col items-center lg:hidden">
                        <img src="/img/logo-light.svg" alt="Loops" class="h-12 w-auto" />
                        <span
                            class="mt-3 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            Loops
                        </span>
                    </div>

                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Welcome back
                    </h2>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Sign in to keep watching, posting and following.
                    </p>

                    <div class="mt-8 space-y-3">
                        <button
                            type="button"
                            class="w-full rounded-xl bg-[#F02C56] px-6 py-3 font-semibold text-white transition hover:bg-[#d81f47] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#F02C56] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950"
                            @click="openAuth('login')"
                        >
                            Sign in
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-900"
                            @click="openAuth('register')"
                        >
                            Create an account
                        </button>
                    </div>

                    <div class="mt-8 flex items-center gap-4">
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800" />
                        <span
                            class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-600"
                        >
                            or
                        </span>
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800" />
                    </div>

                    <router-link
                        to="/"
                        class="mt-8 flex items-center justify-center gap-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Go back home
                    </router-link>
                </div>
            </div>
        </div>
    </BlankLayout>
</template>

<script setup>
import { onMounted, watch, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
    ArrowLeftIcon,
    GlobeAltIcon,
    ShieldCheckIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline'
import BlankLayout from '@/layouts/BlankLayout.vue'

const authStore = inject('authStore')

const route = useRoute()
const router = useRouter()

const features = [
    {
        icon: GlobeAltIcon,
        title: 'Federated by design',
        description: 'Reach people on Mastodon, Pixelfed and the wider fediverse.'
    },
    {
        icon: SparklesIcon,
        title: 'A feed you control',
        description: 'Follow creators directly instead of chasing an algorithm.'
    },
    {
        icon: ShieldCheckIcon,
        title: 'Your account, your data',
        description: 'No ad tracking, no lock in, no surveillance.'
    }
]

const redirectTarget = () => {
    const next = route.query.next
    return typeof next === 'string' && next.startsWith('/') ? next : '/'
}

const openAuth = (mode) => {
    authStore.openAuthModal(mode)
}

onMounted(() => {
    if (authStore.authenticated) {
        router.replace(redirectTarget())
        return
    }

    openAuth('login')
})

watch(
    () => authStore.authenticated,
    (value) => {
        if (value) {
            router.replace(redirectTarget())
        }
    }
)
</script>
