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
        Schema::table('producto_lista_precios', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('precio');
            $table->index(['lista_precio_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_lista_precios', function (Blueprint $table) {
            $table->dropIndex(['lista_precio_id', 'activo']);
            $table->dropColumn('activo');
        });
    }
};
