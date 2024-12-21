<script setup>
import Modal from "@/Components/Modal.vue";
import { inject, onMounted, computed, ref, watch, defineEmits } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    abrierto: {
        type: Boolean,
        default: false,
    },
    form: {
        type: Object,
        default: () =>
            useForm({
                id: 0,
                moneda1_id: 0,
                valor1: 1,
                moneda2_id: "",
                valor2: 1,
                menor_valor: 0,
                defecto: 0,
            }),
    },
    monedas: {
        type: Array,
        default: () => [],
    },
    list_monedas: {
        type: Array,
        default: () => ref([]),
    },
    principal: {
        type: Object,
        default: () => ({
            nombre: "",
            descripcion: "",
        }),
    },
});

const titulo_modal = computed(() => {
    if (props.form.id == 0) {
        return `NUEVO TIPO DE CAMBIO`;
    }
    return "EDITAR TIPO DE CAMBIO";
});

const errors = ref([]);

// TIPO DE CAMBIO
const modal_tipo_cambio = ref(false);

watch(
    () => props.abrierto,
    (newVal) => {
        modal_tipo_cambio.value = newVal;
    }
);
const Swal = inject("$swal");

function enviaFormularioTipoCambio() {
    let url = route("tipo_cambios.store");
    let datos = props.form.data();
    if (props.form.id != 0) {
        datos["_method"] = "put";
        url = route("tipo_cambios.update", datos.id);
    }
    axios
        .post(url, datos)
        .then((response) => {
            if (response.data.sw) {
                Swal.fire({
                    title: "Correcto",
                    icon: "success",
                    text: response.data.message,
                    confirmButtonText: "Cerrar",
                    confirmButtonColor: "#C40F51",
                });
                emit("formulario-enviado", modal_tipo_cambio.value);
                cierraFormulario();
            }
        })
        .catch((error) => {
            console.log(error);
            if (error.response) {
                if (error.response.data) {
                    if(error.response.data.errors){
                        errors.value = error.response.data.errors;
                    }
                    Swal.fire({
                        title: "Error",
                        icon: "error",
                        text: error.response.data.message,
                        confirmButtonText: "Cerrar",
                        confirmButtonColor: "#C40F51",
                    });
                }
            }
        });
}

function cancelaFormularioTipoCambio() {
    cierraFormulario();
}
const emit = defineEmits(["cerrar-formulario", "formulario-enviado"]);
const cierraFormulario = () => {
    modal_tipo_cambio.value = false;
    emit("cerrar-formulario", modal_tipo_cambio.value);
};

const txtBotonTipoCambio = computed(() => {
    if (props.form.id == 0) {
        return `Registrar`;
    }
    return "Guardar cambios";
});

function limpiarTipoCambio() {
    form.reset();
}
</script>
<template>
    <Modal :mostrar="modal_tipo_cambio">
        <template v-slot:header>
            <font-awesome-icon v-if="form.id != 0" icon="edit" class="mr-2" />
            <!-- Muestra el icono "edit" si form.id es 0 -->
            <font-awesome-icon v-else icon="plus" class="mr-2" />
            <!-- Muestra el icono "plus" en otros casos -->
            {{ titulo_modal }}
        </template>
        <template v-slot:body>
            <form @submit.prevent="submit" id="formulario_tipo_cambio">
                <div class="w-full md:w-2/4 inline-block p-2">
                    <label
                        for="Title"
                        class="block mb-2 text-sm font-medium text-black dark:text-gray-300"
                        >Moneda Principal*</label
                    >
                    <input
                        type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        :value="
                            principal.nombre + ' | ' + principal.descripcion
                        "
                        readonly
                    />
                    <div v-if="errors.moneda1_id" class="text-sm text-red-600">
                        {{ errors.moneda1_id[0] }}
                    </div>
                </div>
                <div class="w-full md:w-2/4 inline-block p-2">
                    <label
                        for="Title"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                        >Valor Moneda Principal*</label
                    >
                    <input
                        type="number"
                        step="0.01"
                        v-model="form.valor1"
                        name="valor1"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder=""
                    />
                    <div v-if="errors.valor1" class="text-sm text-red-600">
                        {{ errors.valor1[0] }}
                    </div>
                </div>
                <div class="w-full md:w-2/4 inline-block p-2">
                    <label
                        for="Title"
                        class="block mb-2 text-sm font-medium text-black dark:text-gray-300"
                        >Seleccionar Moneda 2*</label
                    >
                    <select
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        name="moneda2_id"
                        v-model="form.moneda2_id"
                    >
                        <option
                            v-for="item in list_monedas"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ `${item.nombre} | ${item.descripcion}` }}
                        </option>
                    </select>
                    <div v-if="errors.moneda2_id" class="text-sm text-red-600">
                        {{ errors.moneda2_id[0] }}
                    </div>
                </div>
                <div class="w-full md:w-2/4 inline-block p-2">
                    <label
                        for="Title"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                        >Valor Moneda 2*</label
                    >
                    <input
                        type="number"
                        step="0.01"
                        v-model="form.valor2"
                        name="valor2"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder=""
                    />
                    <div v-if="errors.valor2" class="text-sm text-red-600">
                        {{ errors.valor2[0] }}
                    </div>
                </div>
            </form>
        </template>
        <template v-slot:foot>
            <div class="w-full p-2 flex justify-between">
                <button
                    type="submit"
                    class="text-BLACK bg-white focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5"
                    @click="cancelaFormularioTipoCambio"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="text-white bg-blue-700 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5"
                    :disabled="form.processing"
                    :class="{
                        'opacity-25': form.processing,
                    }"
                    @click="enviaFormularioTipoCambio"
                >
                    {{ txtBotonTipoCambio }}
                </button>
            </div>
        </template>
    </Modal>
</template>
