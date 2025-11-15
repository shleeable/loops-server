<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Instances
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Manage fediverse instances
                </p>
            </div>
            <div
                class="grid grid-cols-1 gap-px bg-gradient-to-b from-gray-100/50 to-gray-400/80 dark:from-gray-600/50 dark:to-gray-800/70 sm:grid-cols-2 lg:grid-cols-4 rounded-lg border border-gray-300 dark:border-gray-600/70 overflow-hidden transition-all duration-300 ease-in-out"
            >
                <div
                    v-for="stat in instanceStats"
                    :key="stat.name"
                    class="relative bg-gradient-to-b from-white to-gray-50/80 dark:from-gray-900 dark:to-gray-800/90 px-4 py-3 sm:px-6 hover:from-gray-50/50 hover:to-gray-100/60 dark:hover:from-gray-800/90 dark:hover:to-gray-700/80 transition-all duration-200 ease-in-out overflow-hidden"
                >
                    <p
                        class="text-sm/5 font-medium text-gray-600 dark:text-gray-300"
                    >
                        {{ stat.name }}
                    </p>
                    <p class="mt-1 flex items-baseline gap-x-2">
                        <span
                            class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white"
                            >{{ stat.value }}</span
                        >
                        <span
                            v-if="stat.unit"
                            class="text-sm text-gray-500 dark:text-gray-400"
                            >{{ stat.unit }}</span
                        >
                    </p>
                </div>
            </div>
            <AnimatedButton @click="openAddModal">
                Add Instance
            </AnimatedButton>
        </div>

        <DataTable
            title="Instances"
            :columns="columns"
            :data="instances"
            :loading="loading"
            :has-previous="pagination.prev_cursor"
            :has-next="pagination.next_cursor"
            :has-actions="false"
            :sort-options="sortOptions"
            :initialSearchQuery="searchQuery"
            @sort="handleSort"
            @search="handleSearch"
            @refresh="fetchInstances"
            @previous="previousPage"
            @next="nextPage"
        >
            <template #cell-id="{ value }">
                <router-link
                    :to="`/admin/instances/${value}`"
                    :title="value"
                    class="text-xs text-blue-500 hover:text-blue-300"
                >
                    {{ value }}
                </router-link>
            </template>

            <template #cell-user_count="{ value }">
                <div>
                    {{ formatNumber(value) }}
                </div>
            </template>

            <template #cell-video_count="{ value }">
                <div>
                    {{ formatNumber(value) }}
                </div>
            </template>

            <template #cell-follower_count="{ value }">
                <div>
                    {{ formatNumber(value) }}
                </div>
            </template>

            <template #cell-software="{ value }">
                <button
                    class="text-blue-500 cursor-pointer"
                    @click="toggleSoftwareSearch(value)"
                >
                    {{ value }}
                </button>
            </template>

            <template #cell-status="{ value }">
                <div
                    :class="[
                        value === 'active' ? 'text-green-600' : 'text-red-600',
                    ]"
                >
                    {{ value }}
                </div>
            </template>

            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>
        </DataTable>

        <div
            v-if="showAddModal"
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            >
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-xl font-bold text-gray-900 dark:text-white"
                        >
                            Add Instance{{ addMode === "mass" ? "s" : "" }}
                        </h2>
                        <div class="flex items-center">
                            <div
                                class="flex bg-gray-100 dark:bg-slate-900 rounded-lg p-1"
                            >
                                <button
                                    @click="addMode = 'single'"
                                    :class="[
                                        'px-3 py-1.5 text-[10px] lg:text-[14px] font-medium rounded-md transition-all duration-200 relative cursor-pointer',
                                        addMode === 'single'
                                            ? 'bg-white dark:bg-slate-700 text-black dark:text-white shadow-sm'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200',
                                    ]"
                                >
                                    Single Instance
                                </button>
                                <button
                                    @click="addMode = 'mass'"
                                    :class="[
                                        'px-3 py-1.5 text-[10px] lg:text-[14px] font-medium rounded-md transition-all duration-200 relative cursor-pointer',
                                        addMode === 'mass'
                                            ? 'bg-white dark:bg-slate-700 text-black dark:text-white shadow-sm'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200',
                                    ]"
                                >
                                    Bulk Add
                                </button>
                            </div>
                        </div>
                        <div class="w-[120px] flex justify-end">
                            <button
                                @click="closeAddModal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="handleSubmit">
                        <div v-if="addMode === 'single'" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Domain
                                </label>
                                <input
                                    v-model="form.domain"
                                    type="text"
                                    placeholder="example.com"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                            </div>
                        </div>

                        <div v-if="addMode === 'mass'" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Domains (comma-separated)
                                </label>
                                <textarea
                                    v-model="form.domains"
                                    rows="6"
                                    :disabled="submitting"
                                    placeholder="example1.com, example2.com, example3.com"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                                    required
                                ></textarea>
                                <div class="flex justify-between items-center">
                                    <div
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Enter domains separated by commas,
                                        spaces, or new lines
                                        <span
                                            v-if="
                                                bulkDomainCount >
                                                MAX_BULK_DOMAIN_LIMIT
                                            "
                                            class="block text-amber-600 mt-1"
                                        >
                                            ⚠️ Large lists will be processed in
                                            batches of
                                            {{ MAX_BULK_DOMAIN_LIMIT }}
                                        </span>
                                    </div>
                                    <div
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <span
                                            :class="[
                                                bulkDomainCount >
                                                MAX_BULK_DOMAIN_LIMIT
                                                    ? 'text-amber-600 font-medium'
                                                    : '',
                                            ]"
                                        >
                                            {{ bulkDomainCount }}
                                        </span>
                                        <span class="text-sm opacity-75 pl-1"
                                            >unique</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 mt-6">
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-white"
                            >
                                Options
                            </h3>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Suspended
                                        </label>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            Block federation and discovery
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        :disabled="submitting"
                                        @click="
                                            form.is_blocked = !form.is_blocked
                                        "
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50',
                                            form.is_blocked
                                                ? 'bg-red-600'
                                                : 'bg-gray-200 dark:bg-gray-700',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                form.is_blocked
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        ></span>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Allow Video Posts
                                        </label>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            Accept and process incoming
                                            top-level video posts that can
                                            appear in feeds
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        :disabled="submitting"
                                        @click="
                                            form.allow_video_posts =
                                                !form.allow_video_posts
                                        "
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50',
                                            form.allow_video_posts
                                                ? 'bg-green-600'
                                                : 'bg-gray-200 dark:bg-gray-700',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                form.allow_video_posts
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        ></span>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <label
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Allow Videos in FYF
                                        </label>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            Process videos for use in the For
                                            You Feed algorithm
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        :disabled="submitting"
                                        @click="
                                            form.allow_videos_in_fyf =
                                                !form.allow_videos_in_fyf
                                        "
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50',
                                            form.allow_videos_in_fyf
                                                ? 'bg-green-600'
                                                : 'bg-gray-200 dark:bg-gray-700',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                form.allow_videos_in_fyf
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        ></span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Admin Notes
                                </label>
                                <textarea
                                    v-model="form.admin_notes"
                                    rows="3"
                                    :disabled="submitting"
                                    placeholder="Add any admin notes about this instance..."
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                                ></textarea>
                            </div>
                        </div>

                        <div
                            class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
                        >
                            <button
                                type="button"
                                @click="closeAddModal"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                :disabled="submitting"
                            >
                                Cancel
                            </button>
                            <AnimatedButton
                                type="submit"
                                :disabled="submitting || canSubmitAddInstances"
                            >
                                <span v-if="!submitting">
                                    Add
                                    {{
                                        addMode === "mass"
                                            ? `${bulkDomainCount} Instance${bulkDomainCount !== 1 ? "s" : ""}`
                                            : "Instance"
                                    }}
                                </span>
                                <span v-else class="flex items-center">
                                    <svg
                                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    {{
                                        addMode === "mass"
                                            ? "Processing..."
                                            : "Adding..."
                                    }}
                                </span>
                            </AnimatedButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted, computed, nextTick } from "vue";
