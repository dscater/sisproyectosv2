import { defineStore } from "pinia";

export const useAppStore = defineStore("app_store", {
    state: () => ({
        loading: true,
        delayLoading: 300,
    }),
    actions: {
        setLoading(value) {
            this.loading = value;
        },
        startLoading() {
            this.loading = true;
        },
        stopLoading() {
            setTimeout(() => {
                this.loading = false;
            }, this.delayLoading);
        },
        setDelayLoading(value) {
            this.delayLoading = value;
        },
    },
    getters: {
        getLoading() {
            return this.loading;
        },
        getDelayLoading(value) {
            this.delayLoading = value;
        },
    },
});
