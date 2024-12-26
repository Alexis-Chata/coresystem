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
        Schema::create('f_comprobante_sunat_detalles', function (Blueprint $table) {
            $table->id();
            $table->string('codProducto')->nullable();
            $table->string('unidad')->nullable();
            $table->string('cantidad')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('mtoBaseIgv')->nullable();
            $table->string('porcentajeIgv')->nullable();
            $table->string('igv')->nullable();
            $table->string('totalImpuestos')->nullable();
            $table->string('tipAfeIgv')->nullable();
            $table->string('mtoValorVenta')->nullable();
            $table->string('mtoValorUnitario')->nullable();
            $table->string('mtoPrecioUnitario')->nullable();
            $table->string('factorIcbper')->nullable();
            $table->string('icbper')->nullable();
            $table->string('mtoBaseIsc')->nullable();
            $table->string('tipSisIsc')->nullable();
            $table->string('porcentajeIsc')->nullable();
            $table->string('isc')->nullable();
            $table->foreignId('f_comprobante_sunat_id')->constrained('f_comprobante_sunats');
            $table->string('ref_producto_lista_precio')->nullable();
            $table->string('ref_producto_precio_cajon')->nullable();
            $table->string('ref_producto_cantidad_cajon')->nullable();
            $table->string('ref_producto_cant_vendida')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f_comprobante_sunat_detalles');
    }
};
