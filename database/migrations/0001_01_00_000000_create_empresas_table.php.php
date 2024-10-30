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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('ruc')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('name_comercial')->nullable();
            $table->string('direccion')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cert_path')->nullable();
            $table->string('sol_user')->nullable();
            $table->string('sol_pass')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->boolean('production')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
