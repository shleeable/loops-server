<template>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
    >
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Thumbnail</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Shown in feeds, on your profile and in link previews. Cropped to 9:16.
            </p>
        </div>

        <div class="p-4">
            <div v-if="stage === 'idle'" class="flex items-center gap-4">
                <img
                    :src="currentThumbnail"
                    alt="Current thumbnail"
                    class="w-16 h-28 rounded-md object-cover bg-gray-100 dark:bg-gray-900 shrink-0"
                />
                <div class="flex-1 min-w-0">
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="handleFileChange"
                    />
                    <button
                        type="button"
                        @click="fileInput?.click()"
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                    >
                        Choose new image
                    </button>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        JPEG, PNG or WebP up to {{ maxSizeMb }}MB.
                    </p>
                    <p
                        v-if="errorMessage"
                        class="text-xs text-red-600 dark:text-red-400 mt-2"
                        role="alert"
                    >
                        {{ errorMessage }}
                    </p>
                    <p
                        v-else-if="successMessage"
                        class="text-xs text-green-600 dark:text-green-400 mt-2"
                    >
                        {{ successMessage }}
                    </p>
                </div>
            </div>

            <div v-else-if="stage === 'crop'" class="space-y-3">
                <div class="relative rounded-md overflow-hidden bg-gray-900 aspect-[3/4]">
                    <Cropper
                        ref="cropperRef"
                        class="absolute inset-0"
                        :src="sourceUrl"
                        :stencil-props="{ aspectRatio: 9 / 16, movable: true, resizable: true }"
                        image-restriction="stencil"
                        :canvas="{ maxWidth: 2160, maxHeight: 3840 }"
                    />
                </div>
                <div class="flex items-center justify-between gap-3">
                    <button
                        type="button"
                        @click="cancelCrop"
                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="rotate(-90)"
                            class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                            aria-label="Rotate left"
                        >
                            <ArrowUturnLeftIcon class="w-4 h-4" />
                        </button>
                        <button
                            type="button"
                            @click="rotate(90)"
                            class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                            aria-label="Rotate right"
                        >
                            <ArrowUturnRightIcon class="w-4 h-4" />
                        </button>
                        <button
                            type="button"
                            @click="confirmCrop"
                            :disabled="isProcessing"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#F02C56] hover:bg-red-600 disabled:bg-red-300 disabled:cursor-not-allowed rounded-md transition-colors flex items-center cursor-pointer"
                        >
                            <Spinner v-if="isProcessing" size="sm" class="mr-2" />
                            {{ isProcessing ? 'Preparing...' : 'Use this crop' }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-else-if="stage === 'upload'" class="flex items-start gap-4">
                <img
                    :src="previewUrl"
                    alt="New thumbnail preview"
                    class="w-16 h-28 rounded-md object-cover bg-gray-100 dark:bg-gray-900 shrink-0"
                />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ isUploading ? 'Uploading thumbnail' : 'Ready to upload' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        1080 x 1920, {{ formatBytes(outputBlob?.size) }}
                    </p>
                    <div
                        v-if="isUploading"
                        class="mt-2 h-1.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden"
                    >
                        <div
                            class="h-full bg-[#F02C56] transition-all duration-150"
                            :style="{ width: `${uploadProgress}%` }"
                        ></div>
                    </div>
                    <p
                        v-if="errorMessage"
                        class="text-xs text-red-600 dark:text-red-400 mt-2"
                        role="alert"
                    >
                        {{ errorMessage }}
                    </p>
                    <div v-if="!isUploading" class="flex flex-col items-center gap-2 mt-3">
                        <button
                            type="button"
                            @click="backToCrop"
                            class="w-full px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                        >
                            Adjust crop
                        </button>
                        <button
                            type="button"
                            @click="upload"
                            class="w-full px-3 py-1.5 text-xs font-medium text-white bg-[#F02C56] hover:bg-red-600 rounded-md transition-colors cursor-pointer"
                        >
                            Upload thumbnail
                        </button>
                        <button
                            type="button"
                            @click="reset"
                            class="w-full px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, onBeforeUnmount } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import Pica from 'pica'
import { ArrowUturnLeftIcon, ArrowUturnRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    videoId: {
        type: [String, Number],
        required: true
    },
    currentThumbnail: {
        type: String,
        default: '/storage/videos/video-placeholder.jpg'
    },
    endpoint: {
        type: String,
        default: null
    },
    maxSizeMb: {
        type: Number,
        default: 10
    }
})

const emit = defineEmits(['updated'])

