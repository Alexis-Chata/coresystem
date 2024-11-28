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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cantidad');
            $table->string('sub_cantidad')->default(0);
            $table->string('tipo')->default('estandar');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('marca_id')->constrained('marcas');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('f_tipo_afectacion_id')->constrained('f_tipo_afectacions');
            $table->string('porcentaje_igv')->nullable();
            $table->string('tipo_unidad')->default("NIU");
            $table->boolean('afecto_icbper')->default(false);
            $table->double('factor_icbper')->default(0);
            $table->boolean('afecto_isc')->default(false);
            $table->double('porcentaje_isc')->default(0);
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
        Schema::dropIfExists('productos');
        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
