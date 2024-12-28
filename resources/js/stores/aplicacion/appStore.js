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
        async stopLoading() {
            await this.esperarCargaElementos();
            setTimeout(() => {
                this.loading = false;
            }, this.delayLoading);
        },
        setDelayLoading(value) {
            this.delayLoading = value;
        },
        async esperarCargaElementos() {
            return new Promise((resolve) =>
                window.requestAnimationFrame(resolve)
            );
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
