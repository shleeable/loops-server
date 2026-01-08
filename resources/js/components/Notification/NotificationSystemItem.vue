<template>
    <div
        :data-notification-item="notification.id"
        @click="handleNotificationClick"
        class="group relative flex items-center gap-3 py-3 px-4 transition-all cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5"
        :class="[
            !notification.read_at
                ? 'bg-blue-50/40 dark:bg-blue-900/10'
                : 'bg-white dark:bg-gray-900'
        ]"
    >
        <div
            v-if="!notification.read_at"
            class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"
        ></div>

        <div class="relative flex-shrink-0">
            <div
                class="flex items-center justify-center w-10 h-10 rounded-full"
                :class="getIconBackgroundClass()"
            >
                <component :is="getSystemIcon()" class="w-5 h-5" :class="getIconColorClass()" />
            </div>
        </div>

        <div class="flex-1 min-w-0 pr-2">
            <p
                class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-0.5"
                :class="!notification.read_at ? 'font-bold' : 'font-semibold'"
            >
                {{ notification.systemMessage.title }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-1 leading-snug">
                {{ notification.systemMessage.summary }}
            </p>
            <span class="text-gray-400 text-xs whitespace-nowrap mt-1 inline-block">
                {{ formatRecentDate(notification.systemMessage.published_at) }}
            </span>
        </div>

        <div class="flex-shrink-0">
            <div v-if="!notification.read_at" class="w-2 h-2 bg-blue-500 rounded-full"></div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useUtils } from '@/composables/useUtils'
import {
    BellIcon,
    InformationCircleIcon,
    SparklesIcon,
    MegaphoneIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    notification: {
        type: Object,
        required: true
    }
})

const { formatRecentDate } = useUtils()

const emit = defineEmits(['mark-as-read'])
const router = useRouter()

const systemTypeConfig = {
    info: {
        icon: InformationCircleIcon,
        iconColor: 'text-white',
        bgColor: 'bg-blue-500'
    },
    update: {
        icon: MegaphoneIcon,
        iconColor: 'text-white',
        bgColor: 'bg-purple-500'
    },
    feature: {
        icon: SparklesIcon,
        iconColor: 'text-white',
        bgColor: 'bg-green-500'
    }
}

const getSystemIcon = () => {
    const config = systemTypeConfig[props.notification.systemType]
    return config?.icon || BellIcon
}

const getIconColorClass = () => {
    const config = systemTypeConfig[props.notification.systemType]
    return config?.iconColor || 'text-white'
}

const getIconBackgroundClass = () => {
    const config = systemTypeConfig[props.notification.systemType]
    return config?.bgColor || 'bg-gray-500'
}

const markAsRead = () => {
    if (!props.notification.read_at) {
        emit('mark-as-read', props.notification.id)
    }
}

const handleNotificationClick = () => {
    markAsRead()
    router.push(`/notifications/system/${props.notification.systemMessage.id}`)
}
</script>
