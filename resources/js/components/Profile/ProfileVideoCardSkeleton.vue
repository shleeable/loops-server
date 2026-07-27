<template>
    <div class="relative rounded-md overflow-hidden bg-gray-200 dark:bg-gray-800 animate-pulse">
        <div class="aspect-[3/4] w-full"></div>

        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/20 via-black/5 to-transparent"
            ></div>
            <div
                class="absolute bottom-3 left-3 h-4 w-12 rounded bg-gray-300/80 dark:bg-gray-700/80"
            ></div>
            <div
                class="absolute bottom-3 right-3 h-4 w-12 rounded bg-gray-300/80 dark:bg-gray-700/80"
            ></div>
        </div>
    </div>
</template>
