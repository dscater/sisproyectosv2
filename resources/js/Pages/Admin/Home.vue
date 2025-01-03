<script setup>
import Content from "@/Components/Content.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { onMounted, onBeforeMount, ref } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
import { fHelpers } from "@/Functions/fHelpers";
import Highcharts from "highcharts";
import exporting from "highcharts/modules/exporting";
import accessibility from "highcharts/modules/accessibility";
import axios from "axios";
exporting(Highcharts);
accessibility(Highcharts);
Highcharts.setOptions({
    lang: {
        downloadPNG: "Descargar PNG",
        downloadJPEG: "Descargar JPEG",
        downloadPDF: "Descargar PDF",
        downloadSVG: "Descargar SVG",
        printChart: "Imprimir gráfico",
        contextButtonTitle: "Menú de exportación",
        viewFullscreen: "Pantalla completa",
        exitFullscreen: "Salir de pantalla completa",
    },
});

const { getFormatoMoneda, getAnioActual } = fHelpers();
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});

const props = defineProps({
    moneda_principal: {
        type: Object,
        default: () => ({}),
    },
    total_trabajos: {
        type: Number,
        default: 0,
    },
    cancelados: {
        type: Number,
        default: 0,
    },
    no_cancelados: {
        type: Number,
        default: 0,
    },
    en_proceso: {
        type: Number,
        default: 0,
    },
    total_cancelado: {
        type: String,
        default: 0,
    },
    total_saldo: {
        type: String,
        default: 0,
    },
    total_saldo_enviando: {
        type: String,
        default: 0,
    },
    costo_total: {
        type: String,
        default: 0,
    },
    anios: {
        type: Array,
        default: [],
    },
});

const graficoResumen = () => {
    Highcharts.chart("graficoResumen", {
        chart: {
            type: "column",
        },
        title: {
            align: "center",
            text: `RESUMEN COSTOS-SALDOS`,
        },
        subtitle: {
            align: "center",
            text: ``,
        },
        accessibility: {
            announceNewData: {
                enabled: true,
            },
        },
        xAxis: {
            type: "category",
        },
        yAxis: {
            title: {
                text: "Monto de la Oferta/Puja",
            },
        },
        legend: {
            enabled: true,
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    // format: "{point.y}",
                    style: {
                        fontSize: "11px",
                        fontWeight: "bold",
                    },
                    formatter: function () {
                        return getFormatoMoneda(this.point.y); // Aquí se aplica el formato de moneda
                    },
                },
            },
        },
        tooltip: {
            formatter: function () {
                return `<span style="font-size:11px;color:${
                    this.point.color
                }" class="font-weight-bold">${
                    this.point.name
                }</span><br/><div style="display:block;width:100%;" class="text-md text-center">${getFormatoMoneda(
                    this.point.y
                )}</div>`;
            },
        },
        series: [
            {
                name: "TOTALES",
                data: [
                    {
                        name: "CANCELADO",
                        y: parseFloat(props.total_cancelado),
                        color: "#67c23a",
                    },
                    {
                        name: "SALDO ENVIADOS/CONCLUIDOS",
                        y: parseFloat(props.total_saldo_enviando),
                        color: "#f56c6c",
                    },
                    {
                        name: "SALDO TOTAL",
                        y: parseFloat(props.total_saldo),
                        color: "#e6a23c",
                    },
                    {
                        name: "TOTAL",
                        y: parseFloat(props.costo_total),
                        color: "#409eff",
                    },
                ],
            },
        ],
    });
};

const filtroGPagos = ref({
    filtro: "poranio",
    anio: getAnioActual(),
});

const graficoPagos = () => {
    axios
        .get(route("entrenamientos.graficoPagos"), {
            params: filtroGPagos.value,
        })
        .then((response) => {
            Highcharts.chart("graficoPagos", {
                chart: {
                    type: "line",
                },
                title: {
                    align: "center",
                    text: `PAGOS`,
                },
                subtitle: {
                    align: "center",
                    text: ``,
                },
                accessibility: {
                    announceNewData: {
                        enabled: true,
                    },
                },
                xAxis: {
                    categories: response.data.categories,
                },
                yAxis: {
                    title: {
                        text: "Monto de la Oferta/Puja",
                    },
                },
                legend: {
                    enabled: true,
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            // format: "{point.y}",
                            style: {
                                fontSize: "11px",
                                fontWeight: "bold",
                            },
                            formatter: function () {
                                return getFormatoMoneda(this.point.y); // Aquí se aplica el formato de moneda
                            },
                        },
                    },
                },
                tooltip: {
                    formatter: function () {
                        return `<span style="font-size:11px" class="font-weight-bold">${
                            this.point.category
                        }</span><br/><div class="text-md d-block w-100 text-center">${getFormatoMoneda(
                            this.point.y
                        )}</div>`;
                    },
                },
                series: [
                    {
                        name: "TOTALES",
                        data: response.data.data,
                    },
                ],
            });
        });
};

onMounted(() => {
    appStore.stopLoading();
    graficoResumen();
    graficoPagos();
});
</script>

<template>
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Inicio</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Inicio</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>

        <div class="row">
            <!-- <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__success">
                        <p class="h5">TOTAL CANCELADO</p>
                        <p class="font-weight-bold h4">
                            {{ moneda_principal.nombre }}
                            {{ total_cancelado }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__danger">
                        <p class="h5">SALDO ENVIADOS/CONCLUIDOS</p>
                        <p class="font-weight-bold h4">
                            {{ moneda_principal.nombre }}
                            {{ total_saldo_enviando }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__warning">
                        <p class="h5">SALDO TOTAL</p>
                        <p class="font-weight-bold h4">
                            {{ moneda_principal.nombre }}
                            {{ total_saldo }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__primary">
                        <p class="h5">TOTAL TRABAJOS</p>
                        <p class="font-weight-bold h4">
                            {{ moneda_principal.nombre }}
                            {{ costo_total }}
                        </p>
                    </div>
                </div>
            </div> -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__dark5">
                        <p class="h5">CANTIDAD TRABAJOS</p>
                        <p class="font-weight-bold h4">
                            {{ total_trabajos }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__dark5">
                        <p class="h5">CANTIDAD CANCELADOS</p>
                        <p class="font-weight-bold h4">
                            {{ cancelados }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__warning">
                        <p class="h5">CANTIDAD EN PROCESO</p>
                        <p class="font-weight-bold h4">
                            {{ en_proceso }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__danger">
                        <p class="h5">CANTIDAD PENDIENTES</p>
                        <p class="font-weight-bold h4">
                            {{ no_cancelados }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 overflow-hidden">
                <div id="graficoResumen"></div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Seleccionar</label>
                                <select
                                    class="form-control rounded-0"
                                    v-model="filtroGPagos.filtro"
                                    @change="graficoPagos"
                                >
                                    <option value="poranio">Anual</option>
                                    <option value="gestion">
                                        Por gestiones
                                    </option>
                                </select>
                            </div>
                            <div
                                class="col-md-6"
                                v-if="filtroGPagos.filtro == 'poranio'"
                                @change="graficoPagos"
                            >
                                <label>Seleccionar año</label>
                                <select
                                    class="form-control rounded-0"
                                    v-model="filtroGPagos.anio"
                                >
                                    <option v-for="item in anios" :value="item">
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 overflow-hidden mb-5">
                                <div id="graficoPagos"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
