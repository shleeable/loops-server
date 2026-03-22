<template>
    <div class="min-h-screen bg-[#FAFAFA] dark:bg-[#0A0A0A] font-body">
        <div class="max-w-5xl mx-auto px-6 md:px-12 pt-14">
            <div
                class="h-4 w-24 bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse mb-10"
            ></div>
            <div class="h-8 w-2/3 bg-gray-200 dark:bg-gray-800 rounded-xl animate-pulse mb-3"></div>
            <div
                class="h-4 w-full bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse mb-2"
            ></div>
            <div
                class="h-4 w-1/2 bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse mb-8"
            ></div>
            <div class="flex gap-2 mb-12">
                <div class="h-6 w-16 bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse"></div>
                <div class="h-6 w-14 bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse"></div>
            </div>
            <div class="space-y-2">
                <div
                    v-for="i in 8"
                    :key="i"
                    class="flex items-center gap-4 p-4 bg-white dark:bg-gray-900/60 border border-gray-100 dark:border-gray-800 rounded-2xl animate-pulse"
                    :style="`animation-delay: ${i * 50}ms`"
                >
                    <div
                        class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 flex-shrink-0"
                    ></div>
                    <div class="flex-1">
                        <div class="h-3.5 w-32 bg-gray-200 dark:bg-gray-800 rounded mb-2"></div>
                        <div class="h-3 w-20 bg-gray-200 dark:bg-gray-800 rounded"></div>
                    </div>
                    <div
                        class="h-3 w-24 bg-gray-200 dark:bg-gray-800 rounded hidden sm:block"
                    ></div>
                    <div class="h-8 w-16 bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                </div>
            </div>
        </div>
    </div>
</template>
