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
        Schema::create('documento', function (Blueprint $table) {
            $table->id('IdDocumento');
            $table->timestamps();
            $table->string('Titulo');
            $table->date('fechaExpedicion');
            $table->date('fechaEntrega')->nullable();
            $table->string('Estatus');
            $table->string('region');
            $table->foreignId('IdTipoDocumento')->references('IdTipoDocumento')->on('tipo_documento');
            $table->foreignId('IdPeriodoEscolar')->references('IdPeriodoEscolar')->on('periodo_escolar');
            $table->foreignId('IdExpediente')->references('IdExpediente')->on('expediente');
            $table->foreignId('IdDepartamento')->nullable()->references('IdDepartamento')->on('departamento');
            $table->foreignId('IdUsuario')->references('id')->on('users');
            $table->string('dependencia')->nullable();
            $table->string('URL');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento');
    }
};
