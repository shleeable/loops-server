<template>
    <div>
        <div class="mb-6">
            <div class="mb-8">
                <div class="mb-3">
                    <router-link to="/admin/invites" class="text-sm text-gray-500">
                        ← Back to invites
                    </router-link>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">View Invite Link</h1>
            </div>

            <div
                v-if="isDeleted"
                class="mb-6 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-lg p-6 shadow-sm"
            >
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                        <svg
                            class="w-6 h-6 text-amber-600 dark:text-amber-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-200 mb-1">
                            This Invite Has Been Deleted
                        </h3>
                        <p class="text-sm text-amber-800 dark:text-amber-300 mb-3">
                            Deleted {{ formatRelativeTime(inviteData.deleted_at) }} on
                            {{ formatDate(inviteData.deleted_at) }}. The invite link is no longer
                            active and cannot be used by new users.
                        </p>
                        <button
                            @click="handleRestore"
                            :disabled="isRestoring"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <svg
                                v-if="!isRestoring"
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                            <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24">
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    fill="none"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{ isRestoring ? 'Restoring...' : 'Restore Invite' }}
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="inviteData"
                class="mb-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 border border-blue-200 dark:border-gray-700 rounded-lg p-6 shadow-sm"
                :class="{ 'opacity-60': isDeleted }"
            >
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2"
                        >
                            Invite URL
                        </label>
                        <div
                            class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-3"
                        >
                            <code
                                class="flex-1 text-sm text-gray-900 dark:text-gray-100 break-all font-mono"
                            >
                                {{ inviteData.invite_url }}
                            </code>
                            <div class="flex gap-2 flex-shrink-0">
                                <button
                                    @click="copyInviteUrl"
                                    type="button"
                                    :disabled="isDeleted"
                                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Copy to clipboard"
                                >
                                    <svg
                                        v-if="!copiedInviteUrl"
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-5 h-5 text-green-600 dark:text-green-400"
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
                                </button>
                                <a
                                    :href="inviteData.invite_url"
                                    target="_blank"
                                    :class="{ 'pointer-events-none': isDeleted }"
                                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                    title="Open in new tab"
                                >
                                    <svg
                                        class="w-5 h-5"
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
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <p
                                        class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide"
                                    >
                                        Created
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatDate(inviteData.created_at) }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatRelativeTime(inviteData.created_at) }}
                                    </p>
                                </div>
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                    <svg
                                        class="w-4 h-4 text-blue-600 dark:text-blue-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <p
                                        class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide"
                                    >
                                        Expires
                                    </p>
                                    <p
                                        v-if="inviteData.expires_at"
                                        class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatDate(inviteData.expires_at) }}
                                    </p>
                                    <p
                                        v-else
                                        class="mt-1 text-sm font-medium text-gray-500 dark:text-gray-400 italic"
                                    >
                                        Never
                                    </p>
                                    <p
                                        v-if="inviteData.expires_at"
                                        class="mt-0.5 text-xs"
                                        :class="
                                            isExpired(inviteData.expires_at)
                                                ? 'text-red-600 dark:text-red-400 font-medium'
                                                : 'text-gray-500 dark:text-gray-400'
                                        "
                                    >
                                        {{
                                            isExpired(inviteData.expires_at)
                                                ? 'Expired'
                                                : formatRelativeTime(inviteData.expires_at)
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="p-2 rounded-lg"
                                    :class="
                                        inviteData.expires_at
                                            ? isExpired(inviteData.expires_at)
                                                ? 'bg-red-100 dark:bg-red-900/30'
                                                : 'bg-amber-100 dark:bg-amber-900/30'
                                            : 'bg-gray-100 dark:bg-gray-700'
                                    "
                                >
                                    <svg
                                        class="w-4 h-4"
                                        :class="
                                            inviteData.expires_at
                                                ? isExpired(inviteData.expires_at)
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-amber-600 dark:text-amber-400'
                                                : 'text-gray-600 dark:text-gray-400'
                                        "
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <p
                                        class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide"
                                    >
                                        Usage
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ inviteData.total_uses }} /
                                        {{ inviteData.max_uses || '∞' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{
                                            inviteData.max_uses
                                                ? `${Math.round(
                                                      (inviteData.total_uses /
                                                          inviteData.max_uses) *
                                                          100
                                                  )}% used`
                                                : 'Unlimited'
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="inviteData.total_uses"
                                    class="flex items-center justify-center"
                                >
                                    <router-link
                                        class="text-sm text-blue-500"
                                        :to="`/admin/profiles?q=invited_with:${inviteData.id}`"
                                        >View invited users</router-link
                                    >
                                </div>
                                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                    <svg
                                        class="w-4 h-4 text-green-600 dark:text-green-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form
                @submit.prevent
                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700"
                :class="{ 'opacity-60 pointer-events-none': isDeleted }"
            >
                <div class="p-6 space-y-6 flex justify-center flex-col lg:flex-row lg:gap-10">
                    <div class="flex-1 space-y-6">
                        <div>
                            <label
                                for="title"
                                class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-2"
                            >
                                Title
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                placeholder="Add a title for internal use"
                                :maxlength="80"
                                :disabled="isDeleted"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:opacity-50 disabled:cursor-not-allowed"
                            />
                            <div class="mt-1 flex justify-between items-center gap-5">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Only visible to admins - use this for easy ID reference
                                </p>
                                <div class="text-xs font-mono text-gray-400 dark:text-gray-500">
                                    {{ form.title?.length || 0 }}/80
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                for="message"
                                class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-2"
                            >
                                Invite Message
                            </label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="3"
                                placeholder="Optional welcome message for invited users..."
                                :disabled="isDeleted"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:opacity-50 disabled:cursor-not-allowed"
                            ></textarea>
                            <div class="mt-0 flex justify-between items-start gap-5">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    This message will be shown to users when they use the invite
                                    link
                                </p>
                                <div class="text-xs font-mono text-gray-400 dark:text-gray-500">
                                    {{ form.message?.length || 0 }}/500
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                for="admin_note"
                                class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-2"
                            >
                                Admin Note
                            </label>
                            <textarea
                                id="admin_note"
                                v-model="form.admin_note"
                                rows="6"
                                placeholder="Internal note (not visible to users)..."
                                :disabled="isDeleted"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:opacity-50 disabled:cursor-not-allowed"
                            ></textarea>
                            <div class="mt-0 flex justify-between items-start gap-5">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Only visible to admins - use this for tracking invite purpose
                                </p>
                                <div class="text-xs font-mono text-gray-400 dark:text-gray-500">
                                    {{ form.admin_note?.length || 0 }}/2000
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 flex-1">
                        <div class="relative">
                            <label
                                class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-2"
                            >
                                Auto-follow Accounts ({{ selectedAccounts.length }}/5)
                            </label>

                            <div
                                class="w-full min-h-[42px] px-2 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition flex flex-wrap items-center gap-2 cursor-text"
                                :class="{ 'opacity-50 cursor-not-allowed': isDeleted }"
                                @click="!isDeleted && $refs.searchInput.focus()"
                            >
                                <div
                                    v-for="account in selectedAccounts"
                                    :key="account.id"
                                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800"
                                >
                                    <img
                                        :src="account.avatar"
                                        class="w-4 h-4 rounded-full bg-gray-200"
                                        alt=""
                                    />
                                    <span
                                        class="text-xs font-medium text-blue-700 dark:text-blue-200"
                                        >@{{ account.username }}</span
                                    >
                                    <button
                                        type="button"
                                        @click.stop="removeAccount(account.id)"
                                        :disabled="isDeleted"
                                        class="ml-0.5 text-blue-400 hover:text-blue-600 dark:hover:text-blue-100 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg
                                            class="w-3 h-3"
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

                                <input
                                    ref="searchInput"
                                    v-model="searchQuery"
                                    @input="handleSearchInput"
                                    @keydown.backspace="handleBackspace"
                                    @focus="showResults = true"
                                    :disabled="selectedAccounts.length >= 5 || isDeleted"
                                    :placeholder="
                                        selectedAccounts.length === 0
                                            ? 'Search users (@username)...'
                                            : selectedAccounts.length < 5
                                              ? 'Add another...'
                                              : 'Limit reached'
                                    "
                                    class="flex-1 min-w-[120px] bg-transparent border-none outline-none text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                            </div>

                            <div
                                v-if="
                                    showResults &&
                                    (searchResults.length > 0 || isSearching) &&
                                    !isDeleted
                                "
                                class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto"
                            >
                                <div
                                    v-if="isSearching"
                                    class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2"
                                >
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                            fill="none"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Searching...
                                </div>

                                <ul v-else class="py-1">
                                    <li v-for="result in searchResults" :key="result.id">
                                        <button
                                            type="button"
                                            @click="selectAccount(result)"
                                            class="w-full px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                                        >
                                            <img
                                                :src="result.avatar"
                                                class="w-8 h-8 rounded-full bg-gray-200 object-cover"
                                                alt=""
                                            />
                                            <div>
                                                <div
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ result.name || result.username }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    @{{ result.username }}
                                                </div>
                                            </div>
                                            <span
                                                v-if="isSelected(result.id)"
                                                class="ml-auto text-xs font-medium text-blue-600 dark:text-blue-400"
                                            >
                                                Selected
                                            </span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-if="showResults && !isDeleted"
                                @click="showResults = false"
                                class="fixed inset-0 z-0 cursor-default"
                            ></div>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Search and select up to 5 accounts invited users will automatically
                                follow.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="max_uses"
                                    class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-2"
                                >
                                    Max Uses
                                </label>
                                <input
                                    id="max_uses"
                                    v-model.number="form.max_uses"
                                    type="number"
                                    min="0"
                                    :disabled="isDeleted"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:opacity-50 disabled:cursor-not-allowed"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    0 = unlimited uses
                                </p>
                            </div>

                            <div>
                                <label
                                    for="expires_at"
                                    class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-2"
                                >
                                    Expires At
                                </label>
                                <input
                                    id="expires_at"
                                    v-model="form.expires_at"
                                    type="datetime-local"
                                    :disabled="isDeleted"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:opacity-50 disabled:cursor-not-allowed"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Leave empty for no expiration
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label
                                        for="verify_email"
                                        class="text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >
                                        Require Email Verification
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Users must verify their email before accessing their account
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="!isDeleted && (form.verify_email = !form.verify_email)"
                                    :disabled="isDeleted"
                                    :class="[
                                        form.verify_email
                                            ? 'bg-blue-600'
                                            : 'bg-gray-200 dark:bg-gray-700',
                                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            form.verify_email ? 'translate-x-5' : 'translate-x-0',
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out mt-0.5'
                                        ]"
                                    />
                                </button>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label
                                        for="is_active"
                                        class="text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >
                                        Notify Admins
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Send an email notification to admins when a new user joins
                                        using this invite link
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="
                                        !isDeleted &&
                                        (form.email_admin_on_join = !form.email_admin_on_join)
                                    "
                                    :disabled="isDeleted"
                                    :class="[
                                        form.email_admin_on_join
                                            ? 'bg-blue-600'
                                            : 'bg-gray-200 dark:bg-gray-700',
                                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            form.email_admin_on_join
                                                ? 'translate-x-5'
                                                : 'translate-x-0',
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out mt-0.5'
                                        ]"
                                    />
                                </button>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label
                                        for="is_active"
                                        class="text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >
                                        Active
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Enable this invite link immediately
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="!isDeleted && (form.is_active = !form.is_active)"
                                    :disabled="isDeleted"
                                    :class="[
                                        form.is_active
                                            ? 'bg-blue-600'
                                            : 'bg-gray-200 dark:bg-gray-700',
                                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            form.is_active ? 'translate-x-5' : 'translate-x-0',
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out mt-0.5'
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-if="error"
                    class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 mx-6 mb-6"
                >
                    <div class="flex">
                        <svg
                            class="h-5 w-5 text-red-400 dark:text-red-500"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!isDeleted"
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-lg flex items-center justify-between"
                >
                    <button
                        type="button"
                        @click="handleDelete"
                        class="px-4 py-2 text-sm font-medium text-red-500 hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer"
                    >
                        Delete
                    </button>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="router.push('/admin/invites')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="handleSubmit"
                            :disabled="loading || !form.title"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <span v-if="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                        fill="none"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Updating...
                            </span>
                            <span v-else>Update</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { invitesApi } from '@/services/adminApi'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const isLoading = ref(false)
