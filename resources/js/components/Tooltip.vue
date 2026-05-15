<template>
    <span
        ref="triggerRef"
        class="inline-flex items-center"
        tabindex="0"
        :aria-describedby="visible ? tooltipId : undefined"
        @mouseenter="show"
        @mouseleave="hide"
        @focusin="show"
        @focusout="hide"
        @click="toggle"
    >
        <slot />
    </span>

    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="visible"
                :id="tooltipId"
                ref="tooltipRef"
                role="tooltip"
                :class="[
                    'fixed z-50 rounded-lg px-3 py-2 text-sm leading-snug shadow-lg',
                    'origin-center select-text',
                    themeClasses
                ]"
                :style="{
                    top: `${coords.y}px`,
                    left: `${coords.x}px`,
                    maxWidth: maxWidth
                }"
                @mouseenter="show"
                @mouseleave="hide"
            >
                <slot name="content">
                    <div v-if="html" v-html="html" />
                    <template v-else-if="title || body">
                        <div v-if="title" class="font-semibold">{{ title }}</div>
                        <div v-if="body" class="mt-0.5 text-xs opacity-90">{{ body }}</div>
                    </template>
                    <template v-else>{{ text }}</template>
                </slot>

                <div
                    v-if="showArrow"
                    :class="[
                        'absolute h-2 w-2 rotate-45',
                        arrowClasses,
                        actualPosition === 'top' ? '-bottom-1' : '-top-1'
                    ]"
                    :style="{
                        left: `${coords.arrowX}px`,
                        transform: 'translateX(-50%) rotate(45deg)'
                    }"
                />
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, useId } from 'vue'

const props = defineProps({
    position: {
        type: String,
        default: 'top',
        validator: (v) => ['top', 'bottom', 'auto'].includes(v)
    },
    text: { type: String, default: '' },
    title: { type: String, default: '' },
    body: { type: String, default: '' },
    html: { type: String, default: '' },
    theme: {
        type: String,
        default: 'default',
        validator: (v) =>
            ['default', 'dark', 'light', 'primary', 'danger', 'success', 'warning'].includes(v)
    },
    delay: { type: Number, default: 80 },
    hideDelay: { type: Number, default: 80 },
    offset: { type: Number, default: 8 },
    maxWidth: { type: String, default: '16rem' },
    disabled: { type: Boolean, default: false },
    showArrow: { type: Boolean, default: true }
})

const triggerRef = ref(null)
const tooltipRef = ref(null)
const visible = ref(false)
const actualPosition = ref('top')
const coords = ref({ x: 0, y: 0, arrowX: 0 })
const tooltipId = `tt-${useId()}`

let showTimer = null
let hideTimer = null

const themeClasses = computed(
    () =>
        ({
            default: 'bg-gray-900 text-white dark:bg-gray-700 dark:text-gray-50',
            dark: 'bg-gray-900 text-white',
            light: 'bg-white text-gray-900 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-50 dark:ring-gray-700',
            primary: 'bg-indigo-600 text-white dark:bg-indigo-500',
            danger: 'bg-red-600 text-white dark:bg-red-500',
            success: 'bg-emerald-600 text-white dark:bg-emerald-500',
            warning: 'bg-amber-500 text-white dark:bg-amber-600'
        })[props.theme]
)

const arrowClasses = computed(
    () =>
        ({
            default: 'bg-gray-900 dark:bg-gray-700',
            dark: 'bg-gray-900',
            light: 'bg-white ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700',
            primary: 'bg-indigo-600 dark:bg-indigo-500',
            danger: 'bg-red-600 dark:bg-red-500',
            success: 'bg-emerald-600 dark:bg-emerald-500',
            warning: 'bg-amber-500 dark:bg-amber-600'
        })[props.theme]
)

const updatePosition = async () => {
    await nextTick()
    if (!triggerRef.value || !tooltipRef.value) return

    const trig = triggerRef.value.getBoundingClientRect()
    const tip = tooltipRef.value.getBoundingClientRect()
    const vh = window.innerHeight
    const vw = window.innerWidth
    const pad = 8

    let pos = props.position
    if (pos === 'auto') {
        pos = trig.top > vh - trig.bottom ? 'top' : 'bottom'
    } else if (pos === 'top' && trig.top < tip.height + props.offset + pad) {
        pos = 'bottom'
    } else if (pos === 'bottom' && vh - trig.bottom < tip.height + props.offset + pad) {
        pos = 'top'
    }
    actualPosition.value = pos

    const triggerCenter = trig.left + trig.width / 2
    let x = triggerCenter - tip.width / 2
    x = Math.max(pad, Math.min(x, vw - tip.width - pad))

    const arrowX = Math.max(12, Math.min(triggerCenter - x, tip.width - 12))

    const y = pos === 'top' ? trig.top - tip.height - props.offset : trig.bottom + props.offset

    coords.value = { x, y, arrowX }
}

const show = () => {
    if (props.disabled) return
    clearTimeout(hideTimer)
    showTimer = setTimeout(async () => {
        visible.value = true
        await nextTick()
        updatePosition()
    }, props.delay)
}

const hide = () => {
    clearTimeout(showTimer)
    hideTimer = setTimeout(() => {
        visible.value = false
    }, props.hideDelay)
}

const toggle = () => (visible.value ? hide() : show())

const onScroll = () => {
    if (visible.value) updatePosition()
}
const onResize = () => {
    if (visible.value) updatePosition()
}
const onKeydown = (e) => {
    if (e.key === 'Escape' && visible.value) hide()
}
const onDocClick = (e) => {
    if (!visible.value) return
    if (triggerRef.value?.contains(e.target)) return
    if (tooltipRef.value?.contains(e.target)) return
    visible.value = false
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, true)
    window.addEventListener('resize', onResize)
    document.addEventListener('keydown', onKeydown)
    document.addEventListener('click', onDocClick)
})

onBeforeUnmount(() => {
    clearTimeout(showTimer)
    clearTimeout(hideTimer)
    window.removeEventListener('scroll', onScroll, true)
    window.removeEventListener('resize', onResize)
    document.removeEventListener('keydown', onKeydown)
    document.removeEventListener('click', onDocClick)
})
</script>
