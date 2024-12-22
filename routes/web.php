<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TipoCambioController;
use App\Http\Controllers\TrabajoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Login');
});

Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('optimize');
    return 'Cache eliminado <a href="/">Ir al inicio</a>';
})->name('clear.cache');

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Login');
})->name("login");

// ADMINISTRACION
Route::middleware(['auth'])->prefix("admin")->group(function () {
    Route::get('/inicio', [InicioController::class, 'inicio'])->name('inicio');
    Route::get("/inicio/getMaximoImagenes", [InicioController::class, 'getMaximoImagenes'])->name("entrenamientos.getMaximoImagenes");

    // USUARIO
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/update_foto', [ProfileController::class, 'update_foto'])->name('profile.update_foto');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get("/getUser", [UserController::class, 'getUser'])->name('users.getUser');
    Route::get("/permisos", [UserController::class, 'permisos']);
    Route::get("/menu_user", [UserController::class, 'permisos']);

    // USUARIOS
    Route::put("/usuarios/password/{user}", [UsuarioController::class, 'actualizaPassword'])->name("usuarios.password");
    Route::get("/usuarios/paginado", [UsuarioController::class, 'paginado'])->name("usuarios.paginado");
    Route::get("/usuarios/listado", [UsuarioController::class, 'listado'])->name("usuarios.listado");
    Route::get("/usuarios/listado/byTipo", [UsuarioController::class, 'byTipo'])->name("usuarios.byTipo");
    Route::get("/usuarios/show/{user}", [UsuarioController::class, 'show'])->name("usuarios.show");
    Route::put("/usuarios/update/{user}", [UsuarioController::class, 'update'])->name("usuarios.update");
    Route::delete("/usuarios/{user}", [UsuarioController::class, 'destroy'])->name("usuarios.destroy");
    Route::resource("usuarios", UsuarioController::class)->only(
        ["index", "store"]
    );

    // CLIENTES
    Route::get("/clientes/paginado", [ClienteController::class, 'paginado'])->name("clientes.paginado");
    Route::get("/clientes/listado", [ClienteController::class, 'listado'])->name("clientes.listado");
    Route::resource("clientes", ClienteController::class)->only(
        ["index", "store", "update", "show", "destroy"]
    );

    // PROYECTOS
    Route::get("/proyectos/paginado", [ProyectoController::class, 'paginado'])->name("proyectos.paginado");
    Route::get("/proyectos/listado", [ProyectoController::class, 'listado'])->name("proyectos.listado");
    Route::resource("proyectos", ProyectoController::class)->only(
        ["index", "store", "update", "show", "destroy"]
    );

    // TRABAJOS
    Route::get("/trabajos/paginado", [TrabajoController::class, 'paginado'])->name("trabajos.paginado");
    Route::get("/trabajos/listado", [TrabajoController::class, 'listado'])->name("trabajos.listado");
    Route::resource("trabajos", TrabajoController::class)->only(
        ["index", "create", "store", "edit", "update", "show", "destroy"]
    );

    // MONEDAS
    Route::get("/monedas/paginado", [MonedaController::class, 'paginado'])->name("monedas.paginado");
    Route::get("/monedas/listado", [MonedaController::class, 'listado'])->name("monedas.listado");
    Route::resource("monedas", MonedaController::class)->only(
        ["index", "create", "store", "edit", "update", "show", "destroy"]
    );

    // TIPO DE CAMBIOS
    Route::get("/tipo_cambios/paginado", [TipoCambioController::class, 'paginado'])->name("tipo_cambios.paginado");
    Route::get("/tipo_cambios/listado", [TipoCambioController::class, 'listado'])->name("tipo_cambios.listado");
    Route::resource("tipo_cambios", TipoCambioController::class)->only(
        ["index", "create", "store", "edit", "update", "show", "destroy"]
    );


    // REPORTES
    Route::get('reportes/trabajos', [ReporteController::class, "trabajos"])->name("reportes.trabajos");
    Route::get('reportes/trabajos_pdf', [ReporteController::class, "trabajos_pdf"])->name("reportes.trabajos_pdf");
    Route::get('reportes/pagos', [ReporteController::class, "pagos"])->name("reportes.pagos");
    Route::get('reportes/pagos_pdf', [ReporteController::class, "pagos_pdf"])->name("reportes.pagos_pdf");
});
require __DIR__ . '/auth.php';
