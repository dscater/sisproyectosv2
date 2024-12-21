<script setup>
const props = defineProps({
    pago: {
        type: Object,
        default: () => ({}),
    },
    trabajo: {
        type: Object,
        default: () => ({}),
    },
    nro: {
        type: Number,
        default: 0,
    },
    muestra_desc: {
        type: Boolean,
        default: true,
    },
});
</script>
<template>
    <div :class="[nro > 1 ? 'mt-3' : '']">
        <div
            class="border border-gray-50 p-5 rounded-xl bg-white text-gray-700"
        >
            <h4 v-if="nro != 0" class="font-bold underline text-2xl">
                Pago {{ nro }}
            </h4>
            <p v-if="muestra_desc">
                <strong>Nombre del proyecto: </strong
                >{{ trabajo.proyecto.nombre }}
                <strong
                    ><i>({{ trabajo.proyecto.alias }})</i></strong
                >
            </p>
            <p v-if="muestra_desc">
                <strong>Descripción del trabajo: </strong>
                <br>
                <span class="" v-html="trabajo.descripcion"></span>
            </p>
            <p><strong>Cliente: </strong>{{ trabajo.cliente.nombre }}</p>
            <p>
                <strong>Monto registrado: </strong>{{ pago.moneda.nombre }}
                {{ pago.monto }}
                <template v-if="trabajo.tipo_cambio_id != 0">
                    &nbsp;&nbsp; | &nbsp;&nbsp;{{ pago.moneda_cambio.nombre }}
                    {{ pago.monto_cambio }}
                </template>
            </p>
            <p><strong>Fecha del pago: </strong>{{ pago.fecha_pago }}</p>
            <div v-if="pago.foto_comprobante && pago.foto_comprobante != ''">
                <h4 class="mt-2 text-lg font-bold">Comprobantes</h4>
                <hr class="border-1 mt-2 border-gray-400" />
                <strong>Foto del comprobante:</strong>
                <img
                    :src="pago.url_foto"
                    alt=""
                    class="w-1/2 border border-gray-500 p-5"
                />
            </div>
            <div
                v-if="
                    pago.archivo_comprobante && pago.archivo_comprobante != ''
                "
            >
                <hr class="border-1 mt-2 border-gray-400" />
                <strong>Archivo/Imagen del comprobante:</strong>
                <img
                    :src="pago.url_archivo"
                    alt=""
                    class="w-1/2 border border-gray-500 p-5"
                    v-if="pago.tipo_archivo == 'imagen'"
                />
                <iframe
                    :src="pago.url_archivo"
                    frameborder="0"
                    v-if="pago.tipo_archivo == 'pdf'"
                    width="50%"
                    height="300px"
                ></iframe>
                <div v-if="pago.tipo_archivo == 'otros'">
                    <a :href="pago.url_archivo" target="_blank">Descargar</a>
                </div>
                <p>
                    <strong>Descripción:</strong>{{ pago.descripcion_archivo }}
                </p>
            </div>
        </div>
    </div>
</template>
