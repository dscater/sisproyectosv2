import { ref } from "vue";
import axios from "axios";

export default function useTrabajos() {
    const errors = ref([]);
    const listTrabajos = ref([]);
    const cancelado_aux = ref(0);
    const total_pagos = ref([]);

    const saldo_aux = ref(0);
    const oTrabajo = ref({
        id: "",
        moneda: {
            nombre: "",
        },
        cliente: {
            nombre: "",
        },
        costo: "0.00",
        cancelado: "0.00",
        saldo: "0.00",
    });
    const monedas = ref([]);

    const getTrabajos = async () => {
        await axios.get("/trabajos/get/listaTrabajos").then((response) => {
            listTrabajos.value = response.data;
        });
    };

    const getMontoCambio = (tipo_cambio, moneda_id, monto) => {
        let moneda1_id = tipo_cambio.moneda1_id;
        let valor1 = tipo_cambio.valor1;
        let valor2 = tipo_cambio.valor2;
        // verificar la moneda seleccionada con las monedas de cambio
        if (moneda1_id == moneda_id) {
            // es igual a la moneda 1
            if (valor1 > valor2) {
                return (parseFloat(monto) / parseFloat(valor1)).toFixed(2);
            } else {
                return (parseFloat(monto) * parseFloat(valor2)).toFixed(2);
            }
        } else {
            if (valor1 > valor2) {
                return (parseFloat(monto) * parseFloat(valor1)).toFixed(2);
            } else {
                return (parseFloat(monto) / parseFloat(valor2)).toFixed(2);
            }
        }
    };

    const getTrabajo = async (id, filtra, pago) => {
        monedas.value = [];
        if (id != "") {
            await axios
                .get(
                    "/trabajos/get/getTrabajo/" +
                        id +
                        "?filtra=" +
                        filtra +
                        "&pago=" +
                        pago
                )
                .then((response) => {
                    oTrabajo.value = response.data.trabajo;
                    if (oTrabajo.value.tipo_cambio_id != 0) {
                        console.log("asdasdasdasd");
                        monedas.value.push({
                            id: oTrabajo.value.tipo_cambio.moneda1_id,
                            nombre: oTrabajo.value.tipo_cambio.moneda_1.nombre,
                        });
                        monedas.value.push({
                            id: oTrabajo.value.tipo_cambio.moneda2_id,
                            nombre: oTrabajo.value.tipo_cambio.moneda_2.nombre,
                        });
                    } else {
                        console.log("BBBBB");
                        monedas.value.push({
                            id: oTrabajo.value.moneda_id,
                            nombre: oTrabajo.value.moneda.nombre,
                        });
                    }
                    cancelado_aux.value = response.data.total_pagos;
                    total_pagos.value = response.data.total_pagos;
                    saldo_aux.value = oTrabajo.value.saldo;
                });
        } else {
            oTrabajo.value = {
                id: "",
                moneda: {
                    nombre: "",
                },
                cliente: {
                    nombre: "",
                },
                costo: "0.00",
                cancelado: "0.00",
                saldo: "0.00",
            };

            cancelado_aux.value = 0;
            saldo_aux.value = 0;
        }
    };
    return {
        errors,
        listTrabajos,
        cancelado_aux,
        saldo_aux,
        oTrabajo,
        total_pagos,
        getTrabajo,
        monedas,
        getMontoCambio,
    };
}
