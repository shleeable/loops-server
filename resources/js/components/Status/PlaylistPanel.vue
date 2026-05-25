<template>
    <div ref="bodyEl" @scroll="onScroll" class="h-full overflow-y-auto overscroll-contain">
        <div v-if="isLoading && !videos.length" class="flex justify-center py-16">
            <Spinner />
        </div>

        <div
            v-else-if="error"
            class="flex flex-col items-center justify-center py-16 px-6 text-center"
        >
            <p class="text-gray-600 dark:text-gray-400 mb-2">
                An error occurred while attempting to load this playlist.
            </p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Please try again later.</p>
            <button
                @click="load(true)"
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors cursor-pointer"
            >
                {{ $t('common.tryAgain') }}
            </button>
        </div>

        <div
            v-else-if="!videos.length"
            class="flex flex-col items-center justify-center py-16 px-6 text-center"
        >
            <p class="text-gray-500 dark:text-gray-400">
                {{ $t('profile.playlistEmpty') }}
            </p>
        </div>

        <ul v-else class="divide-y divide-gray-100 dark:divide-slate-800 px-4">
            <li v-for="(video, idx) in videos" :key="video.id" :data-current="isCurrent(video)">
                <router-link
                    :to="`/v/${video.hid}?playlist_id=${playlistId}`"
                    class="flex items-stretch gap-3 py-3 px-2 my-1 rounded-lg transition-colors"
                    :class="
                        isCurrent(video)
                            ? 'bg-[#F02C56]/5 dark:bg-[#F02C56]/10'
                            : 'hover:bg-gray-100 dark:hover:bg-slate-800/60'
                    "
                >
                    <span
                        class="w-8 flex items-center justify-center text-sm font-medium tabular-nums shrink-0"
                        :class="
                            isCurrent(video) ? 'text-[#F02C56]' : 'text-gray-400 dark:text-gray-500'
                        "
                    >
                        <span
                            v-if="isCurrent(video)"
                            class="bx bx-play text-xl leading-none"
                        ></span>
                        <template v-else>{{ String(idx + 1).padStart(2, '0') }}</template>
                    </span>

                    <div
                        class="w-14 h-20 rounded-md overflow-hidden bg-gray-100 dark:bg-slate-800 shrink-0 relative"
                    >
                        <img
                            v-if="thumbFor(video)"
                            :src="thumbFor(video)"
                            :alt="video.caption || ''"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                        <div
                            v-if="isCurrent(video)"
                            class="absolute inset-0 bg-black/40 flex items-center justify-center"
                        >
                            <PlayIcon class="h-6 w-6 text-white" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0 flex flex-col justify-around py-0.5">
                        <p
                            class="text-sm font-medium line-clamp-2"
                            :class="
                                isCurrent(video)
                                    ? 'text-[#F02C56]'
                                    : 'text-gray-900 dark:text-gray-100'
                            "
                        >
                            {{ video.caption || 'Untitled video' }}
                        </p>
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
                        >
                            <span v-if="video.views"
                                >{{ formatCount(video.views || 0) }} Views</span
                            >
                            <span v-if="video.views">·</span>
                            <span
                                >{{ formatCount(video.likes || 0) }} {{ $t('profile.likes') }}</span
                            >
                            <span>·</span>
                            <span
                                >{{ formatCount(video.comments || 0) }}
                                {{ $t('post.comments') }}</span
                            >
                        </div>
                    </div>
                </router-link>
            </li>
        </ul>

        <div v-if="isLoadingMore" class="flex justify-center py-4">
            <Spinner />
        </div>

        <div
            v-else-if="videos.length && !nextCursor"
            class="text-center py-4 text-xs text-gray-400 dark:text-gray-500"
        >
            end of playlist
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { PlayIcon } from '@heroicons/vue/24/solid'
import axios from '@/plugins/axios'
import Spinner from '~/components/Spinner.vue'
import { useUtils } from '@/composables/useUtils'

const props = defineProps({
    playlistId: { type: [String, Number], required: true },
    currentVideoHid: { type: String, default: null }
})

const { formatCount } = useUtils()

const emit = defineEmits(['error'])

const MAX_PREFETCH_PAGES = 5

const videos = ref([])
const nextCursor = ref(null)
const isLoading = ref(false)
const isLoadingMore = ref(false)
const error = ref(null)
const bodyEl = ref(null)

const thumbFor = (v) => v.media?.thumbnail || v.cover_image || null
const isCurrent = (v) => !!props.currentVideoHid && v.hid === props.currentVideoHid
const hasCurrent = () => videos.value.some(isCurrent)

const fetchPage = async (cursor = null) => {
    const axiosInstance = axios.getAxiosInstance()
    const res = await axiosInstance.get(`/api/v1/playlists/${props.playlistId}/videos`, {
        params: cursor ? { cursor } : {}
    })
    return {
        data: res.data.data || [],
        nextCursor: res.data.meta?.next_cursor || null
    }
}

const prefetchUntilCurrent = async () => {
    let pages = 0
    while (
        props.currentVideoHid &&
        !hasCurrent() &&
        nextCursor.value &&
        pages < MAX_PREFETCH_PAGES
    ) {
        const more = await fetchPage(nextCursor.value)
        videos.value.push(...more.data)
        nextCursor.value = more.nextCursor
        pages++
    }
}

const load = async (reset = false) => {
    if (reset) {
        videos.value = []
        nextCursor.value = null
        error.value = null
    }

    isLoading.value = true

    try {
        const { data, nextCursor: next } = await fetchPage()
        videos.value = data
        nextCursor.value = next
        await prefetchUntilCurrent()
        await scrollToCurrent()
    } catch (err) {
        console.error('Error loading playlist videos:', err)
        error.value = err
        emit('error', err)
    } finally {
        isLoading.value = false
    }
}

const loadMore = async () => {
    if (!nextCursor.value || isLoadingMore.value || isLoading.value) return

    isLoadingMore.value = true

    try {
        const { data, nextCursor: next } = await fetchPage(nextCursor.value)
        videos.value.push(...data)
        nextCursor.value = next
    } catch (err) {
        console.error('Error loading more playlist videos:', err)
    } finally {
        isLoadingMore.value = false
    }
}

const onScroll = () => {
    if (!bodyEl.value) return
    const el = bodyEl.value
    if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) {
        loadMore()
    }
}

const scrollToCurrent = async (smooth = false) => {
    if (!props.currentVideoHid || !bodyEl.value) return
    await nextTick()
    const el = bodyEl.value.querySelector('[data-current="true"]')
    if (el) el.scrollIntoView({ block: 'center', behavior: smooth ? 'smooth' : 'auto' })
}

onMounted(() => load())

watch(
    () => props.currentVideoHid,
    async () => {
        await prefetchUntilCurrent()
        await scrollToCurrent(true)
    }
)
</script>
