<template>
    <span
        class="inline-flex items-center font-medium rounded-full"
        :class="[sizeClasses, colorClasses]"
    >
        <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="dotClasses" />
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: { type: String, required: true },
    size: { type: String, default: 'sm' }
})

const statusConfig = {
    pending: {
        label: 'Pending',
        bg: 'bg-amber-50 dark:bg-amber-900/20',
        text: 'text-amber-700 dark:text-amber-300',
        dot: 'bg-amber-500'
    },
    ready: {
        label: 'Ready',
        bg: 'bg-sky-50 dark:bg-sky-900/20',
        text: 'text-sky-700 dark:text-sky-300',
        dot: 'bg-sky-500'
    },
    approved: {
        label: 'Approved',
        bg: 'bg-green-50 dark:bg-green-900/20',
        text: 'text-green-700 dark:text-green-300',
        dot: 'bg-green-500'
    },
    rejected: {
        label: 'Rejected',
        bg: 'bg-red-50 dark:bg-red-900/20',
        text: 'text-red-700 dark:text-red-300',
        dot: 'bg-red-500'
    },
    auto_rejected: {
        label: 'Auto-Rejected',
        bg: 'bg-red-50 dark:bg-red-900/20',
        text: 'text-red-700 dark:text-red-300',
        dot: 'bg-red-500'
    },
    expired: {
        label: 'Expired',
        bg: 'bg-gray-50 dark:bg-gray-800',
        text: 'text-gray-600 dark:text-gray-400',
        dot: 'bg-gray-400'
    }
}

const config = computed(() => statusConfig[props.status] || statusConfig.pending)
const label = computed(() => config.value.label)
const colorClasses = computed(() => `${config.value.bg} ${config.value.text}`)
const dotClasses = computed(() => config.value.dot)
const sizeClasses = computed(() =>
    props.size === 'lg' ? 'px-3 py-1 text-sm' : 'px-2 py-0.5 text-xs'
)
</script>
