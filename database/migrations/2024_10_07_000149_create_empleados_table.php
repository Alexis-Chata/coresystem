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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('name')->nullable();
            $table->string('direccion')->nullable();
            $table->string('celular')->nullable();
            $table->foreignId('f_tipo_documento_id')->constrained('f_tipo_documentos');
            $table->string('numero_documento')->nullable();
            $table->string('tipo_empleado'); // 'conductor', 'vendedor', 'almacenero', etc
            $table->string('numero_brevete')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
