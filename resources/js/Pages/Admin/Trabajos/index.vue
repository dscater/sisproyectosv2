<script setup>
import Content from "@/Components/Content.vue";
import MiTable from "@/Components/MiTable.vue";
import { Head, usePage, Link, router } from "@inertiajs/vue3";
import { useTrabajos } from "@/composables/trabajos/useTrabajos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { ref, onMounted, reactive, onBeforeMount } from "vue";
import axios from "axios";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const { props } = usePage();
const { limpiarTrabajo, setTrabajo, oTrabajo } = useTrabajos();
const { axiosGet, axiosDelete } = useCrudAxios();
const responseTrabajos = ref([]);
const listTrabajos = ref([]);
const itemsPerPage = ref(5);
const miTable = ref(null);
const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
        fixed: true,
        width: "80",
    },
    {
        label: "NOMBRE PROYECTO",
        key: "proyecto.nombre",
        keySortable: "proyectos.nombre",
        sortable: true,
        fixed: true,
        width: "10%",
    },
    {
        label: "CLIENTE",
        key: "cliente.nombre",
        keySortable: "clientes.nombre",
        sortable: true,
    },
    {
        label: "COSTO",
        key: "costo",
        sortable: true,
    },
    {
        label: "CANCELADO",
        key: "cancelado",
        sortable: true,
    },
    {
        label: "SALDO",
        key: "saldo",
        sortable: true,
        classTd: (item) => {
            return item.estado_pago == "COMPLETO" ? "bg-cancelado" : "bg-red";
        },
    },
    {
        label: "ESTADOS",
        key: "estado_trabajo",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN",
        key: "descripcion",
        sortable: true,
        width: "400",
    },
    {
        label: "FECHAS",
        key: "fecha_registro_t",
        keySortable: "fecha_registro",
        sortable: true,
        fixed: "right",
        width: "10%",
    },
    {
        label: "ACCIÓN",
        key: "accion",
        fixed: "right",
        width: "10%",
        classTd: () => {
            return "accion";
        },
    },
]);

const search = ref("");
const multiSearch = ref({
    search: "",
    filtro: [],
});
const options = ref({
    page: 1,
    itemsPerPage: itemsPerPage,
    sortBy: "",
    sortOrder: "desc",
    search: "",
});

const loading = ref(true);

const editarTrabajo = (item) => {
    setTrabajo(item);
    router.get(route("trabajos.edit", item.id));
};

const eliminarTrabajo = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.proyecto.nombre} <br/> ${item.cliente.nombre}</strong>`,
        showCancelButton: true,
        confirmButtonColor: "#B61431",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosDelete(
                route("trabajos.destroy", item.id)
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
    }
};
onMounted(() => {
    appStore.stopLoading();
});
</script>
<template>
    <Head title="Trabajos" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Trabajos</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Trabajos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4 my-1 d-flex align-end pt-3">
                        <Link
                            v-if="
                                props.auth.user.permisos.includes(
                                    'trabajos.create'
                                )
                            "
                            class="btn btn-primary btn-flat"
                            :href="route('trabajos.create')"
                        >
                            <i class="fa fa-plus"></i> Nuevo Trabajo
                        </Link>
                    </div>
                    <div class="col-md-3 my-1 d-flex align-end">
                        <el-select
                            class="w-100 mt-auto"
                            size="large"
                            placeholder="Filtrar por:"
                            v-model="multiSearch.filtro"
                            multiple
                            collapse-tags
                            collapse-tags-tooltip
                            clearable
                        >
                            <el-option value="pagopendiente"
                                >PAGOS PENDIENTES</el-option
                            >
                            <el-option value="proceso">EN PROCESO</el-option>
                            <el-option value="concluidosenviados"
                                >CONCLUIDOS/ENVIADOS</el-option
                            >
                        </el-select>
                    </div>
                    <div class="col-md-5 my-1 d-flex">
                        <div class="input-group" style="align-items: end">
                            <input
                                v-model="multiSearch.search"
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
                    :url="route('trabajos.paginado')"
                    :numPages="5"
                    :multiSearch="multiSearch"
                    :syncOrderBy="'fecha_registro'"
                    :syncOrderAsc="'DESC'"
                    table-responsive
                    fix-cols
                    fixed-header
                >
                    <template #['proyecto.nombre']="{ item }">
                        <div style="word-wrap: break-word; white-space: wrap">
                            {{ item.proyecto.nombre }}
                        </div>
                    </template>
                    <template #costo="{ item }">
                        <div class="w-100">
                            <div
                                class="badge badge-primary text-md rounded-0 d-block text-center"
                            >
                                {{ item.moneda.nombre }}
                                {{ item.costo }}
                            </div>
                            <div
                                v-if="item.tipo_cambio_id != 0"
                                class="badge badge-success text-md rounded-0 d-block text-center"
                            >
                                {{ item.moneda_cambio.nombre }}
                                {{ item.costo_cambio }}
                            </div>
                        </div>
                    </template>

                    <template #descripcion="{ item }">
                        <div class="w-100" v-html="item.descripcion"></div>
                    </template>

                    <template #cancelado="{ item }">
                        <div class="w-100">
                            <div class="w-100 text-center">
                                {{ item.moneda.nombre }}
                                {{ item.cancelado }}
                            </div>
                            <div
                                v-if="item.tipo_cambio_id != 0"
                                class="w-100 text-center"
                            >
                                {{ item.moneda_cambio.nombre }}
                                {{ item.cancelado_cambio }}
                            </div>
                        </div>
                    </template>

                    <template #accion="{ item }">
                        <button
                            class="btn btn-warning accion_icon"
                            @click.prevent="editarTrabajo(item)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            class="btn btn-danger accion_icon"
                            @click.prevent="eliminarTrabajo(item)"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </template>
                </MiTable>
            </div>
        </div>
    </Content>
</template>
