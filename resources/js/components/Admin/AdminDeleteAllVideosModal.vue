<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-videos-title"
        >
            <div class="flex min-h-screen items-center justify-center px-4 py-8">
                <div
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm dark:bg-black/70"
                    @click="requestClose"
                />

                <div
                    class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800"
                    >
                        <div class="flex min-w-0 items-start gap-3">
                            <div
                                :class="[
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl',
                                    isDone && !failed.length
                                        ? 'bg-emerald-50 dark:bg-emerald-900/30'
                                        : 'bg-red-50 dark:bg-red-900/30'
                                ]"
                            >
                                <component
                                    :is="
                                        isDone && !failed.length ? CheckCircleIcon : VideoCameraIcon
                                    "
                                    :class="[
                                        'h-5 w-5',
                                        isDone && !failed.length
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-red-600 dark:text-red-400'
                                    ]"
                                />
                            </div>
                            <div class="min-w-0">
                                <h3
                                    id="delete-videos-title"
                                    class="text-base font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ headingText }}
                                </h3>
                                <p
                                    v-if="profile"
                                    class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400"
                                >
                                    @{{ profile.username }}
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="!isRunning"
                            type="button"
                            @click="requestClose"
                            class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <template v-if="phase === 'confirm'">
                            <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                                This permanently deletes every video posted by
                                <strong class="text-gray-900 dark:text-white"
                                    >@{{ profile?.username }}</strong
                                >. Each video is moderated individually and this cannot be undone.
                            </p>

                            <div
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/50 dark:bg-amber-900/20"
                            >
                                <p class="text-sm text-amber-800 dark:text-amber-300">
                                    <ExclamationTriangleIcon class="mr-1 inline h-4 w-4" />
                                    Approximately
                                    <strong>{{ formatNumber(profile?.post_count || 0) }}</strong>
                                    videos will be removed. Larger accounts may take several
                                    minutes.
                                </p>
                            </div>

                            <div>
                                <label
                                    for="delete-videos-confirm"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Type "DELETE" to confirm:
                                </label>
                                <input
                                    id="delete-videos-confirm"
                                    ref="confirmInputRef"
                                    v-model="confirmText"
                                    type="text"
                                    placeholder="DELETE"
                                    autocomplete="off"
                                    class="mt-2 w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    @keydown.enter="start"
                                />
                            </div>
                        </template>

                        <template v-else>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ statusText }}
                                    </span>
                                    <span
                                        v-if="phase !== 'collecting'"
                                        class="font-mono text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ formatNumber(processed) }} /
                                        {{ formatNumber(total) }}
                                    </span>
                                </div>

                                <div
                                    class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800"
                                    role="progressbar"
                                    :aria-valuenow="phase === 'collecting' ? undefined : percent"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                >
                                    <div
                                        :class="[
                                            'h-full rounded-full transition-all duration-300 ease-out',
                                            phase === 'collecting'
                                                ? 'w-full animate-pulse bg-gray-300 dark:bg-gray-700'
                                                : failed.length
                                                  ? 'bg-amber-500'
                                                  : 'bg-[#F02C56]'
                                        ]"
                                        :style="
                                            phase === 'collecting' ? null : { width: percent + '%' }
                                        "
                                    />
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ detailText }}
                                </p>
                            </div>

                            <div
                                v-if="failed.length"
                                class="max-h-40 overflow-y-auto rounded-2xl border border-gray-200 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-800/40"
                            >
                                <div
                                    class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                >
                                    Failed ({{ failed.length }})
                                </div>
                                <div
                                    v-for="item in failed"
                                    :key="item.id"
                                    class="flex items-center justify-between gap-3 py-1 text-xs"
                                >
                                    <span class="font-mono text-gray-600 dark:text-gray-400">
                                        #{{ item.id }}
                                    </span>
                                    <span class="truncate text-gray-500 dark:text-gray-500">
                                        {{ item.message }}
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-gray-800"
                    >
                        <template v-if="phase === 'confirm'">
                            <button
                                type="button"
                                class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                @click="requestClose"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                :disabled="!canStart"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-white transition',
                                    canStart
                                        ? 'bg-red-600 hover:bg-red-700'
                                        : 'cursor-not-allowed bg-gray-300 dark:bg-gray-700'
                                ]"
                                @click="start"
                            >
                                <TrashIcon class="h-4 w-4" />
                                Delete all videos
                            </button>
                        </template>

                        <template v-else-if="isRunning">
                            <button
                                type="button"
                                :disabled="cancelRequested"
                                class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                @click="cancelRequested = true"
                            >
                                <ArrowPathIcon class="h-4 w-4 animate-spin" />
                                {{ cancelRequested ? 'Stopping…' : 'Stop' }}
                            </button>
                        </template>

                        <template v-else>
                            <button
                                v-if="failed.length"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                @click="retryFailed"
                            >
                                <ArrowPathIcon class="h-4 w-4" />
                                Retry failed
                            </button>
                            <button
                                type="button"
                                class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                @click="requestClose"
                            >
                                Close
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, nextTick, ref, watch, onUnmounted } from 'vue'
import { profilesApi, videosApi } from '@/services/adminApi'
import { useUtils } from '@/composables/useUtils'
import {
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    TrashIcon,
    VideoCameraIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    show: { type: Boolean, default: false },
    profile: { type: Object, default: null }
})

