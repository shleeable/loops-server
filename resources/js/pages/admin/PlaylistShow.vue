<template>
    <div class="">
        <div v-if="loading" class="space-y-6">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
            >
                <div class="animate-pulse space-y-4">
                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                    <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>
        </div>

        <div v-else-if="video" class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <router-link
                        to="/admin/playlists"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                    </router-link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                Playlist Details
                            </h1>
                            <span
                                class="px-2.5 py-0.5 text-xs font-semibold rounded-full capitalize"
                                :class="getVisibilityBadgeClass(playlist.visibility)"
                            >
                                {{ playlist.visibility }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <router-link
                        v-if="video.status === 'published'"
                        :to="`/v/${video.hid}`"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                        View Live
                    </router-link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6"
                    >
                        <div class="flex flex-col lg:flex-row lg:items-center lg:space-x-6">
                            <div class="flex-shrink-0 mb-4 lg:mb-0">
                                <div
                                    class="w-32 h-64 bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden"
                                >
                                    <img
                                        v-if="playlist.cover_image"
                                        :src="playlist.cover_image"
                                        :alt="`${playlist.name} cover`"
                                        class="w-full h-full object-cover"
                                        onerror="
                                            this.style.display = 'none'
                                            this.parentElement.innerHTML =
                                                '<div class=\'w-full h-full flex items-center justify-center\'><svg class=\'w-16 h-16 text-gray-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\'></path></svg></div>'
                                        "
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <QueueListIcon class="w-16 h-16 text-gray-400" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <h1
                                            class="text-2xl font-bold text-gray-900 dark:text-white mb-2"
                                        >
                                            {{ playlist.name }}
                                        </h1>
                                        <div v-if="playlist.description" class="mb-4 max-w-2xl">
                                            <AutolinkedText
                                                :caption="playlist.description"
                                                maxCharLimit="80"
                                                root-class="text-gray-500 dark:text-slate-400 whitespace-pre-wrap break-all leading-5 tracking-tight"
                                            />
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 text-sm">
                                            <span
                                                :class="
                                                    getVisibilityBadgeClass(playlist.visibility)
                                                "
                                            >
                                                {{ formatVisibility(playlist.visibility) }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ playlist.videos_count }}
                                                {{ playlist.videos_count > 1 ? 'videos' : 'video' }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ $t('common.created') }}
                                                {{ formatDate(playlist.created_at) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="videos && videos.length"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-3"
                    >
                        <div
                            v-for="(video, index) in videos"
                            :key="video.id"
                            class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 transition-colors group"
                        >
                            <div
                                class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <Bars3Icon class="h-6 w-6" />
                            </div>

                            <div
                                class="flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400 w-8"
                            >
                                {{ index + 1 }}
                            </div>

                            <img
                                :src="video.media.thumbnail"
                                :alt="video.caption"
                                class="w-12 h-20 rounded-lg object-cover flex-shrink-0"
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
                                    class="flex items-center gap-2 mt-3 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    <div>{{ formatDate(video.created_at) }}</div>
                                    <div>·</div>
                                    <div class="flex items-center">
                                        <EyeIcon class="h-4 w-4 mr-1" />
                                        {{ video.views?.toLocaleString() || 0 }}
                                    </div>
                                    <div>·</div>
                                    <div class="flex items-center">
                                        <HeartIcon class="h-4 w-4 mr-1" />
                                        {{ video.likes?.toLocaleString() || 0 }}
                                    </div>
                                    <div>·</div>
                                    <div class="flex items-center">
                                        <ChatBubbleLeftIcon class="h-4 w-4 mr-1" />
                                        {{ video.comments?.toLocaleString() || 0 }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <router-link
                                    :to="`/v/${video.hid}`"
                                    class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-sm font-medium border-blue-300 text-blue-700 bg-blue-50/60 hover:bg-blue-100 hover:border-blue-400 dark:border-blue-500/70 dark:text-blue-300 dark:bg-blue-900/20 dark:hover:bg-blue-900/50 dark:hover:border-blue-300 transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900 cursor-pointer"
                                >
                                    <Tooltip title="View this video">
                                        <EyeIcon class="w-4 h-4" />
                                    </Tooltip>
                                </router-link>

                                <router-link
                                    :to="`/admin/videos/${video.id}`"
                                    class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-sm font-medium border-red-300 text-red-700 bg-red-50/60 hover:bg-red-100 hover:border-red-400 dark:border-red-500/70 dark:text-red-300 dark:bg-red-900/20 dark:hover:bg-red-900/50 dark:hover:border-red-300 transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900 cursor-pointer"
                                >
                                    <Tooltip title="Manage this video">
                                        <Cog6ToothIcon class="w-4 h-4" />
                                    </Tooltip>
                                </router-link>
                            </div>
                        </div>

                        <div v-if="hasMoreVideos" class="mt-6 text-center">
                            <button
                                @click="loadMoreVideos"
                                :disabled="loadingVideos"
                                class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ loadingVideos ? 'Loading...' : 'Load More Videos' }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-20 space-y-3"
                    >
                        <div class="text-center">
                            No videos have been added to this playlist yet
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5"
                    >
                        <div
                            class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3"
                        >
                            <UserCircleIcon class="h-4 w-4" />
                            Posted By
                        </div>
                        <router-link
                            :to="`/admin/profiles/${video.profile.id}`"
                            class="flex items-center gap-3 -m-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <img
                                :src="video.profile.avatar"
                                :alt="video.profile.username"
                                class="w-12 h-12 rounded-full ring-2 ring-gray-100 dark:ring-gray-700"
                                @error="$event.target.src = '/storage/avatars/default.jpg'"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-900 dark:text-white truncate">
                                    {{ video.profile.display_name || video.profile.username }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                    @{{ video.profile.username }}
                                </div>
                            </div>
                            <ChevronRightIcon class="h-5 w-5 text-gray-400 flex-shrink-0" />
                        </router-link>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                Actions
                            </h3>
                        </div>
                        <div class="p-5 space-y-2">
                            <button
                                @click="handleDeletePlaylist()"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/30 text-sm font-medium rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors cursor-pointer"
                            >
                                <TrashIcon class="h-5 w-5" />
                                Delete Playlist
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center"
        >
            <FilmIcon class="h-12 w-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Playlist Not Found
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                The playlist you're looking for doesn't exist or has been removed.
            </p>
            <router-link
                to="/admin/playlists"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                <ArrowLeftIcon class="h-4 w-4" />
                Back to Playlist
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { playlistsApi } from '@/services/adminApi'
import { useAlertModal } from '@/composables/useAlertModal.js'
import {
    ArrowDownTrayIcon,
    ArrowLeftIcon,
    ArrowPathIcon,
    ArrowTopRightOnSquareIcon,
    BookmarkIcon,
    CalendarIcon,
    ChatBubbleLeftRightIcon,
    ChatBubbleOvalLeftIcon,
    CheckBadgeIcon,
    CheckCircleIcon,
    CheckIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    ChevronUpIcon,
    CircleStackIcon,
    ClipboardDocumentIcon,
    ClockIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    EnvelopeIcon,
    EyeIcon,
    EyeSlashIcon,
    FilmIcon,
    FlagIcon,
    GlobeAltIcon,
    HeartIcon,
    KeyIcon,
    LanguageIcon,
    LockClosedIcon,
    MegaphoneIcon,
    NoSymbolIcon,
    PencilSquareIcon,
    ScissorsIcon,
    ShareIcon,
    ShieldCheckIcon,
    SparklesIcon,
    TrashIcon,
    UserCircleIcon,
    UserIcon,
    UsersIcon,
    ArrowsPointingOutIcon,
    Cog8ToothIcon,
    CodeBracketIcon,
    Cog6ToothIcon,
    QueueListIcon
} from '@heroicons/vue/24/outline'
import { useUtils } from '@/composables/useUtils'

const DEFAULT_AVATAR = '/storage/avatars/default.jpg'
const { formatNumber, formatTimeAgo, formatDateTime, formatBytes, formatDuration, formatDate } =
    useUtils()
const { confirmModal, alertModal } = useAlertModal()

const route = useRoute()
const router = useRouter()

const videoId = computed(() => route.params.id)
const loading = ref(true)
const video = ref(null)
const playlist = ref(null)
const videos = ref([])
const hasMoreVideos = ref(false)
const loadingVideos = ref(false)
const isLoadingMore = ref(false)
const nextCursor = ref()

const formatValue = (val) => {
    if (val === true) return 'Enabled'
    if (val === false) return 'Disabled'
    if (val === null || val === undefined || val === '') return '—'
    return String(val)
}

const fetchPlaylist = async () => {
    loading.value = true
    try {
        const response = await playlistsApi.getPlaylist(videoId.value)
        video.value = response.data
        playlist.value = response.data

        await fetchPlaylistVideos()
    } catch (error) {
        console.error('Error fetching video:', error)
        video.value = null
    } finally {
        loading.value = false
    }
}

const fetchPlaylistVideos = async () => {
    loadingVideos.value = true
    try {
        const res = await playlistsApi.getPlaylistVideos(videoId.value)
        videos.value = res.data
        nextCursor.value = res.meta?.next_cursor
        hasMoreVideos.value = res.meta?.next_cursor
    } catch (error) {
        console.error('Error fetching playlist videos:', error)
        videos.value = null
    } finally {
        loadingVideos.value = false
    }
}

const loadMoreVideos = async () => {
    if (!nextCursor.value || isLoadingMore.value) return

    loadingVideos.value = true
    isLoadingMore.value = true
    try {
        const res = await playlistsApi.getPlaylistVideos(videoId.value, nextCursor.value)
        videos.value.push(...(res.data || []))
        nextCursor.value = res.meta?.next_cursor
        hasMoreVideos.value = res.meta?.next_cursor
    } catch (error) {
        console.error('Error fetching playlist videos:', error)
    } finally {
        loadingVideos.value = false
        isLoadingMore.value = false
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
            return `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200`
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

const handleDeletePlaylist = async () => {
    const result = await confirmModal(
        'Delete Playlist?',
        'Are you sure you want to delete this playlist?',
        'Delete',
        'Cancel'
    )

    if (result) {
        await playlistsApi.deletePlaylist(playlist.value.id)
        router.push('/admin/playlists')
    }
}

const handleAvatarError = (event) => {
    const img = event.target
    if (img.dataset.fallbackApplied) return
    img.dataset.fallbackApplied = '1'
    img.src = DEFAULT_AVATAR
}

const deleteCommentReply = async (comment, parent = null) => {}

onMounted(() => {
    fetchPlaylist()
})
</script>
