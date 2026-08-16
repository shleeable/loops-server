<template>
    <div
        class="relative hidden lg:flex flex-col justify-between overflow-hidden p-12 bg-gradient-to-br from-[#F02C56] via-[#d81f47] to-[#7a1129]"
    >
        <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-white/10 blur-3xl" />
        <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-black/20 blur-3xl" />

        <div class="relative flex items-center gap-3">
            <img src="/img/logo-light.svg" alt="Loops" class="h-9 w-auto" />
            <span class="text-2xl font-bold tracking-tight text-white"> Loops </span>
        </div>

        <div class="relative max-w-md">
            <h1 class="text-5xl font-bold leading-tight tracking-tight text-white">
                {{ heading }}
            </h1>
            <p class="mt-4 text-lg text-white/80">
                {{ subheading }}
            </p>

            <ul class="mt-10 space-y-6">
                <li v-for="feature in features" :key="feature.title" class="flex gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15"
                    >
                        <component :is="feature.icon" class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ feature.title }}</p>
                        <p class="mt-1 text-sm text-white/70">
                            {{ feature.description }}
                        </p>
                    </div>
                </li>
            </ul>
        </div>

        <p class="relative text-sm text-white/60">
            {{ $t('auth.openSourceCommunityFunded') }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { GlobeAltIcon, ShieldCheckIcon, SparklesIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    heading: {
        type: String,
        required: true
    },
    subheading: {
        type: String,
        required: true
    }
})

const { t } = useI18n()

const features = computed(() => [
    {
        icon: GlobeAltIcon,
        title: t('auth.featureFederatedTitle'),
        description: t('auth.featureFederatedDesc')
    },
    {
        icon: SparklesIcon,
        title: t('auth.featureFeedTitle'),
        description: t('auth.featureFeedDesc')
    },
    {
        icon: ShieldCheckIcon,
        title: t('auth.featurePrivacyTitle'),
        description: t('auth.featurePrivacyDesc')
    }
])
</script>
