<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeMount, defineEmits } from "vue";
import { usePagos } from "@/composables/pagos/usePagos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { fHelpers } from "@/Functions/fHelpers";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const { oPago, limpiarPago } = usePagos();
const enviando = ref(false);
const listTrabajos = ref([]);
const monedaPrincipal = ref(null);
const oTipoCambio = ref(null);
const oTrabajo = ref(null);
const file_foto = ref(null);
const file_archivo = ref(null);

let form = useForm(oPago.value);

function cargaArchivo(e, key) {
    form[key] = null;
    form[key] = e.target.files[0];
}

const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Enviando...`;
    }
    if (form.id == 0) {
        return `<i class="fa fa-save"></i> Guardar`;
    }
    return `<i class="fa fa-edit"></i> Actualizar`;
});

const montoSaldo = ref(0);
const montoSaldoCambio = ref(0);
const montoCancelado = ref(0);
const montoCanceladoCambio = ref(0);
const listMonedas = ref([]);

const monedaSeleccionadaText = computed(() => {
    if (oTrabajo.value && form.moneda_seleccionada_id != "") {
        const oMoneda = listMonedas.value.filter(
            (elem) => elem.moneda_id === form.moneda_seleccionada_id
        )[0];
        if (oMoneda && oMoneda.moneda) {
            return oMoneda.moneda.nombre;
        }
    }
    return "";
});

const calularSaldosYCancelado = () => {
    if (monedaPrincipal.value) {
        if (monedaPrincipal.value.id == form.moneda_seleccionada_id) {
            calculaMonto();
        } else {
            calculaMontoCambio();
        }
    } else {
        Swal.fire({
            icon: "info",
            title: "Error",
            text: `Error, no se cargo la moneda principal`,
            confirmButtonColor: "#3085d6",
            confirmButtonText: `Aceptar`,
        });
    }
};

const calculaMonto = () => {
    let monto = getMontosSaldo();
    montoSaldo.value = isNaN(monto) ? 0 : parseFloat(monto);
    montoCancelado.value =
        parseFloat(oTrabajo.value.costo) - parseFloat(montoSaldo.value);
    montoCancelado.value = parseFloat(montoCancelado.value);

    if (oTipoCambio.value) {
        montoSaldoCambio.value = fHelpers().getMontoCambio(
            oTrabajo.value.moneda_id,
            montoSaldo.value,
            oTipoCambio.value
        );
        montoCanceladoCambio.value =
            oTrabajo.value.costo_cambio - montoSaldoCambio.value;
        montoCanceladoCambio.value = parseFloat(montoCanceladoCambio.value);
        form.monto_cambio = fHelpers().getMontoCambio(
            oTrabajo.value.moneda_id,
            form.monto,
            oTipoCambio.value
        );
    }
};

const calculaMontoCambio = () => {
    let monto = getMontosSaldo(2);
    montoSaldoCambio.value = isNaN(monto) ? 0 : parseFloat(monto).toFixed(2);
    montoCanceladoCambio.value =
        oTrabajo.value.costo_cambio - parseFloat(montoSaldoCambio.value);

    montoCanceladoCambio.value = parseFloat(montoCanceladoCambio.value).toFixed(
        2
    );
    if (oTipoCambio.value) {
        montoSaldo.value = fHelpers().getMontoCambio(
            oTrabajo.value.moneda_cambio_id,
            montoSaldoCambio.value,
            oTipoCambio.value
        );
        montoCancelado.value = oTrabajo.value.costo - montoSaldo.value;
        montoCancelado.value = parseFloat(montoCancelado.value).toFixed(2);
        form.monto = fHelpers().getMontoCambio(
            oTrabajo.value.moneda_cambio_id,
            form.monto_cambio,
            oTipoCambio.value
        );
    }
};

const getMontosSaldo = (sw = 1) => {
    let array_keys = ["monto_original", "saldo", "moneda", "cancelado"];
    if (sw == 2) {
        array_keys = [
            "monto_original",
            "saldo_cambio",
            "moneda_cambio",
            "cancelado_cambio",
        ];
    }
    const key_monto = array_keys[0];
    const key_saldo = array_keys[1];
    const key_moneda = array_keys[2];
    const key_cancelado = array_keys[2];

    delete form.errors[key_monto];
    let monto = 0;
    if (oTrabajo.value) {
        monto = oTrabajo.value[key_saldo];
        let form_monto = form[key_monto] ? parseFloat(form[key_monto]) : 0;
        if (oTrabajo.value) {
            monto = parseFloat(oTrabajo.value[key_saldo]);
            if (fHelpers().contarDecimales(monto) > 2) {
                monto = fHelpers().redondear(monto, 2);
            }
            if (form_monto <= monto) {
                if (form_monto && form_monto > 0) {
                    monto = parseFloat(monto) - parseFloat(form_monto);
                }
            } else {
                form.errors[
                    key_monto
                ] = `El monto no puede ser mayor a ${oTrabajo.value[key_moneda].nombre} ${monto}`;
            }
        }
    }
    monto = isNaN(monto) ? 0 : monto;
    return parseFloat(parseFloat(monto).toFixed(2)) ?? 0;
};

// enviar los datos para guardar en la bd
const enviarFormulario = () => {
    enviando.value = true;
    let url =
        form["_method"] == "POST"
            ? route("pagos.store")
            : route("pagos.update", form.id);

    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (res) => {
            const flash = usePage().props.flash;
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.message ? flash.message : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            limpiarPago();
        },
        onError: (err) => {
            console.log(err);
            const flash = usePage().props.flash;
            Swal.fire({
                icon: "info",
                title: "Error",
                text: `${
                    flash.error
                        ? flash.error
                        : err.error
                        ? err.error
                        : "Hay errores en el formulario"
                }`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
        },
        onFinish: () => {
            enviando.value = false;
            if (file_foto.value) {
                file_foto.value.value = null;
            }
            if (file_archivo.value) {
                file_archivo.value.value = null;
            }
            form.file_foto = null;
            form.file_archivo = null;
        },
    });
};

// obtener el tipo de cambio del trabajo si este lo tuviera
const getTipoCambio = async () => {
    oTipoCambio.value = null;
    if (oTrabajo.value && oTrabajo.value.tipo_cambio_id != 0) {
        const resp = await useCrudAxios().axiosGet(
            route("tipo_cambios.getInfo", oTrabajo.value.tipo_cambio_id)
        );
        oTipoCambio.value = resp;

        // crear la lista de monedas
        listMonedas.value.push({
            moneda_id: oTipoCambio.value.moneda1_id,
            valor: oTipoCambio.value.valor1,
            moneda: oTipoCambio.value.moneda_1,
        });
        listMonedas.value.push({
            moneda_id: oTipoCambio.value.moneda2_id,
            valor: oTipoCambio.value.valor2,
            moneda: oTipoCambio.value.moneda_2,
        });
    }
};

// obtener informacion del trabajo seleccionado
const getTrabajo = async (value) => {
    listMonedas.value = [];
    oTrabajo.value = null;
    if (value) {
        const resp = await useCrudAxios().axiosGet(
            route("trabajos.show", value)
        );

        oTrabajo.value = resp.trabajo;
        if (oTrabajo.value.tipo_cambio_id != 0) {
            await getTipoCambio();
        } else {
            listMonedas.value.push({
                moneda_id: oTrabajo.value.moneda_id,
                valor: 0,
                moneda: oTrabajo.value.moneda,
            });
        }

        if (oPago.value.id == 0) {
            form.moneda_seleccionada_id = oTrabajo.value.moneda_id;
        }

        if (
            oPago.value.id != 0 &&
            oPago.value.trabajo_id == oTrabajo.value.id
        ) {
            // para EDITAR restaurar los montos con los del pago
            // trabajo
            oTrabajo.value.cancelado =
                parseFloat(oTrabajo.value.cancelado) -
                parseFloat(oPago.value.monto);
            oTrabajo.value.saldo =
                parseFloat(oTrabajo.value.saldo) +
                parseFloat(oPago.value.monto);

            oTrabajo.value.cancelado_cambio =
                parseFloat(oTrabajo.value.cancelado_cambio) -
                parseFloat(oPago.value.monto_cambio);
            oTrabajo.value.saldo_cambio =
                parseFloat(oTrabajo.value.saldo_cambio) +
                parseFloat(oPago.value.monto_cambio);
            // pago
            montoCancelado.value = oTrabajo.value.cancelado;
            montoSaldo.value = oTrabajo.value.saldo;
            montoCanceladoCambio.value = oTrabajo.value.cancelado_cambio;
            montoSaldoCambio.value = oTrabajo.value.saldo_cambio;
        } else {
            montoCancelado.value = oTrabajo.value.cancelado;
            montoCanceladoCambio.value = oTrabajo.value.cancelado_cambio;
            montoSaldo.value = oTrabajo.value.saldo;
            montoSaldoCambio.value = oTrabajo.value.saldo_cambio;
        }

        if (oPago.value.id != 0) {
            setTimeout(() => {
                calularSaldosYCancelado();
            }, 300);
        }
    }
};

// obtener la lista de trabajos
const getTrabajos = async () => {
    const resp = await useCrudAxios().axiosGet(route("trabajos.listado"), {
        order: "desc",
        conSaldo: true,
    });
    listTrabajos.value = resp.trabajos;
};

const getMonedaPrincipal = async () => {
    const resp = await useCrudAxios().axiosGet(
        route("monedas.getMonedaPrincipal")
    );
    monedaPrincipal.value = resp;
};

// cargar las funciones que traen listas
const cargarListas = async () => {
    await getTrabajos();
    await getMonedaPrincipal();
    appStore.stopLoading();
};

onMounted(async () => {
    await cargarListas();
    if (oPago.value.id != 0) {
        getTrabajo(oPago.value.trabajo_id);
    }
});
</script>

<template>
    <form>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p>
                            <strong>Código de trabajo: </strong
                            >{{ oTrabajo?.id }}
                        </p>
                        <hr />
                        <div class="bg-saldo">
                            <p
                                class="text-lg"
                                :class="
                                    parseFloat(montoSaldo) <= 0
                                        ? 'text-success font-weight-bold'
                                        : 'text-primary'
                                "
                            >
                                <strong
                                    >Saldo
                                    {{ oTrabajo?.moneda?.nombre }}: </strong
                                >{{ parseFloat(montoSaldo).toFixed(2) }}
                            </p>
                            <p
                                class="text-lg"
                                :class="
                                    parseFloat(montoSaldo) <= 0
                                        ? 'text-success font-weight-bold'
                                        : ''
                                "
                                v-if="
                                    oTrabajo && oTrabajo.moneda_cambio_id != 0
                                "
                            >
                                <strong
                                    >Saldo
                                    {{ oTrabajo?.moneda_cambio?.nombre }}:
                                </strong>
                                {{ parseFloat(montoSaldoCambio).toFixed(2) }}
                            </p>
                        </div>
                        <div class="bg-cancelado">
                            <p
                                class="text-md"
                                :class="
                                    parseFloat(montoCancelado) <= 0
                                        ? 'text-danger'
                                        : ''
                                "
                            >
                                <strong
                                    >Cancelado
                                    {{ oTrabajo?.moneda?.nombre }}: </strong
                                >{{ parseFloat(montoCancelado).toFixed(2) }}
                            </p>
                            <p
                                class="text-md"
                                :class="
                                    parseFloat(montoCancelado) <= 0
                                        ? 'text-danger'
                                        : ''
                                "
                                v-if="
                                    oTrabajo && oTrabajo.moneda_cambio_id != 0
                                "
                            >
                                <strong
                                    >Cancelado
                                    {{
                                        oTrabajo?.moneda_cambio?.nombre
                                    }}: </strong
                                >{{
                                    parseFloat(montoCanceladoCambio).toFixed(2)
                                }}
                            </p>
                        </div>
                        <div class="bg-costo">
                            <p class="text-md">
                                <strong
                                    >Costo
                                    {{ oTrabajo?.moneda?.nombre }}: </strong
                                >{{ oTrabajo?.costo }}
                            </p>
                            <p
                                class="text-md"
                                v-if="
                                    oTrabajo && oTrabajo.moneda_cambio_id != 0
                                "
                            >
                                <strong
                                    >Costo
                                    {{
                                        oTrabajo?.moneda_cambio?.nombre
                                    }}: </strong
                                >{{ oTrabajo?.costo_cambio }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card card-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Seleccionar Trabajo*</label>
                            <el-select
                                class="w-100"
                                size="large"
                                :class="{
                                    'is-invalid': form.errors?.trabajo_id,
                                }"
                                required
                                placeholder="Seleccionar Proyecto"
                                v-model="form.trabajo_id"
                                filterable
                                clearable
                                @change="getTrabajo"
                            >
                                <el-option
                                    v-for="item in listTrabajos"
                                    :value="item.id"
                                    :label="`${item.proyecto.alias} - ${item.proyecto.nombre}`"
                                >
                                </el-option>
                            </el-select>
                            <span
                                v-if="form.errors?.trabajo_id"
                                class="error invalid-feedback"
                                >{{ form.errors.trabajo_id }}</span
                            >
                        </div>
                    </div>
                    <div class="row" v-if="oTrabajo">
                        <div class="col-md-6 form-group">
                            <label>Seleccionar moneda*</label>
                            <select
                                class="form-control"
                                v-model="form.moneda_seleccionada_id"
                                @change="calularSaldosYCancelado"
                            >
                                <option value="">- Seleccione -</option>
                                <option
                                    v-for="item in listMonedas"
                                    :value="item.moneda_id"
                                >
                                    {{ item.moneda.descripcion }} ({{
                                        item.moneda.nombre
                                    }})
                                </option>
                            </select>
                            <span
                                v-if="form.errors?.moneda_seleccionada_id"
                                class="error invalid-feedback"
                                >{{ form.errors.moneda_seleccionada_id }}</span
                            >
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Monto {{ monedaSeleccionadaText }}*</label>
                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors?.monto_original,
                                }"
                                required
                                v-model="form.monto_original"
                                @keyup.prevent="calularSaldosYCancelado"
                                @change="calularSaldosYCancelado"
                            />
                            <span
                                v-if="form.errors?.monto_original"
                                class="error invalid-feedback"
                                >{{ form.errors.monto_original }}</span
                            >
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Fecha de Pago*</label>
                            <input
                                type="date"
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors?.fecha_pago,
                                }"
                                required
                                v-model="form.fecha_pago"
                            />
                            <span
                                v-if="form.errors?.fecha_pago"
                                class="error invalid-feedback"
                                >{{ form.errors.fecha_pago }}</span
                            >
                        </div>
                        <div class="col-12 form-group">
                            <label>Descripción*</label>
                            <textarea
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors?.descripcion,
                                }"
                                required
                                rows="1"
                                v-model="form.descripcion"
                            ></textarea>
                            <span
                                v-if="form.errors?.descripcion"
                                class="error invalid-feedback"
                                >{{ form.errors.descripcion }}</span
                            >
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Imagen Comprobante</label>
                            <input
                                type="file"
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors?.fecha_pago,
                                }"
                                ref="file_foto"
                                @change="
                                    cargaArchivo($event, 'foto_comprobante')
                                "
                            />
                            <span
                                v-if="form.errors?.fecha_pago"
                                class="error invalid-feedback"
                                >{{ form.errors.fecha_pago }}</span
                            >
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Archivo comprobante</label>
                            <input
                                type="file"
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors?.fecha_pago,
                                }"
                                ref="file_archivo"
                                @change="
                                    cargaArchivo($event, 'archivo_comprobante')
                                "
                            />
                            <span
                                v-if="form.errors?.fecha_pago"
                                class="error invalid-feedback"
                                >{{ form.errors.fecha_pago }}</span
                            >
                        </div>
                        <div class="col-12 form-group">
                            <label>Descripción archivos</label>
                            <textarea
                                class="form-control"
                                :class="{
                                    'is-invalid':
                                        form.errors?.descripcion_archivo,
                                }"
                                required
                                rows="1"
                                v-model="form.descripcion_archivo"
                            ></textarea>
                            <span
                                v-if="form.errors?.descripcion_archivo"
                                class="error invalid-feedback"
                                >{{ form.errors.descripcion_archivo }}</span
                            >
                        </div>
                    </div>
                </div>

                <div class="row mt-2 mb-4">
                    <div class="col-12">
                        <Link
                            :href="route('pagos.index')"
                            class="btn btn-outline-secondary mr-1"
                            ><i class="fa fa-arrow-left"></i> Volver</Link
                        >
                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="enviando"
                            @click.prevent="enviarFormulario"
                            v-html="textBtn"
                        ></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
<style scoped>
.bg-costo,
.bg-cancelado,
.bg-saldo {
    margin-bottom: 0px;
    border-bottom: solid 1px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: start;
    align-items: center;
    padding: 10px 8px;
}

.bg-costo p,
.bg-cancelado p,
.bg-saldo p {
    width: 100%;
    text-align: left;
}
.bg-costo {
    background-color: rgb(223, 248, 253);
}
.bg-cancelado {
    background-color: rgb(223, 253, 223);
}
.bg-saldo {
    background-color: rgb(253, 237, 223);
}
</style>
