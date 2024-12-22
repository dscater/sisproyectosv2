<script setup>
// includes
import { ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import Footer from "./includes/Footer.vue";
import NavBar from "./includes/NavBar.vue";
import SideBar from "./includes/SideBar.vue";
</script>
<template>
    <div class="wrapper">
        <NavBar></NavBar>
        <SideBar></SideBar>
        <slot />
        <Footer></Footer>
    </div>
</template>
