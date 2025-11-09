import { defineStore } from "pinia";
import axios from "~/plugins/axios";

export const useAdminStore = defineStore("admin", {
    state: () => ({
        reportsCount: 0,
        isLoading: false,
        error: null,
    }),

    getters: {
        displayReportsCount(state) {
            if (state.reportsCount > 99) return "99+";
            return state.reportsCount;
        },
    },

    actions: {
        async fetchReportsCount() {
            try {
                this.isLoading = true;
                this.error = null;

                const axiosInstance = axios.getAxiosInstance();
                const response = await axiosInstance(
                    "/api/v1/admin/reports-count",
                );

                const rawCount = Number(response.data.data.count ?? 0);

                this.reportsCount =
                    Number.isFinite(rawCount) && rawCount > 0
                        ? Math.floor(rawCount)
                        : 0;
            } catch (error) {
                console.error("Error fetching reports count:", error);
                this.error = error;
                this.reportsCount = 0;
            } finally {
                this.isLoading = false;
            }
        },

        setReportsCount(count) {
            const n = Number(count);
            this.reportsCount =
                Number.isFinite(n) && n >= 0 ? Math.floor(n) : 0;
        },

        /**
         * Decrement when a report is dismissed/closed.
         * (Optionally guard against going below zero)
         */
        decrementReportsCount() {
            if (this.reportsCount > 0) {
                this.reportsCount -= 1;
            }
        },

        /**
         * Increment if you ever need to add new pending reports
         */
        incrementReportsCount() {
            this.reportsCount += 1;
        },
    },
});
