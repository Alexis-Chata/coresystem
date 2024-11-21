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
        Schema::create('movimiento_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_id')->constrained('movimientos');
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('cantidad');
            $table->string('precio_venta_unitario');
            $table->string('precio_venta_total');
            $table->string('costo_unitario');
            $table->string('costo_total');
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
        Schema::dropIfExists('movimiento_detalles');
    }
};
