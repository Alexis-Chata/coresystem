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
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->string('item')->nullable();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('producto_name')->nullable();
            $table->string('cantidad')->nullable();
            $table->string('producto_precio')->nullable();
            $table->string('producto_cantidad_caja')->nullable();
            $table->string('lista_precio')->nullable();
            $table->string('importe')->nullable();
            $table->string('peso')->default('0.250');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
