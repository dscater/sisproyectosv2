import { onMounted, ref } from "vue";

const oMoneda = ref({
    id: 0,
    nombre: "",
    descripcion: "",
    principal: "",
    _method: "POST",
});

export const useMonedas = () => {
    const setMoneda = (item = null) => {
        if (item) {
            oMoneda.value.id = item.id;
            oMoneda.value.nombre = item.nombre;
            oMoneda.value.descripcion = item.descripcion;
            oMoneda.value.principal = item.principal;
            oMoneda.value._method = "PUT";
            return oMoneda;
        }
        return false;
    };

    const limpiarMoneda = () => {
        oMoneda.value.id = 0;
        oMoneda.value.nombre = "";
        oMoneda.value.descripcion = "";
        oMoneda.value.principal = "";
        oMoneda.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oMoneda,
        setMoneda,
        limpiarMoneda,
    };
};
