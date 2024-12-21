<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);

const rutas = [
    {
        label: "Inicio",
        route: route("dashboard"),
        active: route().current("dashboard"),
    },
    {
        label: "Proyectos",
        route: route("proyectos.index"),
        active:
            route().current("proyectos.index") ||
            route().current("proyectos.create") ||
            route().current("proyectos.edit"),
    },
    {
        label: "Clientes",
        route: route("clientes.index"),
        active:
            route().current("clientes.index") ||
            route().current("clientes.create") ||
            route().current("clientes.edit"),
    },
    {
        label: "Trabajos",
        route: route("trabajos.index"),
        active:
            route().current("trabajos.index") ||
            route().current("trabajos.create") ||
            route().current("trabajos.edit"),
    },
    {
        label: "Pagos",
        route: route("pagos.index"),
        active:
            route().current("pagos.index") ||
            route().current("pagos.create") ||
            route().current("pagos.edit"),
    },
    {
        label: "Tipo de cambio",
        route: route("monedas.index"),
        active:
            route().current("monedas.index") ||
            route().current("monedas.create") ||
            route().current("monedas.edit"),
    },
    {
        label: "Reportes",
        multiselect: true,
        menu: [
            {
                route: route("reportes.trabajos"),
                label: "Trabajos",
                active: false,
            },
            { route: route("reportes.pagos"), label: "Pagos", active: false },
        ],
        route: "",
        active:
            route().current("reportes.trabajos") ||
            route().current("reportes.pagos"),
    },
];
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                            </div>

                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                          
                        </div>

                     
                    </div>
                </div>

            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-700 dark:text-gray-200 uppercase"
                v-if="$slots.header"
            >
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
