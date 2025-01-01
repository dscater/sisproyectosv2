<script setup>
import Content from "@/Components/Content.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    inject,
    onMounted,
    onBeforeMount,
    computed,
    ref,
    nextTick,
    watch,
} from "vue";
import { debounce } from "lodash";

import axios from "axios";
import { useAppStore } from "@/stores/aplicacion/appStore";
import MiTable from "@/Components/MiTable.vue";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});

const miTable = ref(null);

const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
        fixed: true,
        width:"4%",
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "NOMBRE PROYECTO",
        key: "proyecto.nombre",
        keySortable: "proyectos.nombre",
        sortable: true,
        fixed: true,
        width:"200",
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "CLIENTE",
        key: "cliente.nombre",
        keySortable: "clientes.nombre",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN",
        key: "descripcion",
        sortable: true,
    },
    {
        label: "FECHA INICIO",
        key: "fecha_inicio_t",
        sortable: true,
    },
    {
        label: "DÍAS PLAZO",
        key: "dias_plazo",
        sortable: true,
    },
    {
        label: "FECHA ENTREGA",
        key: "fecha_entrega_t",
        sortable: true,
    },
    {
        label: "ESTADO/TRABAJO",
        key: "estado_trabajo",
        sortable: true,
    },
    {
        label: "FECHA ENVÍO",
        key: "fecha_envio_t",
        sortable: true,
    },
    {
        label: "FECHA CONCLUSIÓN",
        key: "fecha_conclucion_t",
        sortable: true,
    },
    {
        label: "COSTO",
        key: "costo",
        sortable: true,
        fixed: "right",
        width:"8%",
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "CANCELADO",
        key: "cancelado",
        sortable: true,
        fixed: "right",
        width:"8%",
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "SALDO",
        key: "saldo",
        sortable: true,
        fixed: "right",
        type: "Number",
        width:"8%",
        classTd: () => {
            let class_fixed = "bg__fixed";
            return class_fixed;
        },
    },
    {
        label: "ESTADO/PAGO",
        key: "estado_pago",
        sortable: true,
        fixed: "right",
        width:"8%",
        classTd: (item) => {
            let class_fixed = "bg-danger";
            if (item.estado_pago == "COMPLETO") {
                class_fixed = " bg-cancelado";
            }
            return class_fixed;
        },
    },
]);

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

watch(
    () => cargando.value,
    (newVal) => {
        miTable.value.setLoading(newVal);
    }
);

const cargaDatos = () => {
    axios
        .get(route("reportes.trabajos_pdf"), { params: form.data() })
        .then((response) => {
            listTrabajos.value = response.data;
        })
        .catch((error) => {})
        .finally(() => {
            cargando.value = false;
        });
};

const cambioValoresFiltros = debounce(() => {
    cargando.value = true;

    cargaDatos();
}, 300);

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

