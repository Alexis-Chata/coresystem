<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FGuiaSunat extends Model
{
    /** @use HasFactory<\Database\Factories\FGuiaSunatFactory> */
    use HasFactory;

    protected $fillable = [
        'version',
        'tipoDoc',
        'serie',
        'correlativo',
        'fechaEmision',
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
        'codTraslado',
        'desTraslado',
        'modTraslado',
        'fecTraslado',
        'pesoTotal',
        'undPesoTotal',
        'llegadaUbigeo',
        'llegadaDireccion',
        'partidaUbigeo',
        'partidaDireccion',
        'transportista_tipoDoc',
        'transportista_numDoc',
        'transportista_rznSocial',
        'transportista_nroMtc',
        'vehiculo_placa',
        'chofer_tipoDoc',
        'chofer_nroDoc',
        'chofer_licencia',
        'chofer_nombres',
        'chofer_apellidos',
        'nombrexml',
        //'xmlbase64',
        'hash',
        'cdrxml',
        //'cdrbase64',
        'codigo_sunat',
        'mensaje_sunat',
        'obs',
        'sede_id',
    ];

    public function detalle()
    {
        return $this->hasMany(FGuiaDetalleSunat::class);
    }

    public function sede()
    {
        return $this->belongsTo(FSede::class, 'sede_id');
    }
}
