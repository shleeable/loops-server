<template>
    <div
        :class="
            view === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4' : 'space-y-3'
        "
    >
        <div
            v-for="n in count"
            :key="n"
            class="bg-white dark:bg-gray-900/60 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 flex flex-col gap-4 animate-pulse"
            :class="view === 'list' ? 'flex-row items-center' : ''"
        >
            <template v-if="view === 'grid'">
                <div class="flex items-center justify-between">
                    <div class="flex">
                        <div
                            v-for="i in 4"
                            :key="i"
                            class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 border-2 border-white dark:border-gray-900 -ml-2 first:ml-0"
                        />
                    </div>
                    <div class="w-12 h-6 rounded-lg bg-gray-100 dark:bg-gray-800" />
                </div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-3/4 rounded-lg bg-gray-100 dark:bg-gray-800" />
                    <div class="h-3 w-full rounded-lg bg-gray-100 dark:bg-gray-800" />
                </div>
                <div class="flex gap-1.5">
                    <div
                        v-for="i in 3"
                        :key="i"
                        class="h-5 w-14 rounded-full bg-gray-100 dark:bg-gray-800"
                    />
                </div>
                <div
                    class="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-gray-800"
                >
                    <div class="h-3 w-28 rounded-lg bg-gray-100 dark:bg-gray-800" />
                    <div class="h-3 w-16 rounded-lg bg-gray-100 dark:bg-gray-800" />
                </div>
            </template>

            <template v-else>
                <div class="flex items-center gap-5 w-full">
                    <div class="flex-shrink-0 relative" style="width: 58px; height: 36px">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="absolute top-0 w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800"
                            :style="{ left: (i - 1) * 16 + 'px' }"
                        />
                    </div>
                    <div class="flex-1 space-y-2 min-w-0">
                        <div class="flex items-center justify-between gap-4">
                            <div class="space-y-1.5 flex-1">
                                <div class="h-4 w-1/2 rounded-lg bg-gray-100 dark:bg-gray-800" />
                                <div class="h-3 w-3/4 rounded-lg bg-gray-100 dark:bg-gray-800" />
                            </div>
                            <div
                                class="flex-shrink-0 h-7 w-16 rounded-lg bg-gray-100 dark:bg-gray-800"
                            />
                        </div>
                        <div class="flex items-center gap-3 mt-2">
                            <div class="h-3 w-24 rounded-lg bg-gray-100 dark:bg-gray-800" />
                            <div class="h-5 w-12 rounded-full bg-gray-100 dark:bg-gray-800" />
                            <div class="h-5 w-12 rounded-full bg-gray-100 dark:bg-gray-800" />
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
defineProps({
    view: { type: String, default: 'grid' },
    count: { type: Number, default: 9 }
})
</script>