const error = ref(null)
const success = ref(false)
const inviteUrl = ref(null)
const copied = ref(false)
const copiedInviteUrl = ref(false)
const inviteData = ref(null)
const isRestoring = ref(false)

const isDeleted = computed(() => {
    return inviteData.value?.deleted_at != null
})

const searchQuery = ref('')
const searchResults = ref([])
const selectedAccounts = ref([])
const isSearching = ref(false)
const showResults = ref(false)
let debounceTimer = null

const form = reactive({
    title: '',
    message: '',
    admin_note: '',
    autofollow_accounts: [],
    verify_email: true,
    email_admin_on_join: true,
    max_uses: 0,
    is_active: true,
    expires_at: ''
})

const convertToDatetimeLocal = (isoString) => {
    if (!isoString) return ''
    return isoString.substring(0, 16)
}

const convertToISO = (datetimeLocal) => {
    if (!datetimeLocal) return null
    return datetimeLocal + ':00Z'
}

const hydrateAccounts = async (ids) => {
    if (!ids || ids.length === 0) return []

    const promises = ids.map(async (id) => {
        try {
            const acct = await invitesApi.getAccount(id)
            return acct.data
        } catch (e) {
            console.error(`Failed to hydrate account ${id}`, e)
            return null
        }
    })

    const results = await Promise.all(promises)

    return results.filter((a) => a !== null)
}

