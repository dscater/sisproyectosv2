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
    // Trabajo::refactorizarCostos();
    // Trabajo::refactorizarPagos();
    // Trabajo::refactorizarCostosOriginales();
    // TrabajoController::registraPagos();
    public function index()
    {
        return Inertia::render("Admin/Trabajos/Index");
    }

    public function listado(Request $request)
    {
        $trabajos = Trabajo::select("trabajos.*");
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
        $pendiente = $request->pendiente;
        $proceso = $request->proceso;
        $concluido = $request->concluido;
        $trabajos = Trabajo::with(["proyecto", "cliente", "moneda_seleccionada", "moneda", "moneda_cambio", "tipo_cambio.moneda_1", "tipo_cambio.moneda_2"])
            ->select("trabajos.*")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->join("clientes", "clientes.id", "=", "trabajos.cliente_id");
        if (trim($search) != "") {
            $trabajos->where(DB::raw('CONCAT(proyectos.nombre, proyectos.alias, trabajos.descripcion, trabajos.estado_pago,clientes.nombre)'), 'LIKE', "%$search%");
        }
        if ($pendiente && $pendiente == 'true') {
            $trabajos->where("trabajos.estado_pago", "PENDIENTE");
        }
        if ($proceso && $proceso == 'true') {
            $trabajos->where("trabajos.estado_trabajo", "EN PROCESO");
        }
        if ($concluido && $concluido == 'true') {
            $trabajos->whereIn("trabajos.estado_trabajo", ["CONCLUIDO", "ENVIADO"]);
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
        $proyectos = Proyecto::orderBy("alias", 'asc')->get();
        $clientes = Cliente::orderBy("nombre", 'asc')->get();
        $monedas = Moneda::orderBy("nombre", 'asc')->get();
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        $tipo_cambios = TipoCambio::with("moneda_1", "moneda_2", "moneda_menor_valor")->orderBy("created_at", "desc")->get();
        return Inertia::render(
            'Admin/Trabajos/Create',
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
        $request->validate($this->validacion, $this->mensajes);
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
        $proyectos = Proyecto::orderBy("alias", 'asc')->get();
        $clientes = Cliente::orderBy("nombre", 'asc')->get();
        $monedas = Moneda::orderBy("nombre", 'asc')->get();
        $tipo_cambios = TipoCambio::with("moneda_1", "moneda_2", "moneda_menor_valor")->orderBy("created_at", "desc")->get();
        return Inertia::render(
            'Admin/Trabajos/edit',
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
        try {
            $request->validate($this->validacion, $this->mensajes);
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
                throw new Exception("No es posible eliminar eliminar el registro porque esta siendo utiliado", 422);
            }
            DB::delete("DELETE FROM pagos WHERE trabajo_id = $trabajo->id");
            $trabajo->delete();
            DB::commit();
            return redirect()->route('trabajos.index')->with('message', 'Registro eliminado con éxito');
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
