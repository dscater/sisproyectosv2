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

    protected $appends = [
        "porcentaje_cancelado",
        "cantidad_pagos",
        "fecha_inicio_t",
        "fecha_entrega_t",
        "fecha_envio_t",
        "fecha_conclusion_t",
        "fecha_registro_t"
    ];

    public function getFechaConclusionTAttribute()
    {
        if ($this->fecha_conclusion) {
            return date("d/m/Y", strtotime($this->fecha_conclusion));
        }
        return "";
    }

    public function getFechaEnvioTAttribute()
    {
        if ($this->fecha_envio) {
            return date("d/m/Y", strtotime($this->fecha_envio));
        }
        return "";
    }

    public function getFechaEntregaTAttribute()
    {
        if ($this->fecha_entrega) {
            return date("d/m/Y", strtotime($this->fecha_entrega));
        }
        return "";
    }

    public function getFechaInicioTAttribute()
    {
        if ($this->fecha_inicio) {
            return date("d/m/Y", strtotime($this->fecha_inicio));
        }
        return "";
    }

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

}
