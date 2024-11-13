<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FComprobanteSunat extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'mtoOperGravadas',
        'mtoOperInafectas',
        'mtoOperExoneradas',
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
        'cdrbase64',
        'codigo_sunat',
        'mensaje_sunat',
        'obs',
        'empresa_id',
    ];
}
