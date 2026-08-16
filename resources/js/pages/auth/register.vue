<template>
    <BlankLayout>
        <div class="w-full h-screen grid grid-cols-1 lg:grid-cols-2">
            <AuthBrandPanel
                :heading="$t('auth.brandHeadingRegister')"
                :subheading="$t('auth.brandSubheadingRegister')"
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

                    <div v-if="registrationClosed">
                        <div
                            class="flex size-16 items-center justify-center rounded-2xl bg-red-50 dark:bg-red-900/30"
                        >
                            <LockClosedIcon class="size-8 text-red-500" />
                        </div>

                        <h2
                            class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            {{ $t('auth.registrationsAreCurrentlyClosed') }}
                        </h2>

                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            {{ $t('auth.thisServerIsntAccepting') }}
                        </p>

                        <div class="mt-8 flex flex-col gap-3">
                            <AnimatedButton
                                v-if="showLoopsVideo"
                                class="w-full"
                                variant="primaryGradient"
                                size="lg"
                                pill
                                @click="redirectToLoopsVideo"
                            >
                                <div class="flex gap-2 items-center justify-center">
                                    <UserPlusIcon class="w-5 h-5 text-white" />
                                    {{ $t('auth.joinLoopsVideo') }}
                                </div>
                            </AnimatedButton>

                            <AnimatedButton
                                class="w-full"
                                variant="outline"
                                size="lg"
                                pill
                                @click="router.push('/')"
                            >
                                <div class="flex gap-2 items-center justify-center">
                                    <ChevronLeftIcon class="w-5 h-5" />
                                    {{ $t('common.goBackHome') }}
                                </div>
                            </AnimatedButton>
                        </div>
                    </div>

                    <div v-else-if="registrationCurated">
                        <div
                            class="flex size-16 items-center justify-center rounded-2xl bg-green-50 dark:bg-green-900/30"
                        >
                            <ShieldCheckIcon class="size-8 text-green-500" />
                        </div>

                        <h2
                            class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            {{ $t('auth.curatedOnboarding') }}
                        </h2>

                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            {{ $t('auth.curatedOnboardingDesc') }}
                        </p>

                        <div class="mt-8 flex flex-col gap-3">
                            <AnimatedButton
                                class="w-full"
                                variant="primaryGradient"
                                size="lg"
                                pill
                                @click="router.push('/auth/curated')"
                            >
                                <div class="flex gap-2 items-center justify-center">
                                    <UserPlusIcon class="w-5 h-5 text-white" />
                                    {{ $t('auth.applyToJoin') }}
                                </div>
                            </AnimatedButton>

                            <AnimatedButton
                                class="w-full"
                                variant="outline"
                                size="lg"
                                pill
                                @click="router.push('/')"
                            >
                                <div class="flex gap-2 items-center justify-center">
                                    <ChevronLeftIcon class="w-5 h-5" />
                                    {{ $t('common.goBackHome') }}
                                </div>
                            </AnimatedButton>
                        </div>
                    </div>

                    <div v-else>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ $t('auth.createYourAccount') }}
                        </h2>

                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            {{ $t('auth.createYourAccountDesc') }}
                        </p>

                        <div class="mt-8 flex flex-col gap-3">
                            <AnimatedButton
                                class="w-full"
                                variant="primaryGradient"
                                size="lg"
                                pill
                                @click="openAuth('register')"
                            >
                                <div class="flex gap-2 items-center justify-center">
                                    <UserPlusIcon class="w-5 h-5 text-white" />
                                    {{ $t('auth.createAnAccount') }}
                                </div>
                            </AnimatedButton>

                            <AnimatedButton
                                class="w-full"
                                variant="outline"
                                size="lg"
                                pill
                                @click="openAuth('login')"
                            >
                                {{ $t('auth.alreadyHaveAnAccount') }}
                            </AnimatedButton>
                        </div>

                        <router-link
                            to="/"
                            class="mt-8 flex items-center justify-center gap-2 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <ChevronLeftIcon class="h-4 w-4" />
                            {{ $t('common.goBackHome') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </BlankLayout>
</template>

<script setup>
import { onMounted, watch, inject, computed } from 'vue'
import { useRouter } from 'vue-router'
import AuthBrandPanel from '@/components/Auth/AuthBrandPanel.vue'
import AnimatedButton from '@/components/AnimatedButton.vue'
import {
    ChevronLeftIcon,
    LockClosedIcon,
    ShieldCheckIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline'
import BlankLayout from '@/layouts/BlankLayout.vue'

const authStore = inject('authStore')
const appConfig = inject('appConfig')

const router = useRouter()

const registrationClosed = computed(() => appConfig?.registration_mode === 'closed')
const registrationCurated = computed(() => appConfig?.registration_mode === 'curated')
const registrationOpen = computed(() => !registrationClosed.value && !registrationCurated.value)
const showLoopsVideo = computed(() => appConfig?.app?.url !== 'https://loops.video')

const redirectToLoopsVideo = () => {
    window.location.href = 'https://loops.video/register'
}

const openAuth = (mode) => {
    authStore.openAuthModal(mode)
}

onMounted(() => {
    if (authStore.authenticated) {
        router.replace('/')
        return
    }

    if (registrationOpen.value) {
        openAuth('register')
    }
})

watch(
    () => authStore.authenticated,
    (value) => {
        if (value) {
            router.replace('/')
        }
    }
)
</script>
