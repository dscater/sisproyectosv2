<script setup>
import Content from "@/Components/Content.vue";
import { Head } from "@inertiajs/vue3";
import Pago from "@/Pages/Admin/Pagos/Pago.vue";
const props = defineProps({
    trabajo: {
        type: Object,
        default: () => ({}),
    },
    pagos: {
        type: Object,
        default: () => ({}),
    },
});
</script>
<template>
    <Head title="Trabajos - Pagos" />
    <Content>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">
                Listado de pagos
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="p-6 bg-gray-700 text-white border-b border-gray-200"
                    >
                        <h3 class="font-bold text-center text-2xl">
                            "{{ trabajo.proyecto.nombre }} ({{
                                trabajo.proyecto.alias
                            }})"
                        </h3>
                        <table
                            class="border-collapse rounded overflow-hidden mb-3"
                        >
                            <tbody>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Descripción
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ trabajo.descripcion }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Pagos encontrados:
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ trabajo.cantidad_pagos }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Estado del pago:
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ trabajo.estado_pago }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Costo:
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ trabajo.moneda_seleccionada.nombre }}
                                        {{ trabajo.costo_original }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Tipo de cambio:
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        <span
                                            v-if="trabajo.tipo_cambio_id != 0"
                                            >{{
                                                trabajo.tipo_cambio.moneda_1
                                                    .nombre +
                                                " " +
                                                trabajo.tipo_cambio.valor1 +
                                                " = " +
                                                trabajo.tipo_cambio.moneda_2
                                                    .nombre +
                                                " " +
                                                trabajo.tipo_cambio.valor2
                                            }}</span
                                        >
                                        <span v-else>Ninguno</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Cancelado:
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ trabajo.moneda.nombre }}
                                        {{ trabajo.cancelado }}
                                        <template
                                            v-if="trabajo.tipo_cambio_id != 0"
                                        >
                                            &nbsp;&nbsp; | &nbsp;&nbsp;{{
                                                trabajo.moneda_cambio.nombre
                                            }}
                                            {{ trabajo.cancelado_cambio }}
                                        </template>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        Saldo:
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ trabajo.moneda.nombre }}
                                        {{ trabajo.saldo }}
                                        <template
                                            v-if="trabajo.tipo_cambio_id != 0"
                                        >
                                            &nbsp;&nbsp; | &nbsp;&nbsp;{{
                                                trabajo.moneda_cambio.nombre
                                            }}
                                            {{ trabajo.saldo_cambio }}
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pago
                            v-for="(item, index) in pagos"
                            :nro="index + 1"
                            :key="item.id"
                            :pago="item"
                            :muestra_desc="false"
                            :trabajo="trabajo"
                        ></Pago>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