import { useRouter } from "vue-router";
import DataTable from "@/components/DataTable.vue";
import { instancesApi } from "@/services/adminApi";
import { useUtils } from "@/composables/useUtils";
import { useAlertModal } from "@/composables/useAlertModal.js";

const { alertModal, confirmModal } = useAlertModal();
const {
    truncateMiddle,
    formatDate,
    formatNumber,
    normalizeDomain,
    isValidDomain,
} = useUtils();

const router = useRouter();
const profiles = ref([]);
const instances = ref([]);
const instanceStats = ref([]);
const loading = ref(false);
const pagination = ref({
    cursor: null,
    hasPrevious: false,
    hasNext: false,
});

const searchQuery = ref("");
const sortBy = ref("");
const DEBOUNCE_DELAY = 300;
const MAX_BULK_DOMAIN_LIMIT = 100;
let searchTimeout = null;

const showAddModal = ref(false);
const addMode = ref("single");
const submitting = ref(false);
const submitError = ref(false);
const submitErrorMessage = ref("");

const form = ref({
    domain: "",
    domains: "",
    is_blocked: false,
    allow_video_posts: false,
    allow_videos_in_fyf: false,
    admin_notes: "",
});

const columns = [
    { key: "id", label: "ID" },
    { key: "domain", label: "Domain" },
    { key: "user_count", label: "Users" },
    { key: "video_count", label: "Videos" },
    { key: "follower_count", label: "Followers" },
    { key: "software", label: "Software" },
    { key: "status", label: "Status" },
    { key: "created_at", label: "First Seen" },
];

