<template>
    <SettingsLayout>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <button
                    class="flex items-center text-gray-500 hover:text-gray-400 dark:text-gray-400 dark:hover:text-gray-300 cursor-pointer"
                    @click="$router.back()"
                >
                    <i class="bx bx-arrow-back text-[20px] mr-1"></i>
                </button>
                <h1
                    class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100"
                >
                    Account status
                </h1>
            </div>
            <hr class="border-gray-300 dark:border-gray-700" />

            <section class="my-8">
                <div
                    class="bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-700 rounded-lg p-4 mb-6"
                >
                    <div class="flex items-start gap-3">
                        <i
                            class="bx bx-check-circle text-green-500 text-[20px] mt-0.5"
                        ></i>
                        <div>
                            <h3
                                class="font-medium text-green-800 dark:text-green-300 mb-1"
                            >
                                Your account is in good standing
                            </h3>
                            <p
                                class="text-sm text-green-700 dark:text-green-400"
                            >
                                You currently have no restrictions and can use
                                all Loops features.
                            </p>
                        </div>
                    </div>
                </div>

                <h2
                    class="tracking-tight font-light mb-4 text-gray-900 dark:text-gray-100"
                >
                    Account standing levels
                </h2>
                <div class="flex flex-col gap-3 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="p-4 flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center mt-1"
                            >
                                <i
                                    class="bx bx-check text-green-600 dark:text-green-400 text-[16px]"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="font-medium text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    Good standing
                                </h3>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                >
                                    Full access to all features. No content
                                    violations or restrictions.
                                </p>
                            </div>
                            <span
                                class="text-xs bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 px-2 py-1 rounded-full font-medium"
                                >Current</span
                            >
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="p-4 flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center mt-1"
                            >
                                <i
                                    class="bx bx-error text-yellow-600 dark:text-yellow-400 text-[16px]"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="font-medium text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    Warning issued
                                </h3>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                >
                                    Minor violations detected. Account remains
                                    active but further violations may lead to
                                    restrictions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="p-4 flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center mt-1"
                            >
                                <i
                                    class="bx bx-time text-orange-600 dark:text-orange-400 text-[16px]"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="font-medium text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    Limited features
                                </h3>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                >
                                    Some features restricted due to policy
                                    violations. Can view content but cannot post
                                    or comment.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="p-4 flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center mt-1"
                            >
                                <i
                                    class="bx bx-block text-red-600 dark:text-red-400 text-[16px]"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="font-medium text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    Suspended
                                </h3>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                >
                                    Temporary suspension due to serious
                                    violations. Cannot access account during
                                    suspension period.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <h2
                    class="tracking-tight font-light mb-4 text-gray-900 dark:text-gray-100"
                >
                    Your activity summary
                </h2>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6"
                >
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div
                                    class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    {{ activityStats.reports }}
                                </div>
                                <div
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Reports received
                                </div>
                            </div>
                            <div class="text-center">
                                <div
                                    class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    {{ activityStats.violations }}
                                </div>
                                <div
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Policy violations
                                </div>
                            </div>
                            <div class="text-center">
                                <div
                                    class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1"
                                >
                                    {{ activityStats.warnings }}
                                </div>
                                <div
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Warnings issued
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h2
                    class="tracking-tight font-light mb-4 text-gray-900 dark:text-gray-100"
                >
                    Community guidelines
                </h2>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6"
                >
                    <div class="p-6">
                        <p
                            class="text-sm text-gray-600 dark:text-gray-300 mb-4"
                        >
                            To maintain a safe and positive environment, all
                            Loops users must follow our Community Guidelines.
                            Violations can result in content removal, account
                            restrictions, or suspension.
                        </p>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <i
                                    class="bx bx-shield text-blue-500 text-[16px] mt-0.5"
                                ></i>
                                <div>
                                    <h4
                                        class="font-medium text-gray-800 dark:text-gray-100 text-sm"
                                    >
                                        Respect others
                                    </h4>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-300"
                                    >
                                        No harassment, bullying, hate speech, or
                                        discrimination.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i
                                    class="bx bx-heart text-pink-500 text-[16px] mt-0.5"
                                ></i>
                                <div>
                                    <h4
                                        class="font-medium text-gray-800 dark:text-gray-100 text-sm"
                                    >
                                        Keep it appropriate
                                    </h4>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-300"
                                    >
                                        No explicit sexual content, violence, or
                                        harmful activities.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i
                                    class="bx bx-copyright text-purple-500 text-[16px] mt-0.5"
                                ></i>
                                <div>
                                    <h4
                                        class="font-medium text-gray-800 dark:text-gray-100 text-sm"
                                    >
                                        Respect intellectual property
                                    </h4>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-300"
                                    >
                                        Only post content you own or have
                                        permission to use.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i
                                    class="bx bx-check-circle text-green-500 text-[16px] mt-0.5"
                                ></i>
                                <div>
                                    <h4
                                        class="font-medium text-gray-800 dark:text-gray-100 text-sm"
                                    >
                                        Be authentic
                                    </h4>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-300"
                                    >
                                        No spam, fake accounts, or misleading
                                        information.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700"
                        >
                            <router-link
                                to="/community-guidelines"
                                class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                            >
                                Read full Community Guidelines →
                            </router-link>
                        </div>
                    </div>
                </div>

                <h2
                    class="hidden tracking-tight font-light mb-4 text-gray-900 dark:text-gray-100"
                >
                    Appeal a decision
                </h2>
                <div
                    class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6"
                >
                    <div class="p-6">
                        <p
                            class="text-sm text-gray-600 dark:text-gray-300 mb-4"
                        >
                            If you believe a warning or restriction was applied
                            incorrectly, you can request a review of the
                            decision.
                        </p>

                        <div class="space-y-3 mb-4">
                            <div class="text-sm">
                                <span
                                    class="font-medium text-gray-800 dark:text-gray-100"
                                    >Response time:</span
                                >
                                <span
                                    class="text-gray-600 dark:text-gray-300 ml-1"
                                    >Usually within 2-3 business days</span
                                >
                            </div>
                            <div class="text-sm">
                                <span
                                    class="font-medium text-gray-800 dark:text-gray-100"
                                    >What to include:</span
                                >
                                <span
                                    class="text-gray-600 dark:text-gray-300 ml-1"
                                    >Specific details about why you believe the
                                    decision was incorrect</span
                                >
                            </div>
                        </div>

                        <button
                            class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
                        >
                            Submit an appeal
                        </button>
                    </div>
                </div>

                <h2
                    class="tracking-tight font-light mb-4 text-gray-900 dark:text-gray-100"
                >
                    Get help
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i
                                    class="bx bx-help-circle text-blue-500 text-[20px]"
                                ></i>
                                <div>
                                    <h3
                                        class="font-medium text-gray-800 dark:text-gray-100"
                                    >
                                        Help Center
                                    </h3>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-300"
                                    >
                                        Find answers to common questions
                                    </p>
                                </div>
                            </div>
                            <i
                                class="bx bx-chevron-right text-gray-400 dark:text-gray-500"
                            ></i>
                        </div>
                    </div>

                    <router-link to="/contact">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                        >
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i
                                        class="bx bx-message text-green-500 text-[20px]"
                                    ></i>
                                    <div>
                                        <h3
                                            class="font-medium text-gray-800 dark:text-gray-100"
                                        >
                                            Contact Support
                                        </h3>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-300"
                                        >
                                            Get personalized help
                                        </p>
                                    </div>
                                </div>
                                <i
                                    class="bx bx-chevron-right text-gray-400 dark:text-gray-500"
                                ></i>
                            </div>
                        </div>
                    </router-link>
                </div>
            </section>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { ref } from "vue";
import SettingsLayout from "~/layouts/SettingsLayout.vue";

const activityStats = ref({
    reports: 0,
    violations: 0,
    warnings: 0,
});
</script>
