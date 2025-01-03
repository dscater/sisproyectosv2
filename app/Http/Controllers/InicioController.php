<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\Pago;
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
        $total_saldo = Trabajo::getTotalSaldos();
        $total_saldo_enviando = Trabajo::getTotalSaldoPendiente();

        $costo_total = Trabajo::getTotalTrabajos();
        // fin informacion trabajos


        $array_infos = UserController::getInfoBoxUser();


        $anio_ini = Pago::orderBy("fecha_pago", "asc")->get()->first();
        $anio_fin = Pago::orderBy("fecha_pago", "desc")->get()->first();
        $anio_ini = date("Y", strtotime($anio_ini->fecha_pago));
        $anio_fin = date("Y", strtotime($anio_fin->fecha_pago));
        $anio_actual = date("Y");
        if ($anio_actual > $anio_fin) {
            $anio_fin = $anio_actual;
        }
        $anios = [];
        for ($i = $anio_ini; $i <= $anio_fin; $i++) {
            $anios[] = $i;
        }
        return Inertia::render('Admin/Home', [
            'array_infos' => $array_infos,
            "total_trabajos" => count(Trabajo::all()),
            'cancelados' => count($cancelados),
            'no_cancelados' => count($no_cancelados),
            'en_proceso' => count($en_proceso),

            'moneda_principal' => $moneda_principal,
            'total_cancelado' => $total_cancelado,
            'total_saldo' => $total_saldo,
            'total_saldo_enviando' => $total_saldo_enviando,
            'costo_total' => $costo_total,
            "anios" => $anios
        ]);
    }

    public function getMaximoImagenes()
    {
        $maximo_archivos = (int)ini_get("max_file_uploads");
        return response()->JSON($maximo_archivos);
    }

    public function graficoPagos(Request $request)
    {
        $filtro = $request->filtro;
        $anio = $request->anio;

        $meses = [
            "01" => "Enero",
            "02" => "Febrero",
            "03" => "Marzo",
            "04" => "Abril",
            "05" => "Mayo",
            "06" => "Junio",
            "07" => "Julio",
            "08" => "Agosto",
            "09" => "Septiembre",
            "10" => "Octubre",
            "11" => "Noviembre",
            "12" => "Diciembre",
        ];

        $data = [];
        $categories = [];
        if ($filtro == "gestion") {
            $anio_ini = Pago::orderBy("fecha_pago", "asc")->get()->first();
            $anio_fin = Pago::orderBy("fecha_pago", "desc")->get()->first();
            $anio_ini = date("Y", strtotime($anio_ini->fecha_pago));
            $anio_fin = date("Y", strtotime($anio_fin->fecha_pago));
            $anio_actual = date("Y");
            if ($anio_actual > $anio_fin) {
                $anio_fin = $anio_actual;
            }
            for ($i = $anio_ini; $i <= $anio_fin; $i++) {
                $categories[] = $i;
                $total = Pago::where("fecha_pago", "LIKE", "$i%")->sum("monto");
                $data[] = [(float)$total];
            }
        } else if ($filtro == "poranio") {
            if ($anio) {
                foreach ($meses as $key => $mes) {
                    $categories[] = $mes;
                    $total = Pago::where("fecha_pago", "LIKE", "$anio-$key%")->sum("monto");
                    $data[] = [(float)$total];
                }
            }
        }

        return response()->JSON([
            "data" => $data,
            "categories" => $categories
        ]);
    }
}
