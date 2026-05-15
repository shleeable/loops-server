<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm sm:items-center"
                @click="onBackdropClick"
                role="dialog"
                aria-modal="true"
                aria-labelledby="embed-modal-title"
            >
                <div
                    class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-950 dark:ring-white/10"
                    @click.stop
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800"
                    >
                        <div>
                            <CodeBracketIcon class="w-5 h-5 text-gray-500" />
                        </div>
                        <h2
                            id="embed-modal-title"
                            class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Embed Video
                        </h2>
                        <button
                            type="button"
                            class="rounded-full p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                            aria-label="Close"
                            @click="close"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 6l12 12M18 6L6 18"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-[260px_1fr]">
                        <div
                            class="flex justify-center sm:block pointer-events-none select-none touch-none"
                        >
                            <div
                                class="w-full max-w-[260px] overflow-hidden rounded-xl bg-white ring-1 ring-gray-200 dark:bg-gray-950 dark:ring-gray-800"
                                :class="{ dark: theme === 'dark' }"
                            >
                                <div class="relative aspect-[9/16] bg-gray-900">
                                    <img
                                        v-if="video.media?.thumbnail"
                                        :src="video.media.thumbnail"
                                        :alt="video.caption || 'Video preview'"
                                        class="h-full w-full object-cover"
                                    />
                                    <video
                                        v-else
                                        :src="video.media.src_url"
                                        class="h-full w-full object-cover"
                                    />

                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="flex h-12 w-12 items-center justify-center">
                                            <svg
                                                class="start-overlay__icon"
                                                viewBox="0 0 24 24"
                                                width="72"
                                                height="72"
                                                fill="white"
                                                aria-hidden="true"
                                            >
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div
                                        class="absolute bottom-0 left-0 right-0 flex items-center justify-between bg-[#F02C56] px-2.5 py-1.5"
                                    >
                                        <span class="text-[10px] font-semibold text-white"
                                            >Watch on Loops</span
                                        >
                                        <div
                                            class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-[#F02C56] hover:bg-gray-100"
                                        >
                                            Watch now
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1 p-3 dark:bg-black">
                                    <p
                                        class="truncate text-[11px] font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        @{{ video.account?.username }}
                                    </p>
                                    <p
                                        v-if="video.caption"
                                        class="line-clamp-2 text-[11px] text-gray-600 dark:text-gray-400"
                                    >
                                        {{ video.caption }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex min-w-0 flex-col gap-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Copy code to embed this video
                            </h3>

                            <pre
                                class="overflow-auto whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
                            ><code>{{ blockquoteSnippet }}</code></pre>

                            <div class="rounded-lg border border-gray-200 dark:border-gray-800">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50"
                                    :aria-expanded="showAdvanced"
                                    @click="showAdvanced = !showAdvanced"
                                >
                                    <span>Advanced settings</span>
                                    <svg
                                        class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                        :class="showAdvanced ? 'rotate-180' : ''"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            d="M6 9l6 6 6-6"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>

                                <Transition
                                    enter-active-class="overflow-hidden transition-all duration-200 ease-out"
                                    enter-from-class="max-h-0 opacity-0"
                                    enter-to-class="max-h-96 opacity-100"
                                    leave-active-class="overflow-hidden transition-all duration-150 ease-in"
                                    leave-from-class="max-h-96 opacity-100"
                                    leave-to-class="max-h-0 opacity-0"
                                >
                                    <div
                                        v-show="showAdvanced"
                                        class="space-y-3 border-t border-gray-200 px-3 py-3 dark:border-gray-800"
                                    >
                                        <div>
                                            <label
                                                class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300"
                                            >
                                                Theme
                                            </label>
                                            <select
                                                v-model="theme"
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                            >
                                                <option value="light">Light</option>
                                                <option value="dark">Dark</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300"
                                            >
                                                Start at
                                                <span class="font-normal text-gray-400"
                                                    >(seconds, optional)</span
                                                >
                                            </label>
                                            <input
                                                v-model.number="startTime"
                                                type="number"
                                                min="0"
                                                step="1"
                                                :max="maxStartTime"
                                                placeholder="0"
                                                @blur="clampStartTime"
                                                @change="clampStartTime"
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                            />
                                            <p
                                                v-if="maxStartTime > 0"
                                                class="mt-1 text-[11px] text-gray-500 dark:text-gray-400"
                                            >
                                                Max: {{ maxStartTime }}s
                                            </p>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <AnimatedButton
                                variant="primaryGradient"
                                pill
                                size="lg"
                                @click="copyToClipboard"
                            >
                                <div class="flex items-center gap-2">
                                    <svg
                                        v-if="!copied"
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <rect
                                            x="9"
                                            y="9"
                                            width="13"
                                            height="13"
                                            rx="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            d="M5 13l4 4L19 7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    {{ copied ? 'Copied!' : 'Copy embed code' }}
                                </div>
                            </AnimatedButton>

                            <div class="px-5">
                                <p class="text-sm font-light text-gray-500 leading-relaxed">
                                    By embedding this video, you confirm that you agree to our
                                    <a href="/terms" class="font-bold underline" target="_blank"
                                        >Terms of Service</a
                                    >
                                    and acknowledge you have read and understood our
                                    <a href="/privacy" class="font-bold underline" target="_blank"
                                        >Privacy Policy</a
                                    >.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import AnimatedButton from '../AnimatedButton.vue'
import { CodeBracketIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    open: { type: Boolean, default: false },
    video: { type: Object, required: true },
    embedOrigin: { type: String, default: () => window.location.origin }
})

const emit = defineEmits(['close'])

const theme = ref('light')
const startTime = ref(0)
const showAdvanced = ref(false)
const copied = ref(false)

const maxStartTime = computed(() => {
    const duration = props.video?.media?.duration ?? 0
    return Math.max(0, Math.floor(duration - 3))
})

const CAPTION_PREVIEW_LIMIT = 180

function truncateCaption(s, maxChars = CAPTION_PREVIEW_LIMIT) {
    const str = String(s || '').trim()
    if (str.length <= maxChars) return str

    const slice = str.slice(0, maxChars)
    const lastSpace = slice.lastIndexOf(' ')
    const cut = lastSpace > maxChars * 0.7 ? lastSpace : maxChars

    return slice.slice(0, cut).trimEnd() + '...'
}

function clampStartTime() {
    const val = startTime.value
    if (typeof val !== 'number' || Number.isNaN(val)) {
        startTime.value = 0
        return
    }
    if (val < 0) startTime.value = 0
    else if (val > maxStartTime.value) startTime.value = maxStartTime.value
}

const blockquoteSnippet = computed(() => {
    const handle = props.video.account?.username ? `@${props.video.account.username}` : ''
    const caption = props.video.caption ? escapeHtml(truncateCaption(props.video.caption)) : ''
    const attrs = [
        `class="loops-embed"`,
        `data-shortcode="${escapeHtml(props.video.shortcode)}"`,
        `data-theme="${theme.value}"`,
        startTime.value > 0 ? `data-start="${startTime.value}"` : null,
        `style="min-width:325px;margin:0 auto;"`,
        `cite="${escapeHtml(props.video.url)}"`
    ]
        .filter(Boolean)
        .join(' ')

    return [
        `<blockquote ${attrs}><section>`,
        `<a href="${escapeHtml(props.video.url)}" target="_blank" rel="noopener">${handle}</a>`,
        caption
            ? `  <a href="${escapeHtml(props.video.url)}" target="_blank" rel="noopener">${caption}</a>`
            : null,
        `</section></blockquote>`,
        `<script async src="${props.embedOrigin}/embed.js">`,
        '</' + 'script>'
    ]
        .filter(Boolean)
        .join('')
})

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}

async function copyToClipboard() {
    try {
        await navigator.clipboard.writeText(blockquoteSnippet.value)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 1500)
    } catch (_) {
        const ta = document.createElement('textarea')
        ta.value = blockquoteSnippet.value
        ta.style.position = 'fixed'
        ta.style.opacity = '0'
        document.body.appendChild(ta)
        ta.select()
        try {
            document.execCommand('copy')
            copied.value = true
            setTimeout(() => (copied.value = false), 1500)
        } catch (_) {}
        document.body.removeChild(ta)
    }
}

function close() {
    emit('close')
}

function onBackdropClick(e) {
    if (e.target === e.currentTarget) close()
}

function onKeydown(e) {
    if (e.key === 'Escape') close()
}

watch(
    () => props.open,
    (isOpen) => {
        copied.value = false
        if (isOpen) {
            document.addEventListener('keydown', onKeydown)
        } else {
            document.removeEventListener('keydown', onKeydown)
            showAdvanced.value = false
        }
    }
)
</script>
