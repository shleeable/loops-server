<template>
    <SettingsLayout>
        <div class="p-6">
            <h1 class="text-2xl font-semibold tracking-tight mb-6 dark:text-gray-300">
                Content preferences
            </h1>
            <hr class="border-gray-300 dark:border-gray-700" />

            <section v-if="!loaded" class="my-8">
                <Spinner />
            </section>

            <section v-else class="my-8">
                <div class="flex flex-col gap-3">
                    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="px-4 py-6 flex items-center justify-between">
                            <div class="flex flex-col max-w-[60%]">
                                <h3 class="font-medium mb-2 dark:text-gray-300">
                                    Hide AI content in feeds
                                </h3>
                                <p class="text-xs text-gray-500 font-light">
                                    Control your exposure to content partially or fully authored by
                                    AI.
                                </p>
                            </div>
                            <ToggleSwitch v-model="hideAI" />
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="px-4 py-6 flex items-center justify-between">
                            <div class="flex flex-col max-w-[60%]">
                                <h3 class="font-medium mb-2 dark:text-gray-300">
                                    Hide Sensitive Content in feeds
                                </h3>
                                <p class="text-xs text-gray-500 font-light">
                                    Allow sensitive content to be included in feeds, behind a
                                    content warning.
                                </p>
                            </div>
                            <ToggleSwitch v-model="hideSensitive" />
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import SettingsLayout from '~/layouts/SettingsLayout.vue'
import ToggleSwitch from '@/components/Form/ToggleSwitch.vue'

const apiClient = useApiClient()
const hideAI = ref(false)
const hideSensitive = ref(false)
const loaded = ref(false)

watch(
    () => hideAI.value,
    async (newVal, oldVal) => {
        if (!loaded.value) {
            return
        }
        if (oldVal === null) return
        try {
            await apiClient.post('/api/v1/account/settings/content', {
                hide_ai: newVal
            })
        } catch {}
    }
)

watch(
    () => hideSensitive.value,
    async (newVal, oldVal) => {
        if (!loaded.value) {
            return
        }
        if (oldVal === null) return
        try {
            await apiClient.post('/api/v1/account/settings/content', {
                hide_sensitive: newVal
            })
        } catch {}
    }
)

const fetchContentSettings = async () => {
    try {
        await apiClient.get('/api/v1/account/settings/content').then((res) => {
            hideAI.value = res.data.data.hide_ai
            hideSensitive.value = res.data.data.hide_sensitive
        })
    } catch {
    } finally {
        await nextTick()
        loaded.value = true
    }
}

onMounted(async () => {
    await fetchContentSettings()
    await nextTick()
})
</script>
