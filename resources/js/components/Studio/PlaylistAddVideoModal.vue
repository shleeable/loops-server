<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/25 dark:bg-black/50" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left align-middle shadow-xl transition-all"
                        >
                            <div class="p-6">
                                <DialogTitle
                                    as="h3"
                                    class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4"
                                >
                                    {{ $t('studio.addVideosToPlaylist') }}
                                </DialogTitle>

                                <div class="mb-4 flex gap-2">
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="searchQuery"
                                            @input="handleSearch"
                                            type="text"
                                            :placeholder="$t('studio.searchYourVideosDotDotDot')"
                                            class="pl-10 pr-4 py-2 w-full border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                        />
                                    </div>

                                    <Listbox v-model="selectedSort">
                                        <div class="relative w-52">
                                            <ListboxButton
                                                class="relative w-full cursor-pointer rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 py-[10px] pl-3 pr-10 text-left text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            >
                                                <span class="block truncate">{{
                                                    selectedSort.label
                                                }}</span>
                                                <span
                                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                                                >
                                                    <ChevronUpDownIcon
                                                        class="h-5 w-5 text-gray-400"
                                                    />
                                                </span>
                                            </ListboxButton>
                                            <transition
                                                leave-active-class="transition duration-100 ease-in"
                                                leave-from-class="opacity-100"
                                                leave-to-class="opacity-0"
                                            >
                                                <ListboxOptions
                                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-700 py-1 text-sm shadow-lg ring-1 ring-black/5 dark:ring-white/10 focus:outline-none"
                                                >
                                                    <ListboxOption
                                                        v-for="option in sortOptions"
                                                        :key="option.id"
                                                        :value="option"
                                                        v-slot="{ active, selected }"
                                                        as="template"
                                                    >
                                                        <li
                                                            :class="[
                                                                active
                                                                    ? 'bg-red-50 dark:bg-red-900/30 text-red-900 dark:text-red-100'
                                                                    : 'text-gray-900 dark:text-gray-100',
                                                                'relative cursor-pointer select-none py-2 pl-10 pr-4'
                                                            ]"
                                                        >
                                                            <span
                                                                :class="[
                                                                    selected
                                                                        ? 'font-medium'
                                                                        : 'font-normal',
                                                                    'block truncate'
                                                                ]"
                                                            >
                                                                {{ option.label }}
                                                            </span>
                                                            <span
                                                                v-if="selected"
                                                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-red-600 dark:text-red-400"
                                                            >
                                                                <CheckIcon class="h-5 w-5" />
                                                            </span>
                                                        </li>
                                                    </ListboxOption>
                                                </ListboxOptions>
                                            </transition>
                                        </div>
                                    </Listbox>
                                </div>

                                <div
                                    v-if="loading && availableVideos.length === 0"
                                    class="text-center py-8"
                                >
                                    <Spinner />
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ $t('studio.loadingVideosDotDotDot') }}
                                    </p>
                                </div>

                                <div
                                    v-else-if="availableVideos.length === 0"
                                    class="text-center py-8"
                                >
                                    <VideoCameraIcon class="mx-auto h-12 w-12 text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{
                                            searchQuery
                                                ? $t('studio.noVideosFound')
                                                : $t('studio.noAvailableVideosToAdd')
                                        }}
                                    </p>
                                </div>

                                <div
                                    v-else
                                    ref="scrollContainer"
                                    @scroll="handleScroll"
                                    class="max-h-96 overflow-y-auto space-y-2 scroll-smooth"
                                >
                                    <div
                                        v-for="video in availableVideos"
                                        :key="video.id"
                                        @click="toggleVideo(video.id)"
                                        class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                        :class="{
                                            'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-700/20':
                                                selectedVideos.has(video.id)
                                        }"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="selectedVideos.has(video.id)"
                                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded pointer-events-none"
                                        />
                                        <img
                                            :src="video.media.thumbnail"
                                            :alt="video.caption"
                                            class="w-9 h-16 rounded-lg object-cover flex-shrink-0"
                                            onerror="
                                                this.src = '/storage/videos/video-placeholder.jpg'
                                                this.onerror = null
                                            "
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                                            >
                                                {{ video.caption || 'Untitled Video' }}
                                            </p>
                                            <div
                                                class="flex gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                <div>
                                                    {{ formatDate(video.created_at) }}
                                                </div>

                                                <div>·</div>

                                                <div>
                                                    {{ formatCount(video.views_count) }} Views
                                                </div>

                                                <div>·</div>

                                                <div>{{ formatCount(video.likes) }} Likes</div>

                                                <div>·</div>

                                                <div>
                                                    {{ formatCount(video.comments) }} Comments
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="loading && availableVideos.length > 0"
                                        class="text-center py-4"
                                    >
                                        <Spinner class="inline-block" />
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                            {{ $t('studio.loadingMoreDotDotDot') }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="error"
                                    class="mt-4 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-md text-sm"
                                >
                                    {{ error }}
                                </div>

                                <div
                                    class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700"
                                >
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ selectedVideos.size }} video(s) selected
                                    </p>
                                    <div class="flex space-x-3">
                                        <button
                                            type="button"
                                            @click="closeModal"
                                            :disabled="adding"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md disabled:opacity-50 transition-colors"
                                        >
                                            {{ $t('common.cancel') }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="handleAdd"
                                            :disabled="adding || selectedVideos.size === 0"
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md disabled:opacity-50 transition-colors"
                                        >
                                            {{
                                                adding
                                                    ? 'Adding...'
                                                    : `Add ${selectedVideos.size} Video(s)`
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import { useUtils } from '@/composables/useUtils'
import { usePlaylistStore } from '@/stores/playlist'
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption
} from '@headlessui/vue'
import {
    MagnifyingGlassIcon,
    VideoCameraIcon,
    ChevronUpDownIcon,
    CheckIcon
} from '@heroicons/vue/24/outline'

const STORAGE_KEY = 'loops_studio_playlists_add_sort'

const sortOptions = [
    { id: 'newest', label: 'Newest videos', field: 'created_at', direction: 'desc' },
    { id: 'oldest', label: 'Oldest videos', field: 'created_at', direction: 'asc' },
    { id: 'most_liked', label: 'Most liked', field: 'likes', direction: 'desc' },
    { id: 'most_commented', label: 'Most commented', field: 'comments', direction: 'desc' }
]

const getStoredSort = () => {
    const storedId = localStorage.getItem(STORAGE_KEY)
    return sortOptions.find((opt) => opt.id === storedId) || sortOptions[0]
}

const selectedSort = ref(getStoredSort())
const skipNextSortWatch = ref(false)

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    playlistId: {
        type: Number,
        required: true
    },
    excludeVideoIds: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['close', 'added'])

const playlistStore = usePlaylistStore()
const { formatDate, formatNumber, formatCount } = useUtils()

const { availableVideos, loading } = storeToRefs(playlistStore)
const hasMore = computed(() => playlistStore.availableVideosPagination.hasMore)
const error = computed(() => playlistStore.error)

const adding = ref(false)
const searchQuery = ref('')
const selectedVideos = ref(new Set())
const scrollContainer = ref(null)
const isLoadingMore = ref(false)

let searchTimeout = null

const debounce = (func, wait) => {
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(searchTimeout)
            func(...args)
        }
        clearTimeout(searchTimeout)
        searchTimeout = setTimeout(later, wait)
    }
}

