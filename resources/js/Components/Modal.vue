<script setup>
const props = defineProps({
    mostrar: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        v-show="mostrar"
        class="contenedor_principal_modal fixed flex h-screen w-screen top-0 justify-center items-center flex-col"
    >
        <div class="contenedor_modal h-5/6 w-5/6 flex items-center flex-col">
            <div
                class="header_modal bg-dark w-5/6 h-5/6 p-5 header_modal bg-white dark:text-white dark:bg-gray-900"
            >
                <slot name="header" />
            </div>
            <div
                class="body_modal bg-dark w-5/6 p-5 body_modal bg-white dark:bg-gray-900"
            >
                <slot name="body" />
            </div>
            <div
                class="footer_modal bg-dark w-5/6 h-5/6 p-5 body_modal bg-white dark:bg-gray-900"
            >
                <slot name="foot" />
            </div>
        </div>
    </div>
</template>
<style scoped>
.contenedor_principal_modal {
    background-color: rgba(0, 0, 0, 0.664);
}

.body_modal{
    height: auto;
}

.header_modal {
    height: 60px;
    max-height: auto;
    border-bottom: solid 1px gray;
}
.footer_modal {
    border-top: solid 1px gray;
    height: 90px;
    max-height: auto;
}
</style>
