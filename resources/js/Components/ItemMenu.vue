<script setup>
import { onMounted, ref } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";

const props = defineProps({
    label: {
        type: String,
        default: "Label",
    },
    icon: {
        type: String,
        default: "mdi-home-outline",
    },
    ruta: {
        type: String,
        default: "/",
    },
    method: {
        type: String,
        default: "GET",
    },
});

const route_current = ref("");

router.on("navigate", (event) => {
    route_current.value = route().current();
});

const ejecutaPost = () => {
    router.post(route(props.ruta));
};

onMounted(() => {});
</script>
<template>
    <li class="nav-item" v-if="method.toLowerCase() == 'get'">
        <Link
            :href="ruta ? route(ruta) : '/'"
            class="nav-link"
            :class="[route_current == ruta ? 'active' : '']"
        >
            <i class="nav-icon fas" :class="icon ? icon : 'fa-th'"></i>
            <p>
                {{ label }}
            </p>
        </Link>
    </li>
    <li class="nav-item" v-else>
        <a href="#" class="nav-link" @click.prevent="ejecutaPost()">
            <i class="nav-icon fas" :class="icon ? icon : 'fa-th'"></i>
            <p>
                {{ label }}
            </p>
        </a>
    </li>
</template>
