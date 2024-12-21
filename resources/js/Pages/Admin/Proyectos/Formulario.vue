<script setup>
import MiModal from "@/Components/MiModal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useProyectos } from "@/composables/proyectos/useProyectos";
import { watch, ref, computed, defineEmits } from "vue";
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

const { oProyecto, limpiarProyecto } = useProyectos();
const accion_form = ref(props.accion_formulario);
const estado_form = ref(props.estado_formulario);
const enviando = ref(false);
let form = useForm(oProyecto.value);
watch(
    () => props.estado_formulario,
    (newValue) => {
        estado_form.value = newValue;
        if (estado_form.value) {
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
            form = useForm(oProyecto.value);
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
            ? route("proyectos.store")
            : route("proyectos.update", form.id);

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
            limpiarProyecto();
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

const emits = defineEmits(["cerrar-formulario", "envio-formulario"]);

watch(estado_form, (newVal) => {
    if (!newVal) {
        emits("cerrar-formulario");
    }
});

const cerrarFormulario = () => {
    estado_form.value = false;
};
</script>

<template>
    <MiModal
        :open_modal="estado_form"
        @close="cerrarFormulario"
        :size="'modal-xl'"
        :header-class="'bg-primary'"
        :footer-class="'justify-content-end'"
    >
        <template #header>
            <h4 class="modal-title">Nuevo proyecto</h4>
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
                    <div class="col-md-8">
                        <label>Nombre del proyecto*</label>
                        <input
                            class="form-control"
                            required
                            v-model="form.nombre"
                        />
                    </div>
                    <div class="col-md-4">
                        <label>Alias*</label>
                        <input
                            class="form-control"
                            required
                            v-model="form.alias"
                        />
                    </div>
                    <div class="col-12">
                        <label>Descripción*</label>
                        <textarea
                            class="form-control"
                            required
                            rows="1"
                            v-model="form.descripcion"
                        ></textarea>
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
