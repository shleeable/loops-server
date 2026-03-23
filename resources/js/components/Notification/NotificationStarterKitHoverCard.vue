<template>
    <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 scale-95 translate-y-1"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-1"
    >
        <div
            v-if="show && kit"
            ref="cardRef"
            role="tooltip"
            :id="`starter-kit-hover-card-${kit.id}`"
            :aria-label="`Starter Kit: ${kit.title}`"
            :style="{ top: position.top, left: position.left }"
            class="fixed z-[9999] w-72 rounded-2xl shadow-xl ring-1 overflow-hidden bg-white dark:bg-gray-900 ring-gray-200 dark:ring-white/10 pointer-events-auto"
            @mouseenter="$emit('mouseenter')"
            @mouseleave="$emit('mouseleave')"
        >
            <div class="relative h-20 w-full" aria-hidden="true">
                <div class="absolute inset-0 overflow-hidden rounded-t-2xl">
                    <img
                        v-if="kit.header_url && !headerError"
                        :src="kit.header_url"
                        :alt="`${kit.title} banner`"
                        class="h-full w-full object-cover"
                        @error="headerError = true"
                    />
                    <div
                        v-else
                        class="h-full w-full"
                        :style="{ background: gradientForKit(kit.title) }"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                </div>

                <div class="absolute -bottom-5 left-3 z-10">
                    <div
                        class="w-10 h-10 rounded-xl ring-2 ring-white dark:ring-gray-900 overflow-hidden shadow-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                    >
                        <img
                            v-if="kit.icon_url && !iconError"
                            :src="kit.icon_url"
                            :alt="`${kit.title} icon`"
                            class="w-full h-full object-cover"
                            @error="iconError = true"
                        />
                        <component
                            v-else
                            :is="CollectionIcon"
                            class="w-5 h-5 text-gray-400 dark:text-gray-500"
                            aria-hidden="true"
                        />
                    </div>
                </div>
            </div>

            <div class="pt-8 px-3.5 pb-3.5 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <h3
                        class="text-sm font-semibold text-gray-900 dark:text-white leading-tight line-clamp-1"
                    >
                        {{ kit.title }}
                    </h3>
                    <span
                        class="inline-flex shrink-0 items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400"
                        :aria-label="`Status: ${kit.status_text}`"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true" />
                        {{ kit.status_text }}
                    </span>
                </div>

                <p
                    v-if="kit.description"
                    class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2"
                >
                    {{ kit.description }}
                </p>

                <div
                    v-if="kit.hashtags?.length"
                    class="flex flex-wrap gap-1"
                    role="list"
                    aria-label="Hashtags"
                >
                    <span
                        v-for="tag in kit.hashtags.slice(0, 4)"
                        :key="tag"
                        role="listitem"
                        class="text-[11px] font-medium px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        #{{ tag }}
                    </span>
                </div>

                <div class="border-t border-gray-100 dark:border-white/[0.06]" aria-hidden="true" />

                <div
                    class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400"
                    role="group"
                    aria-label="Kit statistics"
                >
                    <div class="flex items-center gap-1">
                        <UsersIcon class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        <span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">{{
                                kit.approved_accounts
                            }}</span>
                            <span class="sr-only">approved accounts</span>
                        </span>
                    </div>
                    <div v-if="kit.uses > 0" class="flex items-center gap-1">
                        <ArrowDownTrayIcon class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        <span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                {{ kit.uses }}
                            </span>
                            <span class="sr-only">uses</span>
                        </span>
                    </div>
                </div>

                <div
                    v-if="kit.creator"
                    class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500"
                    aria-label="Created by"
                >
                    <img
                        :src="kit.creator.avatar"
                        :alt="kit.creator.name"
                        class="w-4 h-4 rounded-full object-cover"
                        aria-hidden="true"
                    />
                    <span class="truncate"
                        >{{ $t('common.curatedBy') }}
                        <span class="font-medium text-gray-600 dark:text-gray-300"
                            >@{{ kit.creator.name }}</span
                        ></span
                    >
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue'
import { UsersIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import { RectangleStackIcon as CollectionIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    kit: {
        type: Object,
        default: null
    },
    position: {
        type: Object,
        default: () => ({ top: '0px', left: '0px' })
    }
})

defineEmits(['mouseenter', 'mouseleave'])

const cardRef = ref(null)
const headerError = ref(false)
const iconError = ref(false)

const gradientForKit = (title = '') => {
    const gradients = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
        'linear-gradient(135deg, #fccb90 0%, #d57eeb 100%)',
        'linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%)',
        'linear-gradient(135deg, #fd7743 0%, #fda085 100%)',
        'linear-gradient(135deg, #30cfd0 0%, #330867 100%)'
    ]
    const index = title.split('').reduce((acc, c) => acc + c.charCodeAt(0), 0) % gradients.length
    return gradients[index]
}
</script>
