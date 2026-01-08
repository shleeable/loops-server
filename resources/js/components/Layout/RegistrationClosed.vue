<template>
    <div
        class="w-screen h-screen flex flex-col items-center justify-center bg-white/80 dark:bg-gray-900/80 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl p-8 text-center backdrop-blur"
    >
        <div
            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/30"
        >
            <i class="bx bx-lock-alt text-2xl text-red-500"></i>
        </div>

        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $t('auth.registrationsAreCurrentlyClosed') }}
        </h1>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ $t('auth.thisServerIsntAccepting') }}
        </p>

        <router-link
            to="/"
            class="inline-flex items-center justify-center mt-6 px-5 py-2.5 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
        >
            <i class="bx bx-arrow-back mr-2 text-base"></i>
            {{ $t('common.goBackHome') }}
        </router-link>
    </div>
</template>
