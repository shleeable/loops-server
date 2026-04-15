<template>
    <StudioLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
            <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
                <div
                    class="relative overflow-hidden border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-2xl"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-purple-500/5 dark:from-indigo-500/10 dark:to-purple-500/10 pointer-events-none"
                    ></div>
                    <div class="relative p-6">
                        <div class="flex items-center gap-5">
                            <div class="relative">
                                <div
                                    class="absolute -inset-1 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full opacity-20 blur-sm"
                                ></div>
                                <img
                                    :src="user.avatar"
                                    class="relative w-20 h-20 rounded-full border-2 border-white dark:border-gray-800 shadow-sm object-cover"
                                    @error="$event.target.src = '/storage/avatars/default.jpg'"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-2.5">
                                    <h1
                                        class="text-xl font-bold text-gray-900 dark:text-white tracking-tight"
                                    >
                                        {{ user.username }}
                                    </h1>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                                    >
                                        Creator
                                    </span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div
                                        v-for="(stat, index) in userStats"
                                        :key="stat.label"
                                        class="flex items-center gap-1.5"
                                    >
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums"
                                        >
                                            {{ formatCount(stat.value) }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ stat.label }}
                                        </span>
                                        <span
                                            v-if="index < userStats.length - 1"
                                            class="text-gray-300 dark:text-gray-700 ml-2"
                                            >·</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div
                        class="border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-2xl p-6"
                    >
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    New Followers
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Last 30 days
                                </p>
                            </div>
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10"
                            >
                                <ArrowUpIcon
                                    class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                                />

                                <span
                                    class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                                    >{{ followerGrowth }}%</span
                                >
                            </div>
                        </div>
                        <div ref="followersChartRef" class="w-full h-64"></div>
                        <div
                            class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between"
                        >
                            <span class="text-xs text-gray-500 dark:text-gray-400"
                                >Total new followers</span
                            >
                            <span
                                class="text-sm font-bold text-gray-900 dark:text-white tabular-nums"
                                >{{ totalNewFollowers.toLocaleString() }}</span
                            >
                        </div>
                    </div>

                    <div
                        class="border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-2xl p-6"
                    >
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Video Views
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Last 30 days
                                </p>
                            </div>
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10"
                            >
                                <EyeIcon class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                <span
                                    class="text-xs font-semibold text-indigo-600 dark:text-indigo-400"
                                    >{{ totalVideoViews.toLocaleString() }}</span
                                >
                            </div>
                        </div>
                        <div ref="viewsChartRef" class="w-full h-64"></div>
                        <div
                            class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between"
                        >
                            <span class="text-xs text-gray-500 dark:text-gray-400"
                                >Avg. daily views</span
                            >
                            <span
                                class="text-sm font-bold text-gray-900 dark:text-white tabular-nums"
                                >{{ avgDailyViews.toLocaleString() }}</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    class="border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-2xl overflow-hidden"
                >
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Profile Links
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Click-through performance
                                </p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                                {{ totalLinkClicks.toLocaleString() }} total clicks
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                    >
                                        Link URL
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                    >
                                        Clicks
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                    >
                                        % of Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="link in profileLinks"
                                    :key="link.url"
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                                            >
                                                <svg
                                                    class="w-4 h-4 text-gray-400 dark:text-gray-500"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                                                    />
                                                </svg>
                                            </div>
                                            <a
                                                :href="link.url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-sm text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate max-w-md"
                                            >
                                                {{ link.url }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums"
                                        >
                                            {{ link.clicks.toLocaleString() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div
                                                class="w-16 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full bg-indigo-500 dark:bg-indigo-400"
                                                    :style="{
                                                        width: linkPercentage(link.clicks) + '%'
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400 tabular-nums w-10 text-right"
                                            >
                                                {{ linkPercentage(link.clicks) }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!profileLinks.length" class="px-6 py-12 text-center">
                        <svg
                            class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                            />
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No profile links added yet
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </StudioLayout>
</template>

<script setup>
import { ref, reactive, computed, nextTick, watch, inject, onMounted, onBeforeUnmount } from 'vue'
import * as echarts from 'echarts'
import { useUtils } from '@/composables/useUtils'
import { useApiClient } from '@/composables/useApiClient'
import { ArrowUpIcon, EyeIcon } from '@heroicons/vue/24/outline'

const authStore = inject('authStore')
const { formatCount, formatDate } = useUtils()
const api = useApiClient()

const user = computed(() => authStore.getUser)

const userStats = computed(() => [
    { label: 'Posts', value: user.value.post_count },
    { label: 'Likes', value: user.value.likes_count },
    { label: 'Followers', value: user.value.follower_count },
    { label: 'Following', value: user.value.following_count }
])

const loading = ref(true)
const chartDates = ref([])
const followersData = ref([])
const viewsData = ref([])

const totalNewFollowers = computed(() => followersData.value.reduce((a, b) => a + b, 0))
const totalVideoViews = computed(() => viewsData.value.reduce((a, b) => a + b, 0))
const avgDailyViews = computed(() => Math.round(totalVideoViews.value / 30))
const followerGrowth = computed(() => {
    const first = followersData.value.slice(0, 15).reduce((a, b) => a + b, 0)
    const second = followersData.value.slice(15).reduce((a, b) => a + b, 0)
    return first > 0 ? Math.round(((second - first) / first) * 100) : 0
})

function formatDateLabel(dateStr) {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

async function fetchAnalytics() {
    loading.value = true
    try {
        const [followersRes, viewsRes, linksRes] = await Promise.all([
            api.get('/api/v1/studio/analytics/followers'),
            api.get('/api/v1/studio/analytics/views'),
            api.get('/api/v1/studio/analytics/links')
        ])

        const followers = followersRes.data
        const views = viewsRes.data
        const links = linksRes.data

        chartDates.value = followers.data.map((d) => formatDateLabel(d.date))
        followersData.value = followers.data.map((d) => d.count)
        viewsData.value = views.data.map((d) => d.views)
        profileLinks.value = links.data
    } catch (e) {
        console.error('Failed to fetch analytics:', e)
    } finally {
        loading.value = false
    }
}
const profileLinks = ref([])

const totalLinkClicks = computed(() => profileLinks.value.reduce((a, b) => a + b.clicks, 0))

function linkPercentage(clicks) {
    if (!totalLinkClicks.value) return 0
    return Math.round((clicks / totalLinkClicks.value) * 100)
}

const followersChartRef = ref(null)
const viewsChartRef = ref(null)
let followersChart = null
let viewsChart = null

const isDark = computed(() => document.documentElement.classList.contains('dark'))

function getChartColors() {
    const dark = isDark.value
    return {
        textColor: dark ? '#9ca3af' : '#6b7280',
        borderColor: dark ? '#1f2937' : '#f3f4f6',
        bgGradientStart: dark ? 'rgba(99,102,241,0.25)' : 'rgba(99,102,241,0.12)',
        bgGradientEnd: 'rgba(99,102,241,0)'
    }
}

function buildAreaOption(data, color, gradientStart, gradientEnd, colors) {
    return {
        grid: {
            top: 10,
            right: 12,
            bottom: 24,
            left: 40
        },
        tooltip: {
            trigger: 'axis',
            backgroundColor: isDark.value ? '#1f2937' : '#ffffff',
            borderColor: isDark.value ? '#374151' : '#e5e7eb',
            textStyle: {
                color: isDark.value ? '#f3f4f6' : '#111827',
                fontSize: 12
            },
            axisPointer: {
                type: 'cross',
                crossStyle: { color: colors.textColor }
            }
        },
        xAxis: {
            type: 'category',
            data: chartDates.value,
            boundaryGap: false,
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: {
                color: colors.textColor,
                fontSize: 10,
                interval: 6
            },
            splitLine: { show: false }
        },
        yAxis: {
            type: 'value',
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: {
                color: colors.textColor,
                fontSize: 10
            },
            splitLine: {
                lineStyle: {
                    color: colors.borderColor,
                    type: 'dashed'
                }
            }
        },
        series: [
            {
                type: 'line',
                data: data,
                smooth: 0.4,
                symbol: 'none',
                lineStyle: {
                    color: color,
                    width: 2.5
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: gradientStart },
                        { offset: 1, color: gradientEnd }
                    ])
                }
            }
        ]
    }
}

function initCharts() {
    const colors = getChartColors()

    if (followersChartRef.value) {
        followersChart = echarts.init(followersChartRef.value)
        followersChart.setOption(
            buildAreaOption(
                followersData.value,
                '#10b981',
                isDark.value ? 'rgba(16,185,129,0.25)' : 'rgba(16,185,129,0.12)',
                'rgba(16,185,129,0)',
                colors
            )
        )
    }

    if (viewsChartRef.value) {
        viewsChart = echarts.init(viewsChartRef.value)
        viewsChart.setOption(
            buildAreaOption(
                viewsData.value,
                '#6366f1',
                isDark.value ? 'rgba(99,102,241,0.25)' : 'rgba(99,102,241,0.12)',
                'rgba(99,102,241,0)',
                colors
            )
        )
    }
}

function handleResize() {
    followersChart?.resize()
    viewsChart?.resize()
}

onMounted(async () => {
    await fetchAnalytics()
    nextTick(() => {
        initCharts()
    })
    window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize)
    followersChart?.dispose()
    viewsChart?.dispose()
})

const darkModeObserver = ref(null)
onMounted(() => {
    darkModeObserver.value = new MutationObserver(() => {
        followersChart?.dispose()
        viewsChart?.dispose()
        nextTick(() => initCharts())
    })
    darkModeObserver.value.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    })
})
onBeforeUnmount(() => {
    darkModeObserver.value?.disconnect()
})
</script>
