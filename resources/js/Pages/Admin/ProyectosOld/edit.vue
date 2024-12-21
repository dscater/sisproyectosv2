<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
const props = defineProps({
    proyecto: {
        type: Object,
        default: () => ({}),
    },
});
const descripcion_reemplazo = props.proyecto.descripcion.replace(
    /<br\s*\/?>/g,
    "\n"
);
const descripcion_reemplazo_limpio = descripcion_reemplazo.replace(
    /\n\s*\n/g,
    "\n"
);
const form = useForm({
    id: props.proyecto.id,
    nombre: props.proyecto.nombre,
    alias: props.proyecto.alias,
    descripcion: descripcion_reemplazo_limpio,
});

const autoResize = (event) => {
    const textarea = event.target;
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + 3 + "px";
};

onMounted(() => {
    const textareas = document.querySelectorAll("textarea");
    textareas.forEach((textarea) => {
        autoResize({ target: textarea });
    });
});

const submit = () => {
    form.put(route("proyectos.update", props.proyecto.id));
};
</script>
<template>
    <Head title="Proyecto Edit" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Editar Proyecto</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="w-2/4 inline-block p-2">
                                <label
                                    for="Title"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Nombre del sistema*</label
                                >
                                <input
                                    type="text"
                                    v-model="form.nombre"
                                    name="nombre"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.nombre"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.nombre }}
                                </div>
                            </div>
                            <div class="w-2/4 inline-block p-2">
                                <label
                                    for="Autor"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500"
                                    >Alias*</label
                                >
                                <input
                                    type="text"
                                    v-model="form.alias"
                                    name="alias"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder=""
                                />
                                <div
                                    v-if="form.errors.alias"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.alias }}
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
                                    :href="route('proyectos.index')"
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
