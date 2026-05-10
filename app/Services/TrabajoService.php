<?php

namespace App\Services;

use App\Models\Moneda;
use App\Models\Pago;
use App\Models\Trabajo;
use Exception;
use Illuminate\Support\Facades\Log;

class TrabajoService
{
    public function __construct(private TipoCambioService $tipo_cambio_service) {}

    public function crear(array $datos)
    {
        $montos_trabajo = $this->tipo_cambio_service->generarMontosCambioTrabajo($datos["tipo_cambio_id"], $datos["moneda_seleccionada_id"], $datos["costo_original"]);

        $datos_trabajo = [
            "proyecto_id" => $datos["proyecto_id"],
            "cliente_id" => $datos["cliente_id"],
            "costo_original" => $datos["costo_original"],
            "moneda_seleccionada_id" => $datos["moneda_seleccionada_id"],
            "costo" => $montos_trabajo["costo"],
            "moneda_id" => $montos_trabajo["moneda_id"],
            "tipo_cambio_id" => $datos["tipo_cambio_id"],
            "cancelado" => $datos["cancelado"] ? $datos["cancelado"] : 0,
            "saldo" => $montos_trabajo["saldo"],
            "cancelado_cambio" => $montos_trabajo["cancelado_cambio"],
            "saldo_cambio" => $montos_trabajo["saldo_cambio"],
            "costo_cambio" => $montos_trabajo["costo_cambio"],
            "moneda_cambio_id" => $montos_trabajo["moneda_cambio_id"],
            "estado_pago" => $datos["estado_pago"],
            "descripcion" => $datos["descripcion"],
            "fecha_inicio" => $datos["fecha_inicio"],
            "dias_plazo" => $datos["dias_plazo"],
            "fecha_entrega" => $datos["fecha_entrega"],
            "estado_trabajo" => $datos["estado_trabajo"],
            "fecha_envio" => $datos["fecha_envio"] ? $datos["fecha_envio"] : NULL,
            "fecha_conclusion" => $datos["fecha_conclusion"] ? $datos["fecha_conclusion"] : NULL,
            "fecha_registro" => date("Y-m-d")
        ];

        return Trabajo::create($datos_trabajo);
    }

    public function actualizar(Trabajo $trabajo, array $datos)
    {
        $montos_trabajo = $this->tipo_cambio_service->generarMontosCambioTrabajo($datos["tipo_cambio_id"], $datos["moneda_seleccionada_id"], $datos["costo_original"]);

        $old_tipo_cambio = $trabajo->tipo_cambio_id;

        $datos_trabajo = [
            "proyecto_id" => $datos["proyecto_id"],
            "cliente_id" => $datos["cliente_id"],
            "costo_original" => $datos["costo_original"],
            "moneda_seleccionada_id" => $datos["moneda_seleccionada_id"],
            "costo" => $montos_trabajo["costo"],
            "moneda_id" => $montos_trabajo["moneda_id"],
            "tipo_cambio_id" => $datos["tipo_cambio_id"],
            "cancelado" => $datos["cancelado"] ? $datos["cancelado"] : 0,
            "saldo" => $montos_trabajo["saldo"],
            "cancelado_cambio" => $montos_trabajo["cancelado_cambio"],
            "saldo_cambio" => $montos_trabajo["saldo_cambio"],
            "costo_cambio" => $montos_trabajo["costo_cambio"],
            "moneda_cambio_id" => $montos_trabajo["moneda_cambio_id"],
            "estado_pago" => $datos["estado_pago"],
            "descripcion" => $datos["descripcion"],
            "fecha_inicio" => $datos["fecha_inicio"],
            "dias_plazo" => $datos["dias_plazo"],
            "fecha_entrega" => $datos["fecha_entrega"],
            "estado_trabajo" => $datos["estado_trabajo"],
            "fecha_envio" => $datos["fecha_envio"] ? $datos["fecha_envio"] : NULL,
            "fecha_conclusion" => $datos["fecha_conclusion"] ? $datos["fecha_conclusion"] : NULL,
        ];

        $trabajo->update($datos_trabajo);

        // if ($old_tipo_cambio != $trabajo->tipo_cambio_id) {
        $this->reestablecerPagosTrabajo($trabajo->id);
        // }
    }