const fetchInvite = async (id) => {
    isLoading.value = true

    try {
        const response = await invitesApi.getInvite(id)
        inviteData.value = response.data

        let autofollowIds = []
        if (Array.isArray(response.data.autofollow_accounts)) {
            autofollowIds = response.data.autofollow_accounts
        } else if (
            typeof response.data.autofollow_accounts === 'string' &&
            response.data.autofollow_accounts
        ) {
            autofollowIds = response.data.autofollow_accounts.split(',')
        }

        if (autofollowIds.length > 0) {
            selectedAccounts.value = await hydrateAccounts(autofollowIds)
        } else {
            selectedAccounts.value = []
        }

        Object.assign(form, {
            ...response.data,
            expires_at: convertToDatetimeLocal(response.data.expires_at)
        })
    } catch (err) {
        console.error('Error fetching invite:', err)
        error.value = 'Failed to load invite data'
    } finally {
        isLoading.value = false
    }
}

const handleSubmit = async () => {
    loading.value = true
    error.value = null
    success.value = false
    inviteUrl.value = null

    form.autofollow_accounts = selectedAccounts.value.map((a) => a.id)

    try {
        const payload = {
            ...form,
            expires_at: convertToISO(form.expires_at)
        }
        const response = await invitesApi.updateInvite(route.params.id, payload)
        success.value = true
        inviteUrl.value = response.data.invite_url

        window.location.reload()
    } catch (err) {
        error.value = err.message || 'Failed to update invite link'
    } finally {
        loading.value = false
    }
}

