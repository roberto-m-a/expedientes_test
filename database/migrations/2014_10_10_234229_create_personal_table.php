<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\password;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id('IdPersonal');
            $table->string('Nombre');
            $table->string('Apellidos');
            $table->string('Sexo')->nullable();
            $table->timestamps();
            $table->foreignId('IdDepartamento')->nullable()->references('IdDepartamento')->on('departamento');
        });
        DB::table('personal')->insert([
            [
                'IdPersonal'=>10000,
                'Nombre'=>'admin',
                'Apellidos'=>'admin',
                'Sexo'=>'Mujer',
                'IdDepartamento'=>1000,
            ],
            [
                'IdPersonal'=>10001,
                'Nombre'=>'secretariado',
                'Apellidos'=>'test',
                'Sexo'=>'Mujer',
                'IdDepartamento'=>1000,
            ],
            [
                'IdPersonal'=>10002,
                'Nombre'=>'docente',
                'Apellidos'=>'test',
                'Sexo'=>'Hombre',
                'IdDepartamento'=>1000,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
