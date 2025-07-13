import axios from "axios";

let axiosInstance = null;

const createAxiosInstance = () => {
    axiosInstance = axios.create({
        baseURL: import.meta.env.VITE_API_URL,
        headers: {
            common: {
                "X-Requested-With": "XMLHttpRequest",
            },
        },
        withCredentials: true,
    });
    return axiosInstance;
};

export default {
    install: (app) => {
        const instance = createAxiosInstance();
        app.config.globalProperties.$axios = instance;
        app.provide("axios", instance);
    },
    getAxiosInstance: () => {
        if (!axiosInstance) {
            return createAxiosInstance();
        }
        return axiosInstance;
    },
};
