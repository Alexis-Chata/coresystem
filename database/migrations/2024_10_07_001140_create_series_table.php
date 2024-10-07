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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('serie')->nullable();
            $table->string('correlativo')->nullable();
            $table->string('fechaemision')->nullable();
            $table->foreignId('sede_id')->constrained('sedes');
            $table->foreignId('f_tipo_comprobante_id')->constrained('f_tipo_comprobantes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
