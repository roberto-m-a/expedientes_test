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
        Schema::create('periodo_escolar', function (Blueprint $table) {
            $table->id('IdPeriodoEscolar');
            $table->timestamps();
            $table->date('fechaInicio');
            $table->date('fechaTermino');
            $table->string('nombre_corto')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo_escolar');
    }
};
