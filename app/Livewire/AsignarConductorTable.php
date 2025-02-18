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
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

final class AsignarConductorTable extends PowerGridComponent
{
    public string $tableName = "asignar-conductor-table-wah29z-table";
    public string $sortField = 'conductor_id';
    public string $sortDirection = 'asc';
    public $selectedConductor = "";
    public $conductores = [];
    public $fecha_reparto = "";

    public $startDate = null;
    public $endDate = null;
    protected function initProperties(): void
    {
        //$this->startDate = Carbon::now()->subWeek()->format("Y-m-d");
        $this->startDate = Carbon::now()->format("Y-m-d");
        $this->endDate = Carbon::now()->format("Y-m-d");
        $this->fecha_reparto = Carbon::now();

        if ($this->fecha_reparto->isSaturday()) {
            $this->fecha_reparto = $this->fecha_reparto->addDays(2); // Agregar 2 días si es sábado
        } else {
            $this->fecha_reparto = $this->fecha_reparto->addDay(); // Agregar 1 día en otros casos
        }

        $this->fecha_reparto = $this->fecha_reparto->format("Y-m-d");
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
            PowerGrid::header()->includeViewOnTop(
                "components.date-range-filter"
            ),
            PowerGrid::footer()->showPerPage()->showRecordCount()->showPerPage(perPage: 25),
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
        return Pedido::query()->whereIn('estado', ['pendiente', 'asignado'])
            ->join("rutas", "pedidos.ruta_id", "=", "rutas.id")
            ->join(
                "empleados as vendedores",
                "pedidos.vendedor_id",
                "=",
                "vendedores.id"
            )
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
                return number_format($model->importe_total, 2);
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
            Column::make("Fecha Emision", "fecha_emision")->sortable()->searchable(),
        ];
    }

    public function onUpdatedEditable(
        string|int $id,
        string $field,
        string $value
    ): void {
        $this->fecha_reparto = !empty($this->fecha_reparto) ? $this->fecha_reparto : now()->format("Y-m-d");
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
        $this->fecha_reparto = !empty($this->fecha_reparto) ? $this->fecha_reparto : now()->format("Y-m-d");

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

        // Obtener los pedidos en el rango
        $pedidos = Pedido::query()
            ->join("rutas", "pedidos.ruta_id", "=", "rutas.id")
            ->join("empleados as vendedores", "pedidos.vendedor_id", "=", "vendedores.id")
            ->join("clientes", "pedidos.cliente_id", "=", "clientes.id")
            ->leftJoin("empleados as conductores", "pedidos.conductor_id", "=", "conductores.id")
            ->leftJoin("vehiculos", "conductores.vehiculo_id", "=", "vehiculos.id")
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
                "pedidos.cliente_id"
            )
            ->get();
        $pedidosAgrupados = $pedidos->groupBy('conductor_id')->map(function ($grupo) {
            $clientesPorRuta = $grupo->groupBy('ruta_id')->map(function ($pedidosPorRuta) {
                return $pedidosPorRuta->unique('cliente_id')->count();
            });

            return [
                'pedidos' => $grupo,
                'importeTotal' => $grupo->sum('importe_total'),
                'clientesPorRuta' => $clientesPorRuta,
                'totalClientes' => $clientesPorRuta->sum(), // Sumar los clientes únicos por ruta
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

        // Descargar el PDF
        return response()->streamDownload(
            fn() => print $pdf->output(),
            "pedidos_" . $this->startDate . "_a_" . $this->endDate . ".pdf"
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
}
