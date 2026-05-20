<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Playlists</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Monitor and manage user video playlists
            </p>
        </div>

        <DataTable
            title="Playlists"
            :columns="columns"
            :data="hashtags"
            :loading="loading"
            :has-previous="pagination.prev_cursor !== null"
            :has-next="pagination.next_cursor !== null"
            :has-actions="false"
            :sort-options="sortOptions"
            @search="handleSearch"
            @refresh="fetchPlaylists"
            @previous="previousPage"
            @sort="handleSort"
            @next="nextPage"
        >
            <template #cell-id="{ value }">
                <router-link
                    :to="`/admin/playlists/${value}`"
                    :title="value"
                    class="text-xs text-blue-500 hover:text-blue-300"
                >
                    {{ truncateMiddle(value, 12) }}
                </router-link>
            </template>

            <template #cell-cover_image="{ value }">
                <div class="flex items-center">
                    <img v-if="value" :src="value" class="w-9 h-16 rounded object-cover" />
                    <div
                        v-else
                        class="w-9 h-16 bg-gray-300 dark:bg-gray-900 rounded flex items-center justify-center"
                    >
                        <PhotoIcon class="w-4 h-4" />
                    </div>
                </div>
            </template>

            <template #cell-profile="{ value }">
                <router-link :to="`/admin/profiles/${value.id}`">
                    <div class="flex items-center min-w-0">
                        <img
                            :src="value.avatar"
                            :alt="value.username"
                            class="w-8 h-8 rounded-full mr-2 flex-shrink-0"
                            @error="$event.target.src = '/storage/avatars/default.jpg'"
                        />
                        <div class="min-w-0 max-w-[180px]">
                            <span
                                class="font-bold truncate block"
                                :class="[value.username.length > 30 ? 'text-xs' : 'text-sm']"
                                >{{ value.username }}</span
                            >
                        </div>
                    </div>
                </router-link>
            </template>

            <template #cell-videos_count="{ value }">
                {{ formatNumber(value) }}
            </template>

            <template #cell-created_at="{ value }">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatRecentDate(value) }}
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DataTable from '@/components/DataTable.vue'
import ToggleSwitch from '@/components/Form/ToggleSwitch.vue'
import { playlistsApi } from '@/services/adminApi'
import { useUtils } from '@/composables/useUtils'
import { PhotoIcon } from '@heroicons/vue/24/outline'
const { formatNumber, formatDate, formatRecentDate, truncateMiddle } = useUtils()

const route = useRoute()
const router = useRouter()

const hashtags = ref([])
const loading = ref(false)
const pagination = ref({
    cursor: null,
    prev: false,
    next: false
})

const showModal = ref(false)
const selectedHashtag = ref(null)
const saving = ref(false)
const formData = ref({
    can_autolink: false,
    can_search: false,
    can_trend: false,
    is_banned: false,
    is_nsfw: false
})

const searchQuery = ref('')
const sortBy = ref('')
const hydrating = ref(true)

const currentCursor = ref(null)
const currentDir = ref('next')

const DEBOUNCE_DELAY = 300
let searchTimeout = null

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'cover_image', label: 'Cover' },
    { key: 'name', label: 'Name' },
    { key: 'videos_count', label: 'Video count' },
    { key: 'profile', label: 'Creator' },
    { key: 'visibility', label: 'Visibility' },
    { key: 'created_at', label: 'Created At' }
]

const sortOptions = [
    { name: 'Newest', value: 'created_at_desc' },
    { name: 'Oldest', value: 'created_at_asc' }
]

const setQuery = (updates, { replace = false } = {}) => {
    const nextQuery = { ...route.query, ...updates }
    Object.keys(nextQuery).forEach((k) => {
        if (nextQuery[k] === '' || nextQuery[k] === null || nextQuery[k] === undefined) {
            nextQuery[k] = undefined // removes param
        }
    })

    const same =
        Object.keys(nextQuery).length === Object.keys(route.query).length &&
        Object.keys(nextQuery).every((k) => nextQuery[k] === route.query[k])

    if (same) return Promise.resolve()

    const nav = { query: nextQuery }
    return replace ? router.replace(nav) : router.push(nav)
}

