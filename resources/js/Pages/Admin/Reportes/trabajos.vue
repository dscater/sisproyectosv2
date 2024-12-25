<script setup>
import Content from "@/Components/Content.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { inject, onMounted, onBeforeMount, computed, ref, nextTick } from "vue";
import axios from "axios";
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

const renderizaFixedColumns = () => {
    nextTick(() => {
        const listRows = document.querySelectorAll("table tr");
        listRows.forEach((row, index_row) => {
            let ancho_anterior = 0; // Desplazamiento acumulado
            const listTds = row.querySelectorAll("td");
            listTds.forEach((td) => {
                const ancho_elem = td.offsetWidth; // Obtener ancho del elemento actual

                if (td.classList.contains("fixed-column")) {
                    td.classList.add("xd"); // Agregar clase
                    td.style.position = "sticky"; // Hacerlo sticky
                    td.style.left = `${ancho_anterior}px`; // Posicionarlo con left
                }

                ancho_anterior += ancho_elem; // Sumar ancho actual al acumulado
            });

            ancho_anterior = 0; // Desplazamiento acumulado
            const listThs = row.querySelectorAll("th");
            listThs.forEach((th) => {
                if (index_row == 0) {
                    console.log("content:", th.innerHTML);
                    console.log("WIDTH:", th.offsetWidth);
                }
                const ancho_elem = th.offsetWidth; // Obtener ancho del elemento actual

                if (th.classList.contains("fixed-column")) {
                    th.classList.add("xd"); // Agregar clase
                    th.style.position = "sticky"; // Hacerlo sticky
                    th.style.left = `${ancho_anterior}px`; // Posicionarlo con left
                }

                ancho_anterior += ancho_elem; // Sumar ancho actual al acumulado
            });
        });
    });
};

onMounted(async () => {
    cargaDatos();
    appStore.stopLoading();
    renderizaFixedColumns();
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
                                    <label>Filtro*</label>
                                    <select
                                        name="filtro"
                                        class="form-control"
                                        v-model="form.filtro"
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
                                    class="col-md-6"
                                    v-if="form.filtro == 'proyecto'"
                                >
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
                                <div
                                    class="col-md-6"
                                    v-if="form.filtro == 'trabajo'"
                                >
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
                                <div
                                    class="col-md-6"
                                    v-if="form.filtro == 'estado_pago'"
                                >
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
                                <div
                                    class="col-md-6"
                                    v-if="form.filtro == 'estado_trabajo'"
                                >
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
                                <div class="col-12 mt-2">
                                    <div class="row">
                                        <div class="col-md-6 offset-md-3">
                                            <label>Fechas*</label>
                                            <select
                                                name="filtro_fecha"
                                                class="form-control"
                                                v-model="form.filtro_fecha"
                                                @change="cambioValoresFiltros"
                                            >
                                                <option value="todos">
                                                    Todos
                                                </option>
                                                <option value="fechas">
                                                    Filtrar fechas
                                                </option>
                                            </select>
                                        </div>
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
                <div
                    class="h-screen flex"
                    :style="[
                        listTrabajos.length > 5
                            ? 'height: calc(75vh - 2rem)'
                            : 'height:auto',
                    ]"
                >
                    <div
                        style="overflow: auto"
                        :style="[
                            listTrabajos.length > 5
                                ? 'height: calc(70vh - 2rem)'
                                : 'height:auto',
                        ]"
                    >
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="fixed-column">#</th>
                                    <th class="fixed-column">Proyecto</th>
                                    <th class="fixed-column">Cliente</th>
                                    <th>Descripción</th>
                                    <th>Fecha Inicio</th>
                                    <th>Plazo días</th>
                                    <th>Fecha Entrega</th>
                                    <th>Estado/Trabajo</th>
                                    <th>Fecha de envío</th>
                                    <th>Fecha de conclusión</th>
                                    <th>Costo</th>
                                    <th>Cancelado</th>
                                    <th>Saldo</th>
                                    <th class="fixed-column">Estado/Pago</th>
                                </tr>
                            </thead>
                            <tbody style="max-height: 30vh" v-if="!cargando">
                                <tr v-for="(item, index) in listTrabajos">
                                    <td class="fixed-column">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="fixed-column">
                                        {{ item.proyecto?.nombre }}<br />({{
                                            item.proyecto?.alias
                                        }})
                                    </td>
                                    <td class="fixed-column">
                                        {{ item.cliente?.nombre }}
                                    </td>
                                    <td>
                                        {{ item.descripcion }}
                                    </td>
                                    <td>
                                        {{ item.fecha_inicio }}
                                    </td>
                                    <td>
                                        {{ item.dias_plazo }}
                                    </td>
                                    <td>
                                        {{ item.fecha_entrega }}
                                    </td>
                                    <td>
                                        {{ item.estado_trabajo }}
                                    </td>
                                    <td>
                                        {{ item.fecha_envio }}
                                    </td>
                                    <td>
                                        {{ item.fecha_conclusion }}
                                    </td>
                                    <td>
                                        {{ item.costo }}
                                    </td>
                                    <td>
                                        {{ item.cancelado }}
                                    </td>
                                    <td>
                                        {{ item.saldo }}
                                    </td>
                                    <td
                                        class="fixed-column"
                                        :class="[
                                            item.estado_pago == 'COMPLETO'
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
                            <tfoot>
                                <tr>
                                    <td colspan="10">TOTALES</td>
                                    <td>
                                        {{ total_costo }}
                                    </td>
                                    <td>
                                        {{ total_cancelado }}
                                    </td>
                                    <td>
                                        {{ total_saldos }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="">
                        <tr>
                            <th colspan="8">TOTALES</th>
                        </tr>
                        <tr>
                            <th>COSTOS</th>
                            <th>CANCELADO</th>
                            <th>SALDO</th>
                            <th>EN PROCESO</th>
                            <th>ENVIADOS</th>
                            <th>CONCLUIDOS</th>
                            <th>PAGOS COMPLETOS</th>
                            <th>PAGOS PENDIENTES</th>
                        </tr>
                    </thead>
                    <tbody v-if="!cargando">
                        <tr>
                            <td>
                                {{ total_costo }}
                            </td>
                            <td>
                                {{ total_cancelado }}
                            </td>
                            <td>
                                {{ total_saldos }}
                            </td>
                            <td>
                                {{ total_proceso }}
                            </td>
                            <td>
                                {{ total_enviado }}
                            </td>
                            <td>
                                {{ total_concluidos }}
                            </td>
                            <td>
                                {{ total_pagos_completos }}
                            </td>
                            <td>
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
    z-index: 10000000;
    background-color: white;
    box-shadow: 0 3px 3px -3px rgb(216, 216, 216);
}
table tfoot tr {
    position: sticky;
    bottom: -2px;
    z-index: 10000000;
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
