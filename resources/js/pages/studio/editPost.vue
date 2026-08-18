<template>
    <StudioLayout>
        <div class="max-w-7xl mx-auto px-4 py-8 pb-32">
            <div class="mb-6 flex items-center gap-3">
                <router-link
                    to="/studio/posts"
                    class="inline-flex items-center gap-1 text-lg font-bold border border-gray-200 dark:border-gray-800 px-3 py-2 rounded rounded-xl text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    {{ $t('studio.myPosts') }}
                </router-link>
            </div>

            <div v-if="loading" class="text-center py-24">
                <div
                    class="animate-spin mx-auto w-8 h-8 border-4 border-gray-300 border-t-red-500 rounded-full mb-4"
                ></div>
                <p class="text-gray-500 dark:text-gray-400">Loading post...</p>
            </div>

            <div
                v-else-if="loadError"
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center"
            >
                <ExclamationTriangleIcon class="w-10 h-10 mx-auto text-amber-500 mb-4" />
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    This post could not be loaded
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    It may have been deleted, or it does not belong to your account.
                </p>
                <router-link
                    to="/studio/posts"
                    class="inline-flex px-4 py-2 rounded-md text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:opacity-90 transition"
                >
                    Back to my posts
                </router-link>
            </div>

            <div v-else-if="video" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <aside class="lg:col-span-4">
                    <div class="lg:sticky lg:top-6 space-y-4">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                        >
                            <div class="relative bg-black aspect-[9/16]">
                                <video
                                    v-if="video.media?.src_url"
                                    :src="video.media.src_url"
                                    :poster="thumbnailUrl"
                                    class="absolute inset-0 w-full h-full object-contain"
                                    controls
                                    playsinline
                                    preload="metadata"
                                ></video>
                                <img
                                    v-else
                                    :src="thumbnailUrl"
                                    :alt="`${video.caption || 'Video'} thumbnail`"
                                    class="absolute inset-0 w-full h-full object-cover"
                                />
                            </div>

                            <div class="p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span :class="getStatusBadgeClass(video.status)">
                                        {{ capitalize(video.status) }}
                                    </span>
                                    <router-link
                                        v-if="video.status === 'published'"
                                        :to="`/v/${video.hid}`"
                                        class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        {{ $t('studio.view') }}
                                    </router-link>
                                </div>

                                <dl class="grid grid-cols-3 gap-2 text-center">
                                    <div
                                        class="rounded-md bg-gray-50 dark:bg-gray-900 py-2 px-1 border border-gray-100 dark:border-gray-700"
                                    >
                                        <dt
                                            class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $t('studio.likes') }}
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatCount(video.likes) }}
                                        </dd>
                                    </div>
                                    <div
                                        class="rounded-md bg-gray-50 dark:bg-gray-900 py-2 px-1 border border-gray-100 dark:border-gray-700"
                                    >
                                        <dt
                                            class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $t('studio.comments') }}
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatCount(video.comments) }}
                                        </dd>
                                    </div>
                                    <div
                                        class="rounded-md bg-gray-50 dark:bg-gray-900 py-2 px-1 border border-gray-100 dark:border-gray-700"
                                    >
                                        <dt
                                            class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            Views
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatCount(video.views) }}
                                        </dd>
                                    </div>
                                </dl>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Posted {{ formatDate(video.created_at) }}
                                </p>
                            </div>
                        </div>

                        <ThumbnailUploader
                            :video-id="video.id"
                            :current-thumbnail="thumbnailUrl"
                            @updated="handleThumbnailUpdated"
                        />
                    </div>
                </aside>

                <section class="lg:col-span-8 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Details
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label
                                    for="caption"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    {{ $t('post.caption') }}
                                </label>
                                <textarea
                                    id="caption"
                                    v-model="form.caption"
                                    rows="4"
                                    maxlength="200"
                                    :placeholder="$t('post.writeYourCaptionDotDotDot')"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#F02C56] focus:border-[#F02C56] resize-none"
                                />
                                <div
                                    class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1"
                                >
                                    {{ form.caption.length }}/200
                                </div>
                            </div>

                            <div>
                                <label
                                    for="altText"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ $t('studio.altText') }}
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ $t('studio.altTextHelp') }}
                                </p>
                                <textarea
                                    id="altText"
                                    v-model="form.altText"
                                    rows="3"
                                    maxlength="2000"
                                    :placeholder="$t('studio.describeYourVideoDotDotDotAltText')"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#F02C56] focus:border-[#F02C56] resize-none"
                                />
                                <div
                                    class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1"
                                >
                                    {{ form.altText.length }}/2000
                                </div>
                            </div>

                            <div>
                                <label
                                    for="lang"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    {{ $t('studio.language') }}
                                </label>
                                <div class="relative">
                                    <select
                                        id="lang"
                                        v-model="form.lang"
                                        class="block w-full px-4 py-2 pr-8 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 appearance-none focus:outline-none focus:ring-2 focus:ring-[#F02C56] focus:border-[#F02C56]"
                                    >
                                        <option value="" disabled>
                                            {{ $t('studio.selectLanguage') }}
                                        </option>
                                        <option
                                            v-for="lang in languages"
                                            :key="lang.code"
                                            :value="lang.code"
                                        >
                                            {{ lang.name }}
                                        </option>
                                    </select>
                                    <ChevronDownIcon
                                        class="absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                    />
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $t('studio.selectLanguageHelp') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Interactions
                            </h2>
                        </div>
                        <div class="p-6 divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="row in interactionRows"
                                :key="row.key"
                                class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0"
                            >
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ row.label }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ row.help }}
                                    </p>
                                </div>
                                <ToggleSwitch v-model="form[row.key]" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Disclosures
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                These labels can be added but not removed once saved.
                            </p>
                        </div>
                        <div class="p-6 divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="row in disclosureRows"
                                :key="row.key"
                                class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0"
                            >
                                <div class="flex-1">
                                    <label
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ row.label }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ row.help }}
                                    </p>
                                </div>
                                <ToggleSwitch
                                    v-model="form[row.key]"
                                    :disabled="original[row.key]"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-900/60"
                    >
                        <div class="px-6 py-4 border-b border-red-200 dark:border-red-900/60">
                            <h2 class="text-base font-semibold text-red-700 dark:text-red-400">
                                Danger zone
                            </h2>
                        </div>
                        <div
                            class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                        >
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $t('post.deleteVideo') }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $t('post.thisActionCannotBeUndone') }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="confirmDelete"
                                :disabled="isDeleting"
                                class="shrink-0 bg-red-600 hover:bg-red-700 disabled:bg-red-300 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors cursor-pointer"
                            >
                                {{ $t('post.deleteVideo') }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <div
                v-if="video && !loading"
                class="fixed bottom-0 inset-x-0 z-40 border-t border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-900/95 backdrop-blur"
            >
                <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                        <span v-if="saveError" class="text-red-600 dark:text-red-400">{{
                            saveError
                        }}</span>
                        <span v-else-if="isDirty">You have unsaved changes</span>
                        <span v-else-if="savedRecently">Changes saved</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="resetForm"
                            :disabled="!isDirty || isSaving"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"
                        >
                            Discard
                        </button>
                        <button
                            type="button"
                            @click="saveChanges"
                            :disabled="!isDirty || isSaving"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#F02C56] hover:bg-red-600 disabled:bg-red-300 disabled:cursor-not-allowed rounded-md transition-colors flex items-center cursor-pointer"
                        >
                            <Spinner v-if="isSaving" size="sm" class="mr-2" />
                            {{ isSaving ? $t('common.savingDotDotDot') : $t('post.saveChanges') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </StudioLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, inject } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { ArrowLeftIcon, ChevronDownIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useUtils } from '@/composables/useUtils'
import { useAlertModal } from '@/composables/useAlertModal.js'
import ThumbnailUploader from '@/components/Studio/ThumbnailUploader.vue'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const videoStore = inject('videoStore')
const appStore = inject('appStore')
const { languages } = storeToRefs(appStore)
const { formatDate } = useUtils()
const { confirmModal } = useAlertModal()

const loading = ref(true)
const loadError = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const saveError = ref('')
const savedRecently = ref(false)
const thumbnailOverride = ref(null)

const video = computed(() => videoStore.video)
const thumbnailUrl = computed(
    () =>
        thumbnailOverride.value ||
        video.value?.media?.thumbnail ||
        '/storage/videos/video-placeholder.jpg'
)

const emptyForm = () => ({
    caption: '',
    altText: '',
    lang: '',
    pinToProfile: false,
    commentsEnabled: true,
    downloadsEnabled: true,
    embedsEnabled: false,
    allowDuets: true,
    allowStitches: true,
    isNsfw: false,
    containsAds: false,
    containsAiContent: false
})

const form = reactive(emptyForm())
const original = reactive(emptyForm())

const formFromVideo = (v) => ({
    caption: v.caption || '',
    altText: v.media?.alt_text || '',
    lang: v.lang || '',
    pinToProfile: !!v.pinned,
    commentsEnabled: v.permissions?.can_comment !== false,
    downloadsEnabled: !!v.permissions?.can_download,
    embedsEnabled: !!v.permissions?.can_embed,
    allowDuets: v.permissions?.can_duet !== false,
    allowStitches: v.permissions?.can_stitch !== false,
    isNsfw: !!v.is_sensitive,
    containsAds: !!v.meta?.contains_ad,
    containsAiContent: !!v.meta?.contains_ai
})

const hydrate = (v) => {
    Object.assign(original, formFromVideo(v))
    Object.assign(form, formFromVideo(v))
}

const isDirty = computed(() => Object.keys(original).some((k) => form[k] !== original[k]))

const interactionRows = computed(() => [
    {
        key: 'pinToProfile',
        label: t('post.pinToProfile'),
        help: t('post.showThisVideoAtTheTopOfYourProfile')
    },
    {
        key: 'commentsEnabled',
        label: t('post.commentsEnabled'),
        help: t('post.allowPeopleToCommentOnThisVideo')
    },
    {
        key: 'downloadsEnabled',
        label: t('post.downloadsEnabled'),
        help: t('post.allowPeopleToDownloadThisVideo')
    },
    {
        key: 'embedsEnabled',
        label: 'Embeds',
        help: 'Allow anyone to embed this video on websites.'
    },
    { key: 'allowDuets', label: t('studio.duet'), help: t('studio.duetMessage') },
    { key: 'allowStitches', label: t('studio.stitch'), help: t('studio.stitchMessage') }
])

const disclosureRows = computed(() => [
    { key: 'isNsfw', label: t('studio.containsNSFW'), help: t('studio.containsNSFWMessage') },
    {
        key: 'containsAds',
        label: t('studio.disclosePostContent'),
        help: t('studio.disclosePostContentHelp')
    },
    {
        key: 'containsAiContent',
        label: t('studio.containsAlteredContent'),
        help: t('studio.containsAlteredContentHelp')
    }
])

const capitalize = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '')
const formatCount = (n) => (typeof n === 'number' ? n.toLocaleString() : '-')

const getStatusBadgeClass = (status) => {
    const base = 'inline-flex px-2 py-1 text-xs font-semibold rounded-full'
    switch (status) {
        case 'published':
            return `${base} bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200`
        case 'processing':
            return `${base} bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200`
        case 'scheduled':
            return `${base} bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200`
        default:
            return `${base} bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200`
    }
}

const loadVideo = async () => {
    loading.value = true
    loadError.value = false
    try {
        await videoStore.getVideoById(route.params.id)
        if (!videoStore.video) {
            loadError.value = true
            return
        }
        hydrate(videoStore.video)
    } catch (error) {
        console.error('Error loading video:', error)
        loadError.value = true
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    Object.assign(form, original)
    saveError.value = ''
}

const saveChanges = async () => {
    if (!video.value || !isDirty.value) return
    isSaving.value = true
    saveError.value = ''
    try {
        await videoStore.updateVideoStore({
            id: video.value.id,
            caption: form.caption,
            alt_text: form.altText,
            is_pinned: form.pinToProfile,
            lang: form.lang,
            can_comment: form.commentsEnabled,
            can_download: form.downloadsEnabled,
            can_embed: form.embedsEnabled,
            can_duet: form.allowDuets,
            can_stitch: form.allowStitches,
            is_sensitive: form.isNsfw,
            contains_ad: form.containsAds,
            contains_ai: form.containsAiContent
        })
        Object.assign(original, form)
        savedRecently.value = true
        setTimeout(() => (savedRecently.value = false), 3000)
    } catch (error) {
        console.error('Error saving video:', error)
        saveError.value =
            error?.response?.data?.message || 'Could not save changes. Please try again.'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = async () => {
    const confirmed = await confirmModal(
        'Confirm Delete',
        `Are you sure you want to delete this video? All likes, comments and other interactions will be lost forever.<br /><br /><strong class="text-red-500">This action cannot be undone.</strong>`,
        'Delete',
        'Cancel'
    )
    if (!confirmed) return
    isDeleting.value = true
    try {
        await videoStore.deleteVideoById(video.value.id)
        Object.assign(original, form)
        router.replace('/studio/posts')
    } catch (error) {
        console.error('Error deleting video:', error)
        saveError.value = 'Could not delete this video. Please try again.'
    } finally {
        isDeleting.value = false
    }
}

const handleThumbnailUpdated = (payload) => {
    if (payload?.thumbnail) {
        thumbnailOverride.value = `${payload.thumbnail}${payload.thumbnail.includes('?') ? '&' : '?'}v=${Date.now()}`
    }
    if (payload?.video && videoStore.video) {
        videoStore.video = payload.video
    }
}

onBeforeRouteLeave(() => {
    if (!isDirty.value) return true
    return window.confirm('You have unsaved changes. Leave without saving?')
})

const beforeUnload = (e) => {
    if (isDirty.value) {
        e.preventDefault()
        e.returnValue = ''
    }
}

onMounted(async () => {
    window.addEventListener('beforeunload', beforeUnload)
    await appStore.ensureLanguages()
    await loadVideo()
})

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', beforeUnload)
})
</script>