    // Funcion para asignar los pagos a Moneda Principal de un solo trabajo
    public function reestablecerPagosTrabajo($id)
    {
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $trabajo = Trabajo::findOrFail($id);

        if (!$moneda_principal) {
            throw new Exception("No encontro el registro de moneda principal");
        }

        $pagos = Pago::where("trabajo_id", $id)->get();
        $suma_total = 0;
        $suma_total_cambio = 0;
        if (count($pagos) > 0) {
            foreach ($pagos as $p) {
                // verificar si usan un tipo de cambios
                if ($p->trabajo->tipo_cambio_id != 0) {
                    $tipo_cambio = $p->trabajo->tipo_cambio;
                    if ($p->moneda_seleccionada_id != $moneda_principal->id) {
                        // recalcular los valores segun tipo de cambio
                        $monto_cambio = $this->tipo_cambio_service->getMontoCambio($tipo_cambio->id, $p->moneda_seleccionada_id, $p->monto_original);
                        $p->monto = $monto_cambio;
                        $p->monto_cambio = $p->monto_original;
                        // monedas
                        $p->moneda_id = $moneda_principal->id;
                        $p->moneda_cambio_id = $p->moneda_seleccionada_id;
                    } else {
                        // monto
                        $monto_cambio = $this->tipo_cambio_service->getMontoCambio($tipo_cambio->id, $p->moneda_id, $p->monto);
                        $p->monto_cambio = $monto_cambio;
                        // monedas
                        $p->moneda_id = $moneda_principal->id;
                        $p->moneda_cambio_id = $tipo_cambio->moneda2_id;
                    }
                    $p->save();
                }
                $suma_total += (float)$p->monto;
                $suma_total_cambio += (float)$p->monto_cambio;
            }
        }

        $saldo = (float)$trabajo->costo - (float)$suma_total;
        $saldo_cambio = (float)$trabajo->costo_cambio - (float)$suma_total_cambio;
        $trabajo->estado_pago = 'PENDIENTE';
        $trabajo->cancelado = $suma_total;
        $trabajo->saldo = $saldo;
        $trabajo->cancelado_cambio = (float)$suma_total_cambio;
        $trabajo->saldo_cambio = $saldo_cambio;
        if ($trabajo->saldo <= 0) {
            $trabajo->estado_pago = 'COMPLETO';
        }
        $trabajo->save();
    }

    // actualizar saldo de trabajo por un nuevo pago
    public function actualizaSaldoTrabajoPorPago($pago, $trabajo)
    {
        $monto_cancelado = $pago->monto;
        // $monto_cancelado_cambio = $pago->monto_cambio;

        // actualizar los saldos de las columnas correspondientes
        $trabajo->cancelado = (float)$trabajo->cancelado + (float)$monto_cancelado;
        $trabajo->saldo  = (float)$trabajo->costo - (float)$trabajo->cancelado;
        if ($trabajo->tipo_cambio_id != 0) {
            // $trabajo->cancelado_cambio = (float)$trabajo->cancelado_cambio + (float)$monto_cancelado_cambio;
            $trabajo->cancelado_cambio = $this->tipo_cambio_service->getMontoCambio($trabajo->tipo_cambio_id, $trabajo->moneda_id, $trabajo->cancelado);
            // $trabajo->saldo_cambio = (float)$trabajo->saldo_cambio - (float)$monto_cancelado_cambio;
            $trabajo->saldo_cambio = $this->tipo_cambio_service->getMontoCambio($trabajo->tipo_cambio_id, $trabajo->moneda_id, $trabajo->saldo);
        } else {
            $trabajo->cancelado_cambio = (float)$trabajo->cancelado + (float)$monto_cancelado;
            $trabajo->saldo_cambio  = (float)$trabajo->costo - (float)$trabajo->cancelado;
        }
        if ($trabajo->saldo == 0) {
            $trabajo->estado_pago = "COMPLETO";
        } else {
            $trabajo->estado_pago = "PENDIENTE";
        }
        $trabajo->save();
        return $trabajo;
    }

