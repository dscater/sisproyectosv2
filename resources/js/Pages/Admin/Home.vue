<script setup>
import Content from "@/Components/Content.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { onMounted, onBeforeMount } from "vue";
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
});

onMounted(() => {
    appStore.stopLoading();
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
            <div class="col-md-3">
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
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__dark2">
                        <p class="h5">CANTIDAD TRABAJOS</p>
                        <p class="font-weight-bold h4">
                            {{ total_trabajos }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__dark3">
                        <p class="h5">CANTIDAD CANCELADOS</p>
                        <p class="font-weight-bold h4">
                            {{ cancelados }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__dark4">
                        <p class="h5">CANTIDAD EN PROCESO</p>
                        <p class="font-weight-bold h4">
                            {{ en_proceso }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body bg__dark5">
                        <p class="h5">CANTIDAD PENDIENTES</p>
                        <p class="font-weight-bold h4">
                            {{ no_cancelados }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
