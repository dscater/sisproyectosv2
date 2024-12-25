<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\Pago;
use App\Models\TipoCambio;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoController extends Controller
{
    public $validacion = [
        "trabajo_id" => "required",
        "monto" => "required|numeric|min:1",
        "fecha_pago" => "required|date",
        "descripcion" => "required|min:4",
    ];

    public $mensajes = [
        "trabajo_id.requried" => "Este campo es obligatorio",
        "monto.requried" => "Este campo es obligatorio",
        "monto.numeric" => "Debes ingresar un valor númerico",
        "monto.min" => "Debes ingresar al menos :min",
        "fecha_pago.requried" => "Este campo es obligatorio",
        "fecha_pago.date" => "Debes ingrsar una fecha valida",
        "descripcion.requried" => "Este campo es obligatorio",
        "descripcion.min" => "Debes ingresar al menos :min caracteres",
    ];

    public function index(Request $request)
    {
        // Pago::refactorizarCostos();
        return Inertia::render("Admin/Pagos/Index");
    }

    public function listado(Request $request)
    {
        $pagos = Pago::select("pagos.*");
        if ($request->order && $request->order == "desc") {
            $pagos->orderBy("pagos.id", $request->order);
        }
        $pagos = $pagos->get();
        return response()->JSON([
            "pagos" => $pagos
        ]);
    }
    public function paginado(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $search = $request->search;
        $pagos = Pago::with(["trabajo.proyecto", "cliente", "moneda"])
            ->select("pagos.*")
            ->join("trabajos", "trabajos.id", "=", "pagos.trabajo_id")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->join("clientes", "clientes.id", "=", "pagos.cliente_id");
        if (trim($search) != "") {
            $pagos->where(DB::raw('CONCAT(proyectos.nombre, proyectos.alias, pagos.descripcion, clientes.nombre)'), 'LIKE', "%$search%");
        }

        if ($request->orderBy && $request->orderAsc) {
            $pagos->orderBy($request->orderBy, $request->orderAsc);
        }

        $pagos = $pagos->paginate($perPage);
        return response()->JSON([
            'data' => $pagos->items(),
            'total' => $pagos->total(),
            'lastPage' => $pagos->lastPage(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pagos/Create');
    }

    public function store(Request $request)
    {
        if ($request->hasFile("foto_comprobante")) {
            $this->validacion["foto_comprobante"] = "image|mimes:jpeg,jpg,png,gif|max:4096";
        }
        if ($request->hasFile("archivo_comprobante")) {
            $this->validacion["archivo_comprobante"] = "required|mimes:pdf,jpeg,jpg,png,gif|max:5120";
        }
        $request->validate($this->validacion);
        DB::beginTransaction();
        try {
            $trabajo = Trabajo::find($request['trabajo_id']);
            $request['cliente_id'] = $trabajo->cliente_id;

            $datos_pago = [
                "trabajo_id" => $trabajo->id,
                "cliente_id" => $trabajo->cliente_id,
                "monto" => $request->monto,
                "moneda_id" => $trabajo->moneda_id,
                "monto_cambio" => $trabajo->tipo_cambio_id != 0 ? $request->monto_cambio : $request->monto,
                "moneda_cambio_id" => $trabajo->tipo_cambio_id != 0 ? $trabajo->moneda_cambio_id : 0,
                "descripcion" => $request->descripcion,
                "fecha_pago" => $request->fecha_pago,
                "descripcion_archivo" => $request->descripcion_archivo,
            ];

            $nuevo_pago = Pago::create($datos_pago);

            if ($request->hasFile("foto_comprobante")) {
                $file = $request->file("foto_comprobante");
                $extension = $file->getClientOriginalExtension();
                $nom_file = "fc_" . $nuevo_pago->id . time() . "." . $extension;
                $file->move(public_path() . "/files/", $nom_file);
                $nuevo_pago->foto_comprobante = $nom_file;
            }
            if ($request->hasFile("archivo_comprobante")) {
                $file = $request->file("archivo_comprobante");
                $extension = $file->getClientOriginalExtension();
                $nom_file = "ac_" . $nuevo_pago->id . time() . "." . $extension;
                $file->move(public_path() . "/files/", $nom_file);
                $nuevo_pago->archivo_comprobante = $nom_file;
            }
            $nuevo_pago->save();

            Trabajo::actualizaSaldoTrabajoPorPago($nuevo_pago, $trabajo);

            DB::commit();
            return redirect()->route('pagos.index')->with('msj', 'Pago registrado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pagos.index')->with('error', 'No se puedo realizar el pago debido a este error: ' . $e->getMessage());
        }
    }

    public function edit(Pago $pago)
    {
        return Inertia::render('Admin/Pagos/Edit', ['pago' => $pago]);
    }

    public function show(Pago $pago)
    {
        return Inertia::render(
            'pagos/show',
            [
                'pago' => $pago,
            ]
        );
    }

    public function update(Request $request, Pago $pago)
    {
        if ($request->hasFile("foto_comprobante")) {
            $this->validacion["foto_comprobante"] = "image|mimes:jpeg,jpg,png,gif|max:4096";
        }
        if ($request->hasFile("archivo_comprobante")) {
            $this->validacion["archivo_comprobante"] = "required|mimes:pdf,jpeg,jpg,png,gif|max:5120";
        }
        $request->validate($this->validacion);
        DB::beginTransaction();
        try {
            $trabajo = $pago->trabajo;
            $datos_pago = [
                "trabajo_id" => $trabajo->id,
                "cliente_id" => $trabajo->cliente_id,
                "monto" => $request->monto,
                "moneda_id" => $trabajo->moneda_id,
                "monto_cambio" => $trabajo->tipo_cambio_id != 0 ? $request->monto_cambio : $request->monto,
                "moneda_cambio_id" => $trabajo->tipo_cambio_id != 0 ? $trabajo->moneda_cambio_id : 0,
                "descripcion" => $request->descripcion,
                "fecha_pago" => $request->fecha_pago,
                "descripcion_archivo" => $request->descripcion_archivo,
            ];

            // ACTUALIZAR EL REGISTRO
            $pago->update($datos_pago);

            if ($request->hasFile("foto_comprobante")) {
                if ($pago->foto_comprobante) {
                    \File::delete(public_path() . "/files/" . $pago->foto_comprobante);
                }
                $file = $request->file("foto_comprobante");
                $extension = $file->getClientOriginalExtension();
                $nom_file = "fc_" . $pago->id . time() . "." . $extension;
                $file->move(public_path() . "/files/", $nom_file);
                $pago->foto_comprobante = $nom_file;
            }
            if ($request->hasFile("archivo_comprobante")) {
                if ($pago->archivo_comprobante) {
                    \File::delete(public_path() . "/files/" . $pago->archivo_comprobante);
                }
                $file = $request->file("archivo_comprobante");
                $extension = $file->getClientOriginalExtension();
                $nom_file = "ac_" . $pago->id . time() . "." . $extension;
                $file->move(public_path() . "/files/", $nom_file);
                $pago->archivo_comprobante = $nom_file;
            }
            $pago->save();

            // reestablecer pagos del trabajo
            Trabajo::reestablecerPagosTrabajo($trabajo->id);

            DB::commit();
            return redirect()->route('pagos.index')->with('msj', 'Registro actualizado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function destroy(Pago $pago)
    {
        DB::beginTransaction();
        try {
            $trabajo = $pago->trabajo;
            $pago->delete();
            // reestablecer pagos
            Trabajo::reestablecerPagosTrabajo($trabajo->id);
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
