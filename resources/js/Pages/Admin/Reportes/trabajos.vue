<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, computed, ref } from "vue";
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
    estado_pago: "todos",
    estado_trabajo: "todos",
    trabajo: "todos",
    proyecto: "todos",
    filtro_fecha: "todos",
    fecha_ini: obtenerFechaActual(),
    fecha_fin: obtenerFechaActual(),
});

const submit = () => {
    const url = route("reportes.trabajos_pdf", form.data());
    window.open(url, "_blank");
};

const listTrabajos = ref([]);
let time_out_filtros = null;
const cargando = ref(true);
const cargaDatos = () => {
    axios
        .get(route("reportes.trabajos_pdf"), { params: form.data() })
        .then((response) => {
            listTrabajos.value = response.data;
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

function obtenerFechaActual() {
    const fecha = new Date();
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // Sumamos 1 al mes ya que los meses en JavaScript van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, "0");
    return `${año}-${mes}-${dia}`;
}

const total_saldos = computed(() => {
    return listTrabajos.value.reduce(
        (suma, objeto) =>
            (parseFloat(suma) + parseFloat(objeto.saldo)).toFixed(2),
        0
    );
});

const total_cancelado = computed(() => {
    return listTrabajos.value.reduce(
        (suma, objeto) =>
            (parseFloat(suma) + parseFloat(objeto.cancelado)).toFixed(2),
        0
    );
});

const total_costo = computed(() => {
    return listTrabajos.value.reduce(
        (suma, objeto) =>
            (parseFloat(suma) + parseFloat(objeto.costo)).toFixed(2),
        0
    );
});

const total_proceso = computed(() => {
    let enproceso = listTrabajos.value.filter(
        (elem) => elem.estado_trabajo == "EN PROCESO"
    );
    return enproceso.length;
});
const total_enviado = computed(() => {
    let enviados = listTrabajos.value.filter(
        (elem) => elem.estado_trabajo == "ENVIADO"
    );
    return enviados.length;
});
const total_concluidos = computed(() => {
    let concluidos = listTrabajos.value.filter(
        (elem) => elem.estado_trabajo == "CONCLUIDO"
    );
    return concluidos.length;
});
const total_pagos_completos = computed(() => {
    let pagoscompletos = listTrabajos.value.filter(
        (elem) => elem.estado_pago == "COMPLETO"
    );
    return pagoscompletos.length;
});
const total_pagos_pendientes = computed(() => {
    let pagoscompletos = listTrabajos.value.filter(
        (elem) => elem.estado_pago == "PENDIENTE"
    );
    return pagoscompletos.length;
});
</script>
<template>
    <Head title="Clientes" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Reportes</h2>
        </template>
        <div class="py-5">
            <div class="mx-auto w-full grid sm:grid-cols-1 px-5 gap-2">
                <div
                    class="block w-full overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="mb-2">
                        <h4
                            class="w-full text-center font-bold text-lg uppercase"
                        >
                            Trabajos
                        </h4>
                    </div>
                    <div
                        class="relative overflow-x-auto shadow-md sm:rounded-lg"
                    >
                        <form @submit.prevent="submit" target="_blank" class="p-3">
                            <div class="w-full md:w-1/4 inline-block p-2">
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
                                    <option value="trabajo">Por trabajo</option>
                                    <option value="estado_pago">
                                        Estado de pago
                                    </option>
                                    <option value="estado_trabajo">
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
                                class="w-full md:w-1/4 inline-block p-2"
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
                                class="w-full md:w-1/4 inline-block p-2"
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
                                class="w-full md:w-1/4 inline-block p-2"
                                v-if="form.filtro == 'estado_pago'"
                            >
                                <label
                                    for="Title"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Estado de pago*</label
                                >
                                <select
                                    v-model="form.estado_pago"
                                    name="estado_pago"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                    @change="cambioValoresFiltros"
                                >
                                    <option value="todos">Todos</option>
                                    <option value="COMPLETO">COMPLETO</option>
                                    <option value="PENDIENTE">PENDIENTE</option>
                                </select>
                                <div
                                    v-if="form.errors.estado_pago"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.estado_pago }}
                                </div>
                            </div>
                            <div
                                class="w-full md:w-1/4 inline-block p-2"
                                v-if="form.filtro == 'estado_trabajo'"
                            >
                                <label
                                    for="Title"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Estado de trabajo*</label
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
                                    <option value="CONCLUIDO">CONCLUIDO</option>
                                </select>
                                <div
                                    v-if="form.errors.estado_trabajo"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.estado_trabajo }}
                                </div>
                            </div>
                            <div class="w-full md:w-1/4 inline-block p-2">
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
                            <div class="w-full md:w-1/4 inline-block p-2">
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
                                <div class="w-full md:w-1/4 inline-block p-2 ml-auto">
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
                                <div class="w-full md:w-1/4 inline-block p-2 mr-auto">
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
                <div
                    class="block w-full overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <h4 class="font-bold text-lg uppercase text-center">
                                Expresado en
                                {{ moneda_principal.descripcion }}
                            </h4>
                            <p class="text-center text-sm">
                                <span class="font-bold">Resultado:</span>
                                {{ listTrabajos.length }} Registros encontrados
                            </p>
                        </div>
                        <div
                            class="h-screen flex"
                            :style="[
                                listTrabajos.length > 5
                                    ? 'height: calc(75vh - 2rem)'
                                    : 'height:auto',
                            ]"
                        >
                            <div
                                class="overflow-y-auto"
                                :style="[
                                    listTrabajos.length > 5
                                        ? 'height: calc(70vh - 2rem)'
                                        : 'height:auto',
                                ]"
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
                                            <th scope="col" class="px-6 py-3">
                                                Proyecto
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Cliente
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Descripción
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Fecha Inicio
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Plazo días
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Fecha Entrega
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Estado/Trabajo
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Fecha de envío
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Fecha de conclusión
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Costo
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Cancelado
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Saldo
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Estado/Pago
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
                                            v-for="(
                                                item, index
                                            ) in listTrabajos"
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
                                                {{ item.proyecto.nombre
                                                }}<br />({{
                                                    item.proyecto.alias
                                                }})
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
                                                {{ item.descripcion }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.fecha_inicio }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.dias_plazo }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.fecha_entrega }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.estado_trabajo }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.fecha_envio }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap"
                                            >
                                                {{ item.fecha_conclusion }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                            >
                                                {{ item.costo }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                            >
                                                {{ item.cancelado }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                            >
                                                {{ item.saldo }}
                                            </td>
                                            <td
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                                :class="[
                                                    item.estado_pago ==
                                                    'COMPLETO'
                                                        ? 'bg-green-600'
                                                        : 'bg-red-700',
                                                ]"
                                            >
                                                {{ item.estado_pago }}
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
                                                colspan="14"
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
                                                colspan="10"
                                                class="px-6 py-4 font-large text-gray-900 dark:text-white whitespace-wrap text-right text-xl"
                                            >
                                                TOTALES
                                            </td>
                                            <td
                                                class="px-6 py-4 font-large text-gray-900 dark:text-white whitespace-wrap text-right text-xl"
                                            >
                                                {{ total_costo }}
                                            </td>
                                            <td
                                                class="px-6 py-4 font-large text-gray-900 dark:text-white whitespace-wrap text-right text-xl"
                                            >
                                                {{ total_cancelado }}
                                            </td>
                                            <td
                                                class="px-6 py-4 font-large text-gray-900 dark:text-white whitespace-wrap text-right text-xl"
                                            >
                                                {{ total_saldos }}
                                            </td>
                                            <td
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-xl"
                                            ></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <table
                            class="w-full max-w-full text-sm text-left text-gray-500 dark:text-gray-400"
                        >
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0"
                            >
                                <tr>
                                    <th
                                        scope="col"
                                        colspan="8"
                                        class="px-6 py-3 text-center font-bold text-lg"
                                    >
                                        TOTALES
                                    </th>
                                </tr>
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        COSTOS
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        CANCELADO
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        SALDO
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        EN PROCESO
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        ENVIADOS
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        CONCLUIDOS
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        PAGOS COMPLETOS
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center font-bold"
                                    >
                                        PAGOS PENDIENTES
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="flex-grow overflow-y-auto"
                                v-if="!cargando"
                            >
                                <tr
                                    class="bg-white border-b dark:bg-gray-600 dark:border-solid border-white"
                                >
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_costo }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_cancelado }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_saldos }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_proceso }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_enviado }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_concluidos }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_pagos_completos }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-wrap text-center"
                                    >
                                        {{ total_pagos_pendientes }}
                                    </td>
                                </tr>
                            </tbody>
                            <tbody class="flex-grow overflow-y-auto" v-else>
                                <tr>
                                    <td
                                        colspan="9"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 text-center text-xl"
                                    >
                                        CARGANDO...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
<style scoped></style>
