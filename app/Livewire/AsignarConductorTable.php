<?php

namespace App\Livewire;

use App\Models\Pedido;
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

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Pedido::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add("id")
            ->add("ruta_id")
            ->add("vendedor_id")
            ->add("conductor_id")
            ->add("cliente_id")
            ->add("importe_total");
    }

    public function columns(): array
    {
        return [
            Column::make("Conductor id", "conductor_id")
                ->editOnClick(),
            Column::make("Ruta id", "ruta_id"),
            Column::make("Vendedor id", "vendedor_id"),
            Column::make("Cliente id", "cliente_id")
                ->sortable()
                ->searchable(),
            Column::make("Importe total", "importe_total")
                ->sortable()
                ->searchable(),
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        Pedido::find($id)->update([$field => $value]);
    }
}
