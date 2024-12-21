<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return Inertia::render('Admin/Clientes/index', ['clientes' => $clientes]);
    }

    public function listaClientes()
    {
        $clientes = Cliente::all();
        return response()->JSON($clientes);
    }

    public function create()
    {
        return Inertia::render(
            'clientes/create'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required",
        ]);
        $request["fecha_registro"] = date("Y-m-d");
        Cliente::create(array_map('mb_strtoupper', $request->all()));
        sleep(1);
        return redirect()->route('clientes.index')->with('msj', 'Cliente registrado con éxito');
    }

    public function edit(Cliente $cliente)
    {
        return Inertia::render(
            'clientes/edit',
            [
                'cliente' => $cliente
            ]
        );
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            "nombre" => "required",
        ]);
        $cliente->update(array_map('mb_strtoupper', $request->all()));
        return redirect()->route('clientes.index')->with('msj', 'Registro actualizado con éxito');
    }

    public function destroy(Cliente $cliente)
    {
        $existe_trabajos = Trabajo::where("cliente_id", $cliente->id)->get();
        if (count($existe_trabajos) > 0) {
            return redirect()->route('clientes.index')->with('error', 'No es posible eliminar este registro porque esta siendo utilizado en Trabajo');
        }
        $cliente->delete();
        return redirect()->route('clientes.index')->with('msj', 'Registro eliminado con éxito');
    }
}
