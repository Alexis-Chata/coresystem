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
        Schema::create('vendedors', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('name')->nullable();
            $table->string('direccion')->nullable();
            $table->string('celular')->nullable();
            $table->foreignId('f_tipo_documento_id')->constrained('f_tipo_documentos');
            $table->string('numero_documento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendedors');
    }
};
