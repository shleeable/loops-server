<template>
    <div>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Starter Kits</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage user Starter Kits</p>
                </div>

                <div class="flex gap-3">
                    <label
                        class="flex items-center space-x-2 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                    >
                        <input
                            v-model="showMedia"
                            type="checkbox"
                            class="w-4 h-6 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300"> Show Media </span>
                    </label>

                    <AnimatedButton @click="router.push('/admin/settings?tab=starterKits')">
                        <div class="flex items-center gap-2">
                            <Cog8ToothIcon class="w-5 h-5" />
                            Starter Kit Settings
                        </div>
                    </AnimatedButton>
                </div>
            </div>
        </div>

        <DataTable
            title="Starter Kits"
            :columns="columns"
            :data="profiles"
            :loading="loading"
            :has-previous="pagination.prev_cursor"
            :has-next="pagination.next_cursor"
            :has-actions="false"
            :sort-options="sortOptions"
            :show-local-filter="true"
            :initial-local-filter="true"
            :initial-sort="sortBy"
            :initial-search-query="searchQuery"
            @local-change="handleLocalChange"
            @sort="handleSort"
            @search="handleSearch"
            @refresh="fetchStarterKits"
            @previous="previousPage"
            @next="nextPage"
        >
            <template #cell-id="{ value }">
                <router-link
                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline cursor-pointer"
                    :to="`/admin/starterkits/${value}`"
                >
                    {{ truncateMiddle(value, 12) }}
                </router-link>
            </template>

            <template #cell-title="{ value, item }">
                <div class="flex items-center gap-2">
                    <div class="flex flex-col">
                        <p>{{ value }}</p>
                        <p class="text-xs text-gray-500 truncate max-w-[300px]">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </template>

            <template #cell-icon_url="{ value }">
                <div class="flex items-center gap-2">
                    <img
                        v-if="showMedia && value"
                        :src="value"
                        alt="Icon"
                        class="w-10 h-10 rounded"
                    />
                </div>
            </template>

            <template #cell-header_url="{ value }">
                <div class="flex items-center gap-2">
                    <img
                        v-if="showMedia && value"
                        :src="value"
                        alt="Header"
                        class="w-20 h-10 rounded"
                    />
                </div>
            </template>

            <template #cell-creator="{ item, value }">
                <router-link
                    :to="`/admin/profiles/${value.id}`"
                    class="flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 -m-2 p-2 rounded-lg transition-colors group"
                >
                    <div class="relative flex-shrink-0">
                        <img
                            :src="value.avatar"
                            :alt="value.username"
                            class="w-12 h-12 rounded-full ring-2 ring-gray-200 dark:ring-gray-700 group-hover:ring-blue-400 dark:group-hover:ring-blue-500 transition-all"
                            @error="$event.target.src = '/storage/avatars/default.jpg'"
                        />
                        <div
                            v-if="!item.is_local"
                            class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 dark:bg-blue-400 rounded-full border-2 border-white dark:border-gray-900 flex items-center justify-center"
                            title="Federated user"
                        >
                            <GlobeAltIcon class="w-2.5 h-2.5 text-white" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div
                                class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate transition-colors"
                            >
                                @{{ value.username }}
                            </div>
                        </div>
                        <div
                            v-if="value.name"
                            class="text-sm text-gray-600 dark:text-gray-400 truncate"
                        >
                            {{ value.name }}
                        </div>
                    </div>
                </router-link>
            </template>

            <template #cell-status="{ item }">
                <div class="flex items-start flex-col gap-2 min-w-[120px]">
                    <span
                        v-if="item.status === 10"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs font-medium rounded-full"
                    >
                        <CheckCircleIcon class="w-3.5 h-3.5" />
                        Active
                    </span>
                    <span
                        v-else-if="item.status === 5"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-medium rounded-full"
                    >
                        <XCircleIcon class="w-3.5 h-3.5" />
                        Suspended
                    </span>
                    <span
                        v-else-if="item.status === 0"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-yellow-100 dark:bg-yellow-800 text-yellow-600 dark:text-yellow-400 text-xs font-medium rounded-full"
                    >
                        <EyeSlashIcon class="w-3.5 h-3.5" />
                        Awaiting Approval
                    </span>
                    <span
                        v-else-if="item.status === 8"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-medium rounded-full"
                    >
                        <XCircleIcon class="w-3.5 h-3.5" />
                        Deleted
                    </span>
                    <span
                        v-else-if="item.status === 2"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-medium rounded-full"
                    >
                        <EyeSlashIcon class="w-3.5 h-3.5" />
                        Pending
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 text-xs font-medium rounded-full"
                    >
                        <ExclamationCircleIcon class="w-3.5 h-3.5" />
                        {{ item.status_desc }}
                    </span>
                    <span
                        v-if="item.is_blocking"
                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 text-xs rounded"
                        title="Instance is blocked"
                    >
                        <ShieldExclamationIcon class="w-3 h-3" />
                    </span>

                    <span
                        v-if="item.is_discoverable"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs font-medium rounded-full"
                    >
                        <CheckCircleIcon class="w-3.5 h-3.5" />
                        Discoverable
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 text-xs font-medium rounded-full"
                    >
                        <EyeSlashIcon class="w-3.5 h-3.5" />
                        Not Discoverable
                    </span>

                    <span
                        v-if="item.is_sensitive"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-medium rounded-full"
                    >
                        <EyeSlashIcon class="w-3.5 h-3.5" />
                        Sensitive
                    </span>
                </div>
            </template>

            <template #cell-created_at="{ item }">
                <div class="flex flex-col gap-1 text-sm">
                    <div class="text-gray-900 dark:text-white font-medium">
                        {{ formatDate(item.created_at) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500" :title="item.created_at">
                        {{ formatRelativeTime(item.created_at) }}
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DataTable from '@/components/DataTable.vue'
import { starterKitsApi } from '@/services/adminApi'
import { useUtils } from '@/composables/useUtils'
import {
    GlobeAltIcon,
    ShieldCheckIcon,
    VideoCameraIcon,
    UsersIcon,
    UserPlusIcon,
    CloudArrowUpIcon,
    NoSymbolIcon,
    CheckCircleIcon,
    XCircleIcon,
    EyeSlashIcon,
    ExclamationCircleIcon,
    ShieldExclamationIcon,
    EyeIcon,
    Cog6ToothIcon,
    Cog8ToothIcon
} from '@heroicons/vue/24/outline'

const { truncateMiddle, formatDate } = useUtils()

const router = useRouter()
const route = useRoute()
const profiles = ref([])
const loading = ref(false)
const showMedia = ref(localStorage.getItem('loops_admin_show_media') === '1')
const pagination = ref({
    cursor: null,
    hasPrevious: false,
    hasNext: false
})

const searchQuery = ref(route.query.q || '')
const sortBy = ref(route.query.sortBy || localStorage.getItem('loops_admin_skits_sortby') || '')
const localOnly = ref(true)
const DEBOUNCE_DELAY = 300
let searchTimeout = null

const columns = computed(() => {
    return showMedia.value
        ? [
              { key: 'id', label: 'ID' },
              { key: 'title', label: 'Title' },
              { key: 'icon_url', label: 'Icon' },
              { key: 'header_url', label: 'Header' },
              { key: 'status', label: 'Status' },
              { key: 'creator', label: 'Creator' },
              { key: 'created_at', label: 'Created' }
          ]
        : [
              { key: 'id', label: 'ID' },
              { key: 'title', label: 'Title' },
              { key: 'status', label: 'Status' },
              { key: 'creator', label: 'Creator' },
              { key: 'created_at', label: 'Created' }
          ]
})

const sortOptions = [
    { name: 'Awaiting Review', value: 'starter_kits.awaiting_review' },
    { name: 'Approved', value: 'starter_kits.approved' },
    { name: 'Disabled/Pending', value: 'starter_kits.disabled' },
    { name: 'Suspended', value: 'starter_kits.suspended' },
    { name: 'Newest', value: 'created_at_desc' },
    { name: 'Oldest', value: 'created_at_asc' },
    { name: 'Title A-Z', value: 'title_asc' },
    { name: 'Title Z-A', value: 'title_desc' },
    { name: 'Last Updated', value: 'updated_at_desc' }
]

const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M'
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K'
    }
    return num.toString()
}

const formatRelativeTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const seconds = Math.floor((now - date) / 1000)

    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    }

    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit)
        if (interval >= 1) {
            return `${interval} ${unit}${interval === 1 ? '' : 's'} ago`
        }
    }

    return 'just now'
}

const extractDomain = (url) => {
    try {
        const urlObj = new URL(url)
        return urlObj.hostname.replace('www.', '')
    } catch {
        return url
    }
}

const fetchStarterKits = async (cursor = null, direction = 'next') => {
    loading.value = true
    try {
        const params = { cursor, direction }

        if (searchQuery.value) {
            params.search = searchQuery.value
        }

        if (sortBy.value) {
            params.sort = sortBy.value
        }

        if (localOnly.value) {
            params.local = true
        }

        const response = await starterKitsApi.getStarterKits(params)
        profiles.value = response.data
        pagination.value = response.meta
    } catch (error) {
        console.error('Error fetching profiles:', error)
    } finally {
        loading.value = false
    }
}

const viewProfile = (profile) => {
    router.push(`/@${profile.username}`)
}

const manageProfile = (profile) => {
    router.push(`/admin/profiles/${profile.id}`)
}

watch(
    () => route.query.q,
    (newQuery) => {
        searchQuery.value = newQuery || ''
    }
)

watch(localOnly, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
        fetchStarterKits()
    }, DEBOUNCE_DELAY)
})

watch(searchQuery, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
        fetchStarterKits()
    }, DEBOUNCE_DELAY)
})

watch(sortBy, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    if (newQuery) {
        localStorage.setItem('loops_admin_skits_sortby', newQuery)
    } else {
        localStorage.removeItem('loops_admin_skits_sortby')
    }

    searchTimeout = setTimeout(() => {
        fetchStarterKits()
    }, DEBOUNCE_DELAY)
})

watch(showMedia, (newVal) => {
    if (newVal) {
        localStorage.setItem('loops_admin_show_media', '1')
    } else {
        localStorage.removeItem('loops_admin_show_media')
    }
})

const handleSort = (sortValue) => {
    sortBy.value = sortValue
}

const handleSearch = (query) => {
    searchQuery.value = query
}

const handleLocalChange = (value) => {
    localOnly.value = value
}

const nextPage = () => {
    if (pagination.value.next_cursor) {
        fetchStarterKits(pagination.value.next_cursor, 'next')
    }
}

const previousPage = () => {
    if (pagination.value.prev_cursor) {
        fetchStarterKits(pagination.value.prev_cursor, 'previous')
    }
}

onUnmounted(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
})

onMounted(() => {
    fetchStarterKits()
})
</script>
