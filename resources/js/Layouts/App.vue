<script setup>
// includes
import { ref } from "vue";
import Footer from "./includes/Footer.vue";
import NavBar from "./includes/NavBar.vue";
import SideBar from "./includes/SideBar.vue";
import { onMounted } from "vue";

onMounted(() => {});
</script>
<template>
    <div class="wrapper">
        <NavBar></NavBar>
        <SideBar></SideBar>
        <slot />
        <Footer></Footer>
    </div>
</template>
