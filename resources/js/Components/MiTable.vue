<script setup>
import axios from "axios";
import MiPaginacion from "@/Components/MiPaginacion.vue";
import { ref, onMounted, watch, nextTick, useSlots } from "vue";
import { debounce } from "lodash";
const props = defineProps({
    cols: {
        type: Array,
        default: [],
    },
    data: {
        type: Array,
        default: [],
    },
    conPaginacion: {
        type: Boolean,
        default: true,
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
    tableResponsive: {
        type: Boolean,
        default: false,
    },
    tableHeight: {
        type: [String, Number],
        default: null,
    },
    tableWidth: {
        type: [String, Number],
        default: null,
    },
    fixCols: {
        // para habilitar los fixs de columnas
        type: Boolean,
        default: false,
    },
    fixedHeader: {
        type: Boolean,
        default: false,
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
const eContentTable = ref(null);
const intervalSearch = ref(null);
const widthColumnsFix = ref([]);
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

watch(
    () => listItems.value,
    async () => {
        await nextTick();
        await esperarCargaElementos();
        actualizaAltoAnchoTablaColumnas();
        pLoading.value = false;
    }
);

// funcion para otener los datos de renderizado, alto tabla y ancho columnas
const actualizaAltoAnchoTablaColumnas = () => {
    setTimeout(() => {
        if (eContentTable.value) {
            const altura = eContentTable.value.getBoundingClientRect().height;
            eContentTable.value.style.height = `${altura}px`;
            eContentTable.value.style.minHeight = `${altura}px`;
        }

        if (miTheadRef.value) {
            initColsWidth();
        }
        if (props.fixCols) {
            renderColumnsStyleFixed();
        }
    }, 200);
};

// funcion para determinar si se cargaron todos los elementos
const esperarCargaElementos = () => {
    return new Promise((r) => window.requestAnimationFrame(r));
};

// funcion general para generar los datos que se muestran en la tabla
// ya sea via URL o el Listado pasado en propiedades
const cargarDatos = async () => {
    pLoading.value = true;
    if (props.api) {
        const resp = await apiRegistros();
        total.value = resp.total;
        listItems.value = resp.data;
        totalPages.value = resp.lastPage;
        muestraCantidadRegistros();
    } else {
        listItems.value = await generarDatosPorLista();
        // Actualiza los contadores de registros
        muestraCantidadRegistros();
    }
};

// generar anchos de celdas
const initColsWidth = () => {
    widthColumnsFix.value = [];
    miTableRef.value.style.tableLayout = "auto";
    const cols = miTheadRef.value.querySelectorAll("th");
    cols.forEach((elem, index) => {
        let contentWidth = 0;
        const data_width = elem.getAttribute("data-width");
        if (data_width) {
            const regex = /px|%|vw/; // Busca px, %, o vw
            const unidad = data_width.match(regex); // Encuentra la unidad en el valor
            if (unidad == "px") {
                contentWidth = data_width;
            } else {
                contentWidth = elem.getBoundingClientRect().width;
            }
        } else {
            contentWidth = elem.getBoundingClientRect().width;
        }
        widthColumnsFix.value[index] = contentWidth;
    });
};

// obtener la distancia right o left del elemento stick
const getRightLetfStick = (indexCol, item, head = false, indexRow = -1) => {
    let pStyle = {
        position: "sticky",
    };
    let inicio = 0;
    let final = 0;

    if (item.fixed) {
        inicio = 0;
        final = indexCol > 0 ? indexCol : 0;
        if (item.fixed == "right") {
            inicio =
                indexCol + 1 < listCols.value.length
                    ? indexCol + 1
                    : listCols.value.length;
            final = listCols.value.length;
        }
    }
    let listFill = [];
    let lefright = "0px";
    if (item.fixed) {
        listFill = widthColumnsFix.value.slice(inicio, final);
        lefright = listFill.reduce((a, b) => {
            return a + b;
        }, 0);
        pStyle["left"] = `${lefright}px`;
        if (item.fixed == "right") {
            pStyle["right"] = `${lefright}px`;
        }
    }

    if (head && item.width) {
        const width = item.width
            ? /\d+%$/.test(item.width) ||
              /\d+px$/.test(item.width) ||
              /\d+vw$/.test(item.width)
                ? item.width
                : `${item.width}%`
            : "";
        pStyle["width"] = `${width}`;
        pStyle["max-width"] = `${width}!important`;
    }

    return pStyle;
};

// obtener el ancho por columna
const getWidthColGroup = (index, item = null) => {
    let width = (widthColumnsFix.value[index] ?? 0) + "px";
    if (item.width) {
        width = item.width
            ? /\d+%$/.test(item.width) ||
              /\d+px$/.test(item.width) ||
              /\d+vw$/.test(item.width)
                ? item.width
                : `${item.width}%`
            : "";
    }

    return {
        width: width,
    };
};

// detectar el cambio de cantidad de registros por pagina
const cambiaPerPage = async () => {
    currentPage.value = 1;
    await cargarDatos();
};

// detectar cambio del orden
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

// detectar cambio de pagina
const cambioDePagina = async (value) => {
    currentPage.value = value;
    if (props.api) {
        await cargarDatos();
    } else {
        listItems.value = await generarDatosPorLista();
        // Actualiza los contadores de registros
        muestraCantidadRegistros();
    }
};

// generar el listado de datos sin uso de una api
const generarDatosPorLista = async () => {
    pLoading.value = true;
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
    let listaFiltrada = filteredArray;
    if (props.conPaginacion) {
        listaFiltrada = filteredArray.slice(startIndex, startIndex + pageSize);
    }

    // Devuelve la sección del array filtrado, ordenado y paginado
    return listaFiltrada;
};

const muestraCantidadRegistros = () => {
    const startIndex = (parseInt(currentPage.value) - 1) * per_page.value;
    const total_reg = listItems.value.length - 1;
    cDeRegistros.value = startIndex + 1; // Primer registro de la página
    cARegistros.value = cDeRegistros.value + total_reg; // Último registro de la página
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

const thtdRefs = ref({});
const miTableRef = ref(null);
const miTheadRef = ref(null);
// Funcion para renderizar las columnas con position sticky|posicion fixeada
const renderColumnsStyleFixed = () => {
    const table = miTableRef.value;
    // Configurar estilos iniciales para la tabla
    const cols = listCols.value;
    const rows = listItems.value;
    const thRefs = thtdRefs.value;

    // Estilo para columnas definidas por clase
    ["fixed-column-ext", "fixed-column-ext-right"].forEach((className, dir) => {
        let offset = 0;
        const elements = table.querySelectorAll(`.${className}`);
        const iter = dir === 0 ? elements : [...elements].reverse();

        iter.forEach((el) => {
            if (el) {
                el.style.position = "sticky";
                el.style[dir === 0 ? "left" : "right"] = `${offset}px`;
                if (el.classList.contains("footer-fixed")) {
                    el.style.bottom = 0;
                }
                if (el.classList.contains("header-fixed")) {
                    el.style.top = 0;
                }

                if (el.classList.contains("fixed-width")) {
                    // el.style.width = el.getBoundingClientRect().width;
                }
                offset += el.offsetWidth;
            }
        });
    });
};

function getClassActiveSort(item) {
    return orderBy.value == (item.keySortable ? item.keySortable : item.key);
}

const setLoading = (value) => {
    pLoading.value = value;
};

const slots = useSlots();

onMounted(async () => {
    if (props.api) {
        cargarDatos();
    }
});

defineExpose({
    cargarDatos,
    setLoading,
});
</script>
<template>
    <div class="mi-table" :class="[$attrs.class]">
        <div
            class="content-table"
            :style="{
                maxHeight: tableHeight ? tableHeight : '',
                width: tableWidth ? tableWidth : '',
            }"
            :class="[pLoading ? 'mi-loading-table' : '']"
            ref="eContentTable"
        >
            <table
                class="table table-bordered mb-0"
                :class="[
                    tableClass,
                    tableResponsive ? 'table-resposive' : '',
                    fixedHeader ? 'tablaFixeada' : '',
                ]"
                ref="miTableRef"
            >
                <thead :class="[headerClass]" ref="miTheadRef">
                    <template v-if="$slots.tableHeader">
                        <slot name="tableHeader"></slot>
                    </template>
                    <template v-else>
                        <tr>
                            <th
                                v-for="(item, index) in listCols"
                                :colspan="`${item.colspan ? item.colspan : 1}`"
                                :class="[
                                    item.fixed
                                        ? item.fixed == 'right'
                                            ? 'fixed-column-right'
                                            : 'fixed-column'
                                        : '',
                                    fixedHeader ? 'fixed-header' : '',
                                ]"
                                :ref="(el) => (thtdRefs[`th-${index}`] = el)"
                                :data-width="item.width ? item.width : ''"
                                :style="getRightLetfStick(index, item, true)"
                            >
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
                <tbody
                    :class="[bodyClass, pLoading ? 'mi-loading-table' : '']"
                    ref="eTbody"
                >
                    <div class="mi-loading-table" v-show="pLoading">
                        <div>
                            <template v-if="$slots.loading">
                                <slot name="loading"></slot>
                            </template>
                            <template v-else> {{ textCargando }} </template>
                        </div>
                    </div>
                    <template v-if="listItems.length > 0">
                        <tr
                            v-for="(item, index_row) in listItems"
                            :class="getRowClass(item)"
                        >
                            <td
                                v-for="(i_col, index_col) in listCols"
                                :data-label="i_col.label"
                                :class="[
                                    i_col.classTd ? i_col.classTd(item) : '',
                                    i_col.fixed
                                        ? i_col.fixed == 'right'
                                            ? 'fixed-column-right'
                                            : 'fixed-column'
                                        : '',
                                ]"
                                :colspan="`${item.colspan ? item.colspan : 1}`"
                                :style="getRightLetfStick(index_col, i_col)"
                                :ref="
                                    (el) =>
                                        (thtdRefs[
                                            `td-${index_row}-${index_col}`
                                        ] = el)
                                "
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
                            <td :colspan="listCols.length" class="text-center">
                                {{ textSinRegistros }}
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <template v-if="$slots.tableFooter">
                        <slot name="tableFooter"></slot>
                    </template>
                </tfoot>
            </table>
        </div>
        <div class="content-foot px-3 pt-2" v-if="conPaginacion">
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
                    Mostrando {{ cDeRegistros }} a {{ cARegistros }} registros -
                    Total {{ total }} registros
                </div>
            </div>
            <div v-if="error" class="alert alert-danger">
                Ocurrió un error al intentar obtener los registros
            </div>
        </div>
    </div>
</template>
<style scoped>
.mi-table {
    width: 100%;
}

.mi-table .content-table {
    overflow: auto;
    position: relative;
}
.mi-table .content-table table {
    margin: 0px;
}

.mi-table .content-table tbody {
    position: relative;
}

.mi-table .content-table td,
.mi-table .content-table th {
    transition: left 0.1s ease-in, right 0.1s ease-out;
}

.mi-table .content-foot {
    border-top: solid 1px rgb(216, 216, 216);
}

.mi-table table thead {
    transition: all 0.3s;
}

.mi-table table thead tr th .iheader {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    gap: 3px;
}

.mi-table table thead tr th .iheader.sortable {
    cursor: pointer;
}
.mi-table table thead tr th .iheader .accion {
    color: rgba(0, 0, 0, 0.3);
}
.mi-table table thead tr th .iheader .accion.active {
    color: black;
    display: block;
}

.mi-table .content-table.mi-loading-table {
    overflow: hidden;
}

.mi-table .content-table .mi-loading-table {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10;
    display: flex;
    justify-content: center;
    align-items: center;
}

.mi-table .content-table .mi-loading-table::before {
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

/* FIXEDS */
.mi-table .content-table table.tablaFixeada {
    border-collapse: separate;
    border-spacing: 0;
    white-space: nowrap;
    table-layout: fixed;
}

.mi-table .content-table table.tablaFixeada td,
.mi-table .content-table table.tablaFixeada th {
    border: solid 1px rgb(250, 250, 250);
    border-bottom: solid 1px rgb(211, 211, 211);
}

.mi-table .fixed-column-ext,
.mi-table .fixed-column-ext-right,
.mi-table .fixed-column,
.mi-table .fixed-column-right {
    position: sticky;
    z-index: 2;
    background-color: white;
}

.mi-table .fixed-header,
.mi-table .footer-fixed {
    position: sticky;
    top: 0;
    z-index: 1;
    background-color: white;
}

.mi-table .fixed-header.fixed-column,
.mi-table .fixed-header.fixed-column-right,
.mi-table th.fixed-column,
.mi-table th.fixed-column-right {
    z-index: 3;
}

@media screen and (max-width: 790px) {
    .mi-table table.table-resposive thead {
        display: none;
    }

    .mi-table table.table-resposive tbody tr:nth-child(odd) {
        background-color: rgb(233, 233, 233);
    }

    .mi-table table.table-resposive tbody tr td {
        display: block;
        font-size: 0.8rem;
    }

    .mi-table table.table-resposive tbody tr td::before {
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
