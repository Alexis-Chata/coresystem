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
        Schema::table('marcas', function (Blueprint $table) {
            $table->boolean('resaltar_cobertura')->default(false)->after('empresa_id');
            $table->string('color_identificador', 20)->default('#4b96e1')->after('resaltar_cobertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->dropColumn(['resaltar_cobertura', 'color_identificador']);
        });
    }
};
