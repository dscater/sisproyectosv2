<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Pago from "./Pago.vue";
const props = defineProps({
    pago: {
        type: Object,
        default: () => ({}),
    },
});
</script>
<template>
    <Head title="Pago Edit" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Ver Pago</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div
                        class="p-6 bg-white border-b bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                    >
                        <Pago :pago="pago" :trabajo="pago.trabajo"></Pago>

                        <a
                            :href="route('pagos.index')"
                            type="submit"
                            class="mt-2 text-white bg-white border border-gray-300 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-black"
                        >
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
