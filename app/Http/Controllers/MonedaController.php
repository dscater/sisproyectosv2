<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\TipoCambio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonedaController extends Controller
{
    public function index()
    {
        $list_monedas = Moneda::where("principal", 0)->get();
        $monedas = Moneda::all();
        $principal = Moneda::where("principal", 1)->first();
        $tipo_cambios = TipoCambio::with("moneda_1", "moneda_2", "moneda_menor_valor")->orderBy("created_at", "desc")->get();
        return Inertia::render('monedas/index', ['monedas' => $monedas, 'tipo_cambios' => $tipo_cambios, "principal" => $principal, "list_monedas" => $list_monedas]);
    }

    public function listado(Request $request)
    {
        $monedas = Moneda::select("monedas.*");
        if ($request->order && $request->order == "desc") {
            $monedas->orderBy("monedas.id", $request->order);
        }
        $monedas = $monedas->get();
        return response()->JSON([
            "monedas" => $monedas
        ]);
    }

    public function listaMonedas()
    {
        $monedas = Moneda::all();
        return response()->JSON($monedas);
    }

    public function create()
    {
        return Inertia::render(
            'monedas/create'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required",
        ]);
        $request["fecha_registro"] = date("Y-m-d");
        Moneda::create(array_map('mb_strtoupper', $request->all()));
        return response()->JSON(true);
    }

    public function edit(Moneda $moneda)
    {
        return Inertia::render(
            'monedas/edit',
            [
                'moneda' => $moneda
            ]
        );
    }

    public function update(Request $request, Moneda $moneda)
    {
        $request->validate([
            "nombre" => "required",
        ]);
        $moneda->update(array_map('mb_strtoupper', $request->all()));
        return redirect()->route('monedas.index')->with('msj', 'Registro actualizado con éxito');
    }

    public function destroy(Moneda $moneda)
    {
        $moneda->delete();
        return response()->JSON(true);
    }
}
