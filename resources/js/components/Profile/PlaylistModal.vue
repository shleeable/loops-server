<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="$emit('close')"
        >
            <div
                class="w-full max-w-xl max-h-[85vh] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                role="dialog"
                aria-modal="true"
            >
                <div
                    class="relative border-b border-gray-200 dark:border-gray-800 px-6 py-4 flex-shrink-0"
                >
                    <h2
                        class="text-center font-bold text-xl text-gray-900 dark:text-gray-100 tracking-tight truncate px-8"
                    >
                        {{ playlist.name }}
                        <span class="tracking-wide">({{ playlist.videos_count }})</span>
                    </h2>
                    <button
                        @click="$emit('close')"
                        class="absolute top-1/2 -translate-y-1/2 right-4 p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition-colors"
                        aria-label="Close"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div ref="bodyEl" @scroll="onScroll" class="flex-1 min-h-0 overflow-y-auto">
                    <div v-if="isLoading && !videos.length" class="flex justify-center py-16">
                        <Spinner />
                    </div>

                    <div
                        v-else-if="error"
                        class="flex flex-col items-center justify-center py-16 px-6 text-center"
                    >
                        <p class="text-gray-600 dark:text-gray-400 mb-2">
                            An error occured while attempting to load this playlist.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Please try again later.</p>
                        <button
                            @click="load(true)"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors"
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

                    <ul v-else class="divide-y divide-gray-100 dark:divide-gray-800 px-4">
                        <li v-for="(video, idx) in videos" :key="video.id">
                            <router-link
                                :to="`/v/${video.hid}?playlist_id=${playlist.id}`"
                                @click="$emit('close')"
                                class="flex items-stretch gap-3 py-3 group"
                            >
                                <span
                                    class="w-8 flex items-center justify-center text-sm font-medium text-gray-400 dark:text-gray-500 tabular-nums shrink-0"
                                >
                                    {{ String(idx + 1).padStart(2, '0') }}
                                </span>

                                <div
                                    class="w-14 h-20 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0"
                                >
                                    <img
                                        v-if="thumbFor(video)"
                                        :src="thumbFor(video)"
                                        :alt="video.caption || ''"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                </div>

                                <div class="flex-1 min-w-0 flex flex-col justify-around py-0.5">
                                    <p
                                        class="text-sm text-gray-900 font-medium dark:text-gray-100 line-clamp-2"
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
                                            >{{ formatCount(video.likes || 0) }}
                                            {{ $t('profile.likes') }}</span
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
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import axios from '@/plugins/axios'
import Spinner from '~/components/Spinner.vue'
import { useUtils } from '@/composables/useUtils'

const props = defineProps({
    playlist: { type: Object, required: true }
})

const emit = defineEmits(['close'])

const { formatCount } = useUtils()

const videos = ref([])
const nextCursor = ref(null)
const isLoading = ref(false)
const isLoadingMore = ref(false)
const error = ref(null)
const bodyEl = ref(null)

const thumbFor = (v) => v.media.thumbnail || v.thumbnail || v.media_url || v.cover_image || null

const load = async (reset = false) => {
    if (reset) {
        videos.value = []
        nextCursor.value = null
        error.value = null
    }

    isLoading.value = true

    try {
        const axiosInstance = axios.getAxiosInstance()
        const res = await axiosInstance.get(`/api/v1/playlists/${props.playlist.id}/videos`)
        videos.value = res.data.data || []
        nextCursor.value = res.data.meta?.next_cursor
    } catch (err) {
        console.error('Error loading playlist videos:', err)
        error.value = err
    } finally {
        isLoading.value = false
    }
}

const loadMore = async () => {
    if (!nextCursor.value || isLoadingMore.value) return

    isLoadingMore.value = true

    try {
        const axiosInstance = axios.getAxiosInstance()
        const res = await axiosInstance.get(`/api/v1/playlists/${props.playlist.id}/videos`, {
            params: { cursor: nextCursor.value }
        })
        videos.value.push(...(res.data.data || []))
        nextCursor.value = res.data.meta?.next_cursor
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

const onKeydown = (e) => {
    if (e.key === 'Escape') emit('close')
}

onMounted(() => {
    load()
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', onKeydown)
})
</script>