const sortOptions = [
    { name: "Domain A-Z", value: "domain_asc" },
    { name: "Domain Z-A", value: "domain_desc" },
    { name: "Most Followers", value: "follower_count_desc" },
    { name: "Most Videos", value: "video_count_desc" },
    { name: "Newest", value: "created_at_desc" },
    { name: "Oldest", value: "created_at_asc" },
    { name: "Suspended", value: "is_blocked" },
    { name: "Last Updated", value: "updated_at_desc" },
];

const fetchInstanceStats = async () => {
    const stats = await instancesApi.getInstanceStats();
    instanceStats.value = stats.data;
};

const fetchInstances = async (cursor = null, direction = "next") => {
    loading.value = true;
    try {
        const params = { cursor, direction };

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        if (sortBy.value) {
            params.sort = sortBy.value;
        }

        const response = await instancesApi.getInstances(params);
        instances.value = response.data;
        pagination.value = response.meta;
    } catch (error) {
        console.error("Error fetching profiles:", error);
    } finally {
        loading.value = false;
    }
};

// Modal functions
const openAddModal = () => {
    showAddModal.value = true;
    resetForm();
};

const closeAddModal = () => {
    showAddModal.value = false;
    resetForm();
};

const resetForm = () => {
    form.value = {
        domain: "",
        domains: "",
        is_blocked: false,
        allow_video_posts: false,
        allow_videos_in_fyf: false,
        admin_notes: "",
    };
    addMode.value = "single";
};

const addSingleInstance = async () => {
    const instanceData = {
        domain: "https://" + form.value.domain.trim(),
        is_blocked: form.value.is_blocked,
        allow_video_posts: form.value.allow_video_posts,
        allow_videos_in_fyf: form.value.allow_videos_in_fyf,
        admin_notes: form.value.admin_notes.trim(),
    };

    await instancesApi.createInstance(instanceData);
    await fetchInstances();
};

const handleSubmit = async () => {
    submitting.value = true;
    submitError.value = false;
    submitErrorMessage.value = "";

    try {
        if (addMode.value === "single") {
            await addSingleInstance();
        } else {
            await addMassInstancesInChunks();
        }

        closeAddModal();
        await fetchInstances();
    } catch (error) {
        submitError.value = true;
        submitErrorMessage.value =
            error?.response?.data?.message || "Something went wrong!";
        alertModal("Error", submitErrorMessage.value);
        console.error("Error adding instance(s):", error);
    } finally {
        submitting.value = false;
    }
};

