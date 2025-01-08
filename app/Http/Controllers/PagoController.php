<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use App\Models\Pago;
use App\Models\TipoCambio;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PagoController extends Controller
{
    public $validacion = [
        "trabajo_id" => "required",
        "moneda_seleccionada_id" => "required",
        "monto_original" => "required|numeric|min:1",
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
        $fecha_pago = $request->fecha_pago;
        $pagos = Pago::with(["trabajo.proyecto", "cliente", "moneda", "moneda_cambio"])
            ->select("pagos.*")
            ->join("trabajos", "trabajos.id", "=", "pagos.trabajo_id")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->join("clientes", "clientes.id", "=", "pagos.cliente_id");
        if (trim($search) != "") {
            $pagos->where(DB::raw('CONCAT(proyectos.nombre, proyectos.alias, pagos.descripcion, trabajos.descripcion, clientes.nombre)'), 'LIKE', "%$search%");
        }

        if ($fecha_pago && trim($fecha_pago) != "") {
            $pagos->where("pagos.fecha_pago", $fecha_pago);
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
            $trabajo = Trabajo::find($request->trabajo_id);
            $request['cliente_id'] = $trabajo->cliente_id;

            $montos_pago = PagoController::generaMontosCambio($trabajo->tipo_cambio_id, $request->moneda_seleccionada_id, $request->monto_original);

            $datos_pago = [
                "trabajo_id" => $trabajo->id,
                "cliente_id" => $trabajo->cliente_id,
                "monto_original" => $request->monto_original,
                "moneda_seleccionada_id" => $request->moneda_seleccionada_id,
                "monto" => $montos_pago["monto"],
                "moneda_id" => $montos_pago["moneda_id"],
                "monto_cambio" => $montos_pago["monto_cambio"],
                "moneda_cambio_id" => $montos_pago["moneda_cambio_id"],
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

    public function edit(Pago $pago)
    {
        return Inertia::render('Admin/Pagos/Edit', ['pago' => $pago]);
    }

    public function show(Pago $pago)
    {
        return Inertia::render(
            'Admin/Pagos/Show',
            [
                'pago' => $pago->load(["trabajo.proyecto", "cliente", "moneda_seleccionada", "moneda", "moneda_cambio"]),
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
            $old_trabajo = $pago->trabajo;
            $trabajo = Trabajo::find($request->trabajo_id);
            $montos_pago = PagoController::generaMontosCambio($trabajo->tipo_cambio_id, $request->moneda_seleccionada_id, $request->monto_original);
            $datos_pago = [
                "trabajo_id" => $trabajo->id,
                "cliente_id" => $trabajo->cliente_id,
                "monto_original" => $request->monto_original,
                "moneda_seleccionada_id" => $request->moneda_seleccionada_id,
                "monto" => $montos_pago["monto"],
                "moneda_id" => $montos_pago["moneda_id"],
                "monto_cambio" => $montos_pago["monto_cambio"],
                "moneda_cambio_id" => $montos_pago["moneda_cambio_id"],
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

            // restablecer pagos trabajo erroneo
            if ($old_trabajo->id != $trabajo->id) {
                Trabajo::reestablecerPagosTrabajo($old_trabajo->id);
            }

            DB::commit();
            return redirect()->route('pagos.index')->with('message', 'Registro actualizado con éxito');
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

    public static function generaMontosCambio($tipo_cambio_id, $moneda_seleccionada_id, $monto_original)
    {
        $montos_pago = [
            "monto" => $monto_original,
            "moneda_id" => $moneda_seleccionada_id,
            "monto_cambio" => $monto_original,
            "moneda_cambio_id" => 0
        ];

        if ($tipo_cambio_id != 0) {
            $moneda_principal = Moneda::where("principal", 1)->get()->first();
            $tipo_cambio = TipoCambio::findOrFail($tipo_cambio_id);
            $monto_cambio = Trabajo::getMontoCambio($tipo_cambio_id, $moneda_seleccionada_id, $monto_original);
            if ($moneda_seleccionada_id == $moneda_principal->id) {
                // convertir a la segunda moneda
                // solo afectara las columnas de cambio
                $montos_pago["monto_cambio"] = $monto_cambio;
                $montos_pago["monto"] = $monto_original;
            } else {
                // convertir a moneda principal
                $montos_pago["monto_cambio"] = $monto_original;
                $montos_pago["monto"] = $monto_cambio;
            }
            $montos_pago["moneda_id"] = $tipo_cambio->moneda1_id;
            $montos_pago["moneda_cambio_id"] = $tipo_cambio->moneda2_id;
        }
        return $montos_pago;
    }
}
