import { ref } from 'vue'
import axios from '@/plugins/axios'
import { useAlertModal } from '@/composables/useAlertModal.js'

export function useKitImageUpload(kitId) {
    const { alertModal } = useAlertModal()

    const iconUrl = ref(null)
    const headerUrl = ref(null)
    const uploadingIcon = ref(false)
    const uploadingHeader = ref(false)
    const deletingIcon = ref(false)
    const deletingHeader = ref(false)

    const uploadImage = async (type, file) => {
        const isIcon = type === 'icon'
        if (isIcon) uploadingIcon.value = true
        else uploadingHeader.value = true

        const form = new FormData()
        form.append(type, file)

        try {
            const api = axios.getAxiosInstance()
            const res = await api.post(`/api/v1/starter-kits/details/${kitId}/${type}`, form, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            if (isIcon) {
                iconUrl.value = res.data.icon_url
            } else {
                headerUrl.value = res.data.header_url
            }
        } catch (err) {
            if (err.response.data?.error) {
                await alertModal('Upload Error', err.response.data?.error)
            } else if (err.response.data?.message) {
                await alertModal('Error', err.response.data?.message)
            }
        } finally {
            if (isIcon) {
                uploadingIcon.value = false
            } else {
                uploadingHeader.value = false
            }
        }
    }

    const deleteImage = async (type) => {
        const isIcon = type === 'icon'
        if (isIcon) {
            deletingIcon.value = true
        } else {
            deletingHeader.value = true
        }

        try {
            const api = axios.getAxiosInstance()
            await api.delete(`/api/v1/starter-kits/details/${kitId}/${type}`)
            if (isIcon) {
                iconUrl.value = null
            } else {
                headerUrl.value = null
            }
        } catch (err) {
            if (err.response.data?.error) {
                await alertModal('Error', err.response.data?.error)
            } else if (err.response.data?.message) {
                await alertModal('Error', err.response.data?.message)
            }
        } finally {
            if (isIcon) {
                deletingIcon.value = false
            } else {
                deletingHeader.value = false
            }
        }
    }

    return {
        iconUrl,
        headerUrl,
        uploadingIcon,
        uploadingHeader,
        deletingIcon,
        deletingHeader,
        uploadImage,
        deleteImage
    }
}
