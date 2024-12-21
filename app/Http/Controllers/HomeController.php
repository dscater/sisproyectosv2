<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $cancelados = Trabajo::where("estado_pago", "COMPLETO")->get();
        $en_proceso = Trabajo::where("estado_trabajo", "EN PROCESO")->get();
        $no_cancelados = Trabajo::where("estado_pago", "PENDIENTE")
            ->whereIn("estado_trabajo", ["ENVIADO", "CONCLUIDO"])->get();

        $total_cancelado = Trabajo::getTotalCancelado();
        $total_saldo = Trabajo::getTotalSaldoPendiente();
        $costo_total = Trabajo::getTotalTrabajos();

        return Inertia::render('Dashboard',  [
            "total_trabajos" => count(Trabajo::all()),
            'cancelados' => count($cancelados),
            'no_cancelados' => count($no_cancelados),
            'en_proceso' => count($en_proceso),

            'moneda_principal' => $moneda_principal,
            'total_cancelado' => $total_cancelado,
            'total_saldo' => $total_saldo,
            'costo_total' => $costo_total,
        ]);
    }
}
