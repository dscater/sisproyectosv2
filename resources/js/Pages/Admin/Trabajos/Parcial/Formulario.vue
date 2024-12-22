<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, defineEmits } from "vue";
import { useTrabajos } from "@/composables/trabajos/useTrabajos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { fHelpers } from "@/Functions/fHelpers";

const { oTrabajo } = useTrabajos();
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
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.bien ? flash.bien : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            limpiarTrabajo();
            emits("envio-formulario");
        },
        onError: (err) => {
            enviando.value = false;
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

const calculaDiaEntrega = () => {
    if (form.fecha_inicio != "" && form.disabled != "") {
        var d = new Date(form.fecha_inicio + "T00:00:00");
        let fecha_entrega = sumarDias(d, parseInt(form.dias_plazo));
        let nuev_fecha = fecha_entrega.getFullYear() + "-";
        let mes = parseInt(fecha_entrega.getMonth()) + 1;
        nuev_fecha += (mes < 10 ? "0" + mes : mes) + "-";
        nuev_fecha +=
            fecha_entrega.getDate() < 10
                ? "0" + fecha_entrega.getDate()
                : fecha_entrega.getDate();
        form.fecha_entrega = nuev_fecha; //asignando la fecha automaticamente
    }
};

const getProyectos = async () => {
    const resp = await useCrudAxios().axiosGet(route("proyectos.listado"),{order:'desc'});
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
                <select
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.proyecto_id,
                    }"
                    required
                    v-model="form.proyecto_id"
                >
                    <option value="">- Seleccione -</option>
                    <option v-for="item in listProyectos" :value="item.id">
                        {{ item.nombre }}
                    </option>
                </select>
                <span
                    v-if="form.errors?.proyecto_id"
                    class="error invalid-feedback"
                    >{{ form.errors.proyecto_id }}</span
                >
            </div>
            <div class="col-md-4 mt-1">
                <label>Seleccionar Cliente*</label>
                <select
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.cliente_id,
                    }"
                    required
                    v-model="form.cliente_id"
                >
                    <option value="">- Seleccione -</option>
                    <option v-for="item in listClientes" :value="item.id">
                        {{ item.nombre }}
                    </option>
                </select>
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
                <label>Alias*</label>
                <input
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.alias,
                    }"
                    required
                    v-model="form.alias"
                />

                <span
                    v-if="form.errors?.alias"
                    class="error invalid-feedback"
                    >{{ form.errors.alias }}</span
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
