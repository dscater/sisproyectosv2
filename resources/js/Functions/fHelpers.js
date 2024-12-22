export const fHelpers = () => {
    function getFechaActual() {
        const fecha = new Date();
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // Sumamos 1 al mes ya que los meses en JavaScript van de 0 a 11
        const dia = String(fecha.getDate()).padStart(2, "0");
        return `${año}-${mes}-${dia}`;
    }
    function sumarDias(fecha, dias) {
        fecha.setDate(fecha.getDate() + dias);
        return fecha;
    }
    return {
        getFechaActual,
        sumarDias,
    };
};
