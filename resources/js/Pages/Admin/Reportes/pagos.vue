<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, inject, onMounted, ref, watch } from "vue";
import axios from "axios";
const props = defineProps({
    moneda_principal: {
        type: Object,
        default: () => ({}),
    },
    clientes: {
        type: Object,
        default: () => ({}),
    },
    proyectos: {
        type: Object,
        default: () => ({}),
    },
    trabajos: {
        type: Object,
        default: () => ({}),
    },
});
const Swal = inject("$swal");

const form = useForm({
    filtro: "todos",
    cliente_id: "todos",
    trabajo: "todos",
    proyecto: "todos",
    filtro_fecha: "todos",
    fecha_ini: obtenerFechaActual(),
    fecha_fin: obtenerFechaActual(),
});

const submit = () => {
    const url = route("reportes.pagos_pdf", form.data());
    window.open(url, "_blank");
};

const listPagos = ref([]);
let time_out_filtros = null;
const cargando = ref(true);
const cargaDatos = () => {
    axios
        .get(route("reportes.pagos_pdf"), { params: form.data() })
        .then((response) => {
            listPagos.value = response.data;
            cargando.value = false;
        });
};
const cambioValoresFiltros = () => {
    cargando.value = true;
    clearInterval(time_out_filtros);
    time_out_filtros = setTimeout(() => {
        cargaDatos();
    }, 400);
};

onMounted(() => {
    cargaDatos();
});

const total_pagos = computed(() => {
    return listPagos.value.reduce(
        (suma, objeto) =>
            (parseFloat(suma) + parseFloat(objeto.monto)).toFixed(2),
        0
    );
});

