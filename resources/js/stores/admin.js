import { defineStore } from 'pinia'
import axios from '~/plugins/axios'

export const useAdminStore = defineStore('admin', {
    state: () => ({
        isDarkMode: false,
        reportsCount: 0,
        starterKitsAwaitingApproval: 0,
        starterKitsUpdates: 0,
        isLoading: false,
        error: null
    }),

    getters: {
        displayReportsCount(state) {
            if (state.reportsCount > 99) {
                return '99+'
            }
            return state.reportsCount
        },

        displayAwaitingStarterKitsCount(state) {
            if (state.starterKitsAwaitingApproval > 99) {
                return '99+'
            }
            return state.starterKitsAwaitingApproval
        },

        displayUpdatedStarterKitsCount(state) {
            if (state.starterKitsUpdates > 99) {
                return '99+'
            }
            return state.starterKitsUpdates
        }
    },

    actions: {
        initTheme() {
            if (typeof window === 'undefined') return

            const storedTheme = localStorage.getItem('theme')

            if (storedTheme === 'dark') {
                this.setIsDarkMode(true)
            } else if (storedTheme === 'light') {
                this.setIsDarkMode(false)
            } else {
                const prefersDark =
                    window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches

                this.setIsDarkMode(prefersDark)
            }
        },

        async fetchReportsCount() {
            try {
                this.isLoading = true
                this.error = null

                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance('/api/v1/admin/reports-count')

                const rawCount = Number(response.data?.data?.count ?? 0)
                const rawStarterKitsAwaitingApproval = Number(
                    response.data?.data?.starter_kit_awaiting_approval ?? 0
                )
                const rawStarterKitsUpdates = Number(response.data?.data?.starter_kit_updates ?? 0)

                this.reportsCount =
                    Number.isFinite(rawCount) && rawCount > 0 ? Math.floor(rawCount) : 0
                this.starterKitsAwaitingApproval =
                    Number.isFinite(rawStarterKitsAwaitingApproval) &&
                    rawStarterKitsAwaitingApproval > 0
                        ? Math.floor(rawStarterKitsAwaitingApproval)
                        : 0
                this.starterKitsUpdates =
                    Number.isFinite(rawStarterKitsUpdates) && rawStarterKitsUpdates > 0
                        ? Math.floor(rawStarterKitsUpdates)
                        : 0
            } catch (error) {
                console.error('Error fetching reports count:', error)
                this.error = error
                this.reportsCount = 0
                this.starterKitsAwaitingApproval = 0
                this.starterKitsUpdates = 0
            } finally {
                this.isLoading = false
            }
        },

        setReportsCount(count) {
            const n = Number(count)
            this.reportsCount = Number.isFinite(n) && n >= 0 ? Math.floor(n) : 0
        },

        decrementReportsCount() {
            if (this.reportsCount > 0) {
                this.reportsCount -= 1
            }
        },

        decrementKitsUpdatesCount() {
            if (this.starterKitsUpdates > 0) {
                this.starterKitsUpdates -= 1
            }
        },

        incrementReportsCount() {
            this.reportsCount += 1
        },

        setIsDarkMode(val = null) {
            if (typeof window === 'undefined') {
                this.isDarkMode = false
                return
            }

            if (val === null) {
                this.isDarkMode = document.documentElement.classList.contains('dark')
            } else {
                this.isDarkMode = !!val
            }

            if (this.isDarkMode) {
                document.documentElement.classList.add('dark')
                localStorage.setItem('theme', 'dark')
            } else {
                document.documentElement.classList.remove('dark')
                localStorage.setItem('theme', 'light')
            }
        },

        toggleDarkMode() {
            this.setIsDarkMode(!this.isDarkMode)
        }
    }
})
