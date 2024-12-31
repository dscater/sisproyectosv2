<script setup>
import Content from "@/Components/Content.vue";
import MiTable from "@/Components/MiTable.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { useTipoCambios } from "@/composables/tipo_cambios/useTipoCambios";
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
const { limpiarTipoCambio, setTipoCambio, oTipoCambio } = useTipoCambios();
const { axiosGet, axiosDelete } = useCrudAxios();
const responseTipoCambios = ref([]);
const listTipoCambios = ref([]);
const itemsPerPage = ref(5);
const miTable = ref(null);
const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
        width: "80",
    },
    {
        label: "PRINCIPAL",
        key: "moneda1",
        sortable: true,
        keySortable: "monedas.valor1",
    },
    {
        label: "MONEDA 2",
        key: "moneda2",
        sortable: true,
        keySortable: "monedas.valor2",
    },
    { label: "Acción", key: "accion", width: "100" },
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
    limpiarTipoCambio();
    accion_formulario.value = 0;
    muestra_formulario.value = true;
};
const editarTipoCambio = (item) => {
    setTipoCambio(item);
    accion_formulario.value = 1;
    muestra_formulario.value = true;
};

const eliminarTipoCambio = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.moneda_1.nombre} ${item.valor1} - ${item.moneda_2.nombre} ${item.valor2}</strong>`,
        showCancelButton: true,
        confirmButtonColor: "#B61431",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosDelete(
                route("tipo_cambios.destroy", item.id)
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
    <Head title="Tipo de Cambios" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tipo de Cambios</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Tipo de Cambios</li>
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
                                    'tipo_cambios.create'
                                )
                            "
                            class="btn btn-primary btn-flat h-100"
                            @click="agregarRegistro"
                        >
                            <i class="fa fa-plus"></i> Nuevo Tipo de Cambio
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
                    :url="route('tipo_cambios.paginado')"
                    :numPages="5"
                    :search="search"
                >
                    <template #moneda1="{ item }">
                        {{ item.moneda_1.nombre }} {{ item.valor1 }}
                    </template>
                    <template #moneda2="{ item }">
                        {{ item.moneda_2.nombre }} {{ item.valor2 }}
                    </template>

                    <template #accion="{ item }">
                        <button
                            class="btn btn-warning accion_icon"
                            @click.prevent="editarTipoCambio(item)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            class="btn btn-danger accion_icon"
                            @click.prevent="eliminarTipoCambio(item)"
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
