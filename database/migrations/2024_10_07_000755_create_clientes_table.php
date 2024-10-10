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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            //$table->string('name')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('direccion')->nullable();
            $table->string('clientecol')->nullable();
            $table->foreignId('f_tipo_documento_id')->constrained('f_tipo_documentos');
            $table->string('numero_documento')->nullable();
            $table->string('celular')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('lista_precio_id')->constrained('lista_precios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
