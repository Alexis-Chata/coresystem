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
        Schema::create('f_guia_detalle_sunats', function (Blueprint $table) {
            $table->id();
            $table->string('cantidad')->nullable();
            $table->string('unidad')->nullable();
            $table->string('serie')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('codigo')->nullable();
            $table->foreignId('f_guia_sunat_id')->constrained('f_guia_sunats');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f_guia_detalle_sunats');
    }
};
