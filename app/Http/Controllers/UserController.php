<?php

namespace App\Http\Controllers;

use App\Models\Publicacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public static $permisos = [
        "ADMINISTRADOR" => [
            "proyectos.create",
            "proyectos.index",
            "proyectos.edit",
            "proyectos.destroy",

            "clientes.create",
            "clientes.index",
            "clientes.edit",
            "clientes.destroy",

            "trabajos.create",
            "trabajos.index",
            "trabajos.edit",
            "trabajos.destroy",

            "pagos.create",
            "pagos.index",
            "pagos.edit",
            "pagos.destroy",

            "monedas.create",
            "monedas.index",
            "monedas.edit",
            "monedas.destroy",

            "tipo_cambios.create",
            "tipo_cambios.index",
            "tipo_cambios.edit",
            "tipo_cambios.destroy",

            "reportes.usuarios",
            "reportes.productos",
        ],
    ];
    public static function getPermisosUser()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $tipo = $user->tipo;
            $array_permisos = self::$permisos;
            if (isset($array_permisos[$tipo]) && $array_permisos[$tipo]) {
                return $array_permisos[$tipo];
            }
        }
        return [];
    }


    public function getUser()
    {
        return response()->JSON([
            "user" => Auth::user()
        ]);
    }

    public static function getInfoBoxUser()
    {
        $permisos = [];
        $array_infos = [];
        if (Auth::check()) {
            $oUser = new User();
        }


        return $array_infos;
    }
}
