<template>
    <StudioLayout>
        <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-950">
            <div class="container max-w-7xl mx-auto p-8">
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Scheduled
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Loops waiting to publish. Nobody can see them until their time arrives.
                        </p>
                    </div>
                </div>

                <div v-if="isLoading" class="space-y-3">
                    <div
                        v-for="n in 3"
                        :key="n"
                        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex gap-4"
                    >
                        <div
                            class="w-16 h-28 rounded-md bg-gray-200 dark:bg-gray-700 animate-pulse"
                        ></div>
                        <div class="flex-1 space-y-3 py-1">
                            <div
                                class="h-4 w-1/3 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"
                            ></div>
                            <div
                                class="h-3 w-2/3 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"
                            ></div>
                            <div
                                class="h-3 w-1/4 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"
                            ></div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="!items.length"
                    class="bg-white dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center"
                >
                    <svg
                        class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Nothing scheduled
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Turn on Schedule when you upload to queue a loop for later.
                    </p>
                    <AnimatedButton
                        variant="primaryGradient"
                        pill
                        @click="router.push('/studio/upload')"
                    >
                        Upload a Loop
                    </AnimatedButton>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                    >
                        <div class="flex gap-4">
                            <div
                                class="w-16 h-28 rounded-md bg-gray-200 dark:bg-gray-700 overflow-hidden flex-shrink-0"
                            >
                                <img
                                    v-if="thumbnailFor(item)"
                                    :src="thumbnailFor(item)"
                                    alt=""
                                    class="w-full h-full object-cover"
                                />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2"
                                    >
                                        {{ captionFor(item) }}
                                    </p>
                                    <span
                                        :class="[
                                            'flex-shrink-0 px-2 py-0.5 rounded-full text-[11px] font-medium border',
                                            statusFor(item).classes
                                        ]"
                                    >
                                        {{ statusFor(item).label }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                                    {{ absoluteTime(item.scheduled_at) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ relativeTime(item.scheduled_at) }} in {{ timezoneLabel }}
                                </p>

                                <p
                                    v-if="item.publish_failure_reason"
                                    class="text-xs text-red-500 mt-2"
                                >
                                    {{ item.publish_failure_reason }}
                                </p>

                                <div class="flex flex-wrap gap-2 mt-4">
                                    <button
                                        v-if="canPublishNow(item)"
                                        @click="publishNow(item)"
                                        :disabled="busyId === item.id"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md border border-[#F02C56] text-[#F02C56] hover:bg-[#F02C56]/10 transition-colors cursor-pointer dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Publish now
                                    </button>
                                    <button
                                        v-if="statusFor(item).key !== 'failed'"
                                        @click="openReschedule(item)"
                                        :disabled="busyId === item.id"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Reschedule
                                    </button>
                                    <button
                                        @click="remove(item)"
                                        :disabled="busyId === item.id"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="nextCursor" class="pt-4 text-center">
                        <button
                            @click="loadMore"
                            :disabled="isLoadingMore"
                            class="px-5 py-2.5 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer disabled:opacity-50"
                        >
                            {{ isLoadingMore ? 'Loading...' : 'Load more' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </StudioLayout>

    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="rescheduleItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                @click.self="closeReschedule"
            >
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-md p-6"
                >
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Reschedule</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 mb-5">
                        Pick a new time to publish this loop.
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                                >Date</label
                            >
                            <input
                                v-model="rescheduleDay"
                                type="date"
                                :min="minDayInput"
                                :max="maxDayInput"
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                                >Time</label
                            >
                            <input
                                v-model="rescheduleTime"
                                type="time"
                                step="900"
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <p v-if="rescheduleError" class="text-sm text-red-500 mt-3">
                        {{ rescheduleError }}
                    </p>
                    <p
                        v-else-if="rescheduleDateTime"
                        class="text-xs text-gray-500 dark:text-gray-400 mt-3"
                    >
                        Publishes {{ relativeTime(rescheduleDateTime) }} in {{ timezoneLabel }}
                    </p>

                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            @click="closeReschedule"
                            class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitReschedule"
                            :disabled="!canReschedule || isRescheduling"
                            class="px-4 py-2 text-sm font-medium rounded-md text-white transition-colors"
                            :class="
                                canReschedule && !isRescheduling
                                    ? 'bg-[#F02C56] hover:bg-[#F02C56]/80 cursor-pointer'
                                    : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                            "
                        >
                            {{ isRescheduling ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
<script setup>
import { ref, computed, inject, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAlertModal } from '@/composables/useAlertModal.js'
import AnimatedButton from '@/components/AnimatedButton.vue'

const router = useRouter()
const axios = inject('axios')
const { alertModal, confirmModal } = useAlertModal()

const MIN_LEAD_MS = 15 * 60 * 1000
const MAX_HORIZON_MS = 90 * 24 * 60 * 60 * 1000

const items = ref([])
const nextCursor = ref(null)
const isLoading = ref(true)
const isLoadingMore = ref(false)
const busyId = ref(null)
const now = ref(Date.now())

const rescheduleItem = ref(null)
const rescheduleDay = ref('')
const rescheduleTime = ref('')
const isRescheduling = ref(false)

let tickTimer = null
let lastRefreshAt = 0

const padTwo = (value) => String(value).padStart(2, '0')

const toDayValue = (date) =>
    `${date.getFullYear()}-${padTwo(date.getMonth() + 1)}-${padTwo(date.getDate())}`

const toTimeValue = (date) => `${padTwo(date.getHours())}:${padTwo(date.getMinutes())}`

const timezoneLabel = computed(() => {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone
    } catch (e) {
        return 'your local time'
    }
})

const minDayInput = computed(() => toDayValue(new Date(now.value + MIN_LEAD_MS)))

const maxDayInput = computed(() => toDayValue(new Date(now.value + MAX_HORIZON_MS)))

const absoluteTime = (value) => {
    if (!value) return ''

    return new Date(value).toLocaleString(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    })
}

const relativeTime = (value) => {
    if (!value) return ''

    const target = value instanceof Date ? value : new Date(value)
    const formatter = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' })
    const minutes = Math.round((target.getTime() - now.value) / 60000)

    if (Math.abs(minutes) < 60) {
        return formatter.format(minutes, 'minute')
    }

    const hours = Math.round(minutes / 60)

    if (Math.abs(hours) < 48) {
        return formatter.format(hours, 'hour')
    }

    return formatter.format(Math.round(hours / 24), 'day')
}

const captionFor = (item) => {
    const text = item.description || item.caption

    return text && text.trim() ? text.trim() : 'No caption'
}

const thumbnailFor = (item) => item.thumbnail || item.media?.thumbnail || null

const statusFor = (item) => {
    if (item.publish_state === 3) {
        return {
            key: 'failed',
            label: 'Failed',
            classes:
                'border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400'
        }
    }

    if (new Date(item.scheduled_at).getTime() <= now.value) {
        return {
            key: 'publishing',
            label: 'Publishing',
            classes:
                'border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400'
        }
    }

    return {
        key: 'scheduled',
        label: 'Scheduled',
        classes:
            'border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300'
    }
}

const canPublishNow = (item) => statusFor(item).key === 'scheduled'

const rescheduleDateTime = computed(() => {
    if (!rescheduleDay.value || !rescheduleTime.value) return null

    const parsed = new Date(`${rescheduleDay.value}T${rescheduleTime.value}`)

    return isNaN(parsed.getTime()) ? null : parsed
})

const rescheduleError = computed(() => {
    if (!rescheduleDay.value || !rescheduleTime.value) return null

    const date = rescheduleDateTime.value

    if (!date) {
        return 'Enter a valid date and time.'
    }

    if (date.getTime() < Date.now() + MIN_LEAD_MS) {
        return 'Pick a time at least 15 minutes from now.'
    }

    if (date.getTime() > Date.now() + MAX_HORIZON_MS) {
        return 'Pick a time within the next 90 days.'
    }

    return null
})

const canReschedule = computed(() => !!rescheduleDateTime.value && !rescheduleError.value)

const readRows = (payload) => {
    if (Array.isArray(payload)) return payload

    return payload?.data || []
}

const readCursor = (payload) => payload?.meta?.next_cursor ?? payload?.next_cursor ?? null

const fetchScheduled = async (cursor = null) => {
    const response = await axios.get('/api/v1/studio/scheduled', {
        params: cursor ? { cursor } : {}
    })

    return response.data
}

const load = async () => {
    try {
        const payload = await fetchScheduled()

        items.value = readRows(payload)
        nextCursor.value = readCursor(payload)
    } catch (error) {
        console.error('Error loading scheduled posts:', error)
        await alertModal(
            'Could not load',
            '<p class="text-gray-700">We could not load your scheduled loops. Please refresh and try again.</p>'
        )
    } finally {
        isLoading.value = false
    }
}

const loadMore = async () => {
    if (!nextCursor.value || isLoadingMore.value) return

    isLoadingMore.value = true

    try {
        const payload = await fetchScheduled(nextCursor.value)

        items.value = [...items.value, ...readRows(payload)]
        nextCursor.value = readCursor(payload)
    } catch (error) {
        console.error('Error loading more scheduled posts:', error)
    } finally {
        isLoadingMore.value = false
    }
}

const refreshQuietly = async () => {
    if (Date.now() - lastRefreshAt < 60000) return

    lastRefreshAt = Date.now()

    try {
        const payload = await fetchScheduled()

        items.value = readRows(payload)
        nextCursor.value = readCursor(payload)
    } catch (error) {
        console.error('Error refreshing scheduled posts:', error)
    }
}

const openReschedule = (item) => {
    const current = new Date(item.scheduled_at)

    rescheduleItem.value = item
    rescheduleDay.value = toDayValue(current)
    rescheduleTime.value = toTimeValue(current)
}

const closeReschedule = () => {
    rescheduleItem.value = null
    rescheduleDay.value = ''
    rescheduleTime.value = ''
}

const submitReschedule = async () => {
    if (!canReschedule.value || isRescheduling.value) return

    const item = rescheduleItem.value
    isRescheduling.value = true

    try {
        const response = await axios.post(`/api/v1/video/schedule/${item.id}`, {
            scheduled_at: rescheduleDateTime.value.toISOString()
        })

        const updated = response.data?.data || response.data
        const index = items.value.findIndex((row) => row.id === item.id)

        if (index !== -1 && updated?.scheduled_at) {
            items.value[index] = { ...items.value[index], ...updated }
        }

        closeReschedule()
    } catch (error) {
        console.error('Error rescheduling:', error)
        await alertModal('Could not reschedule', errorHtml(error))
    } finally {
        isRescheduling.value = false
    }
}

const publishNow = async (item) => {
    const confirmed = await confirmModal(
        'Publish now',
        '<p class="text-gray-700 dark:text-gray-300">This loop will publish immediately and federate to other servers. This cannot be undone.</p>',
        'Publish',
        'Cancel'
    )

    if (!confirmed) return

    busyId.value = item.id

    try {
        await axios.post(`/api/v1/video/publish/${item.id}`)

        items.value = items.value.filter((row) => row.id !== item.id)
    } catch (error) {
        console.error('Error publishing:', error)
        await alertModal('Could not publish', errorHtml(error))
    } finally {
        busyId.value = null
    }
}

const remove = async (item) => {
    const confirmed = await confirmModal(
        'Delete this loop',
        '<p class="text-gray-700">This removes the video and its schedule. This cannot be undone.</p>',
        'Delete',
        'Cancel'
    )

    if (!confirmed) return

    busyId.value = item.id

    try {
        await axios.delete(`/api/v1/video/schedule/${item.id}`)

        items.value = items.value.filter((row) => row.id !== item.id)
    } catch (error) {
        console.error('Error deleting scheduled post:', error)
        await alertModal('Could not delete', errorHtml(error))
    } finally {
        busyId.value = null
    }
}

const errorHtml = (error) => {
    const message =
        error.response?.data?.message ||
        error.response?.data?.error ||
        'Something went wrong. Please try again.'

    return `<p class="text-gray-700">${message}</p>`
}

onMounted(() => {
    load()

    tickTimer = setInterval(() => {
        now.value = Date.now()

        const hasOverdue = items.value.some(
            (item) => item.scheduled_at && new Date(item.scheduled_at).getTime() <= now.value
        )

        if (hasOverdue) {
            refreshQuietly()
        }
    }, 30000)
})

onBeforeUnmount(() => {
    if (tickTimer) {
        clearInterval(tickTimer)
    }
})
</script>
