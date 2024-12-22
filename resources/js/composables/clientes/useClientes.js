import { onMounted, ref } from "vue";

const oCliente = ref({
    id: 0,
    nombre: "",
    _method: "POST",
});

export const useClientes = () => {
    const setCliente = (item = null) => {
        if (item) {
            oCliente.value.id = item.id;
            oCliente.value.nombre = item.nombre;
            oCliente.value._method = "PUT";
            return oCliente;
        }
        return false;
    };

    const limpiarCliente = () => {
        oCliente.value.id = 0;
        oCliente.value.nombre = "";
        oCliente.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oCliente,
        setCliente,
        limpiarCliente,
    };
};
