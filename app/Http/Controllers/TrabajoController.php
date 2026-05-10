<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrabajoStoreRequest;
use App\Http\Requests\TrabajoUpdateRequest;
use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\Proyecto;
use App\Models\TipoCambio;
use App\Models\Trabajo;
use App\Services\TipoCambioService;
use App\Services\TrabajoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TrabajoController extends Controller
{
    public function __construct(private TrabajoService $trabajo_service) {}

    // $this->trabajo_service->reestablecerCostos();
    // $this->trabajo_service->reestablecerPagos();
    // $this->trabajo_service->reestablecerCostosOriginales();
    // $this->trabajo_service->registraPagos();
    public function index()
    {
        return Inertia::render("Admin/Trabajos/Index");
    }

    public function listado(Request $request)
    {
        $trabajos = Trabajo::with(["proyecto"])->select("trabajos.*");
        if ($request->conSaldo && $request->conSaldo == true) {
            $trabajos->where("saldo", ">", 0);
        }

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

    public function store(TrabajoStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->trabajo_service->crear($request->validated());
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
        $total_pagos = $this->trabajo_service->getTotalCanceladoSinPago($trabajo->id, $request->filtra, $request->pago);
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

    public function update(TrabajoUpdateRequest $request, Trabajo $trabajo)
    {
        try {
            $this->trabajo_service->actualizar($trabajo, $request->validated());
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
}