const emit = defineEmits(['close', 'complete'])

const { formatNumber } = useUtils()

const PER_PAGE = 40
const CONCURRENCY = 3
const MAX_PAGES = 500

const phase = ref('confirm')
const confirmText = ref('')
const confirmInputRef = ref(null)
const cancelRequested = ref(false)

const total = ref(0)
const deleted = ref(0)
const collected = ref(0)
const failed = ref([])
const alive = ref(true)

const isRunning = computed(() => phase.value === 'collecting' || phase.value === 'deleting')
const isDone = computed(() => phase.value === 'done')
const canStart = computed(() => confirmText.value === 'DELETE' && !!props.profile?.id)
const processed = computed(() => deleted.value + failed.value.length)
const percent = computed(() =>
    total.value ? Math.min(100, Math.round((processed.value / total.value) * 100)) : 0
)

const headingText = computed(() => {
    if (phase.value === 'confirm') return 'Delete All Videos'
    if (phase.value === 'done') return cancelRequested.value ? 'Stopped' : 'Finished'
    return 'Deleting videos'
})

const statusText = computed(() => {
    if (phase.value === 'collecting') return 'Collecting videos…'
    if (phase.value === 'deleting') return cancelRequested.value ? 'Stopping…' : 'Deleting…'
    return failed.value.length ? 'Completed with errors' : 'All videos deleted'
})

const detailText = computed(() => {
    if (phase.value === 'collecting') {
        return `Found ${formatNumber(collected.value)} so far`
    }
    if (phase.value === 'deleting') {
        return `${formatNumber(deleted.value)} deleted, ${formatNumber(failed.value.length)} failed`
    }
    if (!total.value) return 'This account has no videos.'
    return `${formatNumber(deleted.value)} deleted, ${formatNumber(failed.value.length)} failed.`
})

const reset = () => {
    phase.value = 'confirm'
    confirmText.value = ''
    cancelRequested.value = false
    total.value = 0
    deleted.value = 0
    collected.value = 0
    failed.value = []
}

const requestClose = () => {
    if (isRunning.value) return
    emit('close')
}

const errorMessage = (error) =>
    error?.response?.data?.message || error?.response?.data?.error || error?.message || 'Failed'

const collectVideoIds = async () => {
    const ids = []
    const seen = new Set()
    let cursor = null
    let page = 1

    for (let i = 0; i < MAX_PAGES; i++) {
        if (cancelRequested.value && alive.value) break

        const params = { limit: PER_PAGE }
        if (cursor) params.cursor = cursor
        else if (!cursor && page > 1) params.page = page

        const res = await profilesApi.getProfileVideos(props.profile.id, params)
        const rows = res?.data || []
        if (!rows.length) break

        for (const row of rows) {
            const id = row?.id ?? row?.video_id
            if (id == null) continue
            const key = String(id)
            if (seen.has(key)) continue
            seen.add(key)
            ids.push(key)
        }

        collected.value = ids.length

        const nextCursor = res?.meta?.next_cursor || null
        if (nextCursor) {
            cursor = nextCursor
        } else if (rows.length < PER_PAGE) {
            break
        } else {
            page += 1
        }
    }

    return ids
}

const runPool = async (ids, worker) => {
    let index = 0
    const size = Math.min(CONCURRENCY, ids.length)
    const workers = Array.from({ length: size }, async () => {
        while (index < ids.length && !cancelRequested.value && alive.value) {
            const id = ids[index++]
            await worker(id)
        }
    })
    await Promise.all(workers)
}

const deleteVideos = async (ids) => {
    await runPool(ids, async (id) => {
        try {
            await videosApi.moderateVideo(id, { action: 'delete' })
            deleted.value += 1
        } catch (error) {
            failed.value = [...failed.value, { id, message: errorMessage(error) }]
        }
    })
}

const finish = () => {
    phase.value = 'done'
    emit('complete', {
        deleted: deleted.value,
        failed: failed.value.length,
        cancelled: cancelRequested.value
    })
}

const start = async () => {
    if (!canStart.value) return

    cancelRequested.value = false
    deleted.value = 0
    failed.value = []
    collected.value = 0
    total.value = 0
    phase.value = 'collecting'

    try {
        const ids = await collectVideoIds()
        total.value = ids.length

        if (!ids.length || cancelRequested.value) {
            finish()
            return
        }

        phase.value = 'deleting'
        await deleteVideos(ids)
    } catch (error) {
        failed.value = [...failed.value, { id: '—', message: errorMessage(error) }]
    } finally {
        finish()
    }
}

const retryFailed = async () => {
    const ids = failed.value.map((item) => item.id).filter((id) => id !== '—')
    if (!ids.length) return

    cancelRequested.value = false
    failed.value = []
    total.value = ids.length
    deleted.value = 0
    phase.value = 'deleting'

    await deleteVideos(ids)
    finish()
}

watch(
    () => props.show,
    async (open) => {
        if (open) {
            reset()
            document.body.style.overflow = 'hidden'
            await nextTick()
            confirmInputRef.value?.focus()
        } else {
            document.body.style.overflow = ''
        }
    }
)
onUnmounted(() => {
    alive.value = false
    document.body.style.overflow = ''
})
</script>
