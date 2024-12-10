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
        Schema::create('almacens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('encargado_id')->nullable()->constrained('empleados');
            $table->foreignId('f_sede_id')->constrained('f_sedes');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['f_sede_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacens');
    }
};
