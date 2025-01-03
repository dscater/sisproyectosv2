<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\Proyecto;
use App\Models\TipoCambio;
use App\Models\Trabajo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TrabajoController extends Controller
{
    public $validacion = [
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
    ];

    public $mensajes = [
        "proyecto_id.required" => "Este campo es obligatorio",
        "cliente_id.required" => "Este campo es obligatorio",
        "costo_original.required" => "Este campo es obligatorio",
        "costo_original.numeric" => "Debes ingresar un valor númerico",
        "costo_original.min" => "Debes ingresar al menos :min",
        "moneda_seleccionada_id.required" => "Este campo es obligatorio",
        "estado_pago.required" => "Este campo es obligatorio",
        "descripcion.required" => "Este campo es obligatorio",
        "descripcion.min" => "Debes ingresar al menos :min caracteres",
        "fecha_inicio.required" => "Este campo es obligatorio",
        "fecha_inicio.date" => "Debes ingresar una fecha valida",
        "dias_plazo.required" => "Este campo es obligatorio",
        "dias_plazo.numeric" => "Debes ingresar un valor númerico",
        "fecha_entrega.required" => "Este campo es obligatorio",
        "estado_trabajo.required" => "Este campo es obligatorio",
        "fecha_envio.required" => "Este campo es obligatorio",
        "fecha_envio.date" => "Debes ingresar una fecha valida",
        "fecha_conclusion.required" => "Este campo es obligatorio",
        "fecha_conclusion.date" => "Debes ingresar una fecha valida",
    ];
    // Trabajo::reestablecerCostos();
    // Trabajo::reestablecerPagos();
    // Trabajo::reestablecerCostosOriginales();
    // TrabajoController::registraPagos();
    public function index()
    {
        return Inertia::render("Admin/Trabajos/Index");
    }

    public function listado(Request $request)
    {
        $trabajos = Trabajo::with(["proyecto"])->select("trabajos.*");
        if ($request->order && $request->order == "desc") {
            $trabajos->orderBy("trabajos.id", $request->order);
        }
        $trabajos = $trabajos->get();
        return response()->JSON([
            "trabajos" => $trabajos
        ]);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $search = $request->search;
        $filtro = $request->filtro;
        $trabajos = Trabajo::with(["proyecto", "cliente", "moneda_seleccionada", "moneda", "moneda_cambio", "tipo_cambio.moneda_1", "tipo_cambio.moneda_2"])
            ->select("trabajos.*")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->join("clientes", "clientes.id", "=", "trabajos.cliente_id");
        if (trim($search) != "") {
            $trabajos->where(DB::raw('CONCAT(proyectos.nombre, proyectos.alias, trabajos.descripcion, trabajos.estado_pago,clientes.nombre)'), 'LIKE', "%$search%");
        }

        if ($filtro && count($filtro) > 0) {
            $estados = [];
            foreach ($filtro as $value) {
                if ($value == 'pagopendiente') {
                    $trabajos->where("trabajos.estado_pago", "PENDIENTE");
                }
                if ($value == 'pagocompleto') {
                    $trabajos->where("trabajos.estado_pago", "COMPLETO");
                }

                if ($value == 'proceso') {
                    $estados[] = "EN PROCESO";
                }
                if ($value == 'enviado') {
                    $estados[] = "ENVIADO";
                }
                if ($value == 'concluido') {
                    $estados[] = "CONCLUIDO";
                }
            }

            // filtrar estados
            if (count($estados) > 0) {
                $trabajos->whereIn("estado_trabajo", $estados);
            }
        }

        if ($request->orderBy && $request->orderAsc) {
            $trabajos->orderBy($request->orderBy, $request->orderAsc);
        }

        $trabajos = $trabajos->paginate($perPage);
        return response()->JSON([
            'data' => $trabajos->items(),
            'total' => $trabajos->total(),
            'lastPage' => $trabajos->lastPage(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Trabajos/Create');
    }

    public function store(Request $request)
    {
        $request->validate($this->validacion, $this->mensajes);
        DB::beginTransaction();
        try {
            $montos_trabajo = TrabajoController::generaMontosCambio($request->tipo_cambio_id, $request->moneda_seleccionada_id, $request->costo_original);
            $datos_trabajo = [
                "proyecto_id" => $request->proyecto_id,
                "cliente_id" => $request->cliente_id,
                "costo_original" => $request->costo_original,
                "moneda_seleccionada_id" => $request->moneda_seleccionada_id,
                "costo" => $montos_trabajo["costo"],
                "moneda_id" => $montos_trabajo["moneda_id"],
                "tipo_cambio_id" => $request->tipo_cambio_id,
                "cancelado" => $request->cancelado ? $request->cancelado : 0,
                "saldo" => $montos_trabajo["saldo"],
                "cancelado_cambio" => $montos_trabajo["cancelado_cambio"],
                "saldo_cambio" => $montos_trabajo["saldo_cambio"],
                "costo_cambio" => $montos_trabajo["costo_cambio"],
                "moneda_cambio_id" => $montos_trabajo["moneda_cambio_id"],
                "estado_pago" => $request->estado_pago,
                "descripcion" => nl2br(mb_strtoupper($request->descripcion)),
                "fecha_inicio" => $request->fecha_inicio,
                "dias_plazo" => $request->dias_plazo,
                "fecha_entrega" => $request->fecha_entrega,
                "estado_trabajo" => $request->estado_trabajo,
                "fecha_envio" => $request->fecha_envio ? $request->fecha_envio : NULL,
                "fecha_conclusion" => $request->fecha_conclusion ? $request->fecha_conclusion : NULL,
                "fecha_registro" => date("Y-m-d")
            ];

            Trabajo::create($datos_trabajo);
            DB::commit();
            return redirect()->route('trabajos.index')->with('message', 'Trabajo registrado con éxito');
        } catch (ValidationException $e) {
            DB::rollBack();
            $errors = $e->errors();
            throw ValidationException::withMessages($errors);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("ERROR: " . $e->getMessage());
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function edit(Trabajo $trabajo)
    {
        return Inertia::render(
            'Admin/Trabajos/Edit',
            compact("trabajo")
        );
    }

    public function show(Trabajo $trabajo, Request $request)
    {
        $trabajo = $trabajo->load(["moneda_seleccionada", "moneda", "moneda_cambio", "tipo_cambio"]);
        $total_pagos = Trabajo::getTotalCanceladoSinPago($trabajo->id, $request->filtra, $request->pago);
        return response()->JSON(["trabajo" => $trabajo, "total_pagos" => $total_pagos]);
    }

    public function confirma_envio(Trabajo $trabajo, Request $request)
    {
        $trabajo->estado_trabajo = 'ENVIADO';
        $trabajo->fecha_envio = date("Y-m-d");
        $trabajo->save();
        return response()->JSON(['trabajo' => $trabajo, 'message' => 'Registro actualizado con éxito', "sw" => true]);
    }

    public function cancelar_envio(Trabajo $trabajo, Request $request)
    {
        $trabajo->estado_trabajo = 'EN PROCESO';
        $trabajo->fecha_envio = NULL;
        $trabajo->save();
        return response()->JSON(['trabajo' => $trabajo, 'message' => 'Registro actualizado con éxito', "sw" => true]);
    }

    public function confirma_concluido(Trabajo $trabajo, Request $request)
    {
        $trabajo->estado_trabajo = 'CONCLUIDO';
        $trabajo->fecha_conclusion = date("Y-m-d");
        $trabajo->save();
        return response()->JSON(['trabajo' => $trabajo, 'message' => 'Registro actualizado con éxito', "sw" => true]);
    }

    public function cancelar_concluido(Trabajo $trabajo, Request $request)
    {
        $trabajo->estado_trabajo = 'ENVIADO';
        $trabajo->fecha_conclusion = NULL;
        $trabajo->save();
        return response()->JSON(['trabajo' => $trabajo, 'message' => 'Registro actualizado con éxito', "sw" => true]);
    }

    public function update(Request $request, Trabajo $trabajo)
    {
        try {
            $montos_trabajo = TrabajoController::generaMontosCambio($request->tipo_cambio_id, $request->moneda_seleccionada_id, $request->costo_original);
            $old_tipo_cambio = $trabajo->tipo_cambio_id;
            $datos_trabajo = [
                "proyecto_id" => $request->proyecto_id,
                "cliente_id" => $request->cliente_id,
                "costo_original" => $request->costo_original,
                "moneda_seleccionada_id" => $request->moneda_seleccionada_id,
                "costo" => $montos_trabajo["costo"],
                "moneda_id" => $montos_trabajo["moneda_id"],
                "tipo_cambio_id" => $request->tipo_cambio_id,
                "cancelado" => $request->cancelado ? $request->cancelado : 0,
                "saldo" => $montos_trabajo["saldo"],
                "cancelado_cambio" => $montos_trabajo["cancelado_cambio"],
                "saldo_cambio" => $montos_trabajo["saldo_cambio"],
                "costo_cambio" => $montos_trabajo["costo_cambio"],
                "moneda_cambio_id" => $montos_trabajo["moneda_cambio_id"],
                "estado_pago" => $request->estado_pago,
                "descripcion" => nl2br(mb_strtoupper($request->descripcion)),
                "fecha_inicio" => $request->fecha_inicio,
                "dias_plazo" => $request->dias_plazo,
                "fecha_entrega" => $request->fecha_entrega,
                "estado_trabajo" => $request->estado_trabajo,
                "fecha_envio" => $request->fecha_envio ? $request->fecha_envio : NULL,
                "fecha_conclusion" => $request->fecha_conclusion ? $request->fecha_conclusion : NULL,
            ];
            $trabajo->update($datos_trabajo);
            if ($old_tipo_cambio != $trabajo->tipo_cambio_id) {
                Trabajo::reestablecerPagosTrabajo($trabajo->id);
            }

            DB::commit();
            return redirect()->route('trabajos.index')->with('message', 'Registro actualizado con éxito');
        } catch (ValidationException $e) {
            DB::rollBack();
            $errors = $e->errors();
            throw ValidationException::withMessages($errors);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("ERROR: " . $e->getMessage());
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function destroy(Trabajo $trabajo)
    {
        DB::beginTransaction();
        try {
            $existe_pagos = Pago::where("trabajo_id", $trabajo->id)->get();
            if (count($existe_pagos) > 0) {
                throw new Exception("No es posible eliminar el registro porque tiene pagos realizados", 422);
            }
            DB::delete("DELETE FROM pagos WHERE trabajo_id = $trabajo->id");
            $trabajo->delete();
            DB::commit();
            return response()->JSON([
                "sw" => true,
                "message" => "Registro eliminado con éxito"
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            $errors = $e->errors();
            throw ValidationException::withMessages($errors);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug("ERROR " . $e->getCode() . ": " . $e->getMessage());
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function pagos(Trabajo $trabajo)
    {
        $pagos = Pago::where("trabajo_id", $trabajo->id)->orderBy("created_at", "desc")->get();
        return Inertia::render("Admin/Trabajos/Pagos", [
            "pagos" => $pagos,
            "trabajo" => $trabajo
        ]);
    }

    public static function generaMontosCambio($tipo_cambio_id, $moneda_seleccionada_id, $costo_original)
    {
        $montos_trabajo = [
            "costo" => $costo_original,
            "moneda_id" => $moneda_seleccionada_id,
            "costo_cambio" => $costo_original,
            "cancelado_cambio" => 0,
            "saldo_cambio" => $costo_original,
            "saldo" => $costo_original,
            "moneda_cambio_id" => 0
        ];

        if ($tipo_cambio_id != 0) {
            $moneda_principal = Moneda::where("principal", 1)->get()->first();
            $tipo_cambio = TipoCambio::findOrFail($tipo_cambio_id);
            $costo_cambio = Trabajo::getMontoCambio($tipo_cambio_id, $moneda_seleccionada_id, $costo_original);
            if ($moneda_seleccionada_id == $moneda_principal->id) {
                // convertir a la segunda moneda
                // solo afectara las columnas de cambio
                $montos_trabajo["costo_cambio"] = $costo_cambio;
                $montos_trabajo["saldo_cambio"] = $costo_cambio;
                $montos_trabajo["saldo"] = $costo_original;
            } else {
                // convertir a moneda principal
                $montos_trabajo["costo_cambio"] = $costo_original;
                $montos_trabajo["saldo_cambio"] = $costo_original;
                $montos_trabajo["costo"] = $costo_cambio;
                $montos_trabajo["saldo"] = $costo_cambio;
            }
            $montos_trabajo["moneda_id"] = $tipo_cambio->moneda1_id;
            $montos_trabajo["moneda_cambio_id"] = $tipo_cambio->moneda2_id;
        }
        return $montos_trabajo;
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
