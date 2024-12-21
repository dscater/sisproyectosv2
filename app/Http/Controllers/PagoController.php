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
        "monto" => "required|numeric",
        "moneda_id" => "required",
        "fecha_pago" => "required|date",
        "descripcion" => "required|min:4",
    ];

    public function index(Request $request)
    {
        // Pago::refactorizarCostos();
        $texto = $request->texto;
        $pagos = Pago::select("pagos.*")->join("trabajos", "trabajos.id", "=", "pagos.trabajo_id")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->where(DB::raw('CONCAT(proyectos.nombre, proyectos.alias, pagos.descripcion)'), 'LIKE', "%$texto%")
            ->orWhere("pagos.fecha_pago", 'LIKE', "%$texto%")
            ->orderBy('pagos.fecha_pago', 'desc')
            ->orderBy('pagos.created_at', 'desc')->paginate(10)
            ->withQueryString();
        return Inertia::render('pagos/index', ['pagos' => $pagos, 'filtros' => $request->only(["texto"])]);
    }

    public function listaPagos()
    {
        $pagos = Pago::all();
        return response()->JSON($pagos);
    }

    public function create()
    {
        $trabajos = Trabajo::select("trabajos.*")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->where('trabajos.saldo', '>', 0)
            ->orderBy("proyectos.alias", "asc")
            ->get();
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        return Inertia::render(
            'pagos/create',
            [
                'trabajos' => $trabajos,
                "moneda_principal" => $moneda_principal
            ]
        );
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

            $moneda_principal = Moneda::where("principal", 1)->get()->first();
            if ($trabajo->tipo_cambio_id != 0) {
                $request["moneda_cambio_id"] = $trabajo->tipo_cambio->moneda2_id;
            } else {
                $request["moneda_cambio_id"] = 0;
                $request["monto_cambio"] = $request["monto"];
            }
            // asignar la moneda correspondiente a la principal
            $request["moneda_id"] = $moneda_principal->id;

            $nuevo_pago = Pago::create(array_map('mb_strtoupper', $request->except("foto_comprobante", "archivo_comprobante")));

            $monto_cancelado = $nuevo_pago->monto;
            $monto_cancelado_cambio = $nuevo_pago->monto_cambio;

            // actualizar los saldos de las columnas correspondientes
            $trabajo->cancelado = (float)$trabajo->cancelado + (float)$monto_cancelado;
            $trabajo->saldo  = (float)$trabajo->saldo - (float)$monto_cancelado;
            $trabajo->cancelado_cambio = (float)$trabajo->cancelado_cambio + (float)$monto_cancelado_cambio;
            $trabajo->saldo_cambio = (float)$trabajo->saldo_cambio - (float)$monto_cancelado_cambio;
            $trabajo->save();

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

            // ACTUALIZAR SALDO Y CANCELADO DEL TRABAJO
            if ($trabajo->saldo == 0) {
                $trabajo->estado_pago = "COMPLETO";
            } else {
                $trabajo->estado_pago = "PENDIENTE";
            }
            $trabajo->save();
            DB::commit();
            return redirect()->route('pagos.index')->with('msj', 'Pago registrado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pagos.index')->with('error', 'No se puedo realizar el pago debido a este error: ' . $e->getMessage());
        }
    }

    public function edit(Pago $pago)
    {
        $trabajos = Trabajo::select("trabajos.*")
            ->join("proyectos", "proyectos.id", "=", "trabajos.proyecto_id")
            ->orderBy("proyectos.alias", "asc")
            ->get();
        $moneda_principal = Moneda::where("principal", 1)->get()->first();
        return Inertia::render(
            'pagos/edit',
            [
                'pago' => $pago,
                'trabajos' => $trabajos,
                'moneda_principal' => $moneda_principal,
            ]
        );
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
            // actualizar los saldos de las columnas correspondientes antes del pago
            $trabajo->cancelado = (float)$trabajo->cancelado - (float)$pago->monto;
            $trabajo->saldo  = (float)$trabajo->saldo + (float)$pago->monto;
            $trabajo->cancelado_cambio = (float)$trabajo->cancelado_cambio - (float)$pago->monto_cambio;
            $trabajo->saldo_cambio = (float)$trabajo->saldo_cambio + (float)$pago->monto_cambio;
            $trabajo->save();

            // ACTUALIZAR EL REGISTRO
            $pago->update(array_map('mb_strtoupper', $request->except("foto_comprobante", "archivo_comprobante")));

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

            $monto_cancelado = $pago->monto;
            $monto_cancelado_cambio = $pago->monto_cambio;
            // actualizar los saldos de las columnas correspondientes
            $trabajo->cancelado = (float)$trabajo->cancelado + (float)$monto_cancelado;
            $trabajo->saldo  = (float)$trabajo->saldo - (float)$monto_cancelado;
            $trabajo->cancelado_cambio = (float)$trabajo->cancelado_cambio + (float)$monto_cancelado_cambio;
            $trabajo->saldo_cambio = (float)$trabajo->saldo_cambio - (float)$monto_cancelado_cambio;
            $trabajo->save();
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
            $total_pagos = Trabajo::getTotalCanceladoSinPago($trabajo->id, false);
            $trabajo->cancelado = $total_pagos["suma_pagos"];
            $trabajo->saldo = (float)($trabajo->costo) - (float)$trabajo->cancelado;
            $trabajo->cancelado_cambio = $total_pagos["suma_pagos_cambio"];
            $trabajo->saldo_cambio = (float)($trabajo->costo_cambio) - (float)$trabajo->cancelado_cambio;
            $trabajo->save();
            DB::commit();
            return redirect()->route('pagos.index')->with('msj', 'Registro eliminado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pagos.index')->with('error', 'Error al eliminar. ' . $e->getMessage());
        }
    }
}
