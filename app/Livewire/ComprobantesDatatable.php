<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\FComprobanteSunat;
use App\Services\EnvioSunatService;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Columns\DateColumn;

class ComprobantesDatatable extends DataTableComponent
{
    protected $model = FComprobanteSunat::class;
    protected ?int $searchFilterDebounce = 1000;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function query()
{
    return FComprobanteSunat::query()->select('id'); // Asegúrate de incluir 'id'
}

    public function pdf($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->pdf($comprobante);
    }

    public function xml($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->xml($comprobante);
    }

    public function cdr($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->send($comprobante);
    }

    public function sunatResponse($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        if($comprobante->codigo_sunat === '0') {
            return dd('Aceptado');
        }

        $envioSunat = new EnvioSunatService;
        $envioSunat->send($comprobante);
    }

    public function anular($id)
    {
        dd($id);
    }

    public function columns(): array
    {
        return [
            Column::make('Action')
                ->label(
                    fn($row, Column $column) => view('livewire.components.dropdown')->with([
                        'id' => $row->id,
                    ])
                ),
            Column::make("Id", "id")
                ->hideIf(true),
            DateColumn::make('Fecha Emision', 'fechaEmision')
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y')
                ->emptyValue('Not Found'),
            Column::make("TipoDoc name", "tipoDoc_name")
                ->sortable()
                ->searchable(),
            Column::make("Serie", "serie")
                ->sortable()
                ->searchable(),
            Column::make("Correlativo", "correlativo")
                ->sortable(),
            Column::make("ClientTipoDoc", "tipo_doc.tipo_documento")
                ->sortable(),
            Column::make("ClientNumDoc", "clientNumDoc")
                ->sortable()
                ->searchable(),
            Column::make("ClientRazonSocial", "clientRazonSocial")
                ->sortable()
                ->searchable(),
            Column::make("ClientDireccion", "clientDireccion")
                ->sortable()
                ->searchable(),
            Column::make("MtoImpVenta", "mtoImpVenta")
                ->sortable()
                ->searchable(),
            // Column::make("Ruta", "ruta.name")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Vendedor", "vendedor.name")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Conductor", "conductor.name")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Cliente", "cliente.razon_social")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Movimiento id", "movimiento_id")
            //     ->sortable(),
            // Column::make("Pedido id", "pedido_id")
            //     ->sortable(),
            // Column::make("Pedido obs", "pedido_obs")
            //     ->sortable(),
            // Column::make("Sede", "sede.name")
            //     ->sortable(),
            // Column::make("UblVersion", "ublVersion")
            //     ->sortable(),
            // Column::make("TipoDoc", "tipoDoc")
            //     ->sortable(),
            // Column::make("TipoOperacion", "tipoOperacion")
            //     ->sortable(),
            // Column::make("FormaPagoTipo", "formaPagoTipo")
            //     ->sortable(),
            // Column::make("TipoMoneda", "tipoMoneda")
            //     ->sortable(),
            Column::make("CompanyRuc", "companyRuc")
                ->sortable(),
            Column::make("CompanyRazonSocial", "companyRazonSocial")
                ->sortable(),
            // Column::make("CompanyNombreComercial", "companyNombreComercial")
            //     ->sortable(),
            // Column::make("CompanyAddressUbigueo", "companyAddressUbigueo")
            //     ->sortable(),
            // Column::make("CompanyAddressDepartamento", "companyAddressDepartamento")
            //     ->sortable(),
            // Column::make("CompanyAddressProvincia", "companyAddressProvincia")
            //     ->sortable(),
            // Column::make("CompanyAddressDistrito", "companyAddressDistrito")
            //     ->sortable(),
            // Column::make("CompanyAddressUrbanizacion", "companyAddressUrbanizacion")
            //     ->sortable(),
            Column::make("CompanyAddressDireccion", "companyAddressDireccion")
                ->sortable(),
            Column::make("CompanyAddressCodLocal", "companyAddressCodLocal")
                ->sortable(),
            // Column::make("MtoOperGravadas", "mtoOperGravadas")
            //     ->sortable(),
            // Column::make("MtoOperInafectas", "mtoOperInafectas")
            //     ->sortable(),
            // Column::make("MtoOperExoneradas", "mtoOperExoneradas")
            //     ->sortable(),
            // Column::make("MtoIGV", "mtoIGV")
            //     ->sortable(),
            // Column::make("MtoBaseIsc", "mtoBaseIsc")
            //     ->sortable(),
            // Column::make("MtoISC", "mtoISC")
            //     ->sortable(),
            // Column::make("Icbper", "icbper")
            //     ->sortable(),
            // Column::make("TotalImpuestos", "totalImpuestos")
            //     ->sortable(),
            // Column::make("ValorVenta", "valorVenta")
            //     ->sortable(),
            // Column::make("SubTotal", "subTotal")
            //     ->sortable(),
            // Column::make("Redondeo", "redondeo")
            //     ->sortable(),
            // Column::make("LegendsCode", "legendsCode")
            //     ->sortable(),
            // Column::make("LegendsValue", "legendsValue")
            //     ->sortable(),
            Column::make("TipDocAfectado", "tipDocAfectado")
                ->sortable(),
            Column::make("NumDocfectado", "numDocfectado")
                ->sortable(),
            Column::make("CodMotivo", "codMotivo")
                ->sortable(),
            Column::make("DesMotivo", "desMotivo")
                ->sortable(),
            Column::make("Nombrexml", "nombrexml")
                ->sortable(),
            Column::make("Xmlbase64", "xmlbase64")
                ->sortable(),
            Column::make("Hash", "hash")
                ->sortable(),
            Column::make("Cdrbase64", "cdrbase64")
                ->sortable(),
            Column::make("Codigo sunat", "codigo_sunat")
                ->sortable(),
            Column::make("Mensaje sunat", "mensaje_sunat")
                ->sortable(),
            Column::make("Obs", "obs")
                ->sortable(),
            // Column::make("Empresa id", "empresa_id")
            //     ->sortable(),
            DateColumn::make("Created at", "created_at")
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y H:i:s')
                ->emptyValue('Not Found'),
            DateColumn::make("Updated at", "updated_at")
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y H:i:s')
                ->emptyValue('Not Found'),
        ];
    }
}
