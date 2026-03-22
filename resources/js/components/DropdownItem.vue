<template>
    <component
        :is="to ? 'router-link' : as"
        v-bind="to ? { to } : href ? { href } : {}"
        role="menuitem"
        :tabindex="disabled ? -1 : 0"
        :aria-disabled="disabled"
        :class="[
            'flex items-center gap-3 w-full px-4 py-3 text-sm font-medium',
            'transition-colors duration-150 cursor-pointer',
            'focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-zinc-400 dark:focus-visible:ring-zinc-500',
            disabled
                ? 'opacity-40 cursor-not-allowed pointer-events-none'
                : destructive
                  ? 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40'
                  : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white'
        ]"
        @click="!disabled && (emit('click', $event), closeDropdown())"
    >
        <slot />
    </component>
</template>

<script setup>
import { inject } from 'vue'
defineProps({
    as: {
        type: String,
        default: 'button'
    },
    to: String,
    href: String,
    disabled: Boolean,
    destructive: Boolean
})

const closeDropdown = inject('dropdown-close', () => {})
const emit = defineEmits(['click'])
</script>
