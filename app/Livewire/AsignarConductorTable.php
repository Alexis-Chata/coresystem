<?php

namespace App\Livewire;

use App\Exports\PedidoDetallesExport;
use App\Models\Pedido;
use App\Models\Empleado;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

final class AsignarConductorTable extends PowerGridComponent
{
    public string $tableName = "asignar-conductor-table-wah29z-table";
    public string $sortField = 'conductor_id';
    public string $sortDirection = 'asc';
    public $selectedConductor = "";
    public $conductores = [];
    public $fecha_reparto = null;
    public $rutas_agrupadas = [];

    public $startDate = null;
    public $endDate = null;
    public $pedidosFueraRango = [];
    public $pedidosUltimoMes = [];

    protected function initProperties(): void
    {
        //$this->startDate = Carbon::now()->subWeek()->format("Y-m-d");
        $this->startDate = Carbon::now()->format("Y-m-d");
        $this->endDate = Carbon::now()->format("Y-m-d");
        $this->fecha_reparto = $this->init_fecha_reparto();
    }

    public function init_fecha_reparto()
    {
        $fecha = Carbon::now();

        if ($fecha->isSaturday()) {
            $fecha = $fecha->addDays(2); // Agregar 2 días si es sábado
        } else {
            $fecha = $fecha->addDay(); // Agregar 1 día en otros casos
        }

        return $fecha->format("Y-m-d");
    }


    public function updatedStartDate($value)
    {
        $this->dispatch("pg:eventRefresh-" . $this->tableName);
    }

    public function updatedEndDate($value)
    {
        $this->dispatch("pg:eventRefresh-" . $this->tableName);
    }

    public function setUp(): array
    {
        $this->conductores = $this->getConductores();
        $this->initProperties();
        $this->showCheckBox();
        return [
            PowerGrid::header()->includeViewOnTop("components.date-range-filter"),
            PowerGrid::footer()->showPerPage()->showRecordCount()->showPerPage(perPage: 25)->includeViewOnBottom("components.view-bottom"),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select("ruta_nombre", "pedidos.ruta_id")
                ->dataSource(
                    Ruta::query()
                        ->whereIn("id", function ($query) {
                            $query
                                ->select("ruta_id")
                                ->from("pedidos")
                                ->whereIn("estado", ["asignado", "pendiente"])
                                ->distinct();
                        })
                        ->select(["id", "name"])
                        ->get()
                        ->map(function ($ruta) {
                            return [
                                "id" => $ruta->id,
                                "name" => $ruta->id . " - " . $ruta->name,
                            ];
                        })
                )
                ->optionLabel("name")
                ->optionValue("id"),
        ];
    }

