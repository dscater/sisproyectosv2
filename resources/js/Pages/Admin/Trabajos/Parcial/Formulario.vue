<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeMount, defineEmits } from "vue";
import { useTrabajos } from "@/composables/trabajos/useTrabajos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { fHelpers } from "@/Functions/fHelpers";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const { oTrabajo, limpiarTrabajo } = useTrabajos();
const enviando = ref(false);
const listProyectos = ref([]);
const listClientes = ref([]);
const listMonedas = ref([]);
const listEstadoTrabajo = ref(["EN PROCESO", "ENVIADO", "CONCLUIDO"]);
const listEstadoPagos = ref(["PENDIENTE", "COMPLETO"]);

let form = useForm(oTrabajo.value);

const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Enviando...`;
    }
    if (form.id == 0) {
        return `<i class="fa fa-save"></i> Guardar`;
    }
    return `<i class="fa fa-edit"></i> Actualizar`;
});

const enviarFormulario = () => {
    enviando.value = true;
    let url =
        form["_method"] == "POST"
            ? route("trabajos.store")
            : route("trabajos.update", form.id);

    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            enviando.value = false;
            const flash = usePage().props.flash;
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.message ? flash.message : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            limpiarTrabajo();
        },
        onError: (err) => {
            enviando.value = false;
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
    });
};

const obtenerFechaEntrega = () => {
    form.fecha_entrega = "";
    if (form.fecha_inicio != "") {
        if (
            form.dias_plazo &&
            ("" + form.dias_plazo).trim() != "" &&
            parseInt(form.dias_plazo) > 0
        ) {
            form.fecha_entrega = fHelpers().sumarDiasFecha(
                form.fecha_inicio,
                form.dias_plazo
            );
        } else {
            form.fecha_entrega = form.fecha_inicio;
        }
    }
};

const getProyectos = async () => {
    const resp = await useCrudAxios().axiosGet(route("proyectos.listado"), {
        order: "desc",
    });
    listProyectos.value = resp.proyectos;
};

const getClientes = async () => {
    const resp = await useCrudAxios().axiosGet(route("clientes.listado"));
    listClientes.value = resp.clientes;
};

const getMonedas = async () => {
    const resp = await useCrudAxios().axiosGet(route("monedas.listado"));
    listMonedas.value = resp.monedas;
};

const cargarListas = async () => {
    await getProyectos();
    await getClientes();
    await getMonedas();
    appStore.stopLoading();
};

onMounted(() => {
    cargarListas();
});
</script>

<template>
    <form>
        <div class="row">
            <div class="col-md-4 mt-1">
                <label>Seleccionar Proyecto*</label>
                <el-select
                    class="w-100"
                    size="large"
                    :class="{
                        'is-invalid': form.errors?.proyecto_id,
                    }"
                    required
                    placeholder="Seleccionar Proyecto"
                    v-model="form.proyecto_id"
                    filterable
                    clearable
                >
                    <el-option
                        v-for="item in listProyectos"
                        :value="item.id"
                        :label="item.nombre"
                    >
                    </el-option>
                </el-select>
                <span
                    v-if="form.errors?.proyecto_id"
                    class="error invalid-feedback"
                    >{{ form.errors.proyecto_id }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Seleccionar Cliente*</label>
                <el-select
                    class="w-100"
                    size="large"
                    :class="{
                        'is-invalid': form.errors?.proyecto_id,
                    }"
                    required
                    placeholder="Seleccionar Cliente"
                    v-model="form.cliente_id"
                    filterable
                    clearable
                >
                    <el-option
                        v-for="item in listClientes"
                        :value="item.id"
                        :label="item.nombre"
                    >
                    </el-option>
                </el-select>
                <span
                    v-if="form.errors?.cliente_id"
                    class="error invalid-feedback"
                    >{{ form.errors.cliente_id }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Costo*</label>
                <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.costo_original,
                    }"
                    required
                    v-model="form.costo_original"
                />
                <span
                    v-if="form.errors?.costo_original"
                    class="error invalid-feedback"
                    >{{ form.errors.costo_original }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Moneda*</label>
                <select
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.moneda_seleccionada_id,
                    }"
                    required
                    v-model="form.moneda_seleccionada_id"
                >
                    <option value="">- Seleccione -</option>
                    <option v-for="item in listMonedas" :value="item.id">
                        {{ item.nombre }}
                    </option>
                </select>
                <span
                    v-if="form.errors?.moneda_seleccionada_id"
                    class="error invalid-feedback"
                    >{{ form.errors.moneda_seleccionada_id }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Tipo de cambio*</label>
                <select
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.tipo_cambio_id,
                    }"
                    required
                    v-model="form.tipo_cambio_id"
                >
                    <option value="0">Ninguno</option>
                    <option v-for="item in listMonedas" :value="item.id">
                        {{ item.nombre }}
                    </option>
                </select>
                <span
                    v-if="form.errors?.tipo_cambio_id"
                    class="error invalid-feedback"
                    >{{ form.errors.tipo_cambio_id }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Estado del pago*</label>
                <select
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.estado_pago,
                    }"
                    required
                    v-model="form.estado_pago"
                >
                    <option value="">- Seleccione -</option>
                    <option
                        v-for="item in listEstadoPagos"
                        :key="item"
                        :value="item"
                        v-text="item"
                    ></option>
                </select>
                <span
                    v-if="form.errors?.estado_pago"
                    class="error invalid-feedback"
                    >{{ form.errors.estado_pago }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Fecha de recepción*</label>
                <input
                    type="date"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.fecha_inicio,
                    }"
                    required
                    v-model="form.fecha_inicio"
                    @keyup="obtenerFechaEntrega"
                    @change="obtenerFechaEntrega"
                />
                <span
                    v-if="form.errors?.fecha_inicio"
                    class="error invalid-feedback"
                    >{{ form.errors.fecha_inicio }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Días de plazo para entregar*</label>
                <input
                    type="number"
                    step="1"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.dias_plazo,
                    }"
                    required
                    v-model="form.dias_plazo"
                    @keyup="obtenerFechaEntrega"
                    @change="obtenerFechaEntrega"
                    min="0"
                />
                <span
                    v-if="form.errors?.dias_plazo"
                    class="error invalid-feedback"
                    >{{ form.errors.dias_plazo }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Fecha de entrega (automatico)*</label>
                <input
                    type="date"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.fecha_entrega,
                    }"
                    required
                    readonly
                    v-model="form.fecha_entrega"
                />
                <span
                    v-if="form.errors?.fecha_entrega"
                    class="error invalid-feedback"
                    >{{ form.errors.fecha_entrega }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Estado del trabajo*</label>
                <select
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.estado_trabajo,
                    }"
                    required
                    v-model="form.estado_trabajo"
                >
                    <option value="">- Seleccione -</option>
                    <option
                        v-for="item in listEstadoTrabajo"
                        :key="item"
                        :value="item"
                        v-text="item"
                    ></option>
                </select>
                <span
                    v-if="form.errors?.estado_trabajo"
                    class="error invalid-feedback"
                    >{{ form.errors.estado_trabajo }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Fecha de envío</label>
                <input
                    type="date"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.fecha_envio,
                    }"
                    required
                    v-model="form.fecha_envio"
                />
                <span
                    v-if="form.errors?.fecha_envio"
                    class="error invalid-feedback"
                    >{{ form.errors.fecha_envio }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Fecha de conclusión</label>
                <input
                    type="date"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.fecha_conclusion,
                    }"
                    required
                    v-model="form.fecha_conclusion"
                />
                <span
                    v-if="form.errors?.fecha_conclusion"
                    class="error invalid-feedback"
                    >{{ form.errors.fecha_conclusion }}</span
                >
            </div>
            <div class="col-12 mt-1">
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
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <Link
                    :href="route('trabajos.index')"
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
    </form>
</template>
<style scoped></style>
