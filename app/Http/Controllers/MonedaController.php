<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\Pago;
use App\Models\TipoCambio;
use App\Models\Trabajo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MonedaController extends Controller
{
    public $validacion = [
        "nombre" => 'required|min:2|unique:monedas,nombre|regex:/^[\pL\s\.\'\",0-9áéíóúÁÉÍÓÚñÑ\$€]+$/u',
        "descripcion" => 'required|min:2|unique:monedas,nombre|regex:/^[\pL\s\.\'\",0-9áéíóúÁÉÍÓÚñÑ\$€]+$/u',

    ];

    public $mensajes = [
        "nombre.required" => "Este campo es obligatorio",
        'nombre.regex' => 'Debes ingresar solo texto',
        "nombre.min" => "Debes ingresar al menos :min caracteres",
        "descripcion.required" => "Este campo es obligatorio",
        'descripcion.regex' => 'Debes ingresar solo texto',
        "descripcion.min" => "Debes ingresar al menos :min caracteres",
    ];

    public function index()
    {
        return Inertia::render("Admin/Monedas/Index");
    }

    public function listado(Request $request)
    {
        $monedas = Moneda::select("monedas.*");
        if ($request->order && $request->order == "desc") {
            $monedas->orderBy("monedas.id", $request->order);
        }
        if ($request->sin_principal && $request->sin_principal == true) {
            $monedas->where("principal", 0);
        }

        $monedas = $monedas->get();
        return response()->JSON([
            "monedas" => $monedas
        ]);
    }

    public function getMonedaPrincipal()
    {
        $moneda = Moneda::where("principal", 1)->get()->first();
        return response()->JSON($moneda);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $search = $request->search;
        $monedas = Moneda::select("monedas.*");
        if (trim($search) != "") {
            $monedas->where("nombre", "LIKE", "%$search%");
        }

        if ($request->orderBy && $request->orderAsc) {
            $monedas->orderBy($request->orderBy, $request->orderAsc);
        }

        $monedas = $monedas->paginate($perPage);
        return response()->JSON([
            'data' => $monedas->items(),
            'total' => $monedas->total(),
            'lastPage' => $monedas->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate($this->validacion, $this->mensajes);
            $request["principal"] = 0;
            Moneda::create($request->all());
            DB::commit();
            return redirect()->route('monedas.index')->with('message', 'Moneda registrado con éxito');
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

    public function update(Request $request, Moneda $moneda)
    {
        DB::beginTransaction();
        try {

            $this->validacion["nombre"] = "required|min:2|unique:monedas,nombre,$moneda->id|regex:/^[\pL\s\.\'\",0-9áéíóúÁÉÍÓÚñÑ\$€]+$/u";
            $request->validate($this->validacion, $this->mensajes);
            $moneda->update($request->all());
            DB::commit();
            return redirect()->route('monedas.index')->with('message', 'Registro actualizado con éxito');
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

    public function destroy(Moneda $moneda)
    {
        DB::beginTransaction();
        try {
            $existe_trabajos = Trabajo::where("moneda_id", $moneda->id)->get();
            if (count($existe_trabajos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utiliado", 422);
            }
            $existe_trabajos = Trabajo::where("moneda_seleccionada_id", $moneda->id)->get();
            if (count($existe_trabajos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utiliado", 422);
            }
            $existe_trabajos = Trabajo::where("moneda_cambio_id", $moneda->id)->get();
            if (count($existe_trabajos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utiliado", 422);
            }
            $existe_pagos = Pago::where("moneda_id", $moneda->id)->get();
            if (count($existe_pagos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utiliado", 422);
            }
            $existe_pagos = Pago::where("moneda_cambio_id", $moneda->id)->get();
            if (count($existe_pagos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utiliado", 422);
            }
            $moneda->delete();
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
