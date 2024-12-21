<script setup>
import Content from "@/Components/Content.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";

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
    costo_total: {
        type: String,
        default: 0,
    },
});

onMounted(() => {});
</script>

<template>
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Starter Page</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Starter Page</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">TOTAL CANCELADO</p>
                            <p class="font-weight-bold text-h5">
                                {{ moneda_principal.nombre }}
                                {{ total_cancelado }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">SALDO TOTAL</p>
                            <p class="font-weight-bold text-h5">
                                {{ moneda_principal.nombre }}
                                {{ total_saldo }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">TOTAL TRABAJOS</p>
                            <p class="font-weight-bold text-h5">
                                {{ moneda_principal.nombre }}
                                {{ costo_total }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">CANTIDAD TRABAJOS</p>
                            <p class="font-weight-bold text-h5">
                                {{ total_trabajos }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">CANTIDAD CANCELADOS</p>
                            <p class="font-weight-bold text-h5">
                                {{ cancelados }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">CANTIDAD EN PROCESO</p>
                            <p class="font-weight-bold text-h5">
                                {{ en_proceso }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-h6">CANTIDAD PENDIENTES</p>
                            <p class="font-weight-bold text-h5">
                                {{ no_cancelados }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
