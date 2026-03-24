<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-4">
        <KitBrowseGridCard :filtered-kits="kits" />
    </div>
</template>

<script setup>
import KitBrowseGridCard from '@/components/StarterKits/KitBrowseGridCard.vue'
const props = defineProps({
    kits: {
        type: Array,
        required: true
    }
})
</script>
