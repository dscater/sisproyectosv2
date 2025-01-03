export const fHelpers = () => {
    function getAnioActual() {
        const fecha = new Date();
        const año = fecha.getFullYear();
        return `${año}`;
    }
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

    const getMontoCambio = (moneda_id, monto, oTipoCambio) => {
        const moneda1_id = oTipoCambio.moneda1_id;
        const valor1 = oTipoCambio.valor1;
        const valor2 = oTipoCambio.valor2;
        let nuevo_monto = monto;
        // verificar la moneda seleccionada con las monedas de cambio
        if (moneda1_id == moneda_id) {
            // es igual a la moneda 1
            if (valor1 > valor2) {
                nuevo_monto = parseFloat(monto) / parseFloat(valor1);
            } else if (valor2 > valor1) {
                nuevo_monto = parseFloat(monto) * parseFloat(valor2);
            }
        } else {
            if (valor1 > valor2) {
                nuevo_monto = parseFloat(monto) * parseFloat(valor1);
            } else if (valor2 > valor1) {
                nuevo_monto = parseFloat(monto) / parseFloat(valor2);
            }
        }
        return parseFloat(parseFloat(nuevo_monto).toFixed(2)) ?? 0;
    };

    // verificar redondeo
    function contarDecimales(numero) {
        const partes = numero.toString().split(".");
        return partes[1] ? partes[1].length : 0;
    }
    function redondear(numero, decimales) {
        const factor = Math.pow(10, decimales);
        return Math.round(numero * factor) / factor;
    }

    const getFormatoMoneda = (valor) => {
        return new Intl.NumberFormat("es-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(valor);
    };

    return {
        getFechaActual,
        getAnioActual,
        sumarDiasFecha,
        getMontoCambio,
        contarDecimales,
        redondear,
        getFormatoMoneda,
    };
};
