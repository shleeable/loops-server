<template>
    <div>
        <div v-if="!config.curated_onboarding_enabled">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Curated Applications
                </h1>
            </div>
            <p>
                This feature is not available when registration mode is open or closed, to enable
                this, please enable Curated registration mode.
            </p>
        </div>
        <div v-else>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Curated Applications
                </h1>
                <div
                    v-if="stats"
                    class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400"
                >
                    <span>{{ stats.ready }} ready to review</span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span>{{ stats.total }} total</span>
                    <span v-if="stats.avg_review_hours" class="text-gray-300 dark:text-gray-600"
                        >|</span
                    >
                    <span v-if="stats.avg_review_hours"
                        >~{{ stats.avg_review_hours }}h avg review</span
                    >
                </div>
                <AnimatedButton @click="router.push('/admin/curated-onboarding-settings')">
                    Manage Settings
                </AnimatedButton>
            </div>

            <div v-if="stats" class="grid grid-cols-2 sm:grid-cols-5 md:grid-cols-6 gap-3 mb-6">
                <button
                    v-for="s in statusCards"
                    :key="s.key"
                    @click="setStatusFilter(s.key)"
                    class="p-3 rounded-lg border text-left transition-colors"
                    :class="
                        activeStatus === s.key
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ s.count }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ s.label }}</div>
                </button>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
                <div class="relative flex-1 w-full sm:max-w-sm">
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by email, username, or fedi account..."
                        @input="debouncedSearch"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div v-if="selectedIds.length" class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400"
                        >{{ selectedIds.length }} selected</span
                    >
                    <AnimatedButton
                        size="xs"
                        variant="primaryGradient"
                        @click="handleBulkApprove"
                        :disabled="bulkLoading"
                    >
                        Approve</AnimatedButton
                    >
                    <AnimatedButton
                        size="xs"
                        variant="primaryOutline"
                        @click="showBulkRejectModal = true"
                        :disabled="bulkLoading"
                    >
                        Reject</AnimatedButton
                    >
                </div>

                <label
                    class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer"
                >
                    <input
                        v-model="verifiedOnly"
                        type="checkbox"
                        @change="loadApplications"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    Verified only
                </label>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="w-10 px-3 py-3">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    :indeterminate="someSelected && !allSelected"
                                    @change="toggleSelectAll"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                />
                            </th>
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                @click="col.sortable && toggleSort(col.key)"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                :class="{
                                    'cursor-pointer hover:text-gray-700 dark:hover:text-gray-200':
                                        col.sortable
                                }"
                            >
                                <span class="inline-flex items-center gap-1">
                                    {{ col.label }}
                                    <template v-if="col.sortable && sortField === col.key">
                                        <svg
                                            v-if="sortDir === 'asc'"
                                            class="w-3 h-3"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M5 12l5-5 5 5H5z" />
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-3 h-3"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M5 8l5 5 5-5H5z" />
                                        </svg>
                                    </template>
                                </span>
                            </th>
                            <th class="w-20 px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900"
                    >
                        <tr v-if="loading && !applications.length">
                            <td
                                :colspan="columns.length + 2"
                                class="px-4 py-12 text-center text-gray-500 dark:text-gray-400"
                            >
                                Loading...
                            </td>
                        </tr>
                        <tr v-else-if="!applications.length">
                            <td
                                :colspan="columns.length + 2"
                                class="px-4 py-12 text-center text-gray-500 dark:text-gray-400"
                            >
                                No applications found.
                            </td>
                        </tr>
                        <tr
                            v-for="app in applications"
                            :key="app.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        >
                            <td class="px-3 py-3">
                                <input
                                    v-if="app.status === 'ready'"
                                    type="checkbox"
                                    :value="app.id"
                                    v-model="selectedIds"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ app.email }}
                                </div>
                                <div
                                    v-if="app.username_requested"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    @{{ app.username_requested }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ app.age_at_submission }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="app.fediverse_account"
                                    class="text-sm text-blue-600 dark:text-blue-400"
                                >
                                    {{ app.fediverse_account }}
                                </span>
                                <span v-else class="text-sm text-gray-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge :status="app.status" />
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    {{ app.email_verified ? '✓' : '✗' }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap"
                            >
                                {{ formatDate(app.created_at) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <AnimatedButton
                                    size="xs"
                                    variant="primaryOutline"
                                    pill
                                    @click="router.push(`/admin/curated-onboarding/${app.id}`)"
                                >
                                    Review
                                </AnimatedButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="pagination && pagination.last_page > 1"
                class="flex items-center justify-between mt-4"
            >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </p>
                <div class="flex gap-1">
                    <button
                        v-for="page in pagination.last_page"
                        :key="page"
                        @click="goToPage(page)"
                        class="px-3 py-1 text-sm rounded-lg border transition-colors"
                        :class="
                            page === pagination.current_page
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-600'
                                : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'
                        "
                    >
                        {{ page }}
                    </button>
                </div>
            </div>

            <Teleport to="body">
                <div
                    v-if="showBulkRejectModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                >
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md mx-4"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Reject {{ selectedIds.length }} application(s)
                        </h3>
                        <textarea
                            v-model="bulkRejectReason"
                            rows="3"
                            placeholder="Reason (optional)..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white resize-none mb-4"
                        />
                        <div class="flex justify-end gap-2">
                            <button
                                @click="showBulkRejectModal = false"
                                class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="handleBulkReject"
                                :disabled="bulkLoading"
                                class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-medium transition-colors"
                            >
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCuratedApplicationsAdmin } from '@/composables/useCuratedOnboarding'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import AnimatedButton from '@/components/AnimatedButton.vue'
import { useAdminStore } from '~/stores/admin'

const {
    applications,
    stats,
    loading,
    pagination,
    fetchApplications,
    fetchStats,
    bulkApprove,
    bulkReject
} = useCuratedApplicationsAdmin()

const searchQuery = ref('')
const activeStatus = ref(localStorage.getItem('loops_admin_curon_as') || null)
const verifiedOnly = ref(false)
const sortField = ref('created_at')
const sortDir = ref('desc')
const selectedIds = ref([])
const bulkLoading = ref(false)
const showBulkRejectModal = ref(false)
const bulkRejectReason = ref('')
const currentPage = ref(1)
const router = useRouter()

const adminStore = useAdminStore()
const { config } = storeToRefs(adminStore)

const columns = [
    { key: 'email', label: 'Applicant', sortable: true },
    { key: 'age_at_submission', label: 'Age', sortable: true },
    { key: 'fediverse_account', label: 'Fedi Account', sortable: false },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'email_verified', label: 'Verified', sortable: false },
    { key: 'created_at', label: 'Submitted', sortable: true }
]

const statusCards = computed(() => {
    if (!stats.value) return []
    return [
        { key: null, label: 'Total', count: stats.value.total },
        { key: 'ready', label: 'Ready', count: stats.value.ready },
        { key: 'pending', label: 'Pending', count: stats.value.pending },
        { key: 'approved', label: 'Approved', count: stats.value.approved },
        { key: 'rejected', label: 'Rejected', count: stats.value.rejected },
        { key: 'auto_rejected', label: 'Auto-Rejected', count: stats.value.auto_rejected }
    ]
})

const pendingApplications = computed(() => applications.value.filter((a) => a.status === 'ready'))

const allSelected = computed(
    () =>
        pendingApplications.value.length > 0 &&
        pendingApplications.value.every((a) => selectedIds.value.includes(a.id))
)

const someSelected = computed(() => selectedIds.value.length > 0)

let searchTimeout = null
function debouncedSearch() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        currentPage.value = 1
        loadApplications()
    }, 300)
}

