<template>
    <section
        id="how-it-works"
        class="py-32 dark:bg-white/[0.02] bg-black/[0.025] dark:border-y dark:border-white/[0.05] border-y border-black/[0.05]"
    >
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-20">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.2em] dark:text-white/35 text-black/40 mb-5"
                >
                    {{ $t('common.gettingStarted') }}
                </p>
                <h2
                    class="font-display text-5xl md:text-[4rem] font-black tracking-tight leading-[0.92]"
                >
                    Up and running<br /><span class="gradient-text">in three steps.</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-12 xl:gap-20">
                <div v-for="(s, index) in steps" :key="s.n" class="relative">
                    <div class="h-40 flex items-center">
                        <div
                            class="flex align-center items-center font-display font-black gradient-text leading-none select-none"
                            style="font-size: clamp(3.5rem, 6vw, 5rem); opacity: 0.8"
                        >
                            {{ s.n }}
                        </div>

                        <div
                            v-if="index < steps.length - 1"
                            class="hidden md:flex mt-5 flex-1 h-px ml-5 dark:bg-white/[0.08] bg-black/[0.08]"
                            aria-hidden="true"
                        />
                    </div>

                    <h3 class="font-display text-xl font-bold mb-3 tracking-tight">
                        {{ s.title }}
                    </h3>
                    <p class="dark:text-white/50 text-black/50 leading-relaxed text-[0.95rem]">
                        {{ s.desc }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const steps = [
    {
        n: '1',
        title: t('common.browseTheDirectory'),
        desc: t('common.exploreByTopicHashtagOrVibeEveryStarterKit')
    },
    {
        n: '2',
        title: t('common.followWithOneTap'),
        desc: t('common.everyCreatoryInTheStarterKitFollowedInstantlyYourFeed')
    },
    {
        n: '3',
        title: t('common.makeItYours'),
        desc: t('common.dropWhoeverIsntForYouDiscoverRelatedStarterKits')
    }
]
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
