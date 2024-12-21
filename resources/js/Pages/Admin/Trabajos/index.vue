<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import pagination from "@/Components/Pagination.vue";
import { inject, ref, watch, computed } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
const props = defineProps({
    trabajos: {
        type: Object,
        default: () => ({}),
    },
    total_trabajos: {
        type: Number,
        default: 0,
    },
    cancelados: {
        type: Number,
        default: 0,
    },
    no_cancelados: {
        type: Number,
        default: 0,
    },
    en_proceso: {
        type: Number,
        default: 0,
    },
    total_cancelado: {
        type: Number,
        default: 0,
    },
    total_saldo: {
        type: Number,
        default: 0,
    },
    costo_total: {
        type: Number,
        default: 0,
    },
    filtros: {
        type: Object,
        default: () => ({}),
    },
    paginationLinks: {
        type: String,
        default: "",
    },
});

const Swal = inject("$swal");

const form = useForm();

function destroy(id) {
    Swal.fire({
        title: "¿Estás seguro de eliminar este registro?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: `No cancelar`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            form.delete(route("trabajos.destroy", id));
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}

function marca_enviado(id, index, descripcion = "") {
    Swal.fire({
        title: "¿Estás seguro(a) de marcar como ENVIADO este registro?",
        html: `${descripcion != "" ? descripcion : ""}`,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonColor: "#2563eb",
        confirmButtonText: "Si, confirmar enviado",
        cancelButtonText: `No cancelar`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            axios.post("/trabajos/confirma/envio/" + id).then((response) => {
                console.log(props);
                console.log(props.trabajos.data[index]);
                props.trabajos.data[index].estado_trabajo =
                    response.data.trabajo.estado_trabajo;
                props.trabajos.data[index].fecha_envio =
                    response.data.trabajo.fecha_envio;
                Swal.fire({
                    icon: "success",
                    title: response.data.message,
                    showConfirmButton: false,
                });
            });
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}
function marca_concluido(id, index, descripcion = "") {
    Swal.fire({
        title: "¿Estás seguro(a) de marcar como CONCLUIDO este registro?",
        html: `${descripcion != "" ? descripcion : ""}`,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonColor: "#16a34a",
        confirmButtonText: "Si, confirmar concluido",
        cancelButtonText: `No cancelar`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            axios
                .post("/trabajos/confirma/concluido/" + id)
                .then((response) => {
                    console.log(props);
                    console.log(props.trabajos.data[index]);
                    props.trabajos.data[index].estado_trabajo =
                        response.data.trabajo.estado_trabajo;
                    props.trabajos.data[index].fecha_conclusion =
                        response.data.trabajo.fecha_conclusion;
                    Swal.fire({
                        icon: "success",
                        title: response.data.message,
                        showConfirmButton: false,
                    });
                });
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}
const search = ref(props.filtros.texto);
const pendiente = ref(props.filtros.pendiente == "true" ? true : false);
const proceso = ref(props.filtros.proceso == "true" ? true : false);
const concluido = ref(props.filtros.concluido == "true" ? true : false);
const buscar = () => {
    // let val_pendiente = pendiente._value ? "si" : "no";
    // let val_proceso = proceso._value ? "si" : "no";
    // let val_concluido = concluido._value ? "si" : "no";
    let txtBuscar = search._value;
    router.get(
        route("trabajos.index"),
        {
            texto: txtBuscar,
            pendiente: pendiente._value,
            proceso: proceso._value,
            concluido: concluido._value,
        },
        { preserveState: true }
    );
};

const page = usePage();

const showFlash = ref(false);

const flash = computed(function () {
    return page.props.flash.error;
});

watch(
    page.props,
    function (val) {
        if (val.flash.error) {
            showFlash.value = true;
            Swal.fire({
                title: "Error",
                icon: "error",
                text: val.flash.error,
                confirmButtonText: "Cerrar",
                confirmButtonColor: "#C40F51",
            });
        }
    },
    {
        immediate: true,
        deep: true,
    }
);
</script>
<template>
    <Head title="Trabajos" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Trabajos</h2>
        </template>
        <div class="pb-6 pt-2">
            <div class="mx-auto max-w-8xl sm:px-6 lg:px-8">
                <div
                    v-if="$page.props.flash.msj"
                    class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800"
                    role="alert"
                >
                    <span class="font-medium">
                        {{ $page.props.flash.msj }}
                    </span>
                </div>
                <div
                    v-if="$page.props.flash.error"
                    class="p-4 mb-4 text-sm text-white bg-red-700 rounded-lg dark:bg-red-700 dark:text-white"
                    role="alert"
                >
                    <span class="font-medium">
                        {{ $page.props.flash.error }}
                    </span>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <Link :href="route('trabajos.create')">
                                <BreezeButton
                                    >Agregar Trabajo</BreezeButton
                                ></Link
                            >
                        </div>
                        <div
                            class="relative overflow-x-auto shadow-md sm:rounded-lg"
                        >
                            <div class="sm:grid sm:grid-cols-2">
                                <div
                                    class="w-full sm:mr-auto sm:col-span-1 p-1"
                                >
                                    <pagination :links="trabajos.links" />
                                </div>
                                <div
                                    class="w-full sm:ml-auto sm:col-span-1 p-1"
                                >
                                    <input
                                        type="text"
                                        v-model="search"
                                        name="buscar"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full"
                                        placeholder="Buscar"
                                        @keyup="buscar()"
                                    />
                                </div>
                                <div class="sm:col-span-2 p-1 m-1 text-center">
                                    <div class="block">
                                        <label>
                                            Pago pendiente
                                            <input
                                                type="checkbox"
                                                v-model="pendiente"
                                                @change="buscar()"
                                            />
                                        </label>
                                        <label>
                                            Trabajo en proceso
                                            <input
                                                type="checkbox"
                                                v-model="proceso"
                                                @change="buscar()"
                                            />
                                        </label>
                                        <label>
                                            Trabajo concluido/enviado
                                            <input
                                                type="checkbox"
                                                v-model="concluido"
                                                @change="buscar()"
                                            />
                                        </label>
                                    </div>
                                </div>
                                <div class="sm:col-span-2 p-1 m-1 text-center">
                                    Total registros encontrados:
                                    <span v-text="trabajos.total"></span>
                                </div>
                            </div>
                            <table
                                class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 hidden sm:table-header-group"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Proyecto
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Cliente
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Costo
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Cancelado
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Saldo
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Estados
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Descripción
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Fechas
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3"
                                            width="80px"
                                        ></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            trabajo, index
                                        ) in trabajos.data"
                                        :key="trabajo.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-white"
                                    >
                                        <td
                                            data-col="Proyecto"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <strong>{{
                                                trabajo.proyecto.alias
                                            }}</strong>
                                            - {{ trabajo.proyecto.nombre }}
                                        </td>
                                        <td
                                            data-col="Cliente"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ trabajo.cliente.nombre }}
                                        </td>
                                        <td
                                            data-col="Costo"
                                            class="text-lg px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <div
                                                class="font-bold mb-2 bg-white p-1 rounded-lg font-bold text-black text-center"
                                            >
                                                {{ trabajo.moneda.nombre }}
                                                {{ trabajo.costo }}
                                            </div>
                                            <template
                                                v-if="
                                                    trabajo.tipo_cambio_id != 0
                                                "
                                            >
                                                <div
                                                    class="font-bold mt-1 text-md block text-center"
                                                >
                                                    <span>{{
                                                        trabajo.moneda_cambio
                                                            .nombre
                                                    }}</span>
                                                    {{ trabajo.costo_cambio }}
                                                </div>
                                            </template>
                                        </td>
                                        <td
                                            data-col="Cancelado"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell text-center"
                                        >
                                            <span
                                                class="bg-white p-1 rounded-lg font-bold text-sm text-black"
                                            >
                                                {{ trabajo.moneda.nombre }}
                                                {{ trabajo.cancelado }}
                                            </span>
                                            <template
                                                v-if="
                                                    trabajo.tipo_cambio_id != 0
                                                "
                                            >
                                                <span
                                                    class="font-bold mt-1 text-sm block"
                                                >
                                                    <span>{{
                                                        trabajo.moneda_cambio
                                                            .nombre
                                                    }}</span>
                                                    {{
                                                        trabajo.cancelado_cambio
                                                    }}</span
                                                >
                                            </template>

                                            <div
                                                class="w-full bg-gray-300 h-2 relative mt-1"
                                            >
                                                <div
                                                    class="progreso absolute h-2 top-0 left-0 bg-green-500"
                                                    :style="{
                                                        width:
                                                            trabajo.porcentaje_cancelado +
                                                            '%',
                                                    }"
                                                ></div>
                                            </div>
                                            <span class="text-xs text-gray-100"
                                                >{{
                                                    trabajo.porcentaje_cancelado
                                                }}
                                                %</span
                                            >
                                        </td>
                                        <td
                                            data-col="Saldo"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell text-center"
                                        >
                                            <div
                                                class="bg-white p-1 rounded-lg font-bold text-sm"
                                                :class="[
                                                    trabajo.saldo > 0
                                                        ? 'bg-red-700 text-white'
                                                        : 'bg-green-500 text-white',
                                                ]"
                                            >
                                                {{ trabajo.moneda.nombre }}
                                                {{ trabajo.saldo }}
                                            </div>
                                            <template
                                                v-if="
                                                    trabajo.tipo_cambio_id != 0
                                                "
                                            >
                                                <span
                                                    class="mb-1 font-bold text-base"
                                                    :class="[
                                                        trabajo.saldo_cambio > 0
                                                            ? 'text-red-500'
                                                            : 'text-green-500',
                                                    ]"
                                                >
                                                    <span
                                                        v-if="
                                                            trabajo.tipo_cambio
                                                                .moneda1_id !=
                                                            trabajo.moneda_id
                                                        "
                                                        >{{
                                                            trabajo.tipo_cambio
                                                                .moneda_1.nombre
                                                        }}</span
                                                    >
                                                    <span v-else>{{
                                                        trabajo.tipo_cambio
                                                            .moneda_2.nombre
                                                    }}</span>
                                                    {{
                                                        trabajo.saldo_cambio
                                                    }}</span
                                                >
                                            </template>
                                        </td>
                                        <td
                                            data-col="Estados"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <div
                                                class="bg-white w-full font-bold text-xs rounded-lg text-center break-words text-black mb-1"
                                                :class="[
                                                    trabajo.estado_pago ==
                                                        'PENDIENTE' &&
                                                    trabajo.estado_trabajo ==
                                                        'EN PROCESO'
                                                        ? 'bg-blue-400'
                                                        : '',
                                                    trabajo.estado_pago ==
                                                        'PENDIENTE' &&
                                                    (trabajo.estado_trabajo ==
                                                        'CONCLUIDO' ||
                                                        trabajo.estado_trabajo ==
                                                            'ENVIADO')
                                                        ? 'bg-red-400'
                                                        : '',
                                                    trabajo.estado_pago ==
                                                    'COMPLETO'
                                                        ? 'bg-green-400'
                                                        : '',
                                                ]"
                                            >
                                                <div
                                                    class="border-b-2 text-center p-1"
                                                >
                                                    <font-awesome-icon
                                                        icon="money-bill"
                                                    />
                                                    PAGO
                                                </div>
                                                <span
                                                    class="font-black text-green-100"
                                                >
                                                    {{
                                                        trabajo.estado_pago
                                                    }}</span
                                                >
                                            </div>

                                            <div
                                                class="bg-white w-full font-bold text-xs rounded-lg text-center break-words text-black"
                                                :class="[
                                                    trabajo.estado_trabajo ==
                                                    'EN PROCESO'
                                                        ? 'bg-yellow-500'
                                                        : '',
                                                    trabajo.estado_trabajo ==
                                                    'CONCLUIDO'
                                                        ? 'bg-green-400'
                                                        : '',
                                                    trabajo.estado_trabajo ==
                                                    'ENVIADO'
                                                        ? 'bg-blue-400'
                                                        : '',
                                                    trabajo.estado_trabajo ==
                                                    'PENDIENTE'
                                                        ? 'bg-red-400'
                                                        : '',
                                                ]"
                                            >
                                                <div
                                                    class="border-b-2 text-center p-1"
                                                >
                                                    <font-awesome-icon
                                                        icon="wrench"
                                                    />
                                                    TRABAJO
                                                </div>
                                                <span
                                                    class="font-black text-white"
                                                >
                                                    <font-awesome-icon
                                                        :icon="
                                                            trabajo.estado_trabajo ==
                                                            'EN PROCESO'
                                                                ? 'edit'
                                                                : trabajo.estado_trabajo ==
                                                                  'ENVIADO'
                                                                ? 'paper-plane'
                                                                : trabajo.estado_trabajo ==
                                                                  'CONCLUIDO'
                                                                ? 'check'
                                                                : ''
                                                        "
                                                    />
                                                    {{
                                                        trabajo.estado_trabajo
                                                    }}</span
                                                >
                                            </div>
                                        </td>
                                        <td
                                            data-col="Descripción"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <span
                                                v-html="trabajo.descripcion"
                                            ></span>
                                        </td>
                                        <td
                                            data-col="Fechas"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <strong>Inicio: </strong
                                            >{{ trabajo.fecha_inicio }}<br />
                                            <strong>Fecha Entrega: </strong
                                            >{{ trabajo.fecha_entrega }}<br />
                                            <template
                                                v-if="
                                                    trabajo.estado_trabajo ==
                                                        'ENVIADO' ||
                                                    trabajo.estado_trabajo ==
                                                        'CONCLUIDO'
                                                "
                                            >
                                                <strong>Enviado el: </strong
                                                >{{ trabajo.fecha_envio }}<br />
                                                <template
                                                    v-if="
                                                        trabajo.estado_trabajo ==
                                                        'CONCLUIDO'
                                                    "
                                                    ><strong
                                                        >Concluido el: </strong
                                                    >{{
                                                        trabajo.fecha_conclusion
                                                    }}</template
                                                >
                                            </template>
                                        </td>
                                        <td
                                            data-col="Acción"
                                            class="text-xs px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-black before:font-bold before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <template
                                                v-if="
                                                    trabajo.cantidad_pagos > 0
                                                "
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'trabajos.lista_pagos',
                                                            trabajo.id
                                                        )
                                                    "
                                                    class="px-4 py-2 text-white bg-cyan-500 rounded-lg inline-block hover:bg-cyan-600"
                                                    title="Lista de pagos"
                                                >
                                                    <font-awesome-icon
                                                        icon="list"
                                                    /> </Link
                                                ><br />
                                            </template>

                                            <Link
                                                :href="
                                                    route(
                                                        'trabajos.edit',
                                                        trabajo.id
                                                    )
                                                "
                                                class="px-4 py-2 mt-1 text-white bg-yellow-500 rounded-lg inline-block hover:bg-yellow-600"
                                                title="Editar"
                                            >
                                                <font-awesome-icon
                                                    icon="edit"
                                                /> </Link
                                            ><br />
                                            <BreezeButton
                                                v-if="
                                                    trabajo.estado_trabajo ==
                                                    'EN PROCESO'
                                                "
                                                class="bg-blue-600 inline-block mt-2 hover:bg-blue-500"
                                                @click="
                                                    marca_enviado(
                                                        trabajo.id,
                                                        index
                                                    )
                                                "
                                                title="Marcar envíado"
                                            >
                                                <font-awesome-icon
                                                    icon="paper-plane"
                                                />
                                            </BreezeButton>
                                            <BreezeButton
                                                v-if="
                                                    trabajo.estado_trabajo !=
                                                    'CONCLUIDO'
                                                "
                                                class="bg-green-600 inline-block mt-2 hover:bg-green-500"
                                                @click="
                                                    marca_concluido(
                                                        trabajo.id,
                                                        index
                                                    )
                                                "
                                                title="Marcar concluído"
                                            >
                                                <font-awesome-icon
                                                    icon="check"
                                                />
                                            </BreezeButton>
                                            <BreezeButton
                                                v-if="
                                                    trabajo.estado_trabajo !=
                                                    'CONCLUIDO'
                                                "
                                                class="bg-red-700 inline-block mt-2 hover:bg-red-800"
                                                @click="destroy(trabajo.id)"
                                                title="Eliminar"
                                            >
                                                <font-awesome-icon
                                                    icon="trash"
                                                />
                                            </BreezeButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="sm:flex">
                                <div class="w-full sm:mr-auto sm:w-1/2 p-1">
                                    <pagination :links="trabajos.links" />
                                </div>
                                <div class="w-full sm:ml-auto sm:w-1/2 p-1">
                                    <input
                                        type="text"
                                        v-model="search"
                                        name="buscar"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full"
                                        placeholder="Buscar"
                                        @keyup="buscar()"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
