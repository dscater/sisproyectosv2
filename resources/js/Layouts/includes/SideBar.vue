<script setup>
import { onMounted, onUnmounted, ref, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import ItemMenu from "@/Components/ItemMenu.vue";
import { useSideBar } from "@/composables/useSidebar.js";
import { useAppStore } from "@/stores/aplicacion/appStore";
import { useConfiguracionStore } from "@/stores/configuracion/configuracionStore";
const { closeSidebar, toggleSubMenuELem } = useSideBar();
const configuracionStore = useConfiguracionStore();
const appStore = useAppStore();
const configuracion = ref(null);
const usuario = ref(null);
const toggleSubMenu = (e) => {
    e.stopPropagation();
    const elem = e.currentTarget;
    if (
        elem.classList.contains("menu-is-opening") &&
        elem.classList.contains("menu-open")
    ) {
        elem.classList.remove("menu-is-opening");
        elem.classList.remove("menu-open");
        toggleSubMenuELem(elem, false);
    } else {
        elem.classList.add("menu-is-opening");
        elem.classList.add("menu-open");
        toggleSubMenuELem(elem, true);
    }
};

const route_current = ref("");
router.on("navigate", (event) => {
    route_current.value = route().current();
    closeSidebar();
});

onMounted(() => {
    usuario.value = appStore.getUsuario;
    configuracion.value = configuracionStore.getConfiguracion;
    // Selecciona el elemento del widget
    var sidebarSearchElement = $('[data-widget="sidebar-search"]');
    // Configura manualmente el texto de "no encontrado"
    sidebarSearchElement.data("notFoundText", "Sin resultados");
});

onUnmounted(() => {});
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
            <span class="brand-text font-weight-light">{{
                configuracion?.sistema
            }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img
                        :src="usuario?.url_foto"
                        class="img-circle elevation-2"
                        alt="User Image"
                    />
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ usuario?.full_name }}</a>
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
                            'trabajos.pagos',
                        ]"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Pagos'"
                        :ruta="'pagos.index'"
                        :icon="'fa-list'"
                        :array-ruta-class-active="[
                            'pagos.index',
                            'pagos.create',
                            'pagos.edit',
                            'pagos.show',
                        ]"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Monedas'"
                        :ruta="'monedas.index'"
                        :icon="'fa-list'"
                    ></ItemMenu>
                    <ItemMenu
                        :label="'Tipo de cambios'"
                        :ruta="'tipo_cambios.index'"
                        :icon="'fa-list'"
                    ></ItemMenu>

                    <li class="nav-item">
                        <a
                            href="#"
                            class="nav-link sub-menu"
                            :class="[
                                route_current == 'reportes.trabajos' ||
                                route_current == 'reportes.pagos'
                                    ? 'active menu-is-opening menu-open'
                                    : '',
                            ]"
                            @click.stop="toggleSubMenu($event)"
                        >
                            <i class="nav-icon fas fa-table"></i>
                            <p>
                                Reportes
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <ItemMenu
                                :label="'Trabajos'"
                                :ruta="'reportes.trabajos'"
                                :icon="'fa-angle-right'"
                            ></ItemMenu>
                            <ItemMenu
                                :label="'Pagos'"
                                :ruta="'reportes.pagos'"
                                :icon="'fa-angle-right'"
                            ></ItemMenu>
                        </ul>
                    </li>

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
