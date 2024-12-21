<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import pagination from "@/Components/Pagination.vue";
import { inject, ref } from "vue";
const props = defineProps({
    pagos: {
        type: Object,
        default: () => ({}),
    },
    filtros: {
        type: Object,
        default: () => ({}),
    },
});
const form = useForm();
const Swal = inject("$swal");

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
            form.delete(route("pagos.destroy", id));
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}

const search = ref(props.filtros.texto);
const buscar = (e) => {
    router.get(
        route("pagos.index"),
        { texto: e.target.value },
        { preserveState: true }
    );
};
</script>
<template>
    <Head title="Pagos" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Pagos</h2>
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
                            <Link :href="route('pagos.create')">
                                <BreezeButton>Agregar Pago</BreezeButton></Link
                            >
                        </div>
                        <div
                            class="relative overflow-x-auto shadow-md sm:rounded-lg"
                        >
                            <div class="sm:flex">
                                <div class="w-full sm:mr-auto sm:w-1/2 p-1">
                                    <pagination :links="pagos.links" />
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
                                class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 hidden sm:table-header-group"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Proyecto
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Descripción Trabajo
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Cliente
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Monto Cancelado
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Descripción
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Fecha del pago
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="pago in pagos.data"
                                        :key="pago.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                    >
                                        <td
                                            data-col="Proyecto"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <span class="font-bold">{{
                                                pago.trabajo.proyecto.alias
                                            }}</span>
                                            - {{ pago.trabajo.proyecto.nombre }}
                                        </td>
                                        <td
                                            data-col="Descripción Trabajo"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <span
                                                v-html="
                                                    pago.trabajo.descripcion
                                                "
                                            ></span>
                                        </td>
                                        <td
                                            data-col="Cliente"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ pago.cliente.nombre }}
                                        </td>
                                        <td
                                            data-col="Monto"
                                            class="text-center px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <span
                                                class="bg-white p-1 rounded-lg font-bold text-sm text-black"
                                            >
                                                {{ pago.moneda.nombre }}
                                                {{ pago.monto }}
                                            </span>
                                            <template
                                                v-if="
                                                    pago.moneda_cambio_id != 0
                                                "
                                            >
                                                <span
                                                    class="font-bold mt-1 text-sm block"
                                                >
                                                    <span>{{
                                                        pago.moneda_cambio
                                                            ?.nombre
                                                    }}</span>
                                                    {{
                                                        pago.monto_cambio
                                                    }}</span
                                                >
                                            </template>
                                        </td>
                                        <td
                                            data-col="Descripción"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ pago.descripcion }}
                                        </td>
                                        <td
                                            data-col="Fecha"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ pago.fecha_pago }}
                                        </td>
                                        <td
                                            data-col="Acción"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:block before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <Link
                                                :href="
                                                    route('pagos.show', pago.id)
                                                "
                                                class="px-4 py-2 text-white bg-blue-600 rounded-lg inline-block hover:bg-blue-700"
                                                title="Ver pago"
                                            >
                                                <font-awesome-icon icon="eye" />
                                            </Link>
                                            <Link
                                                :href="
                                                    route('pagos.edit', pago.id)
                                                "
                                                class="px-4 py-2 text-white bg-yellow-600 rounded-lg inline-block mt-2 hover:bg-yellow-500"
                                            >
                                                <font-awesome-icon
                                                    icon="edit"
                                                />
                                            </Link>
                                            <BreezeButton
                                                class="bg-red-700 inline-block mt-2 hover:bg-red-800"
                                                @click="destroy(pago.id)"
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
