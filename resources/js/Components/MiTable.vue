<script setup>
import axios from "axios";
import MiPaginacion from "@/Components/MiPaginacion.vue";
import { ref, onMounted, onUnmounted, watch, nextTick, useSlots } from "vue";
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
const eContentHeader = ref(null);
const tableHeaderGroup = ref(null);
const tableContentGroup = ref(null);
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
        if (listItems.value.length > 0) await renderMiTable();
    }
);

// FUNCION QUE CONTIENE LAS FUNCIONES PARA RENDERIZAR LA TABLA
const renderMiTable = async () => {
    await nextTick();
    await esperarCargaElementos();
    actualizaAltoAnchoTablaColumnas();
};

// funcion para otener los datos de renderizado, alto tabla y ancho columnas
const actualizaAltoAnchoTablaColumnas = () => {
    if (listItems.value.length == 0) return;
    if (eContentTable.value) {
        if (props.fixCols) {
            initColsWidthFixed();
        } else {
            initColsWidthNotFixed();
        }
    }
    if (props.fixCols) {
        renderColumnsStyleFixed();
    }
    if (eContentTable.value) {
        let altura = 0;
        let ancho = 0;
        if (props.tableHeight) {
            eContentTable.value.style.height = `${props.tableHeight}`;
            eContentTable.value.style.minHeight = `${props.tableHeight}`;
            altura = eContentTable.value.getBoundingClientRect().height;
            ancho = eContentTable.value.getBoundingClientRect().width;
        } else {
            altura = miTableRef.value.getBoundingClientRect().height;
            ancho = miTableRef.value.getBoundingClientRect().width;
        }
        eContentTable.value.style.height = `${altura}px`;
        eContentTable.value.style.minHeight = `${altura}px`;
        eContentTable.value.style.width = `${eContentTable.value.offsetWidth}px`;
        eContentTable.value.style.maxWidth = `${eContentTable.value.offsetWidth}px`;
        eContentHeader.value.style.width = `${eContentTable.value.offsetWidth}px`;
        eContentHeader.value.style.maxWidth = `${eContentTable.value.offsetWidth}px`;
    }
    resetPositionScroll();
    updateScrollbars();
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

    pLoading.value = false;
};

// generar anchos de celdas con propiedad de columnas fixedas
const initColsWidthFixed = () => {
    widthColumnsFix.value = [];
    miTableRef.value.style.tableLayout = "auto";
    miTableHeaderRef.value.style.tableLayout = "auto";
    const cols = miTableHeaderRef.value
        .querySelector("thead tr")
        .querySelectorAll("th");
    cols.forEach((elem, index) => {
        let contentWidth = 0;
        const data_width = elem.getAttribute("data-width");
        if (data_width) {
            const regex = /px/; // Busca px, %, o vw
            const unidad = data_width.match(regex); // Encuentra la unidad en el valor
            contentWidth = data_width;
            if (unidad == "px") {
                contentWidth = data_width.replace("px", "");
            } else {
                contentWidth = getAnchoPorcentajeAPx(
                    eContentTable.value.getBoundingClientRect().width,
                    contentWidth
                );
            }
        } else {
            contentWidth = elem.getBoundingClientRect().width + 15;
        }
        widthColumnsFix.value[index] = contentWidth;
        const colsContentHeader =
            tableHeaderGroup.value.querySelectorAll("col");
        colsContentHeader[index].style.width = contentWidth + "px";
        const colsContentBody = tableContentGroup.value.querySelectorAll("col");
        colsContentBody[index].style.width = contentWidth + "px";
    });

    miTableRef.value.style.tableLayout = "fixed";
    miTableHeaderRef.value.style.tableLayout = "fixed";
};

