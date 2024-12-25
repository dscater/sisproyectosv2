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


            "reportes.usuarios",
            "reportes.productos",
        ],
    ];
    public static function getPermisosUser()
    {
        $array_permisos = self::$permisos;
        if ($array_permisos["ADMINISTRADOR"]) {
            return $array_permisos["ADMINISTRADOR"];
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
