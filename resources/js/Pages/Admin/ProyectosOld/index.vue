<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { inject, ref } from "vue";
import pagination from "@/Components/Pagination.vue";

const props = defineProps({
    proyectos: {
        type: Object,
        default: () => ({}),
    },
});

const Swal = inject("$swal");

const form = useForm();
function destroy(id) {
    Swal.fire({
        title: "¿Estás seguro de eliminar este registro?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "Si, eliminar",
        cancelButtonText: `No cancelar`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            form.delete(route("proycestos.destroy", id));
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}

const search = ref("");
const buscar = (e) => {
    router.get(
        route("proyectos.index"),
        { texto: e.target.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};
</script>
<template>
    <Head title="Proyectos" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Proyectos</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="$page.props.flash.msj"
                    class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800"
                    role="alert"
                >
                    <span class="font-medium">
                        {{ $page.props.flash.msj }}
                    </span>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-2">
                            <Link :href="route('proyectos.create')">
                                <BreezeButton
                                    >Agregar Proyecto</BreezeButton
                                ></Link
                            >
                        </div>
                        <div
                            class="relative overflow-x-auto shadow-md sm:rounded-lg"
                        >
                            <div class="sm:flex">
                                <div class="w-full sm:mr-auto sm:w-1/2 p-1">
                                    <pagination :links="proyectos.links" />
                                </div>
                                <div class="w-full sm:ml-auto sm:w-1/2 p-1">
                                    <input
                                        type="text"
                                        v-model="search"
                                        name="buscar"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full"
                                        placeholder="Buscar"
                                        @keyup="buscar($event)"
                                    />
                                </div>
                            </div>
                            <table
                                class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 hidden sm:table-header-group"
                                >
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-3"
                                            width="25px"
                                        >
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Nombre
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Alias
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Descripción
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3"
                                            width="170px"
                                        >
                                            Fecha de Registro
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3"
                                            width="90px"
                                        >
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="proyecto in proyectos.data"
                                        :key="proyecto.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                    >
                                        <td
                                            data-col="#: "
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ proyecto.id }}
                                        </td>
                                        <td
                                            data-col="Nombre"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ proyecto.nombre }}
                                        </td>
                                        <td
                                            data-col="Alias"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ proyecto.alias }}
                                        </td>
                                        <td
                                            data-col="Descripción"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <span
                                                v-html="proyecto.descripcion"
                                            ></span>
                                        </td>
                                        <td
                                            data-col="Fecha de registro"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ proyecto.fecha_registro }}
                                        </td>
                                        <td
                                            data-col="Acción"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'proyectos.edit',
                                                        proyecto.id
                                                    )
                                                "
                                                class="px-4 py-2 text-white bg-blue-600 rounded-lg inline-block hover:bg-blue-700"
                                            >
                                                <font-awesome-icon icon="edit"
                                            /></Link>
                                            <BreezeButton
                                                class="bg-red-700 inline-block mt-2 hover:bg-red-800"
                                                @click="destroy(proyecto.id)"
                                            >
                                                <font-awesome-icon
                                                    icon="trash"
                                                />
                                            </BreezeButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
