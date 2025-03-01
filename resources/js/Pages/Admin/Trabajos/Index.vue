<script setup>
import Content from "@/Components/Content.vue";
import MiTable from "@/Components/MiTable.vue";
import { Head, usePage, Link, router } from "@inertiajs/vue3";
import { useTrabajos } from "@/composables/trabajos/useTrabajos";
import { useCrudAxios } from "@/composables/curdAxios/useCrudAxios";
import { ref, onMounted, onBeforeMount } from "vue";
import { fHelpers } from "@/Functions/fHelpers";
import { useAppStore } from "@/stores/aplicacion/appStore";
const { getFormatoMoneda } = fHelpers();
const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});
const { props } = usePage();
const { limpiarTrabajo, setTrabajo, oTrabajo } = useTrabajos();
const { axiosPost, axiosDelete } = useCrudAxios();
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
        width: "3%",
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
        width:"140"
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
            return item.estado_pago == "COMPLETO" ? "bg-cancelado" : "bg__danger";
        },
    },
    {
        label: "DESCRIPCIÓN",
        key: "descripcion",
        sortable: true,
        width: "200",
    },
    {
        label: "FECHA INICIO",
        key: "fecha_inicio_t",
        keySortable: "fecha_inicio",
        sortable: true,
    },
    {
        label: "FECHA ENTREGA",
        key: "fecha_entrega_t",
        keySortable: "fecha_entrega",
        sortable: true,
    },
    {
        label: "FECHA ENVIO",
        key: "fecha_envio_t",
        keySortable: "fecha_envio",
        sortable: true,
    },
    {
        label: "FECHA CONCLUSIÓN",
        key: "fecha_conclusion_t",
        keySortable: "fecha_conclusion",
        sortable: true,
    },
    {
        label: "FECHA REGISTRO",
        key: "fecha_registro_t",
        keySortable: "fecha_registro",
        sortable: true,
    },
    {
        label: "ESTADO TRABAJO",
        key: "estado_trabajo",
        sortable: true,
        fixed:"right",
        classTd: (item) => {
            let claseTd = "bg-proceso";
            if(item.estado_trabajo == 'ENVIADO'){
                claseTd = "bg-enviado";
            }
            if(item.estado_trabajo == 'CONCLUIDO'){
                claseTd = "bg-concluido";
            }
            return claseTd;
        },
    },
    {
        label: "ACCIÓN",
        key: "accion",
        fixed: "right",
        width: "4%",
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


const eliminarTrabajo = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>(${item.proyecto.alias})</strong><br/><p style="margin-bottom:3px;">${item.proyecto.nombre}</p><br/>${item.cliente.nombre}`,
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

const confirmarEnvio= (item)=>{
        Swal.fire({
        icon:"question",
        title: `Confirmar envío <i class="fa fa-paper-plane"></i>`,
        html: `<strong>(${item.proyecto.alias})</strong><br/><p style="margin-bottom:3px;">${item.proyecto.nombre}</p><br/>${item.cliente.nombre}`,
        showCancelButton: true,
        confirmButtonColor: "#1867c0",
        confirmButtonText: "Si, confirmar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
        background: "#e6a23c",
        color: "#ffffff",
        iconColor: '#ffffff',
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosPost(
                route("trabajos.confirma_envio", item.id)
            );
            if (respuesta && respuesta.sw) {
                updateDatos();
            }
        }
    });
}
const confirmarConcluido= (item)=>{
        Swal.fire({
        icon:"question",
        title: `Confirmar concluído <i class="fa fa-check-circle"></i>`,
        html: `<strong>(${item.proyecto.alias})</strong><br/><p style="margin-bottom:3px;">${item.proyecto.nombre}</p><br/>${item.cliente.nombre}`,
        showCancelButton: true,
        confirmButtonColor: "#1867c0",
        confirmButtonText: "Si, confirmar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
        background: "#67c23a",
        color: "#ffffff",
        iconColor: '#ffffff',
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosPost(
                route("trabajos.confirma_concluido", item.id)
            );
            if (respuesta && respuesta.sw) {
                updateDatos();
            }
        }
    });
}

const cancelarConcluido= (item)=>{
        Swal.fire({
        icon:"question",
        title: `Cancelar concluído <i class="fa fa-check-circle"></i>`,
        html: `<strong>(${item.proyecto.alias})</strong><br/><p style="margin-bottom:3px;">${item.proyecto.nombre}</p><br/>${item.cliente.nombre}`,
        showCancelButton: true,
        confirmButtonColor: "#1867c0",
        confirmButtonText: "Si, cancelar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
        background: "#f56c6c",
        color: "#ffffff",
        iconColor: '#ffffff',
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosPost(
                route("trabajos.cancelar_concluido", item.id)
            );
            if (respuesta && respuesta.sw) {
                updateDatos();
            }
        }
    });
}

const updateDatos = async () => {
    if (miTable.value) {
        await miTable.value.cargarDatos();
    }
};

const getPorcentajeCancelado = (item)=>{
    const porcentaje = Math.round((parseFloat(item.cancelado) * 100) / parseFloat(item.costo),2);
    return porcentaje
}

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
                            label="Pago pendiente"
                                >PAGO PENDIENTE</el-option
                            >
                            <el-option value="pagocompleto"
                            label="Pago completo"
                                >PAGO COMPLETO</el-option
                            >
                            <el-option value="proceso" label="En proceso">EN PROCESO</el-option>
                            <el-option value="enviado" label="Enviado"
                                >ENVIADO</el-option
                            >
                            <el-option value="concluido" label="Concluido"
                                >CONCLUIDOS</el-option
                            >
                        </el-select>
                    </div>
                    <div class="col-md-5 my-1 d-flex">
                        <div class="input-group" style="align-items: end">
                            <input
                                v-model="multiSearch.search"
                                placeholder="Buscar"
                                class="form-control border-1 border-right-0"
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
                    :syncOrderBy="'fecha_inicio'"
                    :syncOrderAsc="'DESC'"
                    table-responsive
                    fixed-header
                >
                    <template #['proyecto.nombre']="{ item }">
                        <div style="word-wrap: break-word; white-space: wrap">
                            {{ item.proyecto.nombre }}
                            <br/>
                            <b class="text-sm"> ({{ item.proyecto.alias }})</b>
                        </div>
                    </template>
                    <template #costo="{ item }">
                        <div class="w-100">
                            <div
                                class="badge bg__primary2 text-md rounded-0 d-block text-center text-wrap"
                            >
                                {{ item.moneda.nombre }}
                                {{ getFormatoMoneda(item.costo) }}
                            </div>
                            <div
                                v-if="item.tipo_cambio_id != 0"
                                class="badge bg__success2 text-md rounded-0 d-block text-center text-wrap"
                            >
                                {{ item.moneda_cambio.nombre }}
                                {{ getFormatoMoneda(item.costo_cambio) }}
                            </div>
                        </div>
                    </template>

                    <template #descripcion="{ item }">
                        <div class="w-100" v-html="item.descripcion"></div>
                    </template>

                    <template #cancelado="{ item }">
                        <div class="w-100">
                            <div class="w-100 text-center font-weight-bold text-sm">
                                {{ item.moneda.nombre }}
                                {{ getFormatoMoneda(item.cancelado) }}
                            </div>
                            <div
                                v-if="item.tipo_cambio_id != 0"
                                class="w-100 text-center"
                            >
                                {{ item.moneda_cambio.nombre }}
                                {{ getFormatoMoneda(item.cancelado_cambio) }}
                            </div>
                            <div class="cont_barra_progreso">
                                <span>{{ getPorcentajeCancelado(item) }} %</span>
                                <div class="bara_prog" :style="{
                                    width:getPorcentajeCancelado(item)+'%'
                                }"></div>
                            </div>
                        </div>
                    </template>

                    <template #saldo="{ item }">
                        <div class="w-100">
                            <div class="w-100 text-center">
                                {{ item.moneda.nombre }}
                                {{ getFormatoMoneda(item.saldo) }}
                            </div>
                            <div
                                v-if="item.tipo_cambio_id != 0"
                                class="w-100 text-center"
                            >
                                {{ item.moneda_cambio.nombre }}
                                {{ getFormatoMoneda(item.saldo_cambio) }}
                            </div>
                        </div>
                    </template>

                    <template #estado_trabajo="{ item }">
                        <div class="font-weight-bold text-xs">
                        {{ item.estado_trabajo }}
                        </div>
                    </template>

                    <template #accion="{ item }">
                        <button v-if="item.estado_trabajo == 'CONCLUIDO'" class="btn bg__danger accion_icon"@click="cancelarConcluido(item)"><i class="fa fa-ban"></i></button>
                        <button v-if="!item.fecha_conclusion" class="btn bg__success accion_icon"@click="confirmarConcluido(item)"><i class="fa fa-check-circle"></i></button>
                        <button v-if="!item.fecha_envio" class="btn bg__warning accion_icon"@click="confirmarEnvio(item)"><i class="fa fa-paper-plane"></i></button>
                        <Link
                        :href="route('trabajos.pagos', item.id)"
                            class="btn bg__primary accion_icon"
                            v-if="item.cancelado > 0"
                        >
                            <i class="fa fa-list"></i>
                        </Link>
                        <Link
                        :href="route('trabajos.edit', item.id)"
                            class="btn btn-warning accion_icon"
                        >
                            <i class="fa fa-edit"></i>
                        </Link>
                        <button
                        v-if="item.estado_trabajo != 'CONCLUIDO'"
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
<style scoped>
</style>
