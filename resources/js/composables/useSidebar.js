import { onMounted, onUnmounted, ref, nextTick } from "vue";

const ancho_pantalla = ref(window.innerWidth);

export const useSideBar = () => {
    const cambioPantalla = () => {
        initClasesBody();
    };

    const initClasesBody = () => {
        ancho_pantalla.value = window.innerWidth;
        const body = document.querySelector("body");
        if (ancho_pantalla.value <= 990) {
            body.classList.add("sidebar-closed");
            body.classList.remove("sidebar-open");
            body.classList.add("sidebar-collapse");
        } else if (ancho_pantalla.value > 990 && ancho_pantalla.value < 1010) {
            body.classList.add("sidebar-collapse");
        }
    };

    const toggleSidebar = async () => {
        const body = document.querySelector("body");
        if (body) {
            if (ancho_pantalla.value > 1010) {
                if (body.classList.contains("sidebar-collapse")) {
                    // abrir
                    body.classList.remove("sidebar-collapse");
                } else {
                    body.classList.add("sidebar-collapse");
                }
            } else {
                if (body.classList.contains("sidebar-open")) {
                    body.classList.add("sidebar-collapse");
                    body.classList.add("sidebar-closed");
                    body.classList.remove("sidebar-open");
                } else {
                    // abrir
                    body.classList.remove("sidebar-collapse");
                    body.classList.remove("sidebar-closed");
                    body.classList.add("sidebar-open");
                }
            }
        }
    };

    const closeSidebar = () => {
        const body = document.querySelector("body");
        if (body) {
            if (ancho_pantalla.value <= 990) {
                if (body.classList.contains("sidebar-open")) {
                    body.classList.add("sidebar-collapse");
                    body.classList.add("sidebar-closed");
                    body.classList.remove("sidebar-open");
                }
            }
        }
    };

    const verificaSidebarAbierto = () => {
        const body = document.querySelector("body");
        if (body.classList.contains("sidebar-open")) {
            return true;
        }
        return false;
    };

    function handleClickOutside(event) {
        const toggleButton = document.querySelector(".toggleButton");
        const element = document.querySelector(".main-sidebar");
        if (element) {
            if (
                !element.contains(event.target) &&
                !toggleButton.contains(event.target)
            ) {
                closeSidebar();
            }
        }
    }

    onMounted(() => {
        initClasesBody();
        document.addEventListener("click", handleClickOutside);
        window.addEventListener("resize", cambioPantalla);
    });

    onUnmounted(() => {
        window.removeEventListener("resize", cambioPantalla);
        document.removeEventListener("click", handleClickOutside);
    });

    return {
        toggleSidebar,
        closeSidebar,
    };
};