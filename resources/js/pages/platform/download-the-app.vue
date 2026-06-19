<template>
    <MainLayout>
        <div class="isolate bg-white dark:bg-slate-950 px-6 py-24 sm:py-32 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <div class="text-center mb-12">
                    <h1
                        class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl"
                    >
                        {{ $t('download.title') }}
                    </h1>
                    <p
                        class="mt-6 text-lg leading-8 text-gray-600 dark:text-slate-400 max-w-2xl mx-auto"
                    >
                        {{ $t('download.subtitle') }}
                    </p>
                </div>

                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6 mb-20"
                >
                    <a
                        :href="appStoreUrl"
                        target="_blank"
                        rel="noopener"
                        aria-label="Download Loops on the App Store"
                        class="inline-block rounded-xl transition-transform hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                    >
                        <img
                            src="/img/app-store-light.svg"
                            alt="Download on the App Store"
                            class="h-[75px] w-auto dark:hidden"
                        />
                        <img
                            src="/img/app-store-dark.svg"
                            alt="Download on the App Store"
                            class="h-[75px] w-auto hidden dark:block"
                        />
                    </a>

                    <a
                        :href="playStoreUrl"
                        target="_blank"
                        rel="noopener"
                        aria-label="Get Loops on Google Play"
                        class="inline-block rounded-xl transition-transform hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                    >
                        <img
                            src="/img/google-play-dark.svg"
                            alt="Get it on Google Play"
                            class="h-[75px] w-auto"
                        />
                    </a>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div
                        v-for="feature in features"
                        :key="feature.titleKey"
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-lg dark:hover:shadow-slate-900/20 transition-all duration-300"
                    >
                        <div class="flex items-start space-x-4">
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    {{ $t(feature.titleKey) }}
                                </h3>
                                <p class="text-gray-600 dark:text-slate-400">
                                    {{ $t(feature.bodyKey) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <div
                        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-slate-800 dark:to-slate-700 rounded-2xl p-8"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $t('download.webTitle') }}
                        </h3>
                        <p class="text-gray-600 dark:text-slate-400 mb-4">
                            {{ $t('download.webBody') }}
                        </p>
                        <a
                            :href="webAppUrl"
                            class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 transition-colors"
                        >
                            {{ $t('download.webCta') }}
                            <ArrowTopRightOnSquareIcon class="ml-2 h-4 w-4" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue'
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline'

const appStoreUrl = 'https://apps.apple.com/us/app/loops-by-pixelfed/id6499375182'
const playStoreUrl = 'https://play.google.com/store/apps/details?id=com.pixelfed.loops'
const webAppUrl = import.meta.env.VITE_APP_URL

const features = [
    {
        titleKey: 'download.captureTitle',
        bodyKey: 'download.captureBody'
    },
    {
        titleKey: 'download.fediverseTitle',
        bodyKey: 'download.fediverseBody'
    },
    {
        titleKey: 'download.privacyTitle',
        bodyKey: 'download.privacyBody'
    },
    {
        titleKey: 'download.openSourceTitle',
        bodyKey: 'download.openSourceBody'
    }
]
</script>
