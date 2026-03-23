<template>
    <span class="isolate inline-flex rounded-md shadow-sm dark:shadow-none">
        <button
            v-for="(btn, index) in buttons"
            :key="btn.value ?? index"
            type="button"
            :disabled="disabled || btn.disabled || loadingValue === btn.value"
            :class="getButtonClasses(btn, index)"
            :aria-pressed="isActive(btn)"
            @click="handleClick(btn)"
        >
            <svg
                v-if="loadingValue === btn.value"
                :class="['animate-spin opacity-70', iconSizeClasses[size]]"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                />
            </svg>
            <component
                :is="btn.icon"
                v-else-if="btn.icon"
                class="opacity-50"
                :class="iconSizeClasses[size]"
                aria-hidden="true"
            />

            {{ btn.label }}
        </button>
    </span>
</template>

<script setup>
import { computed } from 'vue'

const VALID_THEMES = ['default', 'primary', 'danger', 'success', 'warning', 'ghost']

const props = defineProps({
    buttons: {
        type: Array,
        required: true
    },
    modelValue: {
        type: [String, Number, null],
        default: null
    },
    theme: {
        type: String,
        default: 'default',
        validator: (v) =>
            ['default', 'primary', 'danger', 'success', 'warning', 'ghost'].includes(v)
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v)
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loadingValue: {
        type: [String, Number, null],
        default: null
    }
})

const emit = defineEmits(['update:modelValue', 'click'])

const sizeClasses = {
    xs: 'px-2.5 py-1 text-xs gap-1',
    sm: 'px-3.5 py-1.5 text-xs gap-1.5',
    md: 'px-5 py-2 text-sm gap-2',
    lg: 'px-6 py-2.5 text-base gap-2.5'
}

const iconSizeClasses = {
    xs: 'size-3',
    sm: 'size-3.5',
    md: 'size-4',
    lg: 'size-5'
}

const themeClasses = {
    default: {
        base: [
            'bg-gradient-to-b from-white to-gray-100',
            'text-gray-800',
            'inset-ring-1 inset-ring-gray-300/80',
            'hover:from-gray-50 hover:to-gray-150',
            'dark:from-white/[0.13] dark:to-white/[0.06]',
            'dark:text-white',
            'dark:inset-ring-white/[0.11]',
            'dark:hover:from-white/[0.18] dark:hover:to-white/[0.10]'
        ].join(' '),
        active: [
            'bg-gradient-to-b from-gray-200 to-gray-300',
            'text-gray-900',
            'inset-ring-1 inset-ring-gray-400/50',
            'dark:from-white/[0.26] dark:to-white/[0.15]',
            'dark:text-white',
            'dark:inset-ring-white/20'
        ].join(' ')
    },

    primary: {
        base: [
            'bg-gradient-to-b from-blue-400 to-blue-600',
            'text-white',
            'inset-ring-1 inset-ring-blue-700/25',
            'hover:from-blue-300 hover:to-blue-500',
            'dark:from-blue-500 dark:to-blue-700',
            'dark:inset-ring-blue-900/50',
            'dark:hover:from-blue-400 dark:hover:to-blue-600'
        ].join(' '),
        active: [
            'bg-gradient-to-b from-blue-700 to-blue-900',
            'text-white',
            'inset-ring-1 inset-ring-blue-900/40',
            'dark:from-blue-800 dark:to-blue-950',
            'dark:inset-ring-blue-950/60'
        ].join(' ')
    },

    danger: {
        base: [
            'bg-gradient-to-b from-red-400 to-red-600',
            'text-white',
            'inset-ring-1 inset-ring-red-700/25',
            'hover:from-red-300 hover:to-red-500',
            'dark:from-red-500 dark:to-red-700',
            'dark:inset-ring-red-900/50',
            'dark:hover:from-red-400 dark:hover:to-red-600'
        ].join(' '),
        active: [
            'bg-gradient-to-b from-red-700 to-red-900',
            'text-white',
            'inset-ring-1 inset-ring-red-900/40',
            'dark:from-red-800 dark:to-red-950',
            'dark:inset-ring-red-950/60'
        ].join(' ')
    },

    success: {
        base: [
            'bg-gradient-to-b from-emerald-400 to-emerald-600',
            'text-white',
            'inset-ring-1 inset-ring-emerald-700/25',
            'hover:from-emerald-300 hover:to-emerald-500',
            'dark:from-emerald-500 dark:to-emerald-700',
            'dark:inset-ring-emerald-900/50',
            'dark:hover:from-emerald-400 dark:hover:to-emerald-600'
        ].join(' '),
        active: [
            'bg-gradient-to-b from-emerald-700 to-emerald-900',
            'text-white',
            'inset-ring-1 inset-ring-emerald-900/40',
            'dark:from-emerald-800 dark:to-emerald-950',
            'dark:inset-ring-emerald-950/60'
        ].join(' ')
    },

    warning: {
        base: [
            'bg-gradient-to-b from-amber-300 to-amber-500',
            'text-amber-950',
            'inset-ring-1 inset-ring-amber-600/25',
            'hover:from-amber-200 hover:to-amber-400',
            'dark:from-amber-400 dark:to-amber-600',
            'dark:text-amber-950',
            'dark:inset-ring-amber-800/50',
            'dark:hover:from-amber-300 dark:hover:to-amber-500'
        ].join(' '),
        active: [
            'bg-gradient-to-b from-amber-600 to-amber-800',
            'text-white',
            'inset-ring-1 inset-ring-amber-900/40',
            'dark:from-amber-700 dark:to-amber-900',
            'dark:text-white',
            'dark:inset-ring-amber-950/60'
        ].join(' ')
    },

    ghost: {
        base: [
            'bg-transparent',
            'text-gray-600',
            'hover:bg-gradient-to-b hover:from-gray-50 hover:to-gray-100',
            'dark:text-gray-400',
            'dark:hover:from-white/[0.09] dark:hover:to-white/[0.04]'
        ].join(' '),
        active: [
            'bg-gradient-to-b from-gray-100 to-gray-200',
            'text-gray-900',
            'dark:from-white/[0.18] dark:to-white/[0.10]',
            'dark:text-white'
        ].join(' ')
    }
}

const positionClass = (index, total) => {
    if (total === 1) return 'rounded-md'
    if (index === 0) return 'rounded-l-md'
    if (index === total - 1) return 'rounded-r-md -ml-px'
    return '-ml-px'
}

const isActive = (btn) => props.modelValue !== null && props.modelValue === btn.value

const resolveTheme = (btn) => {
    const t = btn.theme
    return t && VALID_THEMES.includes(t) ? t : props.theme
}

const getButtonClasses = (btn, index) => {
    const theme = themeClasses[resolveTheme(btn)]
    const active = isActive(btn)
    return [
        'relative inline-flex items-center font-semibold',
        'transition-[background-image,background-color,color,box-shadow] duration-150',
        'focus:z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-blue-500',
        sizeClasses[props.size],
        positionClass(index, props.buttons.length),
        active ? theme.active : theme.base,
        props.disabled || btn.disabled
            ? 'opacity-40 cursor-not-allowed pointer-events-none'
            : 'cursor-pointer'
    ]
}

const handleClick = (btn) => {
    if (props.disabled || btn.disabled) return
    emit('update:modelValue', btn.value)
    emit('click', btn)
}
</script>
