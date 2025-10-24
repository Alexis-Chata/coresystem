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
        Schema::table('f_comprobante_sunat_detalles', function (Blueprint $table) {
            $table->decimal('cantidad_devuelta', 15, 2)->default(0)->after('f_comprobante_sunat_id');
            $table->decimal('valor_devuelto', 15, 2)->default(0)->after('cantidad_devuelta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f_comprobante_sunat_detalles', function (Blueprint $table) {
            $table->dropColumn(['cantidad_devuelta', 'valor_devuelto']);
        });
    }
};