// generar anchos de celdas con columnas sin fixear
const initColsWidthNotFixed = () => {
    widthColumnsFix.value = [];
    miTableRef.value.style.tableLayout = "auto";
    miTableHeaderRef.value.style.tableLayout = "auto";
    const cols = miTableHeaderRef.value
        .querySelector("thead tr")
        .querySelectorAll("th");
    let acumulados_no_fixed = 0;
    let contador_cols_data_width = 0;
    cols.forEach((elem, index) => {
        let contentWidth = 0;
        const data_width = elem.getAttribute("data-width");
        if (data_width) {
            const regex = /px/; // Busca px, %, o vw
            const unidad = data_width.match(regex); // Encuentra la unidad en el valor

            contentWidth = data_width;
            if (unidad == "px") {
                contentWidth = data_width.replace("px", "");
            } else {
                contentWidth = getAnchoPorcentajeAPx(
                    eContentTable.value.getBoundingClientRect().width,
                    contentWidth
                );
            }
            acumulados_no_fixed += parseFloat(contentWidth);
            widthColumnsFix.value[index] = contentWidth;
            contador_cols_data_width++;
            const colsContentHeader =
                tableHeaderGroup.value.querySelectorAll("col");
            colsContentHeader[index].style.width = contentWidth + "px";
            const colsContentBody =
                tableContentGroup.value.querySelectorAll("col");
            colsContentBody[index].style.width = contentWidth + "px";
        }
    });

    const restanteWidth =
        eContentTable.value.getBoundingClientRect().width - acumulados_no_fixed;
    const restantes = cols.length - contador_cols_data_width;
    const widthCols = Math.fround(restanteWidth / restantes) - 1;
    cols.forEach((elem, index) => {
        const data_width = elem.getAttribute("data-width");
        if (!data_width) {
            widthColumnsFix.value[index] = widthCols;
            const colsContentHeader =
                tableHeaderGroup.value.querySelectorAll("col");
            colsContentHeader[index].style.width = widthCols + "px";
            const colsContentBody =
                tableContentGroup.value.querySelectorAll("col");
            colsContentBody[index].style.width = widthCols + "px";
        }
    });
    miTableRef.value.style.tableLayout = "fixed";
    miTableHeaderRef.value.style.tableLayout = "fixed";
};

