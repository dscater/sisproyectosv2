<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
const props = defineProps({
    trabajo: {
        type: Object,
        default: () => ({}),
    },
    proyectos: {
        type: Object,
        default: () => ({}),
    },
    clientes: {
        type: Object,
        default: () => ({}),
    },
    monedas: {
        type: Object,
        default: () => ({}),
    },
    tipo_cambios: {
        type: Array,
        default: [],
    },
});

const listEstadoTrabajo = ref(["EN PROCESO", "ENVIADO", "CONCLUIDO"]);
const listEstadoPagos = ref(["PENDIENTE", "COMPLETO"]);
const calculaSaldo = () => {
    form.saldo = parseFloat(form.costo_original) - parseFloat(form.cancelado);
    if (form.saldo == 0) {
        form.estado_pago = "COMPLETO";
    } else {
        form.estado_pago = "PENDIENTE";
    }
};

const calculaDiaEntrega = () => {
    if (form.fecha_inicio != "" && form.disabled != "") {
        var d = new Date(form.fecha_inicio + "T00:00:00");
        let fecha_entrega = sumarDias(d, parseInt(form.dias_plazo));
        let nuev_fecha = fecha_entrega.getFullYear() + "-";
        let mes = parseInt(fecha_entrega.getMonth()) + 1;
        nuev_fecha += (mes < 10 ? "0" + mes : mes) + "-";
        nuev_fecha +=
            fecha_entrega.getDate() < 10
                ? "0" + fecha_entrega.getDate()
                : fecha_entrega.getDate();
        form.fecha_entrega = nuev_fecha; //asignando la fecha automaticamente
    }
};

function sumarDias(fecha, dias) {
    fecha.setDate(fecha.getDate() + dias);
    return fecha;
}

const descripcion_reemplazo = props.trabajo.descripcion.replace(
    /<br\s*\/?>/g,
    "\n"
);
const descripcion_reemplazo_limpio = descripcion_reemplazo.replace(
    /\n\s*\n/g,
    "\n"
);

const form = useForm({
    id: props.trabajo.id,
    proyecto_id: props.trabajo.proyecto_id,
    cliente_id: props.trabajo.cliente_id,
    costo_original: props.trabajo.costo_original,
    moneda_seleccionada_id: props.trabajo.moneda_seleccionada_id,
    tipo_cambio_id: props.trabajo.tipo_cambio_id,
    cancelado: props.trabajo.cancelado,
    saldo: props.trabajo.saldo,
    estado_pago: props.trabajo.estado_pago,
    descripcion: descripcion_reemplazo_limpio,
    fecha_inicio: props.trabajo.fecha_inicio,
    dias_plazo: props.trabajo.dias_plazo,
    fecha_entrega: props.trabajo.fecha_entrega,
    estado_trabajo: props.trabajo.estado_trabajo,
    fecha_envio: props.trabajo.fecha_envio,
    fecha_conclusion: props.trabajo.fecha_conclusion,
});

const autoResize = (event) => {
    const textarea = event.target;
    textarea.style.height = "auto";
    textarea.style.height = (textarea.scrollHeight + 3) + "px";
};

onMounted(() => {
    const textareas = document.querySelectorAll("textarea");
    textareas.forEach((textarea) => {
        autoResize({ target: textarea });
    });
});

