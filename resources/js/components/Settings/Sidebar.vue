<template>
    <aside class="w-64 bg-white dark:bg-gray-950">
        <nav class="flex flex-col h-full px-3">
            <div class="p-4">
                <button
                    class="flex items-center text-gray-500 hover:text-gray-400 cursor-pointer"
                    @click="$router.back()"
                >
                    <span class="mr-2">←</span> Back
                </button>
            </div>

            <div class="flex-1">
                <SidebarItem
                    v-for="item in menuItems"
                    :key="item.name"
                    :icon="item.icon"
                    :name="item.name"
                    :to="item.path"
                />
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { ref } from "vue";
import SidebarItem from "./SidebarItem.vue";

const menuItems = ref([
    {
        name: "Manage account",
        icon: "bx bx-user",
        path: "/dashboard/account",
        highlight: true,
    },
    {
        name: "Safety",
        icon: "bx bx-lock",
        path: "/dashboard/safety",
    },
    // {
    //   name: 'Federation',
    //   icon: 'bx bx-broadcast',
    //   path: '/dashboard/federation'
    // },
    // {
    //   name: 'Push notifications',
    //   icon: 'bx bx-bell',
    //   path: '/dashboard/notifications'
    // },
    // {
    //   name: 'Integrations',
    //   icon: 'bx bx-code-alt',
    //   path: '/dashboard/business'
    // },
    // {
    //   name: 'Screen time',
    //   icon: 'bx bx-time',
    //   path: '/dashboard/screen-time'
    // },
    // {
    //   name: 'Content preferences',
    //   icon: 'bx bx-poll',
    //   path: '/dashboard/preferences'
    // }
]);
</script>
