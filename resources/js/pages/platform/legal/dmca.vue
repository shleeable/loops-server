<template>
    <MainLayout>
        <div class="isolate bg-white dark:bg-slate-950 px-6 py-24 sm:py-32 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <div class="text-center mb-16">
                    <h1
                        class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl"
                    >
                        Copyright &amp; DMCA
                    </h1>
                    <p
                        class="mt-6 text-lg leading-8 text-gray-600 dark:text-slate-400 max-w-2xl mx-auto"
                    >
                        We respect the intellectual property rights of others and expect our
                        community to do the same. If you believe content on Loops infringes your
                        copyright, you can submit a takedown notice and we'll review it promptly.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 mb-16">
                    <div
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-lg dark:hover:shadow-slate-900/20 transition-all duration-300 flex flex-col"
                    >
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-pink-600 group-hover:scale-110 transition-transform duration-300"
                                >
                                    <EnvelopeIcon class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    Designated Agent
                                </h3>
                                <p class="text-gray-600 dark:text-slate-400 mb-4">
                                    Send copyright takedown notices to our designated agent. Include
                                    all of the information listed below so we can act on your
                                    request without delay.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-12"></div>
                            <div class="flex-1 min-w-0">
                                <div
                                    v-if="loading"
                                    class="inline-flex items-center text-sm text-gray-500 dark:text-slate-500"
                                >
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 dark:border-indigo-400 mr-2"
                                    ></div>
                                    Loading contact…
                                </div>
                                <a
                                    v-else
                                    :href="`mailto:${dmcaEmail}`"
                                    class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 transition-colors break-all"
                                >
                                    {{ dmcaEmail }}
                                    <ArrowTopRightOnSquareIcon class="ml-2 h-4 w-4 flex-shrink-0" />
                                </a>
                            </div>
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
                                    <ShieldCheckIcon class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                                >
                                    Before You File
                                </h3>
                                <p class="text-gray-600 dark:text-slate-400">
                                    Only the copyright owner or someone authorized to act on their
                                    behalf may submit a notice. Knowingly making false claims can
                                    carry legal liability, so please make sure your request is
                                    accurate before sending it.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-16">
                    <div class="flex items-center space-x-3 mb-6">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-pink-600"
                        >
                            <DocumentTextIcon class="h-5 w-5 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            What to Include in a Takedown Notice
                        </h2>
                    </div>
                    <p class="text-gray-600 dark:text-slate-400 mb-8 max-w-3xl">
                        To be valid, your notice must contain each of the following. Missing
                        information may delay our review or prevent us from acting on your request.
                    </p>

                    <div
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm"
                    >
                        <ol class="space-y-6">
                            <li
                                v-for="(item, index) in takedownItems"
                                :key="index"
                                class="flex items-start space-x-4"
                            >
                                <span
                                    class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-pink-600 text-sm font-semibold text-white"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0 pt-1">
                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ item.title }}
                                    </h3>
                                    <p
                                        class="mt-1 text-sm leading-6 text-gray-600 dark:text-slate-400"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="mb-16">
                    <div class="flex items-center space-x-3 mb-6">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-600"
                        >
                            <ArrowUturnLeftIcon class="h-5 w-5 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Filing a Counter-Notification
                        </h2>
                    </div>
                    <p class="text-gray-600 dark:text-slate-400 mb-8 max-w-3xl">
                        If your content was removed and you believe it was a mistake or
                        misidentification, you may submit a counter-notification to our designated
                        agent. It must include:
                    </p>

                    <div
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-sm"
                    >
                        <ol class="space-y-6">
                            <li
                                v-for="(item, index) in counterItems"
                                :key="index"
                                class="flex items-start space-x-4"
                            >
                                <span
                                    class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-sky-600 text-sm font-semibold text-white"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0 pt-1">
                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ item.title }}
                                    </h3>
                                    <p
                                        class="mt-1 text-sm leading-6 text-gray-600 dark:text-slate-400"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <div
                    class="mb-16 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-8"
                >
                    <div class="flex items-start space-x-4">
                        <ExclamationTriangleIcon
                            class="h-6 w-6 flex-shrink-0 text-amber-600 dark:text-amber-400 mt-0.5"
                        />
                        <div>
                            <h3
                                class="text-lg font-semibold text-amber-900 dark:text-amber-200 mb-2"
                            >
                                Repeat Infringer Policy
                            </h3>
                            <p class="text-amber-800 dark:text-amber-300/90 leading-7">
                                Accounts that repeatedly infringe the copyrights of others will have
                                their content removed and may be suspended or permanently
                                terminated. We take repeat violations seriously to protect creators
                                across the network.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <div
                        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-slate-800 dark:to-slate-700 rounded-2xl p-8"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Response Time &amp; Disclaimer
                        </h3>
                        <p class="text-gray-600 dark:text-slate-400 leading-7 max-w-2xl mx-auto">
                            We aim to review valid notices within a few business days. This page is
                            provided for general informational purposes and is not legal advice. If
                            you're unsure about your rights or obligations, please consult an
                            attorney.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import {
    EnvelopeIcon,
    ShieldCheckIcon,
    DocumentTextIcon,
    ArrowUturnLeftIcon,
    ExclamationTriangleIcon,
    ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

const DEFAULT_DMCA_EMAIL = import.meta.env.VITE_APP_LOOPS_EMAILS_DMCA
const DEFAULT_SUPPORT_EMAIL = import.meta.env.VITE_APP_LOOPS_EMAILS_SUPPORT

const dmcaEmail = ref(DEFAULT_DMCA_EMAIL ?? DEFAULT_SUPPORT_EMAIL)
const loading = ref(true)

const takedownItems = [
    {
        title: 'Identification of the copyrighted work',
        description:
            'Describe the work you claim has been infringed. If multiple works are covered by a single notice, provide a representative list.'
    },
    {
        title: 'Identification of the infringing material',
        description:
            'Provide the direct URL(s) on Loops where the material appears, along with any other details that help us locate it.'
    },
    {
        title: 'Your contact information',
        description:
            'Include your full legal name, mailing address, telephone number, and email address so we can reach you about your notice.'
    },
    {
        title: 'A good-faith belief statement',
        description:
            'State that you have a good-faith belief that the disputed use is not authorized by the copyright owner, its agent, or the law.'
    },
    {
        title: 'An accuracy and authority statement',
        description:
            'State that the information in your notice is accurate, and that under penalty of perjury you are the owner or authorized to act on the owner\u2019s behalf.'
    },
    {
        title: 'Your signature',
        description:
            'Include a physical or electronic signature of the copyright owner or a person authorized to act on their behalf.'
    }
]

const counterItems = [
    {
        title: 'Your signature',
        description: 'A physical or electronic signature of the account holder.'
    },
    {
        title: 'Identification of the removed material',
        description:
            'Identify the content that was removed or disabled and the location where it appeared before removal.'
    },
    {
        title: 'A good-faith statement under penalty of perjury',
        description:
            'State that you have a good-faith belief the material was removed or disabled as a result of mistake or misidentification.'
    },
    {
        title: 'Your contact information and consent to jurisdiction',
        description:
            'Include your name, address, and phone number, your consent to the jurisdiction of the appropriate court, and that you will accept service of process from the party who filed the original notice.'
    }
]

const fetchContactInfo = async () => {
    try {
        const response = await fetch('/api/v1/platform/contact')

        if (response.ok) {
            const data = await response.json()
            dmcaEmail.value =
                DEFAULT_DMCA_EMAIL ||
                DEFAULT_SUPPORT_EMAIL ||
                data.support_email ||
                data.admin_email
        }
    } catch (err) {
        console.error('Error fetching DMCA contact info:', err)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchContactInfo()
})
</script>
