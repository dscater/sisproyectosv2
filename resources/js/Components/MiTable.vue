<script setup>
import axios from "axios";
import MiPaginacion from "@/Components/MiPaginacion.vue";
import { ref, onMounted, watch, nextTick, useSlots } from "vue";

const props = defineProps({
    cols: {
        type: Array,
        default: [],
    },
    data: {
        type: Array,
        default: [],
    },
    api: {
        type: Boolean,
        default: false,
    },
    url: {
        type: String,
        default: "",
    },
    sinRegistros: {
        type: String,
        default: "No se encontrarón registros",
    },
    tableClass: {
        type: String,
        default: "",
    },
    headerClass: {
        type: String,
        default: "",
    },
    bodyClass: {
        type: String,
        default: "",
    },
    paginationClass: {
        type: String,
        default: "",
    },
    perPage: {
        type: Number,
        default: 5,
    },
    filterPage: {
        type: Array,
        default: [5, 10, 20, 30, 50, 100],
    },
    syncOrderBy: {
        default: null,
    },
    syncOrderAsc: {
        default: null,
    },
    search: {
        type: String,
        default: "",
    },
    multiSearch: {
        type: Object,
        default: null,
    },
    numPages: {
        type: Number,
        default: 7,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    textCargando: {
        type: String,
        default: "Cargando...",
    },
    delaySearch: {
        type: Number,
        default: 300,
    },
    // para listar los registros por cards - PENDIENTE
    card: {
        type: Boolean,
        default: false,
    },
});

const listCols = ref(props.cols);
const listData = ref(props.data);
const listItems = ref([]);
const textSinRegistros = ref(props.sinRegistros);
const error = ref(false);
const mensajeError = ref("");
const per_page = ref(props.perPage);
const orderBy = ref(props.syncOrderBy);
const orderAsc = ref(
    props.syncOrderAsc ? props.syncOrderAsc.toLowerCase() : null
);

const total = ref(listData.value.length);
const currentPage = ref(1);
const filter_page = ref(props.filterPage);
const tSearch = ref(props.search);
const oMultiSearch = ref(props.multiSearch);
const aPaginas = ref([]);
const cDeRegistros = ref(1);
const cARegistros = ref(per_page.value);
const totalPages = ref(0);
const pLoading = ref(false);
const eTbody = ref(null);
const intervalSearch = ref(null);

const apiRegistros = async () => {
    try {
        let dataParams = {
            page: currentPage.value,
            currentPage: currentPage.value,
            perPage: per_page.value,
            search: tSearch.value,
            orderBy: orderBy.value,
            orderAsc: orderAsc.value,
        };
        if (oMultiSearch.value) {
            dataParams = {
                page: currentPage.value,
                currentPage: currentPage.value,
                perPage: per_page.value,
                ...oMultiSearch.value,
                orderBy: orderBy.value,
                orderAsc: orderAsc.value,
            };
        }
        const response = await axios(props.url, {
            params: dataParams,
        });

        return response.data;
    } catch (err) {
        pLoading.value = false;
        error.value = true;
        console.log(err);
        return null;
    }
};

watch(
    () => props.syncOrderBy,
    (newVal) => {
        orderBy.value = newVal;
        cargarDatos();
    }
);

watch(
    () => props.syncOrderAsc,
    (newVal) => {
        orderAsc.value = newVal ? newVal.toLowerCase() : null;
        cargarDatos();
    }
);

watch(
    () => props.loading,
    (newVal) => {
        pLoading.value = newVal;
    }
);

watch(
    () => props.multiSearch,
    (newVal) => {
        oMultiSearch.value = newVal;
        cargarDatos();
    },
    { deep: true }
);

watch(
    () => props.search,
    (newVal) => {
        tSearch.value = newVal;
        clearInterval(intervalSearch.value);
        intervalSearch.value = setTimeout(() => {
            currentPage.value = 1;
            cargarDatos();
        }, props.delaySearch);
    }
);

watch(per_page, (newValue, oldValue) => {
    cambiaPerPage();
});

watch(
    () => props.data,
    (newVal) => {
        listData.value = newVal;
        total.value = listData.value.length;
        cargarDatos();
    }
);

const cargarDatos = async () => {
    pLoading.value = true;
    if (props.api) {
        const resp = await apiRegistros();
        total.value = resp.total;
        listItems.value = resp.data;
        totalPages.value = resp.lastPage;
        muestraCantidadRegistros();
    } else {
        listItems.value = await filtrarDatosEstaticos();
        // Actualiza los contadores de registros
        muestraCantidadRegistros();
    }
    await nextTick();
    if (eTbody.value) {
        eTbody.value.style.minHeight = `${eTbody.value.offsetHeight}px`;
    }
    pLoading.value = false;
};

const cambiaPerPage = async () => {
    currentPage.value = 1;
    await cargarDatos();
};

const changeOrderBy = async (orderCol) => {
    let oldOrderBy = orderBy.value;
    orderBy.value = orderCol;
    if (oldOrderBy != orderBy.value) {
        orderAsc.value = "asc";
    } else {
        switch (orderAsc.value) {
            case "asc":
                orderAsc.value = "desc";
                break;
            case "desc":
                orderAsc.value = null;
                orderBy.value = null;
                break;
            case null:
                orderAsc.value = "asc";
                break;
        }
    }
    cargarDatos();
};

const cambioDePagina = async (value) => {
    currentPage.value = value;
    if (props.api) {
        await cargarDatos();
    } else {
        listItems.value = await filtrarDatosEstaticos();
        // Actualiza los contadores de registros
        muestraCantidadRegistros();
    }
};

const filtrarDatosEstaticos = async () => {
    const page = parseInt(currentPage.value);
    const pageSize = parseInt(per_page.value);
    const vOrderBy = orderBy.value;
    const vOrderAsc = orderAsc.value;

    // Filtra el array por el valor de búsqueda en cualquier columna
    let filteredArray = await listData.value.filter((item) => {
        return Object.values(item).some((value) =>
            value.toString().toLowerCase().includes(tSearch.value.toLowerCase())
        );
    });

    total.value = filteredArray.length;
    totalPages.value = Math.ceil(total.value / per_page.value);

    // Aplica el ordenamiento según orderBy y orderAsc
    if (vOrderAsc) {
        filteredArray = filteredArray.sort((a, b) => {
            let valA = a[vOrderBy];
            let valB = b[vOrderBy];

            // Si las columnas son numéricas, compara como números, sino como cadenas
            if (typeof valA === "string" && typeof valB === "string") {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            } else if (typeof valA === "number" && typeof valB === "number") {
                // No es necesario hacer nada, ya que JavaScript lo maneja de forma natural
            }

            if (valA < valB) {
                return vOrderAsc === "asc" ? -1 : 1;
            }
            if (valA > valB) {
                return vOrderAsc === "asc" ? 1 : -1;
            }
            return 0;
        });
    }

    // Calcula el índice de inicio para la paginación
    const startIndex = (page - 1) * pageSize;

    // Realiza la paginación
    const listaFiltrada = filteredArray.slice(
        startIndex,
        startIndex + pageSize
    );

    // Actualiza los contadores de registros
    // cDeRegistros.value = startIndex + 1; // Primer registro de la página
    // const total_reg = listaFiltrada.length - 1;
    // cARegistros.value = cDeRegistros.value + total_reg; // Último registro de la página

    // Devuelve la sección del array filtrado, ordenado y paginado
    return listaFiltrada;
};

const muestraCantidadRegistros = () => {
    const startIndex = (parseInt(currentPage.value) - 1) * per_page.value;
    const total_reg = listItems.value.length - 1;
    cDeRegistros.value = startIndex + 1; // Primer registro de la página
    cARegistros.value = cDeRegistros.value + total_reg; // Último registro de la página
};

const getColumnStyle = (item) => {
    const width = item.width
        ? `${item.width + (item.wp ? item.wp : "%")}`
        : "auto"; // Puedes calcular o definir el valor base para el ancho
    return {
        width: width,
    };
};

function getColumnValue(obj, key) {
    return key.split(".").reduce((acc, part) => acc && acc[part], obj);
}

function getRowClass(item) {
    let classes = [];
    for (const column of listCols.value) {
        if (column.classRow) {
            const className = column.classRow(item);
            if (className) {
                classes.push(className);
            }
        }
    }
    return classes.join(" ");
}

function getClassActiveSort(item) {
    return orderBy.value == (item.keySortable ? item.keySortable : item.key);
}

const slots = useSlots();

onMounted(() => {
    if (props.api) {
        cargarDatos();
    }
});

defineExpose({
    cargarDatos,
});
</script>
<template>
    <table
        class="table table-bordered mb-0"
        :class="[tableClass, $attrs.class]"
    >
        <thead :class="[headerClass]">
            <template v-if="$slots.tableHeader">
                <slot name="tableHeader"></slot>
            </template>
            <template v-else>
                <tr>
                    <th v-for="item in listCols" :style="getColumnStyle(item)">
                        <div
                            class="iheader sortable"
                            v-if="item.sortable"
                            @click="
                                changeOrderBy(
                                    item.keySortable
                                        ? item.keySortable
                                        : item.key
                                )
                            "
                        >
                            <div class="label">{{ item.label }}</div>
                            <div
                                class="accion"
                                :class="{
                                    active: getClassActiveSort(item),
                                }"
                            >
                                <i
                                    class="fa"
                                    :class="{
                                        'fa-sort-amount-up-alt':
                                            orderAsc == 'asc' &&
                                            orderBy ==
                                                (item.keySortable
                                                    ? item.keySortable
                                                    : item.key),
                                        'fa-sort-amount-down':
                                            orderAsc == 'desc' &&
                                            orderBy ==
                                                (item.keySortable
                                                    ? item.keySortable
                                                    : item.key),
                                        'fa-sort':
                                            !orderAsc ||
                                            orderBy !=
                                                (item.keySortable
                                                    ? item.keySortable
                                                    : item.key),
                                    }"
                                ></i>
                            </div>
                        </div>
                        <div class="iheader" v-else>
                            <div class="label">{{ item.label }}</div>
                        </div>
                    </th>
                </tr>
            </template>
        </thead>
        <tbody :class="[bodyClass, pLoading ? 'loading' : '']" ref="eTbody">
            <div class="loading" v-show="pLoading">
                <div>
                    <template v-if="$slots.loading">
                        <slot name="loading"></slot>
                    </template>
                    <template v-else> {{ textCargando }} </template>
                </div>
            </div>
            <template v-if="listItems.length > 0">
                <tr
                    v-for="(item, index) in listItems"
                    :class="getRowClass(item)"
                >
                    <td
                        v-for="(i_col, index) in listCols"
                        :data-label="i_col.label"
                        :class="i_col.classTd ? i_col.classTd(item) : ''"
                    >
                        <template v-if="$slots[i_col.key]">
                            <slot
                                :name="i_col.key"
                                :item="item"
                                v-bind="$attrs"
                            ></slot>
                        </template>
                        <template v-else>
                            {{ getColumnValue(item, i_col.key) }}
                        </template>
                    </td>
                </tr>
            </template>
            <template v-else>
                <tr>
                    <td :colspan="listCols.length">
                        {{ textSinRegistros }}
                    </td>
                </tr>
            </template>
        </tbody>
        <tfoot></tfoot>
    </table>
    <div class="row mt-1">
        <div class="my-1 col-md-3">
            <select class="form-control rounded-0" v-model="per_page">
                <option v-for="item in filter_page" :value="item">
                    Mostrar {{ item }} registros
                </option>
            </select>
        </div>
        <div class="my-1 col-md-6 d-flex justify-content-center">
            <MiPaginacion
                :current-page="currentPage"
                :total-data="total"
                :per-page="per_page"
                @updatePage="cambioDePagina"
            />
        </div>
        <div class="my-1 col-md-3 text-right">
            Mostrando {{ cDeRegistros }} a {{ cARegistros }} registros - Total
            {{ total }} registros
        </div>
    </div>
    <div v-if="error" class="alert alert-danger">
        Ocurrió un error al intentar obtener los registros
    </div>
</template>
<style scoped>
table thead tr th .iheader {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    gap: 3px;
}

table thead tr th .iheader.sortable {
    cursor: pointer;
}
table thead tr th .iheader .accion {
    color: rgba(0, 0, 0, 0.3);
}
table thead tr th .iheader .accion.active {
    color: black;
    display: block;
}

tbody {
    position: relative;
}

tbody .loading {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 200;
    display: flex;
    justify-content: center;
    align-items: center;
}

tbody .loading::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    filter: blur(1px);
    z-index: -1; /* Envía el fondo detrás del contenido */
}

@media screen and (max-width: 790px) {
    table thead {
        display: none;
    }

    table tbody tr:nth-child(odd) {
        background-color: rgb(233, 233, 233);
    }

    table tbody tr td {
        display: block;
        font-size: 0.8rem;
    }

    table tbody tr td::before {
        content: attr(data-label) ": ";
        width: 40%;
        float: left;
        text-align: right;
        overflow-wrap: break-word;
        font-weight: 700;
        font-style: normal;
        padding: 0 0.5rem 0 0;
        margin: 0;
    }
}
</style>
