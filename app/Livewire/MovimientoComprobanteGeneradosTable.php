<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\FComprobanteSunat;
use App\Models\Movimiento;
use App\Models\Ruta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class MovimientoComprobanteGeneradosTable extends PowerGridComponent
{
    public string $tableName = 'movimiento-comprobante-generados-table-xkwvdn-table';
    public $fecha_reparto;

    #[On('actualizar_fecha_reparto')]
    public function actualizar_fecha($value): void
    {
        $this->fecha_reparto = $value;
    }

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->fecha_reparto = Carbon::now();

        if ($this->fecha_reparto->isSaturday()) {
            $this->fecha_reparto = $this->fecha_reparto->addDays(2); // Agregar 2 días si es sábado
        } else {
            $this->fecha_reparto = $this->fecha_reparto->addDay(); // Agregar 1 día en otros casos
        }

        $this->fecha_reparto = $this->fecha_reparto->format("Y-m-d");

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Movimiento::query()->with(['tipoMovimiento', 'conductor.fSede', 'almacen'])->where('fecha_liquidacion', $this->fecha_reparto)->where('tipo_movimiento_id', 7)->whereIn('estado', ['por liquidar']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('almacen_id')
            ->add('tipo_movimiento_id')
            ->add('fecha_movimiento_formatted', fn(Movimiento $model) => Carbon::parse($model->fecha_movimiento)->format('d/m/Y'))
            ->add('conductor_id')
            ->add('vehiculo_id')
            ->add('nro_doc_liquidacion')
            ->add('fecha_liquidacion_formatted', fn(Movimiento $model) => Carbon::parse($model->fecha_liquidacion)->format('d/m/Y'))
            ->add('comentario', function ($model) { return nl2br(e($model->comentario));})
            ->add('tipo_movimiento_name')
            ->add('empleado_id')
            ->add('estado')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::action('Action'),
            Column::make('Id', 'id'),
            Column::make('Fecha movimiento', 'fecha_movimiento_formatted', 'fecha_movimiento')
                ->sortable(),
            Column::make('Conductor id', 'conductor_id'),
            Column::make('Vehiculo id', 'vehiculo_id'),
            Column::make('Fecha liquidacion', 'fecha_liquidacion_formatted', 'fecha_liquidacion')
                ->sortable(),
            Column::make('Comentario', 'comentario')
                ->sortable()
                ->searchable(),
            Column::make('Tipo movimiento id', 'tipo_movimiento_id')
                ->sortable(),
            Column::make('Almacen id', 'almacen_id'),

            Column::make('Nro doc liquidacion', 'nro_doc_liquidacion')
                ->sortable()
                ->searchable(),



            Column::make('Tipo movimiento name', 'tipo_movimiento_name')
                ->sortable()
                ->searchable(),

            Column::make('Creado por', 'empleado_id'),
            Column::make('Estado', 'estado')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

        ];
    }

    public function filters(): array
    {
        return [
            // Filter::datepicker('fecha_movimiento'),
            // Filter::datepicker('fecha_liquidacion'),
        ];
    }

    #[\Livewire\Attributes\On('exportarpdf')]
    public function exportarMovimientoCargaPDF($movimiento_id)
    {
        $movimiento = Movimiento::with('conductor')->find($movimiento_id);
        $rutas = Ruta::all();
        $vendedores = Empleado::all();
        $comprobantes_rutas = FComprobanteSunat::where('movimiento_id', $movimiento_id)->get()->sortBy('ruta_id');
        //dd($comprobantes_rutas->groupBy('vendedor_id'));
        // Generar el PDF
        $pdf = Pdf::loadView(
            "pdf.conductor-lista-cliente", compact('comprobantes_rutas', 'rutas', 'vendedores', 'movimiento')
        )->setPaper('A4');

        // Descargar el PDF
        return response()->streamDownload(
            fn() => print $pdf->output(),
            "conductor-lista-cliente" . ".pdf"
        );
    }

    public function actions(Movimiento $row): array
    {
        return [
            Button::add('exportarpdf')
                ->slot('PDF: ' . $row->id)
                ->id()
                ->class('bg-red-500 duration-200 ease-in-out hover:bg-red-700 px-4 py-2 rounded-lg text-white transition-colors')
                ->dispatch('exportarpdf', ['movimiento_id' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
        return [
            // Hide button exportarpdf for ID 1
            Rule::button('exportarpdf')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
