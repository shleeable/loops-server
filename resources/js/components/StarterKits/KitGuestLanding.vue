<template>
    <div
        class="relative min-h-screen lg:-mt-5 lg:-mr-2.5 font-body transition-colors duration-500 dark:bg-[#080810] bg-[#FDFCF7] dark:text-white text-[#12121f]"
    >
        <KitAboutNav />

        <KitAboutHero :is-auth="false" />

        <KitAboutStats :stats="props.stats" />

        <KitAboutBuiltDifferent />

        <KitAboutHowItWorks />

        <KitAboutCreatorCard />

        <KitAboutFaq />

        <KitAboutCta />
    </div>
</template>

<script setup>
const props = defineProps({
    stats: {
        type: Object,
        required: true
    }
})
</script>
