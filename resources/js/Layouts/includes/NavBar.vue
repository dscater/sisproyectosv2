<script setup>
// Composables
import { usePage, Link } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import { useSideBar } from "@/composables/useSidebar.js";
const { props } = usePage();
const { toggleSidebar } = useSideBar();

onMounted(() => {});

onUnmounted(() => {
});
</script>
<template>
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a
                    class="nav-link toggleButton"
                    href="#"
                    role="button"
                    @click="toggleSidebar"
                    ><i class="fas fa-bars"></i
                ></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="index3.html" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Contact</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a
                    class="nav-link"
                    data-widget="fullscreen"
                    href="#"
                    role="button"
                >
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->
</template>
