<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InicioController extends Controller
{
    public function inicio()
    {

        // informacion trabajos
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $cancelados = Trabajo::where("estado_pago", "COMPLETO")->get();
        $en_proceso = Trabajo::where("estado_trabajo", "EN PROCESO")->get();
        $no_cancelados = Trabajo::where("estado_pago", "PENDIENTE")
            ->whereIn("estado_trabajo", ["ENVIADO", "CONCLUIDO"])->get();

        $total_cancelado = Trabajo::getTotalCancelado();
        $total_saldo = Trabajo::getTotalSaldoPendiente();
        $costo_total = Trabajo::getTotalTrabajos();
        // fin informacion trabajos


        $array_infos = UserController::getInfoBoxUser();
        return Inertia::render('Admin/Home', [
            'array_infos' => $array_infos,
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

    public function getMaximoImagenes()
    {
        $maximo_archivos = (int)ini_get("max_file_uploads");
        return response()->JSON($maximo_archivos);
    }
}