function obtenerFechaActual() {
    const fecha = new Date();
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // Sumamos 1 al mes ya que los meses en JavaScript van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, "0");
    return `${año}-${mes}-${dia}`;
}
</script>
<template>
    <Head title="Reportes-Pagos" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Reportes</h2>
        </template>
        <div class="py-5">
            <div class="mx-auto w-full grid sm:grid-cols-1 px-5 gap-2">
                <div
                    class="block w-full overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <h4
                                class="w-full text-center font-bold text-lg uppercase"
                            >
                                Pagos
                            </h4>
                        </div>
                        <div
                            class="relative overflow-x-auto shadow-md sm:rounded-lg"
                        >
                            <form @submit.prevent="submit" target="_blank">
                                <div class="w-2/4 block p-2 m-auto">
                                    <label
                                        for="Title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                        >Filtro*</label
                                    >
                                    <select
                                        v-model="form.filtro"
                                        name="filtro"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        placeholder=""
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option value="proyecto">
                                            Por proyecto
                                        </option>
                                        <option value="trabajo">
                                            Por trabajo
                                        </option>
                                        <option value="estado_pago">
                                            Estado de trabajo
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.bs"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.bs }}
                                    </div>
                                </div>
                                <div
                                    class="w-2/4 block p-2 m-auto"
                                    v-if="form.filtro == 'proyecto'"
                                >
                                    <label
                                        for="Title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                        >Seleccione el proyecto*</label
                                    >
                                    <select
                                        v-model="form.proyecto"
                                        name="proyecto"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        placeholder=""
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option
                                            v-for="item in proyectos"
                                            :value="item.id"
                                        >
                                            {{ item.nombre }} ({{ item.alias }})
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.proyecto"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.proyecto }}
                                    </div>
                                </div>
                                <div
                                    class="w-2/4 block p-2 m-auto"
                                    v-if="form.filtro == 'trabajo'"
                                >
                                    <label
                                        for="Title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                        >Seleccione el trabajo*</label
                                    >
                                    <select
                                        v-model="form.trabajo"
                                        name="trabajo"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        placeholder=""
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option
                                            v-for="item in trabajos"
                                            :value="item.id"
                                        >
                                            ({{ item.proyecto.alias }})
                                            {{ item.descripcion }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.trabajo"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.trabajo }}
                                    </div>
                                </div>
                                <div
                                    class="w-2/4 block p-2 m-auto"
                                    v-if="form.filtro == 'estado_trabajo'"
                                >
                                    <label
                                        for="Title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                        >Estado de pago*</label
                                    >
                                    <select
                                        v-model="form.estado_trabajo"
                                        name="estado_trabajo"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        placeholder=""
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option value="EN PROCESO">
                                            EN PROCESO
                                        </option>
                                        <option value="ENVIADO">ENVIADO</option>
                                        <option value="CONCLUIDO">
                                            CONCLUIDO
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.estado_trabajo"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.estado_trabajo }}
                                    </div>
                                </div>
                                <div class="w-2/4 block p-2 m-auto">
                                    <label
                                        for="Title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                        >Cliente*</label
                                    >
                                    <select
                                        v-model="form.cliente_id"
                                        name="cliente_id"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        placeholder=""
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option
                                            v-for="item in clientes"
                                            :value="item.id"
                                        >
                                            {{ item.nombre }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.cliente_id"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.cliente_id }}
                                    </div>
                                </div>
                                <div class="w-2/4 block p-2 m-auto">
                                    <label
                                        for="Title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                        >Fechas*</label
                                    >
                                    <select
                                        v-model="form.filtro_fecha"
                                        name="filtro_fecha"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        placeholder=""
                                        @change="cambioValoresFiltros"
                                        @keyup="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option value="fechas">
                                            Filtrar fechas
                                        </option>
                                    </select>
                                </div>
                                <div
                                    class="w-full sm:flex sm:center"
                                    v-if="form.filtro_fecha != 'todos'"
                                >
                                    <div class="w-1/4 inline-block p-2 ml-auto">
                                        <label
                                            for="Title"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                            >Fecha inicial*</label
                                        >
                                        <input
                                            v-model="form.fecha_ini"
                                            type="date"
                                            name="fecha_ini"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            placeholder=""
                                            @change="cambioValoresFiltros"
                                            @keyup="cambioValoresFiltros"
                                        />
                                    </div>
                                    <div class="w-1/4 inline-block p-2 mr-auto">
                                        <label
                                            for="Title"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                            >Fecha final*</label
                                        >
                                        <input
                                            v-model="form.fecha_fin"
                                            type="date"
                                            name="fecha_fin"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            placeholder=""
                                            @change="cambioValoresFiltros"
                                            @keyup="cambioValoresFiltros"
                                        />
                                    </div>
                                </div>
                                <div class="w-full text-center p-2">
                                    <button
                                        type="submit"
                                        class="w-2/4 text-white bg-blue-700 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5"
                                        :disabled="form.processing"
                                        :class="{
                                            'opacity-25': form.processing,
                                        }"
                                    >
                                        Generar reporte
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div
                    class="block w-full overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <h4 class="font-bold text-lg uppercase">
                                Resultado
                            </h4>
                        </div>
                        <div class="h-screen flex flex-col">
                            <div
                                class="overflow-y-auto"
                                style="height: calc(70vh - 2rem)"
                            >
                                <table
                                    class="w-full max-w-full text-sm text-left text-gray-500 dark:text-gray-400"
                                >
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0"
                                    >
                                        <tr>
                                            <th
                                                scope="col"
                                                class="px-6 py-3"
                                                width="20px"
                                            >
                                                #
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3"
                                                width="80px"
                                            >
                                                Fecha
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Proyecto
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Trabajo
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Cliente
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Foto Comprobante
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Archivo Comprobante
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Descripción Archivos
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-center w-60"
                                            >
                                                Monto
                                                {{ moneda_principal.nombre }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="flex-grow overflow-y-auto"
                                        style="max-height: 30vh"
                                        v-if="!cargando"
                                    >
                                        <tr
                                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                            v-for="(item, index) in listPagos"
                                        >
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ index + 1 }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.fecha_pago }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.trabajo.proyecto.nombre
                                                }}<br />
                                                {{
                                                    item.trabajo.proyecto.alias
                                                }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.trabajo.descripcion }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.cliente.nombre }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{
                                                    item.foto_comprobante
                                                        ? "SI"
                                                        : "NO"
                                                }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{
                                                    item.archivo_comprobante
                                                        ? "SI"
                                                        : "NO"
                                                }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.descripcion_archivo }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                            >
                                                {{ item.monto }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody
                                        class="flex-grow overflow-y-auto"
                                        style="max-height: 30vh"
                                        v-else
                                    >
                                        <tr>
                                            <td
                                                colspan="9"
                                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 text-center text-xl"
                                            >
                                                CARGANDO...
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-200 dark:text-gray-400 sticky bottom-0"
                                    >
                                        <tr
                                            class="bg-white border-b dark:bg-gray-700 dark:border-solid border-white"
                                        >
                                            <td
                                                colspan="8"
                                                class="px-6 py-4 font-large text-gray-900 dark:text-white whitespace-wrap text-right text-xl"
                                            >
                                                TOTAL
                                            </td>
                                            <td
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-xl"
                                            >
                                                {{ moneda_principal.nombre }}
                                                {{ total_pagos }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
