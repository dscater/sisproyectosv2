<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, inject, onMounted, ref, watch, onBeforeMount } from "vue";
import axios from "axios";
import MiTable from "@/Components/MiTable.vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
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

const miTable = ref(null);

const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
        fixed: true,
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "FECHA PAGO",
        key: "fecha_pago",
        sortable: true,
        fixed: true,
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "NOMBRE PROYECTO",
        key: "proyecto_nombre",
        keySortable: "proyectos.nombre",
        sortable: true,
        fixed: true,
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "DESCRIPCIÓN",
        key: "trabajo.descripcion",
        keySortable: "trabajos.descripcion",
        sortable: true,
        width: "400px",
    },
    {
        label: "CLIENTE",
        key: "cliente.nombre",
        keySortable: "clientes.nombre",
        sortable: true,
    },
    {
        label: "COMPROBANTE",
        key: "foto_comprobante",
        sortable: true,
    },
    {
        label: "ARCHIVO",
        key: "archivo_comprobante",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN ARCHIVOS",
        key: "descripcion_archivo",
        sortable: true,
    },
    {
        label: "MONTO",
        key: "monto",
        sortable: true,
        type: Number,
        fixed: "right",
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
]);

const form = useForm({
    filtro: "todos",
    estado_trabajo: "todos",
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
const cargaDatos = () => {
    axios
        .get(route("reportes.pagos_pdf"), { params: form.data() })
        .then((response) => {
            listPagos.value = response.data;
        })
        .finally(() => {
            miTable.value.setLoading(true);
        });
};
const cambioValoresFiltros = () => {
    miTable.value.setLoading(true);
    clearInterval(time_out_filtros);
    time_out_filtros = setTimeout(() => {
        cargaDatos();
    }, 400);
};

onMounted(() => {
    cargaDatos();
    appStore.stopLoading();
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
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Reporte Pagos</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Reporte Pagos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <h4
                                            class="w-full text-center font-bold text-lg uppercase"
                                        >
                                            Pagos
                                        </h4>
                                    </div>
                                </div>
                                <form
                                    @submit.prevent="submit"
                                    target="_blank"
                                    class="row"
                                >
                                    <div class="col-md-6">
                                        <label>Seleccione el proyecto*</label>
                                        <select
                                            v-model="form.proyecto"
                                            name="proyecto"
                                            class="form-control"
                                            placeholder=""
                                            @change="cambioValoresFiltros"
                                        >
                                            <option value="todos">Todos</option>
                                            <option
                                                v-for="item in proyectos"
                                                :value="item.id"
                                            >
                                                {{ item.nombre }} ({{
                                                    item.alias
                                                }})
                                            </option>
                                        </select>
                                        <div
                                            v-if="form.errors.proyecto"
                                            class="text-sm text-red-600"
                                        >
                                            {{ form.errors.proyecto }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Seleccione el trabajo*</label>
                                        <select
                                            v-model="form.trabajo"
                                            name="trabajo"
                                            class="form-control"
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
                                    <div class="col-md-6">
                                        <label>Estado de trabajo*</label>
                                        <select
                                            v-model="form.estado_trabajo"
                                            name="estado_trabajo"
                                            class="form-control"
                                            placeholder=""
                                            @change="cambioValoresFiltros"
                                        >
                                            <option value="todos">Todos</option>
                                            <option value="EN PROCESO">
                                                EN PROCESO
                                            </option>
                                            <option value="ENVIADO">
                                                ENVIADO
                                            </option>
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
                                    <div class="col-md-6">
                                        <label>Cliente*</label>
                                        <select
                                            v-model="form.cliente_id"
                                            name="cliente_id"
                                            class="form-control"
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
                                    <div class="col-md-6">
                                        <label>Fechas*</label>
                                        <select
                                            v-model="form.filtro_fecha"
                                            name="filtro_fecha"
                                            class="form-control"
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
                                        class="col-12"
                                        v-if="form.filtro_fecha != 'todos'"
                                    >
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Fecha inicial*</label>
                                                <input
                                                    v-model="form.fecha_ini"
                                                    type="date"
                                                    name="fecha_ini"
                                                    class="form-control"
                                                    @change="
                                                        cambioValoresFiltros
                                                    "
                                                    @keyup="
                                                        cambioValoresFiltros
                                                    "
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Fecha final*</label>
                                                <input
                                                    v-model="form.fecha_fin"
                                                    type="date"
                                                    name="fecha_fin"
                                                    class="form-control"
                                                    @change="
                                                        cambioValoresFiltros
                                                    "
                                                    @keyup="
                                                        cambioValoresFiltros
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button
                                            type="submit"
                                            class="btn btn-primary btn-block"
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
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4 class="w-100 text-center font-weight-bold">
                            RESULTADO
                        </h4>
                        <h6 class="w-100 text-center">
                            Expresado en bolivianos
                        </h6>
                        <p class="w-100 text-center">
                            Resultado: {{ listPagos.length }} registros
                        </p>
                    </div>
                    <div class="col-12">
                        <MiTable
                            :tableClass="'bg-white'"
                            ref="miTable"
                            :class-header="'bg-dark'"
                            :cols="headers"
                            :data="listPagos"
                            :con-paginacion="false"
                            fix-cols
                            fixed-header
                            table-height="50vh"
                        >
                            <template #proyecto_nombre="{ item }">
                                <p
                                    style="
                                        width: 120px;
                                        word-wrap: break-word;
                                        white-space: wrap;
                                    "
                                >
                                    {{ item.trabajo.proyecto.nombre }}<br />
                                    <b>({{ item.trabajo.proyecto.alias }})</b>
                                </p>
                            </template>
                            <template #costo="{ item }">
                                <div class="w-100">
                                    {{ item.moneda.nombre }}
                                    {{ item.costo }}
                                </div>
                            </template>

                            <template #cancelado="{ item }">
                                <div class="w-100">
                                    <div class="w-100 text-center">
                                        {{ item.moneda.nombre }}
                                        {{ item.cancelado }}
                                    </div>
                                </div>
                            </template>

                            <template #descripcion="{ item }">
                                <p
                                    style="
                                        width: 120px;
                                        word-wrap: break-word;
                                        white-space: wrap;
                                    "
                                    v-html="item.descripcion"
                                ></p>
                            </template>

                            <template
                                #tableFooter
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-200 dark:text-gray-400 sticky bottom-0"
                            >
                                <tr
                                    class="bg-white border-b dark:bg-gray-700 dark:border-solid border-white"
                                >
                                    <td
                                        colspan="3"
                                        class="bg-dark footer-fixed p-3 fixed-column-ext text-right"
                                    >
                                        TOTAL
                                    </td>
                                    <td
                                        colspan="5"
                                        class="bg-dark"
                                        style="position: sticky; bottom: 0"
                                    >
                                        <div>&nbsp;</div>
                                    </td>
                                    <td
                                        class="bg-dark footer-fixed p-3 fixed-column-ext-right"
                                    >
                                        {{ moneda_principal.nombre }}
                                        {{ total_pagos }}
                                    </td>
                                </tr>
                            </template>
                        </MiTable>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
