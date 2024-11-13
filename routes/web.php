<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MarcaController;

// Rutas públicas (si las hay)
// ...

// Grupo de rutas protegidas por autenticación
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rutas de UserRoleController
    Route::get('/user-roles', [UserRoleController::class, 'index'])
        ->middleware('can:view roles')
        ->name('user-roles.index');
    Route::put('/users/{user}/assign-role', [UserRoleController::class, 'assignRole'])
        ->middleware('can:assign roles')
        ->name('users.assign-role');

    // Rutas de User
    Route::get('/usuarios', function () {
        return view('users.lista-usuarios');
    })->middleware('can:view usuarios')->name('user.lista');

    // Ruta de MarcaController
    Route::get('/marcas', [MarcaController::class, 'index'])
        ->middleware('can:view marca')
        ->name('marcas.index');

    // Rutas de vistas
    Route::get('/cliente', function () {
        return view('cliente');
    })->middleware('can:view cliente')->name('cliente.index');

    Route::get('/producto', function () {
        return view('producto');
    })->middleware('can:view producto')->name('producto.index');

    Route::get('/categoria', function () {
        return view('categoria');
    })->middleware('can:view categoria')->name('categoria.index');

    Route::get('/proveedor', function () {
        return view('proveedor');
    })->middleware('can:view proveedor')->name('proveedor.index');

    Route::get('/empleado', function () {
        return view('empleado');
    })->middleware('can:view empleado')->name('empleado.index');

    Route::get('/ruta', function () {
        return view('ruta');
    })->middleware('can:view ruta')->name('ruta.index');

    Route::get('/padron', function () {
        return view('padron');
    })->middleware('can:view padron')->name('padron.index');

    Route::get('/comprobantes', function () {
        return view('comprobantes');
    })->name('comprobantes.index');
});

Route::get('/', function () {
    return redirect(route('dashboard'));
})->name('index');

// ... código existente ...
