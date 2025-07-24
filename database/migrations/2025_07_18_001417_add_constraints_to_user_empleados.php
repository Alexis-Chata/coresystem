<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            // Eliminar restricciones primero (si aún existen)
            $table->dropForeign(['user_id']);
            $table->dropForeign(['empleado_id']);
        });

        // Eliminar índices personalizados
        DB::statement('ALTER TABLE user_empleados DROP INDEX unique_user_main');
        DB::statement('ALTER TABLE user_empleados DROP INDEX unique_user_empleado');

        // Volver a agregar las foreign keys (sin redefinir columnas)
        Schema::table('user_empleados', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('empleado_id')->references('id')->on('empleados');
        });
    }
};
