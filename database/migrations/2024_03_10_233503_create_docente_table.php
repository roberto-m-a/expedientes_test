<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            $table->id('IdDocente');
            $table->timestamps();
            $table->string('GradoAcademico');
            $table->foreignId('IdPersonal')->references('IdPersonal')->on('personal');
            //$table->foreignId('IdDepartamento')->references('IdDepartamento')->on('departamento');
        });
        DB::table('docente')->insert([
            [
                'IdDcoente'=>1,
                'IdPersonal'=>10002,
                'GradoAcademico'=>'Licenciatura',
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};
