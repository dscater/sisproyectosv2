<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProyectoController extends Controller
{

    public $validacion = [
        "nombre" => 'required|min:2|regex:/^[\pL\s\.\'\"\,0-9áéíóúÁÉÍÓÚñÑ]+$/u',
        "alias" => 'required|regex:/^[\pL\s\.\'\"\,0-9áéíóúÁÉÍÓÚñÑ]+$/u',
        "descripcion" => 'required|min:2|regex:/^[\pL\s\.\'\"\,0-9áéíóúÁÉÍÓÚñÑ]+$/u',
    ];

    public $mensajes = [
        "nombre.required" => "Este campo es obligatorio",
        'nombre.regex' => 'Debes ingresar solo texto',
        "nombre.min" => "Debes ingresar al menos :min caracteres",
        "alias.required" => "Este campo es obligatorio",
        'alias.regex' => 'Debes ingresar solo texto',
        "descripcion.required" => "Este campo es obligatorio",
        'descripcion.regex' => 'Debes ingresar solo texto',
        "descripcion.min" => "Debes ingresar al menos :min caracteres",
    ];

    public function index()
    {
        return Inertia::render("Admin/Proyectos/Index");
    }

    public function listado(Request $request)
    {
        $proyectos = Proyecto::select("proyectos.*");

        if ($request->order && $request->order == "desc") {
            $proyectos->orderBy("proyectos.id", $request->order);
        }

        $proyectos = $proyectos->get();

        return response()->JSON([
            "proyectos" => $proyectos
        ]);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $search = $request->search;
        $proyectos = Proyecto::select("proyectos.*");
        if (trim($search) != "") {
            $proyectos->where("nombre", "LIKE", "%$search%");
        }

        if ($request->orderBy && $request->orderAsc) {
            $proyectos->orderBy($request->orderBy, $request->orderAsc);
        }

        $proyectos = $proyectos->paginate($perPage);
        return response()->JSON([
            'data' => $proyectos->items(),
            'total' => $proyectos->total(),
            'lastPage' => $proyectos->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->validacion, $this->mensajes);
        $request["fecha_registro"] = date("Y-m-d");
        $descripcion_aux = nl2br(mb_strtoupper($request->descripcion));
        $proyecto = Proyecto::create(array_map('mb_strtoupper', $request->all()));
        $proyecto->descripcion = $descripcion_aux;
        $proyecto->save();
        sleep(1);
        return redirect()->route('proyectos.index')->with('message', 'Proyecto registrado con éxito');
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $request->validate($this->validacion, $this->mensajes);
        $descripcion_aux = nl2br(mb_strtoupper($request->descripcion));
        $proyecto->update(array_map('mb_strtoupper', $request->all()));
        $proyecto->descripcion = $descripcion_aux;
        $proyecto->save();
        sleep(1);
        return redirect()->route('proyectos.index')->with('message', 'Registro actualizado con éxito');
    }

    public function destroy(Proyecto $proyecto)
    {
        $proyecto->delete();
        sleep(1);

        return response()->JSON([
            "sw" => true,
            "message" => "Registro eliminado con éxito"
        ]);
    }
}
