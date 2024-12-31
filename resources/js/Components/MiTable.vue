<script setup>
import axios from "axios";
import MiPaginacion from "@/Components/MiPaginacion.vue";
import {
    ref,
    onMounted,
    onUpdated,
    onUnmounted,
    watch,
    nextTick,
    useSlots,
} from "vue";
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
    widthFixDefault: {
        type: Number,
        String,
        default: 180,
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
const intervalSearch = ref(null);

watch(
    () => props.syncOrderBy,
    (newVal) => {
        orderBy.value = newVal;
        // console.log("watch 1");
        cargarDatos();
    }
);

watch(
    () => props.syncOrderAsc,
    (newVal) => {
        orderAsc.value = newVal ? newVal.toLowerCase() : null;
        // console.log("watch 2");
        cargarDatos();
    }
);

watch(
    () => props.loading,
    (newVal) => {
        // console.log("watch 3");
        pLoading.value = newVal;
    }
);

watch(
    () => props.multiSearch,
    (newVal) => {
        // console.log("watch 4");
        oMultiSearch.value = newVal;
        cargarDatos();
    },
    { deep: true }
);

watch(
    () => props.search,
    (newVal) => {
        // console.log("watch 5");
        tSearch.value = newVal;
        clearInterval(intervalSearch.value);
        intervalSearch.value = setTimeout(() => {
            currentPage.value = 1;
            cargarDatos();
        }, props.delaySearch);
    }
);

watch(per_page, (newValue, oldValue) => {
    // console.log("watch 6");
    cambiaPerPage();
});

watch(
    () => props.data,
    (newVal) => {
        // console.log("watch 7");
        listData.value = newVal;
        total.value = listData.value.length;
        cargarDatos();
    }
);

// watch(
//     () => listItems.value,
//     async (newVal) => {
//     }
// );

// funcion para determinar si se cargaron todos los elementos
const esperarCargaElementos = () => {
    return new Promise((r) => window.requestAnimationFrame(r));
};

/**
 * *****************************************
 *  FUNCIONES PARA LA CARGA DE DATOS
 */

// funcion general para generar los datos que se muestran en la tabla
// ya sea via URL o el Listado pasado en propiedades
const cargarDatos = async () => {
    listItems.value = [];
    setLoading(true);
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
    fijarAltoContenedorCambioPagina();

    setLoading(false);
};

// cargar registros por URL axios
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
        setLoading(false);
        error.value = true;
        console.log(err);
        return null;
    }
};
// generar el listado de datos sin uso de una api
const generarDatosPorLista = async () => {
    return new Promise(async (resolve, reject) => {
        try {
            const page = parseInt(currentPage.value);
            const pageSize = parseInt(per_page.value);
            const vOrderBy = orderBy.value;
            const vOrderAsc = orderAsc.value;

            // Filtra el array por el valor de búsqueda en cualquier columna
            let filteredArray = await listData.value.filter((item) => {
                return Object.values(item).some((value) =>
                    value
                        .toString()
                        .toLowerCase()
                        .includes(tSearch.value.toLowerCase())
                );
            });

            total.value = filteredArray.length;
            totalPages.value = Math.ceil(total.value / per_page.value);

            // Aplica el ordenamiento según orderBy y orderAsc
            if (vOrderAsc) {
                filteredArray = filteredArray.sort((a, b) => {
                    // Obtener la configuración de la columna por su key
                    let columnConfig = listCols.value.find(
                        (col) => col.key === vOrderBy
                    );
                    if (!columnConfig) {
                        return 0;
                    }

                    // Obtener valores a comparar
                    let valA = getColumnValue(a, vOrderBy) ?? "";
                    let valB = getColumnValue(b, vOrderBy) ?? "";
                    // Si el tipo es Number, convertir los valores
                    if (columnConfig.type && columnConfig.type.toLowerCase() === "number") {
                        valA = parseFloat(valA) || 0; // Asegurar que sea un número
                        valB = parseFloat(valB) || 0;
                    } else if (columnConfig.type && columnConfig.type.toLowerCase() === "date") {
                        // Convertir fechas al formato YYYY-MM-DD
                        valA = formatDateToISO(valA);
                        valB = formatDateToISO(valB);
                        // Intentar convertir las fechas a milisegundos
                        valA = isValidDate(valA) ? new Date(valA).getTime() : 0;
                        valB = isValidDate(valB) ? new Date(valB).getTime() : 0;
                    } else if (
                        typeof valA === "string" &&
                        typeof valB === "string"
                    ) {
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
                listaFiltrada = filteredArray.slice(
                    startIndex,
                    startIndex + pageSize
                );
            }

            // Devuelve la sección del array filtrado, ordenado y paginado
            resolve(listaFiltrada);
        } catch (error) {
            reject(error);
        }
    });
};

// Función para validar formatos de fecha
function isValidDate(dateString) {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
}
// Función para convertir fechas de DD/MM/YYYY a YYYY-MM-DD
function formatDateToISO(dateString) {
    if (/\d{2}\/\d{2}\/\d{4}/.test(dateString)) {
        const [day, month, year] = dateString.split("/");
        return `${year}-${month}-${day}`;
    }
    return dateString; // Si ya está en formato ISO, devolverlo tal cual
}

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
    cargarDatos();
};

// Mostrar la cantidad de registgros encontrados
// segun la página y total
const muestraCantidadRegistros = () => {
    const startIndex = (parseInt(currentPage.value) - 1) * per_page.value;
    const total_reg = listItems.value.length - 1;
    cDeRegistros.value = startIndex + 1; // Primer registro de la página
    cARegistros.value = cDeRegistros.value + total_reg; // Último registro de la página
};

// obtener el valor anidado de la lista de items
function getColumnValue(obj, key) {
    return key.split(".").reduce((acc, part) => acc && acc[part], obj);
}

// usar la funcion getRowClass enviado desde la lista de columnas
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

// verificar la opcion activa de ordenacion en el header
function getClassActiveSort(item) {
    if (props.api) {
        return (
            orderBy.value == (item.keySortable ? item.keySortable : item.key)
        );
    }
    return orderBy.value == item.key;
}

// setear el estado de loading del contenido de la tabla
const setLoading = (value) => {
    pLoading.value = value;
};
/**
 * FIN FUNCIONES CARGA DE DATOS
 * ******************************************************
 */

/********************************************************
 * ******************************************************
 *  FUNCIONES Y VARIABLES DE RENDERIZACIÓN DE TABLA
 */
const anchoEstablecidoRender = ref(0);
const eTbody = ref(null);
const contentScroll = ref(null);
const eContentTable = ref(null);
const eContentHeader = ref(null);
const tableHeaderGroup = ref(null);
const tableContentGroup = ref(null);
const thtdRefs = ref({});
const miTableRef = ref(null);
const miTableHeaderRef = ref(null);
const miTheadRef = ref(null);
const widthColumnsFix = ref([]);
const miLoading = ref(null);
const anchoDefaultNotFound = ref(170);

// obtener el ancho de celda de un porcentaje en pixeles
const getAnchoPorcentajeAPx = (totalPx, porcentaje) => {
    let ancho = 0;
    if (totalPx && porcentaje) {
        ancho = (porcentaje * totalPx) / 100;
    }
    return ancho;
};

const establecerDimensionesContenedores = async () => {
    await nextTick();
    await esperarCargaElementos();
    console.log("redimencionando");

    anchoEstablecidoRender.value = miTable.value
        ? miTable.value.offsetWidth
        : 0;
    await establecerAnchosContenedores();
    await establecerAnchoColumnGroup();
    await establecerColumnasFixedSlot();

    // scroll
    syncScrollBodyHeader();
    // alto del contenedor
    establecerAltoContenedor();
};

// Fijar el alto del contenedor antes de cambiar de pagina
const fijarAltoContenedorCambioPagina = () => {
    if (!props.tableHeight) {
        eContentTable.value.style.minHeight = "";
        setTimeout(() => {
            if (window.innerWidth > 790) {
                eContentTable.value.style.minHeight = `${contentScroll.value.offsetHeight}px`;
            }
        }, 200);
    }
};

// Fijar el alto que tendra el contenedor segun la propiedad tableHeight
const establecerAltoContenedor = () => {
    if (props.tableHeight) {
        eContentTable.value.style.height = `${props.tableHeight}`;
        eContentTable.value.style.minHeight = `${props.tableHeight}`;
    }

    resetPositionScroll();
    setTimeout(() => {
        updateScrollbars();
    }, 300);
};

// Fjar el ancho que tendran los contenedores
const establecerAnchosContenedores = () => {
    return new Promise((resolve, reject) => {
        try {
            if (anchoEstablecidoRender.value > 0) {
                contentScroll.value.style.width =
                    anchoEstablecidoRender.value + "px";
                eContentTable.value.style.width =
                    anchoEstablecidoRender.value + "px";
                eContentHeader.value.style.width =
                    anchoEstablecidoRender.value + "px";
                eContentTable.value.style.maxWidth =
                    anchoEstablecidoRender.value + "px";
                eContentHeader.value.style.maxWidth =
                    anchoEstablecidoRender.value + "px";
                eContentTable.value.querySelector("table").style.width =
                    anchoEstablecidoRender.value + "px";
                eContentTable.value.querySelector("table").style.maxWidth =
                    anchoEstablecidoRender.value + "px";
                eContentHeader.value.querySelector("table").style.width =
                    anchoEstablecidoRender.value + "px";
                eContentHeader.value.querySelector("table").style.maxWidth =
                    anchoEstablecidoRender.value + "px";
            }
            setTimeout(() => {
                resolve();
            }, 150);
        } catch (error) {
            reject(error);
        }
    });
};

// calcular anchos % o vw
const getCalculoAnchoPVW = (width, el) => {
    const unidad_val = obtenerUnidadValor(width);
    let resultado = unidad_val[1];
    if (unidad_val[0] && unidad_val[0] != "px") {
        resultado = (resultado * anchoEstablecidoRender.value) / 100;
    }
    return parseFloat(resultado);
};
function obtenerUnidadValor(valor) {
    const coincidencia = valor.match(/^(\d+)(px|%)$/);
    let unidad = null;
    let width = valor;
    if (coincidencia) {
        unidad = coincidencia[2] || null;
        width = coincidencia[1];
    }
    return [unidad, width];
}

// Fijar el ancho de las columnas de la tabla
const establecerAnchoColumnGroup = () => {
    return new Promise((resolve, reject) => {
        try {
            const listWidths = listCols.value.map((elemento, indice) =>
                elemento.hasOwnProperty("width")
                    ? getCalculoAnchoPVW(elemento.width, elemento)
                    : 0
            );

            const listIndex = listCols.value.map((elemento, indice) =>
                elemento.hasOwnProperty("width") ? indice : -1
            );
            // indice de columnas con anchos pre establecidos
            const i_cols_width = listIndex.filter((indice) => indice !== -1);
            const restantes = listCols.value.length - i_cols_width.length;
            const total_definidos = listWidths.reduce((a, b) => {
                return parseFloat(a) + parseFloat(b);
            }, 0);

            let ancho_general =
                (anchoEstablecidoRender.value - total_definidos - 1) /
                restantes;

            const colsHeader = tableHeaderGroup.value.querySelectorAll("col");
            const colsHeaderTh = eContentHeader.value.querySelectorAll("th");
            if (colsHeader.length > 0) {
                colsHeader.forEach((elemCol, indexCol) => {
                    // ancho
                    if (i_cols_width.includes(indexCol)) {
                        widthColumnsFix.value[indexCol] = listWidths[indexCol];
                        elemCol.style.width = listWidths[indexCol] + "px";
                    } else {
                        if (props.fixCols) {
                            if (props.widthFixDefault > 0) {
                                ancho_general = props.widthFixDefault;
                            } else {
                                const elem_th = colsHeaderTh[indexCol];
                                if (elem_th) {
                                    const ancho_th = elem_th.offsetWidth;
                                    const elem_div =
                                        elem_th.querySelector("div");
                                    if (elem_div) {
                                        ancho_general = elem_div.offsetWidth;
                                    } else {
                                        ancho_general =
                                            ancho_th < 15
                                                ? ancho_th + 15
                                                : ancho_th; // definir el minimo
                                    }
                                } else {
                                    // en caso de que no existe o no se encuentre el elemento este tendra
                                    // el ancho por default
                                    ancho_general = anchoDefaultNotFound.value;
                                }
                            }
                        }
                        widthColumnsFix.value[indexCol] = ancho_general;
                        elemCol.style.width = ancho_general + "px";
                    }
                });
            }

            // incorporar el colgroup del header en el contenido
            tableContentGroup.value.innerHTML =
                tableHeaderGroup.value.innerHTML;
            setTimeout(() => {
                resolve();
            }, 150);
        } catch (error) {
            reject(error);
        }
    });
};

// funcion para establecer los estilos siempre y cuando
// el item sea fixed
const getStyleColumnFixed = (item, indexCol) => {
    let styles = null;
    if (item.fixed) {
        styles = {
            position: "sticky",
        };
        if (item.fixed == "right") {
            styles["right"] = calcularDistanciaPosicionRL(indexCol, "r");
        } else {
            styles["left"] = calcularDistanciaPosicionRL(indexCol, "l");
        }
    }

    return styles;
};

const establecerColumnasFixedSlot = () => {
    return new Promise((resolve, reject) => {
        try {
            if (miTableRef.value) {
                const table = miTableRef.value;

                const listIzquierda =
                    table.querySelectorAll(".fixed-column-ext");
                const listDerechaIni = table.querySelectorAll(
                    ".fixed-column-ext-right"
                );
                const listDerecha = [...listDerechaIni].reverse();
                let distancia_acum = 0;
                listIzquierda.forEach((elem) => {
                    elem.style.position = "sticky";
                    elem.style.left = distancia_acum + "px";
                    if (elem.classList.contains("footer-fixed")) {
                        elem.style.bottom = "1px";
                    }
                    elem.style.left = distancia_acum + "px";
                    distancia_acum += parseFloat(elem.offsetWidth);
                });

                distancia_acum = 0;
                listDerecha.forEach((elem) => {
                    elem.style.position = "sticky";
                    elem.style.right = distancia_acum + "px";
                    if (elem.classList.contains("footer-fixed")) {
                        elem.style.bottom = "1px";
                    }
                    elem.style.right = distancia_acum + "px";
                    distancia_acum += parseFloat(elem.offsetWidth);
                });
            }
            resolve();
        } catch (error) {
            reject(error);
        }
    });
};

// funcion para calcular la distancia derecha|izquierda del elemento
const calcularDistanciaPosicionRL = (indexCol, rl) => {
    let inicio = 0;
    let fin = indexCol > 0 ? indexCol : 0;
    let tamanio_cols = listCols.value.length;
    let distancia = 0;
    let width_elems = [];
    if (rl == "r") {
        inicio = indexCol + 1 < tamanio_cols ? indexCol : tamanio_cols - 1;
        fin = tamanio_cols - 1;
        if (inicio >= tamanio_cols) {
            inicio = tamanio_cols - 1;
        }
    }
    width_elems = widthColumnsFix.value.slice(inicio, fin);
    distancia = width_elems.reduce((a, b) => {
        return a + b;
    }, 0);

    return distancia + "px";
};

/**
 * FIN FUNCIONES RENDER
 * ***********************************
 */

/************************************
 * **********************************
 * SCROLL
 **/
const scrollX = ref(null);
const scrollY = ref(null);
// Variables para el drag
const isDragging = ref(false);
const dragAxis = ref(null);
const startPos = ref(0);
const startScroll = ref(0);
const originalScrollYpx = ref(-1);
// resetear la posición de los scrollbars
const resetPositionScroll = () => {
    originalScrollYpx.value = -1;
    startPos.value = 0;
    if (eContentTable.value) {
        eContentTable.value.scrollLeft = 0;
        eContentTable.value.scrollTop = 0;
    }
    if (scrollX.value) {
        scrollX.value.style.left = 0 + "px";
    }
    if (scrollY.value) {
        scrollY.value.style.top = 0 + "px";
    }
};
// actualizar el tamaño del elemento de scroll (track)
const updateScrollbars = () => {
    if (eContentTable.value) {
        if (eContentTable.value.scrollWidth > eContentTable.value.offsetWidth) {
            // El ancho de la barra de scroll visible en X
            let anchoScrollX =
                eContentTable.value.offsetWidth /
                eContentTable.value.scrollWidth;
            anchoScrollX *= 100;
            scrollX.value.style.width = anchoScrollX + "%";
            scrollX.value.parentElement.style.display = "block";
        } else {
            scrollX.value.parentElement.style.display = "none";
        }

        if (
            eContentTable.value.scrollHeight > eContentTable.value.offsetHeight
        ) {
            // El ancho de la barra de scroll visible en Y
            let altoScrollY =
                eContentTable.value.offsetHeight /
                eContentTable.value.scrollHeight;
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
    }
};

// sincronizar el scroll X del contenedor con el header
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

    eContentHeader.value.addEventListener("scroll", (e) => {
        // Verifica si el contenedor tiene un scroll horizontal
        if (e.target.scrollWidth > e.target.clientWidth) {
            // Solo sincroniza el scroll si el contenedor tiene desplazamiento
            eContentTable.value.scrollLeft = e.target.scrollLeft;
            let positionLeftScroll =
                e.target.scrollLeft / eContentHeader.value.scrollWidth;
            scrollX.value.style.left =
                positionLeftScroll * (eContentHeader.value.offsetWidth - 5) +
                "px";
        } else {
            eContentHeader.value.removeEventListener(
                "scroll",
                syncScrollBodyHeader
            );
        }
    });
};

// iniciar el drag del scroll
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

// detectar el movimiento del scroll
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

// detener el scroll
const stopDrag = () => {
    // Habilitar la selección de texto nuevamente
    document.body.classList.remove("no-select");
    removerEventosScroll();
};

// remover los eventos de scroll
const removerEventosScroll = () => {
    document.removeEventListener("mousemove", handleMouseMove);
    document.removeEventListener("mouseup", stopDrag);
};

/**
 * fin SCROLL
 ***************************************
 **/

const observerContentMiTable = ref(null);
const anchoAnteriorMiTable = ref(0);
const intervalRenderWindow = ref(null);
const intervalRenderContent = ref(null);
const miTable = ref(null);

const actualizaDimensionesVentana = async () => {
    clearInterval(intervalRenderWindow.value);
    intervalRenderWindow.value = setTimeout(() => {
        establecerDimensionesContenedores();
        clearInterval(intervalRenderContent.value);
        // console.log("resize window");
    }, 400);
};

onUpdated(() => {
    clearInterval(intervalRenderWindow.value);
    if (anchoEstablecidoRender.value != miTable.value.offsetWidth) {
        intervalRenderWindow.value = setTimeout(() => {
            establecerDimensionesContenedores();
            clearInterval(intervalRenderContent.value);
            // console.log("update");
        }, 400);
    }
});

onMounted(async () => {
    if (props.api) {
        cargarDatos();
    }
    establecerDimensionesContenedores();
    window.addEventListener("resize", actualizaDimensionesVentana);

    // Iniciar observer content mitable
    observerContentMiTable.value = new ResizeObserver(async (entries) => {
        clearInterval(intervalRenderContent.value);
        if (entries.length > 0) {
            if (anchoAnteriorMiTable.value == 0) {
                anchoAnteriorMiTable.value =
                    entries[entries.length - 1].contentRect.width;
            }
            if (miTable.value) {
                if (anchoAnteriorMiTable.value != miTable.value.offsetWidth) {
                    anchoAnteriorMiTable.value =
                        entries[entries.length - 1].contentRect.width;
                    if (window.innerWidth > 790) {
                        intervalRenderContent.value = setTimeout(() => {
                            establecerDimensionesContenedores();
                            // console.log("resize content");
                        }, 700);
                    }
                }
            }
        }
    });
    if (miTable.value) {
        observerContentMiTable.value.observe(miTable.value);
    }
});

onUnmounted(() => {
    window.removeEventListener("resize", actualizaDimensionesVentana);
    // Limpiar observer
    if (observerContentMiTable.value && contentScroll.value) {
        observerContentMiTable.value.unobserve(contentScroll.value);
    }
    observerContentMiTable.value = null;
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
        ref="miTable"
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
                                :style="getStyleColumnFixed(item, index)"
                                :data-width="item.width ? item.width : ''"
                            >
                                <div
                                    class="iheader sortable"
                                    v-if="item.sortable"
                                    @click="
                                        changeOrderBy(
                                            api
                                                ? item.keySortable
                                                    ? item.keySortable
                                                    : item.key
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
                                                        (api
                                                            ? item.keySortable
                                                                ? item.keySortable
                                                                : item.key
                                                            : item.key),
                                                'fa-sort-amount-down':
                                                    orderAsc == 'desc' &&
                                                    orderBy ==
                                                        (api
                                                            ? item.keySortable
                                                                ? item.keySortable
                                                                : item.key
                                                            : item.key),
                                                'fa-sort':
                                                    !orderAsc ||
                                                    orderBy !=
                                                        (api
                                                            ? item.keySortable
                                                                ? item.keySortable
                                                                : item.key
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
        <div class="mi-content-scroll" ref="contentScroll">
            <div
                class="content-table"
                :style="{
                    maxHeight: tableHeight ? tableHeight : '',
                    width: tableWidth ? tableWidth : '',
                }"
                ref="eContentTable"
            >
                <div class="mi-loading-table" v-show="pLoading" ref="miLoading">
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
                    <colgroup ref="tableContentGroup"></colgroup>
                    <tbody
                        :class="[bodyClass, pLoading ? 'loading_active' : '']"
                        ref="eTbody"
                    >
                        <template v-if="listItems.length > 0">
                            <tr
                                v-for="(item, index_row) in listItems"
                                :class="getRowClass(item)"
                            >
                                <td
                                    v-for="(i_col, index_col) in listCols"
                                    :class="[
                                        i_col.fixed
                                            ? i_col.fixed == 'right'
                                                ? 'fixed-column-right'
                                                : 'fixed-column'
                                            : '',
                                    ]"
                                    :colspan="`${
                                        item.colspan ? item.colspan : 1
                                    }`"
                                    :style="
                                        getStyleColumnFixed(i_col, index_col)
                                    "
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
                                    <div
                                        class="mi-celda"
                                        :class="[
                                            i_col.classTd
                                                ? i_col.classTd(item)
                                                : '',
                                        ]"
                                    >
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
