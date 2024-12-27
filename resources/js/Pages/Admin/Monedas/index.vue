<script setup>
import Content from "@/Components/Content.vue";
import MiTable from "@/Components/MiTable.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { useMonedas } from "@/composables/monedas/useMonedas";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { ref, onMounted, onBeforeMount } from "vue";
import Formulario from "./Formulario.vue";
import axios from "axios";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();

onBeforeMount(() => {
    appStore.startLoading();
});

const { props } = usePage();
const { limpiarMoneda, setMoneda, oMoneda } = useMonedas();
const { axiosGet, axiosDelete } = useCrudAxios();
const responseMonedas = ref([]);
const listMonedas = ref([]);
const itemsPerPage = ref(5);
const miTable = ref(null);
const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
        width: 1,
    },
    {
        label: "NOMBRE",
        key: "nombre",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN",
        key: "descripcion",
        sortable: true,
    },
    {
        label: "TIPO",
        key: "principal",
        sortable: true,
    },
    { label: "Acción", key: "accion", width: "1" },
]);

const search = ref("");
const options = ref({
    page: 1,
    itemsPerPage: itemsPerPage,
    sortBy: "",
    sortOrder: "desc",
    search: "",
});

const loading = ref(true);
const accion_formulario = ref(0);
const muestra_formulario = ref(false);

const agregarRegistro = () => {
    limpiarMoneda();
    accion_formulario.value = 0;
    muestra_formulario.value = true;
};
const editarMoneda = (item) => {
    setMoneda(item);
    accion_formulario.value = 1;
    muestra_formulario.value = true;
};

const eliminarMoneda = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.nombre}</strong>`,
        showCancelButton: true,
        confirmButtonColor: "#B61431",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosDelete(
                route("monedas.destroy", item.id)
            );
            if (respuesta && respuesta.sw) {
                updateDatos();
            }
        }
    });
};

const updateDatos = async () => {
    if (miTable.value) {
        await miTable.value.cargarDatos();
        muestra_formulario.value = false;
    }
};
onMounted(() => {
    appStore.stopLoading();
});
</script>
<template>
    <Head title="Monedas" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Monedas</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Monedas</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8 my-1">
                        <button
                            v-if="
                                props.auth.user.permisos.includes(
                                    'monedas.create'
                                )
                            "
                            class="btn btn-primary btn-flat h-100"
                            @click="agregarRegistro"
                        >
                            <i class="fa fa-plus"></i> Nuevo Moneda
                        </button>
                    </div>
                    <div class="col-md-4 my-1">
                        <div class="input-group">
                            <input
                                v-model="search"
                                placeholder="Buscar"
                                class="form-control border-1 border-right-0"
                                @keypress.enter.prevent="updateDatos"
                            />
                            <div class="input-append">
                                <button
                                    class="btn btn-default rounded-0 border-left-0"
                                    @click="updateDatos"
                                >
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <MiTable
                    :tableClass="'bg-white mitabla'"
                    ref="miTable"
                    :cols="headers"
                    :api="true"
                    :url="route('monedas.paginado')"
                    :numPages="5"
                    :search="search"
                >
                    <template #principal="{ item }">
                        {{ item.principal }}
                    </template>
                    <template #accion="{ item }">
                        <button
                            class="btn btn-warning accion_icon"
                            @click.prevent="editarMoneda(item)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            v-if="item.principal == 0"
                            class="btn btn-danger accion_icon"
                            @click.prevent="eliminarMoneda(item)"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </template>
                </MiTable>
            </div>
        </div>
        <Formulario
            :estado_formulario="muestra_formulario"
            :accion_formulario="accion_formulario"
            @envio-formulario="updateDatos"
            @cerrar-formulario="muestra_formulario = false"
        ></Formulario>
    </Content>
</template>