    // FUNCION PARA OBTENER EL TOTAL CANCELADO SIN TOMAR EN CUENTA UN PAGO
    public function getTotalCanceladoSinPago($trabajo, $filtra_pago = false, $pago = 0)
    {
        $trabajo = Trabajo::find($trabajo);
        if ($filtra_pago) {
            $pagos = Pago::where("id", "!=", $pago)->where("trabajo_id", $trabajo->id)->get();
        } else {
            $pagos = Pago::where("trabajo_id", $trabajo->id)->get();
        }

        $suma_pagos = 0;
        $suma_pagos_cambio = 0;

        foreach ($pagos as $p) {
            $suma_pagos += (float)$p->monto;
            $suma_pagos_cambio += (float)$p->monto_cambio;
        }
        return ["suma_pagos" => $suma_pagos, "suma_pagos_cambio" => $suma_pagos_cambio];
    }

    // Funcion para asignar los pagos a Moneda Principal de todos los trabajos
    public function reestablecerPagos()
    {
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $trabajos = Trabajo::all();

        if (!$moneda_principal) {
            throw new Exception("No encontro el registro de moneda principal");
        }

        foreach ($trabajos as $trabajo) {
            $pagos = Pago::where("trabajo_id", $trabajo->id)->get();
            if (count($pagos) > 0) {
                $suma_total = 0;
                $suma_total_cambio = 0;
                foreach ($pagos as $p) {
                    if ($p->moneda_id != $moneda_principal->id) {
                        // solo si la moneda es diferente a la principal intercambiar los valores de las columnas
                        $aux_pago = Pago::find($p->id);
                        $p->monto = $aux_pago->monto_cambio;
                        $p->monto_cambio = $aux_pago->monto;
                        // monedas
                        $p->moneda_id = $moneda_principal->id;
                        $p->moneda_cambio_id = $aux_pago->moneda_id;
                    } else {
                        // verificar si usan un tipo de cambios
                        if ($p->trabajo->tipo_cambio_id != 0) {
                            $tipo_cambio = $p->trabajo->tipo_cambio;
                            // monto
                            $monto_cambio = $this->tipo_cambio_service->getMontoCambio($tipo_cambio->id, $p->moneda_id, $p->monto);
                            $p->monto_cambio = $monto_cambio;
                            // monedas
                            $p->moneda_id = $moneda_principal->id;
                            $p->moneda_cambio_id = $tipo_cambio->moneda2_id;
                        }
                    }
                    $suma_total += (float)$p->monto;
                    $suma_total_cambio += (float)$p->monto_cambio;
                    $p->save();
                }
                $trabajo->estado_pago = 'PENDIENTE';
                // if ($trabajo->id == 29) {
                //     Log::debug("SUMA: " . (float)$suma_total);
                //     Log::debug("COSTO: " . (float)$trabajo->costo);
                // }
                if (round((float)$trabajo->costo, 2) == round((float)$suma_total, 2)) {
                    $trabajo->estado_pago = 'COMPLETO';
                    $trabajo->saldo = 0;
                    $trabajo->saldo_cambio = 0;
                    $trabajo->cancelado = $suma_total;
                    $trabajo->cancelado_cambio = $suma_total_cambio;
                }
                $trabajo->save();
            }
        }

        return true;
    }

