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

let form = useForm(oPago.value);

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
            ? route("pagos.store")
            : route("pagos.update", form.id);

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
            limpiarPago();
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

const getTrabajos = async () => {
    const resp = await useCrudAxios().axiosGet(route("proyectos.listado"), {
        order: "desc",
    });
    listTrabajos.value = resp.proyectos;
};

const getMonedaPrincipal = async () => {
    const resp = await useCrudAxios().axiosGet(
        route("monedas.getMonedaPrincipal")
    );
    monedaPrincipal.value = resp;
};

const cargarListas = async () => {
    await getTrabajos();
    await getMonedaPrincipal();
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
                >
                    <el-option
                        v-for="item in listTrabajos"
                        :value="item.id"
                        :label="item.nombre"
                    >
                    </el-option>
                </el-select>
                <span
                    v-if="form.errors?.trabajo_id"
                    class="error invalid-feedback"
                    >{{ form.errors.trabajo_id }}</span
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
                <label>Fecha de recepción*</label>
                <input
                    type="date"
                    class="form-control"
                    :class="{
                        'is-invalid': form.errors?.fecha_inicio,
                    }"
                    required
                    v-model="form.fecha_inicio"
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
    </form>
</template>
<style scoped></style>
