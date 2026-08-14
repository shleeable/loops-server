import './bootstrap'
import { createApp, defineAsyncComponent } from 'vue'
import { createPinia } from 'pinia'
import { createHead } from '@unhead/vue/client'
import axiosPlugin from './plugins/axios'
import { VueQueryPlugin } from '@tanstack/vue-query'
import AlertModalPlugin from '@/composables/useAlertModal.js'
import storePlugin from './plugins/stores'
import App from './App.vue'
import router from './routes/index'
import i18n, { initLocale } from './i18n/locales'

import MainLayout from '@/layouts/MainLayout.vue'
import FeedLayout from '@/layouts/FeedLayout.vue'

import '../css/fonts.css'
import '../sass/next.css'

const app = createApp(App)

app.component('MainLayout', MainLayout)
app.component('FeedLayout', FeedLayout)
app.component(
    'AdminLayout',
    defineAsyncComponent(() => import('@/layouts/AdminLayout.vue'))
)
app.component(
    'BlankLayout',
    defineAsyncComponent(() => import('@/layouts/BlankLayout.vue'))
)
app.component(
    'FullLayout',
    defineAsyncComponent(() => import('@/layouts/FullLayout.vue'))
)
app.component(
    'SettingsLayout',
    defineAsyncComponent(() => import('@/layouts/SettingsLayout.vue'))
)
app.component(
    'StudioLayout',
    defineAsyncComponent(() => import('@/layouts/StudioLayout.vue'))
)

app.config.globalProperties.$appConfig = window.appConfig
app.provide('appConfig', window.appConfig)
app.provide('appCaptcha', window.appCaptcha)

app.use(createPinia())
    .use(createHead())
    .use(axiosPlugin)
    .use(router)
    .use(storePlugin)
    .use(i18n)
    .use(AlertModalPlugin)
    .use(VueQueryPlugin, {
        queryClientConfig: {
            defaultOptions: {
                queries: {
                    staleTime: 1000 * 60 * 5,
                    refetchOnWindowFocus: false
                }
            }
        }
    })

initLocale().finally(() => app.mount('#app'))
