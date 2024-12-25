import { onMounted, ref } from "vue";
import { fHelpers } from "@/Functions/fHelpers";

const oPago = ref({
    id: 0,
    trabajo_id: "",
    cliente_id: "",
    monto: "",
    moneda_id: "",
    tipo_cambio_id: "",
    monto_cambio: "",
    moneda_cambio_id: "",
    descripcion: "",
    fecha_pago: fHelpers().getFechaActual(),
    foto_comprobante: null,
    archivo_comprobante: null,
    descripcion_archivo: "",
    _method: "POST",
});

export const usePagos = () => {
    const setPago = (item = null) => {
        if (item) {
            oPago.value.id = item.id;
            oPago.value.trabajo_id = item.trabajo_id;
            oPago.value.cliente_id = item.cliente_id;
            oPago.value.monto = item.monto;
            oPago.value.moneda_id = item.moneda_id;
            oPago.value.tipo_cambio_id = item.tipo_cambio_id;
            oPago.value.monto_cambio = item.monto_cambio;
            oPago.value.moneda_cambio_id = item.moneda_cambio_id;
            oPago.value.descripcion = item.descripcion;
            oPago.value.fecha_pago = item.fecha_pago;
            oPago.value.foto_comprobante = item.foto_comprobante;
            oPago.value.archivo_comprobante = item.archivo_comprobante;
            oPago.value.descripcion_archivo = item.descripcion_archivo;
            oPago.value._method = "PUT";
            return oPago;
        }
        return false;
    };

    const limpiarPago = () => {
        oPago.value.id = 0;
        oPago.value.trabajo_id = "";
        oPago.value.cliente_id = "";
        oPago.value.monto = "";
        oPago.value.moneda_id = "";
        oPago.value.tipo_cambio_id = "";
        oPago.value.monto_cambio = "";
        oPago.value.moneda_cambio_id = "";
        oPago.value.descripcion = "";
        oPago.value.fecha_pago = fHelpers().getFechaActual();
        oPago.value.foto_comprobante = null;
        oPago.value.archivo_comprobante = null;
        oPago.value.descripcion_archivo = "";
        oPago.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oPago,
        setPago,
        limpiarPago,
    };
};