const addMassInstancesInChunks = async () => {
    const raw = Array.isArray(form.value.domains)
        ? form.value.domains.join(",")
        : String(form.value.domains ?? "");

    const domains = raw
        .split(/[\s,;\n]+/)
        .map(normalizeDomain)
        .filter(Boolean);

    if (domains.length === 0) {
        throw new Error("No valid domains provided");
    }

    const uniqueDomains = [...new Set(domains)];

    const invalidDomains = uniqueDomains.filter(
        (domain) => !isValidDomain(domain),
    );
    if (invalidDomains.length > 0) {
        throw new Error(
            `Invalid domains found: ${invalidDomains.slice(0, 5).join(", ")}${invalidDomains.length > 5 ? ` and ${invalidDomains.length - 5} more` : ""}`,
        );
    }

    const chunks = [];
    for (let i = 0; i < uniqueDomains.length; i += MAX_BULK_DOMAIN_LIMIT) {
        chunks.push(uniqueDomains.slice(i, i + MAX_BULK_DOMAIN_LIMIT));
    }

    console.log(
        `Processing ${uniqueDomains.length} domains in ${chunks.length} chunk(s)`,
    );

    let processedCount = 0;
    let successCount = 0;
    let errorCount = 0;
    const errors = [];

    for (let i = 0; i < chunks.length; i++) {
        const chunk = chunks[i];

        try {
            submitting.value = true;

            console.log(
                `Processing chunk ${i + 1}/${chunks.length} with ${chunk.length} domains`,
            );

            const domainMap = chunk.map((domain) => "https://" + domain);

            const instancesData = {
                domains: domainMap,
                is_blocked: form.value.is_blocked,
                allow_video_posts: form.value.allow_video_posts,
                allow_videos_in_fyf: form.value.allow_videos_in_fyf,
                admin_notes: form.value.admin_notes.trim(),
            };

            await instancesApi.createInstances(instancesData);
            successCount += chunk.length;
        } catch (error) {
            console.error(`Error processing chunk ${i + 1}:`, error);
            errorCount += chunk.length;
            errors.push({
                chunk: i + 1,
                domains: chunk,
                error: error?.response?.data?.message || error.message,
            });
        }

        processedCount += chunk.length;

        if (i < chunks.length - 1) {
            await new Promise((resolve) => setTimeout(resolve, 1000));
        }
    }

    if (errors.length > 0) {
        const errorMessage = `
            <div class="text-left">
                <p class="mb-2"><strong>Bulk operation completed:</strong></p>
                <ul class="text-sm space-y-1">
                    <li class="text-green-600">✓ ${successCount} domains added successfully</li>
                    <li class="text-red-600">✗ ${errorCount} domains failed</li>
                </ul>
                <p class="mt-3 text-sm"><strong>First few errors:</strong></p>
                <ul class="text-xs text-gray-600 mt-1">
                    ${errors
                        .slice(0, 3)
                        .map((e) => `<li>Chunk ${e.chunk}: ${e.error}</li>`)
                        .join("")}
                    ${errors.length > 3 ? `<li>... and ${errors.length - 3} more error(s)</li>` : ""}
                </ul>
            </div>
        `;

        alertModal("Bulk Operation Results", errorMessage);
    } else {
        alertModal(
            "Success",
            `All ${successCount} domains were added successfully!<br /><br /><span class="text-sm text-gray-400 dark:text-gray-500">It may take a few minutes for the domains to appear.</span>`,
        );
    }
};

const canSubmitAddInstances = computed(() => {
    if (addMode.value === "single") {
        const domain = form.value.domain?.trim();
        if (!domain) return true;
        const normalized = normalizeDomain(domain);
        return !isValidDomain(normalized);
    }

    const raw = Array.isArray(form.value.domains)
        ? form.value.domains.join(",")
        : String(form.value.domains ?? "");

    if (!raw.trim()) return true;

    const domains = raw
        .split(/[\s,;\n]+/)
        .map((domain) => domain.trim())
        .map(normalizeDomain)
        .filter(Boolean);

    if (domains.length === 0) return true;

    const unique = [...new Set(domains)];

    return !unique.every(isValidDomain);
});

const bulkDomainCount = computed(() => {
    const raw = Array.isArray(form.value.domains)
        ? form.value.domains.join(",")
        : String(form.value.domains ?? "");

    const domains = raw
        .split(/[\s,;\n]+/)
        .map((domain) => domain.trim())
        .filter((domain) => domain.length > 0);

    const unique = [...new Set(domains.map(normalizeDomain))];
    return unique.length;
});

watch(searchQuery, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        fetchInstances();
    }, DEBOUNCE_DELAY);
});

watch(sortBy, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        fetchInstances();
    }, DEBOUNCE_DELAY);
});

const handleSort = (sortValue) => {
    sortBy.value = sortValue;
};

const handleSearch = (query) => {
    searchQuery.value = query;
};

const toggleSoftwareSearch = (query) => {
    searchQuery.value = "software:" + query;
};

const nextPage = () => {
    if (pagination.value.next_cursor) {
        fetchInstances(pagination.value.next_cursor, "next");
    }
};

const previousPage = () => {
    if (pagination.value.prev_cursor) {
        fetchInstances(pagination.value.prev_cursor, "previous");
    }
};

onUnmounted(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

onMounted(() => {
    fetchInstances();
    fetchInstanceStats();
});
</script>
