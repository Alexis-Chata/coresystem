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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacens');
            $table->foreignId('tipo_movimiento_id')->constrained('tipo_movimientos');
            $table->date('fecha_movimiento');
            $table->foreignId('conductor_id')->nullable()->constrained('empleados');
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos');
            $table->enum('estado', ['liquido', 'facturas_por_generar', 'por liquidar', 'liquidado'])->default('liquido');
            $table->string('nro_doc_liquidacion')->nullable();
            $table->date('fecha_liquidacion')->nullable();
            $table->string('comentario')->nullable();
            $table->string('tipo_movimiento_name');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