onMounted(async () => {
    cargaDatos();
    appStore.stopLoading();
});
</script>
<template>
    <Head title="Reporte trabajos" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Reporte Trabajos</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Reporte Trabajos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <form
                            @submit.prevent="submit"
                            target="_blank"
                            class="p-3"
                        >
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Seleccione el proyecto*</label>
                                    <select
                                        name="proyecto"
                                        class="form-control"
                                        v-model="form.proyecto"
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
                                <div class="col-md-6">
                                    <label>Seleccione el trabajo*</label>
                                    <select
                                        name="trabajo"
                                        class="form-control"
                                        v-model="form.trabajo"
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
                                    <label>Estado de pago*</label>
                                    <select
                                        name="estado_pago"
                                        class="form-control"
                                        v-model="form.estado_pago"
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option value="COMPLETO">
                                            COMPLETO
                                        </option>
                                        <option value="PENDIENTE">
                                            PENDIENTE
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.estado_pago"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.estado_pago }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Estado de trabajo*</label>
                                    <select
                                        name="estado_trabajo"
                                        class="form-control"
                                        v-model="form.estado_trabajo"
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
                                <div class="col-md-6">
                                    <label>Cliente*</label>
                                    <select
                                        name="cliente_id"
                                        class="form-control"
                                        v-model="form.cliente_id"
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
                                        name="filtro_fecha"
                                        class="form-control"
                                        v-model="form.filtro_fecha"
                                        @change="cambioValoresFiltros"
                                    >
                                        <option value="todos">Todos</option>
                                        <option value="fechas">
                                            Filtrar fechas
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="row">
                                        <div
                                            class="col-12"
                                            v-if="form.filtro_fecha != 'todos'"
                                        >
                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <label
                                                        >Fecha inicial*</label
                                                    >
                                                    <input
                                                        type="date"
                                                        name="fecha_ini"
                                                        class="form-control"
                                                        v-model="form.fecha_ini"
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
                                                        type="date"
                                                        name="fecha_fin"
                                                        class="form-control"
                                                        v-model="form.fecha_fin"
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
                                    </div>
                                </div>

                                <div class="col-12 mt-3">
                                    <button
                                        type="submit"
                                        class="btn btn-primary btn-block"
                                        :disabled="form.processing"
                                    >
                                        Generar reporte
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="mb-2">
                    <h4 class="w-100 text-center">
                        Expresado en
                        {{ moneda_principal.descripcion }}
                    </h4>
                    <p class="text-center text-sm">
                        <span class="font-bold">Resultado:</span>
                        {{ listTrabajos.length }} Registros encontrados
                    </p>
                </div>

                <MiTable
                    :tableClass="'bg-white mitabla'"
                    ref="miTable"
                    :class-header="'bg-dark'"
                    :cols="headers"
                    :data="listTrabajos"
                    :con-paginacion="false"
                    fix-cols
                    fixed-header
                    table-height="50vh"
                >
                    <template #['proyecto.nombre']="{ item }">
                        <p
                            style="
                                width: 120px;
                                word-wrap: break-word;
                                white-space: wrap;
                            "
                        >
                            {{ item.proyecto.nombre }}<br />
                            <b>({{ item.proyecto.alias }})</b>
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

                    <template #tableFooter>
                        <tr class="footer-fixed">
                            <td
                                colspan="2"
                                class="bg-dark footer-fixed p-3 fixed-column-ext text-right"
                            >
                                TOTALES
                            </td>
                            <td
                                colspan="8"
                                class="bg-dark"
                                style="position: sticky; bottom: 0"
                            >
                                <div>&nbsp;</div>
                            </td>
                            <td
                                class="bg-dark footer-fixed p-3 fixed-column-ext-right"
                            >
                                {{ total_costo }}
                            </td>
                            <td
                                class="bg-dark footer-fixed p-3 fixed-column-ext-right"
                            >
                                {{ total_cancelado }}
                            </td>
                            <td
                                class="bg-dark footer-fixed p-3 fixed-column-ext-right"
                            >
                                {{ total_saldos }}
                            </td>
                            <td
                                class="bg-dark footer-fixed p-3 fixed-column-ext-right"
                            ></td>
                        </tr>
                    </template>
                </MiTable>

                <table class="table table-bordered mt-5">
                    <thead class="">
                        <tr>
                            <th
                                colspan="8"
                                class="w-100 text-center h4 font-weight-bold"
                            >
                                RESUMEN
                            </th>
                        </tr>
                        <tr>
                            <th class="">COSTOS</th>
                            <th class="">CANCELADO</th>
                            <th class="">SALDO</th>
                            <th class="">EN PROCESO</th>
                            <th class="">ENVIADOS</th>
                            <th class="">CONCLUIDOS</th>
                            <th class="">PAGOS COMPLETOS</th>
                            <th class="">PAGOS PENDIENTES</th>
                        </tr>
                    </thead>
                    <tbody v-if="!cargando" class="bg-white">
                        <tr>
                            <td class="text-center">
                                {{ total_costo }}
                            </td>
                            <td class="text-center">
                                {{ total_cancelado }}
                            </td>
                            <td class="text-center">
                                {{ total_saldos }}
                            </td>
                            <td class="text-center">
                                {{ total_proceso }}
                            </td>
                            <td class="text-center">
                                {{ total_enviado }}
                            </td>
                            <td class="text-center">
                                {{ total_concluidos }}
                            </td>
                            <td class="text-center">
                                {{ total_pagos_completos }}
                            </td>
                            <td class="text-center">
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
    </Content>
</template>
<style scoped>
table thead tr th,
table tbody tr td {
    border: solid 1px rgb(216, 216, 216);
}
table thead tr {
    position: sticky;
    top: -1px;
    z-index: 1;
    background-color: white;
    box-shadow: 0 3px 3px -3px rgb(216, 216, 216);
}
table tfoot tr {
    position: sticky;
    bottom: -2px;
    z-index: 1;
    background-color: white;
    box-shadow: 3px 0px 0px 0px rgb(216, 216, 216);
}

table thead tr th.fixed-column,
table tbody tr td.fixed-column {
    position: sticky;
    left: 0;
    border-right: solid 1px rgb(216, 216, 216);
}
table tbody tr td.fixed-column {
    background-color: white;
}
</style>
