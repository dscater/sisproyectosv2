export const fHelpers = () => {
    function getFechaActual() {
        const fecha = new Date();
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // Sumamos 1 al mes ya que los meses en JavaScript van de 0 a 11
        const dia = String(fecha.getDate()).padStart(2, "0");
        return `${año}-${mes}-${dia}`;
    }

    function sumarDiasFecha(fecha_inicio, dias) {
        var d = new Date(fecha_inicio + "T00:00:00");
        let fecha_entrega = sumarDias(d, parseInt(dias));
        let nueva_fecha = fecha_entrega.getFullYear() + "-";
        let mes = parseInt(fecha_entrega.getMonth()) + 1;
        nueva_fecha += (mes < 10 ? "0" + mes : mes) + "-";
        nueva_fecha +=
            fecha_entrega.getDate() < 10
                ? "0" + fecha_entrega.getDate()
                : fecha_entrega.getDate();
        return nueva_fecha;
    }

    function sumarDias(fecha, dias) {
        fecha.setDate(fecha.getDate() + dias);
        return fecha;
    }
    return {
        getFechaActual,
        sumarDiasFecha,
    };
};