const isScrollable = () => {
    if (!scrollContainer.value) return false
    return scrollContainer.value.scrollHeight > scrollContainer.value.clientHeight
}

const ensureScrollable = async () => {
    await nextTick()

    if (!scrollContainer.value || isLoadingMore.value || loading.value) return

    if (!isScrollable() && hasMore.value && availableVideos.value.length > 0) {
        const cursor = playlistStore.availableVideosPagination.nextCursor
        if (cursor) {
            await loadVideos(cursor)
            await ensureScrollable()
        }
    }
}

const loadVideos = async (cursor = null) => {
    if (isLoadingMore.value) return

    try {
        if (cursor) {
            isLoadingMore.value = true
        }

        await playlistStore.fetchAvailableVideos({
            search: searchQuery.value,
            cursor,
            limit: 10,
            excludeIds: props.excludeVideoIds,
            sortField: selectedSort.value.field,
            sortDirection: selectedSort.value.direction
        })

        if (!cursor) {
            await ensureScrollable()
        }
    } catch (err) {
        console.error('Error loading videos:', err)
    } finally {
        isLoadingMore.value = false
    }
}

const handleSearch = debounce(() => {
    loadVideos()
}, 300)

const handleScroll = (event) => {
    if (!hasMore.value || loading.value || isLoadingMore.value) return

    const target = event.target
    const scrollPosition = target.scrollTop + target.clientHeight
    const scrollHeight = target.scrollHeight

    if (scrollHeight - scrollPosition < 100) {
        const cursor = playlistStore.availableVideosPagination.nextCursor
        if (cursor) {
            loadVideos(cursor).then(() => ensureScrollable())
        }
    }
}

const toggleVideo = (videoId) => {
    if (selectedVideos.value.has(videoId)) {
        selectedVideos.value.delete(videoId)
    } else {
        selectedVideos.value.add(videoId)
    }
    selectedVideos.value = new Set(selectedVideos.value)
}

const handleAdd = async () => {
    adding.value = true

    try {
        const promises = Array.from(selectedVideos.value).map((videoId) =>
            playlistStore.addVideoToPlaylist(props.playlistId, videoId)
        )

        await Promise.all(promises)
        emit('added')
        closeModal()
    } catch (err) {
        console.error('Error adding videos:', err)
    } finally {
        adding.value = false
    }
}

const closeModal = () => {
    if (!adding.value) {
        selectedVideos.value = new Set()
        searchQuery.value = ''
        playlistStore.clearAvailableVideos()
        emit('close')
    }
}

watch(selectedSort, (newVal) => {
    localStorage.setItem(STORAGE_KEY, newVal.id)
    if (skipNextSortWatch.value) {
        skipNextSortWatch.value = false
        return
    }
    playlistStore.clearAvailableVideos()
    loadVideos()
})

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            selectedVideos.value = new Set()
            searchQuery.value = ''
            skipNextSortWatch.value = true
            selectedSort.value = getStoredSort()
            playlistStore.clearAvailableVideos()
            loadVideos()
        }
    }
)

watch(
    () => availableVideos.value.length,
    async () => {
        if (props.isOpen && availableVideos.value.length > 0) {
            await ensureScrollable()
        }
    }
)
</script>
