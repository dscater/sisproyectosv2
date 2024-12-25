<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link } from "@inertiajs/vue3";
import Formulario from "./Parcial/Formulario.vue";
import { useTrabajos } from "@/composables/trabajos/useTrabajos";
const { limpiarTrabajo, setTrabajo, oTrabajo } = useTrabajos();

limpiarTrabajo();
</script>
<template>
    <Head title="Nuevo Trabajo" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Trabajos <small class="text-muted">> Nuevo</small>
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
