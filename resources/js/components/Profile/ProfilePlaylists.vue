<template>
    <div class="mt-6 hidden lg:block">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $t('studio.playlists') }}
            </h2>

            <div class="hidden md:flex items-center gap-2">
                <button
                    @click="scrollBy(-320)"
                    :disabled="!canScrollLeft"
                    class="p-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    aria-label="Scroll left"
                >
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>
                <button
                    @click="scrollBy(320)"
                    :disabled="!canScrollRight"
                    class="p-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    aria-label="Scroll right"
                >
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div
            ref="scrollEl"
            @scroll="onScroll"
            class="flex gap-3 overflow-x-hidden pb-2 snap-x scroll-smooth -mx-1 px-1"
            style="scrollbar-width: thin"
        >
            <button
                v-for="playlist in playlists"
                :key="playlist.id"
                @click="openPlaylist(playlist)"
                class="group flex-shrink-0 w-72 flex items-center gap-3 p-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/60 hover:border-gray-300 dark:hover:border-gray-700 transition-colors text-left snap-start cursor-pointer"
            >
                <div
                    class="w-9 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center"
                >
                    <img
                        v-if="playlist.cover_image && !failedImages.has(playlist.id)"
                        :src="playlist.cover_image"
                        :alt="playlist.name"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        @error="failedImages.add(playlist.id)"
                    />
                    <PlayIcon v-else class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                </div>

                <div class="flex-1 min-w-0">
                    <h3
                        class="font-bold text-gray-900 dark:text-gray-100 truncate text-sm tracking-wide"
                    >
                        {{ playlist.name }}
                    </h3>
                    <div class="flex justify-between items-end mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ playlist.videos_count }}
                            {{ playlist.videos_count === 1 ? $t('post.post') : $t('studio.posts') }}
                        </p>

                        <span v-if="isOwner" :class="getVisibilityBadgeClass(playlist.visibility)">
                            {{ formatVisibility(playlist.visibility) }}
                        </span>
                    </div>
                </div>
            </button>

            <div v-if="isLoadingMore" class="flex-shrink-0 w-32 flex items-center justify-center">
                <Spinner />
            </div>
        </div>

        <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $t('common.videos') }}
        </h2>

        <PlaylistModal
            v-if="selectedPlaylist"
            :playlist="selectedPlaylist"
            @close="selectedPlaylist = null"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, inject, computed } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'
import { PlayIcon } from '@heroicons/vue/24/solid'
import Spinner from '~/components/Spinner.vue'
import PlaylistModal from '~/components/Profile/PlaylistModal.vue'
import { useProfileStore } from '~/stores/profile'

const props = defineProps({
    playlists: { type: Array, required: true }
})

const authStore = inject('authStore')
const profileStore = useProfileStore()
const scrollEl = ref(null)
const isLoadingMore = ref(false)
const canScrollLeft = ref(false)
const canScrollRight = ref(false)
const selectedPlaylist = ref(null)
const failedImages = ref(new Set())

const openPlaylist = (playlist) => {
    selectedPlaylist.value = playlist
}

const isOwner = computed(() => {
    if (!authStore.authenticated) {
        return false
    }
    return props.playlists[0].profile_id == authStore.user.id
})

const updateScrollState = () => {
    if (!scrollEl.value) return
    const el = scrollEl.value
    canScrollLeft.value = el.scrollLeft > 4
    canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 4
}

const scrollBy = (delta) => {
    scrollEl.value?.scrollBy({ left: delta, behavior: 'smooth' })
}

const onScroll = async () => {
    updateScrollState()

    if (isLoadingMore.value || !profileStore.playlistsNextCursor) return

    const el = scrollEl.value
    const distanceFromRight = el.scrollWidth - el.scrollLeft - el.clientWidth

    if (distanceFromRight < 240) {
        isLoadingMore.value = true
        try {
            await profileStore.getPlaylists(profileStore.id, profileStore.playlistsNextCursor)
            await nextTick()
            updateScrollState()
        } catch (e) {
            console.error('Error loading more playlists:', e)
        } finally {
            isLoadingMore.value = false
        }
    }
}

const getVisibilityBadgeClass = (visibility) => {
    const baseClasses = 'inline-flex px-2 py-1 text-xs font-semibold rounded-full'

    switch (visibility) {
        case 'public':
            return `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200`
        case 'unlisted':
            return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200`
        case 'followers':
            return `${baseClasses} bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200`
        case 'private':
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200`
        default:
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200`
    }
}

const formatVisibility = (visibility) => {
    const labels = {
        public: 'Public',
        unlisted: 'Unlisted',
        followers: 'Followers',
        private: 'Private'
    }
    return labels[visibility] || visibility
}

onMounted(() => nextTick(updateScrollState))
</script>
