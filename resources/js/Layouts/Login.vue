<script setup>
import { onMounted, onBeforeUnmount } from "vue";
onMounted(() => {
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
                <a href="../../index2.html" class="h1"><b>Admin</b>LTE</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to start your session</p>

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
