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
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('name')->nullable();
            $table->string('dia_visita')->nullable();
            $table->foreignId('vendedor_id')->constrained('empleados')->where('tipo_empleado', 'vendedor');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('lista_precio_id')->constrained('lista_precios');
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
        Schema::table('rutas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