const handleRestore = async () => {
    isRestoring.value = true
    error.value = null

    try {
        await invitesApi.restoreInvite(route.params.id)
        // Refresh the page to show restored state
        window.location.reload()
    } catch (err) {
        error.value = err.message || 'Failed to restore invite'
        isRestoring.value = false
    }
}

const handleSearchInput = () => {
    if (!searchQuery.value) {
        searchResults.value = []
        isSearching.value = false
        return
    }

    isSearching.value = true
    showResults.value = true

    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(async () => {
        try {
            const response = await invitesApi.searchProfiles(searchQuery.value)
            searchResults.value = response.data.filter(
                (u) => !selectedAccounts.value.some((sel) => sel.id === u.id)
            )
        } catch (e) {
            console.error(e)
        } finally {
            isSearching.value = false
        }
    }, 400)
}

const selectAccount = (account) => {
    if (selectedAccounts.value.length >= 5) return
    if (!isSelected(account.id)) {
        selectedAccounts.value.push(account)
    }
    searchQuery.value = ''
    searchResults.value = []
    showResults.value = false
}

const removeAccount = (id) => {
    selectedAccounts.value = selectedAccounts.value.filter((a) => a.id !== id)
}

const handleBackspace = (e) => {
    if (searchQuery.value === '' && selectedAccounts.value.length > 0) {
        selectedAccounts.value.pop()
    }
}

const isSelected = (id) => {
    return selectedAccounts.value.some((a) => a.id === id)
}

const handleDelete = async () => {
    const confirm = window.confirm('Are you sure you want to delete this invite?')

    if (confirm) {
        try {
            const res = await invitesApi.deleteInvite(route.params.id)
            if (res) {
                // Refresh to show deleted state instead of navigating away
                window.location.reload()
            }
        } catch (err) {
            console.log(err)
        }
    }
}

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(inviteUrl.value)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy:', err)
    }
}

const copyInviteUrl = async () => {
    if (isDeleted.value) return

    try {
        await navigator.clipboard.writeText(inviteData.value.invite_url)
        copiedInviteUrl.value = true
        setTimeout(() => {
            copiedInviteUrl.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy:', err)
    }
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date)
}

const formatRelativeTime = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((date - now) / 1000)
    const diffInMinutes = Math.floor(diffInSeconds / 60)
    const diffInHours = Math.floor(diffInMinutes / 60)
    const diffInDays = Math.floor(diffInHours / 24)

    if (diffInSeconds < 0) {
        const absDays = Math.abs(diffInDays)
        const absHours = Math.abs(diffInHours)
        const absMinutes = Math.abs(diffInMinutes)

        if (absDays > 0) return `${absDays} day${absDays !== 1 ? 's' : ''} ago`
        if (absHours > 0) return `${absHours} hour${absHours !== 1 ? 's' : ''} ago`
        if (absMinutes > 0) return `${absMinutes} minute${absMinutes !== 1 ? 's' : ''} ago`
        return 'Just now'
    } else {
        if (diffInDays > 0) return `in ${diffInDays} day${diffInDays !== 1 ? 's' : ''}`
        if (diffInHours > 0) return `in ${diffInHours} hour${diffInHours !== 1 ? 's' : ''}`
        if (diffInMinutes > 0) return `in ${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''}`
        return 'Soon'
    }
}

const isExpired = (dateString) => {
    if (!dateString) return false
    return new Date(dateString) < new Date()
}

watch(
    () => route.params.id,
    (newId) => {
        if (newId) {
            fetchInvite(newId)
        }
    }
)

onMounted(async () => {
    await fetchInvite(route.params.id)
})
</script>
