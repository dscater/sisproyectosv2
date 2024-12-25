<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Trabajo extends Model
{
    use HasFactory;

    protected $fillable = [
        'proyecto_id',
        'cliente_id',
        'costo_original',
        'moneda_seleccionada_id',
        'costo',
        'moneda_id',
        'tipo_cambio_id',
        'cancelado',
        'saldo',
        'cancelado_cambio',
        'saldo_cambio',
        'costo_cambio',
        'moneda_cambio_id',
        'estado_pago',
        'descripcion',
        'fecha_inicio',
        'dias_plazo',
        'fecha_entrega',
        'estado_trabajo',
        'fecha_envio',
        'fecha_conclusion',
        'fecha_registro',
    ];

    protected $appends = ["porcentaje_cancelado", "cantidad_pagos", "fecha_registro_t"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function getCantidadPagosAttribute()
    {
        $pagos = Pago::without('trabajo')->select("id")->where("trabajo_id", $this->id)->get();
        return count($pagos);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function moneda_seleccionada()
    {
        return $this->belongsTo(Moneda::class, 'moneda_seleccionada_id');
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function moneda_cambio()
    {
        return $this->belongsTo(Moneda::class, 'moneda_cambio_id');
    }

    public function tipo_cambio()
    {
        return $this->belongsTo(TipoCambio::class, 'tipo_cambio_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'trabajo_id');
    }

    // MUTATORS AND ACCESORS
    public function getPorcentajeCanceladoAttribute()
    {
        $p = ((float)$this->cancelado * 100) / (float)$this->costo;
        return number_format($p, 0);
    }

    // end mutators and accesors

    // FUNCION PARA OBTENER EL TOTAL CANCELADO DE TODOS LOS TRABAJOS
    static function getTotalCancelado()
    {
        return Trabajo::sum("cancelado");
    }

    // FUNCION PARA OBTENER EL MONTO PENDIENTE DE TODOS LOS TRABAJOS
    static function getTotalSaldoPendiente()
    {
        return Trabajo::where("saldo", ">", 0)->whereIn("estado_trabajo", ["ENVIADO", "CONCLUIDO"])->sum("saldo");
    }

    // FUNCION PARA OBTENER EL TOTAL COSTO DE TODOS LOS TRABAJOS
    static function getTotalTrabajos()
    {
        return Trabajo::sum("costo");
    }

    // reestablecer costos originales con moneda_selecccionada_id = 0
    static function reestablecerCostosOriginales()
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

    // Funcion para asignar los costos,saldos y cancelado a Moneda Principal de todos los trabajos
    static function reestablecerCostos()
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
                            $costo_cambio = self::getMontoCambio($tipo_cambio->id, $t->moneda_id, $t->costo);
                            $t->costo_cambio = $costo_cambio;
                            // cancelado
                            $cancelado_cambio = self::getMontoCambio($tipo_cambio->id, $t->moneda_id, $t->cancelado);
                            $t->cancelado_cambio = $cancelado_cambio;
                            // saldo
                            $saldo_cambio = self::getMontoCambio($tipo_cambio->id, $t->moneda_id, $t->saldo);
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

    // Funcion para asignar los pagos a Moneda Principal de todos los trabajos
    static function reestablecerPagos()
    {
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $trabajos = Trabajo::all();

        if ($moneda_principal) {
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
                                $monto_cambio = self::getMontoCambio($tipo_cambio->id, $p->moneda_id, $p->monto);
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
                    if ($trabajo->costo == $suma_total) {
                        $trabajo->estado_pago = 'COMPLETO';
                        $trabajo->cancelado = $suma_total;
                        $trabajo->cancelado_cambio = $suma_total_cambio;
                    }
                    $trabajo->save();
                }
            }

            return true;
        }

        throw new Exception("No encontro el registro de moneda principal");
        return false;
    }

    // Funcion para asignar los pagos a Moneda Principal de un solo trabajo
    static function reestablecerPagoTrabajo($id)
    {
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $trabajo = Trabajo::findOrFail($id);
        if ($moneda_principal) {
            $pagos = Pago::where("trabajo_id", $id)->get();
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
                            $monto_cambio = self::getMontoCambio($tipo_cambio->id, $p->moneda_id, $p->monto);
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
                $trabajo->cancelado = $suma_total;
                $trabajo->saldo = $trabajo->costo - $suma_total;
                $trabajo->cancelado_cambio = $suma_total_cambio;
                $trabajo->saldo_cambio = $trabajo->costo_cambio - $suma_total_cambio;
                if ($trabajo->saldo <= 0) {
                    $trabajo->estado_pago = 'COMPLETO';
                }
                $trabajo->save();
            }
            return true;
        }
        throw new Exception("No encontro el registro de moneda principal");
        return false;
    }

    // FUNCION PARA OBTENER EL TOTAL CANCELADO SIN TOMAR EN CUENTA UN PAGO
    public static function getTotalCanceladoSinPago($trabajo, $filtra_pago = false, $pago = 0)
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

    // FUNCION PARA OBTENER EL NUEVO MONTO POR TIPO DE CAMBIO
    // SEGUN EL PARAMETRO 2 DEVOLVERA EL MONTO EN LA MONEDA CONTRARIA AL TIPO DE CAMBIO
    // Ej.: moneda_id = Bs. | tipo_cambio=Bs a $us | retorna $us
    // Ej2.: moneda_id = $us. | tipo_cambio=Bs a $us | retorna Bs
    static function getMontoCambio($tipo_cambio_id, $moneda_id, $monto)
    {
        $tipo_cambio = TipoCambio::find($tipo_cambio_id);
        $moneda1_id = $tipo_cambio->moneda1_id;
        $valor1 = $tipo_cambio->valor1;
        $valor2 = $tipo_cambio->valor2;
        // verificar la moneda seleccionada con las monedas de cambio
        if ($moneda1_id == $moneda_id) { // es igual a la moneda 1
            if ($valor1 > $valor2) {
                return (float)number_format((float)$monto / (float)($valor1), 2, ".", "");
            } elseif ($valor2 > $valor1) {
                return (float)number_format((float)$monto * (float)($valor2), 2, ".", "");
            }
        } else {
            if ($valor1 > $valor2) {
                return (float)number_format((float)$monto * (float)($valor1), 2, ".", "");
            } elseif ($valor2 > $valor1) {
                return (float)number_format((float)$monto / (float)($valor2), 2, ".", "");
            }
        }
        return (float)$monto;
    }
}
