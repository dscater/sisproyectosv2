import { onMounted, ref } from "vue";

const oProyecto = ref({
    id: 0,
    nombre: "",
    alias: "",
    descripcion: "",
    _method: "POST",
});

export const useProyectos = () => {
    const setProyecto = (item = null) => {
        if (item) {
            oProyecto.value.id = item.id;
            oProyecto.value.nombre = item.nombre;
            oProyecto.value.alias = item.alias;
            oProyecto.value.descripcion = item.descripcion;
            oProyecto.value._method = "PUT";
            return oProyecto;
        }
        return false;
    };

    const limpiarProyecto = () => {
        oProyecto.value.id = 0;
        oProyecto.value.nombre = "";
        oProyecto.value.alias = "";
        oProyecto.value.descripcion = "";
        oProyecto.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oProyecto,
        setProyecto,
        limpiarProyecto,
    };
};
