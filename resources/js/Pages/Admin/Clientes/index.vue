<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import BreezeButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { reactive, ref, onMounted, inject, watch, computed } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
const props = defineProps({
    clientes: {
        type: Object,
        default: () => ({}),
    },
});
const Swal = inject("$swal");
const modal = ref(false);

const limpiaFormulario = () => {
    form.id = 0;
    form.nombre = "";
};
const enviarFormulario = async () => {
    await addCliente({ ...form });
    limpiaFormulario();
    modal.value = false;
};

const form = useForm();
function destroy(id) {
    Swal.fire({
        title: "¿Estás seguro de eliminar este registro?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "Si, eliminar",
        cancelButtonText: `No cancelar`,
        confirmButtonColor: "#C40F51",
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            form.delete(route("clientes.destroy", id));
        } else if (result.isDenied) {
            Swal.fire("Eliminación cancelada", "", "info");
        }
    });
}

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
    <Head title="Clientes" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">
                Clientes
            </h2>
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
                            <Link :href="route('clientes.create')">
                                <BreezeButton
                                    >Agregar Cliente</BreezeButton
                                ></Link
                            >
                        </div>
                        <div
                            class="relative overflow-x-auto shadow-md sm:rounded-lg"
                        >
                            <table
                                class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 hidden sm:table-header-group"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-3">#</th>
                                        <th scope="col" class="px-6 py-3">
                                            Nombre
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="cliente in clientes"
                                        :key="cliente.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                    >
                                        <td
                                            data-col="#: "
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ cliente.id }}
                                        </td>
                                        <td
                                            data-col="Nombre"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            {{ cliente.nombre }}
                                        </td>
                                        <td
                                            data-col="Acción"
                                            class="px-6 py-4 block before:content-[attr(data-col)] before:bold before:text-white before:block sm:before:content-[] sm:before:hidden sm:table-cell"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'clientes.edit',
                                                        cliente.id
                                                    )
                                                "
                                                class="px-4 py-2 text-white bg-blue-600 rounded-lg inline-block hover:bg-blue-700"
                                            >
                                                <font-awesome-icon
                                                    icon="edit" /></Link
                                            ><br />
                                            <BreezeButton
                                                class="bg-red-700 inline-block mt-2 hover:bg-red-800"
                                                @click="destroy(cliente.id)"
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
