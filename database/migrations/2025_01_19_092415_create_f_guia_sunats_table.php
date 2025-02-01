<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('f_guia_sunats', function (Blueprint $table) {
            $table->id();
            $table->string('version')->nullable();
            $table->string('tipoDoc')->nullable();
            $table->string('serie')->nullable();
            $table->string('correlativo')->nullable();
            $table->string('fechaEmision')->nullable();
            $table->string('companyRuc')->nullable();
            $table->string('companyRazonSocial')->nullable();
            $table->string('companyNombreComercial')->nullable();
            $table->string('companyAddressUbigueo')->nullable();
            $table->string('companyAddressDepartamento')->nullable();
            $table->string('companyAddressProvincia')->nullable();
            $table->string('companyAddressDistrito')->nullable();
            $table->string('companyAddressUrbanizacion')->nullable();
            $table->string('companyAddressDireccion')->nullable();
            $table->string('companyAddressCodLocal')->nullable();
            $table->string('clientTipoDoc')->nullable();
            $table->string('clientNumDoc')->nullable();
            $table->string('clientRazonSocial')->nullable();
            $table->string('clientDireccion')->nullable();
            $table->string('codTraslado')->nullable();
            $table->string('desTraslado')->nullable();
            $table->string('modTraslado')->nullable();
            $table->string('fecTraslado')->nullable();
            $table->string('pesoTotal')->nullable();
            $table->string('undPesoTotal')->nullable();
            $table->string('llegadaUbigeo')->nullable();
            $table->string('llegadaDireccion')->nullable();
            $table->string('partidaUbigeo')->nullable();
            $table->string('partidaDireccion')->nullable();
            $table->string('transportista_tipoDoc')->nullable();
            $table->string('transportista_numDoc')->nullable();
            $table->string('transportista_rznSocial')->nullable();
            $table->string('transportista_nroMtc')->nullable();
            $table->string('vehiculo_placa')->nullable();
            $table->string('chofer_tipoDoc')->nullable();
            $table->string('chofer_nroDoc')->nullable();
            $table->string('chofer_licencia')->nullable();
            $table->string('chofer_nombres')->nullable();
            $table->string('chofer_apellidos')->nullable();
            $table->string('nombrexml')->nullable();
            $table->longText('xmlbase64')->nullable();
            $table->string('hash')->nullable();
            $table->string('cdrxml')->nullable();
            $table->longText('cdrbase64')->nullable();
            $table->string('codigo_sunat')->nullable();
            $table->longText('mensaje_sunat')->nullable();
            $table->longText('obs')->nullable();
            $table->string('sede_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f_guia_sunats');
    }
};
