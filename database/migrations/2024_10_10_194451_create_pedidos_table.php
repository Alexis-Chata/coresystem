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
            $table->foreignId('vendedor_id')->constrained('empleados')->where('tipo_empleado', 'vendedor');
            $table->foreignId('conductor_id')->nullable()->constrained('empleados')->where('tipo_empleado', 'conductor');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('fecha_emision')->nullable();
            $table->string('importe_total')->nullable();
            $table->string('nro_doc_liquidacion')->nullable();
            $table->string('lista_precio')->nullable();
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
        Schema::dropIfExists('pedidos');
    }
};
