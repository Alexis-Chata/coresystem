<?php

use App\Exports\PedidoDetallesExport;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MarcaController;
use App\Models\Marca;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

// Rutas públicas (si las hay)
// ...
Route::get('html', function () {
    $movimiento = Movimiento::find(1);
    $marca = Marca::all();
        $movimiento->load(['movimientoDetalles.producto.marca', 'tipoMovimiento', 'conductor.fSede', 'almacen', 'vehiculo']);
        $detallesAgrupados = $movimiento->movimientoDetalles->groupBy(function ($detalle) {
            return $detalle->producto->marca->id; // Agrupar por nombre de la marca
        });
        //dd($movimiento->movimientoDetalles->toArray(), $detallesAgrupados->first()->first()->cantidad_bultos);

        return view("pdf.movimiento-carga", compact("movimiento", "detallesAgrupados", "marca"));
});

Route::get('/test-email', function () {
    Mail::raw('Este es un correo de prueba.', function ($message) {
        $message->to('alexis.golomix@gmail.com')
                ->subject('Prueba de correo');
    });

    return 'Correo enviado correctamente';
});

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

    Route::prefix('producto')->group(function () {
        Route::get('/', function () {
            return view('producto');
        })->middleware('can:edit producto')
            ->name('producto.index');

        Route::get('/precios-mayorista', function () {
            return view('producto.precios-mayorista');
        })->name('producto.precios-mayorista');

        Route::get('/precios-bodega', function () {
            return view('producto.precios-bodega');
        })->name('producto.precios-bodega');

        Route::get('/stock', function () {
            return view('producto.stock');
        })->name('producto.stock');
    });

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

    Route::get('/pedido', function () {
        return view('pedido');
    })->middleware('can:view pedido')->name('pedido.index');

    Route::get('/empresas', function () {
        return view('empresas');
    })->middleware('can:view empresa')->name('empresa.index');

    Route::get('/movimientos/create', function () {
        return view('movimiento');
    })->middleware('can:create movimiento')->name('movimiento.create');

    Route::get('/movimientos/view', function () {
        return view('movimiento');
    })->middleware('can:view movimiento')->name('movimiento.view');

    Route::get('/movimientos/generar-movimientoliq', function () {
        return view('movimiento');
    })->middleware('can:view movimiento')->name('movimiento.generar-movimientoliq');

    Route::get('/asignar-pedidos', function () {
        return view('asignar-pedidos');
    })->middleware('can:asignar pedido')->name('pedido.asignar');

    Route::get('/generar-comprobantes', function () {
        return view('generar-comprobantes');
    })->middleware('can:create comprobante')->name('comprobantes.create');

    Route::get('/comprobantes', function () {
        return view('comprobantes');
    })->middleware('can:view movimiento')->name('comprobantes.index');

    Route::get('/imprimir-comprobantes', function () {
        return view('imprimir-comprobantes');
    })->middleware('can:imprimir comprobante')->name('comprobantes.imprimir');

    Route::get('/envio-comprobantes', function () {
        return view('envio-comprobantes');
    })->middleware('can:envio comprobante')->name('comprobantes.envio');

    Route::get('/envio-guias', function () {
        return view('envio-guias');
    })->middleware('can:envio-guias comprobante')->name('guias.envio');

    Route::get('/reporte', function () {
        return view('reporte-view');
    })->middleware('can:view reporte')->name('reporte.view');

    Route::get('storage/{anyPath}', function ($anyPath) {
        // Verificar si el archivo existe en el disco 'local'
        if (Storage::disk('local')->exists($anyPath)) {
            // Obtener el archivo y su tipo MIME
            $file = Storage::disk('local')->get($anyPath);
            $mimeType = Storage::disk('local')->mimeType($anyPath);

            // Devolver el archivo con el tipo MIME adecuado
            return response($file, 200)->header('Content-Type', $mimeType);
        }

        // Si el archivo no existe, devolver un error 404
        return abort(404, 'Archivo no encontrado');
    })->where('anyPath', '.*')->name('storage_file.view');
});

Route::get('/', function () {
    return redirect(route('dashboard'));
})->name('index');

Route::get('/pedido_detalles_report', function () {
    return Excel::download(new PedidoDetallesExport, 'pedido_detalles_report_'.now().'.xlsx');
})->name('report.pedido_detalle');

Route::get('/zip', function () {
    // Ubicado en la carpeta storage/app
    // zip -r micarpeta.zip .
    $path = storage_path('app/micarpeta.zip');
    return response()->download($path);
})->name('download.zip');

// ... código existente ...
