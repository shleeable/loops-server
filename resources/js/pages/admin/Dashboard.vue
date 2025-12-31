<template>
    <div class="space-y-6">
        <div class="flex items-center justify-center lg:justify-between flex-col xl:flex-row">
            <div class="flex justify-center items-center xl:items-start flex-col mb-3 xl:mb-0">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400">Server overview and analytics.</p>
            </div>

            <div
                v-if="dashboardData?.cached_at"
                class="text-xs text-gray-400 mb-3 xl:mb-0 flex gap-1"
            >
                <span>Data Updated: {{ formatTimeAgo(dashboardData?.cached_at) }}</span>
            </div>

            <div class="flex flex-col lg:flex-row gap-2">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 lg:pr-4">
                        <span class="text-sm font-light text-gray-500">Select period: </span>
                        <div
                            class="flex bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-gray-700 shadow rounded-lg p-2 gap-1"
                        >
                            <button
                                v-for="filter in filterOptions"
                                :key="filter"
                                @click="activeFilter = filter"
                                :class="[
                                    'px-5 py-1.5 text-[10px] lg:text-[16px] font-medium rounded-md transition-all duration-200 relative cursor-pointer',
                                    activeFilter === filter
                                        ? 'bg-white dark:bg-slate-700 text-black dark:text-white shadow-sm'
                                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:bg-gray-700 dark:hover:text-gray-200'
                                ]"
                            >
                                {{ filter }}
                            </button>
                        </div>
                    </div>
                </div>

                <AnimatedButton
                    type="button"
                    :loading="loading"
                    variant="primary"
                    @click="refreshDashboard"
                    title="Refresh stats"
                >
                    <div class="flex items-center font-bold gap-2">
                        <span class="bx bx-refresh text-[25px]"></span> Refresh
                    </div>
                </AnimatedButton>
            </div>
        </div>

        <div v-if="loading && !dashboardData" class="flex justify-center items-center py-20">
            <Spinner />
        </div>

        <div v-else class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <MetricCard
                    title="Total Users"
                    :value="dashboardData.metrics.total_users"
                    :change="dashboardData.metrics.users_change"
                    :icon="UserGroupIcon"
                    color="blue"
                />

                <MetricCard
                    title="Total Videos"
                    :value="dashboardData.metrics.total_videos"
                    :change="dashboardData.metrics.videos_change"
                    :icon="VideoCameraIcon"
                    color="yellow"
                />

                <MetricCard
                    title="Engagement Rate"
                    :value="`${dashboardData.metrics.engagement_rate}%`"
                    :change="dashboardData.metrics.engagement_change"
                    :icon="ChartBarIcon"
                    color="pink"
                />

                <MetricCard
                    title="Federated Instances"
                    :value="dashboardData.metrics.active_instances"
                    :change="dashboardData.metrics.instances_change"
                    :icon="GlobeAltIcon"
                    color="green"
                />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <MiniMetric label="Active Today" :value="dashboardData.metrics.active_today" />
                <MiniMetric
                    label="Pending Reports"
                    :value="dashboardData.metrics.pending_reports"
                />
                <MiniMetric
                    label="Comments"
                    :value="formatCount(dashboardData.metrics.total_comments)"
                />
                <MiniMetric label="Likes" :value="formatCount(dashboardData.metrics.total_likes)" />
                <MiniMetric
                    label="Shares"
                    :value="formatCount(dashboardData.metrics.total_shares)"
                />
                <MiniMetric
                    label="Followers"
                    :value="formatCount(dashboardData.metrics.total_follows)"
                />
                <MiniMetric
                    label="Hashtags"
                    :value="formatCount(dashboardData.metrics.total_hashtags)"
                />
                <MiniMetric
                    v-if="dashboardData.metrics.for_you.total_views"
                    label="For You Total Views"
                    :value="formatCount(dashboardData.metrics.for_you.total_views)"
                />
                <MiniMetric
                    v-if="dashboardData.metrics.for_you.total_views"
                    label="For You Watch Time"
                    :value="dashboardData.metrics.for_you.total_watch_time"
                />
                <MiniMetric label="Storage Used" :value="dashboardData.metrics.storage_used" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <ChartCard :title="`Active Users (${dashboardData.period})`">
                    <div ref="activeUsersChart" class="h-80"></div>
                </ChartCard>

                <ChartCard :title="`User Growth (${dashboardData.period})`">
                    <div ref="userGrowthChart" class="h-80"></div>
                </ChartCard>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <ChartCard title="Engagement Metrics (7 Days)">
                    <div ref="engagementChart" class="h-120"></div>
                </ChartCard>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <ChartCard :title="`Video Uploads (${dashboardData.period})`">
                    <div ref="videoUploadsChart" class="h-80"></div>
                </ChartCard>

                <ChartCard title="Content Distribution">
                    <div ref="contentDistChart" class="h-80"></div>
                </ChartCard>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Trending Hashtags
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(tag, index) in dashboardData.top_hashtags"
                            :key="tag.id"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3">
                                <router-link
                                    :to="`/admin/hashtags?id=${tag.id}`"
                                    class="text-sm font-bold text-gray-500 dark:text-gray-400 w-6"
                                >
                                    #{{ index + 1 }}
                                </router-link>
                                <router-link
                                    :to="`/tag/${tag.name}`"
                                    class="text-blue-500 dark:text-white font-medium"
                                >
                                    #{{ tag.name }}
                                </router-link>
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ formatCount(tag.count) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Federation Overview
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Total Instances</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ dashboardData.federation.total_instances }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Remote Profiles</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCount(dashboardData.federation.remote_profiles) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Remote Videos</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCount(dashboardData.federation.remote_videos) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Blocked Instances</span>
                            <span class="text-xl font-bold text-red-600 dark:text-red-400">
                                {{ dashboardData.federation.blocked_instances }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Remote Comments</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCount(dashboardData.federation.remote_comments) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Remote Replies</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCount(dashboardData.federation.remote_replies) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Remote Followers</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCount(dashboardData.federation.remote_followers) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Remote Following</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCount(dashboardData.federation.remote_following) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Moderation Queue
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Pending Reports</span>
                            <span class="text-xl font-bold text-orange-600 dark:text-orange-400">
                                {{ dashboardData.moderation.pending_reports }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Suspended Users</span>
                            <span class="text-xl font-bold text-red-600 dark:text-red-400">
                                {{ dashboardData.moderation.suspended_users }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Total Reports (30d)</span
                            >
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ dashboardData.moderation.total_reports_30d }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Recent Activity
                </h3>
                <div class="space-y-3">
                    <div
                        v-for="activity in dashboardData.recent_activity"
                        :key="activity.id"
                        class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="pr-3 text-gray-300 dark:text-gray-600">
                                <div v-if="activity.icon === 'new_user'">
                                    <UserIcon class="w-8 h-8" />
                                </div>
                                <div v-else-if="activity.icon === 'new_video'">
                                    <VideoCameraIcon class="w-8 h-8" />
                                </div>
                                <div v-else-if="activity.icon === 'new_report'">
                                    <ExclamationTriangleIcon class="w-8 h-8" />
                                </div>
                            </div>
                            <div>
                                <p class="text-gray-900 dark:text-white">
                                    {{ activity.description }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(activity.timestamp) }}
                                </p>
                            </div>
                        </div>
                        <router-link
                            v-if="activity.link"
                            :to="activity.link"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm"
                        >
                            View →
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    ChartBarSquareIcon,
    Cog6ToothIcon,
    ChatBubbleOvalLeftIcon,
    ChatBubbleLeftRightIcon,
    ChartBarIcon,
    HashtagIcon,
    GlobeAltIcon,
    ExclamationTriangleIcon,
    UserGroupIcon,
    UserIcon,
    VideoCameraIcon,
    ServerStackIcon,
    HomeIcon,
    SunIcon,
    MoonIcon
} from '@heroicons/vue/24/outline'

