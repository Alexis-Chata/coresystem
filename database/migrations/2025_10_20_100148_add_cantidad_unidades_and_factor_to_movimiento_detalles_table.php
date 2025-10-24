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
        Schema::table('movimiento_detalles', function (Blueprint $table) {
            $table->integer('cantidad_total_unidades')->nullable()->after('empleado_id');
            $table->integer('factor')->nullable()->after('cantidad_total_unidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento_detalles', function (Blueprint $table) {
            $table->dropColumn(['cantidad_total_unidades', 'factor']);
        });
    }
};
