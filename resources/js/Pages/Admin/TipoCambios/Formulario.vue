<script setup>
import MiModal from "@/Components/MiModal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useTipoCambios } from "@/composables/tipo_cambios/useTipoCambios";
import { watch, ref, computed, defineEmits } from "vue";
import axios from "axios";
const props = defineProps({
    estado_formulario: {
        type: Boolean,
        default: false,
    },
    accion_formulario: {
        type: Number,
        default: 0,
    },
});

const { oTipoCambio, limpiarTipoCambio } = useTipoCambios();
const accion_form = ref(props.accion_formulario);
const estado_form = ref(props.estado_formulario);
const enviando = ref(false);
const oMonedaPrincipal = ref(null);
const listMonedas = ref([]);
let form = useForm(oTipoCambio.value);
watch(
    () => props.estado_formulario,
    async (newValue) => {
        estado_form.value = newValue;
        if (estado_form.value) {
            form.errors = null;
            getMonedas();
            await getMonedaPrincipal();
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
            form = useForm(oTipoCambio.value);
        } else {
            document
                .getElementsByTagName("body")[0]
                .classList.remove("modal-open");
        }
    }
);
watch(
    () => props.accion_formulario,
    (newValue) => {
        accion_form.value = newValue;
    }
);

const { flash } = usePage().props;

const tituloDialog = computed(() => {
    return accion_form.value == 0 ? `Agregar registro` : `Editar registro`;
});
const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Enviando...`;
    }
    if (accion_form.value == 0) {
        return `<i class="fa fa-save"></i> Guardar`;
    }
    return `<i class="fa fa-edit"></i> Actualizar`;
});

const enviarFormulario = () => {
    enviando.value = true;
    let url =
        form["_method"] == "POST"
            ? route("tipo_cambios.store")
            : route("tipo_cambios.update", form.id);

    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.bien ? flash.bien : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            limpiarTipoCambio();
            emits("envio-formulario");
        },
        onError: (err) => {
            console.log(err);
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
        },
    });
};

const emits = defineEmits(["cerrar-formulario", "envio-formulario"]);

watch(estado_form, (newVal) => {
    if (!newVal) {
        emits("cerrar-formulario");
    }
});

const cerrarFormulario = () => {
    estado_form.value = false;
};

const getMonedaPrincipal = async () => {
    const resp = await axios.get(route("monedas.getMonedaPrincipal"));
    if (resp) {
        oMonedaPrincipal.value = resp.data;
        oTipoCambio.value.moneda1_id = oMonedaPrincipal.value.id;
    }
};

const getMonedas = () => {
    axios
        .get(route("monedas.listado"), {
            params: {
                sin_principal: true,
            },
        })
        .then((response) => {
            listMonedas.value = response.data.monedas;
        });
};
</script>

<template>
    <MiModal
        :open_modal="estado_form"
        @close="cerrarFormulario"
        :size="'modal-xl'"
        :header-class="'bg-dark'"
        :footer-class="'justify-content-end'"
    >
        <template #header>
            <h4 class="modal-title">Nuevo tipo de cambio</h4>
            <button
                type="button"
                class="close"
                @click.prevent="cerrarFormulario()"
            >
                <span aria-hidden="true">×</span>
            </button>
        </template>
        <template #body>
            <form>
                <div class="row">
                    <div class="col-12" v-if="form.errors && form.errors.enuso">
                        <div class="alert alert-danger">
                            {{ form.errors.enuso }}
                        </div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Moneda principal*</label>
                        <input
                            class="form-control"
                            :class="{
                                'is-invalid': form.errors?.moneda1_id,
                            }"
                            :value="`${oMonedaPrincipal?.nombre} | ${oMonedaPrincipal?.descripcion}`"
                            readonly
                        />
                        <span
                            v-if="form.errors?.moneda1_id"
                            class="error invalid-feedback"
                            >{{ form.errors.moneda1_id }}</span
                        >
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Valor Moneda principal*</label>
                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            :class="{
                                'is-invalid': form.errors?.valor1,
                            }"
                            v-model="form.valor1"
                        />
                        <span
                            v-if="form.errors?.valor1"
                            class="error invalid-feedback"
                            >{{ form.errors.valor1 }}</span
                        >
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Seleccionar moneda 2*</label>
                        <select
                            class="form-control"
                            :class="{
                                'is-invalid': form.errors?.moneda2_id,
                            }"
                            v-model="form.moneda2_id"
                        >
                            <option value="">- Seleccione -</option>
                            <option
                                v-for="item in listMonedas"
                                :value="item.id"
                            >
                                {{ item.nombre }}
                            </option>
                        </select>
                        <span
                            v-if="form.errors?.moneda2_id"
                            class="error invalid-feedback"
                            >{{ form.errors.moneda2_id }}</span
                        >
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Valor Moneda 2*</label>
                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            :class="{
                                'is-invalid': form.errors?.valor2,
                            }"
                            v-model="form.valor2"
                        />
                        <span
                            v-if="form.errors?.valor2"
                            class="error invalid-feedback"
                            >{{ form.errors.valor2 }}</span
                        >
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <button
                type="button"
                class="btn btn-default"
                @click.prevent="cerrarFormulario()"
            >
                Cerrar
            </button>
            <button
                type="button"
                class="btn btn-primary"
                :disabled="enviando"
                @click.prevent="enviarFormulario"
                v-html="textBtn"
            ></button>
        </template>
    </MiModal>
</template>
<style scoped></style>
