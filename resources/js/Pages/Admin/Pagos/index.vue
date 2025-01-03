<script setup>
import Content from "@/Components/Content.vue";
import MiTable from "@/Components/MiTable.vue";
import { Head, usePage, Link, router } from "@inertiajs/vue3";
import { usePagos } from "@/composables/pagos/usePagos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { ref, onMounted, onBeforeMount } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
import { fHelpers } from "@/Functions/fHelpers";
const { getFormatoMoneda } = fHelpers();
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const { props } = usePage();
const { setPago, oPago } = usePagos();
const { axiosDelete } = useCrudAxios();
const itemsPerPage = ref(5);
const miTable = ref(null);
const headers = ref([
    {
        label: "ID",
        key: "id",
        sortable: true,
        width: "60",
    },
    {
        label: "NOMBRE PROYECTO",
        key: "trabajo.proyecto.nombre",
        keySortable: "proyectos.nombre",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN TRABAJO",
        key: "trabajo.descripcion",
        keySortable: "trabajos.descripcion",
        sortable: true,
    },
    {
        label: "CLIENTE",
        key: "cliente.nombre",
        keySortable: "clientes.nombre",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN",
        key: "descripcion",
        keySortable: "pagos.descripcion",
        sortable: true,
    },
    {
        label: "DESCRIPCIÓN ARCHIVO",
        key: "descripcion_archivo",
        keySortable: "pagos.descripcion_archivo",
        sortable: true,
    },
    {
        label: "FECHA DE PAGO",
        key: "fecha_pago_t",
        keySortable: "pagos.created_at",
        sortable: true,
        fixed:"right"
    },
    {
        label: "MONTO CANCELADO",
        key: "monto",
        sortable: true,
        fixed:"right"
    },
    { label: "ACCIÓN", key: "accion",
    fixed:"right",
    classTd:()=>{
        return 'accion';
    }
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

const eliminarPago = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.trabajo.proyecto.nombre} <br/> ${item.cliente.nombre}</strong>`,
        showCancelButton: true,
        confirmButtonColor: "#B61431",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosDelete(route("pagos.destroy", item.id));
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
    <Head title="Pagos" />
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Pagos</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Pagos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row mb-1">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8 my-1 d-flex align-end pt-3">
                        <Link
                            v-if="
                                props.auth.user.permisos.includes(
                                    'pagos.create'
                                )
                            "
                            class="btn btn-primary btn-flat"
                            :href="route('pagos.create')"
                        >
                            <i class="fa fa-plus"></i> Nuevo Pago
                        </Link>
                    </div>
                    <div class="col-md-4 my-1 d-flex pl-4">
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
                    :url="route('pagos.paginado')"
                    :numPages="5"
                    :multiSearch="multiSearch"
                    :syncOrderBy="'pagos.created_at'"
                    :syncOrderAsc="'DESC'"
                >
                    <template #['trabajo.proyecto.nombre'] = {item}>
                        <div style="word-wrap: break-word; white-space: wrap">
                            {{ item.trabajo.proyecto.nombre }}
                            <br />
                            <b class="text-sm">
                                ({{ item.trabajo.proyecto.alias }})</b
                            >
                        </div>
                    </template>

                    <template #['trabajo.descripcion']="{ item }">
                        <div v-html="item.trabajo.descripcion">
                        </div>
                    </template>

                    <template #descripcion="{ item }">
                        <div v-html="item.descripcion">
                        </div>
                    </template>

                    <template #descripcion_archivo="{ item }">
                        <div v-html="item.descripcion_archivo">
                        </div>
                    </template>

                    <template #monto="{ item }">
                        <div class="w-100">
                            <div class="w-100 text-center font-weight-bold text-md">
                                {{ item.moneda.nombre }}
                                {{ getFormatoMoneda(item.monto) }}
                            </div>
                            <div
                                v-if="item.trabajo.proyecto.tipo_cambio_id != 0 && item.moneda_cambio"
                                class="w-100 text-center"
                            >
                                {{ item.moneda_cambio.nombre }}
                                {{ getFormatoMoneda(item.monto_cambio) }}
                            </div>
                        </div>
                    </template>

                    <template #accion="{ item }">
                        <Link
                            :href="route('pagos.show',item.id)"
                            class="btn btn-primary accion_icon"
                        >
                            <i class="fa fa-eye"></i>
                        </Link>
                        <Link
                            :href="route('pagos.edit', item.id)"
                            class="btn btn-warning accion_icon"
                        >
                            <i class="fa fa-edit"></i>
                        </Link>
                        <button
                            class="btn btn-danger accion_icon"
                            @click.prevent="eliminarPago(item)"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </template>
                </MiTable>
            </div>
        </div>
    </Content>
</template>
