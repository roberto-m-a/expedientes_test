<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departamento', function (Blueprint $table) {
            $table->id('IdDepartamento');
            $table->string('nombreDepartamento')->unique();
            $table->timestamps();
        });
        DB::table('departamento')->insert(
            [
                'IdDepartamento'=>1000,
                'nombreDepartamento'=>'otro',
            ]
            );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamento');
    }
};
