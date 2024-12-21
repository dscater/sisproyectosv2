<script setup>
import Content from "@/Components/Content.vue";
import MiTable from "@/Components/MiTable.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { useProyectos } from "@/composables/proyectos/useProyectos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { ref, onMounted } from "vue";
import Formulario from "./Formulario.vue";
import axios from "axios";

const { props } = usePage();
const { limpiarProyecto, setProyecto, oProyecto } = useProyectos();
const { axiosGet, axiosDelete } = useCrudAxios();
const responseProyectos = ref([]);
const listProyectos = ref([]);
const itemsPerPage = ref(5);
const miTable = ref(null);
const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
    },
    {
        label: "NOMBRE PROYECTO",
        key: "nombre",
        sortable: true,
    },
    {
        label: "ALIAS",
        key: "alias",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN",
        key: "descripcion",
        sortable: true,
    },
    {
        label: "Fecha de Registro",
        key: "fecha_registro_t",
        keySortable: "fecha_registro",
        sortable: true,
    },
    { label: "Acción", key: "accion" },
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
    limpiarProyecto();
    accion_formulario.value = 0;
    muestra_formulario.value = true;
};
const editarProyecto = (item) => {
    setProyecto(item);
    accion_formulario.value = 1;
    muestra_formulario.value = true;
};

const eliminarProyecto = (item) => {
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
                route("proyectos.destroy", item.id)
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
const listData = ref([]);
const cargaRegistros = () => {
    axios.get(route("proyectos.listado")).then((response) => {
        listData.value = response.data.proyectos;
    });
};
cargaRegistros();

onMounted(() => {
    // cargaRegistros();
});
</script>
<template>
    <Head title="Proyectos" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Proyectos</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Proyectos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1">
            <div
                class="col-12"
                v-if="props.auth.user.permisos.includes('proyectos.create')"
            >
                <button class="btn btn-primary" @click="agregarRegistro">
                    <i class="fa fa-plus"></i> Agregar Proyecto
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <div class="row">
                            <div class="col-md-4 offset-md-8">
                                <input
                                    v-model="search"
                                    placeholder="Buscar"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <MiTable
                            ref="miTable"
                            :cols="headers"
                            :api="true"
                            :url="route('proyectos.paginado')"
                            :numPages="5"
                            :search="search"
                        >
                            <template #accion="{ item }">
                                <button
                                    class="btn btn-warning accion_icon"
                                    @click.prevent="editarProyecto(item)"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    class="btn btn-danger accion_icon"
                                    @click.prevent="eliminarProyecto(item)"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </template>
                        </MiTable>
                    </div>
                </div>
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
