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

                    <div v-if="canEmbed" class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="px-4 py-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex flex-col">
                                    <h3 class="font-medium mb-2 dark:text-gray-300">
                                        Video embeds
                                    </h3>
                                    <p class="text-xs text-gray-500 font-light">
                                        Manage whether your videos can be embedded on other sites.
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="dark:text-gray-300">
                                        <span class="text-2xl font-semibold">
                                            {{ totalEmbeds.toLocaleString() }}
                                        </span>
                                        <span class="text-sm text-gray-500 font-normal ml-1">
                                            / {{ totalVideos.toLocaleString() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 font-light mt-0.5">
                                        embeds active
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col sm:flex-row gap-2">
                                <AnimatedButton
                                    variant="primaryGradient"
                                    pill
                                    :disabled="embedActionLoading"
                                    @click="enableAllEmbeds"
                                    class="flex-grow"
                                >
                                    <div class="font-bold">Enable for all my public videos</div>
                                </AnimatedButton>

                                <AnimatedButton
                                    variant="outline"
                                    pill
                                    :disabled="embedActionLoading || totalEmbeds === 0"
                                    @click="disableAllEmbeds"
                                    class="flex-grow"
                                >
                                    <div class="font-bold">Disable all Embeds</div>
                                </AnimatedButton>

                                <AnimatedButton
                                    variant="light"
                                    pill
                                    :disabled="embedActionLoading || totalEmbeds === 0"
                                    @click="viewMyEmbeds"
                                    class="flex-grow"
                                >
                                    <div class="font-bold">My Embeds</div>
                                </AnimatedButton>
                            </div>
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
import { useAlertModal } from '@/composables/useAlertModal.js'
import { useI18n } from 'vue-i18n'

const { alertModal, confirmModal } = useAlertModal()
const { t } = useI18n()

const router = useRouter()

const apiClient = useApiClient()
const hideAI = ref(false)
const canEmbed = ref(false)
const totalVideos = ref(0)
const totalEmbeds = ref(0)
const embedActionLoading = ref(false)
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

const fetchContentSettings = async () => {
    try {
        await apiClient.get('/api/v1/account/settings/content').then((res) => {
            hideAI.value = res.data.data.hide_ai
            canEmbed.value = res.data.data.can_embed ?? 0
            totalVideos.value = res.data.data.total_videos ?? 0
            totalEmbeds.value = res.data.data.total_embeds ?? 0
        })
    } catch {
    } finally {
        await nextTick()
        loaded.value = true
    }
}

const viewMyEmbeds = () => {
    router.push('/studio/posts?only_embeds=1')
}

const enableAllEmbeds = async () => {
    if (embedActionLoading.value) return

    const result = await confirmModal(
        'Confirm action',
        'Are you sure you want to enable embeds for all your public videos?',
        t('common.confirm'),
        t('common.cancel')
    )

    if (!result) {
        return
    }

    embedActionLoading.value = true
    try {
        const res = await apiClient.post('/api/v1/account/settings/content/embeds', {
            action: 'enable_all'
        })
        totalEmbeds.value = res.data.data.total_embeds ?? totalEmbeds.value
    } catch (err) {
        await handleEmbedErrors(err)
        await fetchContentSettings()
        await nextTick()
    } finally {
        embedActionLoading.value = false
    }
}

const disableAllEmbeds = async () => {
    if (embedActionLoading.value) return

    const result = await confirmModal(
        'Confirm action',
        "Are you sure you want to disable embeds for all your public videos?<br/><br/><strong class='text-red-500 font-bold'>This will break existing embeds, proceed with caution.</strong>",
        t('common.confirm'),
        t('common.cancel')
    )

    if (!result) {
        return
    }

    embedActionLoading.value = true
    try {
        const res = await apiClient.post('/api/v1/account/settings/content/embeds', {
            action: 'disable_all'
        })
        totalEmbeds.value = res.data.data.total_embeds ?? 0
    } catch (err) {
        await handleEmbedErrors(err)
        await fetchContentSettings()
        await nextTick()
    } finally {
        embedActionLoading.value = false
    }
}

const handleEmbedErrors = async (err) => {
    const actions = [
        {
            text: 'Contact Admins',
            type: 'danger',
            callback: () => router.push('/contact')
        },
        {
            text: 'OK',
            type: 'cancel'
        }
    ]

    await alertModal('Oops! Something went wrong.', err.response.data?.message, actions)
}

onMounted(async () => {
    await fetchContentSettings()
    await nextTick()
})
</script>
