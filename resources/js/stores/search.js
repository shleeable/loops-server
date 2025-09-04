import { defineStore } from "pinia";
import axios from "~/plugins/axios";

export const useSearchStore = defineStore("search", {
    state: () => ({
        recentSearches: [],
        searchResults: [],
        isLoading: false,
        error: null,
    }),

    actions: {
        async searchQuery(query) {
            if (!query.trim()) {
                this.searchResults = [];
                return;
            }

            try {
                this.isLoading = true;
                const axiosInstance = axios.getAxiosInstance();
                const response = await axiosInstance.post(
                    `/api/v1/search/users?q=${encodeURIComponent(query)}`,
                );
                this.searchResults = response.data.data;
                this.addToRecentSearches(query.trim());
            } catch (error) {
                console.error("Search error:", error);
                this.error = "Failed to perform search";
                this.searchResults = [];
            } finally {
                this.isLoading = false;
            }
        },

        addToRecentSearches(query) {
            this.recentSearches = this.recentSearches.filter(
                (q) => q !== query,
            );
            this.recentSearches.unshift(query);
            this.recentSearches = this.recentSearches.slice(0, 5);
            this.saveToLocalStorage();
        },

        loadRecentSearches() {
            const saved = localStorage.getItem("recentSearches");
            if (saved) {
                try {
                    this.recentSearches = JSON.parse(saved);
                } catch (e) {
                    console.error("Failed to parse recent searches:", e);
                    this.recentSearches = [];
                }
            }
        },

        saveToLocalStorage() {
            localStorage.setItem(
                "recentSearches",
                JSON.stringify(this.recentSearches),
            );
        },

        clearSearchResults() {
            this.searchResults = [];
        },

        clearRecentSearches() {
            this.recentSearches = [];
            localStorage.removeItem("recentSearches");
        },
    },
});
