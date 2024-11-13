<?php

namespace App\Livewire;

use App\Models\FComprobanteSunat;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ComprobanteTable extends PowerGridComponent
{
    public string $tableName = 'comprobante-table-cvrmv4-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
            PowerGrid::responsive()
                ->fixedColumns('id', 'name', Responsive::ACTIONS_COLUMN_NAME),
        ];
    }

    public function datasource(): Builder
    {
        return FComprobanteSunat::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('ublVersion')
            ->add('tipoDoc')
            ->add('tipoDoc_name')
            ->add('tipoOperacion')
            ->add('serie')
            ->add('correlativo')
            ->add('fechaEmision_formatted', fn(FComprobanteSunat $model) => Carbon::parse($model->fechaEmision)->format('d/m/Y'))
            ->add('formaPagoTipo')
            ->add('tipoMoneda')
            ->add('companyRuc')
            ->add('companyRazonSocial')
            ->add('companyNombreComercial')
            ->add('companyAddressUbigueo')
            ->add('companyAddressDepartamento')
            ->add('companyAddressProvincia')
            ->add('companyAddressDistrito')
            ->add('companyAddressUrbanizacion')
            ->add('companyAddressDireccion')
            ->add('companyAddressCodLocal')
            ->add('clientTipoDoc')
            ->add('clientNumDoc')
            ->add('clientRazonSocial')
            ->add('mtoOperGravadas')
            ->add('mtoOperInafectas')
            ->add('mtoOperExoneradas')
            ->add('mtoIGV')
            ->add('mtoBaseIsc')
            ->add('mtoISC')
            ->add('icbper')
            ->add('totalImpuestos')
            ->add('valorVenta')
            ->add('subTotal')
            ->add('redondeo')
            ->add('mtoImpVenta')
            ->add('legendsCode')
            ->add('legendsValue')
            ->add('tipDocAfectado')
            ->add('numDocfectado')
            ->add('codMotivo')
            ->add('desMotivo')
            ->add('nombrexml')
            ->add('xmlbase64')
            ->add('hash')
            ->add('cdrbase64')
            ->add('codigo_sunat')
            ->add('mensaje_sunat')
            ->add('obs')
            ->add('empresa_id')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),
            // Column::make('UblVersion', 'ublVersion')
            //     ->sortable()
            //     ->searchable(),

            Column::make('TipoDoc', 'tipoDoc')
                ->sortable()
                ->searchable(),

            Column::make('TipoDoc name', 'tipoDoc_name')
                ->sortable()
                ->searchable(),

            // Column::make('TipoOperacion', 'tipoOperacion')
            //     ->sortable()
            //     ->searchable(),

            Column::make('Serie', 'serie')
                ->sortable()
                ->searchable(),

            Column::make('Correlativo', 'correlativo')
                ->sortable()
                ->searchable(),

            Column::make('FechaEmision', 'fechaEmision_formatted', 'fechaEmision')
                ->sortable(),

            // Column::make('FormaPagoTipo', 'formaPagoTipo')
            //     ->sortable()
            //     ->searchable(),

            Column::make('TipoMoneda', 'tipoMoneda')
                ->sortable()
                ->searchable(),

            Column::make('CompanyRuc', 'companyRuc')
                ->sortable()
                ->searchable(),

            Column::make('CompanyRazonSocial', 'companyRazonSocial')
                ->sortable()
                ->searchable(),

            // Column::make('CompanyNombreComercial', 'companyNombreComercial')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressUbigueo', 'companyAddressUbigueo')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressDepartamento', 'companyAddressDepartamento')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressProvincia', 'companyAddressProvincia')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressDistrito', 'companyAddressDistrito')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressUrbanizacion', 'companyAddressUrbanizacion')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressDireccion', 'companyAddressDireccion')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('CompanyAddressCodLocal', 'companyAddressCodLocal')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('ClientTipoDoc', 'clientTipoDoc')
            //     ->sortable()
            //     ->searchable(),

            Column::make('ClientNumDoc', 'clientNumDoc')
                ->sortable()
                ->searchable(),

            Column::make('ClientRazonSocial', 'clientRazonSocial')
                ->sortable()
                ->searchable(),

            // Column::make('MtoOperGravadas', 'mtoOperGravadas')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('MtoOperInafectas', 'mtoOperInafectas')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('MtoOperExoneradas', 'mtoOperExoneradas')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('MtoIGV', 'mtoIGV')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('MtoBaseIsc', 'mtoBaseIsc')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('MtoISC', 'mtoISC')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Icbper', 'icbper')
            //     ->sortable()
            //     ->searchable(),

            Column::make('TotalImpuestos', 'totalImpuestos')
                ->sortable()
                ->searchable(),

            Column::make('ValorVenta', 'valorVenta')
                ->sortable()
                ->searchable(),

            // Column::make('SubTotal', 'subTotal')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Redondeo', 'redondeo')
            //     ->sortable()
            //     ->searchable(),

            Column::make('MtoImpVenta', 'mtoImpVenta')
                ->sortable()
                ->searchable(),

            // Column::make('LegendsCode', 'legendsCode')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('LegendsValue', 'legendsValue')
            //     ->sortable()
            //     ->searchable(),

            Column::make('TipDocAfectado', 'tipDocAfectado')
                ->sortable()
                ->searchable(),

            Column::make('NumDocfectado', 'numDocfectado')
                ->sortable()
                ->searchable(),

            Column::make('CodMotivo', 'codMotivo')
                ->sortable()
                ->searchable(),

            Column::make('DesMotivo', 'desMotivo')
                ->sortable()
                ->searchable(),

            // Column::make('Nombrexml', 'nombrexml')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Xmlbase64', 'xmlbase64')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Hash', 'hash')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Cdrbase64', 'cdrbase64')
            //     ->sortable()
            //     ->searchable(),

            Column::make('Codigo sunat', 'codigo_sunat')
                ->sortable()
                ->searchable(),

            Column::make('Mensaje sunat', 'mensaje_sunat')
                ->sortable()
                ->searchable(),

            Column::make('Obs', 'obs')
                ->sortable()
                ->searchable(),

            // Column::make('Empresa id', 'empresa_id'),
            // Column::make('Created at', 'created_at_formatted', 'created_at')
            //     ->sortable(),

            // Column::make('Created at', 'created_at')
            //     ->sortable()
            //     ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            //Filter::datetimepicker('fechaEmision'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(FComprobanteSunat $row): array
    {
        return [
            Button::add('edit')
                ->slot('Editar')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
        return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
