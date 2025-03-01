<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, onBeforeMount } from "vue";
import Pago from "@/Pages/Admin/Pagos/Parcial/Pago.vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const props = defineProps({
    trabajo: {
        type: Object,
        default: () => ({}),
    },
    pagos: {
        type: Array,
        default: () => [],
    },
});
onMounted(() => {
    appStore.stopLoading();
});
</script>
<template>
    <Head title="Trabajos - Pagos" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Trabajos <small class="text-muted">> Pagos</small>
                    </h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('trabajos.index')"
                                >Trabajos</Link
                            >
                        </li>
                        <li class="breadcrumb-item active">Pagos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1" v-if="pagos.length > 0">
            <div class="col-md-4 col-xs-6" v-for="item in pagos">
                <Pago :pago="item"></Pago>
            </div>
        </div>
        <div class="row" v-else>
            <h4 class="w-100 text-center">NO SE ENCONTRARÓN PAGOS</h4>
        </div>
        <div class="row">
            <div class="col-md-6">
                <Link :href="route('trabajos.index')" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Volver</Link>
            </div>
        </div>
    </Content>
</template>
