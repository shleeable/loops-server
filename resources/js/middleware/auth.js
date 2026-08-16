import { useAuthStore } from '~/stores/auth'

export function authMiddleware(to, from) {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath }
        }
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return {
            name: 'dashboard'
        }
    }

    return
}
