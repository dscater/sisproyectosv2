<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCambio extends Model
{
    use HasFactory;

    protected $fillable = [
        "moneda1_id",
        "valor1",
        "moneda2_id",
        "valor2",
        "menor_valor",
        "defecto",
    ];

    public function moneda_1()
    {
        return $this->belongsTo(Moneda::class, 'moneda1_id');
    }

    public function moneda_2()
    {
        return $this->belongsTo(Moneda::class, 'moneda2_id');
    }

    public function moneda_menor_valor()
    {
        return $this->belongsTo(Moneda::class, 'menor_valor');
    }
}
