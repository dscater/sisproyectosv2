<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\Proyecto;
use App\Models\TipoCambio;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TrabajoController extends Controller
{
    public function index(Request $request)
    {
        // Trabajo::refactorizarCostos();
        // Trabajo::refactorizarPagos();
        // Trabajo::refactorizarCostosOriginales();
        // TrabajoController::registraPagos();
        $pendiente = $request->pendiente;
        $proceso = $request->proceso;
        $concluido = $request->concluido;
        $texto = mb_strtoupper($request->texto);

        $trabajos = Trabajo::select("trabajos.*")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->join("clientes", "clientes.id", "=", "trabajos.cliente_id")
            ->where(DB::raw('CONCAT(proyectos.nombre, proyectos.alias, trabajos.descripcion, trabajos.estado_pago,clientes.nombre)'), 'LIKE', "%$texto%")
            ->orderBy('trabajos.created_at', 'desc');
        if ($pendiente == 'true') {
            $trabajos->where("trabajos.estado_pago", "PENDIENTE");
        }

        if ($proceso == 'true') {
            $trabajos->where("trabajos.estado_trabajo", "EN PROCESO");
        }
        if ($concluido == 'true') {
            $trabajos->whereIn("trabajos.estado_trabajo", ["CONCLUIDO", "ENVIADO"]);
        }


        if ($pendiente == 'false' && $proceso == 'false' && $concluido == 'false' && $texto == "") {
            return redirect()->route("trabajos.index");
        } else {
            $trabajos = $trabajos->paginate(10)
                ->withQueryString();
        }

        $cancelados = Trabajo::where("estado_pago", "COMPLETO")->get();
        $en_proceso = Trabajo::where("estado_trabajo", "EN PROCESO")->get();
        $no_cancelados = Trabajo::where("estado_pago", "PENDIENTE")
            ->whereIn("estado_trabajo", ["ENVIADO", "CONCLUIDO"])->get();

        $total_cancelado = Trabajo::getTotalCancelado();
        $total_saldo = Trabajo::getTotalSaldoPendiente();
        $costo_total = Trabajo::getTotalTrabajos();

        return Inertia::render(
            'trabajos/index',
            [
                'trabajos' => $trabajos,
                'paginationLinks' => $trabajos->onEachSide(1)->links(),
                "total_trabajos" => count(Trabajo::all()),
                'cancelados' => count($cancelados),
                'no_cancelados' => count($no_cancelados),
                'en_proceso' => count($en_proceso),
                'total_cancelado' => $total_cancelado,
                'total_saldo' => $total_saldo,
                'costo_total' => $costo_total,
                'filtros' => $request->only(["texto", "pendiente", "proceso", "concluido"])
            ]
        );
    }

    public function create()
    {
        $proyectos = Proyecto::orderBy("alias", 'asc')->get();
        $clientes = Cliente::orderBy("nombre", 'asc')->get();
        $monedas = Moneda::orderBy("nombre", 'asc')->get();
        $moneda_principal = Moneda::where("principal", 1)->get()->first();

        $tipo_cambios = TipoCambio::with("moneda_1", "moneda_2", "moneda_menor_valor")->orderBy("created_at", "desc")->get();
        return Inertia::render(
            'trabajos/create',
            [
                'proyectos' => $proyectos,
                'clientes' => $clientes,
                'monedas' => $monedas,
                "tipo_cambios" => $tipo_cambios,
                "moneda_principal" => $moneda_principal
            ]
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'proyecto_id' => 'required',
            'cliente_id' => 'required',
            'costo_original' => 'required|numeric|min:1',
            'moneda_seleccionada_id' => 'required',
            'estado_pago' => 'required',
            'descripcion' => 'required|min:4',
            'fecha_inicio' => 'required|date',
            'dias_plazo' => 'required|numeric',
            'fecha_entrega' => 'required|date',
            'estado_trabajo' => 'required',
            'fecha_envio' => 'nullable|date',
            'fecha_conclusion' => 'nullable|date',
        ]);
        DB::beginTransaction();
        try {
            $request["fecha_registro"] = date("Y-m-d");
            $request["cancelado"] = 0;
            $request["cancelado_cambio"] = 0;
            $request["costo"] = $request->costo_original;
            $request["moneda_id"] = $request->moneda_seleccionada_id;

            if ($request["tipo_cambio_id"] != 0) {
                $moneda_principal = Moneda::where("principal", 1)->get()->first();
                $tipo_cambio = TipoCambio::findOrFail($request["tipo_cambio_id"]);
                $costo_trasnformado = Trabajo::getMontoCambio($request["tipo_cambio_id"], $request["moneda_id"], (float)$request["costo"]);
                if ($request["moneda_id"] == $moneda_principal->id) {
                    // convertir a la segunda moneda
                    // solo afectara las columnas de cambio
                    $request["costo_cambio"] = $costo_trasnformado;
                    $request["saldo_cambio"] = $costo_trasnformado;
                    $request["saldo"] = $request["costo"];
                } else {
                    // convertir a moneda principal
                    $request["costo_cambio"] = $request["costo"];
                    $request["saldo_cambio"] = $request["costo"];
                    $request["costo"] = $costo_trasnformado;
                    $request["saldo"] = $costo_trasnformado;
                }
                $request["moneda_id"] = $tipo_cambio->moneda1_id;
                $request["moneda_cambio_id"] = $tipo_cambio->moneda2_id;
            } else {
                $request["saldo"] = $request["costo"];
                $request["costo_cambio"] = $request->costo;
                $request["cancelado_cambio"] = $request->cancelado;
                $request["saldo_cambio"] = $request->costo;
                $request["moneda_cambio_id"] = 0;
            }

            $descricion_aux = nl2br(mb_strtoupper($request->descripcion));
            $trabajo = Trabajo::create(array_map('mb_strtoupper', $request->except("fecha_envio", "fecha_conclusion")));
            $trabajo->descripcion = $descricion_aux;
            if (!isset($request->fecha_envio)) {
                $trabajo->fecha_envio = null;
                $trabajo->save();
            } else {
                $trabajo->fecha_envio = $request->fecha_envio;
                $trabajo->save();
            }
            if (!isset($request->fecha_conclusion)) {
                $trabajo->fecha_conclusion = null;
                $trabajo->save();
            } else {
                $trabajo->fecha_conclusion = $request->fecha_conclusion;
                $trabajo->save();
            }
            $trabajo->save();
            DB::commit();
            return redirect()->route('trabajos.index')->with('msj', 'Trabajo registrado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('trabajos.index')->with('error', 'Ocurrió un error inesperado: ' . $e->getMessage());
        }
    }

    public function edit(Trabajo $trabajo)
    {
        $proyectos = Proyecto::orderBy("alias", 'asc')->get();
        $clientes = Cliente::orderBy("nombre", 'asc')->get();
        $monedas = Moneda::orderBy("nombre", 'asc')->get();
        $tipo_cambios = TipoCambio::with("moneda_1", "moneda_2", "moneda_menor_valor")->orderBy("created_at", "desc")->get();
        return Inertia::render(
            'trabajos/edit',
            [
                'trabajo' => $trabajo,
                'proyectos' => $proyectos,
                'clientes' => $clientes,
                'monedas' => $monedas,
                "tipo_cambios" => $tipo_cambios
            ]
        );
    }

    public function getTrabajo(Trabajo $trabajo, Request $request)
    {
        $trabajo = $trabajo->load(["tipo_cambio.moneda_1", "tipo_cambio.moneda_2"]);
        $total_pagos = Trabajo::getTotalCanceladoSinPago($trabajo->id, $request->filtra, $request->pago);
        return response()->JSON(["trabajo" => $trabajo, "total_pagos" => $total_pagos]);
    }

    public function confirma_envio(Trabajo $trabajo, Request $request)
    {
        $trabajo->estado_trabajo = 'ENVIADO';
        $trabajo->fecha_envio = date("Y-m-d");
        $trabajo->save();
        return response()->JSON(['trabajo' => $trabajo, 'message' => 'Registro actualizado con éxito']);
    }
    public function confirma_concluido(Trabajo $trabajo, Request $request)
    {
        $trabajo->estado_trabajo = 'CONCLUIDO';
        $trabajo->fecha_conclusion = date("Y-m-d");
        $trabajo->save();
        return response()->JSON(['trabajo' => $trabajo, 'message' => 'Registro actualizado con éxito']);
    }

    public function update(Request $request, Trabajo $trabajo)
    {
        $request->validate([
            'proyecto_id' => 'required',
            'cliente_id' => 'required',
            'costo_original' => 'required|numeric|min:1',
            'moneda_seleccionada_id' => 'required',
            'estado_pago' => 'required',
            'descripcion' => 'required|min:4',
            'fecha_inicio' => 'required|date',
            'dias_plazo' => 'required|numeric',
            'fecha_entrega' => 'required|date',
            'estado_trabajo' => 'required',
            'fecha_envio' => 'nullable|date',
            'fecha_conclusion' => 'nullable|date',
        ]);

        if (trim($request->fecha_conclusion) == "") {
            unset($request["fecha_conclusion"]);
        }


        $request["costo"] = $request->costo_original;
        $request["moneda_id"] = $request->moneda_seleccionada_id;

        if ($request["tipo_cambio_id"] != 0) {
            $moneda_principal = Moneda::where("principal", 1)->get()->first();
            $tipo_cambio = TipoCambio::findOrFail($request["tipo_cambio_id"]);
            $costo_trasnformado = Trabajo::getMontoCambio($request["tipo_cambio_id"], $request["moneda_id"], (float)$request["costo"]);
            if ($request["moneda_id"] == $moneda_principal->id) {
                // convertir a la segunda moneda
                // solo afectara las columnas de cambio
                $request["costo_cambio"] = $costo_trasnformado;
            } else {
                // convertir a moneda principal
                $request["costo_cambio"] = $request["costo"];
                $request["costo"] = $costo_trasnformado;
            }
            $request["moneda_id"] = $tipo_cambio->moneda1_id;
            $request["moneda_cambio_id"] = $tipo_cambio->moneda2_id;
        } else {
            $request["costo_cambio"] = $request->costo;
            $request["moneda_cambio_id"] = 0;
        }

        $descricion_aux = nl2br(mb_strtoupper($request->descripcion));
        $trabajo->update(array_map('mb_strtoupper', $request->except("fecha_envio")));
        $trabajo->descripcion = $descricion_aux;

        $trabajo->saldo = $trabajo->costo - $trabajo->cancelado;
        if ($trabajo->tipo_cambio_id != 0) {
            $trabajo->saldo_cambio = $trabajo->costo_cambio - $trabajo->cancelado_cambio;
            $trabajo->save();
        } else {
            $trabajo->saldo_cambio = $trabajo->costo_cambio - $trabajo->cancelado_cambio;
            $trabajo->save();
        }

        if (!isset($request->fecha_envio)) {
            $trabajo->fecha_envio = null;
            $trabajo->save();
        } else {
            $trabajo->fecha_envio = $request->fecha_envio;
            $trabajo->save();
        }
        $trabajo->save();
        sleep(1);
        return redirect()->route('trabajos.index')->with('msj', 'Registro actualizado con éxito');
    }

    public function destroy(Trabajo $trabajo)
    {
        DB::beginTransaction();
        try {
            DB::delete("DELETE FROM pagos WHERE trabajo_id = $trabajo->id");
            $trabajo->delete();
            // sleep(1);
            DB::commit();
            return redirect()->route('trabajos.index')->with('msj', 'Registro eliminado con éxito');
        } catch (\Exception $e) {
            return redirect()->route('trabajos.index')->with('error', 'Ocurrió un error. ' . $e->getMessage());
        }
    }

    public function lista_pagos(Trabajo $trabajo)
    {
        $pagos = Pago::without('trabajo')->where("trabajo_id", $trabajo->id)->orderBy("created_at", "desc")->get();
        return Inertia::render("trabajos/pagos", [
            "pagos" => $pagos,
            "trabajo" => $trabajo
        ]);
    }

    // public static function registraPagos()
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
