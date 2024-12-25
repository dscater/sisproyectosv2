<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\Proyecto;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;

class ReporteController extends Controller
{
    public function trabajos()
    {
        $clientes = Cliente::all();
        $trabajos = Trabajo::orderBy("created_at", "desc")->get();
        $proyectos = Proyecto::orderBy("created_at", "desc")->get();
        $moneda_principal = Moneda::where("principal", 1)->get()->first();

        return Inertia::render(
            "Admin/Reportes/Trabajos",
            [
                "clientes" => $clientes,
                "trabajos" => $trabajos,
                "proyectos" => $proyectos,
                "moneda_principal" => $moneda_principal
            ]
        );
    }

    public function trabajos_pdf(Request $request)
    {
        $filtro = $request->filtro;
        $cliente_id = $request->cliente_id;
        $filtro_fecha = $request->filtro_fecha;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $estado_pago = $request->estado_pago;
        $estado_trabajo = $request->estado_trabajo;
        $trabajo = $request->trabajo;
        $proyecto = $request->proyecto;

        $o_cliente = null;
        $o_proyecto = null;
        $o_trabajo = null;

        $trabajos = Trabajo::with(["proyecto", "cliente"])->select("trabajos.*");
        if ($cliente_id != "todos") {
            $trabajos->where("cliente_id", $cliente_id);
            $o_cliente = Cliente::find($cliente_id);
        }
        if ($filtro_fecha != "todos") {
            if ($fecha_ini != "" && $fecha_fin != "") {
                $trabajos->whereBetween("fecha_registro", [$fecha_ini, $fecha_fin]);
            }
        }
        if ($filtro != "todos") {
            if ($filtro == "proyecto" && $proyecto != "todos") {
                $trabajos->where("proyecto_id", $proyecto);
                $o_proyecto = Proyecto::find($proyecto);
            }
            if ($filtro == "trabajo" && $trabajo != "todos") {
                $trabajos->where("id", $trabajo);
                $o_trabajo = Trabajo::find($trabajo);
                $o_proyecto = Proyecto::find($o_trabajo->proyecto_id);
            }
            if ($filtro == "estado_pago" && $estado_pago != "todos") {
                $trabajos->where("estado_pago", $estado_pago);
            }
            if ($filtro == "estado_trabajo" && $estado_trabajo != "todos") {
                $trabajos->where("estado_trabajo", $estado_trabajo);
            }
        }
        $trabajos = $trabajos->get();

        $moneda_principal = Moneda::where("principal", 1)->get()->first();

        if ($request->ajax()) {
            return response()->JSON($trabajos);
        }

        $pdf = PDF::loadView('reportes.trabajos', compact('moneda_principal', 'trabajos', 'filtro', 'estado_pago', 'estado_trabajo', 'filtro_fecha', 'fecha_ini', 'fecha_fin', 'o_cliente', 'o_trabajo', 'o_proyecto'))->setPaper('legal', 'landscape');
        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 10, array(0, 0, 0));
        return $pdf->stream("Trabajos.pdf");
    }

    public function pagos()
    {
        $clientes = Cliente::all();
        $trabajos = Trabajo::orderBy("created_at", "desc")->get();
        $proyectos = Proyecto::orderBy("created_at", "desc")->get();
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        return Inertia::render(
            "Admin/Reportes/Pagos",
            [
                "clientes" => $clientes,
                "trabajos" => $trabajos,
                "proyectos" => $proyectos,
                "moneda_principal" => $moneda_principal
            ]
        );
    }

    public function pagos_pdf(Request $request)
    {
        $filtro = $request->filtro;
        $cliente_id = $request->cliente_id;
        $filtro_fecha = $request->filtro_fecha;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $estado_trabajo = $request->estado_trabajo;
        $trabajo = $request->trabajo;
        $proyecto = $request->proyecto;

        $o_cliente = null;
        $o_proyecto = null;
        $o_trabajo = null;

        $pagos = Pago::select("pagos.*");
        if ($filtro != "todos") {
            if ($filtro == "proyecto" && $proyecto != "todos") {
                $pagos->join("trabajos", "trabajos.id", "=", "pagos.trabajo_id")
                    ->where("trabajos.proyecto_id", $proyecto);
                $o_proyecto = Proyecto::find($proyecto);
            }
            if ($filtro == "trabajo" && $trabajo != "todos") {
                $pagos->where("trabajo_id", $trabajo);
                $o_trabajo = Trabajo::find($trabajo);
                $o_proyecto = Proyecto::find($o_trabajo->proyecto_id);
            }
            if ($filtro == "estado_trabajo" && $estado_trabajo != "todos") {
                $pagos->join("trabajos", "trabajos.id", "=", "pagos.trabajo_id")
                    ->where("trabajos.estado_trabajo", $estado_trabajo);
            }
        }

        if ($cliente_id != "todos") {
            $pagos->where("pagos.cliente_id", $cliente_id);
            $o_cliente = Cliente::find($cliente_id);
        }
        if ($filtro_fecha != "todos") {
            if ($fecha_ini != "" && $fecha_fin != "") {
                $pagos->whereBetween("fecha_pago", [$fecha_ini, $fecha_fin]);
            }
        }
        $pagos = $pagos->orderBy("fecha_pago", "asc")->get();

        if ($request->ajax()) {
            return response()->JSON($pagos->load(["trabajo.proyecto", "cliente"]));
        }

        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $pdf = PDF::loadView('reportes.pagos', compact('moneda_principal', 'pagos', 'filtro', 'estado_trabajo', 'filtro_fecha', 'fecha_ini', 'fecha_fin', 'o_cliente', 'o_trabajo', 'o_proyecto'))->setPaper('legal', 'landscape');
        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 10, array(0, 0, 0));
        return $pdf->stream("Trabajos.pdf");
    }
}
