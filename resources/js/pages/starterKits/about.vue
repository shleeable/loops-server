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
                <KitAboutNav />

                <KitAboutHero :is-auth="isAuth" />

                <KitAboutStats :stats="stats" />

                <KitAboutBuiltDifferent />

                <KitAboutHowItWorks />

                <KitAboutCreatorCard />

                <KitAboutFaq />

                <KitAboutCta />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ArrowRightIcon } from '@heroicons/vue/24/outline'
import { onMounted, ref, inject, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import axios from '~/plugins/axios'

const authStore = inject('authStore')
const api = axios.getAxiosInstance()
const currentStats = ref()
const loading = ref(true)
const isDisabled = ref(false)
const { t } = useI18n()
const router = useRouter()

const stats = ref([
    { value: '0', label: t('common.curatedAccounts') },
    { value: '0', label: t('common.curatedKits') },
    { value: 'Up to 25', label: t('common.accountsPerKit') },
    { value: '1 tap', label: t('common.toFollowAll') }
])

const isAuth = computed(() => authStore.isAuthenticated)

const fetchStats = async () => {
    if (appConfig.starterKits === false) {
        isDisabled.value = true
        loading.value = false
        return
    }

    try {
        const statsRes = await api.get(`/api/v1/starter-kits/stats`)
        const totalAccounts = statsRes.data.data.stats.total_accounts
        const totalKits = statsRes.data.data.stats.total_kits

        stats.value = [
            { value: totalAccounts.toString(), label: t('common.curatedAccounts') },
            { value: totalKits.toString(), label: t('common.curatedKits') },
            ...stats.value.slice(2)
        ]
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
:global(html) {
    scroll-behavior: smooth;
}

#how-it-works,
#faq {
    scroll-margin-top: 72px;
}

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

.btn-primary {
    background: linear-gradient(130deg, #ff6347 0%, #ff1478 100%);
    box-shadow: 0 0 28px rgba(255, 99, 71, 0.35);
}

.btn-primary:hover {
    box-shadow: 0 0 50px rgba(255, 99, 71, 0.55);
}

.glow-blob {
    position: absolute;
    border-radius: 9999px;
    pointer-events: none;
    filter: blur(80px);
}

.glow-a {
    width: 700px;
    height: 700px;
    left: -180px;
    top: -180px;
    background: radial-gradient(circle, rgba(255, 99, 71, 0.22) 0%, transparent 70%);
}

.glow-b {
    width: 550px;
    height: 550px;
    right: -100px;
    bottom: -80px;
    background: radial-gradient(circle, rgba(255, 20, 120, 0.18) 0%, transparent 70%);
}

:not(.dark) .glow-a {
    opacity: 0.45;
}

:not(.dark) .glow-b {
    opacity: 0.35;
}

.feature-card {
    position: relative;
}

.feature-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    background: linear-gradient(130deg, rgba(255, 99, 71, 0.08) 0%, rgba(255, 20, 120, 0.06) 100%);
    pointer-events: none;
}

.feature-card:hover::after {
    opacity: 1;
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