import { ref, onMounted, onUnmounted, nextTick, watch, computed } from 'vue'
import * as echarts from 'echarts'
import { storeToRefs } from 'pinia'
import { dashboardApi } from '@/services/adminApi'
import { useAlertModal } from '@/composables/useAlertModal.js'
import { useUtils } from '@/composables/useUtils'
import { useAdminStore } from '~/stores/admin'

const { formatDate, formatCount, formatTimeAgo } = useUtils()
const { alertModal } = useAlertModal()
const adminStore = useAdminStore()
const { isDarkMode } = storeToRefs(adminStore)

const loading = ref(true)
const dashboardData = ref(null)
const shouldRefresh = ref(false)

const activeFilter = ref('30d')

const filterOptions = ['30d', '60d', '90d', '365d']

const activeUsersChart = ref(null)
const userGrowthChart = ref(null)
const videoUploadsChart = ref(null)
const engagementChart = ref(null)
const contentDistChart = ref(null)

let activeUsersInstance = null
let userGrowthInstance = null
let videoUploadsInstance = null
let engagementInstance = null
let contentDistInstance = null

const axisLineColor = computed(() => {
    return isDarkMode.value ? '#333' : '#ccc'
})

const fetchDashboardData = async () => {
    loading.value = true
    try {
        const response = await dashboardApi.getDashboardStats(
            activeFilter.value,
            shouldRefresh.value
        )
        dashboardData.value = response.data

        await nextTick()
        shouldRefresh.value = false
        initializeCharts()
    } catch (error) {
        alertModal('Error fetching dashboard data', error.message)
    } finally {
        loading.value = false
    }
}

