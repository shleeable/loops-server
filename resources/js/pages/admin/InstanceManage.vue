<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage Instances</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Bulk operations and statistics</p>
        </div>

        <div
            class="grid grid-cols-1 gap-px bg-gradient-to-b from-gray-100/50 to-gray-400/80 dark:from-gray-600/50 dark:to-gray-800/70 sm:grid-cols-2 lg:grid-cols-4 rounded-lg border border-gray-300 dark:border-gray-600/70 overflow-hidden mb-6"
        >
            <div
                v-for="stat in stats"
                :key="stat.name"
                class="relative bg-gradient-to-b from-white to-gray-50/80 dark:from-gray-900 dark:to-gray-800/90 px-4 py-3 sm:px-6"
            >
                <p class="text-sm/5 font-medium text-gray-600 dark:text-gray-300">
                    {{ stat.name }}
                </p>
                <p class="mt-1 flex items-baseline gap-x-2">
                    <span
                        class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white"
                        >{{ formatCount(stat.value) }}</span
                    >
                    <span v-if="stat.unit" class="text-sm text-gray-500 dark:text-gray-400">{{
                        stat.unit
                    }}</span>
                </p>
            </div>
        </div>

        <div
            class="mb-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 overflow-hidden"
        >
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Instances by Software
                </h2>
            </div>
            <div class="p-6">
                <div class="relative">
                    <div
                        :class="[
                            'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 transition-all duration-300',
                            !showAllSoftware && 'max-h-[200px] overflow-hidden'
                        ]"
                    >
                        <div
                            v-for="sw in softwareStats"
                            :key="sw.software"
                            class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
                        >
                            <div
                                class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                :title="sw.software"
                            >
                                {{ sw.software || 'Unknown' }}
                            </div>
                            <div class="mt-2 flex items-baseline justify-between gap-2">
                                <router-link
                                    :to="`/admin/instances?q=software:${sw.software || 'unknown'}`"
                                    class="text-2xl font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    {{ formatCount(sw.count) }}
                                </router-link>
                                <router-link
                                    :to="`/admin/instances?q=software:${sw.software || 'unknown'}&sort=allow_video_posts`"
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    {{ sw.allow_video_posts_count }} allowed
                                </router-link>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!showAllSoftware && softwareStats.length > 8"
                        class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white dark:from-gray-800 to-transparent pointer-events-none"
                    ></div>
                </div>

                <div v-if="softwareStats.length > 8" class="mt-4 text-center">
                    <button
                        @click="showAllSoftware = !showAllSoftware"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/50 dark:hover:bg-blue-950 rounded-lg transition-colors"
                    >
                        <span>{{
                            showAllSoftware
                                ? 'Show Less'
                                : `Show All ${softwareStats.length} Software`
                        }}</span>
                        <svg
                            :class="[
                                'w-4 h-4 transition-transform',
                                showAllSoftware && 'rotate-180'
                            ]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700"
            >
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Toggle by Software
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Enable or disable video posts for all instances of a specific software
                    </p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >Software</label
                            >
                            <select
                                v-model="softwareForm.software"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select software...</option>
                                <option
                                    v-for="sw in softwareStats"
                                    :key="sw.software"
                                    :value="sw.software"
                                >
                                    {{ sw.software || 'Unknown' }} ({{ sw.count }})
                                </option>
                            </select>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg"
                        >
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Allow Video Posts</label
                                >
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    Enable video posts for selected software
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="
                                    softwareForm.allow_video_posts = !softwareForm.allow_video_posts
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                    softwareForm.allow_video_posts
                                        ? 'bg-green-600'
                                        : 'bg-gray-200 dark:bg-gray-700'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        softwareForm.allow_video_posts
                                            ? 'translate-x-6'
                                            : 'translate-x-1'
                                    ]"
                                ></span>
                            </button>
                        </div>

                        <AnimatedButton
                            @click="handleSoftwareToggle"
                            :disabled="!softwareForm.software || softwareSubmitting"
                            class="w-full"
                        >
                            <span v-if="!softwareSubmitting"
                                >Apply to All {{ softwareForm.software }} Instances</span
                            >
                            <span v-else class="flex items-center justify-center">
                                <svg
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Processing...
                            </span>
                        </AnimatedButton>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700"
            >
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Toggle by Domains
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Enable or disable video posts for specific domains
                    </p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >Domains</label
                            >
                            <textarea
                                v-model="domainsForm.domains"
                                rows="6"
                                placeholder="example1.com, example2.com&#10;example3.com"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                            ></textarea>
                            <div class="mt-2 flex justify-between items-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Enter domains separated by commas, spaces, or new lines
                                </p>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">{{ domainCount }}</span> domain{{
                                        domainCount !== 1 ? 's' : ''
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg"
                        >
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Allow Video Posts</label
                                >
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    Enable video posts for listed domains
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="
                                    domainsForm.allow_video_posts = !domainsForm.allow_video_posts
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                    domainsForm.allow_video_posts
                                        ? 'bg-green-600'
                                        : 'bg-gray-200 dark:bg-gray-700'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        domainsForm.allow_video_posts
                                            ? 'translate-x-6'
                                            : 'translate-x-1'
                                    ]"
                                ></span>
                            </button>
                        </div>

                        <AnimatedButton
                            @click="handleDomainsToggle"
                            :disabled="domainCount === 0 || domainsSubmitting"
                            class="w-full"
                        >
                            <span v-if="!domainsSubmitting"
                                >Apply to {{ domainCount }} Domain{{
                                    domainCount !== 1 ? 's' : ''
                                }}</span
                            >
                            <span v-else class="flex items-center justify-center">
                                <svg
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Processing...
                            </span>
                        </AnimatedButton>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-50"></div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { instancesApi } from '@/services/adminApi'
