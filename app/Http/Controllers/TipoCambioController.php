<?php

namespace App\Http\Controllers;

use App\Models\TipoCambio;
use App\Models\Trabajo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TipoCambioController extends Controller
{
    public $validacion = [
        "moneda1_id" => "required",
        "valor1" => "required|numeric|min:1",
        "moneda2_id" => "required",
        "valor2" => "required|numeric|min:1",
    ];

    public $mensajes = [
        "moneda1_id.required" => "Este campo es obligatorio",
        "valor1.required" => "Este campo es obligatorio",
        "valor1.numeric" => "Debes ingresar un valor númerico",
        "valor1.min" => "Debes ingresar al menos :min",
        "moneda2_id.required" => "Este campo es obligatorio",
        "valor2.required" => "Este campo es obligatorio",
        "valor2.numeric" => "Debes ingresar un valor númerico",
        "valor2.min" => "Debes ingresar al menos :min",
    ];

    public function index(Request $request)
    {
        return Inertia::render("Admin/TipoCambios/Index");
    }

    public function listado(Request $request)
    {
        $tipo_cambios = TipoCambio::with(["moneda_1", "moneda_2"])->select("tipo_cambios.*");
        if ($request->order && $request->order == "desc") {
            $tipo_cambios->orderBy("tipo_cambios.id", $request->order);
        }
        $tipo_cambios = $tipo_cambios->get();
        return response()->JSON([
            "tipo_cambios" => $tipo_cambios
        ]);
    }
    public function paginado(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $search = $request->search;
        $tipo_cambios = TipoCambio::with(["moneda_1", "moneda_2"])
            ->select("tipo_cambios.*")
            ->join("monedas as m1", "m1.id", "=", "tipo_cambios.moneda1_id")
            ->join("monedas as m2", "m2.id", "=", "tipo_cambios.moneda2_id");
        if (trim($search) != "") {
            $tipo_cambios->where(DB::raw('CONCAT(m1.nombre, m1.descripcion, m2.nombre, m2.descripcion)'), 'LIKE', "%$search%");
        }

        if ($request->orderBy && $request->orderAsc) {
            $tipo_cambios->orderBy($request->orderBy, $request->orderAsc);
        }

        $tipo_cambios = $tipo_cambios->paginate($perPage);
        return response()->JSON([
            'data' => $tipo_cambios->items(),
            'total' => $tipo_cambios->total(),
            'lastPage' => $tipo_cambios->lastPage(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/TipoCambios/Create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate($this->validacion, $this->mensajes);
            $request["menor_valor"] = 0;
            $request["defecto"] = 0;
            TipoCambio::create($request->all());
            DB::commit();
            return redirect()->route('tipo_cambios.index')->with('message', 'Registro realizado con éxito');
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

    public function edit(TipoCambio $tipo_cambio)
    {
        return Inertia::render('Admin/TipoCambios/Edit', ['tipo_cambio' => $tipo_cambio]);
    }

    public function getInfo(TipoCambio $tipo_cambio)
    {
        return response()->JSON($tipo_cambio->load(["moneda_1", "moneda_2"]));
    }

    public function show(TipoCambio $tipo_cambio)
    {
        return Inertia::render(
            'tipo_cambios/show',
            [
                'tipo_cambio' => $tipo_cambio,
            ]
        );
    }

    public function update(Request $request, TipoCambio $tipo_cambio)
    {
        DB::beginTransaction();
        try {
            $existe_trabajos = Trabajo::where("tipo_cambio_id", $tipo_cambio->id)->get();
            if (count($existe_trabajos) > 0) {
                throw ValidationException::withMessages(
                    [
                        "enuso" => "No es posible actualizar el registro porque esta siendo utilizado"
                    ]
                );
            }
            $request->validate($this->validacion, $this->mensajes);
            $tipo_cambio->update($request->all());
            DB::commit();
            return redirect()->route('tipo_cambios.index')->with('message', 'Registro actualizado con éxito');
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

    public function destroy(TipoCambio $tipo_cambio)
    {
        DB::beginTransaction();
        try {
            $existe_trabajos = Trabajo::where("tipo_cambio_id", $tipo_cambio->id)->get();
            if (count($existe_trabajos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utilizado", 422);
            }
            $tipo_cambio->delete();
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
}
