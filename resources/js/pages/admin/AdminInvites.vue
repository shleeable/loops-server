<template>
    <div>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Invites</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage user invites</p>
                </div>

                <AnimatedButton @click="router.push('/admin/profiles/invites/create')"
                    >Create Invite</AnimatedButton
                >
            </div>
        </div>

        <DataTable
            title="User Invites"
            :columns="columns"
            :data="invites"
            :loading="loading"
            :has-previous="pagination.prev_cursor"
            :has-next="pagination.next_cursor"
            :has-actions="false"
            :sort-options="sortOptions"
            @sort="handleSort"
            @search="handleSearch"
            @refresh="fetchInvites"
            @previous="previousPage"
            @next="nextPage"
        >
            <template #cell-id="{ value, item }">
                <router-link
                    :to="`/admin/profiles/invites/${item.id}`"
                    class="text-blue-600 hover:text-blue-800 font-medium underline cursor-pointer"
                >
                    {{ value }}
                </router-link>
            </template>

            <template #cell-title="{ value, item }">
                <p class="flex text-wrap break-all w-[250px]">
                    {{ value }}
                </p>
            </template>

            <template #cell-total_uses="{ value, item }">
                <div class="flex font-mono">
                    {{ value }}
                    <div v-if="item.max_uses">/{{ item.max_uses }}</div>
                </div>
            </template>

            <template #cell-is_active="{ value, item }">
                <span
                    v-if="item.deleted_at"
                    class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 inset-ring inset-ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:inset-ring-red-400/20"
                    >Deleted</span
                >

                <span
                    v-else-if="!value"
                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 inset-ring inset-ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:inset-ring-gray-400/20"
                    >Expired</span
                >

                <span
                    v-else
                    class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 inset-ring inset-ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:inset-ring-green-500/20"
                    >Active</span
                >
            </template>

            <template #cell-expires_at="{ value, item }">
                <div v-if="!value">
                    <div class="text-gray-900 dark:text-white font-medium text-sm">Never</div>
                </div>
                <div v-else class="flex flex-col gap-1 text-sm min-w-[140px]">
                    <div class="text-gray-900 dark:text-white font-medium">
                        {{ formatDate(value) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500" :title="value">
                        {{ getRelativeFutureTime(value) }}
                    </div>
                </div>
            </template>

            <template #cell-created_at="{ item }">
                <div class="flex flex-col gap-1 text-sm min-w-[140px]">
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
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from '@/components/DataTable.vue'
import { invitesApi } from '@/services/adminApi'
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
    Cog6ToothIcon
} from '@heroicons/vue/24/outline'
import AnimatedButton from '@/components/AnimatedButton.vue'

const { truncateMiddle, formatDate } = useUtils()

const router = useRouter()
const invites = ref([])
const loading = ref(false)
const pagination = ref({
    cursor: null,
    hasPrevious: false,
    hasNext: false
})

const searchQuery = ref('')
const sortBy = ref('')
const localOnly = ref(true)
const DEBOUNCE_DELAY = 300
let searchTimeout = null

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'title', label: 'Title' },
    { key: 'total_uses', label: 'Uses' },
    { key: 'is_active', label: 'Status' },
    { key: 'verify_email', label: 'Verify Email' },
    { key: 'email_admin_on_join', label: 'Notify Admins' },
    { key: 'expires_at', label: 'Expires At' },
    { key: 'created_at', label: 'Created At' }
]

const sortOptions = [
    { name: 'Newest', value: 'created_at_desc' },
    { name: 'Oldest', value: 'created_at_asc' },
    { name: 'Expired', value: 'expired' },
    { name: 'Deleted', value: 'deleted' },
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

function getRelativeFutureTime(futureDate) {
    const now = new Date()
    const future = new Date(futureDate)
    const diffMs = future - now

    if (diffMs < 0) {
        return null
    }

    const diffSeconds = Math.floor(diffMs / 1000)
    const diffMinutes = Math.floor(diffSeconds / 60)
    const diffHours = Math.floor(diffMinutes / 60)
    const diffDays = Math.floor(diffHours / 24)
    const diffWeeks = Math.floor(diffDays / 7)
    const diffMonths = Math.floor(diffDays / 30)
    const diffYears = Math.floor(diffDays / 365)

    if (diffHours < 24) {
        return `in ${diffHours} ${diffHours === 1 ? 'hour' : 'hours'}`
    }

    if (diffDays < 14) {
        return `in ${diffDays} ${diffDays === 1 ? 'day' : 'days'}`
    }

    if (diffDays < 90) {
        return `in ${diffWeeks} ${diffWeeks === 1 ? 'week' : 'weeks'}`
    }

    if (diffDays < 365) {
        return `in ${diffMonths} ${diffMonths === 1 ? 'month' : 'months'}`
    }

    return `in ${diffYears} ${diffYears === 1 ? 'year' : 'years'}`
}

const fetchInvites = async (cursor = null, direction = 'next') => {
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

        const response = await invitesApi.getInvites(params)
        invites.value = response.data
        pagination.value = response.meta
    } catch (error) {
        console.error('Error fetching profiles:', error)
    } finally {
        loading.value = false
    }
}

watch(searchQuery, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
        fetchInvites()
    }, DEBOUNCE_DELAY)
})

watch(sortBy, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
        fetchInvites()
    }, DEBOUNCE_DELAY)
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
        fetchInvites(pagination.value.next_cursor, 'next')
    }
}

const previousPage = () => {
    if (pagination.value.prev_cursor) {
        fetchInvites(pagination.value.prev_cursor, 'previous')
    }
}

onUnmounted(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
})

onMounted(() => {
    fetchInvites()
})
</script>
