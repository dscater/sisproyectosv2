<?php

namespace App\Http\Controllers;

use App\Models\TipoCambio;
use App\Models\Trabajo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoCambioController extends Controller
{
    public function index(Request $request)
    {
        $tipo_cambios = TipoCambio::with("moneda_1", "moneda_2", "moneda_menor_valor")->orderBy("created_at", "desc")->get();
        if ($request->ajax()) {
            return response()->JSON($tipo_cambios);
        }
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

    public function store(Request $request)
    {
        $request->validate([
            "moneda1_id" => "required",
            "valor1" => "required|numeric|min:1",
            "moneda2_id" => "required",
            "valor2" => "required|numeric|min:1",
        ]);

        TipoCambio::create([
            "moneda1_id" => $request->moneda1_id,
            "valor1" => $request->valor1,
            "moneda2_id" => $request->moneda2_id,
            "valor2" => $request->valor2,
            "menor_valor" => $request->menor_valor,
            "defecto" => 0,
        ]);

        if ($request->ajax()) {
            return response()->JSON([
                "sw" => true,
                "message" => "Registro éxitoso"
            ]);
        }

        return redirect()->route('monedas.index')->with('msj', 'Registro éxitoso');
    }

    public function update(TipoCambio $tipo_cambio, Request $request)
    {
        DB::beginTransaction();
        try {
            $uso = Trabajo::where("tipo_cambio_id", $tipo_cambio->id)->get();
            if (count($uso) > 0) {
                throw new Exception('No es posible actualizar el registro debido a que esta siendo utilizado. Esto se agregará en un futuro.');
            }
            $tipo_cambio->update([
                "moneda1_id" => $request->moneda1_id,
                "valor1" => $request->valor1,
                "moneda2_id" => $request->moneda2_id,
                "valor2" => $request->valor2,
                "menor_valor" => $request->menor_valor,
            ]);
            DB::commit();
            if ($request->ajax()) {
                return response()->JSON([
                    "sw" => true,
                    "message" => "Registro actualizado con éxito"
                ]);
            }
            return redirect()->route('monedas.index')->with('msj', 'Registro actualizado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->JSON([
                    "sw" => false,
                    "message" => "No se pudo actualizar el registro: " . $e->getMessage()
                ], 401);
            }
        }
    }

    public function destroy(TipoCambio $tipo_cambio, Request $request)
    {
        DB::beginTransaction();
        try {
            $uso = Trabajo::where("tipo_cambio_id", $tipo_cambio->id)->get();
            if (count($uso) > 0) {
                throw new Exception('No es posible eliminar el registro debido a que esta siendo utilizado');
            }
            $tipo_cambio->delete();
            DB::commit();
            if ($request->ajax()) {
                return response()->JSON([
                    "sw" => true,
                    "message" => "Registro eliminado con éxito"
                ]);
            }
            return redirect()->route('monedas.index')->with('msj', 'Registro eliminado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->JSON([
                    "sw" => false,
                    "message" => "No se pudo eliminar el registro: " . $e->getMessage()
                ], 401);
            }
            return redirect()->route('monedas.index')->with('error', 'No se pudo eliminar el registro: ' . $e->getMessage());
        }
    }
}