    // Funcion para asignar los costos,saldos y cancelado a Moneda Principal de todos los trabajos
    public function reestablecerCostos()
    {
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        if ($moneda_principal) {
            $trabajos = Trabajo::all();
            foreach ($trabajos as $t) {
                if ($t->moneda_id != $moneda_principal->id) {
                    // solo si la moneda es diferente a la principal intercambiar los valores de las columnas
                    $aux_trabajo = Trabajo::find($t->id);
                    $t->costo = $aux_trabajo->costo_cambio;
                    $t->cancelado = $aux_trabajo->cancelado_cambio;
                    $t->saldo = $aux_trabajo->saldo_cambio;
                    $t->costo_cambio = $aux_trabajo->costo;
                    $t->cancelado_cambio = $aux_trabajo->cancelado;
                    $t->saldo_cambio = $aux_trabajo->saldo;
                    // monedas
                    $t->moneda_id = $moneda_principal->id;
                    $t->moneda_cambio_id = $aux_trabajo->moneda_id;
                } else {
                    // verificar si usan un tipo de cambios
                    if ($t->tipo_cambio_id != 0) {
                        $tipo_cambio = $t->tipo_cambio;
                        if ($t->costo == $t->costo_cambio) {
                            $aux_trabajo = Trabajo::find($t->id);

                            // costo
                            $costo_cambio = $this->tipo_cambio_service->getMontoCambio($tipo_cambio->id, $t->moneda_id, $t->costo);
                            $t->costo_cambio = $costo_cambio;
                            // cancelado
                            $cancelado_cambio = $this->tipo_cambio_service->getMontoCambio($tipo_cambio->id, $t->moneda_id, $t->cancelado);
                            $t->cancelado_cambio = $cancelado_cambio;
                            // saldo
                            $saldo_cambio = $this->tipo_cambio_service->getMontoCambio($tipo_cambio->id, $t->moneda_id, $t->saldo);
                            $t->saldo_cambio = $saldo_cambio;
                            // monedas
                            $t->moneda_id = $moneda_principal->id;
                            $t->moneda_cambio_id = $tipo_cambio->moneda2_id;
                        } else {
                            // monedas
                            $t->moneda_id = $moneda_principal->id;
                            $t->moneda_cambio_id = $tipo_cambio->moneda2_id;
                        }
                    }
                }
                $t->save();
            }
            return true;
        }
        return false;
    }

    // reestablecer costos originales con moneda_selecccionada_id = 0
    public function reestablecerCostosOriginales()
    {
        $trabajos = Trabajo::all();
        foreach ($trabajos as $t) {
            if ($t->tipo_cambio_id != 0) {
                $t->costo_original = $t->costo_cambio;
                $t->moneda_seleccionada_id = $t->moneda_cambio_id;
            } else {
                $t->costo_original = $t->costo;
                $t->moneda_seleccionada_id = $t->moneda_id;
            }
            $t->save();
        }
        return true;
    }

    // FUNCION PARA OBTENER EL TOTAL CANCELADO DE TODOS LOS TRABAJOS
    public function getTotalCancelado()
    {
        return Trabajo::where("cancelado", ">", 0)->sum("cancelado");
    }

    // FUNCION PARA OBTENER EL MONTO PENDIENTE DE TODOS LOS TRABAJOS
    public function getTotalSaldoPendiente()
    {
        return Trabajo::where("saldo", ">", 0)->whereIn("estado_trabajo", ["ENVIADO", "CONCLUIDO"])->sum("saldo");
    }

    // FUNCION PARA OBTENER EL MONTO TOTAL DE SALDOS
    public function getTotalSaldos()
    {
        return Trabajo::where("saldo", ">", 0)->sum("saldo");
    }

    // FUNCION PARA OBTENER EL TOTAL COSTO DE TODOS LOS TRABAJOS
    public function getTotalTrabajos()
    {
        return Trabajo::where("costo", ">", 0)->sum("costo");
    }

    // public function registraPagos()
    // {
    //     $trabajos =  Trabajo::where('cancelado', '>', 0)->get();
    //     foreach ($trabajos as $t) {
    //         $t->pagos()->create([
    //             'cliente_id' => $t->cliente_id,
    //             'monto' => $t->cancelado,
    //             'moneda_id' => $t->moneda_id,
    //             'fecha_pago' => $t->fecha_registro
    //         ]);
    //     }
    // }
}
