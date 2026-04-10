<template>
    <div
        class="rounded-xl flex-grow-1 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
    >
        <div
            class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700"
        >
            <h2 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
            <slot name="header-action" />
        </div>
        <slot name="tabs" />
        <div class="p-5">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String
})
</script>
