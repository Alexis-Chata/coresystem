<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\Empleado;
use App\Models\Ruta;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class AsignarConductorTable extends PowerGridComponent
{
    public string $tableName = "asignar-conductor-table-wah29z-table";
    public $selectedConductor = "";

    public function setUp(): array
    {
        $this->showCheckBox();
        return [
            //PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
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
        return Pedido::query()
            ->join("rutas", "pedidos.ruta_id", "=", "rutas.id")
            ->join(
                "empleados as vendedores",
                "pedidos.vendedor_id",
                "=",
                "vendedores.id"
            )
            ->join("clientes", "pedidos.cliente_id", "=", "clientes.id")
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
            ->add("importe_total");
    }

    public function columns(): array
    {
        return [
            Column::make("Conductor id", "conductor_id")->editOnClick(),
            Column::make("Ruta", "ruta_nombre")->sortable()->searchable(),
            Column::make("Vendedor", "vendedor_nombre")
                ->sortable()
                ->searchable(),
            Column::make("Cliente", "cliente_nombre")->sortable()->searchable(),
            Column::make("Importe total", "importe_total")
                ->sortable()
                ->searchable(),
        ];
    }

    public function onUpdatedEditable(
        string|int $id,
        string $field,
        string $value
    ): void {
        // Verificar si existe un conductor con ese ID
        $conductor = Empleado::where("id", $value)
            ->where("tipo_empleado", "conductor")
            ->first();

        if ($conductor) {
            // Si existe, actualiza el pedido
            Pedido::find($id)->update([$field => $value]);
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

    public function header(): array
    {
        $conductores = $this->getConductores();

        $selectHtml = '
        <div class="flex items-center gap-4">
            <div class="relative w-72">
                <select
                    wire:model="selectedConductor"
                    class="cursor-pointer block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                >
                    <option value="">Seleccione un conductor</option>';

        foreach ($conductores as $conductor) {
            $selectHtml .=
                '<option value="' .
                $conductor["id"] .
                '">' .
                $conductor["name"] .
                "</option>";
        }

        $selectHtml .= '
                </select>
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Asignar Conductor
                </label>
            </div>
            <button
                wire:click="asignarConductorASeleccionados"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out"
            >
            <span class="flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Asignar
                                                </span>
            </button>
        </div>';

        return [
            Button::add("bulk-asignar")
                ->slot($selectHtml)
                ->class("text-center mr-2"),
        ];
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
                "conductor_id" => $this->selectedConductor,
            ]);

            $this->selectedConductor = "";
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
}
