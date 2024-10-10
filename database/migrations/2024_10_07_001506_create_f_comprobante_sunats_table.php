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
        Schema::create('f_comprobante_sunats', function (Blueprint $table) {
            $table->id();
            $table->string('ublVersion')->nullable();
            $table->string('tipoDoc')->nullable();
            $table->string('tipoDoc_name')->nullable();
            $table->string('tipoOperacion')->nullable();
            $table->string('serie')->nullable();
            $table->string('correlativo')->nullable();
            $table->string('fechaEmision')->nullable();
            $table->string('formaPagoTipo')->nullable();
            $table->string('f_comprobantes_sunatcol')->nullable();
            $table->string('tipoMoneda')->nullable();
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
            $table->string('mtoOperGravadas')->nullable();
            $table->string('mtoOperInafectas')->nullable();
            $table->string('mtoOperExoneradas')->nullable();
            $table->string('mtoIGV')->nullable();
            $table->string('mtoBaseIsc')->nullable();
            $table->string('mtoISC')->nullable();
            $table->string('icbper')->nullable();
            $table->string('totalImpuestos')->nullable();
            $table->string('valorVenta')->nullable();
            $table->string('subTotal')->nullable();
            $table->string('redondeo')->nullable();
            $table->string('mtoImpVenta')->nullable();
            $table->string('legendsCode')->nullable();
            $table->string('legendsValue')->nullable();
            $table->string('tipDocAfectado')->nullable();
            $table->string('numDocfectado')->nullable();
            $table->string('codMotivo')->nullable();
            $table->string('desMotivo')->nullable();
            $table->string('nombrexml')->nullable();
            $table->string('xmlbase64')->nullable();
            $table->string('hash')->nullable();
            $table->string('cdrbase64')->nullable();
            $table->string('codigo_sunat')->nullable();
            $table->string('mensaje_sunat')->nullable();
            $table->string('obs')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f_comprobante_sunats');
    }
};
