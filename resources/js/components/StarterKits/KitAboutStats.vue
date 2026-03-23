<template>
    <div
        class="dark:border-y dark:border-white/[0.06] border-y border-black/[0.06] dark:bg-white/[0.025] bg-black/[0.025] py-9"
    >
        <div class="max-w-4xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div v-for="s in stats" :key="s.label">
                <div class="font-display text-2xl font-bold mb-1 gradient-text">
                    {{ s.value }}
                </div>
                <div class="text-sm dark:text-white/45 text-black/45">
                    {{ s.label }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    stats: {
        type: Array,
        required: true
    }
})
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