const initializeCharts = () => {
    if (!dashboardData.value) return
    ;[
        userGrowthInstance,
        videoUploadsInstance,
        engagementInstance,
        contentDistInstance,
        activeUsersInstance
    ].forEach((instance) => instance?.dispose())

    initUserGrowthChart()
    initActiveUsersChart()
    initVideoUploadsChart()
    initEngagementChart()
    initContentDistChart()
}

const initActiveUsersChart = () => {
    if (!activeUsersChart.value) return

    activeUsersInstance = echarts.init(activeUsersChart.value, isDarkMode.value ? 'dark' : 'light')
    const data = dashboardData.value.charts.active_users

    const option = {
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.dates,
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' }
        },
        yAxis: {
            type: 'value',
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' },
            splitLine: { lineStyle: { color: axisLineColor.value } }
        },
        series: [
            {
                name: 'Active Users',
                type: 'line',
                smooth: true,
                data: data.values,
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(59, 130, 246, 0.5)' },
                        { offset: 1, color: 'rgba(59, 130, 246, 0.1)' }
                    ])
                },
                lineStyle: { color: '#3b82f6', width: 2 },
                itemStyle: { color: '#3b82f6' }
            }
        ]
    }

    activeUsersInstance.setOption(option)
}

const initUserGrowthChart = () => {
    if (!userGrowthChart.value) return

    userGrowthInstance = echarts.init(userGrowthChart.value, isDarkMode.value ? 'dark' : 'light')
    const data = dashboardData.value.charts.user_growth

    const option = {
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.dates,
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' }
        },
        yAxis: {
            type: 'value',
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' },
            splitLine: { lineStyle: { color: axisLineColor.value } }
        },
        series: [
            {
                name: 'New Users',
                type: 'line',
                smooth: true,
                data: data.values,
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(59, 130, 246, 0.5)' },
                        { offset: 1, color: 'rgba(59, 130, 246, 0.1)' }
                    ])
                },
                lineStyle: { color: '#3b82f6', width: 2 },
                itemStyle: { color: '#3b82f6' }
            }
        ]
    }

    userGrowthInstance.setOption(option)
}

const initVideoUploadsChart = () => {
    if (!videoUploadsChart.value) return

    videoUploadsInstance = echarts.init(
        videoUploadsChart.value,
        isDarkMode.value ? 'dark' : 'light'
    )
    const data = dashboardData.value.charts.video_uploads

    const option = {
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.dates,
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' }
        },
        yAxis: {
            type: 'value',
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' },
            splitLine: { lineStyle: { color: axisLineColor.value } }
        },
        series: [
            {
                name: 'Videos',
                type: 'bar',
                data: data.values,
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: '#a855f7' },
                        { offset: 1, color: '#7c3aed' }
                    ])
                }
            }
        ]
    }

    videoUploadsInstance.setOption(option)
}