// obtener el ancho de celda de un porcentaje en pixeles
const getAnchoPorcentajeAPx = (totalPx, porcentaje) => {
    let ancho = 0;
    if (totalPx && porcentaje) {
        ancho = (porcentaje * totalPx) / 100;
    }
    return ancho;
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
        if (item.fixed == "right") {
            pStyle["right"] = `${lefright}px`;
        } else {
            pStyle["left"] = `${lefright}px`;
        }
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
    pLoading.value = true;
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
            // Obtener la configuración de la columna por su key
            const columnConfig = listCols.value.find(
                (col) => col.key === vOrderBy
            );

            if (!columnConfig) {
                // Si no se encuentra la configuración, no ordenar
                return 0;
            }

            // Obtener valores a comparar
            let valA = a[vOrderBy];
            let valB = b[vOrderBy];

            // Si el tipo es Number, convertir los valores
            if (columnConfig.type === Number) {
                valA = parseFloat(valA) || 0; // Asegurar que sea un número
                valB = parseFloat(valB) || 0;
            } else if (typeof valA === "string" && typeof valB === "string") {
                // Si son cadenas, convertir a minúsculas
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }

            // Comparar valores
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
const miTableHeaderRef = ref(null);
const miTheadRef = ref(null);
// Funcion para renderizar las columnas con position sticky|posicion fixeada
// adjuntadas por un slot (thead,tfooter)
const renderColumnsStyleFixed = () => {
    const table = miTableRef.value;
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

/***********
 * SCROLL
 **********/
const scrollX = ref(null);
const scrollY = ref(null);

// Variables para el drag
const isDragging = ref(false);
const dragAxis = ref(null);
const startPos = ref(0);
const startScroll = ref(0);
const originalScrollYpx = ref(-1);

// Actualizar el tamaño y la posición de los scrollbars
const resetPositionScroll = () => {
    originalScrollYpx.value = -1;
    startPos.value = 0;
    eContentTable.value.scrollLeft = 0;
    eContentTable.value.scrollTop = 0;
    scrollX.value.style.left = 0 + "px";
    scrollY.value.style.top = 0 + "px";
};
const updateScrollbars = () => {
    if (eContentTable.value.scrollWidth > eContentTable.value.offsetWidth) {
        // El ancho de la barra de scroll visible en X
        let anchoScrollX =
            eContentTable.value.offsetWidth / eContentTable.value.scrollWidth;
        anchoScrollX *= 100;
        scrollX.value.style.width = anchoScrollX + "%";
        scrollX.value.parentElement.style.display = "block";
    } else {
        scrollX.value.parentElement.style.display = "none";
    }

    if (eContentTable.value.scrollHeight > eContentTable.value.offsetHeight) {
        // El ancho de la barra de scroll visible en Y
        let altoScrollY =
            eContentTable.value.offsetHeight / eContentTable.value.scrollHeight;
        altoScrollY *= 100;
        scrollY.value.style.height = altoScrollY + "%";
        originalScrollYpx.value = scrollY.value.offsetHeight;
        if (originalScrollYpx.value < 15 && originalScrollYpx.value > 0) {
            scrollY.value.style.height = "16px";
        }
        scrollY.value.parentElement.style.display = "block";
    } else {
        scrollY.value.parentElement.style.display = "none";
    }
    syncScrollBodyHeader();
};

const syncScrollBodyHeader = () => {
    eContentTable.value.addEventListener("scroll", (e) => {
        // Verifica si el contenedor tiene un scroll horizontal
        if (e.target.scrollWidth > e.target.clientWidth) {
            // Solo sincroniza el scroll si el contenedor tiene desplazamiento
            eContentHeader.value.scrollLeft = e.target.scrollLeft;
            let positionLeftScroll =
                e.target.scrollLeft / eContentTable.value.scrollWidth;
            scrollX.value.style.left =
                positionLeftScroll * (eContentTable.value.offsetWidth - 5) +
                "px";
        } else {
            eContentTable.value.removeEventListener(
                "scroll",
                syncScrollBodyHeader
            );
        }

        // Verifica si el contenedor tiene un scroll vertical
        if (e.target.scrollHeight > e.target.clientHeight) {
            // Solo sincroniza el scroll si el contenedor tiene desplazamiento
            eContentHeader.value.scrollTop = e.target.scrollTop;
            let positionTopScroll =
                e.target.scrollTop / eContentTable.value.scrollHeight;
            scrollY.value.style.top =
                positionTopScroll *
                    (eContentTable.value.offsetHeight -
                        (originalScrollYpx.value > -1 ? 16 : 5)) +
                "px";
        } else {
            eContentTable.value.removeEventListener(
                "scroll",
                syncScrollBodyHeader
            );
        }
    });
};

const startDrag = (axis, event) => {
    isDragging.value = true;
    dragAxis.value = axis;
    startPos.value = axis === "x" ? event.pageX : event.pageY;
    const container = eContentTable.value;
    startScroll.value =
        axis === "x" ? container.scrollLeft : container.scrollTop;

    document.body.classList.add("no-select");
    document.addEventListener("mousemove", handleMouseMove);
    document.addEventListener("mouseup", stopDrag);
};

const handleMouseMove = (event) => {
    if (isDragging.value === true) {
        dragAxis.value === "x";
        const mouseDifferential =
            (dragAxis.value === "x" ? event.pageX : event.pageY) -
            startPos.value;
        const container = eContentTable.value;

        let scrollEquivalent = 0;

        if (dragAxis.value === "x") {
            scrollEquivalent =
                mouseDifferential *
                (container.scrollWidth / container.offsetWidth);
            container.scrollLeft = startScroll.value + scrollEquivalent;
        } else {
            scrollEquivalent =
                mouseDifferential *
                (container.scrollHeight / container.offsetHeight);
            container.scrollTop = startScroll.value + scrollEquivalent;
        }
    }
};

const stopDrag = () => {
    // Habilitar la selección de texto nuevamente
    document.body.classList.remove("no-select");
    document.removeEventListener("mousemove", handleMouseMove);
    document.removeEventListener("mouseup", stopDrag);
};

/***********
 * fin SCROLL
 **********/

const cambioTamanioPantalla = async (e) => {
    // console.log(window.innerWidth);
    await renderMiTable();
};

onMounted(async () => {
    window.addEventListener("resize", cambioTamanioPantalla);
    if (props.api) {
        cargarDatos();
    }
});

onUnmounted(() => {
    window.removeEventListener("resize", cambioTamanioPantalla);
});

defineExpose({
    cargarDatos,
    setLoading,
});
</script>
<template>
    <div
        class="mi-table"
        :class="[$attrs.class, fixedHeader ? 'tablaFixeada' : '']"
    >
        <div class="mi-content-header" ref="eContentHeader">
            <table
                class="table table-bordered"
                :class="[
                    tableResponsive ? 'table-resposive' : '',
                    fixedHeader ? 'tablaFixeada' : '',
                ]"
                ref="miTableHeaderRef"
            >
                <colgroup ref="tableHeaderGroup">
                    <col v-for="item in listCols" />
                </colgroup>
                <thead :class="[headerClass]">
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
                                    <div class="label">
                                        {{ item.label }}
                                    </div>
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
                                    <div class="label">
                                        {{ item.label }}
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </template>
                </thead>
            </table>
        </div>
        <div class="mi-content-scroll">
            <div
                class="content-table"
                :style="{
                    maxHeight: tableHeight ? tableHeight : '',
                    width: tableWidth ? tableWidth : '',
                }"
                ref="eContentTable"
            >
                <div class="mi-loading-table" v-show="pLoading">
                    <div>
                        <template v-if="$slots.loading">
                            <slot name="loading"></slot>
                        </template>
                        <template v-else> {{ textCargando }} </template>
                    </div>
                </div>
                <table
                    class="table table-bordered mb-0"
                    :class="[
                        tableClass,
                        tableResponsive ? 'table-resposive' : '',
                        fixedHeader ? 'tablaFixeada' : '',
                    ]"
                    ref="miTableRef"
                >
                    <colgroup ref="tableContentGroup">
                        <col v-for="item in listCols" />
                    </colgroup>
                    <tbody :class="[bodyClass]" ref="eTbody" v-show="!pLoading">
                        <template v-if="listItems.length > 0">
                            <tr
                                v-for="(item, index_row) in listItems"
                                :class="getRowClass(item)"
                            >
                                <td
                                    v-for="(i_col, index_col) in listCols"
                                    :class="[
                                        i_col.classTd
                                            ? i_col.classTd(item)
                                            : '',
                                        i_col.fixed
                                            ? i_col.fixed == 'right'
                                                ? 'fixed-column-right'
                                                : 'fixed-column'
                                            : '',
                                    ]"
                                    :colspan="`${
                                        item.colspan ? item.colspan : 1
                                    }`"
                                    :style="getRightLetfStick(index_col, i_col)"
                                    :ref="
                                        (el) =>
                                            (thtdRefs[
                                                `td-${index_row}-${index_col}`
                                            ] = el)
                                    "
                                >
                                    <div
                                        class="label-responsive"
                                        v-text="`${i_col.label}:`"
                                    ></div>
                                    <div class="mi-celda">
                                        <template v-if="$slots[i_col.key]">
                                            <slot
                                                :name="i_col.key"
                                                :item="item"
                                                v-bind="$attrs"
                                            ></slot>
                                        </template>
                                        <template v-else>
                                            {{
                                                getColumnValue(item, i_col.key)
                                            }}
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td
                                    :colspan="listCols.length"
                                    class="text-center"
                                >
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
            <div class="content-scroll-x">
                <div
                    class="mi-custom-scroll-x"
                    @mousedown="startDrag('x', $event)"
                    ref="scrollX"
                ></div>
            </div>
            <div class="content-scroll-y">
                <div
                    class="mi-custom-scroll-y"
                    @mousedown="startDrag('y', $event)"
                    ref="scrollY"
                ></div>
            </div>
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
.mi-content-scroll {
    position: relative;
}

.content-scroll-x,
.content-scroll-y {
    opacity: 70%;
    position: absolute;
    background-color: transparent;
    z-index: 3;
}
.mi-table .mi-content-scroll:hover .content-scroll-x,
.mi-table .mi-content-scroll:hover .content-scroll-y {
    opacity: 100%;
}
.content-scroll-x {
    height: 10px;
    width: 100%;
    bottom: 0px;
}
.content-scroll-y {
    height: 100%;
    width: 10px;
    top: 0;
    right: 0;
}

.mi-custom-scroll-y,
.mi-custom-scroll-x {
    position: absolute;
    background: #cacaca;
    cursor: pointer;
    border-radius: 10px;
}

.mi-custom-scroll-y {
    margin-left: 2px;
    width: 8px;
    top: 0;
    right: 0px;
}

.mi-custom-scroll-x {
    margin-top: 2px;
    height: 8px;
    left: 0px;
}
</style>
