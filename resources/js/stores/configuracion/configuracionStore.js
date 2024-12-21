import { defineStore } from "pinia";

export const useConfiguracionStore = defineStore("configuracion", {
    state: () => ({
        oConfiguracion: {
            nombre_sistema: "SISPROYECTOS",
            alias: "SP",
            razon_social: "SISPROYECTOS S.A.",
            url_logo: "",
        },
    }),
    actions: {
        setConfiguracion(value) {
            this.oConfiguracion = value;
        },
        getConfiguracion() {
            return this.oConfiguracion;
        },
    },
});
