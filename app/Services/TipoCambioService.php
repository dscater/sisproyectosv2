<?php

namespace App\Services;

use App\Models\Moneda;
use App\Models\TipoCambio;
use App\Models\Trabajo;

class TipoCambioService
{
    public function __construct() {}

    public function generarMontosCambioTrabajo($tipo_cambio_id, $moneda_seleccionada_id, $costo_original)
    {
        $montos_trabajo = [
            "costo" => $costo_original,
            "moneda_id" => $moneda_seleccionada_id,
            "costo_cambio" => $costo_original,
            "cancelado_cambio" => 0,
            "saldo_cambio" => $costo_original,
            "saldo" => $costo_original,
            "moneda_cambio_id" => 0
        ];

        if ($tipo_cambio_id != 0) {
            $moneda_principal = Moneda::where("principal", 1)->get()->first();
            $tipo_cambio = TipoCambio::findOrFail($tipo_cambio_id);
            $costo_cambio = $this->getMontoCambio($tipo_cambio_id, $moneda_seleccionada_id, $costo_original);
            if ($moneda_seleccionada_id == $moneda_principal->id) {
                // convertir a la segunda moneda
                // solo afectara las columnas de cambio
                $montos_trabajo["costo_cambio"] = $costo_cambio;
                $montos_trabajo["saldo_cambio"] = $costo_cambio;
                $montos_trabajo["saldo"] = $costo_original;
            } else {
                // convertir a moneda principal
                $montos_trabajo["costo_cambio"] = $costo_original;
                $montos_trabajo["saldo_cambio"] = $costo_original;
                $montos_trabajo["costo"] = $costo_cambio;
                $montos_trabajo["saldo"] = $costo_cambio;
            }
            $montos_trabajo["moneda_id"] = $tipo_cambio->moneda1_id;
            $montos_trabajo["moneda_cambio_id"] = $tipo_cambio->moneda2_id;
        }
        return $montos_trabajo;
    }

    // FUNCION PARA OBTENER EL NUEVO MONTO POR TIPO DE CAMBIO
    // SEGUN EL PARAMETRO 2 DEVOLVERA EL MONTO EN LA MONEDA CONTRARIA AL TIPO DE CAMBIO
    // Ej.: moneda_id = Bs. | tipo_cambio=Bs a $us | retorna $us
    // Ej2.: moneda_id = $us. | tipo_cambio=Bs a $us | retorna Bs
    public function getMontoCambio($tipo_cambio_id, $moneda_id, $monto)
    {
        $tipo_cambio = TipoCambio::find($tipo_cambio_id);
        $moneda1_id = $tipo_cambio->moneda1_id;
        $valor1 = $tipo_cambio->valor1;
        $valor2 = $tipo_cambio->valor2;
        $nuevo_monto = $monto;
        // verificar la moneda seleccionada con las monedas de cambio
        if ($moneda1_id == $moneda_id) { // es igual a la moneda 1
            if ($valor1 > $valor2) {
                $nuevo_monto = (float)number_format((float)$monto / (float)($valor1), 2, ".", "");
            } elseif ($valor2 > $valor1) {
                $nuevo_monto = (float)number_format((float)$monto * (float)($valor2), 2, ".", "");
            }
        } else {
            if ($valor1 > $valor2) {
                $nuevo_monto = (float)number_format((float)$monto * (float)($valor1), 2, ".", "");
            } elseif ($valor2 > $valor1) {
                $nuevo_monto = (float)number_format((float)$monto / (float)($valor2), 2, ".", "");
            }
        }
        return (float)$nuevo_monto;
    }

    public function generaMontosCambio($tipo_cambio_id, $moneda_seleccionada_id, $monto_original)
    {
        $montos_pago = [
            "monto" => $monto_original,
            "moneda_id" => $moneda_seleccionada_id,
            "monto_cambio" => $monto_original,
            "moneda_cambio_id" => 0
        ];

        if ($tipo_cambio_id != 0) {
            $moneda_principal = Moneda::where("principal", 1)->get()->first();
            $tipo_cambio = TipoCambio::findOrFail($tipo_cambio_id);
            $monto_cambio = $this->getMontoCambio($tipo_cambio_id, $moneda_seleccionada_id, $monto_original);
            if ($moneda_seleccionada_id == $moneda_principal->id) {
                // convertir a la segunda moneda
                // solo afectara las columnas de cambio
                $montos_pago["monto_cambio"] = $monto_cambio;
                $montos_pago["monto"] = $monto_original;
            } else {
                // convertir a moneda principal
                $montos_pago["monto_cambio"] = $monto_original;
                $montos_pago["monto"] = $monto_cambio;
            }
            $montos_pago["moneda_id"] = $tipo_cambio->moneda1_id;
            $montos_pago["moneda_cambio_id"] = $tipo_cambio->moneda2_id;
        }
        return $montos_pago;
    }
}
