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
        Schema::table('user_empleados', function (Blueprint $table) {
            // Asegurarse de que la columna tipo sea nullable
            $table->string('tipo')->nullable()->change();
            $table->unique(['user_id', 'empleado_id'], 'unique_user_empleado');
            $table->unique(['user_id', 'tipo'], 'unique_user_main')->where('tipo', 'main');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_empleados', function (Blueprint $table) {
            $table->dropUnique('unique_user_empleado');
            $table->dropUnique('unique_user_main');
            $table->string('tipo')->nullable(false)->change();
        });
    }
};
