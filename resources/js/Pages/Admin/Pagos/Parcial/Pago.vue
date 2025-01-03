<script setup>
import { ref } from "vue";
import MiModal from "@/Components/MiModal.vue";
import { fHelpers } from "@/Functions/fHelpers";
const { getFormatoMoneda } = fHelpers();

const props = defineProps({
    pago: {
        type: Object,
        default: () => ({}),
    },
});

const open_modal = ref(false);

const cerrarModal = () => {
    open_modal.value = false;
};

const pantallaCompleta = (e) => {
    e.stopPropagation();
    // pantalla completa
    let contenedor = e.target.closest(".contenedor_imagen");
    // Verificamos si la API de pantalla completa está disponible
    if (contenedor) {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            contenedor.requestFullscreen().catch((err) => {
                console.log(
                    `Error al intentar entrar en pantalla completa: ${err.message}`
                );
            });
        }
    }
};

const url_iframe_pdf = ref("");
const verPdf = (e) => {
    e.preventDefault();
    e.stopPropagation();
    let elemento = e.target;
    if (e.target.tagName.toLowerCase() != "button") {
        elemento = e.target.closest("button");
    }
    url_iframe_pdf.value = elemento.getAttribute("data-url");

    open_modal.value = true;
};
</script>
<template>
    <div class="card">
        <div class="card-body">
            <div class="fila_info">
                <div class="label_info">Fecha del pago:</div>
                <div class="content_info">
                    {{ pago.fecha_pago_t }}
                </div>
            </div>
            <div class="fila_info">
                <div class="label_info">Desc. Trabajo:</div>
                <div
                    class="content_info"
                    v-html="pago.trabajo.descripcion"
                ></div>
            </div>
            <div class="fila_info">
                <div class="label_info">Cliente:</div>
                <div class="content_info">
                    {{ pago.cliente.nombre }}
                </div>
            </div>
            <div class="fila_info">
                <div class="label_info">Foto comprobante:</div>
                <div class="content_info">
                    <div class="contenedor_imagen" v-if="pago.url_foto">
                        <button @click="pantallaCompleta($event)">
                            <i class="fa fa-expand"></i>
                        </button>
                        <img
                            :src="pago.url_foto"
                            alt="Comprobante"
                            class="foto"
                        />
                    </div>
                    <span v-else>NO</span>
                </div>
            </div>
            <div class="fila_info">
                <div class="label_info">Archivo comprobante:</div>
                <div class="content_info">
                    <template v-if="pago.url_archivo">
                        <div
                            class="contenedor_imagen"
                            v-if="pago.tipo_archivo == 'imagen'"
                        >
                            <button @click="pantallaCompleta($event)">
                                <i class="fa fa-expand"></i>
                            </button>
                            <img
                                :src="pago.url_archivo"
                                alt="Comprobante"
                                class="foto"
                            />
                        </div>
                        <button
                            v-if="pago.tipo_archivo == 'pdf'"
                            class="btn btn-primary"
                            @click="verPdf($event)"
                            :data-url="pago.url_archivo"
                        >
                            Ver archivo
                            <i class="fa fa-file-pdf"></i>
                        </button>
                        <a
                            :href="pago.url_archivo"
                            target="_blank"
                            v-if="pago.tipo_archivo == 'otros'"
                            >Ver archivo</a
                        >
                    </template>
                    <span v-else>NO</span>
                </div>
            </div>
            <div class="fila_info text-md">
                <div class="label_info">Monto {{ pago.moneda.nombre }}:</div>
                <div class="content_info">
                    {{ getFormatoMoneda(pago.monto) }}
                </div>
            </div>

            <div
                class="fila_info text-md"
                v-if="pago.trabajo.tipo_cambio_id != 0"
            >
                <div class="label_info">
                    Monto {{ pago.moneda_cambio.nombre }}:
                </div>
                <div class="content_info">
                    {{ pago.monto_cambio }}
                </div>
            </div>
        </div>
    </div>
    <MiModal
        :open_modal="open_modal"
        @close="cerrarModal"
        :size="'modal-xl'"
        :header-class="'bg-dark'"
        :footer-class="'justify-content-end'"
        :close-esc="true"
    >
        <template #header>
            <h4 class="modal-title">Nuevo cliente</h4>
            <button type="button" class="close" @click.prevent="cerrarModal()">
                <span aria-hidden="true">×</span>
            </button>
        </template>
        <template #body>
            <iframe
                :src="`${url_iframe_pdf}#view=FITH`"
                frameborder="0"
                width="100%"
                style="height: 75vh"
            ></iframe>
        </template>
        <template #footer>
            <button
                type="button"
                class="btn btn-default"
                @click.prevent="cerrarModal()"
            >
                Cerrar
            </button>
        </template>
    </MiModal>
</template>
<style>
.fila_info {
    display: flex;
    margin-bottom: 10px;
}

.fila_info .contenedor_imagen {
    width: 120px;
    position: relative;
}
.fila_info .contenedor_imagen button {
    position: absolute;
    top: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.473);
    color: white;
}

.foto {
    width: 100%;
}

.fila_info .label_info {
    width: 35%;
    text-align: right;
    font-weight: bold;
    padding-right: 10px;
}
.fila_info .content_info {
    flex: 1;
}

/* Estilo para el contenedor en pantalla completa */
.fila_info .contenedor_imagen:fullscreen {
    overflow: hidden;
    top: 0;
    left: 0;
    position: fixed;
    z-index: 9999;
    background-color: rgba(0, 0, 0, 0.747);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    width: 100vw;
    height: 100vh;
}

.fila_info .contenedor_imagen:fullscreen img {
    height: 98%;
    object-fit: contain;
}

/* Estilo para los botones cuando estamos en pantalla completa */
.fila_info .contenedor_imagen:fullscreen button {
    background-color: rgba(
        0,
        0,
        0,
        0.5
    ); /* Fondo más oscuro para los controles */
    color: white;
}
</style>
