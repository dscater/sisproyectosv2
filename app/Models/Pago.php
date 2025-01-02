<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'trabajo_id',
        'cliente_id',
        "monto_original",
        "moneda_seleccionada_id",
        'monto',
        'moneda_id',
        'monto_cambio',
        'moneda_cambio_id',
        'descripcion',
        'fecha_pago',
        'foto_comprobante',
        'archivo_comprobante',
        "descripcion_archivo"
    ];

    protected $with = ["trabajo.proyecto", "cliente", "moneda", "moneda_cambio"];

    protected $appends = ["url_foto", "url_archivo", "tipo_archivo", "fecha_pago_t"];

    public function getFechaPagoTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_pago));
    }

    public function getUrlFotoAttribute()
    {
        return asset("/files/" . $this->foto_comprobante);
    }

    public function getUrlArchivoAttribute()
    {
        return asset("/files/" . $this->archivo_comprobante);
    }

    public function getTipoArchivoAttribute()
    {
        $tipo = "";
        if ($this->archivo_comprobante && $this->archivo_comprobante != "") {
            $array_nom = explode(".", $this->archivo_comprobante);
            if (in_array($array_nom[1], ["jpg", "jpeg", "png", "webp", "gif"])) {
                $tipo = "imagen";
            }
            if (in_array($array_nom[1], ["pdf"])) {
                $tipo = "pdf";
            }
            if (!in_array($array_nom[1], ["jpg", "jpeg", "png", "webp", "gif", "pdf"])) {
                $tipo = "otros";
            }
        }
        return $tipo;
    }

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_id');
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

    // Funcion para asignar los costos,saldos y cancelado a Bs. de todos los trabajo
    public static function refactorizarCostos()
    {
        $pagos = Pago::all();
        foreach ($pagos as $p) {
            $p->monto_cambio = $p->monto;
            $p->save();
        }
    }
}
