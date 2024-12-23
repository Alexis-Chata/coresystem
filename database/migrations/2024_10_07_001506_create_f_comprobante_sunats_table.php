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
            $table->string('ruta_id')->nullable();
            $table->string('vendedor_id')->nullable();
            $table->string('conductor_id')->nullable();
            $table->string('cliente_id')->nullable();
            $table->string('movimiento_id')->nullable();
            $table->string('sede_id')->nullable();
            $table->string('ublVersion')->default("2.1");
            $table->string('tipoDoc')->default("03");
            $table->string('tipoDoc_name')->default("BOLETA ELECTRONICA");
            $table->string('tipoOperacion')->default("0101");
            $table->string('serie')->nullable();
            $table->string('correlativo')->nullable();
            $table->dateTime('fechaEmision')->nullable();
            $table->string('formaPagoTipo')->default("Contado");
            $table->string('tipoMoneda')->default("PEN");
            $table->string('companyRuc')->nullable();
            $table->string('companyRazonSocial')->nullable();
            $table->string('companyNombreComercial')->nullable();
            $table->string('companyAddressUbigueo')->nullable();
            $table->string('companyAddressDepartamento')->nullable();
            $table->string('companyAddressProvincia')->nullable();
            $table->string('companyAddressDistrito')->nullable();
            $table->string('companyAddressUrbanizacion')->nullable();
            $table->string('companyAddressDireccion')->nullable();
            $table->string('companyAddressCodLocal')->default("0000");
            $table->string('clientTipoDoc')->nullable();
            $table->string('clientNumDoc')->nullable();
            $table->string('clientRazonSocial')->nullable();
            $table->string('mtoOperGravadas')->nullable();
            $table->string('mtoOperInafectas')->nullable();
            $table->string('mtoOperExoneradas')->nullable();
            $table->string('mtoIGV')->nullable();
            $table->string('mtoBaseIsc')->nullable(); // Sumatoria MtoBaseISC detalles
            $table->string('mtoISC')->nullable();
            $table->string('icbper')->nullable();
            $table->string('totalImpuestos')->nullable();
            $table->string('valorVenta')->nullable();
            $table->string('subTotal')->nullable();
            $table->string('redondeo')->default("0.0");
            $table->string('mtoImpVenta')->nullable();
            $table->string('legendsCode')->default("1000");
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
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
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
