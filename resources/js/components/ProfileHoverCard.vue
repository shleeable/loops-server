<template>
    <div
        v-if="show && displayData"
        class="fixed z-[9999] w-72 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
        :style="{ top: position.top, left: position.left }"
        @mouseenter="emit('mouseenter')"
        @mouseleave="emit('mouseleave')"
    >
        <div v-if="isUnavailable" class="p-6 text-center">
            <div
                class="mx-auto w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-3"
            >
                <svg
                    class="w-8 h-8 text-gray-400 dark:text-gray-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                    />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
                {{ $t('profile.accountUnavailable') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $t('profile.thisAccountIsNotAvailable') }}
            </p>
        </div>

        <div v-else-if="userState?.blocking" class="p-6 text-center">
            <div
                class="mx-auto w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center mb-3"
            >
                <svg
                    class="w-8 h-8 text-red-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                    />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
                {{ $t('profile.accountBlocked') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $t('profile.youHaveBlockedThisAccount') }}
            </p>
        </div>

        <div v-else>
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <img
                        :src="displayData.avatar"
                        :alt="displayData.name"
                        class="w-16 h-16 rounded-full object-cover ring-4 ring-white dark:ring-gray-800"
                        @error="handleImageError"
                    />

                    <router-link
                        v-if="isCurrentUser"
                        :to="`/@${displayData.username}`"
                        class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-semibold rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                        {{ $t('profile.editProfile') }}
                    </router-link>

                    <button
                        v-else-if="userState?.pending_follow_request"
                        @click="handleCancelRequest"
                        :disabled="actionLoading"
                        class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-semibold rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                    >
                        {{ actionLoading ? 'Loading...' : 'Requested' }}
                    </button>

                    <button
                        v-else-if="userState?.following"
                        @click="handleUnfollow"
                        :disabled="actionLoading"
                        class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-semibold rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                    >
                        {{ actionLoading ? 'Loading...' : $t('common.following') }}
                    </button>

                    <button
                        v-else-if="!isCurrentUser"
                        @click="handleFollow"
                        :disabled="actionLoading"
                        class="px-4 py-1.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-semibold rounded-full hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                    >
                        {{ actionLoading ? 'Loading...' : $t('common.follow') }}
                    </button>
                </div>

                <div class="mb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">
                        {{ displayData.name }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @{{ displayData.username }}
                    </p>
                </div>

                <p
                    v-if="displayData.bio"
                    class="text-sm text-gray-900 dark:text-gray-100 leading-relaxed mb-3 line-clamp-3"
                >
                    {{ displayData.bio }}
                </p>
            </div>

            <div class="px-4 pb-4 flex items-center gap-4 text-sm">
                <div class="flex items-center gap-1">
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ formatCount(displayData.post_count) }}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ displayData.post_count === 1 ? 'Video' : 'Videos' }}
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ formatCount(displayData.following_count) }}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">Following</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ formatCount(displayData.followers_count) }}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ displayData.followers_count === 1 ? 'Follower' : 'Followers' }}
                    </span>
                </div>
            </div>
        </div>

        <div
            v-if="loading"
            class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center"
        >
            <Spinner />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAccountCardStore } from '@/stores/accountCard'
import { useUtils } from '@/composables/useUtils'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    account: {
        type: Object,
        default: null
    },
    accountId: {
        type: Number,
        default: null
    },
    position: {
        type: Object,
        default: () => ({ top: '0px', left: '0px' })
    },
    currentUserId: {
        type: Number,
        default: null
    }
})

const emit = defineEmits(['mouseenter', 'mouseleave'])

const { formatNumber, formatCount } = useUtils()
const userInteractionsStore = useAccountCardStore()
const loading = ref(false)
const actionLoading = ref(false)
const userState = ref(null)

const displayData = computed(() => {
    if (props.account) {
        return props.account
    }
    return userInteractionsStore.getUserInfo(props.accountId)
})

const isCurrentUser = computed(() => {
    return displayData.value && props.currentUserId && displayData.value.id === props.currentUserId
})

const isUnavailable = computed(() => {
    return displayData.value?.username === 'user'
})

const needsFullData = computed(() => {
    if (!props.account) return true

    const requiredFields = ['bio', 'videos_count', 'following_count', 'followers_count']
    return requiredFields.some((field) => !(field in props.account))
})

onMounted(async () => {
    if (!props.accountId || isUnavailable.value || isCurrentUser.value) return

    try {
        userState.value = await userInteractionsStore.fetchUserState(props.accountId)
    } catch (error) {
        console.error('Failed to fetch user state on mount:', error)
    }
})

const fetchData = async () => {
    if (!props.accountId || isUnavailable.value) return

    loading.value = true

    try {
        if (needsFullData.value) {
            await userInteractionsStore.fetchUserInfo(props.accountId)
        }

        if (!isCurrentUser.value && userState.value) {
            userState.value = await userInteractionsStore.fetchUserState(props.accountId)
        }
    } catch (error) {
        console.error('Failed to fetch user data:', error)
    } finally {
        loading.value = false
    }
}

watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            fetchData()
        }
    }
)

const handleFollow = async () => {
    if (!props.accountId || actionLoading.value) return

    actionLoading.value = true
    try {
        await userInteractionsStore.follow(props.accountId)
        userState.value = await userInteractionsStore.fetchUserState(props.accountId)
    } catch (error) {
        console.error('Failed to follow:', error)
    } finally {
        actionLoading.value = false
    }
}

const handleUnfollow = async () => {
    if (!props.accountId || actionLoading.value) return

    actionLoading.value = true
    try {
        await userInteractionsStore.unfollow(props.accountId)
        userState.value = await userInteractionsStore.fetchUserState(props.accountId)
    } catch (error) {
        console.error('Failed to unfollow:', error)
    } finally {
        actionLoading.value = false
    }
}

const handleCancelRequest = async () => {
    if (!props.accountId || actionLoading.value) return

    actionLoading.value = true
    try {
        await userInteractionsStore.cancelFollowRequest(props.accountId)
        userState.value = await userInteractionsStore.fetchUserState(props.accountId)
    } catch (error) {
        console.error('Failed to cancel request:', error)
    } finally {
        actionLoading.value = false
    }
}

const handleImageError = (event) => {
    event.target.src = '/storage/avatars/default.jpg'
}
</script>
