<template>
    <StudioLayout>
        <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-900">
            <div
                v-if="showWarning"
                class="bg-white border-b border-gray-200 px-6 py-4"
            >
                <div
                    class="flex items-center justify-between max-w-7xl mx-auto"
                >
                    <div class="flex items-center text-orange-600">
                        <svg
                            class="w-5 h-5 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ $t("studio.unsavedWarning") }}
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="showWarning = false"
                            class="text-gray-600 hover:text-gray-800"
                        >
                            {{ $t("common.discard") }}
                        </button>
                        <button
                            @click="showWarning = false"
                            class="text-gray-900 font-medium hover:text-gray-700"
                        >
                            {{ $t("common.continue") }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="container max-w-7xl mx-auto p-8">
                <div v-if="!uploadedFile" class="max-w-6xl mx-auto">
                    <div
                        @drop="handleDrop"
                        @dragover.prevent
                        @dragenter.prevent
                        class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-gray-400 transition-colors"
                        :class="{ 'border-blue-400 bg-blue-50': isDragging }"
                        @dragenter="isDragging = true"
                        @dragleave="isDragging = false"
                    >
                        <div class="mb-6">
                            <svg
                                class="w-16 h-16 mx-auto text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                />
                            </svg>
                        </div>

                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            {{ $t("studio.selectVideoToUpload") }}
                        </h3>
                        <p class="text-gray-600 mb-6">
                            {{ $t("studio.orDragAndDropItHere") }}
                        </p>

                        <button
                            @click="$refs.fileInput.click()"
                            class="bg-[#F02C56] hover:bg-[#F02C56]/80 text-white px-8 py-3 rounded-md font-medium transition-colors"
                        >
                            {{ $t("studio.selectVideo") }}
                        </button>

                        <input
                            ref="fileInput"
                            type="file"
                            accept="video/mp4"
                            @change="handleFileSelect"
                            class="hidden"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-12">
                        <div class="text-center">
                            <div class="flex items-center justify-center mb-3">
                                <svg
                                    class="w-6 h-6 text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900 mb-2">
                                {{ $t("studio.sizeAndDuration") }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                {{ $t("studio.maximumSize") }}
                                {{ appConfig.media.max_video_size }}
                                {{ $t("studio.mbVideoDuration") }}
                                {{ appConfig.media.max_video_duration / 60 }}
                                {{ $t("studio.minutes") }}
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center mb-3">
                                <svg
                                    class="w-6 h-6 text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900 mb-2">
                                {{ $t("studio.fileFormats") }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                {{ $t("studio.recommended") }}
                                ".{{
                                    appConfig.media.allowed_video_formats.join(
                                        ", .",
                                    )
                                }}"{{
                                    $t("studio.otherMajorFormatsAreSupported")
                                }}
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center mb-3">
                                <svg
                                    class="w-6 h-6 text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900 mb-2">
                                {{ $t("studio.videoResolutions") }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                {{ $t("studio.videoResolutionsMessage") }}
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center mb-3">
                                <svg
                                    class="w-6 h-6 text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900 mb-2">
                                {{ $t("studio.aspectRatios") }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                {{ $t("studio.aspectRatiosMessage") }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6"
                        >
                            <div class="flex items-center">
                                <div class="flex items-center">
                                    <div
                                        class="w-3 h-3 bg-green-500 rounded-full mr-3"
                                    ></div>
                                    <span class="font-medium">{{
                                        uploadedFile.name
                                    }}</span>
                                    <span class="text-sm text-gray-500 ml-2"
                                        >({{
                                            formatFileSize(uploadedFile.size)
                                        }})</span
                                    >
                                </div>
                                <button
                                    class="ml-auto text-gray-400 hover:text-gray-600"
                                >
                                    <span class="text-sm">{{
                                        $t("studio.replace")
                                    }}</span>
                                </button>
                            </div>
                            <div
                                class="w-full bg-green-200 rounded-full h-2 mt-3"
                            >
                                <div
                                    class="bg-green-500 h-2 rounded-full w-full"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                        >
                            <h3 class="text-lg font-medium mb-6">
                                {{ $t("studio.details") }}
                            </h3>

                            <div class="mb-6">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >{{ $t("studio.caption") }}</label
                                >
                                <div class="relative">
                                    <textarea
                                        ref="textareaRef"
                                        v-model="description"
                                        @input="handleDescriptionInput"
                                        @keydown="handleKeydown"
                                        :placeholder="
                                            $t(
                                                'studio.describeYourVideoDotDotDot',
                                            )
                                        "
                                        rows="4"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        maxlength="2200"
                                    ></textarea>

                                    <div
                                        v-if="showAutocomplete"
                                        class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1"
                                        :style="autocompletePosition"
                                    >
                                        <div class="max-h-48 overflow-y-auto">
                                            <div
                                                v-if="isLoadingSuggestions"
                                                class="px-3 py-2 text-gray-500 text-sm flex items-center"
                                            >
                                                <svg
                                                    class="animate-spin -ml-1 mr-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        class="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        stroke-width="4"
                                                    ></circle>
                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                {{
                                                    $t(
                                                        "studio.searchingDotDotDot",
                                                    )
                                                }}
                                            </div>

                                            <div
                                                v-else-if="
                                                    !isLoadingSuggestions &&
                                                    filteredSuggestions.length ===
                                                        0 &&
                                                    autocompleteQuery
                                                "
                                                class="px-3 py-2 text-gray-500 text-sm"
                                            >
                                                {{ $t("studio.no") }}
                                                {{
                                                    autocompleteType ===
                                                    "hashtag"
                                                        ? $t("studio.hashtags")
                                                        : $t("studio.users")
                                                }}
                                                {{ $t("studio.found") }}
                                            </div>

                                            <div
                                                v-for="(
                                                    item, index
                                                ) in filteredSuggestions"
                                                :key="index"
                                                @click="selectSuggestion(item)"
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center"
                                                :class="{
                                                    'bg-blue-50':
                                                        index ===
                                                        selectedSuggestionIndex,
                                                }"
                                            >
                                                <div
                                                    v-if="
                                                        autocompleteType ===
                                                        'mention'
                                                    "
                                                    class="w-8 h-8 bg-gray-300 rounded-full mr-3 flex items-center justify-center overflow-hidden"
                                                >
                                                    <img
                                                        v-if="item.avatar"
                                                        :src="item.avatar"
                                                        :alt="item.name"
                                                        class="w-full h-full object-cover"
                                                        @error="
                                                            $event.target.src =
                                                                '/storage/avatars/default.jpg'
                                                        "
                                                    />
                                                    <span
                                                        v-else
                                                        class="text-xs font-medium"
                                                        >{{
                                                            item.username
                                                                .charAt(0)
                                                                .toUpperCase()
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-else
                                                    class="w-8 h-8 bg-blue-100 rounded-full mr-3 flex items-center justify-center"
                                                >
                                                    <span class="text-blue-600"
                                                        >#</span
                                                    >
                                                </div>
                                                <div>
                                                    <div class="font-medium">
                                                        {{
                                                            autocompleteType ===
                                                            "hashtag"
                                                                ? "#"
                                                                : "@"
                                                        }}{{ item.name }}
                                                    </div>
                                                    <div class="flex gap-3">
                                                        <div
                                                            v-if="item.count"
                                                            class="text-xs text-gray-500"
                                                        >
                                                            {{ item.count }}
                                                            posts
                                                        </div>
                                                        <div
                                                            v-if="
                                                                item.post_count
                                                            "
                                                            class="text-xs text-gray-500"
                                                        >
                                                            {{
                                                                item.post_count
                                                            }}
                                                            posts
                                                        </div>
                                                        <div
                                                            v-if="
                                                                item.follower_count
                                                            "
                                                            class="text-xs text-gray-500"
                                                        >
                                                            {{
                                                                item.follower_count
                                                            }}
                                                            followers
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="absolute bottom-2 left-2 text-xs text-gray-400"
                                    ></div>
                                    <div
                                        class="absolute bottom-2 right-2 text-xs text-gray-400"
                                    >
                                        {{ description.length }}/240
                                    </div>
                                </div>
                                <div class="flex space-x-4 -mt-1">
                                    <button
                                        @click="insertHashtag"
                                        class="text-gray-600 hover:text-gray-700 font-bold text-xs cursor-pointer"
                                    >
                                        # {{ $t("studio.hashtag") }}
                                    </button>
                                    <button
                                        @click="insertMention"
                                        class="text-gray-600 hover:text-gray-700 font-bold text-xs cursor-pointer"
                                    >
                                        @ {{ $t("studio.mention") }}
                                    </button>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >{{ $t("studio.altText") }}</label
                                >
                                <div class="relative">
                                    <textarea
                                        v-model="altText"
                                        :placeholder="
                                            $t(
                                                'studio.describeYourVideoDotDotDotAltText',
                                            )
                                        "
                                        rows="2"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        maxlength="2000"
                                    ></textarea>

                                    <div
                                        class="absolute bottom-2 left-2 text-xs text-gray-400"
                                    ></div>
                                    <div
                                        class="absolute bottom-2 right-2 text-xs text-gray-400"
                                    >
                                        {{ altText.length }}/2000
                                    </div>
                                </div>
                                <p
                                    class="text-sm text-gray-500 flex items-start gap-1"
                                >
                                    <svg
                                        class="w-4 h-4 mt-0.5 flex-shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <span>{{ $t("studio.altTextHelp") }}</span>
                                </p>
                            </div>

                            <div class="mb-6">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    {{ $t("studio.language") }}
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="settings.lang"
                                        class="block w-full px-4 py-2 pr-8 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    >
                                        <option
                                            v-for="lang in languages"
                                            :key="lang.code"
                                            :value="lang.code"
                                        >
                                            {{ lang.name }}
                                        </option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none"
                                    >
                                        <svg
                                            class="w-5 h-5 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $t("studio.selectLanguageHelp") }}
                                </p>
                            </div>

                            <div class="hidden mb-6">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >{{ $t("studio.customCover") }}</label
                                >
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="relative w-24 h-32 bg-gray-200 rounded-md flex items-center justify-center overflow-hidden"
                                    >
                                        <img
                                            v-if="coverImage"
                                            :src="coverImage"
                                            alt="Cover"
                                            class="w-full h-full object-cover rounded-md"
                                        />
                                        <svg
                                            v-else
                                            class="w-8 h-8 text-gray-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <button
                                            v-if="coverImage"
                                            @click="removeCoverImage"
                                            class="absolute top-1 right-1 w-6 h-6 bg-black bg-opacity-50 rounded-full flex items-center justify-center text-white hover:bg-opacity-70"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex flex-col space-y-2">
                                        <button
                                            @click="$refs.coverInput.click()"
                                            class="text-blue-600 hover:text-blue-700 text-sm"
                                        >
                                            {{
                                                coverImage
                                                    ? "Change cover"
                                                    : "Upload cover"
                                            }}
                                        </button>
                                    </div>
                                    <input
                                        ref="coverInput"
                                        type="file"
                                        accept="image/*"
                                        @change="handleCoverUpload"
                                        class="hidden"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6"
                        >
                            <h3 class="text-lg font-medium mb-6">
                                {{ $t("studio.settings") }}
                            </h3>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-3"
                                    >{{ $t("studio.allowOthersTo") }}:</label
                                >
                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <span
                                                class="text-sm font-medium text-gray-700"
                                                >{{
                                                    $t("common.comment")
                                                }}</span
                                            >
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    $t("studio.commentMessage")
                                                }}
                                            </p>
                                        </div>
                                        <ToggleSwitch
                                            v-model="settings.allowComments"
                                        />
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <span
                                                class="text-sm font-medium text-gray-700"
                                                >{{
                                                    $t("studio.download")
                                                }}</span
                                            >
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    $t("studio.downloadMessage")
                                                }}
                                            </p>
                                        </div>
                                        <ToggleSwitch
                                            v-model="settings.allowDownloads"
                                        />
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <span
                                                class="text-sm font-medium text-gray-700"
                                                >{{ $t("studio.duet") }}</span
                                            >
                                            <p class="text-xs text-gray-500">
                                                {{ $t("studio.duetMessage") }}
                                            </p>
                                        </div>
                                        <ToggleSwitch
                                            v-model="settings.allowDuets"
                                        />
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <span
                                                class="text-sm font-medium text-gray-700"
                                                >{{ $t("studio.stitch") }}</span
                                            >
                                            <p class="text-xs text-gray-500">
                                                {{ $t("studio.stitchMessage") }}
                                            </p>
                                        </div>
                                        <ToggleSwitch
                                            v-model="settings.allowStitch"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="w-full h-[1px] bg-gray-100 my-6"></div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >{{
                                                $t("studio.containsNSFW")
                                            }}</span
                                        >
                                        <p class="text-xs text-gray-500">
                                            {{
                                                $t("studio.containsNSFWMessage")
                                            }}
                                        </p>
                                    </div>
                                    <ToggleSwitch v-model="settings.nsfw" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >{{
                                                $t("studio.disclosePostContent")
                                            }}</span
                                        >
                                        <p class="text-xs text-gray-500">
                                            {{
                                                $t(
                                                    "studio.disclosePostContentHelp",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <ToggleSwitch
                                        v-model="settings.containsAd"
                                    />
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="max-w-[70%]">
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >{{
                                                $t(
                                                    "studio.containsAlteredContent",
                                                )
                                            }}</span
                                        >
                                        <div class="text-xs text-gray-500">
                                            {{
                                                $t(
                                                    "studio.containsAlteredContentHelp",
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <ToggleSwitch
                                        v-model="settings.containsAi"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="sticky top-6">
                            <div
                                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4"
                            >
                                <div
                                    class="flex justify-center space-x-4 mb-4 text-sm"
                                >
                                    <div
                                        class="flex bg-gray-100 dark:bg-slate-800 rounded-lg p-1"
                                    >
                                        <button
                                            :class="[
                                                'px-3 py-1.5 text-[14px] font-medium rounded-md transition-all duration-200 relative cursor-pointer',
                                                previewMode === 'feed'
                                                    ? 'bg-white dark:bg-slate-700 text-black dark:text-white shadow-sm'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200',
                                            ]"
                                            @click="previewMode = 'feed'"
                                        >
                                            {{ $t("studio.feed") }}
                                        </button>
                                        <button
                                            :class="[
                                                'px-3 py-1.5 text-[14px] font-medium rounded-md transition-all duration-200 relative cursor-pointer',
                                                previewMode === 'profile'
                                                    ? 'bg-white dark:bg-slate-700 text-black dark:text-white shadow-sm'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200',
                                            ]"
                                            @click="previewMode = 'profile'"
                                        >
                                            {{ $t("common.profile") }}
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="bg-black rounded-2xl p-2"
                                    style="
                                        width: 250px;
                                        height: 500px;
                                        margin: 0 auto;
                                    "
                                >
                                    <div
                                        class="relative w-full h-full rounded-xl overflow-hidden bg-black"
                                    >
                                        <video
                                            v-if="videoPreviewUrl"
                                            ref="videoPreviewEl"
                                            :src="videoPreviewUrl"
                                            class="w-full h-full object-cover"
                                            playsinline
                                            muted
                                            :controls="false"
                                        ></video>

                                        <div
                                            v-else
                                            class="w-full h-full flex flex-col items-center justify-center text-white/70 text-xs bg-gray-800"
                                        >
                                            <span class="text-lg mb-1"
                                                >No preview</span
                                            >
                                            <span
                                                >Upload a video to see it
                                                here</span
                                            >
                                        </div>

                                        <div
                                            v-if="videoPreviewUrl"
                                            class="pointer-events-none absolute inset-x-0 px-3 pb-2 bg-gradient-to-t from-black/80 via-black/10 to-transparent pt-10"
                                            :class="[
                                                videoPreviewUrl &&
                                                previewMode === 'feed'
                                                    ? 'bottom-[46px]'
                                                    : 'bottom-0',
                                            ]"
                                        >
                                            <p
                                                class="text-white font-semibold text-sm mb-1"
                                            >
                                                {{ previewUsername }}
                                            </p>
                                            <p
                                                class="text-white/90 text-[12px] leading-snug line-clamp-1 overflow-hidden break-words"
                                            >
                                                {{ previewCaption }}
                                            </p>
                                        </div>

                                        <MockUIIcons
                                            v-if="
                                                videoPreviewUrl &&
                                                previewMode === 'feed'
                                            "
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <button
                                    @click="handleSubmit"
                                    :disabled="!canSubmit || isSubmitting"
                                    class="w-full py-3 rounded-md font-medium transition-colors flex items-center justify-center"
                                    :class="
                                        canSubmit && !isSubmitting
                                            ? 'bg-[#F02C56] border border-[#F02C56] hover:bg-[#F02C56]/80 text-white'
                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    "
                                >
                                    <svg
                                        v-if="isSubmitting"
                                        class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    {{ isSubmitting ? "Posting..." : "Post" }}
                                </button>
                                <button
                                    @click="handleDiscard"
                                    class="w-full border border-gray-300 text-gray-700 py-3 rounded-md font-medium hover:bg-gray-50 transition-colors"
                                    :disabled="isSubmitting"
                                >
                                    {{ $t("common.discard") }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudioLayout>

    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isSubmitting"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm"
            >
                <div class="text-center space-y-6">
                    <div class="flex justify-center">
                        <Spinner size="xl" speed="slow" />
                    </div>

                    <div class="space-y-3">
                        <h2 class="text-2xl font-semibold text-white">
                            Uploading Your Loop
                        </h2>
                        <p class="text-gray-300 text-lg">
                            Please don't close this window...
                        </p>

                        <div class="w-80 mx-auto">
                            <div
                                class="bg-gray-700 rounded-full h-3 overflow-hidden"
                            >
                                <div
                                    class="bg-gradient-to-r from-[#ed5b7bff] to-[#F02C56] h-full transition-all duration-300 ease-out"
                                    :style="{ width: `${uploadProgress}%` }"
                                ></div>
                            </div>
                            <p class="text-white text-sm mt-2 font-medium">
                                {{ uploadProgress }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    nextTick,
    watch,
    inject,
    onMounted,
    onBeforeUnmount,
} from "vue";
import { storeToRefs } from "pinia";
import { useRouter, onBeforeRouteLeave } from "vue-router";
import { useAlertModal } from "@/composables/useAlertModal.js";

const router = useRouter();
const axios = inject("axios");
const showWarning = ref(false);
const uploadedFile = ref(null);
const isDragging = ref(false);
const description = ref("");
const coverImage = ref(null);
const privacy = ref("everyone");
const previewMode = ref("feed");
const isSubmitting = ref(false);
const uploadProgress = ref(0);
const appConfig = inject("appConfig");
const authStore = inject("authStore");
const appStore = inject("appStore");
const { languages } = storeToRefs(appStore);
const { alertModal, confirmModal } = useAlertModal();

const showAutocomplete = ref(false);
const autocompleteType = ref("");
const autocompleteQuery = ref("");
const selectedSuggestionIndex = ref(0);
const textareaRef = ref(null);
const autocompletePosition = ref({ top: "100%", left: "0" });
const isLoadingSuggestions = ref(false);
const apiSuggestions = ref([]);
const searchTimeout = ref(null);
const videoPreviewUrl = ref("");
const videoPreviewEl = ref(null);

const videoPreview = ref(null);
const fileDisplay = ref(null);
const errorType = ref(null);
const caption = ref("");
const altText = ref("");
const fileData = ref(null);
const errors = ref(null);
const isUploading = ref(false);
const isConverting = ref(false);
const progress = ref(0);
const needsConversion = ref(true);
const convertedFile = ref(null);

const ffmpegInstance = ref(null);
const isFFmpegLoaded = ref(false);

const suggestionCache = reactive({
    hashtags: new Map(),
    mentions: new Map(),
});

const settings = reactive({
    lang: "en",
    nsfw: false,
    allowComments: true,
    allowDownloads: true,
    allowDuets: true,
    allowStitch: true,
    containsAd: false,
    containsAi: false,
});

onBeforeRouteLeave((to, from, next) => {
    if (isSubmitting.value) {
        confirmModal(
            "Upload in Progress",
            '<p class="text-gray-700">Your video is still uploading. If you leave now, the upload will be cancelled.</p><p class="text-gray-700 mt-2">Are you sure you want to leave?</p>',
            "Leave Anyway",
            "Stay on Page",
        ).then((confirmed) => {
            if (confirmed) {
                next();
            } else {
                next(false);
            }
        });
    } else {
        next();
    }
});

const handleBeforeUnload = (e) => {
    if (isSubmitting.value) {
        e.preventDefault();
        e.returnValue = "";
        return "";
    }
};

onMounted(async () => {
    window.addEventListener("beforeunload", handleBeforeUnload);
    await appStore.ensureLanguages();
});

onBeforeUnmount(() => {
    window.removeEventListener("beforeunload", handleBeforeUnload);
});

const previewUsername = computed(() => {
    if (authStore?.user?.username) {
        return `@${authStore.user?.username}`;
    }

    if (authStore?.user?.name) {
        return `@${authStore.user?.name}`;
    }
    return "@username";
});

const previewCaption = computed(() => {
    return description.value?.trim()
        ? description.value.trim()
        : "Add a caption to tell people what this loop is about ✨";
});

const canSubmit = computed(() => {
    return uploadedFile.value;
});

const filteredSuggestions = computed(() => {
    return apiSuggestions.value;
});

const searchHashtags = async (query) => {
    try {
        const cacheKey = query.toLowerCase();
        if (suggestionCache.hashtags.has(cacheKey)) {
            return suggestionCache.hashtags.get(cacheKey);
        }

        const response = await axios.get("/api/v1/autocomplete/tags", {
            params: { q: query, limit: 6 },
        });

        const results = response.data.data || [];
        suggestionCache.hashtags.set(cacheKey, results);

        return results;
    } catch (error) {
        console.error("Error searching hashtags:", error);
        return [];
    }
};

const searchMentions = async (query) => {
    try {
        const cacheKey = query.toLowerCase();
        if (suggestionCache.mentions.has(cacheKey)) {
            return suggestionCache.mentions.get(cacheKey);
        }

        const response = await axios.get("/api/v1/autocomplete/accounts", {
            params: { q: query, limit: 6 },
        });

        const results = response.data.data || [];

        suggestionCache.mentions.set(cacheKey, results);

        return results;
    } catch (error) {
        console.error("Error searching mentions:", error);
        return [];
    }
};

const debouncedSearch = async (query, type) => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    if (query.length < 1) {
        apiSuggestions.value = [];
        isLoadingSuggestions.value = false;
        return;
    }

    isLoadingSuggestions.value = true;

    searchTimeout.value = setTimeout(async () => {
        try {
            let results = [];

            if (type === "hashtag") {
                results = await searchHashtags(query);
            } else if (type === "mention") {
                results = await searchMentions(query);
            }

            apiSuggestions.value = results;
        } catch (error) {
            console.error("Search error:", error);
            apiSuggestions.value = [];
        } finally {
            isLoadingSuggestions.value = false;
        }
    }, 300);
};

const handleFileSelect = async (event) => {
    const file = event.target.files[0];
    if (file && file.type.startsWith("video/")) {
        if (videoPreviewUrl.value) {
            URL.revokeObjectURL(videoPreviewUrl.value);
        }

        uploadedFile.value = file;
        videoPreviewUrl.value = URL.createObjectURL(file);

        await checkVideoResolution(file);

        nextTick(() => {
            if (videoPreviewEl.value) {
                videoPreviewEl.value.pause();
                videoPreviewEl.value.currentTime = 0;
            }
        });
    }
};

const handleDrop = async (event) => {
    event.preventDefault();
    isDragging.value = false;

    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith("video/")) {
        if (videoPreviewUrl.value) {
            URL.revokeObjectURL(videoPreviewUrl.value);
        }

        uploadedFile.value = file;
        videoPreviewUrl.value = URL.createObjectURL(file);

        await checkVideoResolution(file);

        nextTick(() => {
            if (videoPreviewEl.value) {
                videoPreviewEl.value.pause();
                videoPreviewEl.value.currentTime = 0;
            }
        });
    }
};

const handleCoverUpload = (event) => {
    const file = event.target.files[0];
    if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (e) => {
            coverImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeCoverImage = () => {
    coverImage.value = null;
};

const handleDescriptionInput = async (event) => {
    const textarea = event.target;
    const cursorPos = textarea.selectionStart;
    const textBeforeCursor = description.value.substring(0, cursorPos);

    const hashtagMatch = textBeforeCursor.match(/#(\w*)$/);
    const mentionMatch = textBeforeCursor.match(/@(\w*)$/);

    if (hashtagMatch) {
        autocompleteType.value = "hashtag";
        autocompleteQuery.value = hashtagMatch[1];
        showAutocomplete.value = true;
        selectedSuggestionIndex.value = 0;

        await debouncedSearch(hashtagMatch[1], "hashtag");
    } else if (mentionMatch) {
        autocompleteType.value = "mention";
        autocompleteQuery.value = mentionMatch[1];
        showAutocomplete.value = true;
        selectedSuggestionIndex.value = 0;

        await debouncedSearch(mentionMatch[1], "mention");
    } else {
        showAutocomplete.value = false;
        apiSuggestions.value = [];
        isLoadingSuggestions.value = false;

        if (searchTimeout.value) {
            clearTimeout(searchTimeout.value);
        }
    }
};

const handleKeydown = (event) => {
    if (!showAutocomplete.value) return;

    if (event.key === "ArrowDown") {
        event.preventDefault();
        selectedSuggestionIndex.value = Math.min(
            selectedSuggestionIndex.value + 1,
            filteredSuggestions.value.length - 1,
        );
    } else if (event.key === "ArrowUp") {
        event.preventDefault();
        selectedSuggestionIndex.value = Math.max(
            selectedSuggestionIndex.value - 1,
            0,
        );
    } else if (event.key === "Enter" || event.key === "Tab") {
        event.preventDefault();
        if (filteredSuggestions.value[selectedSuggestionIndex.value]) {
            selectSuggestion(
                filteredSuggestions.value[selectedSuggestionIndex.value],
            );
        }
    } else if (event.key === "Escape") {
        showAutocomplete.value = false;
    }
};

const selectSuggestion = (suggestion) => {
    const textarea = textareaRef.value;
    const cursorPos = textarea.selectionStart;
    const textBeforeCursor = description.value.substring(0, cursorPos);
    const textAfterCursor = description.value.substring(cursorPos);

    let newText;
    if (autocompleteType.value === "hashtag") {
        const beforeHash = textBeforeCursor.replace(/#\w*$/, "");
        newText = beforeHash + "#" + suggestion.name + " " + textAfterCursor;
    } else {
        const beforeMention = textBeforeCursor.replace(/@\w*$/, "");
        newText =
            beforeMention + "@" + suggestion.username + " " + textAfterCursor;
    }

    description.value = newText;
    showAutocomplete.value = false;

    nextTick(() => {
        const newCursorPos = (
            autocompleteType.value === "hashtag"
                ? textBeforeCursor.replace(/#\w*$/, "") +
                  "#" +
                  suggestion.name +
                  " "
                : textBeforeCursor.replace(/@\w*$/, "") +
                  "@" +
                  suggestion.name +
                  " "
        ).length;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
        textarea.focus();
    });
};

const insertHashtag = () => {
    const textarea = textareaRef.value;
    const cursorPos = textarea.selectionStart;
    const beforeText = description.value.substring(0, cursorPos);
    const afterText = description.value.substring(cursorPos);

    description.value = beforeText + "#" + afterText;

    nextTick(() => {
        textarea.setSelectionRange(cursorPos + 1, cursorPos + 1);
        textarea.focus();
    });
};

const insertMention = () => {
    const textarea = textareaRef.value;
    const cursorPos = textarea.selectionStart;
    const beforeText = description.value.substring(0, cursorPos);
    const afterText = description.value.substring(cursorPos);

    description.value = beforeText + "@" + afterText;

    nextTick(() => {
        textarea.setSelectionRange(cursorPos + 1, cursorPos + 1);
        textarea.focus();
    });
};

const handleDiscard = () => {
    if (
        confirm(
            "Are you sure you want to discard this video? All changes will be lost.",
        )
    ) {
        resetForm();
    }
};

const resetForm = () => {
    uploadedFile.value = null;
    description.value = "";
    altText.value = "";
    coverImage.value = null;
    privacy.value = "everyone";
    settings.allowComments = true;
    settings.allowDuet = true;
    settings.allowStitch = true;
    settings.containsAd = false;
    settings.containsAi = false;
    showAutocomplete.value = false;
    apiSuggestions.value = [];
    isLoadingSuggestions.value = false;

    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
        searchTimeout.value = null;
    }

    suggestionCache.hashtags.clear();
    suggestionCache.mentions.clear();
};

const checkVideoResolution = async (file) => {
    return new Promise((resolve) => {
        const video = document.createElement("video");
        video.preload = "metadata";

        video.onloadedmetadata = function () {
            console.log(video);
            URL.revokeObjectURL(video.src);
            const width = video.videoWidth;
            const height = video.videoHeight;
            const duration = video.duration;

            needsConversion.value = true;

            console.log(
                `Video dimensions: ${width}x${height}, duration: ${duration}s, needs conversion: ${needsConversion.value}`,
            );
            resolve({ width, height, duration });
        };

        video.onerror = function () {
            console.error("Error loading video metadata");
            resolve({ width: 0, height: 0, duration: 0 });
        };

        video.src = URL.createObjectURL(file);
    });
};

const handleSubmit = async () => {
    if (!canSubmit.value || isSubmitting.value) return;

    isSubmitting.value = true;
    uploadProgress.value = 0;

    const videoToUpload = uploadedFile.value;
    let progressInterval = null;
    let hasReceivedResponse = false;

    const startSimulatedProgress = () => {
        let progress = 0;
        const startTime = Date.now();

        progressInterval = setInterval(() => {
            const elapsedSeconds = (Date.now() - startTime) / 1000;

            if (elapsedSeconds < 10) {
                progress = (elapsedSeconds / 10) * 50;
            } else if (elapsedSeconds < 20) {
                progress = 50 + ((elapsedSeconds - 10) / 10) * 30;
            } else if (elapsedSeconds < 40) {
                progress = 80 + ((elapsedSeconds - 20) / 20) * 10;
            } else {
                progress = 90;
            }

            if (!hasReceivedResponse) {
                uploadProgress.value = Math.min(Math.round(progress), 90);
            }
        }, 100);
    };

    startSimulatedProgress();

    try {
        const formData = new FormData();
        formData.append("video", videoToUpload);
        formData.append("description", description.value);
        formData.append("alt_text", altText.value);
        formData.append("lang", settings.lang);
        formData.append("comment_state", settings.allowComments ? 4 : 0);
        formData.append("can_download", settings.allowDownloads);
        formData.append("is_sensitive", settings.nsfw ? 1 : 0);
        formData.append("contains_ad", settings.containsAd ? 1 : 0);
        formData.append("contains_ai", settings.containsAi ? 1 : 0);

        if (coverImage.value) {
            formData.append("cover_image", coverImage.value);
        }

        const response = await axios.post("/api/v1/studio/upload", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            timeout: 120000,
            validateStatus: function (status) {
                return status >= 200 && status < 300;
            },
        });

        hasReceivedResponse = true;
        if (progressInterval) {
            clearInterval(progressInterval);
        }

        if (!response || !response.data || typeof response.data !== "object") {
            throw new Error("Invalid response from server");
        }

        const isSuccess =
            response.data.success === true ||
            response.data.status === "success" ||
            response.status === 200;

        if (!isSuccess) {
            throw new Error(
                response.data.message || "Upload failed - invalid response",
            );
        }

        uploadProgress.value = 95;
        await new Promise((resolve) => setTimeout(resolve, 200));
        uploadProgress.value = 100;
        await new Promise((resolve) => setTimeout(resolve, 300));

        resetForm();
        router.push("/studio/posts");
    } catch (error) {
        hasReceivedResponse = true;
        if (progressInterval) {
            clearInterval(progressInterval);
        }

        console.error("Error posting video:", error);

        let errorMessage = "Error posting video. Please try again.";
        let errorTitle = "Upload Failed";
        let errorDetails = null;

        const status = error.response?.status;
        const responseData = error.response?.data;
        const errorCode = error.code;
        const errorMsg = error.message;

        if (errorCode === "ECONNABORTED" || errorMsg?.includes("timeout")) {
            errorTitle = "Upload Timeout";
            errorMessage =
                "The upload took too long and timed out. This usually happens with larger videos. Please try uploading a smaller video or check your internet connection.";
        } else if (status === 500) {
            errorTitle = "Server Error";

            const hasTimeoutError =
                responseData?.message
                    ?.toLowerCase()
                    .includes("maximum execution time") ||
                responseData?.message
                    ?.toLowerCase()
                    .includes("execution time") ||
                responseData?.exception
                    ?.toLowerCase()
                    .includes("maximum execution time");

            if (hasTimeoutError) {
                errorMessage =
                    "The server took too long to process your video. This usually happens with large files. Please try again with a smaller video or contact support if the issue persists.";
                errorDetails = "Server timeout error";
            } else if (responseData?.message) {
                errorMessage = responseData.message;
            } else {
                errorMessage =
                    "A server error occurred while uploading your video. Please try again later.";
            }
        } else if (status >= 400 && status < 500) {
            if (
                responseData?.errors &&
                typeof responseData.errors === "object"
            ) {
                errors.value = responseData.errors;
                const firstErrorArray = Object.values(responseData.errors)[0];
                errorMessage = Array.isArray(firstErrorArray)
                    ? firstErrorArray[0]
                    : firstErrorArray;
            } else if (responseData?.message) {
                errorMessage = responseData.message;
            } else if (responseData?.error) {
                errorMessage = responseData.error;
            }
        } else if (errorMsg === "Network Error") {
            errorTitle = "Network Error";
            errorMessage =
                "Unable to connect to the server. Please check your internet connection and try again.";
        } else if (errorMsg) {
            errorMessage = errorMsg;
        }

        let errorHtml = `<div class="space-y-3">
            <p class="text-gray-700">${errorMessage}</p>`;

        if (status) {
            errorHtml += `<p class="text-sm text-gray-500">Error code: ${status}</p>`;
        }

        if (errorDetails) {
            errorHtml += `<p class="text-xs text-gray-400 mt-2">${errorDetails}</p>`;
        }

        errorHtml += `</div>`;

        await alertModal(errorTitle, errorHtml);
    } finally {
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        isSubmitting.value = false;
        uploadProgress.value = 0;
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};
</script>
