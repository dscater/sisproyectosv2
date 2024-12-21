<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import fTrabajos from "@/Functions/fTrabajos";
import { onMounted, computed, watch } from "vue";
const props = defineProps({
    pago: {
        type: Object,
        default: () => ({}),
    },
    trabajos: {
        type: Object,
        default: () => ({}),
    },
    moneda_principal: {
        type: Object,
        default: () => ({}),
    },
});

const {
    errors,
    listTrabajos,
    oTrabajo,
    getTrabajo,
    getTrabajos,
    cancelado_aux,
    saldo_aux,
    total_pagos,
    monedas,
    getMontoCambio,
} = fTrabajos();

const form = useForm({
    id: props.pago.id,
    trabajo_id: props.pago.trabajo_id,
    cliente_id: props.pago.cliente_id,
    monto: props.pago.monto,
    monto_cambio: props.pago.monto_cambio,
    moneda_id: props.pago.moneda_id,
    fecha_pago: props.pago.fecha_pago,
    descripcion: props.pago.descripcion,
    foto_comprobante: null,
    archivo_comprobante: null,
    descripcion_archivo: props.pago.descripcion_archivo,
    _method: "put",
});

const submit = () => {
    console.log(form.data());
    form.post(route("pagos.update", props.pago.id));
};

const calcularMontos = (nro_input) => {
    let monto = form.monto;
    let monto_cambio = form.monto_cambio;
    if (monto == "") {
        monto = 0;
    } else {
        monto = parseFloat(monto);
    }
    if (monto_cambio == "") {
        monto_cambio = 0;
    } else {
        monto_cambio = parseFloat(monto_cambio);
    }
    let cancelado = 0;
    let cancelado_cambio = 0;
    let tipo_cambio = oTrabajo.value.tipo_cambio;

    if (nro_input == 1) {
        cancelado =
            parseFloat(total_pagos.value["suma_pagos"]) + parseFloat(monto);
        // obtener montos de cambio
        monto_cambio = getMontoCambio(
            tipo_cambio,
            form.moneda_id,
            parseFloat(monto)
        );
        form.monto_cambio = monto_cambio;
        cancelado_cambio =
            parseFloat(total_pagos.value["suma_pagos_cambio"]) +
            parseFloat(monto_cambio);
    } else {
        // convertir a moneda principal
        cancelado_cambio =
            parseFloat(total_pagos.value["suma_pagos_cambio"]) +
            parseFloat(monto_cambio);
        monto = getMontoCambio(
            tipo_cambio,
            oTrabajo.value.tipo_cambio.moneda2_id,
            parseFloat(monto_cambio)
        );
        form.monto = monto;
        cancelado =
            parseFloat(total_pagos.value["suma_pagos"]) + parseFloat(monto);
    }

    // asignar nuevos valores para mostrar
    oTrabajo.value.cancelado = parseFloat(cancelado).toFixed(2);
    oTrabajo.value.saldo = (
        parseFloat(oTrabajo.value.costo) - cancelado
    ).toFixed(2);

    oTrabajo.value.cancelado_cambio = parseFloat(cancelado_cambio).toFixed(2);
    oTrabajo.value.saldo_cambio = (
        parseFloat(oTrabajo.value.costo_cambio) - cancelado_cambio
    ).toFixed(2);
};

const total_computado = computed(() => {
    return oTrabajo.value.cancelado;
});

const total_computado_cambio = computed(() => {
    return oTrabajo.value.cancelado_cambio;
});

onMounted(getTrabajo(form.trabajo_id, true, props.pago.id));

