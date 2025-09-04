import axios from "~/plugins/axios";

const API_BASE_URL = "/api/admin";

// Cursor pagination utility
const createCursorPagination = (data, currentCursor, limit = 15) => {
    const startIndex = currentCursor ? parseInt(currentCursor) : 0;
    const endIndex = startIndex + limit;
    const paginatedData = data.slice(startIndex, endIndex);

    return {
        data: paginatedData,
        pagination: {
            currentCursor: currentCursor,
            nextCursor: endIndex < data.length ? endIndex.toString() : null,
            previousCursor:
                startIndex > 0
                    ? Math.max(0, startIndex - limit).toString()
                    : null,
            hasNext: endIndex < data.length,
            hasPrevious: startIndex > 0,
            total: data.length,
        },
    };
};

// API delay simulation
const delay = (ms = 500) => new Promise((resolve) => setTimeout(resolve, ms));

// Pages API
export const pagesApi = {
    getPages: async (params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        const url = `/api/v1/admin/pages${queryString ? `?${queryString}` : ""}`;

        const response = await apiClient.get(url);
        return response;
    },

    getPage: async (pageId) => {
        const response = await apiClient.get(`/api/v1/admin/pages/${pageId}`);
        return response;
    },

    createPage: async (pageData) => {
        const response = await apiClient.post("/api/v1/admin/pages", pageData);
        return response;
    },

    updatePage: async (pageId, pageData) => {
        const response = await apiClient.put(
            `/api/v1/admin/pages/${pageId}`,
            pageData,
        );
        return response;
    },

    deletePage: async (pageId) => {
        const response = await apiClient.delete(
            `/api/v1/admin/pages/${pageId}`,
        );
        return response;
    },
};

// Comments API
export const commentsApi = {
    async getComments({
        cursor = null,
        direction = "next",
        search = "",
        limit = 15,
    } = {}) {
        return await apiClient.get("/api/v1/admin/comments", {
            cursor: cursor,
            limit: limit,
            q: search,
        });
    },

    async deleteComment(id) {
        return await apiClient.post(`/api/v1/admin/comments/${id}/delete`);
    },
};

export const hashtagsApi = {
    async getHashtags({
        cursor = null,
        direction = "next",
        search = "",
        limit = 15,
        sort = null,
    } = {}) {
        return await apiClient.get("/api/v1/admin/hashtags", {
            cursor: cursor,
            limit: limit,
            q: search,
            sort: sort,
        });
    },
};

// Reports API
export const reportsApi = {
    async getReports({
        cursor = null,
        direction = "next",
        search = "",
        limit = 15,
        sort = null,
    } = {}) {
        return await apiClient.get("/api/v1/admin/reports", {
            cursor: cursor,
            limit: limit,
            q: search,
            sort: sort,
        });
    },

    async getReport(id) {
        return await apiClient.get("/api/v1/admin/reports/" + id);
    },

    async updateReportNotes(id, data) {
        return await apiClient.post(
            `/api/v1/admin/reports/${id}/update-admin-notes`,
            data,
        );
    },

    async updateReportMarkAsNsfw(id) {
        return await apiClient.post(`/api/v1/admin/reports/${id}/mark-nsfw`);
    },

    async deleteComment(id) {
        return await apiClient.post(
            `/api/v1/admin/reports/${id}/comment-delete`,
        );
    },

    async deleteCommentReply(id) {
        return await apiClient.post(
            `/api/v1/admin/reports/${id}/comment-reply-delete`,
        );
    },

    async deleteVideo(id) {
        return await apiClient.post(`/api/v1/admin/reports/${id}/video-delete`);
    },

    async dismissAllReportsByAccount(id) {
        return await apiClient.post(
            `/api/v1/admin/reports/${id}/dismiss-all-by-account`,
        );
    },

    async dismissReport(id) {
        return await apiClient.post(`/api/v1/admin/reports/${id}/dismiss`);
    },
};

// Profiles API
export const profilesApi = {
    async getProfiles({
        cursor = null,
        direction = "next",
        search = "",
        limit = 15,
        sort = null,
    } = {}) {
        return await apiClient.get("/api/v1/admin/profiles", {
            cursor: cursor,
            limit: limit,
            q: search,
            sort: sort,
        });
    },

    async getProfile(id) {
        return await apiClient.get("/api/v1/admin/profiles/" + id);
    },

    async updateProfile(id, data) {
        await delay(200);
        return { success: true, data: { id, ...data } };
    },

    async updateProfilePermissions(id, data) {
        return await apiClient.post(
            `/api/v1/admin/profiles/${id}/permissions`,
            data,
        );
    },

    async updateProfileNotes(id, data) {
        return await apiClient.post(
            `/api/v1/admin/profiles/${id}/admin_note`,
            data,
        );
    },

    async deleteProfile(id) {
        await delay(200);
        return { success: true };
    },
};

// Videos API
export const videosApi = {
    async getVideos({
        cursor = null,
        direction = "next",
        search = "",
        limit = 15,
        sort = null,
    } = {}) {
        return await apiClient.get("/api/v1/admin/videos", {
            cursor: cursor,
            limit: limit,
            q: search,
            sort: sort,
        });
    },

    async getVideo(id) {
        return await apiClient.get(`/api/v1/admin/video/${id}`);
    },

    async getVideoComments(
        id,
        { cursor = null, direction = "next", search = "", limit = 15 },
    ) {
        return await apiClient.get(`/api/v1/admin/videos/${id}/comments`, {
            cursor: cursor,
            limit: limit,
        });
    },

    async deleteVideoComment(id) {
        return await apiClient.post(`/api/v1/admin/comments/${id}/delete`);
    },

    async moderateVideo(id, data) {
        return await apiClient.post(
            `/api/v1/admin/videos/${id}/moderate`,
            data,
        );
    },

    async deleteVideo(id) {
        await delay(200);
        return { success: true };
    },
};

export const settingsApi = {
    async getSettings() {
        return await apiClient.get("/api/v1/admin/settings");
    },

    async updateSettings(data) {
        const res = await apiClient.put("/api/v1/admin/settings", data);
        return res.data;
    },

    async updateLogo(data) {
        return await apiClient.post("/api/v1/admin/settings/update-logo", data);
    },

    async deleteLogo() {
        return await apiClient.post("/api/v1/admin/settings/delete-logo");
    },
};

// API utility
export const apiClient = {
    async get(endpoint, params = {}) {
        const axiosInstance = axios.getAxiosInstance();

        const response = await axiosInstance.get(endpoint, {
            params: params,
        });

        if (!response) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.data;
    },

    async post(endpoint, data = {}) {
        const axiosInstance = axios.getAxiosInstance();

        const response = await axiosInstance.post(endpoint, data);

        if (!response) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.data;
    },

    async put(endpoint, data = {}) {
        const axiosInstance = axios.getAxiosInstance();

        const response = await axiosInstance.put(endpoint, data);

        if (!response) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response;
    },

    async delete(endpoint) {
        const axiosInstance = axios.getAxiosInstance();
        const response = await axiosInstance.delete(endpoint);
        if (!response) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response;
    },
};
