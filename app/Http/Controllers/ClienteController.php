<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public $validacion = [
        "nombre" => 'required|min:2|unique:clientes,nombre|regex:/^[\pL\s\.\'\"\,0-9áéíóúÁÉÍÓÚñÑ]+$/u',
    ];

    public $mensajes = [
        "nombre.required" => "Este campo es obligatorio",
        'nombre.regex' => 'Debes ingresar solo texto',
        "nombre.min" => "Debes ingresar al menos :min caracteres",
    ];

    public function index()
    {
        return Inertia::render("Admin/Clientes/Index");
    }

    public function listado(Request $request)
    {
        $clientes = Cliente::select("clientes.*");
        if ($request->order && $request->order == "desc") {
            $clientes->orderBy("clientes.id", $request->order);
        }
        $clientes = $clientes->get();
        return response()->JSON([
            "clientes" => $clientes
        ]);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $search = $request->search;
        $clientes = Cliente::select("clientes.*");
        if (trim($search) != "") {
            $clientes->where("nombre", "LIKE", "%$search%");
        }

        if ($request->orderBy && $request->orderAsc) {
            $clientes->orderBy($request->orderBy, $request->orderAsc);
        }

        $clientes = $clientes->paginate($perPage);
        return response()->JSON([
            'data' => $clientes->items(),
            'total' => $clientes->total(),
            'lastPage' => $clientes->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate($this->validacion, $this->mensajes);
            $request["fecha_registro"] = date("Y-m-d");
            Cliente::create(array_map('mb_strtoupper', $request->all()));
            DB::commit();
            return redirect()->route('clientes.index')->with('message', 'Cliente registrado con éxito');
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

    public function update(Request $request, Cliente $cliente)
    {
        DB::beginTransaction();
        try {

            $this->validacion["nombre"] = "required|min:2|unique:clientes,nombre,$cliente->id|regex:/^[\pL\s\.\'\"\,0-9áéíóúÁÉÍÓÚñÑ]+$/u";
            $request->validate($this->validacion, $this->mensajes);
            $cliente->update(array_map('mb_strtoupper', $request->all()));
            DB::commit();
            return redirect()->route('clientes.index')->with('message', 'Registro actualizado con éxito');
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

    public function destroy(Cliente $cliente)
    {
        DB::beginTransaction();
        try {
            $existe_trabajos = Trabajo::where("cliente_id", $cliente->id)->get();
            if (count($existe_trabajos) > 0) {
                throw new Exception("No es posible eliminar el registro porque esta siendo utiliado", 422);
            }
            $cliente->delete();
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
