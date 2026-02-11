<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm"
                @click="close"
            >
                <div class="min-h-screen px-4 flex items-start justify-center pt-[15vh]">
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-4"
                    >
                        <div
                            v-if="isOpen"
                            @click.stop
                            class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden ring-1 ring-black/5 dark:ring-white/10"
                        >
                            <div
                                class="relative flex items-center px-4 border-b border-gray-200 dark:border-gray-700"
                            >
                                <MagnifyingGlassIcon
                                    class="h-5 w-5 text-gray-400 dark:text-gray-500"
                                />
                                <input
                                    ref="searchInput"
                                    v-model="query"
                                    type="text"
                                    placeholder="Search or type a command..."
                                    class="w-full px-3 py-4 bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none text-base"
                                    @keydown.down.prevent="
                                        selectedIndex = Math.min(
                                            selectedIndex + 1,
                                            filteredCommands.length - 1
                                        )
                                    "
                                    @keydown.up.prevent="
                                        selectedIndex = Math.max(selectedIndex - 1, 0)
                                    "
                                    @keydown.enter.prevent="
                                        executeCommand(filteredCommands[selectedIndex])
                                    "
                                    @keydown.esc="close"
                                />
                                <div class="flex items-center gap-2">
                                    <kbd
                                        class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600"
                                    >
                                        <span>↑↓</span>
                                    </kbd>
                                    <kbd
                                        class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600"
                                    >
                                        ↵
                                    </kbd>
                                </div>
                            </div>

                            <div
                                v-if="filteredCommands.length > 0"
                                class="max-h-96 overflow-y-auto py-2 px-2"
                            >
                                <div
                                    v-for="(command, index) in filteredCommands"
                                    :key="command.id"
                                    @click="executeCommand(command)"
                                    @mouseenter="selectedIndex = index"
                                    :class="[
                                        'group flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-colors',
                                        selectedIndex === index
                                            ? 'bg-blue-50 dark:bg-blue-900/30'
                                            : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'flex items-center justify-center w-9 h-9 rounded-lg transition-colors flex-shrink-0',
                                            selectedIndex === index
                                                ? command.iconBg
                                                : 'bg-gray-100 dark:bg-gray-700'
                                        ]"
                                    >
                                        <component
                                            :is="command.icon"
                                            :class="[
                                                'w-5 h-5 transition-colors',
                                                selectedIndex === index
                                                    ? command.iconColor
                                                    : 'text-gray-500 dark:text-gray-400'
                                            ]"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                                            v-html="highlightMatch(command.title, query)"
                                        ></div>
                                        <div
                                            v-if="command.description"
                                            class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                        >
                                            {{ command.description }}
                                        </div>
                                    </div>
                                    <div
                                        v-if="command.badge"
                                        class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                    >
                                        {{ command.badge }}
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="py-12 px-4 text-center text-gray-500 dark:text-gray-400"
                            >
                                <MagnifyingGlassIcon
                                    class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3"
                                />
                                <p class="text-sm font-medium">No results found</p>
                                <p class="text-xs mt-1">Try a different search term</p>
                            </div>

                            <div
                                class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50"
                            >
                                <div
                                    class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    <span class="flex items-center gap-1.5">
                                        <kbd
                                            class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-600 font-mono"
                                            >profiles:</kbd
                                        >
                                        Search profiles
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <kbd
                                            class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-600 font-mono"
                                            >videos:</kbd
                                        >
                                        Search videos
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import {
    MagnifyingGlassIcon,
    ChartBarSquareIcon,
    ChatBubbleOvalLeftIcon,
    ChatBubbleLeftRightIcon,
    HashtagIcon,
    ExclamationTriangleIcon,
    UserGroupIcon,
    VideoCameraIcon,
    ServerStackIcon,
    Cog6ToothIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const isOpen = ref(false)
const query = ref('')
const selectedIndex = ref(0)
const searchInput = ref(null)

const commands = [
    {
        id: 'dashboard',
        title: 'Dashboard',
        description: 'View admin dashboard',
        icon: ChartBarSquareIcon,
        iconBg: 'bg-blue-100 dark:bg-blue-900/50',
        iconColor: 'text-blue-600 dark:text-blue-400',
        action: () => router.push('/admin/dashboard'),
        keywords: ['home', 'overview', 'stats']
    },
    {
        id: 'comments',
        title: 'Comments',
        description: 'Manage comments',
        icon: ChatBubbleOvalLeftIcon,
        iconBg: 'bg-green-100 dark:bg-green-900/50',
        iconColor: 'text-green-600 dark:text-green-400',
        action: () => router.push('/admin/comments'),
        keywords: ['moderate', 'moderation']
    },
    {
        id: 'replies',
        title: 'Replies',
        description: 'Manage replies',
        icon: ChatBubbleLeftRightIcon,
        iconBg: 'bg-teal-100 dark:bg-teal-900/50',
        iconColor: 'text-teal-600 dark:text-teal-400',
        action: () => router.push('/admin/replies'),
        keywords: ['responses', 'threads']
    },
    {
        id: 'hashtags',
        title: 'Hashtags',
        description: 'Manage hashtags',
        icon: HashtagIcon,
        iconBg: 'bg-purple-100 dark:bg-purple-900/50',
        iconColor: 'text-purple-600 dark:text-purple-400',
        action: () => router.push('/admin/hashtags'),
        keywords: ['tags', 'trending']
    },
    {
        id: 'instances',
        title: 'Instances',
        description: 'Manage federated instances',
        icon: ServerStackIcon,
        iconBg: 'bg-indigo-100 dark:bg-indigo-900/50',
        iconColor: 'text-indigo-600 dark:text-indigo-400',
        action: () => router.push('/admin/instances'),
        keywords: ['federation', 'servers', 'fediverse']
    },
    {
        id: 'relays',
        title: 'Relays',
        description: 'Manage ActivityPub relays',
        icon: ArrowPathIcon,
        iconBg: 'bg-cyan-100 dark:bg-cyan-900/50',
        iconColor: 'text-cyan-600 dark:text-cyan-400',
        action: () => router.push('/admin/relays'),
        keywords: ['activitypub', 'sync']
    },
    {
        id: 'reports',
        title: 'Reports',
        description: 'View and handle reports',
        icon: ExclamationTriangleIcon,
        iconBg: 'bg-red-100 dark:bg-red-900/50',
        iconColor: 'text-red-600 dark:text-red-400',
        action: () => router.push('/admin/reports'),
        keywords: ['flags', 'moderation', 'abuse']
    },
    {
        id: 'profiles',
        title: 'Profiles',
        description: 'Manage user profiles',
        icon: UserGroupIcon,
        iconBg: 'bg-amber-100 dark:bg-amber-900/50',
        iconColor: 'text-amber-600 dark:text-amber-400',
        action: () => router.push('/admin/profiles'),
        keywords: ['users', 'accounts']
    },
    {
        id: 'videos',
        title: 'Videos',
        description: 'Manage videos',
        icon: VideoCameraIcon,
        iconBg: 'bg-pink-100 dark:bg-pink-900/50',
        iconColor: 'text-pink-600 dark:text-pink-400',
        action: () => router.push('/admin/videos'),
        keywords: ['content', 'media', 'loops']
    },
    {
        id: 'settings',
        title: 'Settings',
        description: 'Admin settings',
        icon: Cog6ToothIcon,
        iconBg: 'bg-gray-100 dark:bg-gray-700',
        iconColor: 'text-gray-600 dark:text-gray-400',
        action: () => router.push('/admin/settings'),
        keywords: ['config', 'configuration', 'preferences']
    }
]

const filteredCommands = computed(() => {
    const searchTerm = query.value.toLowerCase().trim()

    if (!searchTerm) {
        return commands.slice(0, 8)
    }

    const commandMatch = searchTerm.match(/^(profiles|videos|hashtags|instances):(.+)/)

    if (commandMatch) {
        const [, commandType, searchQuery] = commandMatch
        const cleanQuery = searchQuery.trim().replace(/^[@#]/, '')

        const commandConfig = {
            profiles: {
                icon: UserGroupIcon,
                iconBg: 'bg-amber-100 dark:bg-amber-900/50',
                iconColor: 'text-amber-600 dark:text-amber-400',
                title: `Search profiles for "${cleanQuery}"`,
                description: `Jump to profiles search`,
                action: () => router.push(`/admin/profiles?q=${encodeURIComponent(cleanQuery)}`)
            },
            videos: {
                icon: VideoCameraIcon,
                iconBg: 'bg-pink-100 dark:bg-pink-900/50',
                iconColor: 'text-pink-600 dark:text-pink-400',
                title: `Search videos for "${cleanQuery}"`,
                description: `Jump to videos search`,
                action: () => router.push(`/admin/videos?q=${encodeURIComponent(cleanQuery)}`)
            },
            hashtags: {
                icon: HashtagIcon,
                iconBg: 'bg-purple-100 dark:bg-purple-900/50',
                iconColor: 'text-purple-600 dark:text-purple-400',
                title: `Search hashtags for "${cleanQuery}"`,
                description: `Jump to hashtags search`,
                action: () => router.push(`/admin/hashtags?q=${encodeURIComponent(cleanQuery)}`)
            },
            instances: {
                icon: ServerStackIcon,
                iconBg: 'bg-indigo-100 dark:bg-indigo-900/50',
                iconColor: 'text-indigo-600 dark:text-indigo-400',
                title: `Search instances for "${cleanQuery}"`,
                description: `Jump to instances search`,
                action: () => router.push(`/admin/instances?q=${encodeURIComponent(cleanQuery)}`)
            }
        }

        if (commandConfig[commandType]) {
            return [
                {
                    id: `command-${commandType}-${cleanQuery}`,
                    ...commandConfig[commandType],
                    badge: 'Command'
                }
            ]
        }
    }

    return commands.filter((command) => {
        const titleMatch = command.title.toLowerCase().includes(searchTerm)
        const descriptionMatch = command.description?.toLowerCase().includes(searchTerm)
        const keywordsMatch = command.keywords?.some((keyword) => keyword.includes(searchTerm))

        return titleMatch || descriptionMatch || keywordsMatch
    })
})

const highlightMatch = (text, search) => {
    if (!search) return text

    const regex = new RegExp(`(${search.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi')
    return text.replace(
        regex,
        '<mark class="bg-yellow-200 dark:bg-yellow-900/50 text-gray-900 dark:text-gray-100 font-medium">$1</mark>'
    )
}

const open = () => {
    isOpen.value = true
    nextTick(() => {
        searchInput.value?.focus()
    })
}

const close = () => {
    isOpen.value = false
    query.value = ''
    selectedIndex.value = 0
}

const executeCommand = (command) => {
    if (!command) return
    command.action()
    close()
}

const handleKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault()
        if (isOpen.value) {
            close()
        } else {
            open()
        }
    }
}

watch(query, () => {
    selectedIndex.value = 0
})

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})

defineExpose({ open, close })
</script>

<style scoped>
.overflow-y-auto {
    scroll-behavior: smooth;
}

.dark .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.dark .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 4px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}
</style>
