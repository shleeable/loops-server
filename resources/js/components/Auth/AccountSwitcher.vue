<template>
    <button
        ref="triggerRef"
        @click="toggle"
        :aria-expanded="open"
        :aria-label="t('accounts.switchAccount')"
        class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 p-2 text-left transition hover:bg-gray-100 dark:border-slate-800 dark:hover:bg-slate-800"
        :class="open ? 'bg-gray-100 dark:bg-slate-800' : ''"
    >
        <div class="relative shrink-0">
            <img
                v-if="active?.avatar"
                :src="active.avatar"
                :alt="active.username"
                class="size-10 rounded-full object-cover ring-2 ring-white dark:ring-slate-900"
            />
            <div
                v-else
                class="size-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 dark:from-gray-700 dark:to-gray-900"
            />
            <span
                v-if="hasMultiple"
                class="absolute -bottom-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#F02C56] px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-slate-950"
            >
                {{ accounts.length }}
            </span>
        </div>

        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                {{ active?.name || active?.username }}
            </p>
            <p class="truncate text-xs text-gray-500 dark:text-slate-400">
                @{{ active?.username }}
            </p>
        </div>

        <ChevronUpDownIcon class="size-5 shrink-0 text-gray-400 dark:text-slate-500" />
    </button>

    <Teleport to="body">
        <Transition
            enter-active-class="duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="open" @click="close" class="fixed inset-0 z-[60]" />
        </Transition>

        <Transition
            enter-active-class="duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                :style="panelStyle"
                class="z-[61] flex origin-top flex-col overflow-y-auto rounded-3xl bg-white p-2 shadow-2xl ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"
            >
                <div class="px-3 pb-2 pt-2">
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400"
                    >
                        {{ t('accounts.signedInAs') }}
                    </p>
                </div>

                <ul class="space-y-1">
                    <li v-for="account in accounts" :key="account.id" class="group relative">
                        <button
                            @click="handleSwitch(account)"
                            :disabled="switching === account.id"
                            class="flex w-full items-center gap-3 rounded-2xl px-3 py-2 text-left transition hover:bg-gray-100 disabled:opacity-50 dark:hover:bg-slate-800"
                            :class="account.is_active && 'bg-gray-50 dark:bg-slate-800/50'"
                        >
                            <img
                                v-if="account.avatar"
                                :src="account.avatar"
                                :alt="account.username"
                                class="size-10 rounded-full object-cover"
                            />
                            <div
                                v-else
                                class="size-10 rounded-full bg-gradient-to-br from-pink-400 to-rose-600"
                            />

                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ account.name || account.username }}
                                </p>
                                <p class="truncate text-xs text-gray-500 dark:text-slate-400">
                                    @{{ account.username }}
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <CheckIcon v-if="account.is_active" class="size-5 text-[#F02C56]" />
                                <span
                                    v-else-if="switching === account.id"
                                    class="size-4 animate-spin rounded-full border-2 border-gray-300 border-t-[#F02C56]"
                                />
                            </div>
                        </button>

                        <button
                            v-if="!account.is_active"
                            @click.stop="handleRemove(account)"
                            :disabled="removing === account.id"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1.5 opacity-0 transition group-hover:opacity-100 hover:bg-red-50 dark:text-gray-500 hover:text-red-600 disabled:opacity-50 dark:hover:bg-red-950"
                            :aria-label="t('accounts.removeAccount')"
                        >
                            <TrashIcon class="size-4" />
                        </button>
                    </li>
                </ul>

                <div class="my-2 h-px bg-gray-100 dark:bg-slate-800" />

                <div class="space-y-1">
                    <button
                        @click="handleAdd"
                        :disabled="!canAdd"
                        class="flex w-full items-center gap-3 rounded-2xl px-3 py-2 text-left text-sm font-medium text-gray-900 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-white dark:hover:bg-slate-800"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800"
                        >
                            <PlusIcon class="size-5" />
                        </div>
                        <span>
                            {{
                                canAdd
                                    ? t('accounts.addAnother')
                                    : t('accounts.maxReached', { max: authStore.linkedMax })
                            }}
                        </span>
                    </button>

                    <router-link
                        to="/dashboard/account"
                        class="flex w-full items-center gap-3 rounded-2xl px-3 py-2 text-left text-sm font-medium text-gray-900 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-white dark:hover:bg-slate-800"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800"
                        >
                            <Cog6ToothIcon class="size-5" />
                        </div>
                        <span> Account Settings </span>
                    </router-link>
                    <button
                        @click="handleLogoutAll"
                        class="flex w-full items-center gap-3 rounded-2xl px-3 py-2 text-left text-sm font-medium text-gray-900 transition hover:bg-gray-100 dark:text-white dark:hover:bg-slate-800"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800"
                        >
                            <ArrowRightOnRectangleIcon class="size-5" />
                        </div>
                        <span>{{
                            accounts.length > 1 ? t('accounts.logoutAll') : t('accounts.signOut')
                        }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
    CheckIcon,
    PlusIcon,
    ArrowRightOnRectangleIcon,
    TrashIcon,
    ChevronUpDownIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline'
import { useI18n } from 'vue-i18n'
import { useAlertModal } from '@/composables/useAlertModal.js'

const { t } = useI18n()
const authStore = useAuthStore()
const { alertModal, confirmModal } = useAlertModal()

const open = ref(false)
const removing = ref(null)
const switching = ref(null)

const emit = defineEmits(['close'])

const windowWidth = ref(window.innerWidth)
const isMobile = computed(() => windowWidth.value < 1024)

const triggerRef = ref(null)
const panelStyle = ref({})

const accounts = computed(() => authStore.linkedAccounts)
const active = computed(() => authStore.activeAccount)
const canAdd = computed(() => authStore.canAddAccount)
const hasMultiple = computed(() => accounts.value.length > 1)

const PANEL_WIDTH = 320
const GAP = 8
const VIEWPORT_PAD = 8

onMounted(async () => {
    if (authStore.authenticated && accounts.value.length === 0) {
        await authStore.fetchLinkedAccounts()
    }
})

onBeforeUnmount(() => {
    teardownListeners()
})

function computePosition() {
    const el = triggerRef.value
    if (!el) return

    const rect = el.getBoundingClientRect()
    const vh = window.innerHeight
    const vw = window.innerWidth

    const estimatedHeight = Math.min(accounts.value.length * 56 + 160, vh - 2 * VIEWPORT_PAD)
    const openUp = rect.bottom + GAP + estimatedHeight > vh

    let left = rect.left
    if (left + PANEL_WIDTH > vw - VIEWPORT_PAD) {
        left = vw - PANEL_WIDTH - VIEWPORT_PAD
    }
    if (left < VIEWPORT_PAD) left = VIEWPORT_PAD

    const style = {
        position: 'fixed',
        left: `${left}px`,
        width: `${PANEL_WIDTH}px`
    }

    if (openUp) {
        style.bottom = `${vh - rect.top + GAP}px`
        style.maxHeight = `${rect.top - GAP - VIEWPORT_PAD}px`
    } else {
        style.top = `${rect.bottom + GAP}px`
        style.maxHeight = `${vh - rect.bottom - GAP - VIEWPORT_PAD}px`
    }

    panelStyle.value = style
}

function setupListeners() {
    window.addEventListener('scroll', computePosition, true)
    window.addEventListener('resize', computePosition)
}

function teardownListeners() {
    window.removeEventListener('scroll', computePosition, true)
    window.removeEventListener('resize', computePosition)
}

async function toggle() {
    open.value = !open.value
    if (open.value) {
        await nextTick()
        computePosition()
        setupListeners()
    } else {
        teardownListeners()
    }
}

function close() {
    open.value = false
    teardownListeners()
}

async function handleSwitch(account) {
    if (account.is_active || switching.value) return
    switching.value = account.id
    emit('close')
    await authStore.switchAccount(account.id)
}

async function handleAdd() {
    close()
    emit('close')
    authStore.openAddAccountModal()
}

async function handleRemove(account) {
    if (removing.value) {
        return
    }

    const result = await confirmModal(
        'Confirm Account Logout',
        `<p>Are you sure you want to logout and remove <span class="font-bold">@${account.username}</span>'s session from this device?<br/><br/>You'll need to sign in again to switch back to this account.</p>`,
        t('common.confirm'),
        t('common.cancel')
    )

    if (!result) {
        return
    }
    close()
    removing.value = account.id
    try {
        await authStore.removeLinkedAccount(account.id)
    } catch (error) {
        console.log(error)
    } finally {
        removing.value = null
    }
}

async function handleLogoutAll() {
    const result = await confirmModal(
        t('common.confirm'),
        t('accounts.confirmLogoutAll'),
        t('common.confirm'),
        t('common.cancel')
    )
    if (!result) return
    close()
    await authStore.logoutAll()
}
</script>
