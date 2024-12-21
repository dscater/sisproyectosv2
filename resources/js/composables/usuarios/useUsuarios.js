import { onMounted, ref } from "vue";

const oUsuario = ref({
    id: 0,
    usuario: "",
    nombres: "",
    apellidos: "",
    role_id: "",
    password: "",
    acceso: 0 + "",
    _method: "POST",
});

export const useUsuarios = () => {
    const setUsuario = (item = null, cliente = null) => {
        if (item) {
            oUsuario.value.id = item.id;
            oUsuario.value.usuario = item.usuario;
            oUsuario.value.nombres = item.nombres;
            oUsuario.value.apellidos = item.apellidos;
            oUsuario.value.role_id = item.role_id;
            oUsuario.value.acceso = item.acceso + "";
            oUsuario.value.password = "";
            oUsuario.value._method = "PUT";
            if (cliente) {
                oUsuario.value.cliente = item.cliente;
            }
            return oUsuario;
        }
        return false;
    };

    const limpiarUsuario = () => {
        oUsuario.value.id = 0;
        oUsuario.value.usuario = "";
        oUsuario.value.nombres = "";
        oUsuario.value.apellidos = "";
        oUsuario.value.role_id = "";
        oUsuario.value.acceso = 0 + "";
        oUsuario.value.password = "";
        oUsuario.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oUsuario,
        setUsuario,
        limpiarUsuario,
    };
};
