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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas');
            $table->foreignId('f_tipo_comprobante_id')->constrained('f_tipo_comprobantes');
            $table->foreignId('vendedor_id')->constrained('vendedors');
            $table->foreignId('conductor_id')->constrained('conductors');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('fecha_emision')->nullable();
            $table->string('importe_total')->nullable();
            $table->string('nro_doc_liquidacion')->nullable();
            $table->string('lista_precio')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
