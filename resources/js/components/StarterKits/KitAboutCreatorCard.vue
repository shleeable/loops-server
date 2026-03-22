<template>
    <section class="w-full">
        <div
            class="creator-card relative overflow-hidden rounded-2xl px-8 py-20 md:py-24 text-center"
        >
            <svg
                class="absolute inset-0 w-full h-full pointer-events-none opacity-[0.15]"
                aria-hidden="true"
            >
                <filter id="creator-grain">
                    <feTurbulence
                        type="fractalNoise"
                        baseFrequency="0.65"
                        numOctaves="3"
                        stitchTiles="stitch"
                    />
                    <feColorMatrix type="saturate" values="0" />
                </filter>
                <rect width="100%" height="100%" filter="url(#creator-grain)" />
            </svg>

            <div class="relative z-10 max-w-2xl mx-auto">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-5 h-px bg-white/40"></div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">
                        {{ $t('common.forCreators') }}
                    </p>
                    <div class="w-5 h-px bg-white/40"></div>
                </div>

                <h2
                    class="font-display text-4xl md:text-5xl lg:text-6xl font-black text-white leading-[1.0] tracking-tight mb-6"
                >
                    {{ $t('common.knowAGreatCornerOfTheFediverse') }}?
                </h2>

                <p class="text-white/65 text-lg leading-relaxed mb-10 max-w-md mx-auto">
                    {{ $t('common.buildAStarterKitAndShareItWithTheWorld') }}.
                    {{
                        $t(
                            'common.helpNewUsersDiscoverYourCommunityAndThePeopleWhoMakeItWorthShowingUpFor'
                        )
                    }}.
                </p>

                <AnimatedButton
                    size="lg"
                    variant="light"
                    :pill="true"
                    @click="router.push('/starter-kits/create')"
                >
                    <div class="flex items-center gap-2">
                        {{ $t('common.createAStarterKit') }}
                        <ArrowRightIcon class="w-4 h-4" />
                    </div>
                </AnimatedButton>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ArrowRightIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<style scoped>
.font-display {
    font-family: 'Syne', sans-serif;
}

.creator-card {
    background: linear-gradient(135deg, #ff5a3c 0%, #ff1478 55%, #c8006a 100%);
    box-shadow:
        0 0 0 1px rgba(255, 99, 71, 0.2),
        0 40px 100px -16px rgba(255, 20, 120, 0.55),
        0 8px 24px -4px rgba(255, 99, 71, 0.3);
}
</style>
