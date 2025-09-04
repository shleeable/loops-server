<script setup lang="ts">
import { computed } from "vue";
import Avatar from "./Avatar.vue";

interface Props {
    account: Object;
}

const props = withDefaults(defineProps<Props>(), {
    account: {
        username: "Username",
        name: "name",
        avatar: "",
    },
});
</script>

<template>
    <div class="flex items-center space-x-3">
        <Avatar :src="account.avatar" width="40" />
        <div>
            <div class="font-medium text-[15px] dark:text-slate-200">
                {{ account.name }}
            </div>
            <div class="font-semibold text-gray-500 text-[12px]">
                @{{ account.username }}
            </div>
        </div>
    </div>
</template>

<script lang="ts">
export default {
    name: "ProfileListCard",
};
</script>
