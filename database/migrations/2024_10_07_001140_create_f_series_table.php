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
        Schema::create('f_series', function (Blueprint $table) {
            $table->id();
            $table->string('serie')->nullable();
            $table->string('correlativo')->nullable();
            $table->string('fechaemision')->nullable();
            $table->foreignId('f_sede_id')->constrained('f_sedes');
            $table->foreignId('f_tipo_comprobante_id')->constrained('f_tipo_comprobantes');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
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