const initEngagementChart = () => {
    if (!engagementChart.value) return

    engagementInstance = echarts.init(engagementChart.value, isDarkMode.value ? 'dark' : 'light')
    const data = dashboardData.value.charts.engagement

    const option = {
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        legend: {
            data: [
                'Likes',
                'Comments',
                'Replies',
                'Reports',
                'Bookmarks',
                'Shares',
                'Comment Likes',
                'Reply Likes',
                'Comment Shares',
                'Reply Shares'
            ],
            textStyle: { color: '#999' },
            bottom: '0%',
            type: 'scroll'
        },
        emphasis: {
            focus: 'series',
            lineStyle: { width: 5 }
        },
        grid: {
            top: '5%',
            left: '3%',
            right: '4%',
            bottom: '15%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.dates,
            boundaryGap: false,
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' }
        },
        yAxis: {
            min:
                Math.min(
                    ...Object.values(data)
                        .flat()
                        .filter((v) => v > 0)
                ) || 1,
            type: 'log',
            logBase: 10,
            axisLine: { lineStyle: { color: axisLineColor.value } },
            axisLabel: { color: '#999' },
            splitLine: { lineStyle: { color: axisLineColor.value } }
        },
        series: [
            {
                name: 'Likes',
                type: 'line',
                smooth: true,
                data: data.likes,
                lineStyle: { color: '#ec4899', width: 5 },
                itemStyle: { color: '#ec4899' }
            },
            {
                name: 'Comments',
                type: 'line',
                smooth: true,
                data: data.comments,
                lineStyle: { color: '#3b82f6', width: 4 },
                itemStyle: { color: '#3b82f6' }
            },
            {
                name: 'Replies',
                type: 'line',
                smooth: true,
                data: data.replies,
                lineStyle: { color: '#f59e0b', width: 4 },
                itemStyle: { color: '#f59e0b' }
            },
            {
                name: 'Reports',
                type: 'line',
                smooth: false,
                data: data.reports,
                lineStyle: { color: '#f43f5e', width: 4 },
                itemStyle: { color: '#f43f5e' }
            },
            {
                name: 'Bookmarks',
                type: 'line',
                smooth: true,
                data: data.bookmarks,
                lineStyle: { color: '#a855f7', width: 4 },
                itemStyle: { color: '#a855f7' }
            },
            {
                name: 'Shares',
                type: 'line',
                smooth: false,
                data: data.shares,
                lineStyle: { color: '#10b981', type: 'dashed' },
                itemStyle: { color: '#10b981' }
            },
            {
                name: 'Comment Likes',
                type: 'line',
                smooth: false,
                data: data.commentLikes,
                lineStyle: { color: '#8b5cf6', type: 'dashed' },
                itemStyle: { color: '#8b5cf6' }
            },
            {
                name: 'Reply Likes',
                type: 'line',
                smooth: false,
                data: data.replyLikes,
                lineStyle: { color: '#ef4444', type: 'dashed' },
                itemStyle: { color: '#ef4444' }
            },
            {
                name: 'Comment Shares',
                type: 'line',
                smooth: false,
                data: data.commentShares,
                lineStyle: { color: '#06b6d4', type: 'dotted' },
                itemStyle: { color: '#06b6d4' }
            },
            {
                name: 'Reply Shares',
                type: 'line',
                smooth: false,
                data: data.replyShares,
                lineStyle: { color: '#eab308', type: 'dotted' },
                itemStyle: { color: '#eab308' }
            }
        ]
    }

    engagementInstance.setOption(option)
}

const initContentDistChart = () => {
    if (!contentDistChart.value) return

    contentDistInstance = echarts.init(contentDistChart.value, isDarkMode.value ? 'dark' : 'light')
    const data = dashboardData.value.charts.content_distribution

    const option = {
        tooltip: {
            trigger: 'item',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            textStyle: { color: '#999' }
        },
        series: [
            {
                name: 'Content',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: isDarkMode.value ? '#1f2937' : '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold',
                        color: isDarkMode.value ? '#fff' : '#000'
                    }
                },
                labelLine: {
                    show: false
                },
                data: data
            }
        ]
    }

    contentDistInstance.setOption(option)
}

const refreshDashboard = async () => {
    shouldRefresh.value = true
    await nextTick()
    fetchDashboardData()
}

const handleResize = () => {
    ;[userGrowthInstance, videoUploadsInstance, engagementInstance, contentDistInstance].forEach(
        (instance) => instance?.resize()
    )
}

watch(activeFilter, (newVal, oldVal) => {
    fetchDashboardData()
})

watch(isDarkMode, (newVal, oldVal) => {
    fetchDashboardData()
})

onMounted(() => {
    fetchDashboardData()
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    ;[userGrowthInstance, videoUploadsInstance, engagementInstance, contentDistInstance].forEach(
        (instance) => instance?.dispose()
    )
    window.removeEventListener('resize', handleResize)
})
</script>
