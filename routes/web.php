<?php

use App\Exports\PedidoDetallesExport;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MarcaController;
use App\Models\Marca;
use App\Models\Movimiento;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

// Rutas públicas (si las hay)
// ...
Route::get('html/{id}', function ($id) {
    $movimiento = Movimiento::findOrFail($id); // Usar findOrFail para evitar null
    $marca = Marca::all();
    $movimiento->load(['movimientoDetalles.producto.marca', 'tipoMovimiento', 'conductor.fSede', 'almacen', 'vehiculo']);
    $detallesAgrupados = $movimiento->movimientoDetalles->groupBy(function ($detalle) {
        return $detalle->producto->marca->id; // Agrupar por nombre de la marca
    });

    // Ordenar por nro_orden de la marca
    $detallesAgrupadosOrdenados = $detallesAgrupados->sortBy(function ($grupo, $marcaId) {
        return optional($grupo->first()->producto->marca)->nro_orden;
    });

    //dd($movimiento->movimientoDetalles->toArray(), $detallesAgrupados->first()->first()->cantidad_bultos);
    return view("pdf.movimiento-carga", compact("movimiento", "detallesAgrupadosOrdenados", "marca"));
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
    \App\Http\Middleware\StoreRouteName::class,
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/avance', function () {
        return view('avance-ventas');
    })->middleware('can:view avance')->name('avance.view');

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

    Route::get('/cliente-masivo', function () {
        return view('cliente-masivo');
    })->middleware('can:view cliente')->name('cliente-masivo.index');

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
        })->middleware('can:stock producto')->name('producto.stock');

        Route::get('/precio-bm', function () {
            //$productos = \App\Models\Producto::withTrashed()->get();
            $datos = \App\Models\ProductoListaPrecio::select(
                'productos.id',
                'productos.name',
                'productos.cantidad',
                'producto_lista_precios.lista_precio_id',
                'producto_lista_precios.precio',
                'marcas.id as marca_id',
                'marcas.name as marca_name',
                'productos.deleted_at',
                'almacen_productos.stock_fisico',
                'almacen_productos.stock_disponible',
            )
                ->join('productos', 'producto_lista_precios.producto_id', '=', 'productos.id')
                ->join('marcas', 'productos.marca_id', '=', 'marcas.id')
                ->join('almacen_productos', 'almacen_productos.producto_id', '=', 'productos.id')
                ->get();

            // Listas de precios únicas ordenadas
            $listasDePrecio = $datos->pluck('lista_precio_id')->unique()->sort()->values();

            // Agrupar por producto id
            $productos = $datos->groupBy('id')->map(function ($grupo) use ($listasDePrecio) {
                $producto = $grupo->first();

                // Mapear precios por lista de precio
                $precios = [];
                foreach ($listasDePrecio as $listaId) {
                    $precios[$listaId] = $grupo->firstWhere('lista_precio_id', $listaId)?->precio ?? null;
                }

                // Retornar objeto
                return (object) [
                    'id' => $producto->id,
                    'name' => $producto->name,
                    'marca' => $producto->marca_name,
                    'cantidad' => $producto->cantidad,
                    'deleted_at' => $producto->deleted_at,
                    'stock_fisico' => $producto->stock_fisico,
                    'stock_disponible' => $producto->stock_disponible,
                    'precios' => (object) $precios,
                ];
            })->values(); // Convertir a colección indexada
            //dd($productos->first(), $listasDePrecio);
            return view('producto.precio-bm', compact('productos'));
        })->middleware('can:precio-bm producto')->name('producto.precio-bm');
    });

    Route::get('/categoria', function () {
        return view('categoria');
    })->middleware('can:view categoria')->name('categoria.index');

    Route::get('/vehiculo', function () {
        return view('vehiculo');
    })->middleware('can:view vehiculo')->name('vehiculo.index');

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
    })->middleware('can:generar-movimientoliq movimiento')->name('movimiento.generar-movimientoliq');

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
    })->middleware('can:view comprobante')->name('comprobantes.imprimir');

    Route::get('/envio-comprobantes', function () {
        return view('envio-comprobantes');
    })->middleware('can:envio comprobante')->name('comprobantes.envio');

    Route::get('/invoice-nota', function () {
        return view('invoice-nota');
    })->middleware('can:invoice-nota comprobante')->name('comprobantes.invoice.nota');

    Route::get('/envio-guias', function () {
        return view('envio-guias');
    })->middleware('can:envio-guias comprobante')->name('guias.envio');

    Route::get('/reporte', function () {
        return view('reporte-view');
    })->middleware('can:view reporte')->name('reporte.view');

    Route::get('/liquidacion', function () {
        return view('liquidacion-view');
    })->middleware('can:view reporte')->name('liquidacion.view');

    Route::get('/permisos-usuario', function () {
        return view('permisos-usuario');
    })->middleware('can:view roles')->name('permisos.usuario');

    Route::get('/permisos-roles', function () {
        return view('permisos-roles');
    })->middleware('can:view roles')->name('permisos.roles');

    Route::get('storage/{anyPath}', function ($anyPath) {
        // Verificar si el archivo existe en el disco 'local'
        if (Storage::disk('local')->exists($anyPath)) {
            // Obtener el archivo y su tipo MIME
            $file = Storage::disk('local')->get($anyPath);
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            $mimeType = $disk->mimeType($anyPath);

            // Devolver el archivo con el tipo MIME adecuado
            return response($file, 200)->header('Content-Type', $mimeType);
        }

        // Si el archivo no existe, devolver un error 404
        return abort(404, 'Archivo no encontrado');
    })->where('anyPath', '.*')->name('storage_file.view');

    Route::get('/pedido_detalles_report', function () {
        return Excel::download(new PedidoDetallesExport, 'pedido_detalles_report_' . now() . '.xlsx');
    })->name('report.pedido_detalle');

    Route::get('/zip', function () {
        // Ubicado en la carpeta storage/app
        // zip -r app.zip .
        $path = storage_path('app/app.zip');
        return response()->download($path);
    })->name('download.zip');
    Route::get('/bdzip', function () {
        $appName = config('app.name'); // o env('APP_NAME', 'default')
        $folderPath = storage_path("app/private/{$appName}");
        $files = collect(File::files($folderPath))->sortByDesc(fn($file) => $file->getMTime());

        if ($files->isEmpty()) {
            abort(404, "No hay backups disponibles");
        }
        $latest = $files->first();

        return response()->download($latest->getPathname());
    })->name('download.bdzip');
});

Route::middleware([
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::post('/guardar-ubicacion', function (\Illuminate\Http\Request $request) {
        session([
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
        ]);
        return response()->json(['success' => true]);
    })->name('guardar.ubicacion');
});

Route::get('/', function () {
    return redirect(route('dashboard'));
})->name('index');

// ... código existente ...
