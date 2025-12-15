import axios from '~/plugins/axios'

export const useVideoTracking = () => {
    const recordImpression = async (videoId, watchDuration, completed) => {
        if (watchDuration <= 0) {
            return
        }
        const axiosInstance = axios.getAxiosInstance()

        try {
            await axiosInstance.post('/api/v0/feed/recommended/impression', {
                video_id: videoId,
                watch_duration: Math.floor(watchDuration),
                completed
            })
        } catch (error) {
            console.error('Failed to record impression:', error)
        }
    }

    const recordFeedback = async (videoId, feedbackType) => {
        const axiosInstance = axios.getAxiosInstance()

        try {
            await axiosInstance.post('/api/v0/feed/recommended/feedback', {
                video_id: videoId,
                feedback_type: feedbackType
            })
        } catch (error) {
            console.error('Failed to record feedback:', error)
        }
    }

    return {
        recordImpression,
        recordFeedback
    }
}
