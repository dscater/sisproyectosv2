<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, computed, ref, watch } from "vue";
import FormTipoCambio from "./FormTipoCambio.vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
const props = defineProps({
    monedas: {
        type: Array,
        default: () => [],
    },
    list_monedas: {
        type: Array,
        default: () => [],
    },
    tipo_cambios: {
        type: Array,
        default: () => [],
    },
    principal: {
        type: Object,
        default: () => {},
    },
});
const Swal = inject("$swal");
const list_tipo_cambios = ref(props.tipo_cambios);
// ****************************
// FORMULARIO TIPO CAMBIO
// ****************************
const muestra_form_tipo_cambio = ref(false);
function agregarTipoCambio() {
    limpiarTipoCambio();
    muestra_form_tipo_cambio.value = true;
}
const form_tipo_cambio = useForm({
    id: 0,
    moneda1_id: props.principal.id,
    valor1: 1,
    moneda2_id: "",
    valor2: 1,
    menor_valor: 0,
    defecto: 0,
});
function editarTipoCambio(tipo_cambio) {
    form_tipo_cambio.id = tipo_cambio.id;
    form_tipo_cambio.moneda1_id = tipo_cambio.moneda1_id;
    form_tipo_cambio.valor1 = tipo_cambio.valor1;
    form_tipo_cambio.moneda2_id = tipo_cambio.moneda2_id;
    form_tipo_cambio.valor2 = tipo_cambio.valor2;
    form_tipo_cambio.menor_valor = tipo_cambio.menor_valor;
    form_tipo_cambio.defecto = tipo_cambio.defecto;
    muestra_form_tipo_cambio.value = true;
}
function destroy_tipo_cambio(id) {
    Swal.fire({
        title: "¿Estás seguro de eliminar este registro?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: `No cancelar`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            axios
                .post(route("tipo_cambios.destroy", id), {
                    _method: "delete",
                })
                .then((response) => {
                    Swal.fire({
                        title: "Correcto",
                        icon: "success",
                        text: response.data.message,
                        confirmButtonText: "Cerrar",
                        confirmButtonColor: "#C40F51",
                    });
                    actualizaListaTipoCambios();
                })
                .catch((error) => {
                    console.log(error.message);
                    if (error.response) {
                        if (error.response.data.message) {
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
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}

function limpiarTipoCambio() {
    form_tipo_cambio.reset();
}
const detectaCierre = (val) => {
    muestra_form_tipo_cambio.value = val;
};
const actualizaListaTipoCambios = () => {
    axios.get(route("tipo_cambios.index")).then((response) => {
        list_tipo_cambios.value = response.data;
    });
};

// onMounted(actualizaListaTipoCambios);
// ****************************
// FIN TIPO DE CAMBIO
// ****************************
const page = usePage();

const showFlash = ref(false);

const flash = computed(function () {
    return page.props.flash.error;
});
watch(
    page.props,
    function (val) {
        if (val.flash.error) {
            showFlash.value = true;
            Swal.fire({
                title: "Error",
                icon: "error",
                text: val.flash.error,
                confirmButtonText: "Cerrar",
                confirmButtonColor: "#C40F51",
            });
        }
        if (val.flash.msj) {
            showFlash.value = true;
            Swal.fire({
                title: "Correcto",
                icon: "success",
                text: val.flash.msj,
                confirmButtonText: "Cerrar",
                confirmButtonColor: "#C40F51",
            });
        }
    },
    {
        immediate: true,
        deep: true,
    }
);
</script>
<template>
    <Head title="Tipos de cambio" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Monedas</h2>
        </template>
        <div class="py-12 p-2">
            <div class="mx-auto w-full grid md:grid-cols-2 gap-2 grid-cols-1">
                <div
                    class="w-full bg-white shadow-sm sm:rounded-lg overflow-auto"
                >
                    <div class="p-6 pb-0 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <h4
                                class="font-bold text-lg uppercase flex gap-2 items-center"
                            >
                                Tipos de cambio
                                <BreezeButton @click="agregarTipoCambio"
                                    >Agregar</BreezeButton
                                >
                            </h4>
                        </div>
                    </div>
                    <table
                        class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3" width="20px">
                                    N°
                                </th>
                                <th scope="col" class="px-6 py-3">Principal</th>
                                <th scope="col" class="px-6 py-3">Moneda 2</th>
                                <th scope="col" class="px-6 py-3 w-40">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tipo_cambio in list_tipo_cambios"
                                :key="tipo_cambio.id"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                            >
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    {{ tipo_cambio.id }}
                                </td>
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    {{ tipo_cambio.moneda_1.nombre
                                    }}{{ tipo_cambio.valor1 }}
                                </td>
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    {{ tipo_cambio.moneda_2.nombre }}
                                    {{ tipo_cambio.valor2 }}
                                </td>
                                <td>
                                    <BreezeButton
                                        @click="editarTipoCambio(tipo_cambio)"
                                        class="px-4 py-2 mt-1 text-white bg-yellow-500 rounded-lg inline-block hover:bg-yellow-600 m-1"
                                        title="Editar"
                                    >
                                        <font-awesome-icon icon="edit" />
                                    </BreezeButton>
                                    <BreezeButton
                                        class="px-4 py-2 mt-1 text-white bg-red-700 rounded-lg inline-block hover:bg-red-600 m-1"
                                        title="Editar"
                                        @click="
                                            destroy_tipo_cambio(tipo_cambio.id)
                                        "
                                    >
                                        <font-awesome-icon icon="trash" />
                                    </BreezeButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="w-full overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 pb-0 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <h4
                                class="font-bold text-lg uppercase flex gap-2 items-center"
                            >
                                Monedas
                                <BreezeButton>Agregar</BreezeButton>
                            </h4>
                        </div>
                    </div>
                    <table
                        class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3" width="20px">
                                    N°
                                </th>
                                <th scope="col" class="px-6 py-3">Nombre</th>
                                <th scope="col" class="px-6 py-3">
                                    Descripción
                                </th>
                                <th scope="col" class="px-6 py-3">Principal</th>
                                <th scope="col" class="px-6 py-3 w-1">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="moneda in monedas"
                                :key="moneda.id"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                            >
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    {{ moneda.id }}
                                </td>
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    {{ moneda.nombre }}
                                </td>
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    {{ moneda.descripcion }}
                                </td>
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    <span
                                        v-if="moneda.principal == 1"
                                        class="bg-green-600 p-2 rounded font-bold"
                                        >PRINCIPAL</span
                                    ><span
                                        v-else
                                        class="bg-gray-600 p-2 rounded"
                                        >SECUNDARIO</span
                                    >
                                </td>
                                <td
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                >
                                    <BreezeButton
                                        class="px-4 py-2 mt-1 text-white bg-yellow-500 rounded-lg inline-block hover:bg-yellow-600 m-1"
                                        title="Editar"
                                    >
                                        <font-awesome-icon icon="edit" />
                                    </BreezeButton>
                                    <BreezeButton
                                        v-if="moneda.principal != 1"
                                        class="px-4 py-2 mt-1 text-white bg-red-700 rounded-lg inline-block hover:bg-red-600 m-1"
                                        title="Eliminar"
                                    >
                                        <font-awesome-icon icon="trash" />
                                    </BreezeButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
    <FormTipoCambio
        @cerrar-formulario="detectaCierre"
        @formulario-enviado="actualizaListaTipoCambios"
        :abrierto="muestra_form_tipo_cambio"
        :form="form_tipo_cambio"
        :principal="principal"
        :list_monedas="list_monedas"
    ></FormTipoCambio>
</template>
