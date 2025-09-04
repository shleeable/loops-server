<template>
    <MainLayout>
        <div
            class="isolate bg-white dark:bg-slate-950 px-6 py-24 sm:py-32 lg:px-8"
        >
            <div class="mx-auto max-w-4xl">
                <div class="text-center mb-16">
                    <h1
                        class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl"
                    >
                        Get in Touch
                    </h1>
                    <p
                        class="mt-6 text-lg leading-8 text-gray-600 dark:text-slate-400 max-w-2xl mx-auto"
                    >
                        We're here to help. Reach out to us through any of the
                        channels below and we'll get back to you as soon as
                        possible.
                    </p>
                </div>

                <div v-if="loading" class="text-center py-12">
                    <div
                        class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 dark:border-indigo-400"
                    ></div>
                    <p class="mt-4 text-gray-600 dark:text-slate-400">
                        Loading contact information...
                    </p>
                </div>

                <div v-else-if="error" class="text-center py-12">
                    <div
                        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6"
                    >
                        <p class="text-red-800 dark:text-red-200">
                            {{ error }}
                        </p>
                    </div>
                </div>

                <div v-else class="grid gap-8 md:grid-cols-2 lg:grid-cols-2">
                    <div
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-lg dark:hover:shadow-slate-900/20 transition-all duration-300 flex flex-col"
                    >
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 group-hover:scale-110 transition-transform duration-300"
                                >
                                    <UserCircleIcon
                                        class="h-6 w-6 text-white"
                                    />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    Admin Contact
                                </h3>
                                <p
                                    class="text-gray-600 dark:text-slate-400 mb-4"
                                >
                                    For inquiries related to essential
                                    operational matters (please use the in‑app
                                    reporting system for content issues).
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-12"></div>
                            <a
                                :href="`mailto:${contactInfo.admin_email}`"
                                class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 transition-colors"
                            >
                                {{ contactInfo.admin_email }}
                                <ArrowTopRightOnSquareIcon
                                    class="ml-2 h-4 w-4"
                                />
                            </a>
                        </div>
                    </div>

                    <div
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-lg dark:hover:shadow-slate-900/20 transition-all duration-300 flex flex-col"
                    >
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 group-hover:scale-110 transition-transform duration-300"
                                >
                                    <LifebuoyIcon class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    Technical Support
                                </h3>
                                <p
                                    class="text-gray-600 dark:text-slate-400 mb-4"
                                >
                                    Need help with our platform? Get technical
                                    assistance and troubleshooting support.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-12"></div>
                            <a
                                :href="`mailto:${contactInfo.support_email}`"
                                class="inline-flex items-center text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300 transition-colors"
                            >
                                {{ contactInfo.support_email }}
                                <ArrowTopRightOnSquareIcon
                                    class="ml-2 h-4 w-4"
                                />
                            </a>
                        </div>
                    </div>

                    <div
                        v-if="contactInfo.support_forum_url"
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-lg dark:hover:shadow-slate-900/20 transition-all duration-300 flex flex-col"
                    >
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 group-hover:scale-110 transition-transform duration-300"
                                >
                                    <ChatBubbleLeftRightIcon
                                        class="h-6 w-6 text-white"
                                    />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    Community Forum
                                </h3>
                                <p
                                    class="text-gray-600 dark:text-slate-400 mb-4"
                                >
                                    Join our community discussions, ask
                                    questions, and share knowledge with other
                                    users.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-12"></div>
                            <a
                                :href="contactInfo.support_forum_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition-colors"
                            >
                                Visit Forum
                                <ArrowTopRightOnSquareIcon
                                    class="ml-2 h-4 w-4"
                                />
                            </a>
                        </div>
                    </div>

                    <div
                        v-if="contactInfo.fediverse_account"
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-lg dark:hover:shadow-slate-900/20 transition-all duration-300 flex flex-col"
                    >
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 group-hover:scale-110 transition-transform duration-300"
                                >
                                    <AtSymbolIcon class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    Social Connect
                                </h3>
                                <p
                                    class="text-gray-600 dark:text-slate-400 mb-4"
                                >
                                    Follow us on the fediverse for updates and
                                    quick questions via direct messages.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-12"></div>
                            <a
                                :href="contactInfo.fediverse_account"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-sm font-medium text-purple-600 dark:text-purple-400 hover:text-purple-500 dark:hover:text-purple-300 transition-colors"
                            >
                                {{
                                    formatFediverseHandle(
                                        contactInfo.fediverse_account,
                                    )
                                }}
                                <ArrowTopRightOnSquareIcon
                                    class="ml-2 h-4 w-4"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <div
                        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-slate-800 dark:to-slate-700 rounded-2xl p-8"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Response Time
                        </h3>
                        <p class="text-gray-600 dark:text-slate-400">
                            We typically respond to all inquiries within 24
                            hours during business days. For urgent technical
                            issues, please include "URGENT" in your subject
                            line.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import MainLayout from "@/layouts/MainLayout.vue";
import {
    UserCircleIcon,
    LifebuoyIcon,
    ChatBubbleLeftRightIcon,
    AtSymbolIcon,
    ArrowTopRightOnSquareIcon,
} from "@heroicons/vue/24/outline";

const contactInfo = ref({});
const loading = ref(true);
const error = ref(null);

const fetchContactInfo = async () => {
    try {
        loading.value = true;
        error.value = null;

        const response = await fetch("/api/v1/platform/contact");

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        contactInfo.value = data;
    } catch (err) {
        error.value =
            "Failed to load contact information. Please try again later.";
        console.error("Error fetching contact info:", err);
    } finally {
        loading.value = false;
    }
};

const formatFediverseHandle = (url) => {
    if (!url) return "";

    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;

        if (pathname.includes("@")) {
            const username = pathname.split("/").pop();
            return `${username}@${urlObj.hostname}`;
        } else if (
            pathname.startsWith("/users/") ||
            pathname.startsWith("/@")
        ) {
            const username = pathname.split("/").pop();
            return `@${username}@${urlObj.hostname}`;
        }

        return urlObj.hostname;
    } catch {
        return url;
    }
};

onMounted(() => {
    fetchContactInfo();
});
</script>
