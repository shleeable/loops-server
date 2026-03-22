<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="$emit('update:modelValue', false)"
                />
                <div
                    class="relative w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 p-6"
                >
                    <div
                        class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/50 flex items-center justify-center mx-auto mb-4"
                    >
                        <ExclamationCircleIcon class="w-5 h-5 text-red-500" />
                    </div>
                    <h3
                        class="font-display font-bold text-gray-900 dark:text-white text-center mb-2"
                    >
                        {{ title }}
                    </h3>
                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5 leading-relaxed"
                    >
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{
                            confirmWord
                        }}</span>
                        {{ description }}
                    </p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                        Type
                        <span class="font-mono text-gray-700 dark:text-gray-200">{{
                            confirmWord
                        }}</span>
                        to confirm:
                    </p>
                    <input
                        v-model="inputText"
                        type="text"
                        :placeholder="confirmWord"
                        class="w-full px-4 py-2.5 mb-5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-red-400 focus:ring-2 focus:ring-red-400/20 transition-all"
                    />
                    <div class="flex gap-3">
                        <button
                            @click="$emit('update:modelValue', false)"
                            class="flex-1 py-2.5 text-sm font-semibold rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-gray-300 transition-all"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            @click="handleConfirm"
                            :disabled="inputText !== confirmWord || loading"
                            class="flex-1 py-2.5 text-sm font-semibold rounded-xl bg-red-500 hover:bg-red-600 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600 text-white transition-all flex items-center justify-center gap-2"
                        >
                            <Spinner v-if="loading" size="sm" />
                            {{ confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    title: { type: String, default: 'Are you sure?' },
    description: { type: String, default: 'and all its data will be permanently removed.' },
    confirmWord: { type: String, required: true },
    confirmLabel: { type: String, default: 'Delete' },
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const inputText = ref('')

watch(
    () => props.modelValue,
    (val) => {
        if (val) inputText.value = ''
    }
)

function handleConfirm() {
    if (inputText.value === props.confirmWord && !props.loading) {
        emit('confirm')
    }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
