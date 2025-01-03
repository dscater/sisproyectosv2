<script>
export default {
    layout: Login,
};
</script>

<script setup>
import Login from "@/Layouts/Login.vue";
import { onMounted } from "vue";
import { useForm, Head, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
const { url_assets } = usePage().props;

const form = useForm({
    usuario: "",
    password: "",
});

const submit = () => {
    form.post(route("login"), {
        onError: (err) => {
            if (err.acceso) {
                Swal.fire({
                    icon: "info",
                    title: "Error",
                    text: `${err.acceso}`,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: `Aceptar`,
                });
            }
        },
        onFinish: () => form.reset("password"),
    });
};

const url_asset = ref("/");

onMounted(() => {
    url_asset.value = url_assets;
});
</script>

<template>
    <form @submit.prevent="submit">
        <p class="login-box-msg">Ingresar usuario y contraseña</p>
        <div class="input-group mb-3">
            <input
                type="text"
                class="form-control"
                placeholder="Usuario"
                v-model="form.usuario"
            />
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
            </div>
        </div>
        <div class="input-group mb-3">
            <input
                type="password"
                class="form-control"
                placeholder="Contraseña"
                v-model="form.password"
                autocomplete="false"
            />
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button
                    type="submit"
                    class="btn btn-primary btn-block"
                    :disabled="form.processing"
                >
                    Acceder
                </button>
            </div>
            <!-- /.col -->
        </div>
    </form>
</template>

<style scoped>
.v-container {
    background-color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    min-width: 100vw;
}
</style>
