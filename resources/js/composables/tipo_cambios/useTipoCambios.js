import { onMounted, ref } from "vue";

const oTipoCambio = ref({
    id: 0,
    moneda1_id: "",
    valor1: 1,
    moneda2_id: "",
    valor2: 1,
    menor_valor: "",
    defecto: "",
    _method: "POST",
});

export const useTipoCambios = () => {
    const setTipoCambio = (item = null) => {
        if (item) {
            oTipoCambio.value.id = item.id;
            oTipoCambio.value.moneda1_id = item.moneda1_id;
            oTipoCambio.value.valor1 = item.valor1;
            oTipoCambio.value.moneda2_id = item.moneda2_id;
            oTipoCambio.value.valor2 = item.valor2;
            oTipoCambio.value.menor_valor = item.menor_valor;
            oTipoCambio.value.defecto = item.defecto;
            oTipoCambio.value._method = "PUT";
            return oTipoCambio;
        }
        return false;
    };

    const limpiarTipoCambio = () => {
        oTipoCambio.value.id = 0;
        oTipoCambio.value.moneda1_id = "";
        oTipoCambio.value.valor1 = 1;
        oTipoCambio.value.moneda2_id = "";
        oTipoCambio.value.valor2 = 1;
        oTipoCambio.value.menor_valor = "";
        oTipoCambio.value.defecto = "";
        oTipoCambio.value._method = "POST";
    };

    onMounted(() => {});

    return {
        oTipoCambio,
        setTipoCambio,
        limpiarTipoCambio,
    };
};
