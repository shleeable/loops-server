<template>
    <MainLayout>
        <div
            v-if="loading"
            class="min-h-screen transition-colors duration-500 dark:bg-[#080810] bg-[#FDFCF7] dark:text-white text-[#12121f] font-body lg:-mt-5 lg:-mr-2.5 w-full h-full flex flex-col justify-center items-center"
        >
            <Spinner />
        </div>

        <KitFeatureDisabled v-else-if="isDisabled && !loading" />

        <div v-else class="isolate">
            <div
                class="relative min-h-screen lg:-mt-5 lg:-mr-2.5 font-body transition-colors duration-500 dark:bg-[#080810] bg-[#FDFCF7] dark:text-white text-[#12121f]"
            >
                <nav
                    class="sticky top-17 lg:top-0 left-0 z-20 flex items-center justify-between px-6 md:px-10 py-4 backdrop-blur-xl dark:bg-[#080810]/75 bg-[#FDFCF7]/80 border-b dark:border-white/[0.06] border-black/[0.06]"
                >
                    <div class="flex items-center gap-3">
                        <span class="font-display text-base font-bold tracking-tight"
                            >{{ $t('common.starterKits') }} FAQs</span
                        >
                    </div>
                </nav>

                <KitAboutFaq />

                <KitAboutCta />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import KitAboutFaq from '@/components/StarterKits/KitAboutFaq.vue'
import { ArrowRightIcon } from '@heroicons/vue/24/outline'
import { onMounted, ref } from 'vue'
import axios from '~/plugins/axios'

const api = axios.getAxiosInstance()
const currentStats = ref()
const loading = ref(true)
const isDisabled = ref(false)

const fetchStats = async () => {
    if (appConfig.starterKits === false) {
        isDisabled.value = true
        loading.value = false
        return
    }

    try {
        const statsRes = await api.get(`/api/v1/starter-kits/stats`)
    } catch (e) {
        if (e?.response.status === 404) {
            isDisabled.value = true
            loading.value = false
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => fetchStats())
</script>

<style scoped>
.font-display {
    font-family: 'Syne', sans-serif;
}

.font-body {
    font-family: 'DM Sans', sans-serif;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-up {
    opacity: 0;
    animation: fadeUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
</style>
