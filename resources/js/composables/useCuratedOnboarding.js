import { ref, computed } from 'vue'
import axios from 'axios'

export function useCuratedOnboarding() {
    const config = ref(null)
    const loading = ref(false)
    const error = ref(null)

    async function fetchConfig() {
        loading.value = true
        error.value = null
        try {
            const { data } = await axios.get('/api/v1/onboarding/config')
            config.value = data
        } catch (e) {
            error.value = e.response?.data?.message || 'Failed to load configuration.'
        } finally {
            loading.value = false
        }
    }

    async function submitApplication(form) {
        loading.value = true
        error.value = null
        try {
            const { data } = await axios.post('/api/v1/onboarding/apply', form)
            return data
        } catch (e) {
            if (e.response?.status === 422) {
                throw e.response.data
            }
            error.value = e.response?.data?.message || 'Something went wrong. Please try again.'
            throw e
        } finally {
            loading.value = false
        }
    }

    const isEnabled = computed(() => config.value?.registration_mode === 'curated')
    const customQuestions = computed(() => config.value?.questions || [])
    const guidelines = computed(() => config.value?.guidelines || '')
    const minAge = computed(() => config.value?.min_age)

    return {
        config,
        loading,
        error,
        fetchConfig,
        submitApplication,
        isEnabled,
        customQuestions,
        guidelines,
        minAge
    }
}

export function useCuratedApplicationsAdmin() {
    const applications = ref([])
    const stats = ref(null)
    const currentApplication = ref(null)
    const loading = ref(false)
    const pagination = ref(null)

    async function fetchApplications(params = {}) {
        loading.value = true
        try {
            const { data } = await axios.get('/api/v1/admin/curated-applications', { params })
            applications.value = data.data
            pagination.value = data.meta
        } finally {
            loading.value = false
        }
    }

    async function fetchApplication(id) {
        loading.value = true
        try {
            const { data } = await axios.get(`/api/v1/admin/curated-applications/${id}`)
            currentApplication.value = data.data || data
            return data
        } finally {
            loading.value = false
        }
    }

    async function deleteApplication(applicationId) {
        await axios.post(`/api/v1/admin/curated-applications/${applicationId}/delete`)
    }

    async function deleteApplicationNote(applicationId, noteId) {
        const { data } = await axios.post(
            `/api/v1/admin/curated-applications/${applicationId}/delete-note/${noteId}`
        )
        currentApplication.value = data.data || data
    }

    async function fetchStats() {
        const { data } = await axios.get('/api/v1/admin/curated-applications/stats')
        stats.value = data
    }

    async function approve(id) {
        const { data } = await axios.post(`/api/v1/admin/curated-applications/${id}/approve`)
        return data
    }

    async function forceApprove(id) {
        const { data } = await axios.post(`/api/v1/admin/curated-applications/${id}/force-approve`)
        return data
    }

    async function reject(id, reason = null, sendEmail = true) {
        const { data } = await axios.post(`/api/v1/admin/curated-applications/${id}/reject`, {
            reason,
            send_email: sendEmail
        })
        return data
    }

    async function addNote(id, body) {
        const { data } = await axios.post(`/api/v1/admin/curated-applications/${id}/notes`, {
            body
        })
        return data
    }

    async function bulkApprove(ids) {
        const { data } = await axios.post('/api/v1/admin/curated-applications/bulk-approve', {
            ids
        })
        return data
    }

    async function bulkReject(ids, reason = null) {
        const { data } = await axios.post('/api/v1/admin/curated-applications/bulk-reject', {
            ids,
            reason
        })
        return data
    }

    return {
        applications,
        stats,
        currentApplication,
        loading,
        pagination,
        fetchApplications,
        fetchApplication,
        deleteApplicationNote,
        deleteApplication,
        fetchStats,
        forceApprove,
        approve,
        reject,
        addNote,
        bulkApprove,
        bulkReject
    }
}

export function useCuratedSettingsAdmin() {
    const API_BASE = '/api/v1/admin/curated-onboarding/settings'
    const settings = ref(null)
    const loading = ref(false)

    async function fetchSettings() {
        loading.value = true
        try {
            const { data } = await axios.get(API_BASE)
            settings.value = data
            return data
        } finally {
            loading.value = false
        }
    }

    async function updateSettings(payload) {
        const { data } = await axios.put(API_BASE, payload)
        settings.value = data.data || data
        return data
    }

    async function addQuestion(payload) {
        const { data } = await axios.post(`${API_BASE}/questions`, payload)
        return data
    }

    async function updateQuestion(questionId, payload) {
        const { data } = await axios.put(`${API_BASE}/questions/${questionId}`, payload)
        return data
    }

    async function deleteQuestion(questionId) {
        const { data } = await axios.delete(`${API_BASE}/questions/${questionId}`)
        return data
    }

    async function reorderQuestions(orderedIds) {
        const { data } = await axios.put(`${API_BASE}/questions/reorder`, {
            order: orderedIds
        })
        return data
    }

    return {
        settings,
        loading,
        fetchSettings,
        updateSettings,
        addQuestion,
        updateQuestion,
        deleteQuestion,
        reorderQuestions
    }
}