const fetchPlaylists = async (cursor = null, direction = 'next') => {
    loading.value = true
    try {
        const params = { cursor, direction }
        if (searchQuery.value) params.search = searchQuery.value
        if (sortBy.value) params.sort = sortBy.value

        const response = await playlistsApi.getPlaylists(params)
        hashtags.value = response.data
        pagination.value = response.meta

        currentCursor.value = cursor
        currentDir.value = direction
    } catch (error) {
        console.error('Error fetching hashtags:', error)
    } finally {
        loading.value = false
    }
}

const openModal = (hashtag) => {
    selectedHashtag.value = hashtag
    formData.value = {
        can_autolink: hashtag.can_autolink || false,
        can_search: hashtag.can_search || false,
        can_trend: hashtag.can_trend || false,
        is_banned: hashtag.is_banned || false,
        is_nsfw: hashtag.is_nsfw || false
    }
    showModal.value = true
    setQuery({ id: hashtag.id })
}

const closeModal = () => {
    showModal.value = false
    selectedHashtag.value = null
    saving.value = false
    setQuery({ id: undefined }, { replace: true })
}

const saveSettings = async () => {
    if (!selectedHashtag.value) return

    saving.value = true
    try {
        await hashtagsApi.updateHashtag(selectedHashtag.value.id, formData.value)
        const index = hashtags.value.findIndex((h) => h.id === selectedHashtag.value.id)
        if (index !== -1) {
            hashtags.value[index] = {
                ...hashtags.value[index],
                ...formData.value
            }
        }
        closeModal()
    } catch (error) {
        console.error('Error updating hashtag:', error)
    } finally {
        saving.value = false
    }
}

watch(searchQuery, () => {
    if (hydrating.value) return
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        setQuery({
            q: searchQuery.value || undefined,
            id: undefined,
            cursor: undefined,
            dir: undefined
        })
        fetchPlaylists(null, 'next')
    }, DEBOUNCE_DELAY)
})

const handleSearch = (query) => {
    searchQuery.value = query
}

watch(sortBy, () => {
    if (hydrating.value) return
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        setQuery({
            sort: sortBy.value || undefined,
            cursor: undefined,
            dir: undefined
        })
        fetchPlaylists(null, 'next')
    }, DEBOUNCE_DELAY)
})

const handleSort = (sortValue) => {
    sortBy.value = sortValue
}

const nextPage = () => {
    if (pagination.value.next_cursor) {
        const next = pagination.value.next_cursor
        setQuery({ cursor: next, dir: 'next' })
        fetchPlaylists(next, 'next')
    }
}

const previousPage = () => {
    if (pagination.value.prev_cursor) {
        const prev = pagination.value.prev_cursor
        setQuery({ cursor: prev, dir: 'previous' })
        fetchPlaylists(prev, 'previous')
    }
}

const openModalById = async (id) => {
    if (!id) return
    let tag = hashtags.value.find((h) => String(h.id) === String(id))

    if (!tag) {
        try {
            const numericId = Number(id)
            const res = await hashtagsApi.getHashtag(numericId)
            tag = res?.data || res
        } catch (e) {
            console.error('Unable to load hashtag by id:', id, e)
        }
    }

    if (tag) {
        openModal(tag)
    } else {
        setQuery({ id: undefined }, { replace: true })
    }
}

onMounted(async () => {
    const { q, id, sort, cursor, dir } = route.query

    if (typeof sort === 'string') sortBy.value = sort
    if (typeof q === 'string') searchQuery.value = q

    currentCursor.value = typeof cursor === 'string' ? cursor : null
    currentDir.value = dir === 'previous' ? 'previous' : 'next'

    await fetchPlaylists(currentCursor.value, currentDir.value)

    if (id) await openModalById(id)

    hydrating.value = false
})

watch(
    () => route.query.id,
    async (newId, oldId) => {
        if (hydrating.value) return
        if (newId === oldId) return

        if (newId) {
            await openModalById(newId)
        } else if (showModal.value) {
            showModal.value = false
            selectedHashtag.value = null
        }
    }
)
</script>
