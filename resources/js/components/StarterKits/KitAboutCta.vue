<template>
    <div>
        <section class="py-32 text-center px-6">
            <h2
                class="font-display font-black tracking-tight leading-[0.9] mb-6"
                style="font-size: clamp(2.8rem, 7vw, 5.5rem)"
            >
                {{ $t('common.findYourPeople') }}.<br />
                <span class="gradient-text">{{ $t('common.startToday') }}.</span>
            </h2>
            <p
                class="dark:text-white/50 text-black/50 text-lg max-w-sm mx-auto mb-10 leading-relaxed"
            >
                {{
                    $t('common.browseStarterKitsBuiltByTheCommunityAndFillYourLoopsFeedInSeconds')
                }}.
            </p>
            <AnimatedButton
                variant="primaryGradient"
                size="lg"
                :pill="true"
                @click="router.push('/starter-kits/browse')"
            >
                <div class="flex items-center gap-2">
                    {{ $t('common.browseStarterKits') }}
                    <ArrowRightIcon class="w-4 h-4" />
                </div>
            </AnimatedButton>
        </section>

        <div class="h-20 lg:h-0"></div>
    </div>
</template>

<script setup>
import { ArrowRightIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<style scoped>
.font-display {
    font-family: 'Syne', sans-serif;
}

.font-body {
    font-family: 'DM Sans', sans-serif;
}

.gradient-text {
    background: linear-gradient(130deg, #ff6347 0%, #ff1478 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>