    public function datasource(): Builder
    {
        // Data para la tabla
        $pedidos = Pedido::query()->whereIn('estado', ['pendiente', 'asignado'])
            ->join("rutas", "pedidos.ruta_id", "=", "rutas.id")
            ->join("empleados as vendedores", "pedidos.vendedor_id", "=", "vendedores.id")
            ->join("clientes", "pedidos.cliente_id", "=", "clientes.id")
            ->when($this->startDate && $this->endDate, function ($query) {
                return $query->whereBetween("pedidos.fecha_emision", [
                    $this->startDate . " 00:00:00",
                    $this->endDate . " 23:59:59",
                ]);
            })
            ->select(
                "pedidos.*",
                "rutas.name as ruta_nombre",
                "vendedores.name as vendedor_nombre",
                "clientes.razon_social as cliente_nombre",
                "pedidos.ruta_id"
            );
        //dd($pedidos->get()[0]);

        //$this->rutas_agrupadas = $pedidos->get()->groupBy("ruta_id");
        $this->pedidosFueraRango = Pedido::query()
            ->whereIn('estado', ['pendiente', 'asignado'])
            ->when($this->startDate && $this->endDate, function ($query) {
                return $query->where(function ($q) {
                    $q->where("pedidos.fecha_emision", "<", $this->startDate . " 00:00:00")
                        ->orWhere("pedidos.fecha_emision", ">", $this->endDate . " 23:59:59");
                });
            })
            ->selectRaw('DATE(fecha_emision) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $rutaIds = $pedidos->pluck('ruta_id')->unique();

        $gruposPorDia = Pedido::query()
            ->whereNotIn('estado', ['pendiente', 'asignado'])
            ->whereBetween('fecha_reparto', [
                now()->subWeeks(2)->startOfDay(),
                now()->endOfDay()
            ])
            ->whereIn('pedidos.ruta_id', $rutaIds)
            ->selectRaw('DAYNAME(fecha_reparto) as dia_semana, DAYOFWEEK(fecha_reparto) as orden_semana, COUNT(DISTINCT CONCAT(ruta_id, "-", vendedor_id, "-", conductor_id)) as total_grupos')
            ->groupBy('dia_semana', 'orden_semana')
            ->orderByDesc('total_grupos')
            ->first();

        // Antes de la consulta (para traducir nombres de días a español)
        DB::statement("SET lc_time_names = 'es_ES'");

        $this->pedidosUltimoMes = Pedido::query()
            ->whereNotIn('estado', ['pendiente', 'asignado'])
            ->whereBetween('fecha_reparto', [
                now()->subWeeks(2)->startOfDay(),
                now()->endOfDay()
            ])
            ->whereIn('pedidos.ruta_id', $rutaIds)
            ->when($gruposPorDia?->orden_semana, function ($query) use ($gruposPorDia) {
                $query->whereRaw('DAYOFWEEK(fecha_reparto) = ?', [$gruposPorDia->orden_semana]);
            })
            ->join('rutas', 'pedidos.ruta_id', '=', 'rutas.id')
            ->join('empleados as vendedores', 'pedidos.vendedor_id', '=', 'vendedores.id')
            ->join('empleados as conductores', 'pedidos.conductor_id', '=', 'conductores.id')
            ->selectRaw('
                DAYNAME(pedidos.fecha_reparto) as dia_semana,
                rutas.id as ruta_id,
                rutas.name as ruta_nombre,
                conductores.id as conductor_id,
                conductores.name as conductor_nombre,
                vendedores.id as vendedor_id,
                vendedores.name as vendedor_nombre
            ')
            ->groupBy(
                'dia_semana',
                'rutas.id',
                'ruta_nombre',
                'conductores.id',
                'conductores.name',
                'vendedores.id',
                'vendedores.name'
            )
            ->orderBy('conductor_id')
            ->orderBy('vendedor_id')
            ->orderBy('ruta_id')
            ->get();

        return $pedidos;
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add("id")
            ->add("ruta_nombre", function ($model) {
                return $model->ruta_id . " - " . $model->ruta_nombre;
            })
            ->add("vendedor_nombre", function ($model) {
                return $model->vendedor_id . " - " . $model->vendedor_nombre;
            })
            ->add("conductor_id")
            ->add("cliente_nombre", function ($model) {
                return $model->cliente_id . " - " . $model->cliente_nombre;
            })
            ->add("importe_total", function ($model) {
                return "S/. " . number_format($model->importe_total, 2);
            })
            ->add("fecha_emision");
    }

    public function columns(): array
    {
        return [
            Column::make("Chofer", "conductor_id")->sortable()->editOnClick(),
            Column::make("Ruta", "ruta_nombre")->sortable()->searchable(),
            Column::make("Vendedor", "vendedor_nombre")
                ->sortable()
                ->searchable(),
            Column::make("Cliente", "cliente_nombre")->sortable()->searchable(),
            Column::make("Importe", "importe_total")->bodyAttribute('text-right')
                ->sortable()
                ->searchable(),
            Column::make("Fecha Emision", "fecha_emision")->sortable()->searchable()->hidden(),
            Column::make("User", "user_id")->sortable()->searchable(),
            Column::make("Creado", "created_at")->sortable()->searchable(),
        ];
    }

    public function onUpdatedEditable(
        string|int $id,
        string $field,
        string $value
    ): void {
        $this->fecha_reparto = !empty($this->fecha_reparto) ? $this->fecha_reparto : $this->init_fecha_reparto();
        // Verificar si existe un conductor con ese ID
        $conductor = Empleado::where("id", $value)
            ->where("tipo_empleado", "conductor")
            ->first();

        if ($conductor) {
            // Si existe, actualiza el pedido
            Pedido::find($id)->update([$field => $value, "estado" => "asignado", "fecha_reparto" => $this->fecha_reparto]);
        } else {
            // Si no existe, lanza un error
            $this->addError(
                $field,
                "El conductor con ID " . $value . " no existe"
            );

            // Resetear el valor en la base de datos para reflejar el cambio en PowerGrid
            $pedido = Pedido::find($id);
            $this->dispatch(
                "pg:editable:reset",
                $this->tableName,
                $id,
                $field,
                $pedido->$field
            );
        }
    }

    public function getConductores()
    {
        return Empleado::where("tipo_empleado", "conductor")
            ->select(["id", "name"])
            ->get()
            ->map(function ($conductor) {
                return [
                    "id" => $conductor->id,
                    "name" => $conductor->id . " - " . $conductor->name,
                ];
            });
    }

    public function asignarConductorASeleccionados(): void
    {
        $this->fecha_reparto = !empty($this->fecha_reparto) ? $this->fecha_reparto : $this->init_fecha_reparto();

        if (empty($this->selectedConductor)) {
            $this->dispatch("pg:notification", [
                "type" => "error",
                "title" => "Error",
                "message" => "Debe seleccionar un conductor",
            ]);
            return;
        }

        if (empty($this->checkboxValues)) {
            $this->dispatch("pg:notification", [
                "type" => "error",
                "title" => "Error",
                "message" => "Debe seleccionar al menos un pedido",
            ]);
            return;
        }

        try {
            Pedido::whereIn("id", $this->checkboxValues)->update([
                "conductor_id" => $this->selectedConductor === "null" ? null : $this->selectedConductor,
                "fecha_reparto" => $this->fecha_reparto,
                "estado" => $this->selectedConductor === "null" ? "pendiente" : "asignado",
            ]);

            $this->selectedConductor = "";
            //$this->fecha_reparto = "";
            $this->checkboxValues = [];

            $this->dispatch("pg:notification", [
                "type" => "success",
                "title" => "Éxito",
                "message" =>
                "El conductor ha sido asignado correctamente a los pedidos seleccionados",
            ]);

            // Disparar evento personalizado para limpiar checkbox
            $this->dispatch("limpiarCheckboxHeader");
        } catch (\Exception $e) {
            $this->dispatch("pg:notification", [
                "type" => "error",
                "title" => "Error",
                "message" => "Hubo un error al asignar el conductor",
            ]);
        }
    }

    public function exportarPedidosPDF()
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        // Validar las fechas
        if (!$this->startDate || !$this->endDate) {
            $this->dispatch("pg:notification", [
                "type" => "error",
                "title" => "Error",
                "message" => "Debe seleccionar un rango de fechas válido.",
            ]);
            return;
        }

        $pesosSub = DB::table('pedido_detalles')
            ->select('pedido_id', DB::raw('SUM(CAST(peso AS DECIMAL(15,3))) as peso_pedido'))
            ->groupBy('pedido_id');

        // Obtener los pedidos en el rango
        $pedidos = Pedido::query()
            ->join("rutas", "pedidos.ruta_id", "=", "rutas.id")
            ->join("empleados as vendedores", "pedidos.vendedor_id", "=", "vendedores.id")
            ->join("clientes", "pedidos.cliente_id", "=", "clientes.id")
            ->leftJoin("empleados as conductores", "pedidos.conductor_id", "=", "conductores.id")
            ->leftJoin("vehiculos", "conductores.vehiculo_id", "=", "vehiculos.id")
            ->leftJoinSub($pesosSub, 'pesos', function ($join) {
                $join->on('pesos.pedido_id', '=', 'pedidos.id');
            })
            ->whereBetween("pedidos.fecha_emision", [
                $this->startDate . " 00:00:00",
                $this->endDate . " 23:59:59",
            ])
            ->whereIn("pedidos.estado", ["asignado", "pendiente"])
            ->select(
                "pedidos.id as numero_pedido",
                "pedidos.importe_total",
                "rutas.id as ruta_id",
                "rutas.name as ruta_nombre",
                "vendedores.id as vendedor_id",
                "vendedores.name as vendedor_nombre",
                "conductores.id as conductor_id",
                "conductores.name as conductor_nombre",
                "vehiculos.placa as vehiculo_placa",
                "vehiculos.marca as vehiculo_marca",
                "vehiculos.tonelaje_maximo as vehiculo_tonelaje",
                "pedidos.cliente_id",
                DB::raw("COALESCE(pesos.peso_pedido, 0) as peso_pedido")
            )
            ->get();

        $pedidosAgrupados = $pedidos->groupBy('conductor_id')->map(function ($grupo) {
            $clientesPorRuta = $grupo->groupBy('ruta_id')->map(function ($pedidosPorRuta) {
                return $pedidosPorRuta->unique('cliente_id')->count();
            });

            $pesoPorRuta = $grupo->groupBy('ruta_id')->map(function ($pedidosPorRuta) {
                return $pedidosPorRuta->sum('peso_pedido');
            });

            $pesoTotal = $grupo->sum('peso_pedido');

            $first = $grupo->first();
            $tonelaje = (float) str_replace(',', '', $first->vehiculo_tonelaje ?? 0); // en toneladas
            $capacidadKg = $tonelaje > 0 ? ($tonelaje * 1000) : null;

            // "Diferencia" típico: peso - capacidad (negativo = aún cabe)
            $diferenciaKg = $capacidadKg !== null ? ($pesoTotal - $capacidadKg) : null;

            return [
                'pedidos'        => $grupo,
                'importeTotal'   => $grupo->sum('importe_total'),
                'clientesPorRuta' => $clientesPorRuta,
                'totalClientes'  => $clientesPorRuta->sum(), // Sumar los clientes únicos por ruta
                'pesoPorRuta'    => $pesoPorRuta,
                'pesoTotal'      => $pesoTotal,
                'capacidadKg'    => $capacidadKg,
                'diferenciaKg'   => $diferenciaKg,
            ];
        });

        // Ordenar los conductores sin asignar primero
        $pedidosAgrupados = $pedidosAgrupados->sortBy(function ($grupo, $conductorId) {
            return $conductorId ? 1 : 0; // Los sin asignar tienen conductorId null o 0
        })->sortKeys();
        //dd($pedidosAgrupados->first()['pedidos']->unique('ruta_id')->count());

        if ($pedidos->isEmpty()) {
            $this->dispatch("pg:notification", [
                "type" => "warning",
                "title" => "Sin resultados",
                "message" =>
                "No se encontraron pedidos en el rango seleccionado.",
            ]);
            return;
        }

        // Generar el PDF
        $pdf = Pdf::loadView(
            "pdf.pedidos",
            compact("pedidosAgrupados", "startDate", "endDate")
        );

        // Ruta donde esta/guardará el archivo
        $file_name = "pedidos_" . $this->startDate . "_a_" . $this->endDate . "-created-" . now()->format('d-m-Y_H-i-s') . ".pdf";
        $filePath = 'cola-pdfs/' . $file_name;

        // Guardar el PDF en storage/app/private
        Storage::disk('local')->put($filePath, $pdf->output());

        // Descargar el PDF
        return response()->streamDownload(
            fn() => print $pdf->output(),
            $file_name
        );
    }

    public function report_pedido_detalle()
    {
        $inicio = $this->startDate;
        $fin = $this->endDate;
        return Excel::download(new PedidoDetallesExport($inicio, $fin), 'pedido_detalles_report_' . now() . '.xlsx');
    }

    public function cerrar_sessiones()
    {
        // 1️⃣ Obtener el rol "vendedor"
        $role = Role::findByName('vendedor', 'web');

        // 2️⃣ Quitar el permiso "create pedido"
        $role->revokePermissionTo('create pedido');

        // Obtener los IDs de los usuarios con el rol "vendedor"
        $userIds = User::role('vendedor')->pluck('id');

        // Eliminar sus sesiones
        DB::table('sessions')->whereIn('user_id', $userIds)->delete();
    }

    public function permiso_crear_pedido()
    {
        // 1️⃣ Obtener el rol "vendedor"
        $role = Role::findByName('vendedor', 'web');

        // 2️⃣ Asignarle el permiso "create pedido"
        $role->givePermissionTo('create pedido');
    }

    public function asignacion_sugerida($conductor_id, $ruta_id)
    {
        // Validar entradas obligatorias
        $data = [
            'conductor_id'  => $conductor_id,
            'ruta_id'       => $ruta_id,
            'fecha_reparto' => $this->fecha_reparto,
        ];

        $rules = [
            'conductor_id'  => 'required',
            'ruta_id'       => 'required',
            'fecha_reparto' => 'required|date',
        ];

        $messages = [
            'conductor_id.required'  => 'El conductor es obligatorio.',
            'ruta_id.required'       => 'La ruta es obligatoria.',
            'fecha_reparto.required' => 'Debe definir la fecha de reparto.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag()); // manda errores al @error
            return;
        }

        // 👉 si pasa validación limpio los errores
        $this->resetErrorBag();

        // Construir query
        $query = Pedido::query()
            ->whereIn('estado', ['pendiente', 'asignado'])
            ->where('ruta_id', $ruta_id)
            ->when($this->startDate && $this->endDate, function ($q) {
                return $q->whereBetween("fecha_emision", [
                    $this->startDate . " 00:00:00",
                    $this->endDate . " 23:59:59",
                ]);
            });

        // Actualizar pedidos
        $actualizados = $query->update([
            "conductor_id" => $conductor_id,
            "fecha_reparto" => $this->fecha_reparto,
            "estado" => "asignado",
        ]);

        return $actualizados; // cantidad de registros afectados
    }

    public function asignacion_rapida()
    {
        if (empty($this->fecha_reparto)) {
            $this->fecha_reparto = $this->init_fecha_reparto();
        }
        $this->datasource();

        foreach ($this->pedidosUltimoMes as $pedido) {
            $this->asignacion_sugerida($pedido->conductor_id, $pedido->ruta_id);
        }
    }
}
