<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link } from "@inertiajs/vue3";
import Formulario from "./Parcial/Formulario.vue";
import { usePagos } from "@/composables/pagos/usePagos";

const { limpiarPago, setPago, oPago } = usePagos();
const props = defineProps({
    pago: {
        type: Object,
        default: null,
    },
});

setPago(props.pago);
</script>
<template>
    <Head title="Nuevo Pago" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Pagos <small class="text-muted">> Editar</small>
                    </h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('pagos.index')"
                                >Pagos</Link
                            >
                        </li>
                        <li class="breadcrumb-item active">Nuevo</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <Formulario></Formulario>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
