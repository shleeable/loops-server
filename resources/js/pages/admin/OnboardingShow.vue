<template>
    <div>
        <router-link
            to="/admin/curated-onboarding"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors mb-6"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                />
            </svg>
            Back to Onboarding Applications
        </router-link>

        <div v-if="loading && !app" class="flex flex-col items-center justify-center py-24 gap-3">
            <Spinner />
            <span class="text-sm text-gray-400 dark:text-gray-500">Loading application...</span>
        </div>

        <div v-else-if="!loading && !app" class="max-w-2xl mx-auto p-6">
            <div class="text-center space-y-6">
                <div class="flex justify-center">
                    <div
                        class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center"
                    >
                        <UserPlusIcon class="w-10 h-10 text-gray-400" />
                    </div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-200">
                        Onboarding Application Not Found
                    </h1>
                    <p class="text-gray-600 dark:text-gray-500 max-w-md mx-auto">
                        The curated onboarding application you're looking for doesn't exist or may
                        have been removed. Please check the URL or search for a different profile.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                    <button
                        @click="router.push('/admin/curated-onboarding')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 hover:dark:bg-gray-950 transition-colors duration-200 cursor-pointer"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Go Back
                    </button>
                </div>
            </div>
        </div>

        <template v-else-if="app">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div class="min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                            {{ app.email }}
                        </h1>
                        <StatusBadge :status="app.status" size="lg" />
                    </div>
                    <p
                        v-if="app.username_requested"
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Requested username:
                        <span class="font-medium text-gray-700 dark:text-gray-300"
                            >@{{ app.username_requested }}</span
                        >
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-900/80 overflow-hidden shadow-sm"
                    >
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-900 dark:text-white">
                                Application Details
                            </h2>
                        </div>
                        <div class="p-5 space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <FieldItem label="Age at Submission">
                                    <div class="flex items-center gap-2">
                                        <span>{{ app.age_at_submission }}</span>
                                        <span
                                            v-if="app.age_at_submission < 18"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300"
                                        >
                                            Under 18
                                        </span>
                                    </div>
                                </FieldItem>

                                <FieldItem label="Email Verified">
                                    <span
                                        class="inline-flex items-center gap-1.5"
                                        :class="
                                            app.email_verified
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-gray-400 dark:text-gray-500'
                                        "
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                v-if="app.email_verified"
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                            <path
                                                v-else
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        {{ app.email_verified ? 'Verified' : 'Not verified' }}
                                    </span>
                                </FieldItem>
                            </div>

                            <FieldItem v-if="app.fediverse_account" label="Fediverse Account">
                                <a
                                    :href="fediverseProfileUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
                                >
                                    {{ app.fediverse_account }}
                                    <svg
                                        class="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                        />
                                    </svg>
                                </a>
                            </FieldItem>

                            <FieldItem label="Why they want to join">
                                <p class="whitespace-pre-wrap leading-relaxed">{{ app.reason }}</p>
                            </FieldItem>
                        </div>
                    </div>

                    <div
                        v-if="customQuestions.length"
                        class="rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-900/80 overflow-hidden shadow-sm"
                    >
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-900 dark:text-white">
                                Custom Questions
                            </h2>
                        </div>
                        <div class="p-5 space-y-5">
                            <FieldItem v-for="qa in customQuestions" :key="qa.id" :label="qa.label">
                                <p class="whitespace-pre-wrap leading-relaxed">
                                    {{ qa.answer || '\u2014' }}
                                </p>
                            </FieldItem>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-900/80 overflow-hidden shadow-sm"
                    >
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-900 dark:text-white">
                                Internal Notes
                            </h2>
                        </div>
                        <div class="p-5">
                            <div class="flex gap-2 mb-5">
                                <input
                                    v-model="newNote"
                                    type="text"
                                    placeholder="Add an internal note..."
                                    @keydown.enter="handleAddNote"
                                    class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors"
                                />
                                <AnimatedButton
                                    variant="light"
                                    :disabled="!newNote.trim() || noteLoading"
                                    @click="handleAddNote"
                                >
                                    Add
                                </AnimatedButton>
                            </div>

                            <div v-if="app.notes?.length" class="space-y-5">
                                <div v-for="note in app.notes" :key="note.id" class="flex gap-3">
                                    <img
                                        v-if="note.admin.avatar"
                                        :src="note.admin.avatar"
                                        :alt="note.admin.name"
                                        class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                    />
                                    <div
                                        v-else
                                        class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-xs font-bold text-white"
                                    >
                                        {{ note.admin.username.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-baseline gap-2">
                                            <div
                                                class="flex items-baseline justify-between flex-grow-1"
                                            >
                                                <div class="flex items-baseline gap-2">
                                                    <span
                                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                                        >{{ note.admin.username }}</span
                                                    >
                                                    <span
                                                        class="text-xs text-gray-400 dark:text-gray-500"
                                                        >{{ formatRelative(note.created_at) }}</span
                                                    >
                                                </div>
                                                <button @click="handleDeleteNote(note)">
                                                    <TrashIcon class="size-3 text-red-500" />
                                                </button>
                                            </div>
                                        </div>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 leading-relaxed"
                                        >
                                            {{ note.body }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p
                                v-else
                                class="text-sm text-gray-400 dark:text-gray-500 text-center py-4"
                            >
                                No notes yet.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        v-if="app.status === 'pending'"
                        class="rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-900/80 p-5 space-y-3 shadow-sm"
                    >
                        <h3 class="font-semibold text-gray-900 dark:text-white">Actions</h3>
                        <div class="flex flex-col gap-3">
                            <AnimatedButton
                                variant="outline"
                                class="w-full"
                                :disabled="!!actionLoading"
                                @click="showRejectModal = true"
                            >
                                <div class="flex items-center justify-center gap-2">
                                    <XCircleIcon class="w-4 h-4" />
                                    Reject Application
                                </div>
                            </AnimatedButton>
                        </div>
                    </div>

                    <div
                        v-else-if="app.status === 'ready'"
                        class="rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-900/80 p-5 space-y-3 shadow-sm"
                    >
                        <h3 class="font-semibold text-gray-900 dark:text-white">Actions</h3>
                        <div class="flex flex-col gap-3">
                            <AnimatedButton
                                v-if="app.email_verified"
                                variant="primaryGradient"
                                class="w-full"
                                :loading="actionLoading === 'approve'"
                                :disabled="!!actionLoading"
                                @click="handleApprove"
                            >
                                <div class="flex items-center justify-center gap-2">
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    Approve Application
                                </div>
                            </AnimatedButton>
                            <AnimatedButton
                                variant="outline"
                                class="w-full"
                                :disabled="!!actionLoading"
                                @click="showRejectModal = true"
                            >
                                <div class="flex items-center justify-center gap-2">
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                    Reject Application
                                </div>
                            </AnimatedButton>
                        </div>
                    </div>

                    <div
                        v-else-if="app.status === 'approved'"
                        class="rounded-2xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/20 p-5"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <svg
                                class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <h3 class="font-semibold text-emerald-800 dark:text-emerald-300">
                                Approved
                            </h3>
                        </div>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400/80">
                            This application has been approved and the account has been created.
                        </p>
                    </div>

                    <div v-else-if="app.status === 'rejected'" class="space-y-4">
                        <div
                            class="rounded-2xl border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 p-5"
                        >
                            <h3 class="font-semibold text-red-800 dark:text-red-300 mb-1">
                                Application Rejected
                            </h3>
                            <p class="text-sm text-red-700 dark:text-red-400/80">
                                {{ app.status_reason || 'No reason provided' }}
                            </p>
                        </div>

                        <AnimatedButton
                            variant="outline"
                            class="w-full"
                            :disabled="!!actionLoading"
                            @click="handleDeleteApplication"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <TrashIcon class="w-4 h-4" />
                                Delete Rejected Application
                            </div>
                        </AnimatedButton>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-900/80 p-5 shadow-sm"
                    >
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Metadata</h3>
                        <dl class="space-y-3 text-sm">
                            <MetaItem label="Submitted" :value="formatDate(app.created_at)" />
                            <MetaItem
                                v-if="app.email_verified"
                                label="Email Verified"
                                :value="formatDate(app.email_verified_at)"
                            />
                            <MetaItem
                                v-if="app.reviewed_at"
                                label="Reviewed"
                                :value="formatDate(app.reviewed_at)"
                            />
                            <MetaItem
                                v-if="app.reviewer"
                                label="Reviewed by"
                                :value="app.reviewer.username"
                            />
                            <div v-if="app.user_id">
                                <dt class="text-gray-500 dark:text-gray-400">Account</dt>
                                <dd class="mt-0.5">
                                    <div class="flex items-center gap-2 my-3">
                                        <img
                                            :src="app.user.avatar"
                                            alt="User avatar"
                                            class="size-10 rounded-full"
                                        />
                                        <div class="flex flex-col">
                                            <div class="text-xs text-gray-500 dark:text-gray-300">
                                                {{ app.user.name || app.user.username }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-500 dark:text-gray-300 font-medium"
                                            >
                                                @{{ app.user.username }}
                                            </div>
                                        </div>
                                    </div>
                                    <router-link
                                        :to="`/admin/profiles/${app.user.id}`"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors"
                                    >
                                        View user profile &rarr;
                                    </router-link>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </template>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showRejectModal"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm px-4 pb-4 sm:pb-0"
                    @click.self="
                        () => {
                            showRejectModal = false
                            selectedRejection = ''
                        }
                    "
                >
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-md"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                            Reject Application
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Provide an optional reason. This may be shared with the applicant.
                        </p>
                        <div v-if="app?.custom_rejections?.length" class="mb-3">
                            <label
                                class="block text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5"
                            >
                                Quick Reject Reason
                            </label>
                            <select
                                v-model="selectedRejection"
                                @change="onRejectionPresetChange"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500/40 focus:border-red-500 transition-colors"
                            >
                                <option value="">— Select a preset reason —</option>
                                <option
                                    v-for="rejection in app.custom_rejections"
                                    :key="rejection.id"
                                    :value="rejection.id"
                                >
                                    {{ rejection.title }}
                                </option>
                            </select>
                        </div>
                        <textarea
                            v-model="rejectReason"
                            rows="3"
                            placeholder="Reason for rejection..."
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white resize-none focus:ring-2 focus:ring-red-500/40 focus:border-red-500 transition-colors mb-3"
                        />
                        <label
                            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-5 cursor-pointer select-none"
                        >
                            <input
                                v-model="sendRejectEmail"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500"
                            />
                            Send rejection email to applicant
                        </label>
                        <div class="flex justify-end gap-2">
                            <AnimatedButton variant="light" @click="showRejectModal = false">
                                Cancel
                            </AnimatedButton>
                            <AnimatedButton
                                variant="primaryGradient"
                                :loading="actionLoading === 'reject'"
                                :disabled="!!actionLoading"
                                @click="handleReject"
                            >
                                Reject
                            </AnimatedButton>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, defineComponent, h } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCuratedApplicationsAdmin } from '@/composables/useCuratedOnboarding'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import AnimatedButton from '@/components/AnimatedButton.vue'
import { ArrowLeftIcon, TrashIcon, UserPlusIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import { useAlertModal } from '@/composables/useAlertModal.js'
import { useI18n } from 'vue-i18n'

const FieldItem = defineComponent({
    props: {
        label: String
    },
    setup(props, { slots }) {
        return () =>
            h('div', [
                h(
                    'label',
                    {
                        class: 'block text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5'
                    },
                    props.label
                ),
                h(
                    'div',
                    {
                        class: 'text-sm text-gray-900 dark:text-white'
                    },
                    slots.default?.()
                )
            ])
    }
})

const MetaItem = defineComponent({
    props: {
        label: String,
        value: String
    },
    setup(props) {
        return () =>
            h('div', [
                h('dt', { class: 'text-gray-500 dark:text-gray-400' }, props.label),
                h('dd', { class: 'text-gray-900 dark:text-white mt-0.5 font-medium' }, props.value)
            ])
    }
})

const route = useRoute()
const router = useRouter()

const {
    currentApplication: app,
    loading,
    fetchApplication,
    approve,
    forceApprove,
    reject,
    addNote,
    deleteApplicationNote,
    deleteApplication
} = useCuratedApplicationsAdmin()

const { t } = useI18n()
const customQuestions = ref([])
const newNote = ref('')
const noteLoading = ref(false)
const actionLoading = ref(null)
const showRejectModal = ref(false)
const rejectReason = ref('')
const sendRejectEmail = ref(true)
const { alertModal, confirmModal } = useAlertModal()
const selectedRejection = ref('')

function onRejectionPresetChange() {
    const preset = app.value?.custom_rejections?.find((r) => r.id === selectedRejection.value)
    rejectReason.value = preset?.reason ?? ''
}

const fediverseProfileUrl = computed(() => {
    if (!app.value?.fediverse_account) return '#'
    const handle = app.value.fediverse_account.replace(/^@/, '')
    const [user, domain] = handle.split('@')
    return domain ? `https://${domain}/@${user}` : '#'
})

async function loadApplication() {
    const res = await fetchApplication(route.params.id)
    if (res?.custom_questions) {
        customQuestions.value = res.custom_questions
    }
}

async function handleApprove() {
    const result = await confirmModal(
        'Confirm Approve Application',
        `Are you sure you want to approve this application?<br/><br/><div class="text-gray-500 dark:text-gray-200">Approve to deliver an onboarding email to <strong>${app.value.email}</strong></div>`,
        'Approve',
        t('common.cancel')
    )
    if (result) {
        actionLoading.value = 'approve'
        try {
            await approve(app.value.id)
            await loadApplication()
        } finally {
            actionLoading.value = null
        }
    }
}

async function handleManualApprove() {
    const result = await confirmModal(
        'Confirm Manual Approval',
        `Are you sure you want to approve this application even though <strong>they have not verified their email address</strong>?<br /><br /><strong class="text-red-500">They may not receive this onboarding email if there was a typo or their email is incomplete.</strong><br/><br/>Use this at your own risk, you were warned!<br /><br /><div class="text-gray-500 dark:text-gray-200">Approve to deliver an onboarding email to <strong>${app.value.email}</strong></div>`,
        'Approve',
        t('common.cancel')
    )
    if (result) {
        actionLoading.value = 'approve'
        try {
            await forceApprove(app.value.id)
            await loadApplication()
        } finally {
            actionLoading.value = null
        }
    }
}

async function handleReject() {
    actionLoading.value = 'reject'
    try {
        await reject(app.value.id, rejectReason.value || null, sendRejectEmail.value)
        showRejectModal.value = false
        rejectReason.value = ''
        selectedRejection.value = ''
        await loadApplication()
    } finally {
        actionLoading.value = null
    }
}

async function handleAddNote() {
    if (!newNote.value.trim()) return
    noteLoading.value = true
    try {
        await addNote(app.value.id, newNote.value.trim())
        newNote.value = ''
        await loadApplication()
    } finally {
        noteLoading.value = false
    }
}

async function handleDeleteNote(note) {
    const result = await confirmModal(
        'Confirm Note Delete',
        `Are you sure you want to delete this note<br/><strong>${note.body}</strong>?`,
        t('common.delete'),
        t('common.cancel')
    )

    if (result) {
        await deleteApplicationNote(app.value.id, note.id)
    }
}

async function handleDeleteApplication() {
    const result = await confirmModal(
        'Confirm Delete',
        `Are you sure you want to delete this application?`,
        t('common.delete'),
        t('common.cancel')
    )

    if (result) {
        await deleteApplication(app.value.id).finally(() => {
            router.push('/admin/curated-onboarding')
        })
    }
}

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    })
}

function formatRelative(iso) {
    const diff = Date.now() - new Date(iso).getTime()
    const minutes = Math.floor(diff / 60000)
    if (minutes < 1) return 'just now'
    if (minutes < 60) return `${minutes}m ago`
    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours}h ago`
    const days = Math.floor(hours / 24)
    return `${days}d ago`
}

onMounted(() => {
    loadApplication()
})
</script>