function setStatusFilter(status) {
    if (status) {
        localStorage.setItem('loops_admin_curon_as', status)
    } else {
        localStorage.removeItem('loops_admin_curon_as')
    }
    activeStatus.value = status
    currentPage.value = 1
    selectedIds.value = []
    loadApplications()
}

function toggleSort(field) {
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDir.value = 'desc'
    }
    loadApplications()
}

function toggleSelectAll() {
    if (allSelected.value) {
        selectedIds.value = []
    } else {
        selectedIds.value = pendingApplications.value.map((a) => a.id)
    }
}

function goToPage(page) {
    currentPage.value = page
    loadApplications()
}

function loadApplications() {
    const params = {
        page: currentPage.value,
        sort: sortField.value,
        dir: sortDir.value
    }
    if (activeStatus.value) params.status = activeStatus.value
    if (searchQuery.value) params.q = searchQuery.value
    if (verifiedOnly.value) params.verified_only = true

    fetchApplications(params)
}

async function handleBulkApprove() {
    bulkLoading.value = true
    try {
        await bulkApprove(selectedIds.value)
        selectedIds.value = []
        loadApplications()
        fetchStats()
    } finally {
        bulkLoading.value = false
    }
}

async function handleBulkReject() {
    bulkLoading.value = true
    try {
        await bulkReject(selectedIds.value, bulkRejectReason.value || null)
        selectedIds.value = []
        showBulkRejectModal.value = false
        bulkRejectReason.value = ''
        loadApplications()
        fetchStats()
    } finally {
        bulkLoading.value = false
    }
}

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

onMounted(() => {
    adminStore.fetchConfig()
    loadApplications()
    fetchStats()
})
</script>
