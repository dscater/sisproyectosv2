<script setup>
import { onMounted, ref } from "vue";
import { useMenu } from "@/composables/useMenu";
import { usePage, router } from "@inertiajs/vue3";

const props = defineProps({
    label: {
        type: String,
        default: "Label",
    },
    icon: {
        type: String,
        default: "mdi-dots-horizontal",
    },
    abrev: {
        type: String,
        default: "-",
    },
});

const {
    mobile,
    drawer,
    rail,
    width,
    menu_open,
    setMenuOpen,
    cambiarUrl,
    toggleDrawer,
} = useMenu();

const route_current = ref("");

router.on("navigate", (event) => {
    route_current.value = route().current();
    if (mobile.value) {
        toggleDrawer(false);
    }
});
onMounted(() => {});
</script>
<template>
    <v-list-item class="text-caption bg-blue-darken-4 pa-0 px-5">
        <span v-if="rail && !mobile" class="text-center d-block">{{
            abrev
        }}</span>
        <span v-else>{{ label }}</span></v-list-item
    >
</template>
