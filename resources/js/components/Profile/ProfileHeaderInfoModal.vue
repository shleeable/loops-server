<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 font-body"
            >
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close" />

                <div
                    class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-xl bg-[#F02C56]/10 flex items-center justify-center flex-shrink-0"
                            >
                                <IdentificationIcon class="w-4 h-4 text-[#F02C56]" />
                            </div>
                            <h3 class="font-display font-bold text-gray-900 dark:text-white">
                                Account details
                            </h3>
                        </div>
                        <button
                            @click="close"
                            class="p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="px-5 pt-5 pb-4 flex items-center gap-3">
                        <img
                            v-if="profile.avatar"
                            :src="profile.avatar"
                            :alt="profile.name"
                            class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-800 flex-shrink-0"
                            onerror="
                                this.src = '/storage/avatars/default.jpg'
                                this.onerror = null
                            "
                        />
                        <div
                            v-else
                            class="w-14 h-14 rounded-full bg-gradient-to-br from-[#F02C56] to-[#FF5C8A] flex items-center justify-center text-white font-display font-bold text-lg flex-shrink-0"
                        >
                            {{ initial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="font-display font-bold text-gray-900 dark:text-white truncate"
                            >
                                {{ profile.name || username }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                @{{ username }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="profile.is_owner || profile.is_admin"
                        class="px-5 pb-4 flex flex-wrap gap-2"
                    >
                        <span
                            v-if="profile.is_owner"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F02C56]/10 text-[#F02C56]"
                        >
                            <UserIcon class="w-3.5 h-3.5" /> This is you
                        </span>
                        <span
                            v-if="profile.is_admin"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                        >
                            <CheckCircleIcon class="w-3.5 h-3.5" /> Verified admin
                        </span>
                    </div>

                    <div class="px-5 pb-2 divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="flex items-center gap-3 py-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0"
                            >
                                <UserIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500"
                                >
                                    Name
                                </p>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                >
                                    {{ profile.name || '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 py-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0"
                            >
                                <CalendarIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500"
                                >
                                    {{ profile.local ? 'Joined' : 'First seen' }}
                                </p>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                >
                                    {{ formatDate(profile.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="accountDomain" class="flex items-center gap-3 py-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0"
                            >
                                <GlobeAltIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500"
                                >
                                    Remote account from
                                </p>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                >
                                    {{ accountDomain }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="profile.links && profile.links.length"
                            class="pt-4 pb-2 border-t border-gray-100 dark:border-gray-800"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2"
                            >
                                Links
                            </p>
                            <div
                                class="flex flex-col gap-1.5 max-h-[215px] overflow-y-auto no-scrollbar"
                            >
                                <a
                                    v-for="(linkItem, index) in profile.links"
                                    :key="index"
                                    :href="linkItem.link"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <div
                                        class="w-9 h-9 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center flex-shrink-0"
                                    >
                                        <LinkIcon
                                            class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                        />
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white truncate flex-1"
                                    >
                                        {{ linkItem.url }}
                                    </span>
                                    <CheckBadgeIcon
                                        v-if="linkItem.is_verified"
                                        class="w-4 h-4 text-blue-500 dark:text-blue-400 flex-shrink-0"
                                    />
                                    <ArrowTopRightOnSquareIcon
                                        class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex gap-3">
                            <button
                                @click="copyUrl"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition-colors"
                            >
                                <component
                                    :is="copied ? CheckCircleIcon : ClipboardDocumentIcon"
                                    class="w-4 h-4"
                                    :class="copied ? 'text-emerald-500' : ''"
                                />
                                {{ copied ? 'Copied!' : 'Copy profile link' }}
                            </button>

                            <a
                                v-if="!profile.local"
                                :href="profile.remote_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition-colors"
                            >
                                <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                                View remote profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, inject, computed } from 'vue'
import {
    GlobeAltIcon,
    XMarkIcon,
    CheckCircleIcon,
    ClipboardDocumentIcon,
    IdentificationIcon,
    UserIcon,
    CalendarIcon,
    LinkIcon,
    CheckBadgeIcon,
    ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    username: { type: String, required: true },
    url: { type: String, required: true },
    profile: { type: Object, required: true }
})

const { formatDate } = useUtils()

const emit = defineEmits(['update:modelValue'])
const copied = ref(false)

function close() {
    emit('update:modelValue', false)
}

const initial = computed(() =>
    (props.profile.name || props.username || '?').charAt(0).toUpperCase()
)

const accountDomain = computed(() => {
    if (props.profile.local) {
        return
    }

    return new URL(props.profile.remote_url).hostname
})

watch(
    () => props.modelValue,
    (val) => {
        document.body.style.overflow = val ? 'hidden' : ''
    }
)

onUnmounted(() => {
    document.body.style.overflow = ''
})

async function copyUrl() {
    try {
        await navigator.clipboard.writeText(props.url)
        copied.value = true
        setTimeout(() => (copied.value = false), 2000)
    } catch (e) {
        // clipboard unavailable / denied
    }
}
</script>
