import { defineStore } from 'pinia'
import axios from '~/plugins/axios'

export const useAccountCardStore = defineStore('accountCard', {
    state: () => ({
        userInfoCache: {},
        userStateCache: {},
        loading: {}
    }),

    getters: {
        getUserInfo: (state) => (userId) => {
            return state.userInfoCache[userId] || null
        },

        getUserState: (state) => (userId) => {
            return state.userStateCache[userId] || null
        },

        isLoading: (state) => (userId) => {
            return state.loading[userId] || false
        }
    },

    actions: {
        async fetchUserInfo(userId) {
            if (this.userInfoCache[userId]) {
                return this.userInfoCache[userId]
            }

            this.loading[userId] = true

            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.get(`/api/v1/account/info/${userId}`)

                if (response.data) {
                    this.userInfoCache[userId] = response.data
                    return response.data
                }
            } catch (error) {
                console.error('Failed to fetch user info:', error)
                throw error
            } finally {
                this.loading[userId] = false
            }
        },

        async fetchUserState(userId) {
            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.get(`/api/v1/account/state/${userId}`)

                if (response.data?.data) {
                    this.userStateCache[userId] = response.data.data
                    return response.data.data
                }
            } catch (error) {
                console.error('Failed to fetch user state:', error)
                throw error
            }
        },

        async follow(userId) {
            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.post(`/api/v1/account/follow/${userId}`)

                if (response.data) {
                    await this.fetchUserState(userId)
                    return true
                }
            } catch (error) {
                console.error('Failed to follow user:', error)
                throw error
            }
        },

        async unfollow(userId) {
            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.post(`/api/v1/account/unfollow/${userId}`)

                if (response.data) {
                    await this.fetchUserState(userId)
                    return true
                }
            } catch (error) {
                console.error('Failed to unfollow user:', error)
                throw error
            }
        },

        async cancelFollowRequest(userId) {
            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.post(
                    `/api/v1/account/undo-follow-request/${userId}`
                )

                if (response.data) {
                    await this.fetchUserState(userId)
                    return true
                }
            } catch (error) {
                console.error('Failed to cancel follow request:', error)
                throw error
            }
        },

        async block(userId) {
            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.post(`/api/v1/account/${userId}/block`)

                if (response.data) {
                    await this.fetchUserState(userId)
                    return true
                }
            } catch (error) {
                console.error('Failed to block user:', error)
                throw error
            }
        },

        async unblock(userId) {
            try {
                const axiosInstance = axios.getAxiosInstance()
                const response = await axiosInstance.post(`/api/v1/account/${userId}/unblock`)

                if (response.data) {
                    await this.fetchUserState(userId)
                    return true
                }
            } catch (error) {
                console.error('Failed to unblock user:', error)
                throw error
            }
        },

        clearUserCache(userId) {
            delete this.userInfoCache[userId]
            delete this.userStateCache[userId]
        },

        clearAllCaches() {
            this.userInfoCache = {}
            this.userStateCache = {}
            this.loading = {}
        }
    }
})
