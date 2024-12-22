<script setup>
import { onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import ItemMenu from "@/Components/ItemMenu.vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();
onMounted(() => {
    // Selecciona el elemento del widget
    var sidebarSearchElement = $('[data-widget="sidebar-search"]');
    // Configura manualmente el texto de "no encontrado"
    sidebarSearchElement.data("notFoundText", "Sin resultados");
});
</script>
<template>
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index3.html" class="brand-link">
            <!-- <img
                src="dist/img/AdminLTELogo.png"
                alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3"
                style="opacity: 0.8"
            /> -->
            <span class="brand-text font-weight-light">AdminLTE 3</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <!-- <img
                        src="dist/img/user2-160x160.jpg"
                        class="img-circle elevation-2"
                        alt="User Image"
                    /> -->
                </div>
                <div class="info">
                    <a href="#" class="d-block">Alexander Pierce</a>
                </div>
            </div>

            <!-- SidebarSearch Form -->
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input
                        class="form-control form-control-sidebar"
                        type="search"
                        placeholder="Buscar"
                        aria-label="Buscar"
                    />
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul
                    class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false"
                >
                    <ItemMenu
                        :label="'Inicio'"
                        :ruta="'inicio'"
                        :icon="'fa-home'"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Proyectos'"
                        :ruta="'proyectos.index'"
                        :icon="'fa-list'"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Clientes'"
                        :ruta="'clientes.index'"
                        :icon="'fa-list'"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Trabajos'"
                        :ruta="'trabajos.index'"
                        :icon="'fa-list'"
                        :array-ruta-class-active="[
                            'trabajos.index',
                            'trabajos.create',
                            'trabajos.edit',
                        ]"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Salir'"
                        :ruta="'logout'"
                        :method="'POST'"
                        :icon="'fa-power-off'"
                    ></ItemMenu>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
</template>
<style scoped></style>
