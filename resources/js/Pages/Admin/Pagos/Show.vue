<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, onBeforeMount } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
import Pago from "@/Pages/Admin/Pagos/Parcial/Pago.vue";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const props = defineProps({
    pago: {
        type: Object,
        default: null,
    },
});

onMounted(() => {
    appStore.stopLoading();
});
</script>
<template>
    <Head title="Nuevo Pago" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Pagos <small class="text-muted">> Ver</small>
                    </h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('pagos.index')">Pagos</Link>
                        </li>
                        <li class="breadcrumb-item active">Ver</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1">
            <div class="col-md-8 offset-md-2">
                <div class="row">
                    <div class="col-12">
                        <Pago :pago="pago"></Pago>
                    </div>
                </div>
                <Link
                    :href="route('pagos.index')"
                    class="btn btn-outline-secondary"
                    ><i class="fa fa-arrow-left"></i> Volver</Link
                >
            </div>
        </div>
    </Content>
</template>
