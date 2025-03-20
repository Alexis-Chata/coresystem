<?php

namespace App\Exports;

use App\Models\FComprobanteSunat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FComprobanteSunatsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $fecha_inicio;
    private $fecha_fin;

    public function __construct($fecha_inicio = null, $fecha_fin = null)
    {
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return FComprobanteSunat::when($this->fecha_inicio, function ($query) {
            $query->where('fechaEmision', '>=', $this->fecha_inicio);
        })
            ->when($this->fecha_fin, function ($query) {
                $query->where('fechaEmision', '<=', $this->fecha_fin);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'ruta_id',
            'vendedor_id',
            'conductor_id',
            'cliente_id',
            'movimiento_id',
            'pedido_id',
            'pedido_obs',
            'pedido_fecha_factuacion',
            'sede_id',
            'ublVersion',
            'tipoDoc',
            'tipoDoc_name',
            'tipoOperacion',
            'serie',
            'correlativo',
            'fechaEmision',
            'formaPagoTipo',
            'tipoMoneda',
            'companyRuc',
            'companyRazonSocial',
            'companyNombreComercial',
            'companyAddressUbigueo',
            'companyAddressDepartamento',
            'companyAddressProvincia',
            'companyAddressDistrito',
            'companyAddressUrbanizacion',
            'companyAddressDireccion',
            'companyAddressCodLocal',
            'clientTipoDoc',
            'clientNumDoc',
            'clientRazonSocial',
            'clientDireccion',
            'mtoOperGravadas',
            'mtoOperInafectas',
            'mtoOperExoneradas',
            'mtoOperGratuitas',
            'mtoIGV',
            'mtoBaseIsc',
            'mtoISC',
            'icbper',
            'totalImpuestos',
            'valorVenta',
            'subTotal',
            'redondeo',
            'mtoImpVenta',
            'legendsCode',
            'legendsValue',
            'tipDocAfectado',
            'numDocfectado',
            'codMotivo',
            'desMotivo',
            'nombrexml',
            'xmlbase64',
            'hash',
            'cdrxml',
            'cdrbase64',
            'codigo_sunat',
            'mensaje_sunat',
            'obs',
            'estado_reporte',
            'estado_cpe_sunat',
            'empresa_id',
            'created_at',
            'updated_at',
        ];
    }
}
