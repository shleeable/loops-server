<template>
    <div ref="dropdownRef" class="relative inline-block">
        <div
            :id="triggerId"
            :aria-expanded="open"
            aria-haspopup="menu"
            :aria-controls="menuId"
            @click="toggle"
        >
            <slot name="trigger" :open="open" :toggle="toggle">
                <button
                    type="button"
                    :disabled="disabled"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                >
                    <slot name="trigger-label">Options</slot>
                    <ChevronDownIcon
                        class="w-3.5 h-3.5 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                    />
                </button>
            </slot>
        </div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-1"
            >
                <div
                    v-if="open"
                    :id="menuId"
                    role="menu"
                    :aria-labelledby="triggerId"
                    :aria-label="menuLabel"
                    :style="panelStyle"
                    :class="[
                        'z-[9999] origin-top-right',
                        'rounded-2xl overflow-hidden',
                        'bg-white dark:bg-zinc-900',
                        'border border-zinc-200 dark:border-zinc-700/60',
                        'shadow-xl shadow-black/10 dark:shadow-black/40',
                        width
                    ]"
                >
                    <slot :close="close" />
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, provide } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    align: { type: String, default: 'right', validator: (v) => ['left', 'right'].includes(v) },
    width: { type: String, default: 'w-52' },
    disabled: { type: Boolean, default: false },
    menuLabel: { type: String, default: 'Menu options' }
})

const open = ref(false)
const dropdownRef = ref(null)
const panelStyle = ref({})
const triggerId = `dropdown-trigger-${Math.random().toString(36).slice(2, 8)}`
const menuId = `dropdown-menu-${Math.random().toString(36).slice(2, 8)}`

function computePosition() {
    if (!dropdownRef.value) return
    const rect = dropdownRef.value.getBoundingClientRect()
    const scrollY = window.scrollY
    const scrollX = window.scrollX

    panelStyle.value = {
        position: 'absolute',
        top: `${rect.bottom + scrollY + 8}px`,
        ...(props.align === 'right'
            ? { left: `${rect.right + scrollX}px`, transform: 'translateX(-100%)' }
            : { left: `${rect.left + scrollX}px` })
    }
}

async function toggle() {
    if (props.disabled) return
    open.value = !open.value
    if (open.value) {
        await nextTick()
        computePosition()
    }
}

function close() {
    open.value = false
}

function onKeydown(e) {
    if (e.key === 'Escape') close()
}

function onClickOutside(e) {
    const panel = document.getElementById(menuId)
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(e.target) &&
        panel &&
        !panel.contains(e.target)
    )
        close()
}

function onReposition() {
    if (open.value) computePosition()
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside)
    document.addEventListener('keydown', onKeydown)
    window.addEventListener('scroll', onReposition, true)
    window.addEventListener('resize', onReposition)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', onClickOutside)
    document.removeEventListener('keydown', onKeydown)
    window.removeEventListener('scroll', onReposition, true)
    window.removeEventListener('resize', onReposition)
})

defineExpose({ close, open })

provide('dropdown-close', close)
</script>
