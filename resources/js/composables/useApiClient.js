import axiosPlugin from '@/plugins/axios'

export function useApiClient() {
    return axiosPlugin.getAxiosInstance()
}
