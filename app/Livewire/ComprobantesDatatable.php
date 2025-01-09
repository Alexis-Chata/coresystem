<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\FComprobanteSunat;

class ComprobantesDatatable extends DataTableComponent
{
    protected $model = FComprobanteSunat::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make("Id", "id")
                ->sortable(),
            Column::make("Ruta id", "ruta_id")
                ->sortable(),
            Column::make("Vendedor id", "vendedor_id")
                ->sortable(),
            Column::make("Conductor id", "conductor_id")
                ->sortable(),
            Column::make("Cliente id", "cliente_id")
                ->sortable(),
            Column::make("Movimiento id", "movimiento_id")
                ->sortable(),
            Column::make("Pedido id", "pedido_id")
                ->sortable(),
            Column::make("Pedido obs", "pedido_obs")
                ->sortable(),
            Column::make("Sede id", "sede_id")
                ->sortable(),
            Column::make("UblVersion", "ublVersion")
                ->sortable(),
            Column::make("TipoDoc", "tipoDoc")
                ->sortable(),
            Column::make("TipoDoc name", "tipoDoc_name")
                ->sortable(),
            Column::make("TipoOperacion", "tipoOperacion")
                ->sortable(),
            Column::make("Serie", "serie")
                ->sortable(),
            Column::make("Correlativo", "correlativo")
                ->sortable(),
            Column::make("FechaEmision", "fechaEmision")
                ->sortable(),
            Column::make("FormaPagoTipo", "formaPagoTipo")
                ->sortable(),
            Column::make("TipoMoneda", "tipoMoneda")
                ->sortable(),
            Column::make("CompanyRuc", "companyRuc")
                ->sortable(),
            Column::make("CompanyRazonSocial", "companyRazonSocial")
                ->sortable(),
            Column::make("CompanyNombreComercial", "companyNombreComercial")
                ->sortable(),
            Column::make("CompanyAddressUbigueo", "companyAddressUbigueo")
                ->sortable(),
            Column::make("CompanyAddressDepartamento", "companyAddressDepartamento")
                ->sortable(),
            Column::make("CompanyAddressProvincia", "companyAddressProvincia")
                ->sortable(),
            Column::make("CompanyAddressDistrito", "companyAddressDistrito")
                ->sortable(),
            Column::make("CompanyAddressUrbanizacion", "companyAddressUrbanizacion")
                ->sortable(),
            Column::make("CompanyAddressDireccion", "companyAddressDireccion")
                ->sortable(),
            Column::make("CompanyAddressCodLocal", "companyAddressCodLocal")
                ->sortable(),
            Column::make("ClientTipoDoc", "clientTipoDoc")
                ->sortable(),
            Column::make("ClientNumDoc", "clientNumDoc")
                ->sortable(),
            Column::make("ClientRazonSocial", "clientRazonSocial")
                ->sortable(),
            Column::make("ClientDireccion", "clientDireccion")
                ->sortable(),
            Column::make("MtoOperGravadas", "mtoOperGravadas")
                ->sortable(),
            Column::make("MtoOperInafectas", "mtoOperInafectas")
                ->sortable(),
            Column::make("MtoOperExoneradas", "mtoOperExoneradas")
                ->sortable(),
            Column::make("MtoIGV", "mtoIGV")
                ->sortable(),
            Column::make("MtoBaseIsc", "mtoBaseIsc")
                ->sortable(),
            Column::make("MtoISC", "mtoISC")
                ->sortable(),
            Column::make("Icbper", "icbper")
                ->sortable(),
            Column::make("TotalImpuestos", "totalImpuestos")
                ->sortable(),
            Column::make("ValorVenta", "valorVenta")
                ->sortable(),
            Column::make("SubTotal", "subTotal")
                ->sortable(),
            Column::make("Redondeo", "redondeo")
                ->sortable(),
            Column::make("MtoImpVenta", "mtoImpVenta")
                ->sortable(),
            Column::make("LegendsCode", "legendsCode")
                ->sortable(),
            Column::make("LegendsValue", "legendsValue")
                ->sortable(),
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
            Column::make("Empresa id", "empresa_id")
                ->sortable(),
            Column::make("Created at", "created_at")
                ->sortable(),
            Column::make("Updated at", "updated_at")
                ->sortable(),
        ];
    }
}