const axios = inject('axios')
const pica = new Pica()

const OUTPUT_WIDTH = 1080
const OUTPUT_HEIGHT = 1920
const OUTPUT_QUALITY = 0.86
const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp']

const fileInput = ref(null)
const cropperRef = ref(null)
const stage = ref('idle')
const sourceUrl = ref(null)
const previewUrl = ref(null)
const outputBlob = ref(null)
const isProcessing = ref(false)
const isUploading = ref(false)
const uploadProgress = ref(0)
const errorMessage = ref('')
const successMessage = ref('')

const uploadUrl = () => props.endpoint || `/api/v1/studio/posts/${props.videoId}/thumbnail`

const revoke = (url) => {
    if (url) URL.revokeObjectURL(url)
}

const reset = () => {
    revoke(sourceUrl.value)
    revoke(previewUrl.value)
    sourceUrl.value = null
    previewUrl.value = null
    outputBlob.value = null
    uploadProgress.value = 0
    isProcessing.value = false
    isUploading.value = false
    errorMessage.value = ''
    stage.value = 'idle'
    if (fileInput.value) fileInput.value.value = ''
}

const handleFileChange = (event) => {
    const file = event.target.files?.[0]
    if (!file) return

    errorMessage.value = ''
    successMessage.value = ''

    if (!ACCEPTED_TYPES.includes(file.type)) {
        errorMessage.value = 'That file type is not supported. Use a JPEG, PNG or WebP image.'
        event.target.value = ''
        return
    }

    if (file.size > props.maxSizeMb * 1024 * 1024) {
        errorMessage.value = `That image is too large. Choose one under ${props.maxSizeMb}MB.`
        event.target.value = ''
        return
    }

    revoke(sourceUrl.value)
    sourceUrl.value = URL.createObjectURL(file)
    stage.value = 'crop'
}

const rotate = (degrees) => {
    cropperRef.value?.rotate(degrees)
}

const cancelCrop = () => {
    reset()
}

const backToCrop = () => {
    revoke(previewUrl.value)
    previewUrl.value = null
    outputBlob.value = null
    errorMessage.value = ''
    stage.value = 'crop'
}

const canvasToBlob = (canvas, type, quality) =>
    new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => (blob ? resolve(blob) : reject(new Error('Could not encode image'))),
            type,
            quality
        )
    })

const confirmCrop = async () => {
    const result = cropperRef.value?.getResult()
    if (!result?.canvas) {
        errorMessage.value = 'Could not read the crop. Try choosing the image again.'
        return
    }

    isProcessing.value = true
    errorMessage.value = ''

    try {
        const target = document.createElement('canvas')
        target.width = OUTPUT_WIDTH
        target.height = OUTPUT_HEIGHT

        await pica.resize(result.canvas, target, {
            quality: 3,
            unsharpAmount: 60,
            unsharpRadius: 0.6,
            unsharpThreshold: 2
        })

        const blob = await canvasToBlob(target, 'image/jpeg', OUTPUT_QUALITY)

        revoke(previewUrl.value)
        outputBlob.value = blob
        previewUrl.value = URL.createObjectURL(blob)
        stage.value = 'upload'
    } catch (error) {
        console.error('Error preparing thumbnail:', error)
        errorMessage.value = 'Could not prepare the image. Try a different file.'
    } finally {
        isProcessing.value = false
    }
}

const upload = async () => {
    if (!outputBlob.value) return

    isUploading.value = true
    uploadProgress.value = 0
    errorMessage.value = ''

    const formData = new FormData()
    formData.append('thumbnail', outputBlob.value, `thumbnail-${props.videoId}.jpg`)

    try {
        const response = await axios.post(uploadUrl(), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (e) => {
                if (e.total) uploadProgress.value = Math.round((e.loaded / e.total) * 100)
            }
        })

        const data = response.data?.data ?? response.data
        emit('updated', {
            thumbnail: data?.media?.thumbnail ?? data?.thumbnail ?? null,
            video: data?.media ? data : null
        })

        successMessage.value = 'Thumbnail updated.'
        reset()
        setTimeout(() => (successMessage.value = ''), 4000)
    } catch (error) {
        console.error('Error uploading thumbnail:', error)
        errorMessage.value =
            error?.response?.data?.message || 'Upload failed. Check your connection and try again.'
        isUploading.value = false
    }
}

const formatBytes = (bytes) => {
    if (!bytes) return ''
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

onBeforeUnmount(() => {
    revoke(sourceUrl.value)
    revoke(previewUrl.value)
})
</script>
