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
        Schema::create('f_tipo_afectacions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('letra')->nullable();
            $table->string('codigo')->nullable();
            $table->string('tipo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f_tipo_afectacions');
    }
};