const submit = () => {
    form.put(route("trabajos.update", props.trabajo.id));
};
</script>
<template>
    <Head title="Trabajo Edit" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Editar Trabajo</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    for="Title"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Seleccionar Proyecto*</label
                                >
                                <select
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    name="proyeto_id"
                                    v-model="form.proyecto_id"
                                >
                                    <option
                                        v-for="item in proyectos"
                                        :key="item.id"
                                        :value="item.id"
                                    >
                                        {{ `${item.alias} - ${item.nombre}` }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.proyecto_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.proyecto_id }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Cliente*</label
                                >
                                <select
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    name="cliente_id"
                                    v-model="form.cliente_id"
                                >
                                    <option
                                        v-for="item in clientes"
                                        :key="item.id"
                                        :value="item.id"
                                        v-text="item.nombre"
                                    ></option>
                                </select>
                                <div
                                    v-if="form.errors.cliente_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.cliente_id }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Costo*</label
                                >
                                <input
                                    type="number"
                                    min="1"
                                    v-model="form.costo_original"
                                    name="costo_original"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    step="0.01"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.costo_original"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.costo_original }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Moneda*</label
                                >
                                <select
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    name="moneda_seleccionada_id"
                                    v-model="form.moneda_seleccionada_id"
                                >
                                    <option
                                        v-for="item in monedas"
                                        :key="item.id"
                                        :value="item.id"
                                        v-text="item.nombre"
                                    ></option>
                                </select>
                                <div
                                    v-if="form.errors.moneda_seleccionada_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.moneda_seleccionada_id }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Tipo de Cambio*</label
                                >
                                <select
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    name="tipo_cambio_id"
                                    v-model="form.tipo_cambio_id"
                                >
                                    <option value="0">Ninguno</option>
                                    <option
                                        v-for="item in tipo_cambios"
                                        :key="item.id"
                                        :value="item.id"
                                        v-text="
                                            item.moneda_1.nombre +
                                            ' ' +
                                            item.valor1 +
                                            ' = ' +
                                            item.moneda_2.nombre +
                                            ' ' +
                                            item.valor2
                                        "
                                    ></option>
                                </select>
                                <div
                                    v-if="form.errors.tipo_cambio_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.tipo_cambio_id }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Estado del pago*</label
                                >
                                <select
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    name="estado_pago"
                                    v-model="form.estado_pago"
                                >
                                    <option
                                        v-for="item in listEstadoPagos"
                                        :key="item"
                                        :value="item"
                                        v-text="item"
                                    ></option>
                                </select>
                                <div
                                    v-if="form.errors.estado_pago"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.estado_pago }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Fecha de recepción*</label
                                >
                                <input
                                    type="date"
                                    v-model="form.fecha_inicio"
                                    name="fecha_inicio"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.fecha_inicio"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.fecha_inicio }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Días de plazo a entregar*</label
                                >
                                <input
                                    type="number"
                                    min="0"
                                    v-model="form.dias_plazo"
                                    name="dias_plazo"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                    @keyup="calculaDiaEntrega"
                                />
                                <div
                                    v-if="form.errors.dias_plazo"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.dias_plazo }}
                                </div>
                            </div>

                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Fecha de entrega*</label
                                >
                                <input
                                    type="date"
                                    v-model="form.fecha_entrega"
                                    name="fecha_entrega"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                    readonly
                                />
                                <div
                                    v-if="form.errors.fecha_entrega"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.fecha_entrega }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Estado del trabajo*</label
                                >
                                <select
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    name="estado_trabajo"
                                    v-model="form.estado_trabajo"
                                >
                                    <option
                                        v-for="item in listEstadoTrabajo"
                                        :key="item"
                                        :value="item"
                                        v-text="item"
                                    ></option>
                                </select>
                                <div
                                    v-if="form.errors.estado_trabajo"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.estado_trabajo }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Fecha de envio</label
                                >
                                <input
                                    type="date"
                                    v-model="form.fecha_envio"
                                    name="fecha_envio"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.fecha_envio"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.fecha_envio }}
                                </div>
                            </div>
                            <div class="w-full sm:w-2/4 inline-block p-2">
                                <label
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Fecha de conclusión</label
                                >
                                <input
                                    type="date"
                                    v-model="form.fecha_conclusion"
                                    name="fecha_conclusion"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.fecha_conclusion"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.fecha_conclusion }}
                                </div>
                            </div>
                            <div class="w-full inline-block p-2">
                                <label
                                    for="review"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Descripción*</label
                                >
                                <textarea
                                    type="text"
                                    v-model="form.descripcion"
                                    name="descripcion"
                                    id=""
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                ></textarea>
                                <div
                                    v-if="form.errors.descripcion"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.descripcion }}
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
                                    :href="route('trabajos.index')"
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
