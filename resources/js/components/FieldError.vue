<template>
    <p v-if="message" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ message }}
    </p>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    errors: { type: Object, default: () => ({}) },
    field: { type: String, required: true }
})

const message = computed(() => {
    const fieldErrors = props.errors[props.field]
    if (!fieldErrors) return null
    return Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors
})
</script>