function cargaArchivo(e, key) {
    form[key] = null;
    form[key] = e.target.files[0];
}
</script>
<template>
    <Head title="Editar Pago" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Editar Pago</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div
                        class="p-6 bg-white border-b bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                    >
                        <form
                            @submit.prevent="submit"
                            enctype="multipart/form-data"
                        >
                            <div class="w-full inline-block p-2">
                                <label
                                    for="Title"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Seleccionar Trabajo*</label
                                >
                                <select
                                    name="trabajo_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    v-model="form.trabajo_id"
                                    @change="
                                        getTrabajo(
                                            form.trabajo_id,
                                            true,
                                            props.pago.id
                                        )
                                    "
                                >
                                    <option
                                        v-for="item in trabajos"
                                        :value="item.id"
                                        :key="item.id"
                                        v-text="
                                            item.proyecto.alias +
                                            ' - ' +
                                            item.proyecto.nombre
                                        "
                                    ></option>
                                </select>
                                <div
                                    v-if="form.errors.trabajo_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.trabajo_id }}
                                </div>
                            </div>
                            <div
                                class="w-full border m-2 p-2 rounded-lg"
                                v-if="oTrabajo.id != ''"
                            >
                                <p>
                                    <span class="font-bold">Cliente: </span>
                                    <span class="text-white">{{
                                        oTrabajo.cliente.nombre
                                    }}</span>
                                </p>
                                <p>
                                    <span class="font-bold"
                                        >Detalle del trabajo: </span
                                    ><br />
                                    <span
                                        class="text-white"
                                        v-html="oTrabajo.descripcion"
                                    ></span>
                                </p>
                                <p class="pt-3 pb-2">
                                    <span class="font-bold">Costo: </span>
                                    <span
                                        class="border p-1 rounded-lg bg-green-500 text-white"
                                        >{{
                                            parseFloat(oTrabajo.costo).toFixed(
                                                2
                                            )
                                        }}
                                        {{ oTrabajo.moneda.nombre }}</span
                                    >
                                    <span
                                        v-if="oTrabajo.tipo_cambio_id != 0"
                                        class="border p-1 rounded-lg bg-blue-500 text-white ml-2"
                                        >{{
                                            parseFloat(
                                                oTrabajo.costo_cambio
                                            ).toFixed(2)
                                        }}
                                        <span
                                            v-if="
                                                oTrabajo.tipo_cambio
                                                    .moneda1_id !=
                                                oTrabajo.moneda_id
                                            "
                                            >{{
                                                oTrabajo.tipo_cambio.moneda_1
                                                    .nombre
                                            }}</span
                                        >
                                        <span v-else>{{
                                            oTrabajo.tipo_cambio.moneda_2.nombre
                                        }}</span></span
                                    >
                                </p>
                                <p class="pt-3 pb-2">
                                    <span class="font-bold">Cancelado: </span>
                                    <span
                                        class="border p-1 rounded-lg bg-green-500 text-white"
                                        :class="{
                                            'bg-red-500':
                                                total_pagos.value == 0,
                                            'bg-green-500':
                                                total_pagos.value > 0,
                                        }"
                                        >{{ total_computado }}
                                        {{ oTrabajo.moneda.nombre }}</span
                                    >
                                    <span
                                        v-if="oTrabajo.tipo_cambio_id != 0"
                                        class="border p-1 rounded-lg bg-blue-500 text-white ml-2"
                                        :class="{
                                            'bg-red-500':
                                                total_pagos.value == 0,
                                            'bg-blue-500':
                                                total_pagos.value > 0,
                                        }"
                                        >{{ total_computado_cambio }}
                                        <span
                                            v-if="
                                                oTrabajo.tipo_cambio
                                                    .moneda1_id !=
                                                oTrabajo.moneda_id
                                            "
                                            >{{
                                                oTrabajo.tipo_cambio.moneda_1
                                                    .nombre
                                            }}</span
                                        >
                                        <span v-else>{{
                                            oTrabajo.tipo_cambio.moneda_2.nombre
                                        }}</span></span
                                    >
                                </p>
                                <p class="pt-3 pb-2">
                                    <span class="font-bold">Saldo: </span>
                                    <span
                                        class="border p-1 rounded-lg text-white font-bold text-lg"
                                        :class="{
                                            'bg-red-500': oTrabajo.saldo > 0,
                                            'bg-green-500': oTrabajo.saldo == 0,
                                        }"
                                        >{{ oTrabajo.saldo }}
                                        {{ oTrabajo.moneda.nombre }}</span
                                    >
                                    <span
                                        v-if="oTrabajo.tipo_cambio_id != 0"
                                        class="border p-1 rounded-lg text-white font-bold text-lg ml-2"
                                        :class="{
                                            'bg-red-500': oTrabajo.saldo > 0,
                                            'bg-blue-500': oTrabajo.saldo == 0,
                                        }"
                                        >{{ oTrabajo.saldo_cambio }}
                                        <span
                                            v-if="
                                                oTrabajo.tipo_cambio
                                                    .moneda1_id !=
                                                oTrabajo.moneda_id
                                            "
                                            >{{
                                                oTrabajo.tipo_cambio.moneda_1
                                                    .nombre
                                            }}</span
                                        >
                                        <span v-else>{{
                                            oTrabajo.tipo_cambio.moneda_2.nombre
                                        }}</span></span
                                    >
                                </p>
                            </div>
                            <div class="w-full sm:w-1/3 inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Monto a cancelar
                                    <span
                                        v-if="
                                            oTrabajo.tipo_cambio_id != 0 &&
                                            oTrabajo.tipo_cambio
                                        "
                                        >{{
                                            oTrabajo.tipo_cambio.moneda_1.nombre
                                        }}</span
                                    >
                                    <span v-else>{{
                                        oTrabajo.moneda.nombre
                                    }}</span
                                    >*</label
                                >
                                <input
                                    type="number"
                                    v-model="form.monto"
                                    name="monto"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Ingresar monto"
                                    step="0.01"
                                    @keyup="calcularMontos(1)"
                                    @change="calcularMontos(1)"
                                />
                                <div
                                    v-if="form.errors.monto"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.monto }}
                                </div>
                            </div>
                            <div
                                class="w-full sm:w-1/3 inline-block p-2"
                                v-if="oTrabajo.tipo_cambio_id != 0"
                            >
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Monto a cancelar
                                    <span
                                        v-if="
                                            oTrabajo.tipo_cambio_id != 0 &&
                                            oTrabajo.tipo_cambio
                                        "
                                        >{{
                                            oTrabajo.tipo_cambio.moneda_2.nombre
                                        }}</span
                                    >*</label
                                >
                                <input
                                    type="number"
                                    v-model="form.monto_cambio"
                                    name="monto_cambio"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Monto a cancelar"
                                    step="0.01"
                                    @keyup="calcularMontos(2)"
                                    @change="calcularMontos(2)"
                                />
                                <div
                                    v-if="form.errors.monto_cambio"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.monto_cambio }}
                                </div>
                            </div>
                            <div class="w-full sm:w-1/3 inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Fecha del pago*</label
                                >
                                <input
                                    type="date"
                                    v-model="form.fecha_pago"
                                    name="fecha_pago"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.fecha_pago"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.fecha_pago }}
                                </div>
                            </div>
                            <div class="w-full inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Descripción*</label
                                >
                                <textarea
                                    name="descripcion"
                                    id="descripcion"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    v-model="form.descripcion"
                                ></textarea>
                                <div
                                    v-if="form.errors.descripcion"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.descripcion }}
                                </div>
                            </div>
                            <div class="w-full sm:w-1/2 inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Foto Comprobante</label
                                >
                                <input
                                    name="foto_comprobante"
                                    type="file"
                                    ref="file_foto"
                                    @change="
                                        cargaArchivo($event, 'foto_comprobante')
                                    "
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                />
                                <div
                                    v-if="form.errors.foto_comprobante"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.foto_comprobante }}
                                </div>
                            </div>
                            <div class="w-full sm:w-1/2 inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Archivo comprobante</label
                                >
                                <input
                                    name="archivo_comprobante"
                                    type="file"
                                    ref="file_archivo"
                                    @change="
                                        cargaArchivo(
                                            $event,
                                            'archivo_comprobante'
                                        )
                                    "
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                />
                                <div
                                    v-if="form.errors.archivo_comprobante"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.archivo_comprobante }}
                                </div>
                            </div>
                            <div class="w-full inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                    >Descripción archivos</label
                                >
                                <textarea
                                    name="descripcion_archivo"
                                    id="descripcion_archivo"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    v-model="form.descripcion_archivo"
                                ></textarea>
                                <div
                                    v-if="form.errors.descripcion_archivo"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.descripcion_archivo }}
                                </div>
                            </div>
                            <div class="w-full p-2">
                                <button
                                    type="submit"
                                    class="text-white bg-blue-700 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5"
                                    :disabled="form.processing"
                                    :class="{ 'opacity-25': form.processing }"
                                >
                                    Actualizar
                                </button>
                                <a
                                    :href="route('pagos.index')"
                                    type="submit"
                                    class="text-white bg-white border border-gray-300 ml-2 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-black"
                                    :disabled="form.processing"
                                    :class="{ 'opacity-25': form.processing }"
                                >
                                    Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
