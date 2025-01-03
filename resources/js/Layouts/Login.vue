<script setup>
import { onMounted, onBeforeUnmount, onBeforeMount, ref } from "vue";
import { useConfiguracionStore } from "@/stores/configuracion/configuracionStore";
const configuracionStore = useConfiguracionStore();
const configuracion = ref(null);

onBeforeMount(() => {
    configuracionStore.initConfiguracion();
});

onMounted(() => {
    configuracion.value = configuracionStore.getConfiguracion;
    document.getElementsByTagName("body")[0].classList.add("login-page");
});

onBeforeUnmount(() => {
    document.getElementsByTagName("body")[0].classList.remove("login-page");
});
</script>
<template>
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="../../index2.html" class="h1">{{
                    configuracion?.sistema
                }}</a>
            </div>
            <div class="card-body">
                <slot />
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</template>
<style scoped>
.login-page {
    min-height: 100vh;
}
</style>
