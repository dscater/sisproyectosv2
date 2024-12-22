import { onMounted, ref } from "vue";
import { fHelpers } from "@/Functions/fHelpers";

const oTrabajo = ref({
    id: 0,
    proyecto_id: "",
    cliente_id: "",
    costo_original: 0,
    moneda_seleccionada_id: 1,
    costo: "",
    moneda_id: "",
    tipo_cambio_id: 0,
    cancelado: 0,
    saldo: 0,
    cancelado_cambio: "",
    saldo_cambio: "",
    costo_cambio: "",
    moneda_cambio_id: "",
    estado_pago: "PENDIENTE",
    descripcion: "",
    fecha_inicio: fHelpers().getFechaActual(),
    dias_plazo: "",
    fecha_entrega: fHelpers().getFechaActual(),
    estado_trabajo: "EN PROCESO",
    fecha_envio: "",
    fecha_conclusion: "",
    _method: "POST",
});

export const useTrabajos = () => {
    const setTrabajo = (item = null) => {
        if (item) {
            oTrabajo.value.id = item.id;
            oTrabajo.value.proyecto_id = item.proyecto_id;
            oTrabajo.value.cliente_id = item.cliente_id;
            oTrabajo.value.costo_original = item.costo_original;
            oTrabajo.value.moneda_seleccionada_id = item.moneda_seleccionada_id;
            oTrabajo.value.costo = item.costo;
            oTrabajo.value.moneda_id = item.moneda_id;
            oTrabajo.value.tipo_cambio_id = item.tipo_cambio_id;
            oTrabajo.value.cancelado = item.cancelado;
            oTrabajo.value.saldo = item.saldo;
            oTrabajo.value.cancelado_cambio = item.cancelado_cambio;
            oTrabajo.value.saldo_cambio = item.saldo_cambio;
            oTrabajo.value.costo_cambio = item.costo_cambio;
            oTrabajo.value.moneda_cambio_id = item.moneda_cambio_id;
            oTrabajo.value.estado_pago = item.estado_pago;
            oTrabajo.value.descripcion = item.descripcion;
            oTrabajo.value.fecha_inicio = item.fecha_inicio;
            oTrabajo.value.dias_plazo = item.dias_plazo;
            oTrabajo.value.fecha_entrega = item.fecha_entrega;
            oTrabajo.value.estado_trabajo = item.estado_trabajo;
            oTrabajo.value.fecha_envio = item.fecha_envio;
            oTrabajo.value.fecha_conclusion = item.fecha_conclusion;
            oTrabajo.value._method = "PUT";
            return oTrabajo;
        }
        return false;
    };

    const limpiarTrabajo = () => {
        oTrabajo.value.id = 0;
        oTrabajo.value.proyecto_id = "";
        oTrabajo.value.cliente_id = "";
        oTrabajo.value.costo_original = 0;
        oTrabajo.value.moneda_seleccionada_id = 1;
        oTrabajo.value.costo = 0;
        oTrabajo.value.moneda_id = "";
        oTrabajo.value.tipo_cambio_id = 0;
        oTrabajo.value.cancelado = 0;
        oTrabajo.value.saldo = 0;
        oTrabajo.value.cancelado_cambio = "";
        oTrabajo.value.saldo_cambio = "";
        oTrabajo.value.costo_cambio = "";
        oTrabajo.value.moneda_cambio_id = "";
        oTrabajo.value.estado_pago = "PENDIENTE";
        oTrabajo.value.descripcion = "";
        oTrabajo.value.fecha_inicio = fHelpers().getFechaActual();
        oTrabajo.value.dias_plazo = "";
        oTrabajo.value.fecha_entrega = fHelpers().getFechaActual();
        oTrabajo.value.estado_trabajo = "EN PROCESO";
        oTrabajo.value.fecha_envio = "";
        oTrabajo.value.fecha_conclusion = "";
        oTrabajo.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oTrabajo,
        setTrabajo,
        limpiarTrabajo,
    };
};