import { useUtils } from '@/composables/useUtils'
import { useAlertModal } from '@/composables/useAlertModal.js'

const { formatCount, normalizeDomain } = useUtils()
const { alertModal } = useAlertModal()

const stats = ref([])
const softwareStats = ref([])

const softwareForm = ref({
    software: '',
    allow_video_posts: false
})

const domainsForm = ref({
    domains: '',
    allow_video_posts: false
})

const softwareSubmitting = ref(false)
const domainsSubmitting = ref(false)
const showAllSoftware = ref(false)

const domainCount = computed(() => {
    if (!domainsForm.value.domains) return 0

    const domains = domainsForm.value.domains
        .split(/[\s,;\n]+/)
        .map((d) => d.trim())
        .filter(Boolean)

    return new Set(domains.map(normalizeDomain)).size
})

const fetchStats = async () => {
    try {
        const response = await instancesApi.getManageStats()
        stats.value = response.data.stats
        softwareStats.value = response.data.software_stats
    } catch (error) {
        console.error('Error fetching stats:', error)
        alertModal('Error', 'Failed to load statistics')
    }
}

const handleSoftwareToggle = async () => {
    if (!softwareForm.value.software) return

    softwareSubmitting.value = true

    try {
        const response = await instancesApi.toggleBySoftware({
            software: softwareForm.value.software,
            allow_video_posts: softwareForm.value.allow_video_posts
        })

        alertModal('Success', `Updated ${response.data.updated_count} instance(s)`)
        await fetchStats()
        softwareForm.value.software = ''
        softwareForm.value.allow_video_posts = false
    } catch (error) {
        console.error('Error toggling by software:', error)
        alertModal('Error', error?.response?.data?.message || 'Failed to update instances')
    } finally {
        softwareSubmitting.value = false
    }
}

const handleDomainsToggle = async () => {
    if (domainCount.value === 0) return

    domainsSubmitting.value = true

    try {
        const domains = domainsForm.value.domains
            .split(/[\s,;\n]+/)
            .map((d) => d.trim())
            .filter(Boolean)
            .map(normalizeDomain)

        const uniqueDomains = [...new Set(domains)]

        const response = await instancesApi.toggleByDomains({
            domains: uniqueDomains,
            allow_video_posts: domainsForm.value.allow_video_posts
        })

        alertModal('Success', `Updated ${response.data.updated_count} instance(s)`)
        await fetchStats()
        domainsForm.value.domains = ''
        domainsForm.value.allow_video_posts = false
    } catch (error) {
        console.error('Error toggling by domains:', error)
        alertModal('Error', error?.response?.data?.message || 'Failed to update instances')
    } finally {
        domainsSubmitting.value = false
    }
}

onMounted(() => {
    fetchStats()
})
</script>
